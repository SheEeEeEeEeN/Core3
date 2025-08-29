<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>USERS</title>
  <style>
    :root {
      --sidebar-width: 250px;
      --collapsed-width: 60px;
      --primary-color: #4e73df;
      --secondary-color: #f8f9fc;
      --dark-bg: #1a1a2e;
      --dark-card: #16213e;
      --text-light: #f8f9fa;
      --text-dark: #212529;
      --border-radius: 0.35rem;
      --shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background-color: var(--secondary-color);
      color: var(--text-dark);
      transition: all 0.3s;
    }

    body.dark-mode {
      background-color: var(--dark-bg);
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
      transition: all 0.1s ease;
      overflow: hidden;
    }

    .sidebar.collapsed {
      transform: translateX(-100%);
    }

    .sidebar .logo {
      padding: 1rem;
      text-align: center;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      white-space: nowrap;
      overflow: hidden;

    }

    .sidebar.collapsed .logo h2 {
      font-size: 1.2rem;
    }

    .sidebar a {
      display: block;
      color: rgba(255, 255, 255, 0.8);
      padding: 0.75rem 1.5rem;
      text-decoration: none;
      border-left: 3px solid transparent;
      transition: all 0.3s;
      white-space: nowrap;
      overflow: hidden;
    }

    .sidebar a.active,
    .sidebar a:hover {
      background-color: rgba(255, 255, 255, 0.1);
      color: white;
      border-left: 3px solid white;
    }

    .logo img {
      max-width: 100%;
      height: auto;
    }

    /* Content */
    .content {
      margin-left: var(--sidebar-width);
      padding: 20px;
      transition: all 0.3s;
    }

    .sidebar.collapsed~.content {
      margin-left: var(--collapsed-width);
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
    }

    .hamburger {
      font-size: 1.5rem;
      cursor: pointer;
      margin-right: 1rem;
    }

    .profile {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .profile img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--primary-color);
    }

    /* Cards */
    .card {
      background-color: white;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      padding: 1rem;
      margin-bottom: 1.5rem;
    }

    .dark-mode .card {
      background-color: var(--dark-card);
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      padding: 0.75rem;
      border-bottom: 1px solid #ddd;
    }

    thead {
      background-color: var(--primary-color);
      color: white;
    }

    .dark-mode th,
    .dark-mode td {
      border-bottom-color: #3a4b6e;
    }

    /* Theme Toggle */
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
    .bi{
      color: var(--primary-color);
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="logo">
      <div class="logo">
        <img src="logo.png" alt="SLATE Logo">
      </div>

    </div>

    <a href="#" class="active">Dashboard</a>
    <a href="#">My Shipments</a>
    <a href="#">Documents</a>
    <a href="#">Notifications</a>
    <a href="feedback.php">Feedback</a>
    <a href="login.php">Logout</a>
  </div>

  <!-- Content -->
  <div class="content">
    <!-- Header -->
    <div class="header">
      <div style="display: flex; align-items: center;">
        <div class="hamburger" id="toggleSidebar">☰</div>
        <h1>User Dashboard</h1>
      </div>
      <div class="profile">
        <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" fill="currentColor" class="bi bi-person-circle"
          viewBox="0 0 16 16">
          <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
          <path fill-rule="evenodd"
            d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
        </svg>
        <label class="theme-switch">
          <input type="checkbox" id="themeToggle">
          <span class="slider"></span>
        </label>
      </div>
    </div>

    <!-- Shipments -->
    <div class="card">
      <h3>📦 My Shipments</h3>
      <table>
        <thead>
          <tr>
            <th>Tracking No.</th>
            <th>Status</th>
            <th>Origin</th>
            <th>Destination</th>
            <th>ETA</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>FR-1001</td>
            <td>Delivered</td>
            <td>Shanghai</td>
            <td>Manila</td>
            <td>2025-08-20</td>
          </tr>
          <tr>
            <td>FR-1002</td>
            <td>In Transit</td>
            <td>Singapore</td>
            <td>Cebu</td>
            <td>2025-08-30</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Documents -->
    <div class="card">
      <h3>📑 My Documents</h3>
      <ul>
        <li>Bill of Lading.pdf ✅</li>
        <li>Customs Clearance.pdf ⏳</li>
        <li>Insurance Certificate.pdf ❌</li>
      </ul>
    </div>

    <!-- Notifications -->
    <div class="card">
      <h3>🔔 Notifications</h3>
      <ul>
        <li>Shipment FR-1002 has departed Singapore.</li>
        <li>Customs cleared for FR-1001.</li>
        <li>Shipment FR-1003 delayed due to weather.</li>
      </ul>
    </div>

    <!-- Feedback -->
    <div class="card">
      <h3>💬 Customer Feedback</h3>
      <ul>
        <li>👍 "Smooth delivery, no issues." – <b>Admin</b></li>
        <li>👎 "Shipment FR-1003 was delayed." – <b>Admin</b></li>
        <li>💡 "Please add SMS shipment updates." – <b>Admin</b></li>
      </ul>
    </div>
  </div>

  <script>
    // Dark mode toggle
    document.getElementById('themeToggle').addEventListener('change', function () {
      document.body.classList.toggle('dark-mode', this.checked);
    });

    // Sidebar toggle
    document.getElementById('toggleSidebar').addEventListener('click', function () {
      document.getElementById('sidebar').classList.toggle('collapsed');
    });
  </script>
</body>

</html>