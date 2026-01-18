<?php
ob_start(); 
// user.php (Freight User Dashboard)
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
    
    // Select ALL columns (kasama rating, feedback, proof_image)
    $query = mysqli_query($conn, "SELECT * FROM shipments WHERE id='$shipmentId'");
    $shipment = mysqli_fetch_assoc($query);

    if (!$shipment) { echo json_encode(['success' => false, 'message' => 'Shipment not found']); exit; }

    $history = [];
    $createdAt = strtotime($shipment['created_at']);
    $history[] = ['status' => 'Booking Created', 'time' => date('M d, Y g:i A', $createdAt), 'desc' => 'Shipment details received.', 'completed' => true];

    if ($shipment['status'] == 'Cancelled') {
        $history[] = ['status' => 'Cancelled', 'time' => date('M d, Y g:i A', $createdAt + 600), 'desc' => 'Reason: ' . ($shipment['feedback_text'] ?? 'N/A'), 'completed' => true];
    } else {
        if ($shipment['status'] == 'In Transit' || $shipment['status'] == 'Delivered') {
            $history[] = ['status' => 'Picked Up', 'time' => date('M d, Y g:i A', $createdAt + 3600), 'desc' => 'Package picked up.', 'completed' => true];
        }
        if ($shipment['status'] == 'Delivered') {
            $delTime = !empty($shipment['updated_at']) ? strtotime($shipment['updated_at']) : ($createdAt + 18000);
            $history[] = ['status' => 'Delivered', 'time' => date('M d, Y g:i A', $delTime), 'desc' => 'Package delivered successfully.', 'completed' => true];
        }
    }

    $prediction = "Standard routing applied.";
    $confidence = 85;
    
    // AI Prediction Logic (Simplified)
    if ($shipment['status'] == 'Delivered') { $prediction = "Completed successfully."; $confidence = 100; }
    elseif ($shipment['status'] == 'Cancelled') { $prediction = "Cancelled."; $confidence = 0; }
    else {
        if(!empty($shipment['ai_estimated_time'])) {
            $prediction = $shipment['ai_estimated_time'];
        }
    }

    echo json_encode(['success' => true, 'data' => $shipment, 'history' => $history, 'ai_prediction' => ['text' => $prediction, 'score' => $confidence]]);
    exit;
}

// --- B. GET AI UPDATES (WEATHER/TRAFFIC) ---
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_ai_updates') {
    
    ob_clean(); 
    header('Content-Type: application/json');

    date_default_timezone_set('Asia/Manila');
    $currentDate = date("l, F j, Y"); 
    $currentTime = date("g:i A");      

    // ⚠️ ILAGAY ANG API KEY MO DITO
    $apiKey = "AIzaSyBIEaGsPg86hYn6g7dgkw8zQcdQa5GtgS8-"; 

    $prompt = "
    You are a weather AI specialist for the Philippines.
    Current Context: It is $currentTime on a $currentDate.
    TASK: Generate a realistic weather forecast summary covering Luzon, Visayas, and Mindanao.
    Make it professional and concise (Max 25 words).
    IMPORTANT: Return raw JSON only.
    Format: { \"message\": \"The weather summary text\", \"type\": \"weather\" }
    ";

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;    
    
    $data = [ "contents" => [ [ "parts" => [ ["text" => $prompt] ] ] ] ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    if(curl_errno($ch)) { echo json_encode(['success' => true, 'message' => 'CURL Error', 'icon' => '❌', 'color' => 'text-danger', 'timestamp' => $currentTime]); exit; }
    curl_close($ch);

    $decoded = json_decode($response, true);
    
    if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
        $rawText = $decoded['candidates'][0]['content']['parts'][0]['text'];
        $startIndex = strpos($rawText, '{');
        $endIndex = strrpos($rawText, '}');
        $aiData = null;
        if ($startIndex !== false && $endIndex !== false) {
            $cleanJson = substr($rawText, $startIndex, $endIndex - $startIndex + 1);
            $aiData = json_decode($cleanJson, true);
        }

        if ($aiData) {
            $icon = '📢'; $color = 'text-primary';
            if(($aiData['type']??'') == 'traffic') { $icon = '🚦'; $color = 'text-warning'; }
            elseif(($aiData['type']??'') == 'weather') { $icon = '🌦️'; $color = 'text-info'; }
            echo json_encode(['success' => true, 'message' => $aiData['message'] ?? "System normal.", 'icon' => $icon, 'color' => $color, 'timestamp' => $currentTime]);
        } else {
            echo json_encode(['success' => true, 'message' => substr(strip_tags($rawText), 0, 80) . "...", 'icon' => "🌤️", 'color' => "text-info", 'timestamp' => $currentTime]);
        }
    } else {
        echo json_encode(['success' => true, 'message' => "AI is busy.", 'icon' => "📡", 'color' => "text-muted", 'timestamp' => $currentTime]);
    }
    exit;
}

