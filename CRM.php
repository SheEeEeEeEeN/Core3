<?php
include("darkmode.php");
include 'connection.php';
include('session.php');
requireRole('admin'); // ✅ Only admins can manage accounts

/* Add Account */
if (isset($_POST['add'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $email    = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // ✅ secure hash
    $role     = $conn->real_escape_string($_POST['role']);

    $sql = "INSERT INTO accounts (username, email, password, role, created_at) 
            VALUES ('$username', '$email', '$password', '$role', NOW())";

    if ($conn->query($sql) === TRUE) {
        $activity = "Added new account: $username ($role)";
        $conn->query("INSERT INTO admin_activity (module, activity, status) 
                      VALUES ('CRM', '$activity', 'Success')");
        header("Location: CRM.php?success=1");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

/* Update Account */
if (isset($_POST['update'])) {
    $id       = (int) $_POST['id'];
    $username = $conn->real_escape_string($_POST['username']);
    $email    = $conn->real_escape_string($_POST['email']);
    $role     = $conn->real_escape_string($_POST['role']);

    $sql = "UPDATE accounts 
            SET username='$username', 
                email='$email',
                role='$role'
            WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        $activity = "Updated account: $username ($role)";
        $conn->query("INSERT INTO admin_activity (module, activity, status) 
                      VALUES ('CRM', '$activity', 'Success')");
        header("Location: CRM.php?updated=1");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

/* Delete Account */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $res = $conn->query("SELECT username, role FROM accounts WHERE id=$id");
    $row = $res->fetch_assoc();
    $username = $row['username'];
    $role     = $row['role'];

    if ($conn->query("DELETE FROM accounts WHERE id=$id") === TRUE) {
        $activity = "Deleted account: $username ($role)";
        $conn->query("INSERT INTO admin_activity (module, activity, status) 
                      VALUES ('CRM', '$activity', 'Success')");
    }
    header("Location: CRM.php?deleted=1");
    exit;
}

/* Fetch Accounts */
$result = $conn->query("SELECT * FROM accounts ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | CORE3 Customer Relationship & Business Control</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --tertiary-color: #f43a3aff;
            --dark-bg: #1a1a2e;
            --dark-card: #16213e;
            --text-light: #f8f9fa;
            --text-dark: #212529;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --border-radius: 0.35rem;
            --shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            overflow-x: hidden;
            background-color: var(--secondary-color);
            color: var(--text-dark);
        }

        body.dark-mode {
            --secondary-color: var(--dark-bg);
            background-color: var(--secondary-color);
            color: var(--text-light);
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: #2c3e50;
            color: white;
            padding: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            transform: translateX(0);
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar .logo {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .logo img {
            max-width: 100%;
            height: auto;
        }

        .system-name {
            padding: 0.5rem 1.5rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }

        .sidebar a {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 3px solid white;
        }

        .admin-feature {
            background-color: rgba(0, 0, 0, 0.1);
        }

        /* Main Content */
        .content {
            margin-left: var(--sidebar-width);
            padding: 20px;
        }

        .content.expanded {
            margin-left: 0;
        }

        /* Header */
        .header {
            background-color: white;
            padding: 1rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dark-mode .header {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .hamburger {
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        .system-title {
            color: var(--primary-color);
            font-size: 1rem;
        }


        /* Table Section */
        .table-section {
            position: relative;
            background-color: white;
            padding: 1.3rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .dark-mode .table-section {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .dark-mode th,
        .dark-mode td {
            border-bottom-color: #3a4b6e;
        }

        thead {
            background-color: var(--primary-color);
            color: white;
        }

        .table-section1 {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .table-head input,
        .table-head select {
            width: 300px;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            margin-right: 1.5rem;
            background-color: white;
        }

        .dark-mode .table-head input,
        .dark-mode .table-head select {
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }

        .table-button {
            position: absolute;
            right: 25px;
        }

        .btn {
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }

        .toggle-table-btn {
            background-color: var(--primary-color);
            color: white;
        }

        .toggle-table-btn:hover {
            background-color: #3a5bc7;
        }

        /* The Modal */
        .modal-section {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            position: absolute;
            right: 10rem;
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #ddd;
            width: 70%;
        }

        .dark-mode .modal-content {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .add-form {
            margin-bottom: 1rem;
        }

        .add-form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .add-form input,
        .add-form select,
        .add-form textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .dark-mode .add-form input,
        .dark-mode .add-form select {
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }

        .btn-add {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-add:hover {
            background-color: #3a5bc7;
        }

        .btn-cancel {
            background-color: var(--tertiary-color);
            color: white;
        }

        .btn-cancel:hover {
            background-color: #e50d0dff;
        }

        .username,
        .email,
        .role {
            width: 150px;
            padding: 0.2rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
            background-color: white;
        }

        .dark-mode td input,
        .dark-mode td select {
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }


        .Eupdate {
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            background-color: #4fcbdeff;
            color: white;
        }

        .Eupdate:hover {
            background-color: #15c8e3ff;
        }

        .Ecancel,
        .delete {
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            background-color: var(--tertiary-color);
            color: white;
            text-decoration-line: none;
        }

        .Ecancel,
        .delete:hover {
            background-color: #e50d0dff;
        }

        .edit {
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            background-color: #1a629dff;
            color: white;
            text-decoration-line: none;
        }

        .edit:hover {
            background-color: #0476d3ff;
        }

        /* Make SweetAlert smaller */
        .swal-small {
            width: 400px !important;
            /* shrink width */
            font-size: 0.85rem !important;
            /* smaller text */
            padding: 0.5rem !important;
        }

        .swal-small .swal2-title {
            font-size: 0.1rem !important;
            /* smaller title */
        }

        .swal-small .swal2-html-container {
            font-size: 0.85rem !important;
            /* smaller body text */
        }



        /* Theme Toggle */
        .theme-toggle-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .theme-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .theme-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--primary-color);
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        /* Responsive */
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
        <div class="logo">
            <img src="rem.png" alt="SLATE Logo">
        </div>
        <div class="system-name">CORE TRANSACTION 3</div>
        <a href="admin.php">Dashboard</a>
        <a href="CRM.php" class="active">Customer Relationship Management</a>
        <a href="CSM.php">Contract & SLA Monitoring</a>
        <a href="E-Doc.php">E-Documentations & Compliance Manager</a>
        <a href="BIFA.php">Business Intelligence & Freight Analytics</a>
        <a href="CPN.php">Customer Portal & Notification Hub</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content" id="mainContent">
        <div class="header">
            <div class="hamburger" id="hamburger">☰</div>
            <div>
                <h1>Customer Relationship Management</h1>
            </div>
            <div class="theme-toggle-container">
                <span class="theme-label">Dark Mode</span>
                <label class="theme-switch">
                    <input type="checkbox" id="adminThemeToggle">
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <div class="table-section">
            <div class="table-section1">
                <div class="table-head">
                    <input type="search" class="control" id="searchInput" placeholder="Search username...">
                </div>

                <div class="table-head">
                    <select class="select" id="filterRole">
                        <option value="">Filter by Role</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>

            </div>

            <table id="AccountsTable">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="accountData">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php if (isset($_GET['edit']) && $_GET['edit'] == $row['id']): ?>
                            <tr>
                                <form method="POST">
                                    <td><input class="username" type="text" name="username" value="<?= htmlspecialchars($row['username']); ?>" required></td>
                                    <td><input class="email" type="email" name="email" value="<?= htmlspecialchars($row['email']); ?>" required></td>
                                    <td>
                                        <select class="role" name="role" required>
                                            <option value="admin" <?= $row['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            <option value="user" <?= $row['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                        <button type="submit" name="update" class="Eupdate">Save</button>
                                        <a href="CRM.php" class="Ecancel">Cancel</a>
                                    </td>
                                </form>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td><?= htmlspecialchars($row['username']); ?></td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                                <td><?= htmlspecialchars($row['role']); ?></td>
                                <td>
                                    <a href="CRM.php?edit=<?= $row['id']; ?>" class="edit">Edit</a>
                                    <a href="CRM.php?delete=<?= $row['id']; ?>" class="delete">Delete</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endwhile; ?>
                </tbody>
            </table>




            <div class="modal-section" id="addmodal">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h3>Add New Account</h3>
                        </div>
                        <div class="modal-body">
                            <div class="add-form">
                                <label>Username</label>
                                <input type="text" name="username" required>
                            </div>
                            <div class="add-form">
                                <label>Email</label>
                                <input type="email" name="email" required>
                            </div>
                            <div class="add-form">
                                <label>Password</label>
                                <input type="password" name="password" required>
                            </div>
                            <div class="add-form">
                                <label>Role</label>
                                <select name="role" required>
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="cancel" class="btn btn-cancel">Cancel</button>
                            <button type="submit" name="add" class="btn btn-add">Add Account</button>
                        </div>
                    </form>
                </div>
            </div>



            <script>
    initDarkMode("adminThemeToggle", "adminDarkMode");

    document.getElementById('hamburger').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
        document.getElementById('mainContent').classList.toggle('expanded');
    });

    /* modal */
    var modal = document.getElementById("addmodal");
    var btn = document.getElementById("openAddModal"); // ✅ fix id
    var cancelBtn = document.getElementById("cancel"); // ✅ fix cancel

    if (btn) {
        btn.onclick = function() {
            modal.style.display = "block";
        }
    }
    cancelBtn.onclick = function() {
        modal.style.display = "none";
    }
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    /* search/filter */
    const searchInput = document.getElementById('searchInput');
    const filterRole = document.getElementById('filterRole');
    const tbody = document.getElementById('accountData');

    function getCellText(td) {
        const input = td.querySelector('input, select');
        return input ? input.value.toLowerCase() : td.textContent.toLowerCase();
    }

    function filterTable() {
        const searchValue = searchInput.value.toLowerCase();
        const roleValue = filterRole.value.toLowerCase();

        Array.from(tbody.rows).forEach(row => {
            const usernameText = getCellText(row.cells[0]); // Username
            const roleText = getCellText(row.cells[2]);     // Role

            const matchesSearch = usernameText.includes(searchValue);
            const matchesRole = roleValue === "" || roleText === roleValue;

            row.style.display = (matchesSearch && matchesRole) ? "" : "none";
        });
    }

    searchInput.addEventListener('input', filterTable);
    filterRole.addEventListener('change', filterTable);

    // SweetAlert for delete confirmation
    document.querySelectorAll('.delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');

            Swal.fire({
                title: 'Are you sure?',
                text: "This account will be permanently deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                customClass: { popup: 'swal-small' }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });

    // ✅ Success alerts (Add, Update, Delete)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        Swal.fire({
            title: 'Added!',
            text: 'Account has been added successfully.',
            icon: 'success',
            customClass: { popup: 'swal-small' }
        });
    }
    if (urlParams.has('updated')) {
        Swal.fire({
            title: 'Updated!',
            text: 'Account has been updated successfully.',
            icon: 'success',
            customClass: { popup: 'swal-small' }
        });
    }
    if (urlParams.has('deleted')) {
        Swal.fire({
            title: 'Deleted!',
            text: 'Account has been deleted successfully.',
            icon: 'success',
            customClass: { popup: 'swal-small' }
        });
    }
</script>

</body>

</html>