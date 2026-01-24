<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('connection.php');
include('session.php');
include('darkmode.php');
requireRole('admin');
include('loading.html');

if (isset($_GET['archive'])) {
  $id = intval($_GET['archive']);
  $res = $conn->query("SELECT * FROM accounts WHERE id = $id");

  if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();

    // Move record to archive_crm
    $stmt = $conn->prepare("INSERT INTO archive_crm (username, email, phone_number, gender, role, archived_at)
                                VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param(
      "sssss",
      $row['username'],
      $row['email'],
      $row['phone_number'],
      $row['gender'],
      $row['role']
    );
    $stmt->execute();

    // Delete from main table
    $conn->query("DELETE FROM accounts WHERE id = $id");

    $_SESSION['alert'] = ['title' => 'Archived!', 'text' => 'Customer archived successfully.', 'icon' => 'success'];
    header("Location: CRM.php");
    exit;
  }
}





// Helper
function h($s)
{
  return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// --- Export CSV (server-side) ---
if (isset($_GET['export']) && $_GET['export'] === '1') {
  $base = "SELECT id, customer_name, company, email, phone, status FROM crm";
  $conds = [];
  $params = [];
  $types = '';
  if (!empty($_GET['q'])) {
    $conds[] = "(customer_name LIKE ? OR company LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $term = '%' . $_GET['q'] . '%';
    $params = array_merge($params, [$term, $term, $term, $term]);
    $types .= 'ssss';
  }
  if (!empty($_GET['status'])) {
    $conds[] = "status = ?";
    $params[] = $_GET['status'];
    $types .= 's';
  }
  if ($conds) $base .= " WHERE " . implode(" AND ", $conds);
  $base .= " ORDER BY id DESC";

  $stmt = $conn->prepare($base);
  if ($params) $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $res = $stmt->get_result();

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=crm_customers_export.csv');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['ID', 'Customer Name', 'Company', 'Email', 'Phone', 'Status']);
  while ($r = $res->fetch_assoc()) {
    fputcsv($out, [$r['id'], $r['customer_name'], $r['company'], $r['email'], $r['phone'], $r['status']]);
  }
  fclose($out);
  exit;
}

// --- Handle Add / Edit for Accounts ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];

  // If form is inline Add New Customer
  if (isset($_POST['customer_name'])) {
    $username = trim($_POST['customer_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone']);
    $gender = $_POST['gender'] ?? 'N/A';
    $role = 'customer'; // default role
  } else {
    // If form is modal Add/Edit Account
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $role = trim($_POST['role'] ?? '');
  }

  $errors = [];

  if ($username === '') $errors[] = 'Username is required.';
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';

  if (!$errors) {
    if ($action === 'add') {
      $stmt = $conn->prepare("INSERT INTO accounts (username, email, phone_number, gender, role, password) VALUES (?, ?, ?, ?, ?, SHA2('default123', 256))");
      $stmt->bind_param("sssss", $username, $email, $phone_number, $gender, $role);
      if ($stmt->execute()) {
        header("Location: CRM.php?msg=added");
        exit;
      } else {
        $errors[] = "Insert failed: " . $stmt->error;
      }
    } elseif ($action === 'edit' && !empty($_POST['id'])) {
      $id = intval($_POST['id']);
      $stmt = $conn->prepare("UPDATE accounts SET username=?, email=?, phone_number=?, gender=?, role=? WHERE id=?");
      $stmt->bind_param("sssssi", $username, $email, $phone_number, $gender, $role, $id);

      if ($stmt->execute()) {

        // ✅ Update session if current logged-in user is edited
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
          $_SESSION['username'] = $username;
          $_SESSION['email'] = $email; // optional
        }

        header("Location: CRM.php?msg=updated");
        exit;
      } else {
        $errors[] = "Update failed: " . $stmt->error;
      }
    }
  }
}



// --- Handle Delete via GET ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
  $delid = intval($_GET['delete']);
  $stmt = $conn->prepare("DELETE FROM crm WHERE id = ?");
  $stmt->bind_param("i", $delid);
  $stmt->execute();
  header("Location: CRM.php?msg=deleted");
  exit;
}

