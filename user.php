<?php
ob_start(); 
// user.php (Updated with Notification Bell)
include("connection.php");
include('session.php');
requireRole('user');

// =================================================================================
// 1. API HANDLERS
// =================================================================================

// --- A. GET SHIPMENT DETAILS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_shipment_details') {
    ob_clean();
    header('Content-Type: application/json');
    $shipmentId = intval($_POST['id']);
    
    // Select * will include origin_lat, origin_lng, etc.
    $query = mysqli_query($conn, "SELECT * FROM shipments WHERE id='$shipmentId'");
    $shipment = mysqli_fetch_assoc($query);

    if (!$shipment) { echo json_encode(['success' => false, 'message' => 'Shipment not found']); exit; }

    $history = [];
    $createdAt = strtotime($shipment['created_at']);
    $history[] = ['status' => 'Booking Created', 'time' => date('M d, Y g:i A', $createdAt), 'desc' => 'Shipment details received.', 'completed' => true];

    $status = $shipment['status'];
    
    if ($status == 'Cancelled') {
        $history[] = ['status' => 'Cancelled', 'time' => date('M d, Y g:i A', $createdAt + 600), 'desc' => 'Reason: ' . ($shipment['feedback_text'] ?? 'N/A'), 'completed' => true];
    } else {
        if (in_array($status, ['READY_TO_DISPATCH', 'IN_TRANSIT', 'ARRIVED', 'DELIVERED'])) {
            $history[] = ['status' => 'Processing', 'time' => '---', 'desc' => 'Your package is being prepared.', 'completed' => true];
        }
        if (in_array($status, ['IN_TRANSIT', 'ARRIVED', 'DELIVERED'])) {
            $history[] = ['status' => 'In Transit', 'time' => '---', 'desc' => 'Package is on the way.', 'completed' => true];
        }
        if ($status == 'DELIVERED') {
            $delTime = !empty($shipment['updated_at']) ? strtotime($shipment['updated_at']) : ($createdAt + 18000);
            $history[] = ['status' => 'Delivered', 'time' => date('M d, Y g:i A', $delTime), 'desc' => 'Package delivered successfully.', 'completed' => true];
        }
    }

    $prediction = "Standard routing applied.";
    $confidence = 85;
    
    if ($status == 'DELIVERED') { $prediction = "Completed successfully."; $confidence = 100; }
    elseif ($status == 'Cancelled') { $prediction = "Cancelled."; $confidence = 0; }
    else {
        if(!empty($shipment['ai_estimated_time'])) {
            $prediction = $shipment['ai_estimated_time'];
        }
    }

    echo json_encode(['success' => true, 'data' => $shipment, 'history' => $history, 'ai_prediction' => ['text' => $prediction, 'score' => $confidence]]);
    exit;
}

// --- B. GET AI UPDATES ---
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_ai_updates') {
    ob_clean(); 
    header('Content-Type: application/json');

    date_default_timezone_set('Asia/Manila');
    $currentDate = date("l, F j, Y"); 
    $currentTime = date("g:i A");      
    $apiKey = "AIzaSyBIEaGsPg86hYn6g7dgkw8zQcdQa5GtgS8-"; // Replace with your key

    $prompt = "You are a weather AI specialist for the Philippines. Context: $currentTime, $currentDate. Generate a short weather forecast (max 20 words). Return JSON: { \"message\": \"text\", \"type\": \"weather\" }";
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;    
    $data = [ "contents" => [ [ "parts" => [ ["text" => $prompt] ] ] ] ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    $decoded = json_decode($response, true);
    
    if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
        $rawText = $decoded['candidates'][0]['content']['parts'][0]['text'];
        $startIndex = strpos($rawText, '{');
        $endIndex = strrpos($rawText, '}');
        if ($startIndex !== false) {
            $cleanJson = substr($rawText, $startIndex, $endIndex - $startIndex + 1);
            $aiData = json_decode($cleanJson, true);
            $icon = ($aiData['type']=='traffic') ? '🚦' : '🌦️';
            $color = ($aiData['type']=='traffic') ? 'text-warning' : 'text-info';
            echo json_encode(['success' => true, 'message' => $aiData['message'], 'icon' => $icon, 'color' => $color, 'timestamp' => $currentTime]);
            exit;
        }
    }
    echo json_encode(['success' => true, 'message' => "System normal.", 'icon' => "📡", 'color' => "text-muted", 'timestamp' => $currentTime]);
    exit;
}

