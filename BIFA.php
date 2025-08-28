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

        /* Dashboard Cards */
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

        /* Table Section */
        .table-section {
            position: relative;
            background-color: white;
            padding: 1.5rem;
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

        /* Select Section */
        .Select-section {
            background-color: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        .Select-section1 {
            display: flex;
            text-align: center;
            justify-content: space-between;
        }

        .form input,
        .form select {
            width: 390px;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
            background-color: white;
            margin-top: 0.5rem;
        }
        .dark-mode .form input,
        .dark-mode .form select{
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }

        .dark-mode .Select-section {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        /* Charts */
        .chartarea {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .dark-mode .area {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .chart1 {
            height: 360px;
            width: 65%;
            background-color: white;
            text-align: center;
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1rem;
        }

        .dark-mode .chart1 {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .chart2 {
            height: 360px;
            width: 35%;
            background-color: white;
            text-align: center;
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1rem;
        }

        .dark-mode .chart2 {
            background-color: var(--dark-card);
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
        <a href="BIFA.php" class="active">Business Intelligence & Freight Analytics</a>
        <a href="CPN.php">Customer Portal & Notification Hub</a>
        <a href="login.php">Logout</a>
    </div>

    <div class="content" id="mainContent">
        <div class="header">
            <div class="hamburger" id="hamburger">☰</div>
            <div>
                <h1>Business Intelligence & Freight Analytics</h1>
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
                <h3>Total Shipments</h3>
                <div class="stat-value" id="Total Shipments">0</div>
                <div class="stat-label">Loading data...</div>
            </div>

            <div class="card">
                <h3>On-Time Delivery</h3>
                <div class="stat-value" id="On-Time Delivery">0</div>
                <div class="stat-label">Loading data...</div>
            </div>

            <div class="card">
                <h3>Delayed Shipments</h3>
                <div class="stat-value" id="Delayed Shipments">0</div>
                <div class="stat-label">Loading data...</div>
            </div>

            <div class="card">
                <h3>Total Revenue</h3>
                <div class="stat-value" id="Total Revenue">0</div>
                <div class="stat-label">Loading data...</div>
            </div>
        </div>

        <div class="Select-section">
            <div class="Select-section1">
                <div class="form">
                    <h4>From</h4>
                    <input type="date" id="From" name="From">
                </div>
                <div class="form">
                    <h4>To</h4>
                    <input type="date" id="To" name="To">
                </div>
                <div class="form">
                    <h4>Region</h4>
                    <select class="status" id="Status">
                        <option value="">All</option>
                        <option value="Active">Active</option>
                        <option value="Expired">Expired</option>
                        <option value="Pending Renewal">Pending Renewal</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="chartarea">
            <div class="chart1">
                <h3>Monthly Shipment</h3>
                <canvas id="monthlychart" style="height:200px;width:500px;"></canvas>
            </div>

            <div class="chart2">
                <h3>Revenue by Region</h3>
                <canvas id="revenuechart" style="height:110px;width:150px;"></canvas>
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

            const xValues = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul"];
            const yValues = [];

            new Chart("monthlychart", {
                type: "line",
                data: {
                    labels: xValues,
                    datasets: [{
                        label:'Shipments',
                        fill: false,
                        lineTension: 0,
                        backgroundColor: "rgba(0,0,255,1.0)",
                        borderColor: "rgba(86, 120, 177, 0.92)",
                        data: yValues
                    }]
                },
                options: {
                    legend: {
                        display: true
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                min: 500,
                                max: 1100
                            }
                        }],
                    }
                }
            });

            const aValues = ["Asia", "Europe", "North America"];
            const bValues = [1, 1, 1];
            const barColors = [
                "#c3f13ae4",
                "#b928eaff",
                "#1e84d7ff",
            ];

            new Chart("revenuechart", {
                type: "pie",
                data: {
                    labels: aValues,
                    datasets: [{
                        backgroundColor: barColors,
                        data: bValues
                    }]
                },
                options: {
                    title: {
                        display: true,
                    }
                }
            });
        </script>
</body>

</html>