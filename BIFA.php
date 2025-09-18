<?php
include("darkmode.php");
include("connection.php");
include('session.php');
requireRole('admin');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | CORE3 Customer Relationship & Business Control</title>
    <!-- ✅ Use Chart.js v4 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            padding: .5rem 1.5rem;
            font-size: .9rem;
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }

        .sidebar a {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            padding: .75rem 1.5rem;
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

        .content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s ease;
        }

        .content.expanded {
            margin-left: 0;
        }

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
            padding: .5rem;
        }

        .system-title {
            color: var(--primary-color);
            font-size: 1rem;
        }

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

        .chartarea {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .chart1,
        .chart2,
        .chart3,
        .chart4 {
            background-color: white;
            text-align: center;
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1rem;
        }

        .dark-mode .chart1,
        .dark-mode .chart2,
        .dark-mode .chart3,
        .dark-mode .chart4 {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .chart1 {
            height: 360px;
            width: 50%;
        }

        .chart2 {
            height: 360px;
            width: 50%;
        }

        .chart3 {
            height: 360px;
            width: 50%;
        }

        .chart4 {
            height: 360px;
            width: 50%;
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
        <div class="logo"><img src="rem.png" alt="SLATE Logo"></div>
        <div class="system-name">CORE TRANSACTION 3</div>
        <a href="admin.php">Dashboard</a>
        <a href="CRM.php">Customer Relationship Management</a>
        <a href="CSM.php">Contract & SLA Monitoring</a>
        <a href="E-Doc.php">E-Documentations & Compliance Manager</a>
        <a href="BIFA.php" class="active">Business Intelligence & Freight Analytics</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content" id="mainContent">
        <div class="header">
            <div class="hamburger" id="hamburger">☰</div>
            <h1>Business Intelligence & Freight Analytics</h1>
            <div class="theme-toggle-container">
                <span class="theme-label">Dark Mode</span>
                <label class="theme-switch">
                    <input type="checkbox" id="adminThemeToggle">
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <div class="dashboard-cards">
            <div class="card">
                <h3>Total Shipments</h3>
                <div class="stat-value" id="totalShipments">0</div>
            </div>
            <div class="card">
                <h3>On-Time Delivery</h3>
                <div class="stat-value" id="onTimeDelivery">0</div>
            </div>
            <div class="card">
                <h3>Active Routes</h3>
                <div class="stat-value" id="activeRoutes">0</div>
            </div>
            <div class="card">
                <h3>Current Delays</h3>
                <div class="stat-value" id="currentDelays">0</div>
            </div>
        </div>

        <div class="chartarea">
            <div class="chart1">
                <h3>Shipments (Last 7 Dyas)</h3>
                <canvas id="Shipmentchart"></canvas>
            </div>
            <div class="chart2">
                <h3>Current Delay Reasons</h3>
                <canvas id="Currentdelaychart"></canvas>
            </div>
        </div>

        <div class="chartarea">
            <div class="chart3">
                <h3>Cost Breakdown (Monthly)</h3>
                <canvas id="costchart"></canvas>
            </div>
            <div class="chart4">
                <h3>Total Routes by Volume</h3>
                <canvas id="totalrouteschart"></canvas>
            </div>
        </div>

        <script>
            initDarkMode("adminThemeToggle", "adminDarkMode");

            document.getElementById('hamburger').addEventListener('click', () => {
                document.getElementById('sidebar').classList.toggle('collapsed');
                document.getElementById('mainContent').classList.toggle('expanded');
            });

            const ShipmentCtx = document.getElementById("Shipmentchart").getContext("2d");
            new Chart(ShipmentCtx, {
                type: "line",
                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul"],
                    datasets: [{
                        label: "Shipments",
                        data: [650, 700, 750, 800, 850, 900, 950],
                        fill: false,
                        borderColor: "rgba(86, 120, 177, 0.92)",
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });

            const DelayCtx = document.getElementById("Currentdelaychart").getContext("2d");
            new Chart(DelayCtx, {
                type: "pie",
                data: {
                    labels: ["Weather", "Traffic", "Customs", "Mechanical"],
                    datasets: [{
                        data: [1, 1, 1, 1],
                        backgroundColor: ["#c3f13a", "#b928ea", "#1e84d7", "#d7561eff"]
                    }]
                }
            });

            const costCtx = document.getElementById("costchart").getContext("2d");
            new Chart(costCtx, {
                type: "pie",
                data: {
                    labels: ["Fuel", "Labor", "Carrier", "Tolls", "Other"],
                    datasets: [{
                        data: [55, 49, 44, 24, 15],
                        backgroundColor: ["#b91d47", "#00aba9", "#2b5797", "#e8c3b9", "#1e7145"]
                    }]
                },
                options: {
                    plugins: {
                        title: {
                            display: true,
                        }
                    }
                }
            });

            const routesCtx = document.getElementById("totalrouteschart").getContext("2d");
            new Chart(routesCtx, {
                type: "bar",
                data: {
                    labels: ["Manila-Cebu", "Manila-Davao", "Cebu-Davao", "Manila-HK"],
                    datasets: [{
                        label: "Shipments",
                        data: [55, 49, 44, 24],
                        backgroundColor: ["red", "green", "blue", "brown"]
                    }]
                },
                options: {
                    plugins: {
                        title: {
                            display: true,
                        }
                    }
                }
            });
        </script>
    </div>
</body>

</html>