// --- C. SUBMIT RATING API ---
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_rating') {
    ob_clean();
    header('Content-Type: application/json');

    $id = intval($_POST['id']);
    $rating = intval($_POST['rating']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);

    // Update DB
    $sql = "UPDATE shipments SET rating='$rating', feedback_text='$feedback' WHERE id='$id'";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true, 'message' => 'Thank you for your feedback!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
    exit;
}

// =================================================================================
// PAGE CONTENT
// =================================================================================
include("darkmode.php");
include('loading.html');

$username = $_SESSION['email']; 
$userQuery = mysqli_query($conn, "SELECT * FROM accounts WHERE email='" . mysqli_real_escape_string($conn, $username) . "'");
$user = mysqli_fetch_assoc($userQuery);
$userId = $user['id'];
$profileImage = !empty($user['profile_image']) ? $user['profile_image'] : 'user.png';

function qfetch_all($conn, $sql) { $res = @mysqli_query($conn, $sql); if (!$res) return []; $out = []; while ($r = mysqli_fetch_assoc($res)) $out[] = $r; return $out; }

$summary = ['Pending' => 0, 'In Transit' => 0, 'Delivered' => 0];
$summaryQuery = mysqli_query($conn, "SELECT status, COUNT(*) as total FROM shipments WHERE user_id='" . intval($userId) . "' GROUP BY status");
if ($summaryQuery) { while ($row = mysqli_fetch_assoc($summaryQuery)) $summary[$row['status']] = $row['total']; }

