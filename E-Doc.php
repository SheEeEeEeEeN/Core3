<?php
// E-Doc.php (INTEGRATED VERSION)
include("connection.php");
include("darkmode.php"); 
include('session.php');
requireRole('admin');
include('loading.html');

// IMPORT THE CENTRAL UPLOAD ENGINE
include('file_handler.php'); 

// --- UPLOAD LOGIC (For Shipments) ---
$msg = "";
$msgType = "";

if (isset($_POST['btn_upload'])) {
    $tracking_num = mysqli_real_escape_string($conn, $_POST['tracking_number']);
    $doc_type = mysqli_real_escape_string($conn, $_POST['doc_type']);
    $uploader = $_SESSION['username'] ?? 'Admin'; 
    
    $result = uploadDocument($conn, $tracking_num, $doc_type, $_FILES['doc_file'], $uploader);
    $msg = $result['message'];
    $msgType = $result['status'];
}

// --- FETCH DOCUMENTS (MERGED LOGIC) ---
$search = isset($_GET['search']) ? $_GET['search'] : '';
$all_docs = []; // Dito natin iipunin lahat

// 1. GET SHIPMENT DOCUMENTS
$sql_ship = "SELECT d.*, s.sender_name 
             FROM shipment_documents d
             LEFT JOIN shipments s ON d.tracking_number = s.id";
if($search != ''){ $sql_ship .= " WHERE d.tracking_number LIKE '%$search%' OR d.doc_type LIKE '%$search%'"; }
$res_ship = $conn->query($sql_ship);

while($row = $res_ship->fetch_assoc()){
    $all_docs[] = [
        'type' => 'Shipment',
        'ref_id' => $row['tracking_number'],
        'name' => $row['sender_name'],
        'doc_type' => $row['doc_type'],
        'file_name' => $row['file_name'],
        'uploader' => $row['uploaded_by'],
        'date' => $row['uploaded_at'],
        'link' => $row['file_path'], // Link sa uploaded file
        'is_virtual' => false
    ];
}

// 2. GET CONTRACTS (Virtual Files)
$sql_cont = "SELECT * FROM contracts";
if($search != ''){ $sql_cont .= " WHERE contract_number LIKE '%$search%' OR client_name LIKE '%$search%'"; }
$res_cont = $conn->query($sql_cont);

while($row = $res_cont->fetch_assoc()){
    $all_docs[] = [
        'type' => 'Contract',
        'ref_id' => $row['contract_number'],
        'name' => $row['client_name'], // Client Name instead of Sender
        'doc_type' => 'Service Agreement',
        'file_name' => 'System Generated PDF',
        'uploader' => 'System',
        'date' => $row['created_at'],
        'link' => 'contract_print.php?id=' . $row['id'], // Link sa Generator
        'is_virtual' => true
    ];
}