// --- C. SUBMIT RATING ---
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_rating') {
    ob_clean();
    header('Content-Type: application/json');
    $id = intval($_POST['id']);
    $rating = intval($_POST['rating']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    if (mysqli_query($conn, "UPDATE shipments SET rating='$rating', feedback_text='$feedback' WHERE id='$id'")) {
        echo json_encode(['success' => true, 'message' => 'Thank you for your feedback!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
    }
    exit;
}

// =================================================================================
// 2. PAGE CONTENT
// =================================================================================
include("darkmode.php");
include('loading.html');

$username = $_SESSION['email']; 
$userQuery = mysqli_query($conn, "SELECT * FROM accounts WHERE email='" . mysqli_real_escape_string($conn, $username) . "'");
$user = mysqli_fetch_assoc($userQuery);
$userId = $user['id'];
$profileImage = !empty($user['profile_image']) ? $user['profile_image'] : 'user.png';

// HELPERS & FETCH DATA
function getStatusBadge($status) {
    switch(strtoupper($status)) {
        case 'PENDING': return '<span class="badge bg-warning text-dark">Pending</span>';
        case 'IN_TRANSIT': return '<span class="badge bg-primary">In Transit</span>';
        case 'DELIVERED': return '<span class="badge bg-success">Delivered</span>';
        case 'CANCELLED': return '<span class="badge bg-danger">Cancelled</span>';
        default: return '<span class="badge bg-secondary">'.$status.'</span>';
    }
}

$summary = ['Pending' => 0, 'In Transit' => 0, 'Delivered' => 0];
$totalQuery = mysqli_query($conn, "SELECT status FROM shipments WHERE user_id='" . intval($userId) . "'");
$totalCount = 0;
while($row = mysqli_fetch_assoc($totalQuery)) {
    $st = strtoupper($row['status']);
    if(in_array($st, ['PENDING','BOOKED'])) $summary['Pending']++;
    elseif(in_array($st, ['IN_TRANSIT','READY_TO_DISPATCH','ARRIVED'])) $summary['In Transit']++;
    elseif($st == 'DELIVERED') $summary['Delivered']++;
    $totalCount++;
}

// Financials
$financeQuery = mysqli_query($conn, "SELECT SUM(CASE WHEN status='DELIVERED' THEN price ELSE 0 END) as spent, SUM(CASE WHEN status NOT IN ('DELIVERED','CANCELLED') THEN price ELSE 0 END) as upcoming FROM shipments WHERE user_id='$userId'");
$finance = mysqli_fetch_assoc($financeQuery);
$totalSpent = $finance['spent'] ?? 0;
$upcomingExpense = $finance['upcoming'] ?? 0;

// Action Center
$toRateQuery = mysqli_query($conn, "SELECT id, updated_at FROM shipments WHERE user_id='$userId' AND status='DELIVERED' AND (rating IS NULL OR rating = 0) LIMIT 5");
$toRateCount = mysqli_num_rows($toRateQuery);

// Alerts
$alertQuery = mysqli_query($conn, "SELECT id, status, feedback_text FROM shipments WHERE user_id='$userId' AND status IN ('CANCELLED', 'RETURNED') ORDER BY updated_at DESC LIMIT 1");
$hasAlert = mysqli_num_rows($alertQuery) > 0;
$alertData = mysqli_fetch_assoc($alertQuery);

// Recent Shipments
$shipments = mysqli_query($conn, "SELECT * FROM shipments WHERE user_id='$userId' ORDER BY created_at DESC LIMIT 8");

// Charts Data
$months = []; $counts = []; $revenues = [];
for ($i = 5; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-{$i} months"));
    $months[] = date('M Y', strtotime($m . '-01'));
    $counts[$m] = 0; $revenues[$m] = 0.0;
}
$analytics = mysqli_query($conn, "SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt, COALESCE(SUM(price),0) as rev FROM shipments WHERE user_id='$userId' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY ym");
while ($a = mysqli_fetch_assoc($analytics)) {
    if (array_key_exists($a['ym'], $counts)) { $counts[$a['ym']] = (int) $a['cnt']; $revenues[$a['ym']] = (float) $a['rev']; }
}
$countsArr = array_values($counts); $revenuesArr = array_values($revenues);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

    <style>
        :root { --sidebar-width: 250px; --primary-color: #4e73df; }
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fc; overflow-x: hidden; }
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: #2c3e50; color: white; z-index: 1040; transition: all 0.3s ease; }
        .content { margin-left: var(--sidebar-width); padding: 20px; transition: all 0.3s ease; min-height: 100vh; }
        .sidebar.collapsed { left: -250px; }
        .content.expanded { margin-left: 0; }
        .clickable-row { cursor: pointer; }
        .clickable-row:hover { background-color: rgba(78, 115, 223, 0.05) !important; }
        #modalMap { height: 300px; width: 100%; border-radius: 8px; margin-bottom: 15px; z-index: 1; }
        .leaflet-routing-container { display: none !important; }
        
        /* Dark Mode */
        body.dark-mode { background-color: #1a1a2e; color: #f8f9fa; }
        body.dark-mode .sidebar { box-shadow: 2px 0 10px rgba(0,0,0,0.2); }
        body.dark-mode .card, body.dark-mode .modal-content, body.dark-mode .list-group-item { background-color: #16213e !important; color: #f8f9fa !important; border-color: #2a3a5a !important; }
        body.dark-mode table { color: white !important; }
        body.dark-mode .table-hover tbody tr:hover td { color: white; background-color: #2a3a5a; }
        body.dark-mode .bg-light { background-color: #243355 !important; color: #f8f9fa !important; border-color: #3a4b6e !important; }
        body.dark-mode .btn-close { filter: invert(1); }
    </style>
</head>
<body>
    <div class="sidebar d-flex flex-column flex-shrink-0 p-3" id="sidebar">
        <div class="text-center mb-4 mt-2">
            <img src="Remorig.png" alt="Logo" class="img-fluid mb-2" style="max-width: 140px;">
            <h6 class="fw-semibold text-uppercase text-light-50 mb-0">Core Transaction 3</h6>
        </div>
        <hr class="text-light opacity-25">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-2"><a href="user.php" class="nav-link active"><i class="bi bi-speedometer2 fs-5 me-2"></i>Dashboard</a></li>
            <li class="nav-item mb-2"><a href="bookshipment.php" class="nav-link text-white"><i class="bi bi-box-seam fs-5 me-2"></i>Book Shipment</a></li>
            <li class="nav-item mb-2"><a href="My_shipment.php" class="nav-link text-white"><i class="bi bi-truck fs-5 me-2"></i>My Shipments</a></li>
            <li class="nav-item mb-2"><a href="shiphistory.php" class="nav-link text-white"><i class="bi bi-clock-history fs-5 me-2"></i>History</a></li>
            <li class="nav-item mb-2"><a href="feedback.php" class="nav-link text-white"><i class="bi bi-chat-dots fs-5 me-2"></i>Feedback</a></li>
        </ul>
    </div>

    <div class="content" id="mainContent">
        <header class="d-flex align-items-center justify-content-between px-3 py-3 mb-4 bg-white shadow-sm rounded-4 sticky-top">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light border-0 p-2" id="hamburger"><i class="bi bi-list fs-4"></i></button>
                <div><h5 class="fw-semibold mb-0">Dashboard</h5><small class="text-muted">Welcome, <?php echo htmlspecialchars($user['username']); ?></small></div>
            </div>
            <div class="d-flex align-items-center gap-3">
                
                <div class="dropdown me-2">
                    <a href="#" class="text-dark position-relative" id="notifDropdown" data-bs-toggle="dropdown" onclick="markRead()">
                        <i class="bi bi-bell fs-4"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="display: none;">0</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 300px; max-height: 400px; overflow-y: auto;">
                        <li class="p-2 border-bottom fw-bold bg-light">Notifications</li>
                        <div id="notifList">
                            <li class="text-center p-3 text-muted small">No new notifications</li>
                        </div>
                        <li><a class="dropdown-item text-center small text-primary p-2 border-top" href="feedback.php">View All</a></li>
                    </ul>
                </div>

                <div class="form-check form-switch mb-0 ms-2">
                    <label class="form-check-label" for="userThemeToggle">🌙</label>
                    <input class="form-check-input" type="checkbox" role="switch" id="userThemeToggle">
                </div>
                <img src="<?php echo $profileImage; ?>" alt="Profile" class="rounded-circle" width="40" height="40" style="object-fit:cover;">
            </div>
        </header>

        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden position-relative" style="background-color: #2c3e50; color: white;">
            <div class="card-body p-2 p-lg-3 position-relative z-1">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h2 class="fw-bold mb-2">Track your Shipment</h2>
                        <p class="mb-4 opacity-75">Enter your tracking ID to get real-time status and AI predictions.</p>
                        <div class="input-group input-group-lg bg-white rounded-3 overflow-hidden p-1">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="number" id="quickTrackInput" class="form-control border-0 shadow-none text-dark" placeholder="Enter Tracking ID (e.g., 1024)">
                            <button class="btn btn-primary rounded-2 px-4 fw-bold" onclick="quickTrack()">Track Now</button>
                        </div>
                    </div>
                    <div class="col-lg-5 d-none d-lg-block text-end position-relative">
                        <i class="bi bi-globe-americas position-absolute" style="font-size: 10rem; color: rgba(255,255,255,0.05); bottom: -60px; right: -20px;"></i>
                    </div>
                </div>
            </div>
            <div class="position-absolute bg-white rounded-circle" style="width: 150px; height: 150px; bottom: -50px; left: -50px; opacity: 0.05;"></div>
            <div class="position-absolute bg-white rounded-circle" style="width: 200px; height: 200px; top: -80px; right: -50px; opacity: 0.05;"></div>
        </div>

       <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm border-start border-4 border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted text-uppercase fw-bold small mb-1">Processing</p>
                        <h3 class="fw-bold text-dark mb-0"><?php echo $summary['Pending']; ?></h3>
                    </div>
                    <div class="text-warning bg-warning bg-opacity-10 rounded p-2">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm border-start border-4 border-info">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted text-uppercase fw-bold small mb-1">In Transit</p>
                        <h3 class="fw-bold text-dark mb-0"><?php echo $summary['In Transit']; ?></h3>
                    </div>
                    <div class="text-info bg-info bg-opacity-10 rounded p-2">
                        <i class="bi bi-truck fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm border-start border-4 border-success">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted text-uppercase fw-bold small mb-1">Delivered</p>
                        <h3 class="fw-bold text-dark mb-0"><?php echo $summary['Delivered']; ?></h3>
                    </div>
                    <div class="text-success bg-success bg-opacity-10 rounded p-2">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm border-start border-4 border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted text-uppercase fw-bold small mb-1">Total</p>
                        <h3 class="fw-bold text-dark mb-0"><?php echo $totalCount; ?></h3>
                    </div>
                    <div class="text-primary bg-primary bg-opacity-10 rounded p-2">
                        <i class="bi bi-box-seam-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card p-4 mb-4">
                    <div class="d-flex justify-content-between mb-3"><h5 class="fw-bold">📋 Recent Shipments</h5></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>ID</th><th>Receiver</th><th>Dest.</th><th>Status</th><th>Price</th></tr></thead>
                            <tbody>
                                <?php if($shipments && mysqli_num_rows($shipments)>0): while($s=mysqli_fetch_assoc($shipments)): ?>
                                <tr class="clickable-row" onclick="openShipmentModal(<?php echo $s['id']; ?>)">
                                    <td class="text-primary fw-bold">#<?php echo $s['id']; ?></td>
                                    <td><?php echo $s['receiver_name']; ?></td>
                                    <td><?php echo substr($s['destination_address'],0,12).'...'; ?></td>
                                    <td><?php echo getStatusBadge($s['status']); ?></td>
                                    <td>₱<?php echo number_format($s['price'],0); ?></td>
                                </tr>
                                <?php endwhile; else: ?><tr><td colspan="5" class="text-center text-muted py-4">No shipments found.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6"><div class="card p-3 h-100"><h6 class="fw-bold">Volume</h6><div style="height:200px;"><canvas id="shipmentsChart"></canvas></div></div></div>
                    <div class="col-md-6"><div class="card p-3 h-100"><h6 class="fw-bold">Expenses</h6><div style="height:200px;"><canvas id="revenueChart"></canvas></div></div></div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6"><div class="card p-3 border-0 h-100 shadow-sm text-white" style="background: linear-gradient(to right, #1cc88a, #13855c);"><div><small>Total Spent</small><h3 class="fw-bold">₱<?php echo number_format($totalSpent, 2); ?></h3></div></div></div>
                    <div class="col-md-6"><div class="card p-3 border-0 h-100 shadow-sm bg-white"><div><small class="text-muted">Upcoming Payables</small><h3 class="fw-bold text-dark">₱<?php echo number_format($upcomingExpense, 2); ?></h3></div></div></div>
                </div>
            </div>

            <div class="col-lg-4">
                <?php if($hasAlert): ?>
                <div class="alert alert-danger shadow-sm mb-3">
                    <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Alert</h6>
                    <small>Shipment #<?php echo $alertData['id']; ?> was <?php echo $alertData['status']; ?>. Reason: <?php echo $alertData['feedback_text']; ?></small>
                </div>
                <?php endif; ?>

                <div class="card p-3 mb-3 border-0 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0"><i class="bi bi-list-check text-primary me-2"></i>Action Center</h6>
                        <span class="badge bg-<?php echo $toRateCount>0?'danger':'success'; ?> rounded-pill"><?php echo $toRateCount>0 ? $toRateCount.' Pending' : 'All Good'; ?></span>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php if($toRateCount > 0): while($tr = mysqli_fetch_assoc($toRateQuery)): ?>
                        <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div><small class="fw-bold d-block">Rate Shipment #<?php echo $tr['id']; ?></small></div>
                            <button class="btn btn-sm btn-warning" onclick="openShipmentModal(<?php echo $tr['id']; ?>)">Rate</button>
                        </div>
                        <?php endwhile; else: ?><div class="text-center py-3 text-muted"><small>No pending actions.</small></div><?php endif; ?>
                    </div>
                </div>

                <div class="card p-3 mb-3 border-0 shadow-sm">
                    <div class="d-flex justify-content-between mb-2"><h6 class="fw-bold">📢 AI Forecast</h6><span class="badge bg-primary rounded-pill">Gemini</span></div>
                    <div id="incomingUpdatesList" class="small">Loading...</div>
                </div>
                
                <div class="card p-3 border-0 shadow-sm bg-dark text-white text-center">
                    <h6 class="fw-bold">Need Help?</h6>
                    <a href="feedback.php" class="btn btn-sm btn-light w-100 mt-2">Contact Support</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="shipmentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 overflow-hidden shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Tracking #<span id="modalShipmentId"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-lg-7 p-4 bg-white">
                            <div id="modalMap"></div>
                            
                            <div class="d-flex justify-content-between mb-4 text-center">
                                <div><small class="text-muted fw-bold d-block">ORIGIN</small><span class="fw-bold small" id="modalSender">...</span></div>
                                <div class="text-muted"><i class="bi bi-arrow-right"></i></div>
                                <div><small class="text-muted fw-bold d-block">DESTINATION</small><span class="fw-bold small" id="modalReceiver">...</span></div>
                            </div>
                            
                            <div class="mb-3 bg-light p-3 rounded border-start border-4 border-primary">
                                <small class="text-muted fw-bold">Delivery Address</small>
                                <div class="fw-semibold text-dark" id="modalAddress">...</div>
                            </div>

                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary"><i class="bi bi-stars"></i> Gemini Prediction</h6>
                                    <p class="small fst-italic mb-2" id="modalAiText">Analyzing...</p>
                                    <div class="progress" style="height: 6px;"><div class="progress-bar bg-success" id="modalAiBar" style="width: 0%"></div></div>
                                    <div class="text-end small text-success fw-bold" id="modalAiScore">0%</div>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-6"><div class="p-2 border rounded text-center"><small>Distance</small><h6 class="mb-0 fw-bold" id="modalDistance">0 km</h6></div></div>
                                <div class="col-6"><div class="p-2 border rounded text-center"><small>Total Cost</small><h6 class="mb-0 fw-bold text-success" id="modalPrice">₱0</h6></div></div>
                            </div>
                        </div>
                        <div class="col-lg-5 bg-light p-4 border-start">
                            <h6 class="fw-bold text-secondary text-uppercase mb-4">History</h6>
                            <ul class="timeline position-relative ps-0" style="list-style: none;" id="modalTimeline"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button id="btnViewProof" class="btn btn-success d-none" onclick="viewProof()"><i class="bi bi-card-image"></i> Proof</button>
                    <button id="btnRate" class="btn btn-warning d-none" onclick="openRatingModal()"><i class="bi bi-star-fill"></i> Rate</button>
                    <button class="btn btn-dark" onclick="openWaybill()"><i class="bi bi-printer-fill"></i> Waybill</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="waybillModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-dark text-white"><h5 class="modal-title">Waybill</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body p-0"><iframe id="waybillFrame" src="" style="width: 100%; height: 80vh; border: none;"></iframe></div></div></div></div>
    
    <div class="modal fade" id="ratingModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header bg-warning"><h5 class="modal-title">Rate Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body text-center"><form id="ratingForm"><input type="hidden" name="id" id="ratingShipmentId"><input type="hidden" name="rating" id="ratingValue" value="0"><div class="display-4 text-warning mb-3"><i class="bi bi-star" onclick="setStar(1)" id="star1"></i><i class="bi bi-star" onclick="setStar(2)" id="star2"></i><i class="bi bi-star" onclick="setStar(3)" id="star3"></i><i class="bi bi-star" onclick="setStar(4)" id="star4"></i><i class="bi bi-star" onclick="setStar(5)" id="star5"></i></div><textarea class="form-control" name="feedback" placeholder="Comment" style="height: 100px"></textarea></form></div><div class="modal-footer"><button class="btn btn-primary w-100" onclick="submitRating()">Submit</button></div></div></div></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        if(typeof initDarkMode==='function')initDarkMode('userThemeToggle','userDarkMode');
        
        // Sidebar
        document.getElementById('hamburger').addEventListener('click', () => {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('mainContent');
            if(window.innerWidth > 768) { sidebar.classList.toggle('collapsed'); content.classList.toggle('expanded'); }
        });

        // Charts
        const months=<?php echo json_encode($months); ?>, counts=<?php echo json_encode($countsArr); ?>, revenues=<?php echo json_encode($revenuesArr); ?>;
        if(document.getElementById('shipmentsChart')) new Chart(document.getElementById('shipmentsChart'),{type:'bar',data:{labels:months,datasets:[{data:counts,backgroundColor:'#4e73df'}]},options:{maintainAspectRatio:false}});
        if(document.getElementById('revenueChart')) new Chart(document.getElementById('revenueChart'),{type:'line',data:{labels:months,datasets:[{data:revenues,borderColor:'#1cc88a',fill:true}]},options:{maintainAspectRatio:false}});

        // AI Updates
        fetchAiUpdates();
        function fetchAiUpdates() {
            const c = document.getElementById('incomingUpdatesList');
            const fd = new FormData(); fd.append('action', 'get_ai_updates');
            fetch('user.php', { method: 'POST', body: fd }).then(r=>r.json()).then(d=>{
                if(d.success) c.innerHTML = `<div class="d-flex gap-2"><div class="fs-4">${d.icon}</div><div><strong>Logistics Insight</strong><br>${d.message}</div></div>`;
            });
        }

        function quickTrack() { const id = document.getElementById('quickTrackInput').value; if(id) openShipmentModal(id); }

        // --- MAP & MODAL LOGIC (FIXED) ---
        let shipmentMap = null;
        let shipmentRoute = null;

        function openShipmentModal(id) {
            const m = new bootstrap.Modal(document.getElementById('shipmentDetailsModal'));
            document.getElementById('modalShipmentId').textContent = id;
            m.show(); // Show modal first

            // 1. Fetch Text Data (Immediate)
            const fd = new FormData(); fd.append('action', 'get_shipment_details'); fd.append('id', id);
            fetch('user.php', { method: 'POST', body: fd }).then(r => r.json()).then(d => {
                if (d.success) {
                    document.getElementById('modalSender').textContent = d.data.sender_name;
                    document.getElementById('modalReceiver').textContent = d.data.receiver_name;
                    document.getElementById('modalAddress').textContent = d.data.destination_address; 
                    document.getElementById('modalDistance').textContent = d.data.distance_km + ' km';
                    document.getElementById('modalPrice').textContent = '₱' + Number(d.data.price).toLocaleString();
                    
                    document.getElementById('modalAiText').innerText = d.ai_prediction.text;
                    document.getElementById('modalAiScore').innerText = d.ai_prediction.score + "%";
                    document.getElementById('modalAiBar').style.width = d.ai_prediction.score + "%";

                    const tl = document.getElementById('modalTimeline'); tl.innerHTML = '';
                    d.history.forEach((h,i) => {
                        const col = h.status==='Cancelled'?'bg-danger':(i===d.history.length-1?'bg-primary':'bg-success');
                        tl.innerHTML += `<li class="mb-4 position-relative ps-4"><div class="position-absolute top-0 start-0 translate-middle-x rounded-circle ${col}" style="width:12px;height:12px;top:5px;"></div><div><strong>${h.status}</strong><br><small class="text-muted">${h.time}</small><p class="small mb-0 text-muted">${h.desc}</p></div></li>`;
                    });

                    const btnProof = document.getElementById('btnViewProof'); const btnRate = document.getElementById('btnRate');
                    btnProof.classList.add('d-none'); btnRate.classList.add('d-none');
                    if (d.data.status === 'DELIVERED') {
                        if (d.data.proof_image) { btnProof.classList.remove('d-none'); btnProof.setAttribute('data-img', d.data.proof_image); }
                        if (d.data.rating == 0) btnRate.classList.remove('d-none');
                    }

                    // 2. Map Rendering Logic (Delayed)
                    setTimeout(() => {
                        if (!shipmentMap) {
                            shipmentMap = L.map('modalMap').setView([14.5995, 120.9842], 5);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(shipmentMap);
                        }
                        
                        // Force resize after modal is fully open
                        shipmentMap.invalidateSize();

                        // Clear previous route
                        if (shipmentRoute) { 
                            shipmentMap.removeControl(shipmentRoute); 
                            shipmentRoute = null; 
                        }

                        // Draw new route
                        if (d.data.origin_lat && d.data.origin_lng && d.data.dest_lat && d.data.dest_lng) {
                            const origin = L.latLng(d.data.origin_lat, d.data.origin_lng);
                            const dest = L.latLng(d.data.dest_lat, d.data.dest_lng);
                            shipmentRoute = L.Routing.control({
                                waypoints: [origin, dest],
                                routeWhileDragging: false, addWaypoints: false, fitSelectedRoutes: true, show: false,
                                lineOptions: { styles: [{ color: '#4e73df', opacity: 0.8, weight: 6 }] },
                                createMarker: function(i, wp) {
                                    const col = i===0?'#198754':'#dc3545';
                                    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="${col}" width="40" height="40"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5" fill="white"/></svg>`;
                                    return L.marker(wp.latLng, { icon: L.divIcon({ className: 'custom-pin', html: svg, iconSize: [40, 40], iconAnchor: [20, 40] }) });
                                }
                            }).addTo(shipmentMap);
                        } else {
                            shipmentMap.setView([14.5995, 120.9842], 5); // Default view
                        }
                    }, 500); // 500ms delay to wait for modal slide
                }
            });
        }

        function openWaybill() {
            const id = document.getElementById('modalShipmentId').textContent;
            if(id) { document.getElementById('waybillFrame').src = 'print_invoice.php?id=' + id; new bootstrap.Modal(document.getElementById('waybillModal')).show(); }
        }

        function viewProof() { const img = document.getElementById('btnViewProof').getAttribute('data-img'); if(img) window.open('uploads/'+img, '_blank'); }

        function openRatingModal() {
            document.getElementById('ratingShipmentId').value = document.getElementById('modalShipmentId').textContent;
            bootstrap.Modal.getInstance(document.getElementById('shipmentDetailsModal')).hide();
            new bootstrap.Modal(document.getElementById('ratingModal')).show();
        }
        function setStar(n) { document.getElementById('ratingValue').value=n; for(let i=1;i<=5;i++) document.getElementById('star'+i).className = i<=n?'bi bi-star-fill':'bi bi-star'; }
        function submitRating() {
            const fd = new FormData(document.getElementById('ratingForm')); fd.append('action','submit_rating');
            fetch('user.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{ alert(d.message); location.reload(); });
        }

        // --- NOTIFICATION LOGIC ---
        function fetchNotifications() {
            fetch('api/get_notifications.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('notifBadge');
                const list = document.getElementById('notifList');

                if (data.count > 0) {
                    badge.innerText = data.count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }

                let html = '';
                if (data.data.length > 0) {
                    data.data.forEach(notif => {
                        let bgClass = notif.is_read == 0 ? 'bg-light' : '';
                        let icon = notif.is_read == 0 ? 'bi-circle-fill text-primary' : 'bi-check-circle text-muted';
                        
                        html += `
                        <li>
                            <a class="dropdown-item ${bgClass} p-2 border-bottom" href="${notif.link}">
                                <div class="d-flex align-items-start">
                                    <i class="bi ${icon} me-2 mt-1" style="font-size: 10px;"></i>
                                    <div>
                                        <small class="fw-bold d-block">${notif.title}</small>
                                        <small class="text-muted text-wrap">${notif.message}</small>
                                        <br>
                                        <small class="text-secondary" style="font-size: 0.7rem;">${new Date(notif.created_at).toLocaleString()}</small>
                                    </div>
                                </div>
                            </a>
                        </li>`;
                    });
                } else {
                    html = '<li class="text-center p-3 text-muted small">No notifications</li>';
                }
                list.innerHTML = html;
            });
        }

        function markRead() {
            fetch('api/get_notifications.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=read_all'
            }).then(() => {
                document.getElementById('notifBadge').style.display = 'none';
            });
        }

        fetchNotifications();
        setInterval(fetchNotifications, 5000); // Check every 5 seconds
    </script>
</body>
</html>