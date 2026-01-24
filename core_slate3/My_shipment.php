<?php
include("darkmode.php"); 
include("connection.php");
include('session.php');
requireRole('user');

$username = $_SESSION['email']; 

// Fetch shipments 
$query = "SELECT * FROM shipments WHERE sender_name = '$username' OR contract_number = '$username' ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// User Profile logic
$q_user = mysqli_query($conn, "SELECT * FROM accounts WHERE email = '$username'");
$user = mysqli_fetch_assoc($q_user);
$profileImage = !empty($user['profile_image']) ? $user['profile_image'] : 'user.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
  <title>My Shipments — History & Status</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  
  <style>
    :root {
      --sidebar-width: 250px;
      --secondary-color: #f8f9fc;
      --dark-bg: #1a1a2e;
      --dark-card: #16213e;
      --text-light: #f8f9fa;
      --text-dark: #212529;
    }
    body { background-color: var(--secondary-color); font-family: 'Segoe UI', system-ui, sans-serif; overflow-x: hidden; color: var(--text-dark); }
    /* Sidebar Styles */
    .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: #2c3e50; color: white; z-index: 1040; transition: all .3s ease; }
    .content { margin-left: var(--sidebar-width); padding: 20px; transition: margin-left .3s ease; min-height: 100vh; }
    .sidebar a { color: rgba(255, 255, 255, 0.8); text-decoration: none; padding: .75rem 1.5rem; display: block; border-left: 3px solid transparent; }
    .sidebar a:hover, .sidebar a.active { background-color: rgba(255, 255, 255, 0.1); color: white; border-left: 3px solid white; }
    .sidebar.collapsed { margin-left: calc(var(--sidebar-width) * -1); }
    .content.expanded { margin-left: 0; }
    .header { background-color: white; padding: 1rem; border-radius: 0.5rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }

    /* Status & Rating */
    .rating-stars { display: flex; flex-direction: row-reverse; justify-content: center; gap: 5px; }
    .rating-stars input { display: none; }
    .rating-stars label { font-size: 2rem; color: #ccc; cursor: pointer; transition: color 0.2s; }
    .rating-stars input:checked ~ label, .rating-stars label:hover, .rating-stars label:hover ~ label { color: #ffc107; }

    .card-status { border-left: 5px solid #ccc; transition: transform 0.2s; }
    .card-status:hover { transform: translateY(-2px); }
    .status-pending { border-left-color: #ffc107; }
    .status-transit { border-left-color: #0dcaf0; }
    .status-delivered { border-left-color: #198754; }
    .status-cancelled { border-left-color: #dc3545; }

    @media (max-width: 992px) {
        .sidebar { left: -250px; }
        .sidebar.mobile-open { left: 0; }
        .content { margin-left: 0 !important; }
    }
    .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 1030; display: none; transition: opacity 0.3s; }
    .sidebar-overlay.show { display: block; opacity: 1; }

    /* Dark Mode */
    body.dark-mode { background-color: var(--dark-bg); color: var(--text-light); }
    body.dark-mode .sidebar { box-shadow: 2px 0 10px rgba(0,0,0,0.2); }
    body.dark-mode .header { background-color: var(--dark-card) !important; color: var(--text-light) !important; border: 1px solid #2a3a5a; }
    body.dark-mode .card { background-color: var(--dark-card) !important; color: var(--text-light) !important; border: 1px solid #2a3a5a !important; }
    body.dark-mode .text-muted { color: #adb5bd !important; }
    body.dark-mode .modal-content { background-color: var(--dark-card); color: var(--text-light); border: 1px solid #2a3a5a; }
    body.dark-mode .btn-close { filter: invert(1); }
    body.dark-mode .form-control, body.dark-mode .form-select { background-color: #243355; color: white; border-color: #3a4b6e; }
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
      <li class="nav-item mb-2"><a href="user.php" class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3"><i class="bi bi-house-door-fill fs-5"></i><span>Dashboard</span></a></li>
      <li class="nav-item mb-2"><a href="bookshipment.php" class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3"><i class="bi bi-truck fs-5"></i><span>Book Shipment</span></a></li>
      <li class="nav-item mb-2"><a href="My_shipment.php" class="active nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 hover-link"><i class="bi bi-truck fs-5"></i><span>My Shipments</span></a></li>
      <li class="nav-item mb-2"><a href="shiphistory.php" class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3"><i class="bi bi-clock-history fs-5"></i><span>Shipment History</span></a></li>
      <li class="nav-item mb-2"><a href="feedback.php" class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3"><i class="bi bi-chat-dots fs-5"></i><span>Feedback & Notification</span></a></li>
    </ul>
</div>

<div class="content" id="mainContent">
    <header class="header d-flex align-items-center justify-content-between px-3 py-3 mb-4 bg-white shadow-sm rounded-3 sticky-top">
       <div class="d-flex align-items-center gap-3">
           <button class="btn btn-light border-0 p-2" id="hamburger"><i class="bi bi-list fs-4"></i></button>
           <h5 class="fw-semibold mb-0">My Shipments</h5>
       </div>
       
       <div class="d-flex align-items-center gap-3">
           <div class="dropdown">
             <a href="#" class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle" data-bs-toggle="dropdown">
               <img src="<?php echo $profileImage; ?>" alt="User" class="rounded-circle" width="40" height="40" style="object-fit:cover;">
             </a>
             <ul class="dropdown-menu dropdown-menu-end shadow-sm">
               <li><a class="dropdown-item" href="user-profile.php">Profile</a></li>
               <li><hr class="dropdown-divider"></li>
               <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
             </ul>
           </div>
           
           <div class="form-check form-switch mb-0">
             <label class="form-check-label d-none d-sm-inline" for="userThemeToggle">🌙</label>
             <input class="form-check-input" type="checkbox" role="switch" id="userThemeToggle">
           </div>
       </div>
    </header>

    <div class="container-fluid">
        <div class="row g-3">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <?php 
                        $rawStatus = $row['status'] ?? 'Pending';
                        $st = strtolower($rawStatus); 

                        $statusClass = 'status-pending';
                        if(strpos($st, 'transit') !== false) $statusClass = 'status-transit';
                        else if(strpos($st, 'delivered') !== false) $statusClass = 'status-delivered';
                        else if(strpos($st, 'cancelled') !== false) $statusClass = 'status-cancelled';
                    ?>
                    
                    <div class="col-12">
                        <div class="card shadow-sm card-status <?php echo $statusClass; ?>">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-3 border-end">
                                        <h6 class="text-muted small mb-1">ID: #<?php echo $row['id']; ?></h6>
                                        <span class="badge rounded-pill <?php echo ($statusClass == 'status-delivered')?'bg-success':(($statusClass == 'status-cancelled')?'bg-danger':'bg-warning text-dark'); ?>">
                                            <?php echo strtoupper($rawStatus); ?>
                                        </span>
                                        <div class="small text-muted mt-2">Booked: <?php echo date('M d, Y', strtotime($row['created_at'] ?? 'now')); ?></div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column gap-1">
                                            <div><i class="bi bi-geo-alt-fill text-danger"></i> <small>From:</small> <strong><?php echo $row['origin_address']; ?></strong></div>
                                            <div><i class="bi bi-geo-alt-fill text-success"></i> <small>To:</small> <strong><?php echo $row['destination_address']; ?></strong></div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <small class="text-muted d-block"><i class="bi bi-robot"></i> AI Estimated Arrival</small>
                                        <span class="text-info fw-bold">
                                            <?php echo !empty($row['ai_estimated_time']) ? $row['ai_estimated_time'] : 'Calculating...'; ?>
                                        </span>
                                    </div>
                                    
                                    <div class="col-md-2 text-end d-grid gap-2">
                                        
                                        <?php if(strpos($st, 'delivered') !== false): ?>
                                            
                                            <?php if(!empty($row['proof_image'])): ?>
                                                <button onclick="viewProof('<?php echo $row['proof_image']; ?>')" class="btn btn-info btn-sm text-white">
                                                    <i class="bi bi-image"></i> View Proof
                                                </button>
                                            <?php endif; ?>

                                            <?php if($row['rating'] > 0): ?>
                                                <div class="text-warning text-center">
                                                    <?php for($i=0; $i<$row['rating']; $i++) echo '<i class="bi bi-star-fill"></i>'; ?>
                                                    <small class="text-muted d-block">Rated</small>
                                                </div>
                                            <?php else: ?>
                                                <button onclick="openRateModal(<?php echo $row['id']; ?>)" class="btn btn-warning btn-sm text-dark fw-bold">
                                                    <i class="bi bi-star"></i> Rate Service
                                                </button>
                                            <?php endif; ?>

                                        <?php elseif(strpos($st, 'cancelled') !== false): ?>
                                            <button disabled class="btn btn-secondary btn-sm">Cancelled</button>

                                        <?php else: ?>
                                            <button onclick="openReceiveModal(<?php echo $row['id']; ?>)" class="btn btn-success btn-sm">
                                                <i class="bi bi-camera-fill"></i> Receive & Upload Proof
                                            </button>

                                            <?php if($st == 'pending'): ?>
                                                <button onclick="openCancelModal(<?php echo $row['id']; ?>)" class="btn btn-outline-danger btn-sm">
                                                    Cancel
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center p-5 text-muted bg-white rounded shadow-sm card">
                    <div class="card-body">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2">No shipment history found.</p>
                        <a href="bookshipment.php" class="btn btn-primary mt-2">Book a Shipment</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Cancel Shipment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>We're sorry to see you cancel. Please tell us why:</p>
                <form id="cancelForm">
                    <input type="hidden" id="cancelShipmentId">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Reason for Cancellation</label>
                        <select class="form-select" id="cancelReason" onchange="checkReason()" required>
                            <option value="" selected disabled>Select a reason...</option>
                            <option value="Changed mind">Changed my mind</option>
                            <option value="Found cheaper option">Found a cheaper option</option>
                            <option value="Processing too slow">Processing/Delivery too slow</option>
                            <option value="Booked by mistake">Booked by mistake</option>
                            <option value="Others">Others (Please specify)</option>
                        </select>
                    </div>
                    <div class="mb-3" id="otherReasonDiv" style="display:none;">
                        <textarea class="form-control" id="cancelFeedback" rows="3" placeholder="Please specify your reason..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Go Back</button>
                <button type="button" class="btn btn-danger" onclick="submitCancellation()">Confirm Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="receiveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-camera"></i> Proof of Delivery</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Please upload a photo as proof that you have received the package.</p>
                <form id="receiveForm" enctype="multipart/form-data">
                    <input type="hidden" id="receiveShipmentId" name="shipment_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Upload Photo</label>
                        <input type="file" class="form-control" id="proofImage" name="proof_image" accept="image/*" required>
                        <div class="form-text">Accepted formats: JPG, PNG.</div>
                    </div>
                    <div class="text-center mt-3">
                        <img id="imagePreview" src="#" alt="Preview" style="max-width: 100%; max-height: 200px; display: none; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitReceive()">Confirm Received</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewProofModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Proof of Delivery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="proofImageDisplay" src="" class="img-fluid rounded border shadow-sm" alt="Proof of Delivery">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Rate Delivery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>How was your shipment experience?</p>
                <form id="ratingForm">
                    <input type="hidden" id="rateShipmentId" name="shipment_id">
                    <div class="rating-stars mb-3">
                        <input type="radio" name="rating" id="star5" value="5"><label for="star5">★</label>
                        <input type="radio" name="rating" id="star4" value="4"><label for="star4">★</label>
                        <input type="radio" name="rating" id="star3" value="3"><label for="star3">★</label>
                        <input type="radio" name="rating" id="star2" value="2"><label for="star2">★</label>
                        <input type="radio" name="rating" id="star1" value="1"><label for="star1">★</label>
                    </div>
                    <textarea class="form-control" name="feedback" placeholder="Leave a feedback..." rows="3"></textarea>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100" onclick="submitRating()">Submit Review</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    if(typeof initDarkMode === 'function') initDarkMode("userThemeToggle", "userDarkMode");

    const hamburger = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('mainContent');
    const overlay = document.getElementById('sidebarOverlay');

    hamburger.addEventListener('click', () => {
        if (window.innerWidth <= 992) {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('show');
        } else {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');
        }
    });
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('show');
    });

    // --- MODAL INSTANCES ---
    const cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
    const receiveModal = new bootstrap.Modal(document.getElementById('receiveModal'));
    const viewProofModal = new bootstrap.Modal(document.getElementById('viewProofModal'));
    const rateModal = new bootstrap.Modal(document.getElementById('rateModal'));

    // --- CANCEL LOGIC ---
    function openCancelModal(id) {
        document.getElementById('cancelShipmentId').value = id;
        document.getElementById('cancelReason').value = "";
        document.getElementById('cancelFeedback').value = "";
        document.getElementById('otherReasonDiv').style.display = 'none';
        cancelModal.show();
    }

    function checkReason() {
        const reason = document.getElementById('cancelReason').value;
        const otherDiv = document.getElementById('otherReasonDiv');
        otherDiv.style.display = (reason === 'Others') ? 'block' : 'none';
    }

    function submitCancellation() {
        const id = document.getElementById('cancelShipmentId').value;
        const selectVal = document.getElementById('cancelReason').value;
        let finalReason = selectVal;

        if(!selectVal) {
            alert("Please select a reason for cancellation.");
            return;
        }

        if(selectVal === 'Others') {
            const textVal = document.getElementById('cancelFeedback').value;
            if(!textVal.trim()) {
                alert("Please specify the reason.");
                return;
            }
            finalReason = textVal;
        }
        updateStatus(id, 'Cancelled', finalReason);
    }

    // --- RECEIVE LOGIC ---
    function openReceiveModal(id) {
        document.getElementById('receiveShipmentId').value = id;
        document.getElementById('proofImage').value = ""; 
        document.getElementById('imagePreview').style.display = 'none';
        receiveModal.show();
    }

    document.getElementById('proofImage').onchange = evt => {
        const [file] = document.getElementById('proofImage').files
        if (file) {
            const preview = document.getElementById('imagePreview');
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    }

    async function submitReceive() {
        const id = document.getElementById('receiveShipmentId').value;
        const fileInput = document.getElementById('proofImage');

        if(fileInput.files.length === 0) {
            alert("Please upload a proof of delivery photo first.");
            return;
        }

        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', 'Delivered');
        formData.append('action', 'update_status');
        formData.append('proof_image', fileInput.files[0]);

        const btn = document.querySelector('#receiveModal .btn-success');
        const oldText = btn.innerText;
        btn.disabled = true;
        btn.innerText = "Uploading...";

        try {
            // RELATIVE PATH - TAMA
            const res = await fetch('update_shipment_api.php', { 
                method: 'POST', body: formData 
            });
            const text = await res.text();
            
            try {
                const data = JSON.parse(text);
                if(data.success) {
                    alert("Package marked as delivered! Photo uploaded.");
                    location.reload(); 
                } else {
                    alert("Error: " + (data.message || "Unknown error"));
                    btn.disabled = false;
                    btn.innerText = oldText;
                }
            } catch(e) {
                console.error("Non-JSON Response:", text);
                alert("Server error. Check console.");
                btn.disabled = false;
                btn.innerText = oldText;
            }
        } catch (err) { 
            console.error(err);
            alert("Connection error."); 
            btn.disabled = false;
            btn.innerText = oldText;
        }
    }

    // --- VIEW PROOF ---
    function viewProof(imagePath) {
        document.getElementById('proofImageDisplay').src = imagePath;
        viewProofModal.show();
    }

    // --- GENERIC UPDATE ---
    async function updateStatus(id, newStatus, reason = null) {
        try {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', newStatus);
            formData.append('action', 'update_status');
            if(reason) formData.append('reason', reason);

            const res = await fetch('update_shipment_api.php', { 
                method: 'POST', body: formData 
            });
            const data = await res.json();
            
            if(data.success) {
                location.reload(); 
            } else {
                alert("Error: " + (data.message || "Unknown error"));
            }
        } catch (err) { 
            console.error(err);
            alert("Connection error."); 
        }
    }

    // --- RATING ---
    function openRateModal(id) {
        document.getElementById('rateShipmentId').value = id;
        rateModal.show();
    }

      async function submitRating() {
        const form = document.getElementById('ratingForm');
        if(!form.querySelector('input[name="rating"]:checked')) {
            alert("Please select a star rating!");
            return;
        }

        const formData = new FormData(form);
        formData.append('action', 'submit_rating');
        
        try {
            const res = await fetch('https://core3.slatefreight-ph.com/update_shipment_api.php', { 
                method: 'POST', body: formData 
            });
            const data = await res.json();
            
            if(data.success) { 
                alert("Thank you for your feedback!"); 
                location.reload(); 
            } else { 
                alert("Error: " + (data.message || "Unknown error")); 
            }
        } catch (err) { 
            console.error(err); 
            alert("Connection error.");
        }
    }
</script>
</body>
</html>