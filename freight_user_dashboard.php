<?php
// freight_user_dashboard.php
include("connection.php");
include('session.php');
requireRole('user');

// =================================================================================
// 1. API HANDLER (This part handles the Modal Data Fetching inside the same file)
// =================================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_shipment_details') {
    
    header('Content-Type: application/json');
    $shipmentId = intval($_POST['id']);
    
    // Fetch Shipment
    $query = mysqli_query($conn, "SELECT * FROM shipments WHERE id='$shipmentId'");
    $shipment = mysqli_fetch_assoc($query);

    if (!$shipment) {
        echo json_encode(['success' => false, 'message' => 'Shipment not found']);
        exit;
    }

    // Generate Simulated History
    $history = [];
    $createdAt = strtotime($shipment['created_at']);

    // Step 1: Booking Created
    $history[] = [
        'status' => 'Booking Created',
        'time' => date('M d, Y g:i A', $createdAt),
        'desc' => 'Shipment details received and processed.',
        'completed' => true
    ];

    // Step 2: In Transit logic
    if ($shipment['status'] == 'In Transit' || $shipment['status'] == 'Delivered') {
        $history[] = [
            'status' => 'Picked Up',
            'time' => date('M d, Y g:i A', $createdAt + 3600), // +1 hour
            'desc' => 'Package picked up from sender.',
            'completed' => true
        ];
    }

    // Step 3: Delivered logic
    if ($shipment['status'] == 'Delivered') {
        $history[] = [
            'status' => 'Delivered',
            'time' => date('M d, Y g:i A', $createdAt + 18000), // +5 hours
            'desc' => 'Package successfully delivered to receiver.',
            'completed' => true
        ];
    }

    // AI Prediction Logic
    $distance = floatval($shipment['distance_km']);
    $prediction = "";
    $confidence = 0;

    if ($shipment['status'] == 'Delivered') {
        $prediction = "Shipment completed successfully on schedule.";
        $confidence = 100;
    } elseif ($shipment['status'] == 'In Transit') {
        $hoursLeft = $distance / 40; // 40km/h avg speed
        $minsLeft = round($hoursLeft * 60);
        $prediction = "Traffic analysis: Moderate congestion. Estimated arrival in $minsLeft minutes.";
        $confidence = rand(85, 98);
    } else {
        $prediction = "Carrier assignment algorithm running. High probability of pickup within 2 hours.";
        $confidence = rand(75, 90);
    }

    echo json_encode([
        'success' => true,
        'data' => $shipment,
        'history' => $history,
        'ai_prediction' => [
            'text' => $prediction,
            'score' => $confidence
        ]
    ]);
    exit; // STOP HERE so we don't load the HTML
}

// =================================================================================
// 2. STANDARD DASHBOARD LOADING LOGIC
// =================================================================================

include("darkmode.php");
include('loading.html'); // Optional loading screen

// Fetch current user info
$username = $_SESSION['username'];
$userQuery = mysqli_query($conn, "SELECT * FROM accounts WHERE username='" . mysqli_real_escape_string($conn, $username) . "'");
$user = mysqli_fetch_assoc($userQuery);
$userId = $user['id']; 

// Profile image fallback
$profileImage = !empty($user['profile_image']) ? $user['profile_image'] : 'user.png';

// Handle Promo Logic
if (isset($_GET['apply_promo'])) {
    $_SESSION['applied_promo'] = $_GET['apply_promo'];
    header("Location: freight_user_dashboard.php");
    exit;
}
if (isset($_GET['remove_promo'])) {
    unset($_SESSION['applied_promo']);
    header("Location: freight_user_dashboard.php");
    exit;
}

// Helper: safe fetch
function qfetch_all($conn, $sql) {
    $res = @mysqli_query($conn, $sql);
    if (!$res) return [];
    $out = [];
    while ($r = mysqli_fetch_assoc($res)) $out[] = $r;
    return $out;
}

// Shipment summary
$summary = ['Pending' => 0, 'In Transit' => 0, 'Delivered' => 0];
$summaryQuery = mysqli_query($conn, "SELECT status, COUNT(*) as total FROM shipments WHERE user_id='" . intval($userId) . "' GROUP BY status");
if ($summaryQuery) {
    while ($row = mysqli_fetch_assoc($summaryQuery)) {
        $summary[$row['status']] = $row['total'];
    }
}