// --- Stats ---
$totalCustomers = $conn->query("SELECT COUNT(*) as c FROM crm")->fetch_assoc()['c'] ?? 0;
$activeCustomers = $conn->query("SELECT COUNT(*) as c FROM crm WHERE status='Active'")->fetch_assoc()['c'] ?? 0;
$prospectCustomers = $conn->query("SELECT COUNT(*) as c FROM crm WHERE status='Prospect'")->fetch_assoc()['c'] ?? 0;
$inactiveCustomers = $conn->query("SELECT COUNT(*) as c FROM crm WHERE status='Inactive'")->fetch_assoc()['c'] ?? 0;

// --- Fetch list (limit 500) from accounts table ---
$where = [];
$params = [];
$types = '';
$searchQ = $_GET['q'] ?? '';
$filterRole = $_GET['role'] ?? '';

if ($searchQ !== '') {
  $where[] = "(username LIKE ? OR email LIKE ? OR phone_number LIKE ? OR gender LIKE ?)";
  $term = '%' . $searchQ . '%';
  $params = array_merge($params, [$term, $term, $term, $term]);
  $types .= 'ssss';
}
if ($filterRole !== '') {
  $where[] = "role = ?";
  $params[] = $filterRole;
  $types .= 's';
}

$sql = "SELECT id, username, email, phone_number, gender, role FROM accounts";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY id DESC LIMIT 500";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>CRM — Customer Management</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --primary: #4e73df;
      --secondary: #f8f9fc;
      --dark-bg: #1a1a2e;
      --dark-card: #16213e;
      --light-text: #f8f9fa;
      --radius: 1rem;
      --shadow: 0 0.35rem 1.2rem rgba(0, 0, 0, 0.15);
      --sidebar-width: 250px;
    }

    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background-color: var(--secondary);
      transition: all .3s;
    }

    body.dark-mode {
      background-color: var(--dark-bg);
      color: var(--light-text);
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      height: 100vh;
      background: #2c3e50;
      color: white;
      position: fixed;
      top: 0;
      left: 0;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: all 0.3s ease;
      z-index: 1000;
    }

    .sidebar.collapsed {
      transform: translateX(-100%);
    }

    .sidebar .logo {
      text-align: center;
      padding: 1.2rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .sidebar .logo img {
      width: 120px;
    }

    .sidebar a {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.85rem 1.2rem;
      text-decoration: none;
      color: rgba(255, 255, 255, 0.9);
      transition: 0.2s;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
      border-left: 4px solid #fff;
    }


    /* Dropdown */
    .dropdown-container .dropdown-toggle {
      cursor: pointer;
    }

    .dropdown-content {
      display: none;
      flex-direction: column;
      margin-left: 15px;
      border-left: 2px solid #444;
      margin-top: 5px;
      padding-left: 10px;
    }

    .dropdown-content a {
      font-size: 0.9rem;
      padding: 8px 10px;
      color: #aaa;
    }

    .dropdown-content a:hover {
      color: #fff;
    }

    .dropdown-content.show {
      display: flex;
      animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-5px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .content {
      margin-left: var(--sidebar-width);
      padding: 2rem;
      transition: margin-left .3s;
    }

    .content.expanded {
      margin-left: 0;
    }

    .header {
      background: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 1rem 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    body.dark-mode .header {
      background: var(--dark-card);
      color: var(--light-text);
    }

    .hamburger {
      cursor: pointer;
      font-size: 1.5rem;
    }

    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .card {
      border: none;
      border-radius: var(--radius);
      padding: 1.5rem;
      background: white;
      box-shadow: var(--shadow);
    }

    body.dark-mode .card {
      background: var(--dark-card);
      color: var(--light-text);
    }


    .card h4 {
      color: var(--primary);
      font-weight: 600;
      margin-bottom: .5rem;
    }

    .stat-value {
      font-size: 1.8rem;
      font-weight: 700;
    }

    .table-responsive {
      max-height: 520px;
      overflow: auto;
    }

    body.dark-mode .Select-section {
      background-color: var(--dark-card) !important;
      color: var(--light-text);
    }

    body.dark-mode .Select-section input,
    body.dark-mode .Select-section select {
      background-color: #222a45;
      color: #f8f9fa;
      border: 1px solid #3c4a73;
    }

    body.dark-mode .Select-section input::placeholder {
      color: #b0b8d1;
    }

    body.dark-mode .Select-section label {
      color: #dfe4ff;
    }

    body.dark-mode .Select-section .btn-primary {
      background-color: #3a57e8;
      border-color: #3a57e8;
    }

    body.dark-mode .Select-section .btn-outline-primary {
      color: #f8f9fa;
      border-color: #3a57e8;
    }

    /* 🌙 DARK MODE TABLE STYLES */
    body.dark-mode table.table {
      background-color: #16213e !important;
      color: #000000 !important;
      /* black text */
      border-color: #2e3a5c !important;
    }

    body.dark-mode thead {
      background-color: #1f2a4a !important;
      color: #000000 !important;
      /* black text */
    }

    body.dark-mode thead th {
      border-bottom: 2px solid #2e3a5c !important;
      color: #000000 !important;
      /* black text */
    }

    body.dark-mode tbody tr {
      background-color: #4e73df !important;
      border-bottom: 1px solid #2e3a5c !important;
      transition: background-color 0.25s ease;
      color: #000000 !important;
      /* black text */
    }

    body.dark-mode tbody tr:nth-child(even) {
      background-color: #1e2b4f !important;
      color: #000000 !important;
      /* black text */
    }

    body.dark-mode tbody tr:hover {
      background-color: #2b3b6d !important;
      color: #000000 !important;
      /* black text on hover */
    }

    body.dark-mode td,
    body.dark-mode th {
      color: #000000 !important;
      /* black text everywhere */
      border-color: #2e3a5c !important;
    }

    /* Dark Mode Card Headers / Footers */
    body.dark-mode .card-header {
      background-color: #24325f !important;
      color: #fff !important;
      border-bottom: 1px solid #3c4a73 !important;
    }

    body.dark-mode .card-footer {
      background-color: #1e2747 !important;
      color: #fff !important;
      border-top: 1px solid #2e3a5c !important;
    }

    body.dark-mode .btn-light:hover {
      background-color: #3d4f85 !important;
    }



    body.dark-mode .alert {
      background-color: #24325f;
      color: #e3e9ff;
      border: 1px solid #3b4c84;
    }


    /* Theme Toggle */
    .theme-toggle-container {
      display: flex;
      align-items: center;
      gap: 0.6rem;
    }

    .theme-switch {
      width: 50px;
      height: 25px;
      position: relative;
      display: inline-block;
    }

    .theme-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      inset: 0;
      background-color: #ccc;
      border-radius: 34px;
      transition: .4s;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 4px;
      bottom: 3px;
      background-color: white;
      border-radius: 50%;
      transition: .4s;
    }

    input:checked+.slider {
      background-color: var(--primary);
    }

    input:checked+.slider:before {
      transform: translateX(24px);
    }

    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
      }

      .sidebar.show {
        transform: translateX(0);
      }

      .content {
        margin-left: 0;
      }
    }
  </style>
