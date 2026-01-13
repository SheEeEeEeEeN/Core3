<?php
include("darkmode.php");
include("connection.php");
include('session.php');
include('loading.html');
requireRole('admin');

// QUERY: Kukunin ang mga shipment na may Rating (> 0)
// Kasama ang Proof Image, Tracking Number, at Comment
$sql = "SELECT s.id, s.rating, s.feedback_text, s.proof_image, s.created_at, s.status, a.username, a.username 
        FROM shipments s 
        JOIN accounts a ON s.user_id = a.id 
        WHERE s.rating > 0 
        ORDER BY s.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Reviews & Feedback</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  
  <style>
    :root {
      --primary-color: #4e73df; --secondary-color: #f8f9fc; --dark-bg: #1a1a2e; --dark-card: #16213e;
      --text-light: #f8f9fa; --text-dark: #212529; --border-radius: 0.5rem; --shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.1);
    }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background-color: var(--secondary-color); color: var(--text-dark); }
    body.dark-mode { background-color: var(--dark-bg); color: var(--text-light); }
    
    /* Sidebar */
    .sidebar { width: 250px; height: 100vh; background: #2c3e50; color: white; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; transition: 0.3s; z-index: 1000; }
    .sidebar.collapsed { transform: translateX(-100%); }
    .content { margin-left: 250px; padding: 2rem; transition: 0.3s; }
    .content.expanded { margin-left: 0; }
    
    .sidebar a { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.2rem; text-decoration: none; color: rgba(255,255,255,0.9); }
    .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.2); border-left: 4px solid #fff; color: white; }
    
    .header { background: white; padding: 1rem; border-radius: var(--border-radius); box-shadow: var(--shadow); margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; }
    .dark-mode .header { background-color: var(--dark-card); color: var(--text-light); }
    
    /* Review Card Design */
    .review-card { background: white; border-radius: 10px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: var(--shadow); transition: transform 0.2s; border-left: 5px solid var(--primary-color); }
    .dark-mode .review-card { background: var(--dark-card); color: var(--text-light); border-left: 5px solid #6c8cff; }
    .review-card:hover { transform: translateY(-3px); }
    
    .pod-thumbnail { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid #ddd; }
    .pod-thumbnail:hover { border-color: var(--primary-color); opacity: 0.8; }
    
    /* Theme Toggle */
    .theme-switch { width: 50px; height: 25px; position: relative; display: inline-block; }
    .theme-switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; inset: 0; background-color: #ccc; border-radius: 34px; transition: .4s; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 3px; background-color: white; border-radius: 50%; transition: .4s; }
    input:checked+.slider { background-color: var(--primary-color); }
    input:checked+.slider:before { transform: translateX(24px); }
  </style>
</head>

<body>

  <div class="sidebar" id="sidebar">
    <div>
      <div class="text-center p-3 border-bottom border-secondary">
        <img src="Remorig.png" alt="Logo" style="width: 100px;">
        <h6 class="mt-2 mb-0 text-light">CORE ADMIN</h6>
      </div>
      <nav class="mt-3">
        <a href="admin.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        
        <a href="#crmSubmenu" data-bs-toggle="collapse" class="d-flex justify-content-between">
            <span><i class="bi bi-people"></i> CRM</span><i class="bi bi-chevron-down small"></i>
        </a>
        <div class="collapse show" id="crmSubmenu" style="background: rgba(0,0,0,0.2);">
            <a href="CRM.php" class="ps-4"><i class="bi bi-dot"></i> CRM Dashboard</a>
            <a href="customer_feedback.php" class="ps-4 active"><i class="bi bi-dot"></i> Customer Feedback</a>
        </div>

        <a href="#csmSubmenu" data-bs-toggle="collapse" class="d-flex justify-content-between">
            <span><i class="bi bi-file-text"></i> Contract & SLA</span><i class="bi bi-chevron-down small"></i>
        </a>
        <div class="collapse" id="csmSubmenu" style="background: rgba(0,0,0,0.2);">
            <a href="Admin_contracts.php" class="ps-4"><i class="bi bi-dot"></i> Manage Contracts</a>
            <a href="Admin_shipments.php" class="ps-4"><i class="bi bi-dot"></i> SLA Monitoring</a>
        </div>

        <a href="E-Doc.php"><i class="bi bi-folder2-open"></i> E-Docs</a>
        <a href="BIFA.php"><i class="bi bi-graph-up"></i> BI & Analytics</a>
        <a href="activity-log.php"><i class="bi bi-clock-history"></i> Activity Log</a>
        <a href="Archive.php"><i class="bi bi-archive"></i> Archives</a>
        <a href="logout.php" class="border-top mt-3"><i class="bi bi-box-arrow-right"></i> Logout</a>
      </nav>
    </div>
  </div>

  <div class="content" id="mainContent">
    <div class="header">
      <div class="d-flex align-items-center gap-3">
        <i class="bi bi-list fs-4" id="hamburger" style="cursor: pointer;"></i>
        <h4 class="fw-bold mb-0">Feedback & Proof of Delivery</h4>
      </div>
      <div class="theme-toggle-container d-flex align-items-center gap-2">
        <small>Dark Mode</small>
        <label class="theme-switch">
          <input type="checkbox" id="adminThemeToggle"><span class="slider"></span>
        </label>
      </div>
    </div>

    <div class="container-fluid p-0">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="review-card">
            <div class="row align-items-center">
              
              <div class="col-md-6 mb-3 mb-md-0">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 40px; height: 40px;">
                    <?= strtoupper(substr($row['username'], 0, 1)) ?>
                  </div>
                  <div>
                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($row['username']) ?></h6>
                    <small class="text-muted">TRK-<?= str_pad($row['id'], 6, "0", STR_PAD_LEFT) ?> • <?= date("M d, Y h:i A", strtotime($row['created_at'])) ?></small>
                  </div>
                </div>
                <div class="p-3 bg-light rounded border dark-mode-bg">
                  <i class="bi bi-quote fs-4 text-secondary"></i>
                  <span class="fst-italic"><?= htmlspecialchars($row['feedback_text'] ?: 'No written comment provided.') ?></span>
                </div>
              </div>

              <div class="col-md-3 text-center mb-3 mb-md-0 border-start border-end">
                <h2 class="text-warning fw-bold mb-0">
                  <?= $row['rating'] ?>.0
                </h2>
                <div class="text-warning mb-2">
                  <?php for($i=0; $i<$row['rating']; $i++) echo '<i class="bi bi-star-fill"></i>'; ?>
                  <?php for($i=$row['rating']; $i<5; $i++) echo '<i class="bi bi-star"></i>'; ?>
                </div>
                <span class="badge bg-success">Verified Purchase</span>
              </div>

              <div class="col-md-3 text-center">
                <small class="text-muted d-block mb-2 fw-bold text-uppercase">Proof of Delivery</small>
                <?php if(!empty($row['proof_image'])): ?>
                  <img src="uploads/<?= $row['proof_image'] ?>" 
                       class="pod-thumbnail" 
                       alt="Proof" 
                       onclick="viewImage('uploads/<?= $row['proof_image'] ?>')"
                       data-bs-toggle="tooltip" 
                       title="Click to Enlarge">
                  <div class="mt-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="viewImage('uploads/<?= $row['proof_image'] ?>')">
                      <i class="bi bi-eye"></i> View Evidence
                    </button>
                  </div>
                <?php else: ?>
                  <div class="text-muted fst-italic py-3">No Image Available</div>
                <?php endif; ?>
              </div>

            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="text-center py-5">
          <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" width="100" class="mb-3 opacity-50">
          <h4 class="text-muted">No ratings received yet.</h4>
          <p class="text-muted">Completed deliveries with user feedback will appear here.</p>
        </div>
      <?php endif; ?>
    </div>

  </div>

  <div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content bg-transparent border-0">
        <div class="modal-body text-center position-relative">
          <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
          <img src="" id="modalImage" class="img-fluid rounded shadow-lg" style="max-height: 90vh;">
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    initDarkMode("adminThemeToggle", "adminDarkMode");
    
    document.getElementById('hamburger').addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    });

    // Image Viewer Logic
    function viewImage(src) {
      document.getElementById('modalImage').src = src;
      new bootstrap.Modal(document.getElementById('imageModal')).show();
    }
  </script>
</body>
</html>