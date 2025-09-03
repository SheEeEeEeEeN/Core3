<?php
include 'connection.php';
include('session.php');
requireRole('admin');

if (isset($_GET['action']) && $_GET['action'] === 'stats') {
    /* Total contracts */
    $sql = "SELECT COUNT(*) AS total FROM csm";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $totalContracts = $row['total'];

    /* Active contract */
    $sql = "SELECT COUNT(*) AS total_active FROM csm WHERE status = 'Active'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $totalActive = $row['total_active'];

    /* Expiring soon */
    $sql = "SELECT COUNT(*) AS expiring_soon 
            FROM csm 
            WHERE end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $expiringSoon = $row['expiring_soon'];

    /* Total compliant */
    $sql = "SELECT COUNT(*) AS total_compliant 
            FROM csm 
            WHERE sla_compliance = 'Compliant'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $totalCompliant = $row['total_compliant'];

    // Return plain text (pipe-separated)
    echo "$totalContracts|$totalActive|$expiringSoon|$totalCompliant";
    exit;
}


/* Add contract */
$contract_limit = 100;
if (isset($_POST['add_contract'])) {
    $contract_id    = $conn->real_escape_string($_POST['contract_id']);
    $client_name    = $conn->real_escape_string($_POST['client_name']);
    $start_date     = $conn->real_escape_string($_POST['start_date']);
    $end_date       = $conn->real_escape_string($_POST['end_date']);
    $status         = $conn->real_escape_string($_POST['status']);
    $sla_compliance = $conn->real_escape_string($_POST['sla_compliance']);

    // Check total contracts
    $check_sql = "SELECT COUNT(*) AS total FROM csm";
    $result = $conn->query($check_sql);
    $row = $result->fetch_assoc();
    $total_contracts = $row['total'];

    // Delete oldest if limit reached
    if ($total_contracts >= $contract_limit) {
        $oldest_sql = "SELECT contract_id, client_name FROM csm ORDER BY start_date ASC LIMIT 1";
        $res_old = $conn->query($oldest_sql);
        $oldest = $res_old->fetch_assoc();

        $delete_sql = "DELETE FROM csm ORDER BY start_date ASC LIMIT 1";
        $conn->query($delete_sql);

        // Log delete due to limit
        $activity = "Deleted oldest contract (limit $contract_limit reached): {$oldest['contract_id']} - {$oldest['client_name']}";
        $conn->query("INSERT INTO admin_activity (module, activity, status) VALUES ('CSM', '$activity', 'Success')");
    }

    // Insert new contract
    $insert_sql = "INSERT INTO csm (contract_id, client_name, start_date, end_date, status, sla_compliance) 
                   VALUES ('$contract_id', '$client_name', '$start_date', '$end_date', '$status', '$sla_compliance')";

    if ($conn->query($insert_sql)) {
        // Log add
        $activity = "Added new contract: $contract_id - $client_name";
        $conn->query("INSERT INTO admin_activity (module, activity, status) VALUES ('CSM', '$activity', 'Success')");

        echo "<script>alert('Contract added successfully');</script>";
    } else {
        $errorMsg = $conn->error;
        $conn->query("INSERT INTO admin_activity (module, activity, status) VALUES ('CSM', 'Failed to add contract: $contract_id', 'Failed')");
        echo "Error: " . $errorMsg;
    }
}


