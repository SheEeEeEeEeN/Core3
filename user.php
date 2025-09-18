<?php
include("darkmode.php");
include("connection.php");
include('session.php');
requireRole('user')

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard | Core 3</title>
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

    /* User Icon */
    .user_icon {
      cursor: pointer;
      padding: 0.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .user_icon img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
    }

    .user_dropdown {
      display: none;
      position: absolute;
      top: 60px;
      right: 0;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
      min-width: 150px;
      z-index: 2000;
      overflow: hidden;
    }

    .user_dropdown a {
      display: block;
      padding: 10px 15px;
      text-decoration: none;
      color: #333;
      font-size: 0.9rem;
      transition: background 0.3s;
    }

    .user_dropdown a:hover {
      background-color: #f0f0f0;
    }

    .dark-mode .user_dropdown {
      background-color: var(--dark-card);
      color: var(--text-light);
    }

    .dark-mode .user_dropdown a {
      color: var(--text-light);
    }

    .dark-mode .user_dropdown a:hover {
      background-color: #2a3a5a;
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

    /*Chartarea*/
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
      border: none;
      text-align: center;
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
      border: none;
      text-align: center;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      padding: 1rem;
    }

    .dark-mode .chart2 {
      background-color: var(--dark-card);
      color: var(--text-light);
    }


    /* Table Section */
    .table-section {
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
    <a href="user.php" class="active">Dashboard</a>
    <a href="trackship.php">Track Shipment</a>
    <a href="bookship.php">Book Shipment</a>
    <a href="shiphistory.php">Shipment History</a>
    <a href="CPN.php">Customer Portal & Notification Hub</a>
    <a href="feedback.php">Feedback</a>
  </div>

  <div class="content" id="mainContent">
    <div class="header">
      <div class="hamburger" id="hamburger">☰</div>
      <div>
        <h1>User Dashboard <span class="system-title"></span></h1>
      </div>
      <div class="theme-toggle-container">
        <div class="user_icon" id="userIcon">
          <img src="user.png" alt="User">
          <div class="user_dropdown" id="userDropdown">
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
          </div>
        </div>
        <span class="theme-label">Dark Mode</span>
        <label class="theme-switch">
          <input type="checkbox" id="userThemeToggle">
          <span class="slider"></span>
        </label>
      </div>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <h3>Total Shipments</h3>
        <div class="stat-value" id="totalEmployees">0</div>
      </div>
      <div class="card">
        <h3>In Transit</h3>
        <div class="stat-value" id="activeEmployees">0</div>
      </div>
      <div class="card">
        <h3>Delivered</h3>
        <div class="stat-value" id="activeEmployees">0</div>
      </div>
      <div class="card">
        <h3>Pending</h3>
        <div class="stat-value" id="activeEmployees">0</div>
      </div>
    </div>

    <div class="chartarea">
      <div class="chart1">
        <h3>Shipment Trends</h3>
        <canvas id="myChart1" style="height:200px;width:500px;"></canvas>
      </div>

      <div class="chart2">
        <h3>Status Breakdown</h3>
        <canvas id="myChart2" style="height:110px;width:150px;"></canvas>
      </div>
    </div>

    <div class="table-section">
      <h3>Recent Shipments</h3>
      <table id="shipmentTable">
        <thead>
          <tr>
            <th>Tracking No.</th>
            <th>Origin</th>
            <th>Destination</th>
            <th>Status</th>
            <th>Booked Date</th>
          </tr>
        </thead>
        <tbody id="shipmentTableBody">
          <!-- Employee data will be loaded here -->
        </tbody>
      </table>
    </div>
  </div>

  <script>
    initDarkMode("userThemeToggle", "userDarkMode");

    document.getElementById('hamburger').addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    });

    const xValues = ["Aug 25", "Aug 27", "Aug 30", "Sept 1", "Sept 2", "Sept 4"];
    const yValues = [7, 8, 8, 9, 9, 9];

    new Chart("myChart1", {
      type: "line",
      data: {
        labels: xValues,
        datasets: [{
          fill: false,
          lineTension: 0,
          backgroundColor: "rgba(0,0,255,1.0)",
          borderColor: "rgba(0,0,255,0.1)",
          data: yValues
        }]
      },
      options: {
        legend: {
          display: false
        },
        scales: {
          yAxes: [{
            ticks: {
              min: 6,
              max: 16
            }
          }],
        }
      }
    });

    var aValues = ["In Transit", "Delivered", "Pending"];
    var bValues = [55, 49, 44];
    var barColors = [
      "#1e7145",
      "#d2f50bff",
      "#00aba9"
    ];

    new Chart("myChart2", {
      type: "pie",
      data: {
        labels: aValues,
        datasets: [{
          label: 'Shipments',
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

    // Toggle dropdown
    const userIcon = document.getElementById("userIcon");
    const userDropdown = document.getElementById("userDropdown");

    userIcon.addEventListener("click", () => {
      userDropdown.style.display =
        userDropdown.style.display === "block" ? "none" : "block";
    });

    // Close dropdown if clicking outside
    document.addEventListener("click", (e) => {
      if (!userIcon.contains(e.target)) {
        userDropdown.style.display = "none";
      }
    });
  </script>
</body>

</html>