</head>

<body>
  <div class="sidebar" id="sidebar">
    <div>
      <div class="logo">
        <img src="Remorig.png" alt="Logo">
        <h6 class="mt-2 mb-0 text-light fw-normal">CORE TRANSACTION 3</h6>
      </div>
     <nav class="mt-3">
    <a href="admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <a href="#crmSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people"></i> CRM</span>
        <i class="bi bi-chevron-down" style="font-size: 0.8em;"></i>
    </a>
    
    <div class="collapse <?php echo (basename($_SERVER['PHP_SELF']) == 'CRM.php' || basename($_SERVER['PHP_SELF']) == 'customer_feedback.php') ? 'show' : ''; ?>" id="crmSubmenu" style="background: rgba(0,0,0,0.2);">
        
        <a href="CRM.php" class="ps-4 <?php echo basename($_SERVER['PHP_SELF']) == 'CRM.php' ? 'active' : ''; ?>" style="font-size: 0.9em;">
            <i class="bi bi-dot"></i> CRM Dashboard
        </a>
        
        <a href="customer_feedback.php" class="ps-4 <?php echo basename($_SERVER['PHP_SELF']) == 'customer_feedback.php' ? 'active' : ''; ?>" style="font-size: 0.9em;">
            <i class="bi bi-dot"></i> Customer Feedback
        </a>
        
    </div>
    <a href="#csmSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-text"></i> Contract & SLA</span>
        <i class="bi bi-chevron-down" style="font-size: 0.8em;"></i>
    </a>
    <div class="collapse <?php echo (basename($_SERVER['PHP_SELF']) == 'Admin_contracts.php' || basename($_SERVER['PHP_SELF']) == 'Admin_shipments.php') ? 'show' : ''; ?>" id="csmSubmenu" style="background: rgba(0,0,0,0.2);">
        <a href="Admin_contracts.php" class="ps-4 <?php echo basename($_SERVER['PHP_SELF']) == 'Admin_contracts.php' ? 'active' : ''; ?>" style="font-size: 0.9em;">
            <i class="bi bi-dot"></i> Manage Contracts
        </a>
        <a href="Admin_shipments.php" class="ps-4 <?php echo basename($_SERVER['PHP_SELF']) == 'Admin_shipments.php' ? 'active' : ''; ?>" style="font-size: 0.9em;">
            <i class="bi bi-dot"></i> SLA Monitoring
        </a>
    </div>

    <a href="E-Doc.php"><i class="bi bi-folder2-open"></i> E-Docs</a>
    <a href="BIFA.php"><i class="bi bi-graph-up"></i> BI & Freight Analytics</a>
    <a href="activity-log.php"><i class="bi bi-clock-history"></i> Activity Log</a>
    <a href="Archive.php"><i class="bi bi-archive"></i> Archived Docs</a>

    <a href="logout.php" class="border-top mt-3"><i class="bi bi-box-arrow-right"></i> Logout</a>
