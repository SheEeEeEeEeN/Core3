<?php include("darkmode.php");
include("connection.php");
include('session.php');
requireRole('admin') ?>
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
            transition: all 0.3s ease;
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

        .portalcontent {
            display: flex;
            margin-bottom: 1rem;
        }

        .search-control input {
            width: 400px;
            padding: 0.4rem;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            font-size: 1rem;
            border-right: 0;
            margin: 1rem 0 0.5rem 0;
        }

        .dark-mode .search-control input {
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }

        .search-priorities select {
            width: 225px;
            padding: 0.4rem;
            border: 1px solid #ddd;
            border-radius: 0 4px 4px 0;
            font-size: 1rem;
            border-left: 0;
            background-color: white;
            margin: 1rem 0 0.5rem 0;
        }

        .dark-mode .search-priorities select {
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }

        .portal-wrapper {
            display: flex;
            gap: 20px;
        }

        .notifications {
            flex: 1;
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .notifications h3 {
            margin: 1rem 0 1rem 0;
        }

        .dark-mode .notifications {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .notif-controls {
            margin-bottom: 1rem;
        }

        .notif-controls button {
            margin-right: 0.5rem;
            padding: 0.5rem 1rem;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        /* --- Updated Notification Styles --- */
        .notif-list .notif-item {
            padding: 1rem;
            background-color: #f7f7f7ff;
            border-radius: var(--border-radius);
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between; /* left vs right */
            align-items: center;
        }

        .dark-mode .notif-list .notif-item {
            background-color: #293b5eff;
            color: var(--text-light);
        }

        .notif-text {
            display: flex;
            flex-direction: column; /* stack title + priority */
        }

        .notif-item .time {
            font-size: 0.85rem;
            color: #888;
        }

        .notif-docs {
            padding: 2rem;
            background-color: #f7f7f7ff;
            border-radius: var(--border-radius);
            margin-bottom: 0.3rem;
        }

        .dark-mode .notif-docs {
            background-color: #293b5eff;
            color: var(--text-light);
        }

        .notif-item .priority {
            margin-left: 10px;
            font-size: 0.85rem;
        }

        .priority.high {
            color: red;
        }

        .priority.medium {
            color: orange;
        }

        .priority.low {
            color: green;
        }

        .summary {
            display: grid;
            grid-template-columns: auto auto auto;
            gap: 1rem;
        }

        .summary-box {
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .dark-mode .summary-box {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .customer-portal {
            height: 550px;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
            grid-column-start: 1;
            grid-column-end: 4;
        }

        .dark-mode .customer-portal {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .customer-portal p {
            margin-top: 1rem;
            font-size: 20px;
        }
        .customer-portal button {
            margin-right: 0.5rem;
            padding: 0.5rem 1rem;
            border: none;
            background: #46649eff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            margin: 0.5rem;
        }
        .customer-portal h3{
            margin: 1rem;
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
        <div class="logo"> <img src="rem.png" alt="SLATE Logo"> </div>
        <div class="system-name">CORE TRANSACTION 3</div>
        <a href="admin.php">Dashboard</a>
        <a href="CRM.php">Customer Relationship Management</a>
        <a href="CSM.php">Contract & SLA Monitoring</a>
        <a href="E-Doc.php">E-Documentations & Compliance Manager</a>
        <a href="BIFA.php">Business Intelligence & Freight Analytics</a>
        <a href="CPN.php" class="active">Customer Portal & Notification Hub</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="content" id="mainContent">
        <div class="header">
            <div class="hamburger" id="hamburger">☰</div>
            <div>
                <h1>Customer Portal & Notification Hub</h1>
            </div>
            <div class="theme-toggle-container">
                <span class="theme-label">Dark Mode</span>
                <label class="theme-switch">
                    <input type="checkbox" id="adminThemeToggle">
                    <span class="slider"></span>
                </label>
            </div>
        </div>
        <div class="searchnotif-section">
            <div class="portal-wrapper">
                <!-- Left Side Notifications -->
                <div class="notifications">
                    <!-- Top Controls -->
                    <h1>Notification</h1>
                    <div class="portalcontent">
                        <div class="search-control">
                            <input type="search" class="control" id="searchInput" placeholder="Search Notification...">
                        </div>
                        <div class="search-priorities">
                            <select class="priorities" id="priorities">
                                <option value="">All Priorities</option>
                                <option value="high">High Priority</option>
                                <option value="medium">Medium Priority</option>
                                <option value="low">Low Priority</option>
                            </select>
                        </div>
                    </div>
                    <div class="notif-controls">
                        <button style="background: #d73636ff;">Unread</button> <button style="background: #5092d9ff;">Read</button> <button style="background: #faa1a1ff;">Mark as Read</button> <button style="background: #47a522ff;">Archive</button>
                    </div>
                    <!-- Notification List -->
                    <div class="notif-list">
                        <div class="notif-item">
                            <div class="notif-text">
                                <strong>System Update</strong>
                                <span class="priority medium">Medium</span>
                            </div>
                            <span class="time">2 hours ago</span>
                        </div>

                        <div class="notif-item">
                            <div class="notif-text">
                                <strong>SLA Alert</strong>
                                <span class="priority high">High</span>
                            </div>
                            <span class="time">Yesterday</span>
                        </div>

                        <div class="notif-item">
                            <div class="notif-text">
                                <strong>Document Reminder</strong>
                                <span class="priority low">Low</span>
                            </div>
                            <span class="time">2 days ago</span>
                        </div>
                    </div>
                    <!-- Current Announcements -->
                    <h3>Current Announcements</h3>
                    <div class="notif-docs">
                        <strong>My Documents</strong>
                        <p>Compliance Cert will expire in 5 days</p>
                    </div>
                </div>
                <!-- Right Side Summary -->
                <div class="summary">
                    <div class="summary-box">
                        <p>Unread Notifications</p>
                        <h2>3</h2>
                    </div>
                    <div class="summary-box">
                        <p>Pending SLA Contracts</p>
                        <h2>1</h2>
                    </div>
                    <div class="summary-box">
                        <p>Active Shipments</p>
                        <h2>2</h2>
                    </div>
                    <!-- Customer Portal Section -->
                    <div class="customer-portal">
                        <h2>Customer Portal</h2>
                        <p>
                            <strong>John Doe</strong><br>
                            ABC Corp.<br>
                            123-456-7290<br>
                            johndoe@example.com<br>
                            1234 Main St, Anytown, USA
                        </p>
                        <h3>Contracts & SLA Snapshot</h3>
                        <ul>
                            <li style="color:green">● Active</li>
                            <li style="color:orange">● Expiring Soon</li>
                            <li style="color:red">● Breach Alert</li>
                        </ul>
                        <h3>Documents & Compliance</h3>
                        <button>My Documents</button>
                    </div>
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
    </script>
</body>

</html>
