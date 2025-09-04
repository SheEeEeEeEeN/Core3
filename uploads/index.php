<?php
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | CORE 1</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/style.css">
</head>

<body>
  <div class="sidebar" id="sidebar">
    <div class="logo">
      <img src="assets/logo.png" alt="SLATE Logo">
    </div>
    <div class="system-name"><strong>CORE 1</strong></div>
    <a href="index.php" class="active">📊 Dashboard</a>
    <a href="public/purchase_orders.php">📝 Purchase Orders</a>
    <a href="public/shipment.php">🚚 Shipment Booking & Routing</a>
    <a href="public/consolidation.php">📦 Consolidation</a>
    <a href="public/hmb.php">📄 BL Generator</a>
    <a href="public/ship_tracking.php">🛰 Tracking</a>
    <a href="public/archives.php">🗃 Archives</a>
  </div>

  <div class="content" id="mainContent">
    <div class="header">
      <div class="hamburger" id="hamburger">☰</div>
      <div>
        <h1>Admin Dashboard <span class="system-title">| CORE 1</span></h1>
      </div>
      <div class="theme-toggle-container">
        <div class="dropdown">
          <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person"></i>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Action</a></li>
            <li><a class="dropdown-item" href="#">Another action</a></li>
            <li><a class="dropdown-item" href="login.php">Logout</a></li>
          </ul>
        </div>
        <span class="theme-label">Dark Mode</span>
        <label class="theme-switch">
          <input type="checkbox" id="themeToggle">
          <span class="slider"></span>
        </label>
      </div>
    </div>

    <!-- Dashboard -->
    <section id="page-dashboard" class="page p-3">
      <div class="row g-3">
        <div class="col-6 col-lg-3">
          <div class="card card-hover">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="text-muted">Active Shipments</div>
                <div class="h3 mb-0" id="kpiShipments">0</div>
              </div>
              <div class="display-6">🚚</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card card-hover">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="text-muted">Open Consolidations</div>
                <div class="h3 mb-0" id="kpiConsol">0</div>
              </div>
              <div class="display-6">📦</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card card-hover">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="text-muted">Tracking Events (7d)</div>
                <div class="h3 mb-0" id="kpiEvents">0</div>
              </div>
              <div class="display-6">🛰</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card card-hover">
            <div class="card-body d-flex justify-content-between align-items-center">
              <div>
                <div class="text-muted">Linked POs</div>
                <div class="h3 mb-0" id="kpiPOs">0</div>
              </div>
              <div class="display-6">📝</div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mt-2">
        <div class="col-lg-8">
          <div class="card h-100">
            <div class="card-header bg-white"><strong>Recently Updated Shipments</strong></div>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0" id="tblRecent">
                <thead class="table-light">
                  <tr>
                    <th>Ref</th>
                    <th>Type</th>
                    <th>Mode</th>
                    <th>Status</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>ETA</th>
                  </tr>
                </thead>
                <tbody id="recentShipments"></tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card h-100">
            <div class="card-header bg-white"><strong>Notifications</strong></div>
            <div class="list-group list-group-flush" id="listNotifs"></div>
          </div>
        </div>
      </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      document.getElementById('themeToggle').addEventListener('change', function() {
        document.body.classList.toggle('dark-mode', this.checked);
      });

      document.getElementById('hamburger').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
        document.getElementById('mainContent').classList.toggle('expanded');
      });

      // Fetch KPIs
       fetch("api/dashboard_kpis.php")
  .then(res => {
    if (!res.ok) throw new Error("HTTP error " + res.status);
    return res.text();
  })
  .then(text => {
    try {
      const data = JSON.parse(text);
      document.getElementById("kpiShipments").innerText = data.active_shipments || 0;
      document.getElementById("kpiConsol").innerText = data.open_consolidations || 0;
      document.getElementById("kpiEvents").innerText = data.tracking_events || 0;
      document.getElementById("kpiPOs").innerText = data.linked_pos || 0;
    } catch (e) {
      console.error("Invalid JSON from dashboard_kpis.php:", text);
    }
  })
  .catch(err => console.error("KPI fetch failed:", err));



    </script>

</body>

</html>