/* Fetch all contracts */
$result = $conn->query("SELECT * FROM csm ORDER BY start_date DESC");
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

        /* Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card {
            background-color: white;
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
        }

        .dark-mode .card {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.75rem 0 rgba(58, 59, 69, 0.2);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* Select Section */
        .Select-section {
            background-color: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        .Contract-content {
            text-align: center;
        }

        .contract-form {
            text-align: start;
        }

        .C-form {
            display: flex;
            margin-top: 1rem;
        }

        .contract-form input,
        .contract-form select {
            width: 590px;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
            background-color: white;
            margin: 0.5rem 1rem 0 0;
        }

        .dark-mode .contract-form input,
        .dark-mode .contract-form select {
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }

        .btn {
            width: 260px;
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 1rem;
        }

        .addcontract {
            background-color: var(--primary-color);
            color: white;
        }

        .addcontract:hover {
            background-color: #3a5bc7;
        }

        .form input,
        .form select {
            width: 280px;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
            background-color: white;
        }

        .dark-mode .form input,
        .dark-mode .form select {
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }

        .dark-mode .Select-section {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        /* Table Section */
        .table-section1 {
            background-color: white;
            text-align: center;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        .dark-mode .table-section1 {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .table-section2 {
            background-color: white;
            text-align: center;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .dark-mode .table-section2 {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        table {
            width: 100%;
            margin-top: 1rem;
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
        <a href="admin">Dashboard</a>
        <a href="CRM">Customer Relationship Management</a>
        <a href="CSM" class="active">Contract & SLA Monitoring</a>
        <a href="E-Doc">E-Documentations & Compliance Manager</a>
        <a href="BIFA">Business Intelligence & Freight Analytics</a>
        <a href="CPN">Customer Portal & Notification Hub</a>
        <a href="logout">Logout</a>
    </div>

    <div class="content" id="mainContent">
        <div class="header">
            <div class="hamburger" id="hamburger">☰</div>
            <div>
                <h1>Contract & SLA Monitoring</h1>
            </div>
            <div class="theme-toggle-container">
                <span class="theme-label">Dark Mode</span>
                <label class="theme-switch">
                    <input type="checkbox" id="themeToggle">
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <div class="dashboard-cards">
            <div class="card">
                <h3>Total Contracts</h3>
                <div class="stat-value" id="totalContracts">0</div>
            </div>

            <div class="card">
                <h3>Active Contracts</h3>
                <div class="stat-value" id="totalActive"><?php echo $totalActive; ?></div>
            </div>

            <div class="card">
                <h3>Expiring Soon</h3>
                <div class="stat-value" id="expiringSoon"><?php echo $expiringSoon; ?></div>
            </div>

            <div class="card">
                <h3>SLA Compliance</h3>
                <div class="stat-value" id="totalCompliant"><?php echo $totalCompliant; ?></div>
            </div>
        </div>


        <div class="Select-section">
            <h3>Add New Contract</h3>
            <div class="Contract-content">
                <form method="POST">
                    <div class="C-form">
                        <div class="contract-form">
                            <h5>Contract ID</h5>
                            <input type="text" class="form-control" id="contract_id" name="contract_id" placeholder="Contract ID" required>
                        </div>
                        <div class="contract-form">
                            <h5>Client Name</h5>
                            <input type="text" class="form-control" id="client_name" name="client_name" placeholder="Client Name" required>
                        </div>
                    </div>
                    <div class="C-form">
                        <div class="contract-form">
                            <h5>Start Date</h5>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="contract-form">
                            <h5>End Date</h5>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>
                    <div class="C-form">
                        <div class="contract-form">
                            <h5>Status</h5>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="Active">Active</option>
                                <option value="Expired">Expired</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                        <div class="contract-form">
                            <h5>SLA Compliance</h5>
                            <select class="form-select" id="sla_compliance" name="sla_compliance" required>
                                <option value="">SLA Compliance</option>
                                <option value="Compliant">Compliant</option>
                                <option value="Non-Compliant">Non-Compliant</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="add_contract" class="btn addcontract">
                        Add Contract
                    </button>
                </form>
            </div>
        </div>


        <div class="table-section1">
            <h3>Contracts List</h3>
            <table id="contractsTable" class="table-selection1">
                <thead>
                    <tr>
                        <th>Contract ID</th>
                        <th>Client</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>SLA Compliance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['contract_id']); ?></td>
                            <td><?= htmlspecialchars($row['client_name']); ?></td>
                            <td><?= htmlspecialchars($row['start_date']); ?></td>
                            <td><?= htmlspecialchars($row['end_date']); ?></td>
                            <td><?= htmlspecialchars($row['status']); ?></td>
                            <td><?= htmlspecialchars($row['sla_compliance']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>


        <div class="table-section2">
            <h3>Additional Table</h3>
            <table id="additionalTable" class="table-selection2">
                <thead>
                    <tr>
                        <th>Column 1</th>
                        <th>Column 2</th>
                        <th>Column 3</th>
                        <th>Column 4</th>
                        <th>Column 5</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" style="text-align:center;">No data available</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const checkbox = document.getElementById("themeToggle");

        if (localStorage.getItem("darkMode") === "enabled") {
            document.body.classList.add("dark-mode");
            checkbox.checked = true;
        }

        checkbox.addEventListener("change", () => {
            if (checkbox.checked) {
                document.body.classList.add("dark-mode");
                localStorage.setItem("darkMode", "enabled");
            } else {
                document.body.classList.remove("dark-mode");
                localStorage.setItem("darkMode", "disabled");
            }
        });

        document.getElementById('hamburger').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('expanded');
        });

        function updateDashboard() {
            fetch('?action=stats')
                .then(response => response.text())
                .then(text => {
                    // Split values by pipe
                    const [totalContracts, totalActive, expiringSoon, totalCompliant] = text.split('|');

                    document.getElementById('totalContracts').textContent = totalContracts;
                    document.getElementById('totalActive').textContent = totalActive;
                    document.getElementById('expiringSoon').textContent = expiringSoon;
                    document.getElementById('totalCompliant').textContent = totalCompliant;
                })
                .catch(error => console.error('Error fetching stats:', error));
        }

        // Update immediately and every 5 seconds
        updateDashboard();
        setInterval(updateDashboard, 5000);
    </script>
</body>

</html>