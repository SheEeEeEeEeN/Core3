<?php
include("darkmode.php");
include("connection.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('loading.html');

// =================================================================================
// 1. API HANDLER (Internal)
// =================================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_shipment_details') {

    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');

    $shipmentId = intval($_POST['id']);

    $query = mysqli_query($conn, "SELECT * FROM shipments WHERE id='$shipmentId'");
    $shipment = mysqli_fetch_assoc($query);

    if (!$shipment) {
        echo json_encode(['success' => false, 'message' => 'Shipment not found']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => $shipment
    ]);
    exit;
}

// =================================================================================
// 2. PAGE CONTENT
// =================================================================================

$user_id = $_SESSION['user_id'] ?? 0;
$result = $conn->query("SELECT * FROM shipments WHERE user_id = '$user_id' ORDER BY created_at DESC");

$username = $_SESSION['email'] ?? '';
$query = mysqli_query($conn, "SELECT * FROM accounts WHERE email = '$username'");
$user = mysqli_fetch_assoc($query);
$profileImage = !empty($user['profile_image']) ? $user['profile_image'] : 'user.png';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | Shipment History</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <style>
        :root {
            --primary-color: #4e73df;
            --dark-bg: #1a1a2e;
            --dark-card: #16213e;
            --text-light: #f8f9fa;
            --text-dark: #212529;
            --shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: #f8f9fc;
            color: var(--text-dark);
        }

        body.dark-mode {
            background-color: var(--dark-bg);
            color: var(--text-light);
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
            z-index: 1000;
            transition: all .3s;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar a {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            padding: .75rem 1.5rem;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: .3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 3px solid white;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            transition: all .3s;
        }

        .content.expanded {
            margin-left: 0;
        }

        .header {
            background-color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        /* Timeline CSS */
        .track-line {
            height: 4px;
            background-color: #e9ecef;
            width: 100%;
            position: absolute;
            top: 15px;
            z-index: 0;
        }

        .step-item {
            position: relative;
            z-index: 1;
            text-align: center;
            width: 33.33%;
        }

        .step-icon {
            width: 35px;
            height: 35px;
            background: #fff;
            border: 3px solid #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
            color: #adb5bd;
            transition: all 0.3s;
        }

        .step-item.active .step-icon {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
            box-shadow: 0 0 10px rgba(78, 115, 223, 0.5);
        }

        .step-item.active .step-text {
            color: var(--primary-color);
            font-weight: 700;
        }

        .step-text {
            font-size: 0.8rem;
            color: #6c757d;
        }

        /* Dark Mode Overrides */
        body.dark-mode .header {
            background-color: var(--dark-card) !important;
            color: var(--text-light) !important;
        }

        body.dark-mode .card {
            background-color: var(--dark-card) !important;
            color: var(--text-light) !important;
            border-color: #2a3a5a !important;
        }

        body.dark-mode table,
        body.dark-mode tbody tr {
            background-color: var(--dark-card) !important;
            color: var(--text-light) !important;
        }

        body.dark-mode table tbody tr:hover {
            background-color: #2a3a5a !important;
        }

        body.dark-mode .modal-content {
            background-color: var(--dark-card);
            color: var(--text-light);
            border: 1px solid #2a3a5a;
        }

        body.dark-mode .bg-light {
            background-color: #243355 !important;
            color: white;
            border-color: #3a4b6e !important;
        }

        body.dark-mode .step-icon {
            background-color: #2c3e50;
            border-color: #495057;
        }

        body.dark-mode .track-line {
            background-color: #495057;
        }

        body.dark-mode .btn-close {
            filter: invert(1);
        }

        .clickable-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .clickable-row:hover {
            background-color: rgba(78, 115, 223, 0.1) !important;
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
            <li class="nav-item mb-2"><a href="user.php" class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 hover-link"><i class="bi bi-house-door-fill fs-5"></i><span>Dashboard</span></a></li>
            <li class="nav-item mb-2"><a href="bookshipment.php" class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 hover-link"><i class="bi bi-truck fs-5"></i><span>Book Shipment</span></a></li>
            <li class="nav-item mb-2"><a href="My_shipment.php" class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 hover-link"><i class="bi bi-truck fs-5"></i><span>My Shipments</span></a></li>
            <li class="nav-item mb-2"><a href="shiphistory.php" class="active nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 hover-link"><i class="bi bi-clock-history fs-5"></i><span>Shipment History</span></a></li>
            <li class="nav-item mb-2"><a href="feedback.php" class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 hover-link"><i class="bi bi-chat-dots fs-5"></i><span>Feedback & Notification</span></a></li>
        </ul>
    </div>

    <div class="content" id="mainContent">
        <header class="header d-flex align-items-center justify-content-between px-4 py-3 mb-4 bg-white shadow-sm rounded-3 sticky-top">
            <div class="d-flex align-items-center gap-3">
                <button class="hamburger btn btn-light border-0 p-2" id="hamburger"><i class="bi bi-list fs-4"></i></button>
                <div>
                    <h5 class="fw-semibold mb-0">Shipment History</h5>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
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
                <div class="dropdown">

                    <a href="#" class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="<?php echo $profileImage; ?>" alt="Profile" class="rounded-circle" width="40" height="40" style="object-fit:cover;">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="user-profile.php">Profile</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </div>
                <div class="form-check form-switch mb-0">
                    <label class="form-check-label" for="userThemeToggle">🌙</label>
                    <input class="form-check-input" type="checkbox" role="switch" id="userThemeToggle">
                </div>
            </div>
        </header>

        <div class="container-fluid px-4">
            <div class="card shadow-sm border-0 mb-4 rounded-4">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Shipment Records</h5>
                    <div class="d-flex gap-2">
                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search tracking..." style="width: 200px;">
                        <select id="statusFilter" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>Tracking No</th>
                                    <th>Destination</th>
                                    <th>Weight (kg)</th>
                                    <th>Price (₱)</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Booked Date</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <?php
                                        $st = htmlspecialchars($row['status']);
                                        $bg = match ($st) {
                                            'Pending' => 'bg-warning',
                                            'In Transit' => 'bg-info',
                                            'Delivered' => 'bg-success',
                                            'Cancelled' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        $displayAddress = !empty($row['destination_address']) ? $row['destination_address'] : ($row['specific_address'] ?? 'N/A');
                                        ?>
                                        <tr class="clickable-row" onclick="openShipmentModal(<?php echo $row['id']; ?>)">
                                            <td class="fw-bold text-primary"><?php echo "TRK" . str_pad($row['id'], 6, "0", STR_PAD_LEFT); ?></td>
                                            <td><?php echo htmlspecialchars(substr($displayAddress, 0, 30)) . '...'; ?></td>
                                            <td><?php echo $row['weight']; ?></td>
                                            <td><?php echo number_format($row['price'], 2); ?></td>
                                            <td><?php echo $row['payment_method']; ?></td>
                                            <td><span class="badge <?php echo $bg; ?>"><?php echo $st; ?></span></td>
                                            <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No shipments found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="shipmentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Tracking Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <svg id="barcode"></svg>
                        <h4 class="mt-2 text-primary fw-bold" id="modalTrackingNo"></h4>
                    </div>

                    <div class="position-relative d-flex justify-content-between mb-5 px-3">
                        <div class="track-line"></div>
                        <div class="step-item" id="step-pending">
                            <div class="step-icon"><i class="bi bi-clipboard-check"></i></div>
                            <div class="step-text">Pending</div>
                        </div>
                        <div class="step-item" id="step-transit">
                            <div class="step-icon"><i class="bi bi-truck"></i></div>
                            <div class="step-text">In Transit</div>
                        </div>
                        <div class="step-item" id="step-delivered">
                            <div class="step-icon"><i class="bi bi-house-door"></i></div>
                            <div class="step-text">Delivered</div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-7">
                            <div class="card h-100 border bg-light shadow-sm">
                                <div class="card-header bg-white fw-bold text-secondary"><i class="bi bi-geo-alt me-1"></i> Route Information</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;">From (Sender)</small>
                                        <div class="fw-bold fs-5" id="modalSender"></div>
                                        <div class="text-primary small mb-1">
                                            <i class="bi bi-telephone-fill me-1"></i>
                                            <a href="#" id="modalSenderContactLink" class="text-decoration-none fw-bold"></a>
                                        </div>
                                        <div class="p-2 bg-white border rounded small text-secondary" id="modalOrigin"></div>
                                    </div>
                                    <div class="text-center my-1"><i class="bi bi-arrow-down text-muted"></i></div>
                                    <div>
                                        <small class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;">To (Receiver)</small>
                                        <div class="fw-bold fs-5" id="modalReceiver"></div>
                                        <div class="text-primary small mb-1">
                                            <i class="bi bi-telephone-fill me-1"></i>
                                            <a href="#" id="modalReceiverContactLink" class="text-decoration-none fw-bold"></a>
                                        </div>
                                        <div class="p-2 bg-white border rounded small text-secondary" id="modalDestination"></div>
                                    </div>
                                    <div class="mt-3">
                                        <small class="text-muted fw-bold">Notes:</small>
                                        <span class="fst-italic small" id="modalSpecificAddress"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card mb-3 border shadow-sm">
                                <div class="card-header bg-white fw-bold text-secondary"><i class="bi bi-box me-1"></i> Package Info</div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                        <span class="text-muted small">Type</span>
                                        <strong id="modalPkgType"></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                        <span class="text-muted small">Weight</span>
                                        <strong><span id="modalWeight"></span> kg</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted small">Description</span>
                                        <span class="text-end small" id="modalPkgDesc"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="card border shadow-sm">
                                <div class="card-header bg-white fw-bold text-secondary"><i class="bi bi-wallet2 me-1"></i> Payment Details</div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Method</span>
                                        <strong class="text-uppercase" id="modalPaymentMethod"></strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">Total Amount</span>
                                        <h4 class="text-success fw-bold mb-0" id="modalPrice"></h4>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-3" id="actionButtonsContainer"></div>
                        </div>
                    </div>

                    <div id="ratingDisplaySection" style="display:none;" class="mt-4 border-top pt-3">
                        <h6 class="fw-bold mb-2" id="feedbackTitle">Feedback</h6>
                        <div id="modalStars" class="mb-2 text-warning fs-4"></div>
                        <div class="p-3 bg-light rounded border">
                            <span id="modalFeedbackText" class="fst-italic text-dark"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button class="btn btn-outline-dark btn-sm" onclick="printShippingLabel()"><i class="bi bi-printer me-1"></i>Print Label</button>
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Cancel Shipment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure? Please tell us why:</p>
                    <input type="hidden" id="cancelShipmentId">
                    <select class="form-select mb-2" id="cancelReason" onchange="checkReason()">
                        <option value="" selected disabled>Select Reason...</option>
                        <option value="Changed mind">Changed mind</option>
                        <option value="Found cheaper option">Found cheaper option</option>
                        <option value="Others">Others</option>
                    </select>
                    <textarea class="form-control" id="cancelFeedback" rows="3" placeholder="Specify reason..." style="display:none;"></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                    <button class="btn btn-danger" onclick="submitCancellation()">Confirm Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Rate Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <form id="ratingForm">
                        <input type="hidden" id="rateShipmentId" name="shipment_id">
                        <div class="mb-3 display-6">
                            <input type="radio" name="rating" value="5" id="r5"><label for="r5" class="mx-1" style="cursor:pointer">5★</label>
                            <input type="radio" name="rating" value="4" id="r4"><label for="r4" class="mx-1" style="cursor:pointer">4★</label>
                            <input type="radio" name="rating" value="3" id="r3"><label for="r3" class="mx-1" style="cursor:pointer">3★</label>
                            <input type="radio" name="rating" value="2" id="r2"><label for="r2" class="mx-1" style="cursor:pointer">2★</label>
                            <input type="radio" name="rating" value="1" id="r1"><label for="r1" class="mx-1" style="cursor:pointer">1★</label>
                        </div>
                        <textarea class="form-control" name="feedback" placeholder="How was your experience?" rows="3"></textarea>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary w-100" onclick="submitRating()">Submit Review</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        if (typeof initDarkMode === 'function') initDarkMode("userThemeToggle", "userDarkMode");

        document.getElementById('hamburger').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('expanded');
        });

        document.getElementById('searchInput').addEventListener('keyup', function() {
            const val = this.value.toLowerCase();
            document.querySelectorAll('#historyTableBody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
            });
        });
        document.getElementById('statusFilter').addEventListener('change', function() {
            const filter = this.value.toLowerCase();
            document.querySelectorAll('#historyTableBody tr').forEach(row => {
                const badge = row.querySelector('.badge');
                if (badge) row.style.display = (!filter || badge.innerText.toLowerCase() === filter) ? '' : 'none';
            });
        });

        let currentShipmentData = null;

        function openShipmentModal(id) {
            const modal = new bootstrap.Modal(document.getElementById('shipmentDetailsModal'));

            document.getElementById('ratingDisplaySection').style.display = 'none';
            document.getElementById('actionButtonsContainer').innerHTML = '';
            ['step-pending', 'step-transit', 'step-delivered'].forEach(s => document.getElementById(s).classList.remove('active'));

            const trackingNo = "TRK" + String(id).padStart(6, '0');
            document.getElementById('modalTrackingNo').innerText = trackingNo;

            JsBarcode("#barcode", trackingNo, {
                format: "CODE128",
                lineColor: "#2c3e50",
                width: 2,
                height: 40,
                displayValue: false
            });

            const fd = new FormData();
            fd.append('action', 'get_shipment_details');
            fd.append('id', id);

            fetch(window.location.href, {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const d = res.data;
                        currentShipmentData = d;

                        // POPULATE DATA
                        document.getElementById('modalSender').textContent = d.sender_name;

                        // CONTACT 1: SENDER
                        const sContact = d.sender_contact || "No Contact";
                        const sLink = document.getElementById('modalSenderContactLink');
                        sLink.textContent = sContact;
                        sLink.href = (sContact !== "No Contact") ? "tel:" + sContact : "#";

                        document.getElementById('modalReceiver').textContent = d.receiver_name;

                        // CONTACT 2: RECEIVER
                        const rContact = d.receiver_contact || "No Contact";
                        const rLink = document.getElementById('modalReceiverContactLink');
                        rLink.textContent = rContact;
                        rLink.href = (rContact !== "No Contact") ? "tel:" + rContact : "#";

                        document.getElementById('modalOrigin').textContent = d.origin_address;
                        document.getElementById('modalDestination').textContent = d.destination_address;
                        document.getElementById('modalSpecificAddress').textContent = d.specific_address || d.address || 'No additional notes.';

                        document.getElementById('modalPkgType').textContent = (d.package_type || 'Package').toUpperCase();
                        document.getElementById('modalWeight').textContent = d.weight;
                        document.getElementById('modalPkgDesc').textContent = d.package || '-';

                        document.getElementById('modalPaymentMethod').textContent = d.payment_method;
                        document.getElementById('modalPrice').textContent = "₱" + parseFloat(d.price).toLocaleString('en-US', {
                            minimumFractionDigits: 2
                        });

                        // STATUS TIMELINE
                        const st = (d.status || '').toLowerCase();
                        if (st.includes('pending')) {
                            document.getElementById('step-pending').classList.add('active');
                        } else if (st.includes('transit')) {
                            document.getElementById('step-pending').classList.add('active');
                            document.getElementById('step-transit').classList.add('active');
                        } else if (st.includes('delivered')) {
                            document.getElementById('step-pending').classList.add('active');
                            document.getElementById('step-transit').classList.add('active');
                            document.getElementById('step-delivered').classList.add('active');
                        }

                        let btns = '';
                        if (st == 'pending') {
                            btns += `<button onclick="openCancelModal(${d.id})" class="btn btn-outline-danger w-100">Cancel Shipment</button>`;
                        } else if ((st == 'in transit' || st == 'pending') && (!d.status.includes('Delivered'))) {
                            btns += `<button onclick="updateStatus(${d.id}, 'Delivered')" class="btn btn-success w-100 mt-2">Mark as Received</button>`;
                        } else if (st == 'delivered' && (!d.rating || d.rating == 0)) {
                            btns += `<button onclick="openRateModal(${d.id})" class="btn btn-warning w-100 fw-bold">Rate Service ★</button>`;
                        }
                        document.getElementById('actionButtonsContainer').innerHTML = btns;

                        const sect = document.getElementById('ratingDisplaySection');
                        const title = document.getElementById('feedbackTitle');
                        const stars = document.getElementById('modalStars');
                        const text = document.getElementById('modalFeedbackText');

                        if (st == 'cancelled') {
                            sect.style.display = 'block';
                            title.innerHTML = '<span class="text-danger">Cancellation Reason</span>';
                            stars.innerHTML = '';
                            text.innerHTML = `<strong class="text-danger">${d.feedback_text || 'No reason provided.'}</strong>`;
                        } else if (d.rating > 0) {
                            sect.style.display = 'block';
                            title.innerHTML = '<span class="text-warning">Your Rating</span>';
                            let s = '';
                            for (let i = 1; i <= 5; i++) s += (i <= d.rating) ? '★' : '☆';
                            stars.innerHTML = s;
                            text.textContent = d.feedback_text || "No comments.";
                        }

                        modal.show();
                    } else {
                        alert(res.message);
                    }
                })
                .catch(err => console.error(err));
        }

        function openCancelModal(id) {
            bootstrap.Modal.getInstance(document.getElementById('shipmentDetailsModal')).hide();
            document.getElementById('cancelShipmentId').value = id;
            new bootstrap.Modal(document.getElementById('cancelModal')).show();
        }

        function checkReason() {
            const r = document.getElementById('cancelReason').value;
            document.getElementById('cancelFeedback').style.display = (r === 'Others') ? 'block' : 'none';
        }

        function submitCancellation() {
            const id = document.getElementById('cancelShipmentId').value;
            let r = document.getElementById('cancelReason').value;
            if (r === 'Others') r = document.getElementById('cancelFeedback').value;
            if (!r) {
                alert("Reason required");
                return;
            }
            updateStatus(id, 'Cancelled', r);
        }

        async function updateStatus(id, st, r = null) {
            if (st !== 'Cancelled' && !confirm("Are you sure you want to update this shipment status?")) return;

            const fd = new FormData();
            fd.append('id', id);
            fd.append('status', st);
            fd.append('action', 'update_status');
            if (r) fd.append('reason', r);

            await fetch('update_shipment_api.php', {
                method: 'POST',
                body: fd
            });
            location.reload();
        }

        function openRateModal(id) {
            bootstrap.Modal.getInstance(document.getElementById('shipmentDetailsModal')).hide();
            document.getElementById('rateShipmentId').value = id;
            new bootstrap.Modal(document.getElementById('rateModal')).show();
        }
        async function submitRating() {
            const form = document.getElementById('ratingForm');
            const fd = new FormData(form);
            fd.append('action', 'submit_rating');
            await fetch('update_shipment_api.php', {
                method: 'POST',
                body: fd
            });
            location.reload();
        }

        function printShippingLabel() {
            if (!currentShipmentData) return;
            const d = currentShipmentData;
            const w = window.open('', '', 'width=600,height=600');
            const tracking = "TRK" + String(d.id).padStart(6, '0');

            w.document.write(`
            <html><head><title>Shipping Label - ${tracking}</title>
            <style>body{font-family:sans-serif; padding:20px; border: 2px solid #000; width: 90%; margin: 20px auto;}</style>
            </head><body>
            <h1 style="text-align:center; margin-bottom:0;">SLATE FREIGHT</h1>
            <h3 style="text-align:center; margin-top:5px;">${tracking}</h3>
            <hr>
            <table width="100%">
                <tr>
                    <td width="50%" valign="top">
                        <strong>FROM (SENDER):</strong><br>${d.sender_name}<br>
                        📞 ${d.sender_contact || 'N/A'}<br>
                        ${d.origin_address}
                    </td>
                    <td width="50%" valign="top">
                        <strong>TO (RECEIVER):</strong><br>${d.receiver_name}<br>
                        📞 ${d.receiver_contact || 'N/A'}<br>
                        ${d.destination_address}
                    </td>
                </tr>
            </table>
            <hr>
            <p><strong>Package:</strong> ${d.package} (${d.weight}kg) - ${d.package_type}</p>
            <p><strong>Notes:</strong> ${d.specific_address || 'None'}</p>
            <br><br>
            <div style="text-align:center; border-top:1px dashed #000; padding-top:10px;">Authorized Signature</div>
            <script>window.print();<\/script>
            </body></html>
        `);
            w.document.close();
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