</nav>
    </div>
  </div>

  <div class="content" id="mainContent">
    <div class="header">
      <div class="d-flex align-items-center gap-3">
        <div class="hamburger" id="hamburger">☰</div>
        <h4 class="fw-bold mb-0">Customer Relationship Management</h4>
      </div>
      <div class="theme-toggle-container">
        <small>Dark Mode</small>
        <label class="theme-switch">
          <input type="checkbox" id="adminThemeToggle">
          <span class="slider"></span>
        </label>
      </div>
    </div>

    <div class="dashboard-cards">
      <div class="card text-center border-top border-5 border-info">
        <h4><i class="bi bi-people-fill me-2"></i>Total Customers</h4>
        <div class="stat-value"><?= (int)$totalCustomers ?></div>
      </div>

      <div class="card text-center border-top border-5 border-success">
        <h4><i class="bi bi-check2-circle me-2"></i>Active</h4>
        <div class="stat-value"><?= (int)$activeCustomers ?></div>
      </div>

      <div class="card text-center border-top border-5 border-warning">
        <h4><i class="bi bi-person-plus me-2"></i>Prospect</h4>
        <div class="stat-value"><?= (int)$prospectCustomers ?></div>
      </div>

      <div class="card text-center border-top border-5 border-secondary">
        <h4><i class="bi bi-person-x me-2"></i>Inactive</h4>
        <div class="stat-value"><?= (int)$inactiveCustomers ?></div>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="card shadow-sm p-3 mb-3">
      <div class="row g-2">
        <div class="col-md-6">
          <input type="text" id="searchBox" class="form-control" placeholder="🔍 Search by name, company, email, phone" value="<?= h($searchQ) ?>">
        </div>
        <div class="col-md-3">
          <select id="statusFilter" class="form-select" disabled>
            <option value="">All Status</option>
            <option value="Active" <?= $filterStatus === 'Active' ? 'selected' : '' ?>>Active</option>
            <option value="Prospect" <?= $filterStatus === 'Prospect' ? 'selected' : '' ?>>Prospect</option>
            <option value="Inactive" <?= $filterStatus === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-secondary w-100" onclick="resetFilters()">Reset</button>
          <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#modalCustomer" onclick="openAdd()">+ Add</button>
        </div>
      </div>
    </div>

    <!-- Inline Add Form -->
    <div class="Select-section p-3 mb-4 shadow-sm rounded bg-white">
      <h3 class="fw-bold mb-3">Add New Customer</h3>
      <form method="post" class="row g-3">
        <input type="hidden" name="action" value="add">
        <div class="col-md-6">
          <label class="form-label">Customer Name</label>
          <input type="text" name="customer_name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Phone Number</label>
          <input type="text" name="phone" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Gender</label>
          <select name="gender" class="form-select" required>
            <option value="">Select</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
        <div class="col-12 d-flex justify-content-center">
          <button class="btn btn-primary" type="submit"><i class="bi bi-plus-circle me-2"></i> Add Customer</button>
          <a class="btn btn-outline-primary ms-2" href="CRM.php?export=1">Export CSV</a>
        </div>
      </form>

    </div>

    <!-- Customers Table -->
    <div class="card shadow-sm">
      <div class="card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <?php foreach ($errors as $e) echo "<div>" . h($e) . "</div>"; ?>
          </div>
        <?php endif; ?>



        <div class="card shadow-sm border-0">
          <div class="card-header  text-black py-3">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0">
                <i class="bi bi-people me-2"></i>Customer Accounts
              </h5>
              <a href="CRM.php?export=1" class="btn btn-light btn-sm d-flex align-items-center gap-2">
                <i class="bi bi-download"></i>
                Export CSV
              </a>
            </div>
          </div>

          <div class="card-body p-0">
            <?php if (!empty($errors) || !empty($_GET['msg'])): ?>
              <div class="p-3">
                <?php if (!empty($errors)): ?>
                  <div class="alert alert-danger d-flex align-items-center mb-0" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                      <?php foreach ($errors as $e) echo "<div>" . h($e) . "</div>"; ?>
                    </div>
                  </div>
                <?php endif; ?>

                <?php if (!empty($_GET['msg'])): ?>
                  <div class="alert alert-success d-flex align-items-center mb-0" role="alert">
                    <?php
                    $m = $_GET['msg'];
                    $icon = '';
                    $message = '';
                    switch ($m) {
                      case 'added':
                        $icon = 'check-circle-fill';
                        $message = 'Customer added successfully.';
                        break;
                      case 'updated':
                        $icon = 'pencil-square';
                        $message = 'Customer updated successfully.';
                        break;
                      case 'deleted':
                        $icon = 'trash-fill';
                        $message = 'Customer deleted successfully.';
                        break;
                      case 'archived':
                        $icon = 'archive-fill';
                        $message = 'Customer archived successfully.';
                        break;
                    }
                    ?>
                    <i class="bi bi-<?= $icon ?> me-2"></i>
                    <div><?= $message ?></div>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <div class="table-responsive">
              <table id="customersTable" class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                  <tr>
                    <th scope="col" class="text-center" style="width: 70px">#</th>
                    <th scope="col">Username</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Gender</th>
                    <th scope="col">Role</th>
                    <th scope="col" class="text-center" style="width: 180px;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                      <tr>
                        <td class="text-center text-muted"><?= $row['id'] ?></td>
                        <td class="fw-semibold text-primary"><?= h($row['username']) ?></td>
                        <td>
                          <div class="d-flex align-items-center">
                            <i class="bi bi-envelope-fill text-muted me-2"></i>
                            <?= h($row['email']) ?>
                          </div>
                        </td>
                        <td>
                          <?php if ($row['phone_number']): ?>
                            <div class="d-flex align-items-center">
                              <i class="bi bi-telephone-fill text-muted me-2"></i>
                              <?= h($row['phone_number']) ?>
                            </div>
                          <?php else: ?>
                            <span class="text-muted fst-italic">N/A</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <i class="bi bi-<?= strtolower($row['gender']) === 'male' ? 'gender-male text-primary' : 'gender-female text-danger' ?> me-2"></i>
                          <?= h($row['gender']) ?>
                        </td>
                        <td>
                          <?php
                          $roleClass = match ($row['role']) {
                            'admin' => 'danger',
                            'user' => 'primary',
                            'customer' => 'success',
                            default => 'secondary'
                          };
                          ?>
                          <span class="badge bg-<?= $roleClass ?> rounded-pill px-3 py-2">
                            <?= ucfirst(h($row['role'])) ?>
                          </span>
                        </td>
                        <td>
                          <div class="d-flex justify-content-center gap-2">
                            <button type="button"
                              class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                              data-bs-toggle="modal"
                              data-bs-target="#modalCustomer"
                              onclick="openEdit(
                                <?= $row['id'] ?>,
                                '<?= h(addslashes($row['username'])) ?>',
                                '<?= h(addslashes($row['email'])) ?>',
                                '<?= h(addslashes($row['phone_number'])) ?>',
                                '<?= h(addslashes($row['gender'])) ?>',
                                '<?= h(addslashes($row['role'])) ?>'
                              )">
                              <i class="bi bi-pencil"></i>
                              Edit
                            </button>

                            <a href="CRM.php?archive=<?= $row['id'] ?>"
                              class="btn btn-secondary btn-sm d-flex align-items-center gap-1">
                              <i class="bi bi-archive"></i>
                              Archive
                            </a>
                          </div>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="7" class="text-center py-4">
                        <div class="text-muted">
                          <i class="bi bi-inbox display-6 d-block mb-2"></i>
                          No customer accounts found
                        </div>
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="card-footer bg-light py-3">
            <div class="d-flex justify-content-between align-items-center">
              <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Showing up to 500 records
              </small>
              <small class="text-muted">
                <i class="bi bi-database me-1"></i>
                Total: <?= $result->num_rows ?> customers
              </small>
            </div>
          </div>
        </div>


        <div class="mt-3 text-muted small">
          Showing up to 500 records. For large datasets consider server-side pagination.
        </div>
      </div>
    </div>

  </div>

  <!-- Modal (Add/Edit Account) -->
  <div class="modal fade" id="modalCustomer" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <form class="modal-content" id="modalForm" method="post" action="CRM.php">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Add Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" id="frmAction" value="add">
          <input type="hidden" name="id" id="frmId" value="">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Username</label>
              <input name="username" id="frmUsername" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input name="email" id="frmEmail" class="form-control" type="email" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone Number</label>
              <input name="phone_number" id="frmPhone" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Gender</label>
              <select name="gender" id="frmGender" class="form-select" required>
                <option value="">Select</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Role</label>
              <select name="role" id="frmRole" class="form-select" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
                <option value="customer">Customer</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    initDarkMode("adminThemeToggle", "adminDarkMode");

    // Hamburger toggle for sidebar (mobile)
    document.getElementById('hamburger').addEventListener('click', () => {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    });

    // Sidebar accordion behavior
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

    dropdownToggles.forEach(toggle => {
      toggle.addEventListener('click', (e) => {
        e.preventDefault();
        const currentMenu = toggle.nextElementSibling;

        // Close all others
        document.querySelectorAll('.dropdown-content').forEach(menu => {
          if (menu !== currentMenu) menu.classList.remove('show');
        });

        // Toggle current
        currentMenu.classList.toggle('show');
      });
    });

    // Keep dropdown open if current page is CRM or Feedback
    const path = window.location.pathname.split("/").pop();

    if (path === "CRM.php" || path === "customer_feedback.php") {
      const crmMenu = document.querySelector('.dropdown-content');
      const crmToggle = document.querySelector('.dropdown-toggle');
      crmMenu.classList.add('show');
      crmToggle.classList.add('active');

      // Highlight active sublink
      document.querySelectorAll('.dropdown-content a').forEach(link => {
        if (link.getAttribute('href') === path) {
          link.classList.add('active');
        }
      });
    } else {
      // Highlight main nav links (non-dropdown)
      document.querySelectorAll('.sidebar > div nav > a').forEach(link => {
        if (link.getAttribute('href') === path) {
          link.classList.add('active');
        }
      });
    }

    // Table filter (client-side)
    const searchBox = document.getElementById('searchBox');
    const statusFilter = document.getElementById('statusFilter');
    let tableRows = Array.from(document.querySelectorAll('#customersTable tbody tr'));

    function filterTable() {
      const search = searchBox.value.toLowerCase();
      const status = statusFilter.value;
      tableRows.forEach(row => {
        const cells = Array.from(row.querySelectorAll('td')).map(td => td.textContent.toLowerCase()).join(' ');
        const rowStatus = row.querySelector('td:nth-child(6)').textContent.trim();
        const show = cells.includes(search) && (!status || rowStatus === status);
        row.style.display = show ? '' : 'none';
      });
    }

    searchBox.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);

    function resetFilters() {
      searchBox.value = '';
      statusFilter.value = '';
      filterTable();
    }

    // Inline add form submit (creates a temporary form to POST)
    async function submitAddForm() {
      const form = document.getElementById('inlineAddForm');
      if (!form) return;
      const data = new FormData(form);
      const temp = document.createElement('form');
      temp.method = 'POST';
      temp.action = 'CRM.php';
      temp.style.display = 'none';
      const entries = Array.from(data.entries());
      entries.forEach(([k, v]) => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = k;
        inp.value = v;
        temp.appendChild(inp);
      });
      document.body.appendChild(temp);
      temp.submit();
    }

    // Modal helpers
    function openAdd() {
      document.getElementById('modalTitle').textContent = "Add Account";
      document.getElementById('frmAction').value = "add";
      document.getElementById('frmId').value = "";
      document.getElementById('frmUsername').value = "";
      document.getElementById('frmEmail').value = "";
      document.getElementById('frmPhone').value = "";
      document.getElementById('frmGender').value = "";
      document.getElementById('frmRole').value = "customer";
    }

    function openEdit(id, username, email, phone, gender, role) {
      document.getElementById('modalTitle').textContent = "Edit Account";
      document.getElementById('frmAction').value = "edit";
      document.getElementById('frmId').value = id;
      document.getElementById('frmUsername').value = username;
      document.getElementById('frmEmail').value = email;
      document.getElementById('frmPhone').value = phone;
      document.getElementById('frmGender').value = gender;
      document.getElementById('frmRole').value = role;
    }

    // this is the archive function

    function confirmArchive(id) {
      if (confirm('Archive this customer?')) {
        window.location = 'CRM.php?archive_id=' + encodeURIComponent(id);
      }
    }



    function confirmArchive(id) {
      if (confirm('Archive this customer? This will move the record to archive.')) {
        window.location = 'Archive_CRM.php?id=' + encodeURIComponent(id);
      }
    }


    // Refresh row cache when DOM ready
    function refreshRows() {
      tableRows = Array.from(document.querySelectorAll('#customersTable tbody tr'));
    }
    window.addEventListener('DOMContentLoaded', refreshRows);

    // Initialize Bootstrap tooltips for icons
    document.addEventListener('DOMContentLoaded', function() {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      tooltipTriggerList.map(el => new bootstrap.Tooltip(el))
    });

    // Archive confirmation using SweetAlert2
    document.querySelectorAll(".archive").forEach(btn => {
      btn.addEventListener("click", function(e) {
        e.preventDefault();
        const url = this.href;
        Swal.fire({
          title: 'Archive Customer?',
          text: "This will move the customer to the archive section.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ffc107',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Yes, archive it!'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = url;
          }
        });
      });
    });
  </script>
  <script>
    document.querySelectorAll('.archive').forEach(btn => {
      btn.addEventListener('click', function(e) {
        if (!confirm('Are you sure you want to archive this customer?')) {
          e.preventDefault();
        }
      });
    });
  </script>

</body>

</html>