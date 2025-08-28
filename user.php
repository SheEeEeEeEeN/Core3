<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard | Core 3</title>
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
      transition: all 0.3s;
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
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .sidebar .logo img {
      max-width: 100%;
      height: auto;
    }

    .system-name {
      padding: 0.5rem 1.5rem;
      font-size: 0.9rem;
      color: rgba(255,255,255,0.8);
      text-align: center;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      margin-bottom: 1rem;
    }

    .sidebar a {
      display: block;
      color: rgba(255,255,255,0.8);
      padding: 0.75rem 1.5rem;
      text-decoration: none;
      border-left: 3px solid transparent;
      transition: all 0.3s;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: rgba(255,255,255,0.1);
      color: white;
      border-left: 3px solid white;
    }

    .admin-feature {
      background-color: rgba(0,0,0,0.1);
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
      transition: all 0.3s;
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

    /* Form Section */
    .form-section {
      background-color: white;
      padding: 1.5rem;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      margin-bottom: 1.5rem;
      display: none;
    }

    .dark-mode .form-section {
      background-color: var(--dark-card);
      color: var(--text-light);
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
    }

    .dark-mode .form-group input,
    .dark-mode .form-group select,
    .dark-mode .form-group textarea {
      background-color: #2a3a5a;
      border-color: #3a4b6e;
      color: var(--text-light);
    }

    .form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 0.5rem;
      margin-top: 1rem;
    }

    /* Buttons */
    .btn {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 4px;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-primary {
      background-color: var(--primary-color);
      color: white;
    }

    .btn-primary:hover {
      background-color: #3a5bc7;
    }

    .btn-secondary {
      background-color: #6c757d;
      color: white;
    }

    .btn-secondary:hover {
      background-color: #5a6268;
    }

    .toggle-form-btn {
      background-color: var(--primary-color);
      color: white;
      margin-bottom: 1.5rem;
    }

    .toggle-form-btn:hover {
      background-color: #3a5bc7;
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

    th, td {
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

    input:checked + .slider {
      background-color: var(--primary-color);
    }

    input:checked + .slider:before {
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
    <div class="system-name">(NAME OF DEPARTMENT)</div>
    <a href="#" class="active">Dashboard</a>
    <a href="#">Freight Management</a>
    <a href="#">Employee Management</a>
    <a href="#" class="admin-feature">System Settings</a>
    <a href="#" class="admin-feature">User Permissions</a>
    <a href="#">Reports</a>
    <a href="login.php">Logout</a>
  </div>

  <div class="content" id="mainContent">
    <div class="header">
      <div class="hamburger" id="hamburger">☰</div>
      <div>
        <h1>User Dashboard <span class="system-title">| (NAME OF DEPARTMENT)</span></h1>
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
        <h3>Total Employees</h3>
        <div class="stat-value" id="totalEmployees">0</div>
        <div class="stat-label">Loading data...</div>
      </div>

      <div class="card">
        <h3>Active Employees</h3>
        <div class="stat-value" id="activeEmployees">0</div>
        <div class="stat-label">Loading data...</div>
      </div>

      <div class="card">
        <h3>Average Salary</h3>
        <div class="stat-value" id="avgSalary">$0</div>
        <div class="stat-label">Loading data...</div>
      </div>
    </div>

    <button id="toggleFormBtn" class="btn toggle-form-btn">Add New Employee</button>

    <div class="form-section" id="employeeFormSection">
      <h3 id="formTitle">Add New Employee</h3>
      <form id="employeeForm">
        <input type="hidden" id="employeeId">
        <div class="form-group">
          <label for="employeeName">Full Name</label>
          <input type="text" id="employeeName" name="employeeName" required>
        </div>
        
        <div class="form-group">
          <label for="employeeEmail">Email Address</label>
          <input type="email" id="employeeEmail" name="employeeEmail" required>
        </div>
        
        <div class="form-group">
          <label for="employeeDepartment">Department</label>
          <select id="employeeDepartment" name="employeeDepartment" required>
            <option value="">Select Department</option>
            <option value="HR">Human Resources</option>
            <option value="Finance">Finance</option>
            <option value="IT">Information Technology</option>
            <option value="Operations">Operations</option>
            <option value="Logistics">Logistics</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="employeePosition">Position</label>
          <input type="text" id="employeePosition" name="employeePosition" required>
        </div>
        
        <div class="form-group">
          <label for="employeeSalary">Salary (USD)</label>
          <input type="number" id="employeeSalary" name="employeeSalary" required>
        </div>
        
        <div class="form-group">
          <label for="employeeStartDate">Start Date</label>
          <input type="date" id="employeeStartDate" name="employeeStartDate" required>
        </div>
        
        <div class="form-group">
          <label for="employeeStatus">Status</label>
          <select id="employeeStatus" name="employeeStatus" required>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
            <option value="On Leave">On Leave</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="employeeNotes">Notes</label>
          <textarea id="employeeNotes" name="employeeNotes" rows="3"></textarea>
        </div>
        
        <div class="form-actions">
          <button type="button" id="cancelBtn" class="btn btn-secondary">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Employee</button>
        </div>
      </form>
    </div>

    <div class="table-section">
      <h3>Employee Compensation</h3>
      <table id="employeesTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Department</th>
            <th>Position</th>
            <th>Salary</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="employeesTableBody">
          <!-- Employee data will be loaded here -->
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // You can keep your existing JavaScript or add new functionality here
    document.getElementById('themeToggle').addEventListener('change', function() {
      document.body.classList.toggle('dark-mode', this.checked);
    });

    document.getElementById('hamburger').addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    });

    document.getElementById('toggleFormBtn').addEventListener('click', function() {
      const formSection = document.getElementById('employeeFormSection');
      formSection.style.display = formSection.style.display === 'block' ? 'none' : 'block';
    });

    document.getElementById('cancelBtn').addEventListener('click', function() {
      document.getElementById('employeeFormSection').style.display = 'none';
    });
  </script>
</body>
</html>