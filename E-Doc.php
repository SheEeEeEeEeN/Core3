<?php
// E-Doc.php (SECURE VAULT EDITION) 🔒✨
include("connection.php");
include("darkmode.php"); 
include('session.php');
requireRole('admin');
include('loading.html');
include('file_handler.php'); 

// =========================================================
// 🔐 SECURITY CONFIGURATION
// =========================================================
$VAULT_PASSWORD = "core3"; 

// Handle Unlock Request
$vault_error = "";
if (isset($_POST['btn_unlock'])) {
    $input_pass = $_POST['vault_pass'];
    if ($input_pass === $VAULT_PASSWORD) {
        $_SESSION['edocs_unlocked'] = true;
        header("Location: E-Doc.php"); // Refresh to clear post data
        exit;
    } else {
        $vault_error = "Incorrect password. Access denied.";
    }
}

// Handle Re-Lock Request
if (isset($_GET['action']) && $_GET['action'] == 'lock') {
    unset($_SESSION['edocs_unlocked']);
    header("Location: E-Doc.php");
    exit;
}

// Check Status
$is_unlocked = isset($_SESSION['edocs_unlocked']) && $_SESSION['edocs_unlocked'] === true;

// =========================================================
// 📂 DOCUMENT LOGIC (EXECUTE ONLY IF UNLOCKED)
// =========================================================
$all_docs = [];
$shipment_list = null;
$msg = "";
$msgType = "";
$missing_pod_list = [];
$search = "";
$current_page_docs = [];
$total_pages = 1;
$page = 1;
$total_results = 0;