$shipments = mysqli_query($conn, "SELECT * FROM shipments WHERE user_id='" . intval($userId) . "' ORDER BY created_at DESC LIMIT 8");
$months = []; $counts = []; $revenues = [];
$analytics = qfetch_all($conn, "SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt, COALESCE(SUM(price),0) as rev FROM shipments WHERE user_id='" . intval($userId) . "' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY ym ORDER BY ym ASC");
for ($i = 5; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-{$i} months"));
    $months[] = date('M Y', strtotime($m . '-01'));
    $counts[$m] = 0; $revenues[$m] = 0.0;
}
foreach ($analytics as $a) { if (array_key_exists($a['ym'], $counts)) { $counts[$a['ym']] = (int) $a['cnt']; $revenues[$a['ym']] = (float) $a['rev']; } }
$countsArr = array_values($counts); $revenuesArr = array_values($revenues);
$promos = [['code' => 'PROMO10', 'desc' => 'Get 10% off'], ['code' => 'SHIPFREE', 'desc' => '₱0 delivery fee < 10km'], ['code' => 'XPRESS20', 'desc' => '20% off express']];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-width: 250px; --primary-color: #4e73df; --dark-bg: #1a1a2e; }
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fc; color: #212529; overflow-x: hidden; }
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: #2c3e50; color: white; z-index: 1040; transition: all 0.3s ease; }
        .content { margin-left: var(--sidebar-width); padding: 20px; transition: all 0.3s ease; min-height: 100vh; }
        .sidebar.collapsed { left: -250px; }
        .content.expanded { margin-left: 0; }
        @media (max-width: 768px) {
            .sidebar { left: -250px; }
            .content { margin-left: 0 !important; }
            .sidebar.mobile-open { left: 0; }
        }
        .sidebar a { color: rgba(255, 255, 255, 0.8); text-decoration: none; padding: .75rem 1.5rem; display: block; border-left: 3px solid transparent; }
        .sidebar a:hover, .sidebar a.active { background-color: rgba(255, 255, 255, 0.1); color: white; border-left: 3px solid white; }
        .card { border: none; border-radius: .35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); }
        .clickable-row { cursor: pointer; transition: background-color 0.2s; }
        .clickable-row:hover { background-color: rgba(78, 115, 223, 0.1) !important; }
        .timeline { border-left: 2px solid #e9ecef; padding-left: 20px; margin-left: 10px; list-style: none; }
        .timeline-item { position: relative; margin-bottom: 20px; }
        .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.5); z-index: 1030; display: none; }
        .sidebar-overlay.show { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 0.5s ease-out forwards; }
        body.dark-mode { background-color: var(--dark-bg); color: #f8f9fa; }
        body.dark-mode .sidebar { box-shadow: 2px 0 10px rgba(0,0,0,0.2); }
        body.dark-mode .header, body.dark-mode .card, body.dark-mode .modal-content { background-color: #16213e !important; color: #f8f9fa !important; border: 1px solid #2a3a5a !important; }
        body.dark-mode table { background-color: #1b2432 !important; color: #f8f9fa !important; border-color: #2a3a5a !important; }
        body.dark-mode th { background-color: #3a4b6e !important; color: #fff !important; }
        body.dark-mode td { border-bottom: 1px solid #2a3a5a !important; color: #e1e5ee !important; }
        body.dark-mode .bg-light { background-color: #243355 !important; color: #f8f9fa; border-color: #3a4b6e !important; }
        body.dark-mode .btn-close { filter: invert(1); }
        .timeline-item::before { display: none !important; }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="sidebar d-flex flex-column flex-shrink-0 p-3" id="sidebar">
        <div class="text-center mb-4 mt-2">
            <img src="Remorig.png" alt="Logo" class="img-fluid mb-2" style="max-width: 140px;">
            <h6 class="fw-semibold text-uppercase text-light-50 mb-0" style="font-size: 0.85rem;">Core Transaction 3</h6>
        </div>
        <hr class="text-light opacity-25">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-2"><a href="user.php" class="nav-link active"><i class="bi bi-house-door-fill fs-5 me-2"></i>Dashboard</a></li>
            <li class="nav-item mb-2"><a href="bookshipment.php" class="nav-link"><i class="bi bi-truck fs-5 me-2"></i>Book Shipment</a></li>
            <li class="nav-item mb-2"><a href="My_shipment.php" class="nav-link text-white"><i class="bi bi-truck fs-5 me-2"></i>My Shipments</a></li>
            <li class="nav-item mb-2"><a href="shiphistory.php" class="nav-link"><i class="bi bi-clock-history fs-5 me-2"></i>History</a></li>
            <li class="nav-item mb-2"><a href="feedback.php" class="nav-link"><i class="bi bi-chat-dots fs-5 me-2"></i>Feedback</a></li>
        </ul>
    </div>

    <div class="content" id="mainContent">
        <header class="header d-flex align-items-center justify-content-between px-3 py-3 mb-4 bg-white shadow-sm rounded-3 sticky-top">
            <div class="d-flex align-items-center gap-3">
                
                <button class="btn btn-light border-0 p-2" id="hamburger"><i class="bi bi-list fs-4"></i></button>
                <div><h5 class="fw-semibold mb-0">Dashboard</h5><small class="text-muted d-none d-sm-block">Welcome, <?php echo htmlspecialchars($user['fullname'] ?? $username); ?></small></div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="dropdown me-3">
    <a href="#" class="text-dark position-relative" id="notifDropdown" data-bs-toggle="dropdown" onclick="markRead()">
        <i class="bi bi-bell fs-4"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="display: none;">
            0
        </span>
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
                    <label class="form-check-label d-none d-sm-inline" for="userThemeToggle">🌙</label>
                    <input class="form-check-input" type="checkbox" role="switch" id="userThemeToggle">
                </div>
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
            </div>
        </header>

        <div class="row g-3">
            <div class="col-6 col-md-3"><div class="card p-3 text-center border-top border-5 border-warning h-100"><h6>🚚 Pending</h6><h2><?php echo $summary['Pending']; ?></h2></div></div>
            <div class="col-6 col-md-3"><div class="card p-3 text-center border-top border-5 border-info h-100"><h6>📦 In Transit</h6><h2><?php echo $summary['In Transit']; ?></h2></div></div>
            <div class="col-6 col-md-3"><div class="card p-3 text-center border-top border-5 border-success h-100"><h6>✅ Delivered</h6><h2><?php echo $summary['Delivered']; ?></h2></div></div>
            <div class="col-6 col-md-3"><div class="card p-3 text-center border-top border-5 border-primary h-100"><h6>📊 Total</h6><h2><?php $t=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM shipments WHERE user_id='$userId'")); echo $t['c']; ?></h2></div></div>
        </div>

        <div class="row mt-4 g-4">
            <div class="col-lg-8">
                <div class="card p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3"><h5>📋 Recent Shipments</h5><a href="shiphistory.php" class="small-muted">View All</a></div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead><tr><th>ID</th><th>Receiver</th><th>Address</th><th>Status</th><th>Price</th></tr></thead>
                            <tbody>
                                <?php if($shipments && mysqli_num_rows($shipments)>0): while($s=mysqli_fetch_assoc($shipments)): 
                                    $st=$s['status']; $cls='bg-secondary';
                                    if($st=='Pending')$cls='bg-warning text-dark'; elseif($st=='In Transit')$cls='bg-info text-white'; elseif($st=='Delivered')$cls='bg-success'; elseif($st=='Cancelled')$cls='bg-danger';
                                ?>
                                <tr class="clickable-row" onclick="openShipmentModal(<?php echo $s['id']; ?>)">
                                    <td>#<?php echo $s['id']; ?></td>
                                    <td><?php echo $s['receiver_name']; ?></td>
                                    <td><?php echo substr($s['destination_address'],0,15).'...'; ?></td>
                                    <td><span class="badge <?php echo $cls; ?>"><?php echo $st; ?></span></td>
                                    <td>₱<?php echo number_format($s['price'],0); ?></td>
                                </tr>
                                <?php endwhile; else: ?><tr><td colspan="5" class="text-center text-muted">No shipments</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6"><div style="height:150px;"><canvas id="shipmentsChart"></canvas></div></div>
                        <div class="col-md-6"><div style="height:150px;"><canvas id="revenueChart"></canvas></div></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center"><h5>📢 Incoming Updates</h5><span class="badge bg-primary rounded-pill">AI</span></div>
                    <div id="incomingUpdatesList" style="max-height: 250px; overflow-y:auto;">
                        <div class="text-center small-muted mt-2">No new updates.</div>
                    </div>
                </div>
                <div class="card p-3">
                    <h5>🎁 Promos</h5>
                    <?php foreach($promos as $p): ?>
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div><strong class="text-primary"><?php echo $p['code']; ?></strong><br><small class="text-muted"><?php echo $p['desc']; ?></small></div>
                        <button class="btn btn-sm btn-outline-primary">Use</button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="shipmentDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 overflow-hidden shadow-lg">
                <div class="modal-header bg-primary text-white py-3">
                    <div>
                        <span class="badge bg-white text-primary mb-1 text-uppercase fw-bold" style="font-size: 0.7rem;">Shipment Details</span>
                        <h5 class="modal-title fw-bold" style="letter-spacing: 0.5px;">Tracking #<span id="modalShipmentId"></span></h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-lg-7 p-4 bg-white">
                            <div class="d-flex align-items-center justify-content-between mb-4 position-relative px-2">
                                <div class="text-center z-1">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 text-primary border" style="width:50px; height:50px;"><i class="bi bi-geo-alt-fill fs-4"></i></div>
                                    <small class="text-muted fw-bold d-block" style="font-size: 0.75rem;">ORIGIN</small>
                                    <span class="fw-bold text-dark small d-block" id="modalSender">...</span>
                                    <small class="text-secondary" style="font-size: 0.7rem;"><i class="bi bi-telephone"></i> <span id="modalSenderContact"></span></small>
                                </div>
                                <div class="flex-grow-1 border-top border-2 border-secondary border-opacity-25 position-absolute start-0 end-0 top-50 translate-middle-y" style="z-index: 0; margin: 0 60px; border-style: dashed !important;"></div>
                                <div class="position-absolute top-50 start-50 translate-middle bg-white px-2 text-muted z-1"><i class="bi bi-arrow-right-circle-fill text-primary"></i></div>
                                <div class="text-center z-1">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 text-danger border" style="width:50px; height:50px;"><i class="bi bi-geo-fill fs-4"></i></div>
                                    <small class="text-muted fw-bold d-block" style="font-size: 0.75rem;">DESTINATION</small>
                                    <span class="fw-bold text-dark small d-block" id="modalReceiver">...</span>
                                    <small class="text-secondary" style="font-size: 0.7rem;"><i class="bi bi-telephone"></i> <span id="modalReceiverContact"></span></small>
                                </div>
                            </div>
                            <div class="mb-4 bg-light p-3 rounded-3 border-start border-4 border-primary">
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Delivery Address</small>
                                <div class="fw-semibold text-dark" id="modalAddress">...</div>
                            </div>
                            <div class="card border-0 shadow-sm" style="background: linear-gradient(145deg, #f0f4ff 0%, #ffffff 100%);">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-stars text-warning me-1"></i> Gemini Logistics Forecast</h6>
                                        <span class="badge bg-primary rounded-pill">AI Beta</span>
                                    </div>
                                    <p class="text-dark small mb-3 fst-italic" id="modalAiText">Analyzing route conditions...</p>
                                    <div class="d-flex align-items-center gap-2">
                                        <small class="fw-bold text-muted">Confidence:</small>
                                        <div class="progress flex-grow-1" style="height: 10px; border-radius: 10px; background-color: #e9ecef;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="modalAiBar" style="width: 0%"></div>
                                        </div>
                                        <span class="fw-bold text-success small" id="modalAiScore">0%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2 mt-3">
                                <div class="col-6"><div class="p-2 border rounded text-center"><small class="text-muted text-uppercase" style="font-size: 0.65rem;">Distance</small><h6 class="mb-0 fw-bold"><i class="bi bi-signpost-2 me-1"></i><span id="modalDistance">0 km</span></h6></div></div>
                                <div class="col-6"><div class="p-2 border rounded text-center"><small class="text-muted text-uppercase" style="font-size: 0.65rem;">Total Cost</small><h6 class="mb-0 fw-bold text-success"><span id="modalPrice">₱0</span></h6></div></div>
                            </div>
                        </div>
                        <div class="col-lg-5 bg-light p-4 border-start">
                            <h6 class="fw-bold text-secondary text-uppercase mb-4" style="font-size: 0.8rem; letter-spacing: 1px;">Shipment History</h6>
                            <ul class="timeline position-relative ps-0" style="list-style: none;" id="modalTimeline"></ul>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button id="btnViewProof" class="btn btn-success d-none" onclick="viewProof()">
                            <i class="bi bi-card-image me-1"></i> View Proof
                        </button>
                        <button id="btnRate" class="btn btn-warning d-none text-dark" onclick="openRatingModal()">
                            <i class="bi bi-star-fill me-1"></i> Rate Service
                        </button>
                        <button class="btn btn-dark" onclick="openWaybill()">
                            <i class="bi bi-printer-fill me-1"></i> Print Waybill
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ratingModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="bi bi-star-fill"></i> Rate Our Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <form id="ratingForm">
                        <input type="hidden" name="id" id="ratingShipmentId">
                        <input type="hidden" name="rating" id="ratingValue" value="0">
                        <p class="mb-2">How was your delivery experience?</p>
                        <div class="display-4 text-warning mb-3" style="cursor: pointer;">
                            <i class="bi bi-star" onclick="setStar(1)" id="star1"></i>
                            <i class="bi bi-star" onclick="setStar(2)" id="star2"></i>
                            <i class="bi bi-star" onclick="setStar(3)" id="star3"></i>
                            <i class="bi bi-star" onclick="setStar(4)" id="star4"></i>
                            <i class="bi bi-star" onclick="setStar(5)" id="star5"></i>
                        </div>
                        <div class="form-floating">
                            <textarea class="form-control" name="feedback" placeholder="Leave a comment here" style="height: 100px"></textarea>
                            <label>Feedback (Optional)</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary w-100" onclick="submitRating()">Submit Review</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        if(typeof initDarkMode==='function')initDarkMode('userThemeToggle','userDarkMode');
        
        document.getElementById('hamburger').addEventListener('click', () => {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('mainContent');
            const overlay = document.getElementById('sidebarOverlay');
            if (window.innerWidth > 768) {
                sidebar.classList.toggle('collapsed');
                content.classList.toggle('expanded');
            } else {
                sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('show');
            }
        });
        document.getElementById('sidebarOverlay').addEventListener('click', () => {
            document.getElementById('sidebar').classList.remove('mobile-open');
            document.getElementById('sidebarOverlay').classList.remove('show');
        });

        const months=<?php echo json_encode($months); ?>, counts=<?php echo json_encode($countsArr); ?>, revenues=<?php echo json_encode($revenuesArr); ?>;
        if(document.getElementById('shipmentsChart')) new Chart(document.getElementById('shipmentsChart'),{type:'bar',data:{labels:months,datasets:[{data:counts,backgroundColor:'#4e73df'}]},options:{plugins:{legend:{display:false}},maintainAspectRatio:false}});
        if(document.getElementById('revenueChart')) new Chart(document.getElementById('revenueChart'),{type:'line',data:{labels:months,datasets:[{data:revenues,borderColor:'#1cc88a',fill:true,backgroundColor:'rgba(28,200,138,0.1)'}]},options:{plugins:{legend:{display:false}},maintainAspectRatio:false}});

        document.addEventListener("DOMContentLoaded", fetchAiUpdates);
        function fetchAiUpdates() {
            const c = document.getElementById('incomingUpdatesList');
            c.innerHTML = '<div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm me-2"></div><small>AI Analyzing...</small></div>';
            const fd = new FormData(); fd.append('action', 'get_ai_updates');
            fetch('user.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if(d.success) {
                    c.innerHTML = `<div class="d-flex gap-3 border-bottom py-2 fade-in"><div class="fs-4">${d.icon}</div><div><h6 class="mb-1 fw-bold ${d.color}">Logistics Insight</h6><p class="mb-1 small text-dark">${d.message}</p><small class="text-muted" style="font-size:0.7rem">As of ${d.timestamp}</small></div></div><div class="text-center mt-1"><small class="text-muted" style="font-size:0.65rem">Powered by Gemini AI</small></div>`;
                } else { c.innerHTML = `<div class="text-center small text-danger mt-2">${d.message}</div>`; }
            })
            .catch(e => { c.innerHTML = '<div class="text-center small text-danger mt-2">AI Unavailable</div>'; });
        }

       function openShipmentModal(id) {
        const modalElement = document.getElementById('shipmentDetailsModal');
        const m = new bootstrap.Modal(modalElement);
        document.getElementById('modalShipmentId').textContent = id;
        document.getElementById('modalAiText').textContent = "Consulting Gemini AI...";
        document.getElementById('modalAiBar').style.width = "0%";
        document.getElementById('modalTimeline').innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
        
        // Reset placeholders
        document.getElementById('modalSenderContact').textContent = '';
        document.getElementById('modalReceiverContact').textContent = '';

        m.show();
        const fd = new FormData(); fd.append('action', 'get_shipment_details'); fd.append('id', id);
        fetch('user.php', { method: 'POST', body: fd }).then(r => r.json()).then(d => {
            if (d.success) {
                document.getElementById('modalSender').textContent = d.data.sender_name || 'N/A';
                document.getElementById('modalReceiver').textContent = d.data.receiver_name;
                document.getElementById('modalAddress').textContent = d.data.destination_address; 
                document.getElementById('modalDistance').textContent = d.data.distance_km + ' km';
                document.getElementById('modalPrice').textContent = '₱' + Number(d.data.price).toLocaleString();
                document.getElementById('modalSenderContact').textContent = d.data.sender_contact || 'No contact';
                document.getElementById('modalReceiverContact').textContent = d.data.receiver_contact || 'No contact';

                const aiText = d.ai_prediction.text || "No prediction available.";
                const score = d.ai_prediction.score || 0;
                document.getElementById('modalAiText').innerHTML = `"${aiText}"`;
                document.getElementById('modalAiScore').textContent = score + '%';
                const bar = document.getElementById('modalAiBar');
                bar.style.width = score + '%';
                bar.classList.remove('bg-success', 'bg-warning', 'bg-danger');
                if(score >= 80) bar.classList.add('bg-success'); else if(score >= 50) bar.classList.add('bg-warning'); else bar.classList.add('bg-danger');
                
                const tl = document.getElementById('modalTimeline'); tl.innerHTML = '';
                d.history.forEach((h, index) => {
                    const isLatest = index === d.history.length - 1;
                    const circleColor = h.status === 'Cancelled' ? 'bg-danger' : (isLatest ? 'bg-primary' : 'bg-success');
                    const textColor = isLatest ? 'text-primary fw-bold' : 'text-dark fw-semibold';
                    tl.innerHTML += `<li class="timeline-item mb-4 position-relative ps-4"><div class="position-absolute top-0 start-0 translate-middle-x rounded-circle border border-2 border-white shadow-sm ${circleColor}" style="width: 14px; height: 14px; left: 0px; top: 5px; z-index:2;"></div><div><div class="${textColor}" style="font-size: 0.9rem;">${h.status}</div><div class="text-muted small mb-1" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i>${h.time}</div><div class="p-2 bg-white border rounded small text-secondary shadow-sm">${h.desc}</div></div></li>`;
                });

                // --- PROOF & RATING LOGIC ---
                const btnProof = document.getElementById('btnViewProof');
                const btnRate = document.getElementById('btnRate');
                
                // Defaults
                btnProof.classList.add('d-none');
                btnRate.classList.add('d-none');
                btnProof.setAttribute('data-img', '');

                if (d.data.status === 'Delivered') {
                    // Show Proof if available
                    if (d.data.proof_image && d.data.proof_image !== "") {
                        btnProof.classList.remove('d-none');
                        btnProof.setAttribute('data-img', d.data.proof_image);
                    }
                    // Show Rate if not yet rated
                    if (d.data.rating == 0) {
                        btnRate.classList.remove('d-none');
                    }
                }
            }
        }).catch(err => { document.getElementById('modalAiText').textContent = "Error fetching details."; });
    }

    function openWaybill() {
        const id = document.getElementById('modalShipmentId').textContent;
        if(id && id !== '...') window.open('waybill.php?id=' + id, '_blank');
        else alert("Wait for details to load...");
    }

    function viewProof() {
        const btn = document.getElementById('btnViewProof');
        const imgName = btn.getAttribute('data-img');
        if (imgName) window.open('uploads/' + imgName, '_blank');
        else alert("No proof image found.");
    }

    // --- RATING FUNCTIONS ---
    function openRatingModal() {
        const id = document.getElementById('modalShipmentId').textContent;
        document.getElementById('ratingShipmentId').value = id;
        bootstrap.Modal.getInstance(document.getElementById('shipmentDetailsModal')).hide();
        new bootstrap.Modal(document.getElementById('ratingModal')).show();
        setStar(0);
    }

    function setStar(n) {
        document.getElementById('ratingValue').value = n;
        for (let i = 1; i <= 5; i++) {
            const icon = document.getElementById('star' + i);
            if (i <= n) { icon.classList.remove('bi-star'); icon.classList.add('bi-star-fill'); } 
            else { icon.classList.remove('bi-star-fill'); icon.classList.add('bi-star'); }
        }
    }

    function submitRating() {
        const rateVal = document.getElementById('ratingValue').value;
        if(rateVal == 0) { alert("Please click a star to rate!"); return; }
        const fd = new FormData(document.getElementById('ratingForm'));
        fd.append('action', 'submit_rating');
        fetch('user.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if(d.success) { alert(d.message); location.reload(); }
            else { alert(d.message); }
        });
    }

    
    </script>

    <script>
    // AUTO-CHECK NOTIFICATIONS EVERY 5 SECONDS
    function fetchNotifications() {
        fetch('api/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            const list = document.getElementById('notifList');

            // 1. Update Badge Count
            if (data.count > 0) {
                badge.innerText = data.count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }

            // 2. Update Dropdown List
            let html = '';
            if (data.data.length > 0) {
                data.data.forEach(notif => {
                    // Check if read or unread styling
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

    // Mark as Read when clicked
    function markRead() {
        fetch('api/get_notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=read_all'
        }).then(() => {
            document.getElementById('notifBadge').style.display = 'none';
        });
    }

    // Initial Call + Interval
    fetchNotifications();
    setInterval(fetchNotifications, 5000); // Check every 5 seconds
</script>
</body>
</html>