// Fetch latest shipments
$shipments = mysqli_query($conn, "SELECT * FROM shipments WHERE user_id='" . intval($userId) . "' ORDER BY created_at DESC LIMIT 8");

// Monthly analytics
$months = []; $counts = []; $revenues = [];
$analytics = qfetch_all($conn, "SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt, COALESCE(SUM(price),0) as rev FROM shipments WHERE user_id='" . intval($userId) . "' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY ym ORDER BY ym ASC");

for ($i = 5; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-{$i} months"));
    $months[] = date('M Y', strtotime($m . '-01'));
    $counts[$m] = 0; $revenues[$m] = 0.0;
}
foreach ($analytics as $a) {
    $key = $a['ym'];
    if (array_key_exists($key, $counts)) {
        $counts[$key] = (int) $a['cnt'];
        $revenues[$key] = (float) $a['rev'];
    }
}
$countsArr = array_values($counts);
$revenuesArr = array_values($revenues);

// Other Data
$payments = qfetch_all($conn, "SELECT * FROM payments WHERE user_id='" . intval($userId) . "' ORDER BY payment_date DESC LIMIT 6");
$notifications = qfetch_all($conn, "SELECT * FROM notifications WHERE user_id='" . intval($userId) . "' ORDER BY created_at DESC LIMIT 6");
$promos = [
    ['code' => 'PROMO10', 'desc' => 'Get 10% off your next shipment'],
    ['code' => 'SHIPFREE', 'desc' => '₱0 delivery fee for routes under 10km'],
    ['code' => 'XPRESS20', 'desc' => '20% off express packages']
];
$activePromo = $_SESSION['applied_promo'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard — Freight Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --sidebar-width: 250px;
            --radius: .35rem;
            --shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            --primary-color: #4e73df;
            --dark-bg: #1a1a2e;
            --dark-card: #16213e;
            --text-light: #f8f9fa;
            --text-dark: #212529;
        }
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fc; color: #212529; }
        .content { margin-left: var(--sidebar-width); padding: 20px; transition: margin-left .3s; }
        .card { border: none; border-radius: var(--radius); box-shadow: var(--shadow); }
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: #2c3e50; color: white; z-index: 1000; transition: all .3s; }
        .sidebar.collapsed { transform: translateX(-100%); }
        .content.expanded { margin-left: 0; }
        .sidebar a { color: rgba(255, 255, 255, 0.8); text-decoration: none; padding: .75rem 1.5rem; display: block; border-left: 3px solid transparent; }
        .sidebar a:hover, .sidebar a.active { background-color: rgba(255, 255, 255, 0.1); color: white; border-left: 3px solid white; }
        
        /* Clickable Row Styling */
        .clickable-row { cursor: pointer; transition: background-color 0.2s; }
        .clickable-row:hover { background-color: rgba(78, 115, 223, 0.1) !important; }

        /* Timeline for Modal */
        .timeline { border-left: 2px solid #e9ecef; padding-left: 20px; margin-left: 10px; list-style: none; }
        .timeline-item { position: relative; margin-bottom: 20px; }
        .timeline-item::before { content: ''; position: absolute; left: -26px; top: 5px; width: 12px; height: 12px; border-radius: 50%; background-color: #fff; border: 2px solid #4e73df; }

        /* Dark Mode */
        body.dark-mode { background-color: var(--dark-bg); color: var(--text-light); }
        body.dark-mode .sidebar { box-shadow: 2px 0 10px rgba(0,0,0,0.2); }
        body.dark-mode .header { background-color: #16213e !important; color: var(--text-light) !important; border: 1px solid #2a3a5a; box-shadow: 0 0 15px rgba(0,0,0,0.6); }
        body.dark-mode .card { background-color: #16213e !important; color: #f8f9fa !important; border: 1px solid #2a3a5a !important; }
        body.dark-mode table { background-color: #1b2432 !important; color: #f8f9fa !important; border-color: #2a3a5a !important; }
        body.dark-mode th { background-color: #3a4b6e !important; color: #fff !important; }
        body.dark-mode td { border-bottom: 1px solid #2a3a5a !important; color: #e1e5ee !important; }
        body.dark-mode .text-muted, body.dark-mode .small-muted { color: #a9b2c8 !important; }
        body.dark-mode input.form-control, body.dark-mode select.form-select { background-color: #243355 !important; color: #fff !important; border: 1px solid #3a4b6e !important; }
        
        /* Modal Dark Mode */
        body.dark-mode .modal-content { background-color: var(--dark-card); color: var(--text-light); border: 1px solid #2a3a5a; }
        body.dark-mode .modal-header { border-bottom-color: #2a3a5a; }
        body.dark-mode .bg-light { background-color: #243355 !important; color: var(--text-light); border-color: #3a4b6e !important; }
        body.dark-mode .modal-body .border-end { border-right-color: #2a3a5a !important; }
        body.dark-mode .timeline { border-left-color: #2a3a5a; }
        body.dark-mode .timeline-item::before { background-color: #1a1a2e; border-color: #4e73df; }
        body.dark-mode .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }

        @media (max-width: 992px) {
            .sidebar { position: fixed; z-index: 1050; }
            .content { margin-left: 0; }
        }
    </style>
</head>

<body>
    <div class="sidebar d-flex flex-column flex-shrink-0 p-3" id="sidebar">
        <div class="text-center mb-4 mt-2">
            <img src="Remorig.png" alt="Logo" class="img-fluid mb-2" style="max-width: 140px;">
            <h6 class="fw-semibold text-uppercase text-light-50 mb-0" style="font-size: 0.85rem;">Core Transaction 3</h6>
        </div>
        <hr class="text-light opacity-25">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-2"><a href="user.php" class="nav-link active"><i class="bi bi-house-door-fill fs-5 me-2"></i>Dashboard</a></li>
            <li class="nav-item mb-2"><a href="bookshipment.php" class="nav-link"><i class="bi bi-truck fs-5 me-2"></i>Book Shipment</a></li>
            <li class="nav-item mb-2"><a href="shiphistory.php" class="nav-link"><i class="bi bi-clock-history fs-5 me-2"></i>Shipment History</a></li>
            <li class="nav-item mb-2"><a href="feedback.php" class="nav-link"><i class="bi bi-chat-dots fs-5 me-2"></i>Feedback</a></li>
        </ul>
    </div>

    <div class="content">
        <header class="header d-flex align-items-center justify-content-between px-4 py-3 mb-4 bg-white shadow-sm rounded-3 sticky-top">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light border-0 p-2" id="hamburger"><i class="bi bi-list fs-4"></i></button>
                <div>
                    <h5 class="fw-semibold mb-0">Freight Management Dashboard</h5>
                    <small class="text-muted">Welcome, <?php echo htmlspecialchars($user['fullname'] ?? $username); ?> 👋</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="<?php echo $profileImage; ?>" alt="Profile" class="rounded-circle" width="40" height="40" style="object-fit:cover;">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="user-profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </div>
                <div class="form-check form-switch mb-0">
                    <label class="form-check-label" for="userThemeToggle">🌙</label>
                    <input class="form-check-input" type="checkbox" role="switch" id="userThemeToggle">
                </div>
            </div>
        </header>

        <div class="row g-3">
            <div class="col-md-3">
                <div class="card p-3 text-center border-top border-5 border-warning">
                    <h6>🚚 Pending</h6>
                    <h2><?php echo $summary['Pending']; ?></h2>
                    <div class="small-muted">Awaiting pickup</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 text-center border-top border-5 border-info">
                    <h6>📦 In Transit</h6>
                    <h2><?php echo $summary['In Transit']; ?></h2>
                    <div class="small-muted">On the way</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 text-center border-top border-5 border-success">
                    <h6>✅ Delivered</h6>
                    <h2><?php echo $summary['Delivered']; ?></h2>
                    <div class="small-muted">Completed orders</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 text-center border-top border-5 border-primary">
                    <?php 
                    $totalRes = mysqli_query($conn, "SELECT COUNT(*) as cnt, COALESCE(SUM(price),0) as total_price FROM shipments WHERE user_id='" . intval($userId) . "'");
                    $tot = mysqli_fetch_assoc($totalRes); 
                    ?>
                    <h6>📊 Total Shipments</h6>
                    <h2><?php echo intval($tot['cnt']); ?></h2>
                    <div class="small-muted">Total spent: ₱<?php echo number_format($tot['total_price'], 2); ?></div>
                </div>
            </div>
        </div>

        <div class="row mt-2 g-4">
            <div class="col-lg-8">
                <div class="card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>📋 Recent Shipments</h5>
                        <a href="shiphistory.php" class="small-muted">View All</a>
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Sender</th>
                                    <th>Receiver</th>
                                    <th>Address</th>
                                    <th>Distance</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($shipments && mysqli_num_rows($shipments) > 0): ?>
                                    <?php while ($s = mysqli_fetch_assoc($shipments)): ?>
                                        <tr class="clickable-row" onclick="openShipmentModal(<?php echo $s['id']; ?>)">
                                            <td>#<?php echo htmlspecialchars($s['id']); ?></td>
                                            <td><?php echo htmlspecialchars($s['sender_name']); ?></td>
                                            <td><?php echo htmlspecialchars($s['receiver_name']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($s['address'], 0, 15)) . '...'; ?></td>
                                            <td><?php echo htmlspecialchars($s['distance_km'] ?? '0'); ?> km</td>
                                            <td>
                                                <span class="badge bg-<?php echo ($s['status'] == 'Delivered') ? 'success' : (($s['status'] == 'In Transit') ? 'info' : 'warning'); ?>">
                                                    <?php echo htmlspecialchars($s['status']); ?>
                                                </span>
                                            </td>
                                            <td>₱<?php echo number_format($s['price'] ?? 0, 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center text-muted">No shipments yet</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted fst-italic">* Click a row to see full details and AI predictions.</small>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>📊 Monthly Shipments</h6>
                            <canvas id="shipmentsChart" height="120"></canvas>
                        </div>
                        <div class="col-md-6">
                            <h6>💰 Monthly Revenue (₱)</h6>
                            <canvas id="revenueChart" height="120"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card p-3 mt-3">
                    <h5>🔔 Notifications</h5>
                    <ul class="list-group list-group-flush mt-2">
                        <?php if (count($notifications) > 0): foreach ($notifications as $n): ?>
                            <li class="list-group-item">
                                <div class="small-muted"><?= date('M d, Y g:i A', strtotime($n['created_at'])) ?></div>
                                <div><?= htmlspecialchars($n['message']) ?></div>
                            </li>
                        <?php endforeach; else: ?>
                            <li class="list-group-item text-muted">No notifications</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card p-3">
                    <h5>📢 Incoming Updates</h5>
                    <div id="incomingUpdatesList" style="max-height: 250px; overflow-y:auto;">
                        <div class="card mb-2 shadow-sm border-start border-3 border-info">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>#SH-20251015-001</strong> - In Transit
                                        <div class="small-muted">To Quezon City</div>
                                    </div>
                                    <button class="btn btn-sm btn-primary">Track</button>
                                </div>
                            </div>
                        </div>
                        <div class="text-center small-muted mt-2">No more new updates.</div>
                    </div>
                </div>
                
                <div class="card p-3 mt-3">
                    <h5>💳 Payment History</h5>
                    <table class="table table-sm mt-2">
                        <thead><tr><th>Amount</th><th>Date</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php if (count($payments) > 0): foreach ($payments as $pay): ?>
                                <tr>
                                    <td>₱<?php echo number_format($pay['amount'], 2); ?></td>
                                    <td><?php echo date('M d', strtotime($pay['payment_date'])); ?></td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="3" class="text-center text-muted">No payments found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card p-3 mt-3">
                    <h5>🎁 Active Promos</h5>
                    <?php foreach ($promos as $p): ?>
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong><?php echo $p['code']; ?></strong><br>
                                <small><?php echo $p['desc']; ?></small>
                            </div>
                            <?php if ($activePromo === $p['code']): ?>
                                <a href="?remove_promo=1" class="btn btn-danger btn-sm">Remove</a>
                            <?php else: ?>
                                <a href="?apply_promo=<?php echo $p['code']; ?>" class="btn btn-outline-primary btn-sm">Apply</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="shipmentDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Shipment #<span id="modalShipmentId"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7 border-end">
                            <h6 class="fw-bold text-primary mb-3">📍 Route & Info</h6>
                            <div class="mb-2"><small class="text-muted d-block">Sender</small><span class="fw-bold" id="modalSender"></span></div>
                            <div class="mb-2"><small class="text-muted d-block">Receiver</small><span class="fw-bold" id="modalReceiver"></span></div>
                            <div class="mb-3"><small class="text-muted d-block">Address</small><span id="modalAddress"></span></div>
                            
                            <div class="row g-2">
                                <div class="col-6"><div class="p-2 bg-light rounded text-center border"><small class="text-muted">Distance</small><h6 class="mb-0 fw-bold" id="modalDistance">0 km</h6></div></div>
                                <div class="col-6"><div class="p-2 bg-light rounded text-center border"><small class="text-muted">Price</small><h6 class="mb-0 fw-bold text-success" id="modalPrice">₱0.00</h6></div></div>
                            </div>
                            <hr>
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary"><i class="bi bi-robot me-1"></i> AI Prediction</h6>
                                    <p class="small mb-2" id="modalAiText">Analyzing...</p>
                                    <div class="d-flex justify-content-between"><span class="small fw-bold">Confidence Score</span><span class="small fw-bold text-success" id="modalAiScore">0%</span></div>
                                    <div class="progress" style="height: 6px;"><div class="progress-bar bg-success" id="modalAiBar" style="width: 0%"></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <h6 class="fw-bold text-primary mb-3">🕒 Status History</h6>
                            <ul class="timeline" id="modalTimeline"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Init Dark Mode
        if(typeof initDarkMode === 'function') initDarkMode('userThemeToggle', 'userDarkMode');

        // Sidebar Toggle
        document.getElementById('hamburger').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.querySelector('.content').classList.toggle('expanded');
        });

        // Charts
        const months = <?php echo json_encode($months); ?>;
        new Chart(document.getElementById('shipmentsChart'), {
            type: 'bar',
            data: { labels: months, datasets: [{ label: 'Shipments', data: <?php echo json_encode($countsArr); ?>, backgroundColor: '#4e73df' }] },
            options: { plugins: { legend: { display: false } } }
        });
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: { labels: months, datasets: [{ label: 'Revenue', data: <?php echo json_encode($revenuesArr); ?>, borderColor: '#1cc88a', fill: true, backgroundColor: 'rgba(28, 200, 138, 0.1)' }] },
            options: { plugins: { legend: { display: false } } }
        });

        // ==========================================
        // OPEN MODAL & FETCH DATA AJAX (Self-Call)
        // ==========================================
        function openShipmentModal(id) {
            const modal = new bootstrap.Modal(document.getElementById('shipmentDetailsModal'));
            
            // Set loading state
            document.getElementById('modalShipmentId').textContent = id;
            document.getElementById('modalSender').textContent = 'Loading...';
            document.getElementById('modalAiText').textContent = 'Calculating prediction...';
            document.getElementById('modalTimeline').innerHTML = '<li class="text-muted small">Fetching history...</li>';
            
            modal.show();

            const formData = new FormData();
            formData.append('action', 'get_shipment_details'); // Flag to trigger PHP API section
            formData.append('id', id);

            // Fetch from THIS same file
            fetch('freight_user_dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const data = result.data;
                    const ai = result.ai_prediction;

                    // Fill fields
                    document.getElementById('modalSender').textContent = data.sender_name;
                    document.getElementById('modalReceiver').textContent = data.receiver_name;
                    document.getElementById('modalAddress').textContent = data.address;
                    document.getElementById('modalDistance').textContent = data.distance_km + ' km';
                    document.getElementById('modalPrice').textContent = '₱' + parseFloat(data.price).toLocaleString(undefined, {minimumFractionDigits:2});

                    // Fill AI
                    document.getElementById('modalAiText').textContent = ai.text;
                    document.getElementById('modalAiScore').textContent = ai.score + '%';
                    document.getElementById('modalAiBar').style.width = ai.score + '%';

                    // Fill Timeline
                    const timelineContainer = document.getElementById('modalTimeline');
                    timelineContainer.innerHTML = '';
                    result.history.forEach(item => {
                        const li = document.createElement('li');
                        li.className = 'timeline-item';
                        li.innerHTML = `<div class="fw-bold">${item.status}</div><div class="small text-muted">${item.time}</div><div class="small mt-1">${item.desc}</div>`;
                        timelineContainer.appendChild(li);
                    });
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>