if ($is_unlocked) {
    // --- UPLOAD LOGIC ---
    if (isset($_POST['btn_upload'])) {
        $tracking_num = mysqli_real_escape_string($conn, $_POST['tracking_number']);
        $doc_type = mysqli_real_escape_string($conn, $_POST['doc_type']);
        $uploader = $_SESSION['username'] ?? 'Admin'; 
        $result = uploadDocument($conn, $tracking_num, $doc_type, $_FILES['doc_file'], $uploader);
        $msg = $result['message'];
        $msgType = $result['status'];
    }

    // --- FETCH DOCUMENTS ---
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

    // 1. MANUAL
    $sql_manual = "SELECT d.*, s.sender_name FROM shipment_documents d LEFT JOIN shipments s ON d.tracking_number = s.id";
    if($search != ''){ $sql_manual .= " WHERE d.tracking_number LIKE '%$search%' OR d.doc_type LIKE '%$search%'"; }
    $res_manual = $conn->query($sql_manual);
    while($row = $res_manual->fetch_assoc()){
        $ext = pathinfo($row['file_name'], PATHINFO_EXTENSION);
        $all_docs[] = [
            'category' => 'Manual', 'ref_id' => $row['tracking_number'], 'name' => $row['sender_name'],
            'doc_type' => $row['doc_type'], 'file_name' => $row['file_name'], 'file_ext' => strtolower($ext),
            'uploader' => $row['uploaded_by'], 'date' => $row['uploaded_at'], 'link' => $row['file_path'], 'is_virtual'=> false
        ];
    }

    // 2. print_invoiceS
    $sql_ship = "SELECT id, sender_name, created_at FROM shipments";
    if($search != ''){ $sql_ship .= " WHERE id LIKE '%$search%' OR sender_name LIKE '%$search%'"; }
    $sql_ship .= " ORDER BY created_at DESC LIMIT 100";
    $res_ship = $conn->query($sql_ship);
    while($row = $res_ship->fetch_assoc()){
        $trk = "TRK-" . str_pad($row['id'], 6, "0", STR_PAD_LEFT);
        $all_docs[] = [
            'category' => 'System', 'ref_id' => $trk, 'name' => $row['sender_name'],
            'doc_type' => 'print_invoice', 'file_name' => $trk . '_print_invoice.pdf', 'file_ext' => 'pdf',
            'uploader' => 'System', 'date' => $row['created_at'], 'link' => 'print_invoice.php?id=' . $row['id'], 'is_virtual'=> true
        ];
    }

    // 3. CONTRACTS
    $sql_cont = "SELECT id, contract_number, sender_name, created_at FROM shipments WHERE contract_number IS NOT NULL AND contract_number != ''";
    if($search != ''){ $sql_cont .= " AND (contract_number LIKE '%$search%' OR sender_name LIKE '%$search%')"; }
    $sql_cont .= " ORDER BY created_at DESC LIMIT 100";
    $res_cont = $conn->query($sql_cont);
    while($row = $res_cont->fetch_assoc()){
        $all_docs[] = [
            'category' => 'System', 'ref_id' => $row['contract_number'], 'name' => $row['sender_name'],
            'doc_type' => 'Contract', 'file_name' => $row['contract_number'] . '_Contract.pdf', 'file_ext' => 'pdf',
            'uploader' => 'System', 'date' => $row['created_at'], 'link' => 'view_contract.php?ref=' . $row['contract_number'], 'is_virtual'=> true
        ];
    }

    usort($all_docs, function($a, $b) { return strtotime($b['date']) - strtotime($a['date']); });

    // PAGINATION
    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if($page < 1) $page = 1;
    $total_results = count($all_docs);
    $total_pages = ceil($total_results / $limit);
    $offset = ($page - 1) * $limit;
    $current_page_docs = array_slice($all_docs, $offset, $limit);

    $shipment_list = $conn->query("SELECT id, sender_name FROM shipments ORDER BY created_at DESC");

    // COMPLIANCE
    $q_check = $conn->query("SELECT id, sender_name FROM shipments WHERE status='Delivered'");
    while($s = $q_check->fetch_assoc()){
        $sid = "TRK" . str_pad($s['id'], 6, "0", STR_PAD_LEFT);
        $chk = $conn->query("SELECT id FROM shipment_documents WHERE tracking_number='{$s['id']}' AND doc_type='Proof of Delivery'");
        if($chk->num_rows == 0){ $missing_pod_list[] = $sid; }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Secured Documents | Core Admin</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  
  <style>
    /* STANDARD CSS */
    :root {
      --primary: #4e73df; --secondary: #f8f9fc; --dark-bg: #1e1e2f; --dark-card: #2b2b40;
      --light-text: #f8f9fa; --radius: 1rem; --shadow: 0 0.35rem 1.2rem rgba(0, 0, 0, 0.15);
    }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background-color: var(--secondary); transition: all 0.3s ease; }
    body.dark-mode { background-color: var(--dark-bg); color: var(--light-text); }
    
    .sidebar { width: 250px; height: 100vh; background: #2c3e50; color: white; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; transition: 0.3s; z-index: 1000; }
    .sidebar.collapsed { transform: translateX(-100%); }
    .sidebar a { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.2rem; text-decoration: none; color: rgba(255,255,255,0.9); }
    .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.2); border-left: 4px solid #fff; color: white; }
    
    .content { margin-left: 250px; padding: 2rem; transition: 0.3s; }
    .content.expanded { margin-left: 0; }
    
    .header { background: white; border-radius: var(--radius); box-shadow: var(--shadow); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .card { border: none; border-radius: var(--radius); padding: 1.5rem; background: white; box-shadow: var(--shadow); margin-bottom: 1.5rem; }
    
    body.dark-mode .header, body.dark-mode .card, body.dark-mode .list-group-item, body.dark-mode .modal-content { background: var(--dark-card); color: var(--light-text); border-color: #444; }
    body.dark-mode .table { color: var(--light-text); }
    body.dark-mode .table-striped > tbody > tr:nth-of-type(odd) > * { color: var(--light-text); background-color: rgba(255,255,255,0.05); }
    body.dark-mode .table-hover > tbody > tr:hover > * { color: var(--light-text); background-color: rgba(255,255,255,0.1); }
    
    .page-link { color: var(--primary); }
    .page-item.active .page-link { background-color: var(--primary); border-color: var(--primary); }
    
    .theme-switch { width: 50px; height: 25px; position: relative; display: inline-block; }
    .theme-switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; inset: 0; background-color: #ccc; border-radius: 34px; transition: .4s; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 3px; background-color: white; border-radius: 50%; transition: .4s; }
    input:checked+.slider { background-color: var(--primary); }
    input:checked+.slider:before { transform: translateX(24px); }

    /* LOCK SCREEN STYLES */
    .lock-screen {
        max-width: 400px;
        margin: 5rem auto;
        text-align: center;
    }
    .lock-icon {
        font-size: 4rem;
        color: var(--primary);
        margin-bottom: 1rem;
    }
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
        <a href="E-Doc.php" class="active"><i class="bi bi-folder2-open"></i> E-Docs</a>
        <a href="admin_completed.php"><i class="bi bi-check-circle-fill"></i> Completed Trans.</a>
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
        <h4 class="fw-bold mb-0">Centralized Documentation</h4>
      </div>
      <div class="d-flex align-items-center gap-2">
        <?php if($is_unlocked): ?>
            <a href="?action=lock" class="btn btn-outline-danger btn-sm rounded-pill px-3 me-3">
                <i class="bi bi-lock-fill me-1"></i> Lock Vault
            </a>
        <?php endif; ?>
        
        <div class="dropdown me-3">
            <a href="#" class="text-dark position-relative" id="notifDropdown" data-bs-toggle="dropdown" onclick="markRead()">
                <i class="bi bi-bell fs-4"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="display: none;">0</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 300px; max-height: 400px; overflow-y: auto;">
                <li class="p-2 border-bottom fw-bold bg-light">Notifications</li>
                <div id="notifList"><li class="text-center p-3 text-muted small">Checking...</li></div>
            </ul>
        </div>
        <small>Dark Mode</small>
        <label class="theme-switch">
          <input type="checkbox" id="adminThemeToggle"><span class="slider"></span>
        </label>
      </div>
    </div>

    <?php if (!$is_unlocked): ?>
        <div class="lock-screen fade-in">
            <div class="card shadow-lg border-0 p-4">
                <div class="card-body">
                    <i class="bi bi-shield-lock-fill lock-icon"></i>
                    <h4 class="fw-bold mb-1">Restricted Access</h4>
                    <p class="text-muted small mb-4">Enter vault password to view sensitive documents.</p>
                    
                    <?php if($vault_error): ?>
                        <div class="alert alert-danger py-2 small"><?php echo $vault_error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input type="password" name="vault_pass" class="form-control" placeholder="Enter Password" required autofocus>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="btn_unlock" class="btn btn-primary fw-bold py-2">
                                Unlock E-Docs <i class="bi bi-arrow-right-short"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
    
   

    <?php if($msg != ''): ?>
    <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show"><?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="bi bi-cloud-upload"></i> Upload Document
            </button>
            <form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control" placeholder="Search ID or Name..." value="<?php echo $search; ?>">
                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Category</th>
                        <th>Reference ID</th>
                        <th>Client / Sender</th>
                        <th>Document Type</th>
                        <th>File Name</th>
                        <th>Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($current_page_docs) > 0): ?>
                        <?php foreach($current_page_docs as $doc): ?>
                        <tr>
                            <td>
                                <?php if($doc['category'] == 'Manual'): ?>
                                    <span class="badge bg-secondary">Manual</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Auto-Gen</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-primary"><?php echo $doc['ref_id']; ?></td>
                            <td><?php echo $doc['name']; ?></td>
                            <td>
                                <?php 
                                    $badge = 'secondary';
                                    if($doc['doc_type'] == 'print_invoice') $badge = 'info text-dark';
                                    if($doc['doc_type'] == 'Contract') $badge = 'warning text-dark';
                                ?>
                                <span class="badge bg-<?php echo $badge; ?>"><?php echo $doc['doc_type']; ?></span>
                            </td>
                            <td class="small font-monospace"><?php echo $doc['file_name']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($doc['date'])); ?></td>
                            <td class="text-end">
                                <?php if($doc['is_virtual']): ?>
                                    <button onclick="window.open('<?php echo $doc['link']; ?>', '_blank')" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-printer-fill"></i> View
                                    </button>
                                <?php else: ?>
                                    <a href="<?php echo $doc['link']; ?>" target="_blank" class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-download"></i> DL
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No documents found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>">Previous</a>
                </li>
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>">Next</a>
                </li>
            </ul>
        </nav>
        <div class="text-center small text-muted">
            Page <?php echo $page; ?> of <?php echo $total_pages; ?> (Total <?php echo $total_results; ?> docs)
        </div>
        <?php endif; ?>

      </div>
    </div>
    
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Shipment</label>
                            <input class="form-control" list="shipmentOptions" name="tracking_number" placeholder="Search Tracking #..." required>
                            <datalist id="shipmentOptions">
                                <?php 
                                if ($shipment_list && $shipment_list->num_rows > 0) {
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
                                <option value="print_invoice">print_invoice / BOL</option>
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="btn_upload" class="btn btn-primary">Upload Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php endif; ?> </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Dark Mode
    const toggle = document.getElementById('adminThemeToggle');
    const body = document.body;
    if(localStorage.getItem('theme') === 'dark'){ body.classList.add('dark-mode'); toggle.checked = true; }
    toggle.addEventListener('change', () => {
        if(toggle.checked){ body.classList.add('dark-mode'); localStorage.setItem('theme', 'dark'); }
        else { body.classList.remove('dark-mode'); localStorage.setItem('theme', 'light'); }
    });

    document.getElementById('hamburger').addEventListener('click', () => {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    });

    // Notifications
    function fetchNotifications() {
        fetch('api/get_notifications.php').then(r => r.json()).then(data => {
            const badge = document.getElementById('notifBadge');
            const list = document.getElementById('notifList');
            if (data.count > 0) { badge.innerText = data.count; badge.style.display = 'inline-block'; } 
            else { badge.style.display = 'none'; }
            let html = '';
            if (data.data.length > 0) {
                data.data.forEach(notif => {
                    let bgClass = notif.is_read == 0 ? 'bg-light' : '';
                    html += `<li><a class="dropdown-item ${bgClass} p-2 border-bottom" href="#"><small class="fw-bold d-block">${notif.title}</small><small class="text-muted">${notif.message}</small></a></li>`;
                });
            } else { html = '<li class="text-center p-3 text-muted small">Checking...</li>'; }
            list.innerHTML = html;
        });
    }
    function markRead() {
        fetch('api/get_notifications.php', { method: 'POST', body: 'action=read_all', headers: {'Content-Type': 'application/x-www-form-urlencoded'} })
        .then(() => { document.getElementById('notifBadge').style.display = 'none'; });
    }
    fetchNotifications();
    setInterval(fetchNotifications, 5000);
  </script>
</body>
</html>