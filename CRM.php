<?php
include("darkmode.php");
include 'connection.php';
include('session.php');
requireRole('admin');

/* Add New Customer */
if (isset($_POST['add'])) {
    $customer_name = $conn->real_escape_string($_POST['customer_name']);
    $company       = $conn->real_escape_string($_POST['company']);
    $email         = $conn->real_escape_string($_POST['email']);
    $phone         = $conn->real_escape_string($_POST['phone']);
    $status        = $conn->real_escape_string($_POST['status']);

    $sql = "INSERT INTO crm (customer_name, company, email, phone, status) 
            VALUES ('$customer_name', '$company', '$email', '$phone', '$status')";

    if ($conn->query($sql) === TRUE) {
        // Log to admin_activity
        $activity = "Added new customer: $customer_name ($company)";
        $conn->query("INSERT INTO admin_activity (module, activity, status) 
                      VALUES ('CRM', '$activity', 'Success')");

        header("Location: CRM.php?success=1");
        exit();
    } else {
        $errorMsg = $conn->error;
        $conn->query("INSERT INTO admin_activity (module, activity, status) 
                      VALUES ('CRM', 'Add customer failed: $customer_name', 'Failed')");
        echo "Error: " . $errorMsg;
    }
}

/* Update Customer */
if (isset($_POST['update'])) {
    $id            = (int) $_POST['id'];
    $customer_name = $conn->real_escape_string($_POST['customer_name']);
    $company       = $conn->real_escape_string($_POST['company']);
    $email         = $conn->real_escape_string($_POST['email']);
    $phone         = $conn->real_escape_string($_POST['phone']);
    $status        = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE crm 
            SET customer_name='$customer_name', 
                company='$company', 
                email='$email', 
                phone='$phone',
                status='$status'
            WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        $activity = "Updated customer: $customer_name ($company)";
        $conn->query("INSERT INTO admin_activity (module, activity, status) 
                      VALUES ('CRM', '$activity', 'Success')");

        header("Location: CRM.php?updated=1");
        exit();
    } else {
        $errorMsg = $conn->error;
        $conn->query("INSERT INTO admin_activity (module, activity, status) 
                      VALUES ('CRM', 'Update failed for ID $id', 'Failed')");
        echo "Error: " . $errorMsg;
    }
}

/* Delete Customer */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    // Fetch customer before delete (for logging)
    $res = $conn->query("SELECT customer_name, company FROM crm WHERE id=$id");
    $row = $res->fetch_assoc();
    $customer_name = $row['customer_name'];
    $company       = $row['company'];

    if ($conn->query("DELETE FROM crm WHERE id=$id") === TRUE) {
        $activity = "Deleted customer: $customer_name ($company)";
        $conn->query("INSERT INTO admin_activity (module, activity, status) 
                      VALUES ('CRM', '$activity', 'Success')");
    } else {
        $conn->query("INSERT INTO admin_activity (module, activity, status) 
                      VALUES ('CRM', 'Delete failed for customer ID $id', 'Failed')");
    }

    header("Location: CRM.php?deleted=1");
    exit;
}

/* Fetch Customers */
$result = $conn->query("SELECT * FROM crm ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | CORE3 Customer Relationship & Business Control</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
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

        .Cname,
        .Ccompany,
        .Cemail,
        .Cphone,
        .Sstatus {
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
            font-size: 0.9rem;
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
            font-size: 0.9rem;
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
            font-size: 0.9rem;
            cursor: pointer;
            background-color: #1a629dff;
            color: white;
            text-decoration-line: none;
        }

        .edit:hover {
            background-color: #0476d3ff;
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
                    <input type="search" class="control" id="searchInput" placeholder="Search customer...">
                </div>

                <div class="table-head">
                    <select class="select" id="filterStatus">
                        <option value="">Filter by Status</option>
                        <option value="Active">Active</option>
                        <option value="Prospect">Prospect</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <display class="table-button">
                    <button id="addM" class="btn toggle-table-btn">Add New Employee</button>
            </div>


            <table id="CustomersTable">
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Last Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="customerData">
                    <?php
                    // Fetch customers newest first
                    $sql = "SELECT * FROM crm ORDER BY id DESC";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()):
                    ?>
                        <?php if (isset($_GET['edit']) && $_GET['edit'] == $row['id']): ?>
                            <tr>
                                <form method="POST">
                                    <td><input type="text" class="Cname" name="customer_name" value="<?= htmlspecialchars($row['customer_name']); ?>" required></td>
                                    <td><input type="text" class="Ccompany" name="company" value="<?= htmlspecialchars($row['company']); ?>" required></td>
                                    <td><input type="email" class="Cemail" name="email" value="<?= htmlspecialchars($row['email']); ?>" required></td>
                                    <td><input type="text" class="Cphone" name="phone" value="<?= htmlspecialchars($row['phone']); ?>"></td>
                                    <td>
                                        <select class="Sstatus" name="status" required>
                                            <option value="Active" <?= $row['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="Prospect" <?= $row['status'] == 'Prospect' ? 'selected' : ''; ?>>Prospect</option>
                                            <option value="Inactive" <?= $row['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </td>
                                    <td><?= htmlspecialchars($row['last_contract']); ?></td>
                                    <td>
                                        <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                        <button type="submit" class="Eupdate" name="update">Save</button>
                                        <a href="CRM.php" class="Ecancel">Cancel</a>
                                    </td>
                                </form>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td><?= htmlspecialchars($row['customer_name']); ?></td>
                                <td><?= htmlspecialchars($row['company']); ?></td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                                <td><?= htmlspecialchars($row['phone']); ?></td>
                                <td><?= htmlspecialchars($row['status']); ?></td>
                                <td><?= htmlspecialchars($row['last_contract']); ?></td>
                                <td>
                                    <a href="CRM.php?edit=<?= $row['id']; ?>" class="edit">Edit</a>
                                    <a href="CRM.php?delete=<?= $row['id']; ?>" onclick="return confirm('Delete this record?')" class="delete">Delete</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endwhile; ?>
                </tbody>
            </table>




            <div class="modal-section" id="addmodal">
                <div class="modal-content">
                    <form class="modal-content1" method="POST">
                        <div class="modal-header">
                            <h3 class="modal-title" id="addCustomerModalLabel">Add New Customer</h3>
                        </div>
                        <div class="modal-body">
                            <div class="add-form">
                                <label>Customer Name</label>
                                <input type="text" class="form-control" name="customer_name" required>
                            </div>
                            <div class="add-form">
                                <label>Company</label>
                                <input type="text" class="form-control" name="company" required>
                            </div>
                            <div class="add-form">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="add-form">
                                <label>Phone</label>
                                <input type="text" class="form-control" name="phone" required>
                            </div>
                            <div class="add-form">
                                <label>Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="Active">Active</option>
                                    <option value="Prospect">Prospect</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="cancel" class="btn btn-cancel">Cancel</button>
                            <button type="submit" name="add" class="btn btn-add">Add Customer</button>
                        </div>
                    </form>
                </div>
            </div>
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
        var btn = document.getElementById("addM");
        var span = document.getElementsByClassName("cancel")[0];
        btn.onclick = function() {
            modal.style.display = "block";
        }
        cancel.onclick = function() {
            modal.style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
        /* search/filter */

        const searchInput = document.getElementById('searchInput');
        const filterStatus = document.getElementById('filterStatus');
        const tbody = document.getElementById('customerData');

        // Helper: get text from td or input
        function getCellText(td) {
            const input = td.querySelector('input, select');
            return input ? input.value.toLowerCase() : td.textContent.toLowerCase();
        }

        // Filter table function
        function filterTable() {
            const searchValue = searchInput.value.toLowerCase();
            const statusValue = filterStatus.value.toLowerCase();

            Array.from(tbody.rows).forEach(row => {
                const nameText = getCellText(row.cells[0]); // Customer Name
                const statusText = getCellText(row.cells[4]); // Status column

                const matchesSearch = nameText.includes(searchValue);
                const matchesStatus = statusValue === "" || statusText === statusValue;

                row.style.display = (matchesSearch && matchesStatus) ? "" : "none";
            });
        }

        // Event listeners
        searchInput.addEventListener('input', filterTable);
        filterStatus.addEventListener('change', filterTable);
    </script>
</body>

</html>