// 3. SORT DATA (Pinakabago sa taas)
usort($all_docs, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Fetch Dropdown Data (For Upload Modal)
$shipment_list = $conn->query("SELECT id, sender_name FROM shipments ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>E-Documentation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  
  <style>
    /* UNIFORM STYLES */
    :root { --primary: #4e73df; --secondary: #f8f9fc; --dark-bg: #1e1e2f; --dark-card: #2b2b40; --light-text: #f8f9fa; --radius: 1rem; --shadow: 0 0.35rem 1.2rem rgba(0, 0, 0, 0.15); }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background-color: var(--secondary); transition: all 0.3s ease; }
    
    /* Dark Mode */
    body.dark-mode { background-color: var(--dark-bg); color: var(--light-text); }
    body.dark-mode .header, body.dark-mode .card, body.dark-mode .list-group-item, body.dark-mode .modal-content { background: var(--dark-card); color: var(--light-text); border-color: #444; }
    body.dark-mode .form-control, body.dark-mode .form-select { background-color: #3a3b45; color: white; border: 1px solid #555; }
    body.dark-mode .table { color: var(--light-text); border-color: #444; }
    body.dark-mode .table thead th { background-color: #1a1a2e; color: white; border-bottom: 2px solid #555; }
    body.dark-mode .table tbody td { background-color: var(--dark-card); color: var(--light-text); border-color: #444; }
    body.dark-mode .table-hover tbody tr:hover td { background-color: #3a3b45; color: white; }
    
    /* Sidebar */
    .sidebar { width: 250px; height: 100vh; background: #2c3e50; color: white; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; transition: 0.3s; z-index: 1000; overflow-y: auto; }
    .sidebar.collapsed { transform: translateX(-100%); }
    .content { margin-left: 250px; padding: 2rem; transition: 0.3s; }
    .content.expanded { margin-left: 0; }
    .sidebar a { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.2rem; text-decoration: none; color: rgba(255,255,255,0.9); }
    .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.2); border-left: 4px solid #fff; color: white; }
    
    /* Header & Components */
    .header { background: white; border-radius: var(--radius); box-shadow: var(--shadow); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .card { border: none; border-radius: var(--radius); padding: 1.5rem; background: white; box-shadow: var(--shadow); margin-bottom: 1.5rem; }
    
    .theme-switch { width: 50px; height: 25px; position: relative; display: inline-block; }
    .theme-switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; inset: 0; background-color: #ccc; border-radius: 34px; transition: .4s; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 3px; background-color: white; border-radius: 50%; transition: .4s; }
    input:checked+.slider { background-color: var(--primary); }
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
        <div class="collapse" id="crmSubmenu" style="background: rgba(0,0,0,0.2);">
            <a href="CRM.php" class="ps-4"><i class="bi bi-dot"></i> CRM Dashboard</a>
            <a href="customer_feedback.php" class="ps-4"><i class="bi bi-dot"></i> Customer Feedback</a>
        </div>
        <a href="#csmSubmenu" data-bs-toggle="collapse" class="d-flex justify-content-between">
            <span><i class="bi bi-file-text"></i> Contract & SLA</span><i class="bi bi-chevron-down small"></i>
        </a>
        <div class="collapse" id="csmSubmenu" style="background: rgba(0,0,0,0.2);">
            <a href="admin_contracts.php" class="ps-4"><i class="bi bi-dot"></i> Manage Contracts</a>
            <a href="admin_shipments.php" class="ps-4"><i class="bi bi-dot"></i> SLA Monitoring</a>
        </div>
        <a href="E-Doc.php"><i class="bi bi-folder2-open"></i> E-Docs</a>
        <a href="BIFA.php" class="active"><i class="bi bi-graph-up"></i> BI & Analytics</a>
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
        <h4 class="fw-bold mb-0">Centralized Documentation</h4>
      </div>
      <div class="d-flex align-items-center gap-2">
        <small>Dark Mode</small>
        <label class="theme-switch">
          <input type="checkbox" id="adminThemeToggle"><span class="slider"></span>
        </label>
      </div>
    </div>

    <?php if($msg != ''): ?>
    <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show" role="alert">
        <?php echo $msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-cloud-upload"></i> Manual Upload (Admin)
                </button>
            </div>
            <form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control" placeholder="Search ID or Type..." value="<?php echo $search; ?>">
                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-bold text-primary mb-3">Central Repository (Contracts & Documents)</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Reference ID</th>
                            <th>Client / Sender</th>
                            <th>Document Type</th>
                            <th>File Name</th>
                            <th>Source</th>
                            <th>Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($all_docs) > 0): ?>
                            <?php foreach($all_docs as $doc): ?>
                            
                            <tr>
                                <td>
                                    <?php if($doc['type'] == 'Contract'): ?>
                                        <span class="badge bg-primary">Contract</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Shipment</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="fw-bold text-primary"><?php echo $doc['ref_id']; ?></td>
                                
                                <td><?php echo $doc['name'] ?? '<em class="text-muted">Unknown</em>'; ?></td>
                                
                                <td>
                                    <span class="badge bg-secondary"><?php echo $doc['doc_type']; ?></span>
                                </td>
                                
                                <td class="small"><?php echo $doc['file_name']; ?></td>
                                
                                <td><small class="text-muted fw-bold"><?php echo $doc['uploader']; ?></small></td>
                                
                                <td><?php echo date('M d, Y', strtotime($doc['date'])); ?></td>
                                
                                <td class="text-end">
                                    <a href="<?php echo $doc['link']; ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                        <?php if($doc['is_virtual']): ?>
                                            <i class="bi bi-printer"></i> Print/View
                                        <?php else: ?>
                                            <i class="bi bi-eye"></i> View File
                                        <?php endif; ?>
                                    </a>
                                </td>
                            </tr>
                            
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No documents found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  </div>

  <div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-cloud-upload"></i> Admin Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Shipment</label>
                        <input class="form-control" list="shipmentOptions" name="tracking_number" placeholder="Search Tracking #..." required>
                        <datalist id="shipmentOptions">
                            <?php 
                            if ($shipment_list->num_rows > 0) {
                                while($s = $shipment_list->fetch_assoc()) {
                                    echo "<option value='".$s['id']."'>".$s['sender_name']."</option>";
                                }
                            }
                            ?>
                        </datalist>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Document Type</label>
                        <select name="doc_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="Proof of Delivery">Proof of Delivery (POD)</option>
                            <option value="Waybill">Waybill / BOL</option>
                            <option value="Commercial Invoice">Commercial Invoice</option>
                            <option value="Packing List">Packing List</option>
                            <option value="Permit">Permit / Certificate</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select File</label>
                        <input type="file" name="doc_file" class="form-control" required accept=".jpg,.jpeg,.png,.pdf,.docx">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="btn_upload" class="btn btn-primary">Upload Now</button>
                </div>
            </form>
        </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    if (typeof initDarkMode === 'function') {
        initDarkMode("adminThemeToggle", "adminDarkMode");
    } else {
        const toggle = document.getElementById('adminThemeToggle');
        if(localStorage.getItem('adminDarkMode') === 'enabled'){
            document.body.classList.add('dark-mode');
            toggle.checked = true;
        }
        toggle.addEventListener('change', () => {
            if(toggle.checked){
                document.body.classList.add('dark-mode');
                localStorage.setItem('adminDarkMode', 'enabled');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('adminDarkMode', 'disabled');
            }
        });
    }

    document.getElementById('hamburger').addEventListener('click', () => {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    });
  </script>
</body>
</html>