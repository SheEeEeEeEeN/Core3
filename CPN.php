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
        .searchnotif-section {
            position: relative;
            background-color: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .dark-mode .searchnotif-section {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .portalcontent {
            display: flex;
        }

        .search-control input {
            width: 770px;
            padding: 0.4rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            margin-right: 1.5rem;
        }

        .dark-mode .search-control input{
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }

        .search-priorities select {
            width: 400px;
            padding: 0.4rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            margin-right: 1.5rem;
            background-color: white;
        }
        .dark-mode .search-priorities select{
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
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
        <a href="CRM.php">Customer Relationship Management</a>
        <a href="CSM.php">Contract & SLA Monitoring</a>
        <a href="E-Doc.php">E-Documentations & Compliance Manager</a>
        <a href="BIFA.php">Business Intelligence & Freight Analytics</a>
        <a href="CPN.php" class="active">Customer Portal & Notification Hub</a>
        <a href="login.php">Logout</a>
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
                    <input type="checkbox" id="themeToggle">
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <div class="searchnotif-section">
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
            <div class="notif-section">
                    <h2>Unread Notification</h2><br>
                    <h2>Read Notification</h2>
                </div>
        </div>

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
    </script>
</body>

</html>