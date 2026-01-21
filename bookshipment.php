<?php
include("darkmode.php");
include("connection.php");
include('session.php');
include('loading.html');
requireRole('user');

// Fetch current user info
$username = $_SESSION['email'];
$query = mysqli_query($conn, "SELECT * FROM accounts WHERE email = '$username'");
$user = mysqli_fetch_assoc($query);

$profileImage = !empty($user['profile_image']) ? $user['profile_image'] : 'user.png';
$userContact = isset($user['contact_number']) ? $user['contact_number'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book Shipment — Route & Pricing</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />

  <style>
    :root {
      --sidebar-width: 250px;
      --primary-color: #4e73df;
      --secondary-color: #f8f9fc;
      --dark-bg: #1a1a2e;
      --dark-card: #16213e;
      --text-light: #f8f9fa;
      --text-dark: #212529;
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

    /* --- SIDEBAR & MOBILE MENU --- */
    .sidebar {
      width: var(--sidebar-width);
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      background: #2c3e50;
      color: white;
      z-index: 1040;
      transition: all .3s ease;
    }

    .content {
      margin-left: var(--sidebar-width);
      padding: 20px;
      transition: margin-left .3s ease;
      min-height: 100vh;
    }

    .sidebar.collapsed {
      margin-left: calc(var(--sidebar-width) * -1);
    }

    .content.expanded {
      margin-left: 0;
    }

    @media (max-width: 992px) {
      .sidebar {
        left: -250px;
      }

      .sidebar.mobile-open {
        left: 0;
      }

      .content {
        margin-left: 0 !important;
        padding: 15px;
      }

      #map {
        height: 350px !important;
      }
    }

    .sidebar-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1030;
      display: none;
      opacity: 0;
      transition: opacity 0.3s;
    }

    .sidebar-overlay.show {
      display: block;
      opacity: 1;
    }

    .sidebar a {
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      padding: .75rem 1.5rem;
      display: block;
      border-left: 3px solid transparent;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: rgba(255, 255, 255, 0.1);
      color: white;
      border-left: 3px solid white;
    }

    /* --- COMPONENTS --- */
    .panel {
      background: white;
      border-radius: .5rem;
      padding: 1.5rem;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
      margin-bottom: 20px;
    }

    /* --- MAP CSS FIX --- */
    #map {
      height: 500px !important;
      width: 100%;
      border-radius: .5rem;
      box-shadow: var(--shadow);
      z-index: 1;
      display: block;
    }

    .location-selectors {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 15px;
    }

    @media (max-width: 768px) {
      .location-selectors {
        grid-template-columns: 1fr;
            }
    }

    .location-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .location-group label {
      font-size: 0.875rem;
      font-weight: 600;
      color: #555;
    }

    #priceDisplay,
    #priceDisplaySmall {
      font-weight: 700;
      color: var(--primary-color);
    }

    #priceDisplay {
      font-size: 1.5rem;
    }

    /* --- PAYMENT STYLES --- */
    .payment-option-card {
      border: 2px solid #e3e6f0;
      border-radius: 10px;
      padding: 10px;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 8px;
    }

    .payment-option-card:hover {
      border-color: var(--primary-color);
      background-color: #f8f9fc;
    }

    .payment-option-card.selected {
      border-color: var(--primary-color);
      background-color: rgba(78, 115, 223, 0.1);
      position: relative;
    }

    .payment-option-card.selected::after {
      content: '✔';
      position: absolute;
      right: 15px;
      color: var(--primary-color);
      font-weight: bold;
    }

    /* --- DARK MODE --- */
    body.dark-mode {
      background-color: var(--dark-bg);
      color: var(--text-light);
    }

    body.dark-mode .sidebar {
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
    }

    body.dark-mode .header {
      background-color: var(--dark-card) !important;
      color: var(--text-light) !important;
      border: 1px solid #2a3a5a;
    }

    body.dark-mode .panel,
    body.dark-mode .card,
    body.dark-mode .payment-option-card {
      background-color: var(--dark-card) !important;
      color: var(--text-light);
      border: 1px solid #2a3a5a;
    }

    body.dark-mode input.form-control,
    body.dark-mode select.form-select,
    body.dark-mode textarea {
      background-color: #243355 !important;
      border-color: #3a4b6e !important;
      color: #fff !important;
    }

    body.dark-mode .modal-content {
      background-color: var(--dark-card);
      color: var(--text-light);
      border: 1px solid #2a3a5a;
    }

    body.dark-mode .modal-header {
      border-bottom-color: #2a3a5a;
    }

    body.dark-mode .list-group-item {
      background-color: #243355;
      color: white;
      border-color: #3a4b6e;
    }

    body.dark-mode .btn-close {
      filter: invert(1);
    }

    body.dark-mode .location-group label {
      color: #ddd;
    }

    body.dark-mode .payment-option-card:hover {
      background-color: #2a3a5a;
    }
  </style>
</head>

<body>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <div class="sidebar d-flex flex-column flex-shrink-0 p-3" id="sidebar">
    <div class="text-center mb-4 mt-2">
      <img src="Remorig.png" alt="Freight Logo" class="img-fluid mb-2" style="max-width: 140px;">
      <h6 class="fw-semibold text-uppercase text-light-50 mb-0" style="font-size: 0.85rem;">Core Transaction 3</h6>
    </div>

    <hr class="text-light opacity-25">

    <ul class="nav nav-pills flex-column mb-auto">
      <li class="nav-item mb-2"><a href="user.php" class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 hover-link"><i class="bi bi-house-door-fill fs-5"></i><span>Dashboard</span></a></li>
      <li class="nav-item mb-2"><a href="bookshipment.php" class="active nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 hover-link"><i class="bi bi-truck fs-5"></i><span>Book Shipment</span></a></li>
      <ul class="nav nav-pills flex-column mb-auto">

      </ul>
      <li class="nav-item mb-2"><a href="My_shipment.php" class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 hover-link"><i class="bi bi-truck fs-5"></i><span>My Shipments</span></a></li>
      <li class="nav-item mb-2"><a href="shiphistory.php" class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 hover-link"><i class="bi bi-clock-history fs-5"></i><span>Shipment History</span></a></li>
      <li class="nav-item mb-2"><a href="feedback.php" class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 hover-link"><i class="bi bi-chat-dots fs-5"></i><span>Feedback & Notification</span></a></li>
    </ul>
  </div>

  <div class="content" id="mainContent">
    <header class="header d-flex align-items-center justify-content-between px-3 py-3 mb-4 bg-white shadow-sm rounded-3 sticky-top">
      <div class="d-flex align-items-center gap-3">
        <button class="hamburger btn btn-light border-0 p-2 d-flex align-items-center justify-content-center" id="hamburger"><i class="bi bi-list fs-4"></i></button>
        <div>
          <h5 class="fw-semibold mb-0">Book Shipment</h5>
        </div>
      </div>

      <div class="d-flex align-items-center gap-3">
          <div class="dropdown me-3">
            <a href="#" class="text-dark position-relative" id="notifDropdown" data-bs-toggle="dropdown" onclick="markRead()">
              <i class="bi bi-bell fs-4"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="display: none;">
                0
              </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 300px; max-height: 400px; overflow-y: auto;">
              <li class="p-2 border-bottom fw-bold bg-light">Notifications</li>
              <div id="notifList">
                <li class="text-center p-3 text-muted small">No new notifications</li>
              </div>
              <li><a class="dropdown-item text-center small text-primary p-2 border-top" href="feedback.php">View All</a></li>
            </ul>
          </div>
        <div class="dropdown">
        
          <a href="#" class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle" data-bs-toggle="dropdown">
            <img src="<?php echo $profileImage ?? 'default-avatar.png'; ?>" alt="Profile" class="rounded-circle" width="40" height="40" style="object-fit:cover;">
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><a class="dropdown-item" href="user-profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </div>
        <div class="form-check form-switch mb-0 d-none d-sm-block">
          <label class="form-check-label" for="userThemeToggle">🌙</label>
          <input class="form-check-input" type="checkbox" role="switch" id="userThemeToggle">
        </div>
      </div>
    </header>

    <div class="container-fluid p-0">
      <div class="row g-4">
        <div class="col-lg-8">
          <div class="panel">
            <h4 class="mb-2">Route Search & Navigation</h4>
            <p class="text-muted small">Select origin and destination locations. The map will calculate a drivable route.</p>

            <div class="location-selectors">
              <div class="location-group p-3 border rounded bg-light-subtle">
                <h6 class="text-primary fw-bold mb-3"><i class="bi bi-geo-alt-fill"></i> Origin (Pick Up)</h6>

                <label for="originIsland" class="fw-bold text-muted small">Island Group</label>
                <select id="originIsland" class="form-select mb-2">
                  <option value="" disabled selected>Select Island</option>
                  <option value="Luzon">Luzon</option>
                  <option value="Visayas">Visayas</option>
                  <option value="Mindanao">Mindanao</option>
                </select>

                <label for="originRegion" class="small">Region</label>
                <select id="originRegion" class="form-select mb-2" disabled>
                  <option value="" disabled selected>Select Region</option>
                </select>

                <label for="originProvince" class="small">Province</label>
                <select id="originProvince" class="form-select mb-2" disabled>
                  <option value="" disabled selected>Select Province</option>
                </select>

                <label for="originMunicipality" class="small">City/Municipality</label>
                <select id="originMunicipality" class="form-select mb-2" disabled>
                  <option value="" disabled selected>Select City</option>
                </select>

                <label for="originBarangay" class="small">Barangay</label>
                <select id="originBarangay" class="form-select" disabled>
                  <option value="" disabled selected>Select Barangay</option>
                </select>
              </div>

              <div class="location-group p-3 border rounded bg-light-subtle">
                <h6 class="text-danger fw-bold mb-3"><i class="bi bi-geo-alt"></i> Destination (Drop Off)</h6>

                <label for="destIsland" class="fw-bold text-muted small">Island Group</label>
                <select id="destIsland" class="form-select mb-2">
                  <option value="" disabled selected>Select Island</option>
                  <option value="Luzon">Luzon</option>
                  <option value="Visayas">Visayas</option>
                  <option value="Mindanao">Mindanao</option>
                </select>

                <label for="destRegion" class="small">Region</label>
                <select id="destRegion" class="form-select mb-2" disabled>
                  <option value="" disabled selected>Select Region</option>
                </select>

                <label for="destProvince" class="small">Province</label>
                <select id="destProvince" class="form-select mb-2" disabled>
                  <option value="" disabled selected>Select Province</option>
                </select>

                <label for="destMunicipality" class="small">City/Municipality</label>
                <select id="destMunicipality" class="form-select mb-2" disabled>
                  <option value="" disabled selected>Select City</option>
                </select>

                <label for="destBarangay" class="small">Barangay</label>
                <select id="destBarangay" class="form-select" disabled>
                  <option value="" disabled selected>Select Barangay</option>
                </select>
              </div>
            </div>

            <div class="mb-3 d-flex gap-2">
              <button id="routeBtn" class="btn btn-primary flex-grow-1 flex-md-grow-0">Search Route</button>
              <button id="clearRouteBtn" class="btn btn-outline-secondary">Clear Route</button>
            </div>

            <div id="map"></div>

            <div class="row mt-3 g-2">
              <div class="col-4">
                <div class="form-control p-2 text-center bg-light"><small class="text-muted d-block">Distance</small><strong id="distanceKmDisplay">0.000</strong> km</div>
              </div>
              <div class="col-4">
                <div class="form-control p-2 text-center bg-light"><small class="text-muted d-block">Rate/km</small><strong id="ratePerKmDisplay">--</strong></div>
              </div>
              <div class="col-4">
                <div class="form-control p-2 text-center bg-light"><small class="text-muted d-block">Price</small><strong id="priceDisplay">₱0.00</strong></div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="panel">
            <h5>Book & Pricing</h5>

            <form id="shipmentForm">
              <input type="hidden" name="origin_island" id="hiddenOriginIsland">
              <input type="hidden" name="destination_island" id="hiddenDestIsland">

              <div class="mb-3 p-3 bg-white border rounded shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                  <label class="form-label small fw-bold text-primary mb-0">Active Contract</label>
                  <span class="badge bg-success" id="contractStatusBadge">Checking...</span>
                </div>
                <div class="input-group input-group-sm mt-2">
                  <input type="text" class="form-control fw-bold" name="contract_number" id="contractNumber" readonly>
                  <a href="client_view_contract.php" target="_blank" class="btn btn-outline-primary" type="button" title="View Full Contract">
                    <i class="bi bi-eye"></i> View
                  </a>
                </div>

                <input type="hidden" name="sla_max_days" id="slaMaxDays">
                <input type="hidden" name="target_date" id="targetDeliveryDate">

                <div class="mt-2 small text-muted fst-italic border-top pt-2" id="slaPromiseText">
                  <i class="bi bi-shield-check"></i> Select Origin & Destination to view SLA.
                </div>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-7">
                  <label class="form-label small">Sender Name</label>
                  <input type="text" class="form-control fw-bold" name="sender_name" required readonly value="<?php echo htmlspecialchars($username); ?>">
                </div>
                <div class="col-5">
                  <label class="form-label small">Sender Contact</label>
                  <input type="text" class="form-control" name="sender_contact" placeholder="09xxxxxxxxx" value="<?php echo htmlspecialchars($userContact); ?>" required>
                </div>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-7">
                  <label class="form-label small">Receiver Name</label>
                  <input type="text" class="form-control" name="receiver_name" required>
                </div>
                <div class="col-5">
                  <label class="form-label small">Receiver Contact</label>
                  <input type="text" class="form-control" name="receiver_contact" placeholder="09xxxxxxxxx" required>
                </div>
              </div>

              <div class="mb-2">
                <label class="form-label small">Origin Address (Auto)</label>
                <input type="text" class="form-control bg-light" name="origin_address" id="originField" readonly required>
              </div>
              <div class="mb-2">
                <label class="form-label small">Destination Address (Auto)</label>
                <input type="text" class="form-control bg-light" name="destination_address" id="destinationField" readonly required>
              </div>

              <div class="mb-2">
                <label class="form-label small">Specific Address / Notes</label>
                <textarea class="form-control" name="address" required rows="2"></textarea>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-6">
                  <label class="form-label small">Actual Weight (kg)</label>
                  <input type="number" step="0.01" class="form-control" name="weight" id="actualWeight" required placeholder="0.0">
                </div>
                <div class="col-6">
                  <label class="form-label small">Package Type</label>
                  <select class="form-select" name="package_type" id="packageType" required>
                    <option value="" disabled selected>Select</option>
                    <option value="parcel">Small Parcel (Docs)</option>
                    <option value="box">Standard Box</option>
                    <option value="crate">Large Crate/Box</option>
                    <option value="furniture">Furniture/Appliance</option>
                    <option value="pallet">Palletized Cargo</option>
                  </select>
                </div>
              </div>

              <div class="mb-3 p-2 bg-primary bg-opacity-10 border border-primary rounded">
                <label class="form-label small text-primary fw-bold mb-1">
                  <i class="bi bi-box-seam-fill"></i> Quick Select (Standard Sizes):
                </label>
                <select class="form-select form-select-sm" id="itemHelper">
                  <option value="" selected>-- Select Package Size --</option>

                  <option value="xs_parcel" data-l="30" data-w="21" data-h="2" data-k="0.5" data-type="parcel">
                    📄 Extra Small (Documents / Pouch)
                  </option>
                  <option value="s_box" data-l="30" data-w="30" data-h="20" data-k="3" data-type="box">
                    📦 Small Box (Shoe Box Size)
                  </option>

                  <!-- <option value="m_box" data-l="50" data-w="40" data-h="40" data-k="10" data-type="box">
                    📦 Medium Box (Microwave Size)
                  </option> -->
                  <option value="l_box" data-l="60" data-w="60" data-h="60" data-k="20" data-type="box">
                    📦 Large Box (Standard Balikbayan)
                  </option>

                  <option value="xl_cargo" data-l="100" data-w="60" data-h="60" data-k="40" data-type="crate">
                    🚛 XL Cargo (Appliance / Equipment)
                  </option>
                  <option value="xxl_bulk" data-l="150" data-w="80" data-h="100" data-k="80" data-type="furniture">
                    🛋️ Bulky Item (Furniture / Oversized)
                  </option>

                  <option value="pallet" data-l="120" data-w="100" data-h="150" data-k="200" data-type="pallet">
                    🏭 Standard Pallet (Commercial)
                  </option>
                </select>
              </div>

              <div class="mb-2">
                <label class="form-label small text-muted">Dimensions (L x W x H in cm)</label>
                <div class="input-group input-group-sm">
                  <input type="number" class="form-control" id="dimL" name="length" placeholder="L">
                  <span class="input-group-text">x</span>
                  <input type="number" class="form-control" id="dimW" name="width" placeholder="W">
                  <span class="input-group-text">x</span>
                  <input type="number" class="form-control" id="dimH" name="height" placeholder="H">
                </div>
                <div class="d-flex justify-content-between small text-muted mt-1">
                  <span>Volumetric: <strong id="volWeightDisplay">0.00</strong> kg</span>
                  <span class="text-success">Chargeable: <strong id="chargeableWeightDisplay">0.00</strong> kg</span>
                </div>
              </div>

              <div class="mb-2">
                <label class="form-label small">Package Description</label>
                <input type="text" class="form-control" name="package" required>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-bold">Payment Method</label>
                <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="">
                <input type="hidden" name="bank_name" id="selectedBankName" value="">

                <div class="payment-option-card" onclick="selectPayment('cod', this)">
                  <i class="bi bi-cash-stack fs-4 text-success"></i>
                  <div>
                    <div class="fw-bold">Cash on Delivery</div>
                    <div class="small text-muted" style="font-size: 0.7rem;">Pay upon arrival</div>
                  </div>
                </div>

                <div class="payment-option-card" onclick="selectPayment('online', this)">
                  <i class="bi bi-credit-card-2-front fs-4 text-primary"></i>
                  <div class="w-100">
                    <div class="fw-bold">Online Payment</div>
                    <div class="small text-muted" style="font-size: 0.7rem;">
                      GCash, Maya, Visa/Mastercard
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-3 form-check">
                <input type="checkbox" id="contractAgree" disabled class="form-check-input">
                <label for="contractAgree" class="form-check-label small">
                  I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#contractModal">Terms & Conditions</a>
                </label>
              </div>

              <input type="hidden" name="distance_km" id="distance_km" value="0">
              <input type="hidden" name="rate_per_km" id="rate_per_km" value="15">
              <input type="hidden" name="price_php" id="price_php" value="0">

              <div class="d-grid gap-2">
                <button type="button" id="calcBtn" class="btn btn-success" onclick="calculateTotal()">Calculate Price</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">🚀 Submit Booking</button>
              </div>
            </form>

            <div class="card mt-3 border-0 shadow-sm bg-light">
              <div class="card-body p-3">
                <h6 class="text-primary fw-bold mb-2"><i class="bi bi-robot me-1"></i>AI Prediction</h6>
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <small class="text-muted d-block">Est. Time</small>
                    <h5 class="mb-0" id="aiPredictionTime">--</h5>
                  </div>
                  <div class="text-end">
                    <small class="text-muted d-block">Confidence</small>
                    <span class="fw-bold text-success" id="aiConfidenceScore">0%</span>
                  </div>
                </div>
                <div class="progress mt-2" style="height: 4px;">
                  <div id="aiConfidenceBar" class="progress-bar bg-success" style="width: 0%"></div>
                </div>
                <small class="text-muted mt-2 d-block fst-italic" id="aiReasoning">Waiting for route...</small>
              </div>
            </div>

            <div class="card mt-3">
              <div class="card-body text-center py-2">
                <small class="text-muted">Estimated Shipment Price</small>
                <h4 class="card-text text-success fw-bold mb-0" id="priceDisplaySmall">₱0.00</h4>
              </div>
            </div>

            <div id="responseMessage" class="alert mt-3 d-none"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="contractModal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">📑 Terms and Conditions</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
    <div class="border p-4 rounded bg-light shadow-sm" style="max-height: 400px; overflow-y: auto; font-size: 0.85rem; line-height: 1.6; color: #333;">
        
        <div class="text-center mb-4">
            <h5 class="fw-bold mb-1">STANDARD TRADING CONDITIONS</h5>
        </div>

        <h6 class="fw-bold text-primary mt-3">1. DEFINITIONS</h6>
        <p class="mb-2">"Carrier" refers to Slate Freight and its authorized logistics partners (Core 2 Providers). "Shipper" refers to the person booking the shipment. "Consignee" refers to the receiver of the goods.</p>

        <h6 class="fw-bold text-primary mt-3">2. PROHIBITED ITEMS</h6>
        <p class="mb-1">The Shipper warrants that the package does NOT contain any of the following:</p>
        <ul class="mb-2 ps-3">
            <li>Dangerous goods (Explosives, Flammables, Toxic substances)</li>
            <li>Illegal drugs or narcotics</li>
            <li>Live animals or human remains</li>
            <li>Cash, jewelry, and high-value negotiable instruments</li>
            <li>Firearms and ammunition</li>
        </ul>
        <p class="fst-italic text-danger small">The Carrier reserves the right to inspect and refuse packages suspected of containing prohibited items.</p>

        <h6 class="fw-bold text-primary mt-3">3. SHIPPER'S RESPONSIBILITY (PACKAGING)</h6>
        <p class="mb-2">The Shipper is solely responsible for proper packaging. Items must be packed in a way that withstands the rigors of transportation. The Carrier is <strong>NOT liable</strong> for damage caused by improper or insufficient packaging (e.g., glass without bubble wrap).</p>

        <h6 class="fw-bold text-primary mt-3">4. LIMITATION OF LIABILITY</h6>
        <p class="mb-1">Unless the Shipper declares a higher value and pays the corresponding valuation charge (Insurance), the Carrier's liability for loss or damage is limited to:</p>
        <ul class="mb-2 ps-3">
            <li>The actual value of the item; or</li>
            <li><strong>PHP 2,000.00</strong> (Philippine Peso);</li>
        </ul>
        <p class="mb-2">Whichever is lower. The Carrier is not liable for indirect or consequential damages (e.g., lost profits due to delay).</p>

        <h6 class="fw-bold text-primary mt-3">5. DELIVERY TIMEFRAME & DELAYS</h6>
        <p class="mb-2">Delivery dates provided by the AI Prediction are <strong>estimates only</strong> and are not guaranteed. The Carrier is not liable for delays caused by traffic congestion, checkpoint delays, or incorrect addresses provided by the Shipper.</p>

        <h6 class="fw-bold text-primary mt-3">6. FORCE MAJEURE</h6>
        <p class="mb-2">The Carrier shall not be liable for loss, damage, or delay arising from acts of God (typhoons, floods, earthquakes), strikes, civil commotion, or government acts.</p>

        <h6 class="fw-bold text-primary mt-3">7. CLAIMS</h6>
        <p class="mb-0">Any claim for damage or loss must be filed within <strong>twenty-four (24) hours</strong> from the time of delivery. Failure to report within this period shall be deemed a waiver of the claim.</p>
    </div>

    <div class="mt-3 text-end">
        <div class="form-check d-inline-block text-start">
            <input class="form-check-input" type="checkbox" id="readConfirm">
            <label class="form-check-label small text-muted" for="readConfirm">I have read and understood the Terms and Conditions.</label>
        </div>
    </div>
</div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" id="acceptContract" class="btn btn-success fw-bold">I Agree & Accept</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="paymentGatewayModal" tabindex="-1" data-bs-backdrop="static">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold text-primary"><i class="bi bi-shield-lock-fill me-2"></i>Secure Payment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body pt-2">
            <p class="text-muted small mb-3">Total: <strong id="pgAmount" class="text-dark fs-5">₱0.00</strong></p>
            <ul class="nav nav-tabs nav-fill mb-3" id="paymentTabs">
              <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#ewallet">E-Wallet</a></li>
              <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#card">Card</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="ewallet">
                <div class="d-grid gap-2 mb-3">
                  <button type="button" class="btn btn-outline-primary py-2 d-flex align-items-center justify-content-between" onclick="selectEwallet('GCash')"><span>GCash</span> <i class="bi bi-phone"></i></button>
                  <button type="button" class="btn btn-outline-dark py-2 d-flex align-items-center justify-content-between" onclick="selectEwallet('Maya')"><span>Maya</span> <i class="bi bi-wallet2"></i></button>
                </div>
                <div id="ewalletForm" class="d-none">
                  <div class="mb-2"><label class="small fw-bold">Mobile</label><input type="number" class="form-control" placeholder="09xxxxxxxxx"></div>
                  <div class="d-grid"><button class="btn btn-primary" onclick="simulateProcessing('E-Wallet')">Pay Now</button></div>
                </div>
              </div>
              <div class="tab-pane fade" id="card">
                <div class="mb-2"><label class="small fw-bold">Card Number</label><input type="text" class="form-control" placeholder="0000 0000 0000 0000"></div>
                <div class="d-grid"><button class="btn btn-primary" onclick="simulateProcessing('Credit Card')">Pay Now</button></div>
              </div>
            </div>
            <div id="paymentProcessing" class="text-center py-4 d-none">
              <div class="spinner-border text-primary mb-3"></div>
              <h6>Processing...</h6>
            </div>
            <div id="paymentSuccess" class="text-center py-4 d-none">
              <h5 class="text-success">Success!</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="inputPreviewModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title">Confirm Booking</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <ul class="list-group list-group-flush">
              <li class="list-group-item d-flex justify-content-between"><span>Method</span> <strong id="previewPaymentMethod"></strong></li>
              <li class="list-group-item d-flex justify-content-between"><span>Contract</span> <strong id="previewContractNumber"></strong></li>
              <li class="list-group-item"><strong>Sender:</strong> <span id="previewSenderName"></span> (<span id="previewSenderContact"></span>)</li>
              <li class="list-group-item"><strong>Receiver:</strong> <span id="previewReceiverName"></span> (<span id="previewReceiverContact"></span>)</li>
              <li class="list-group-item"><strong>Origin:</strong> <span id="previewOrigin"></span></li>
              <li class="list-group-item"><strong>Dest:</strong> <span id="previewDestination"></span></li>
              <li class="list-group-item d-flex justify-content-between bg-light"><span>Total Price</span> <strong class="text-success" id="previewPrice"></strong></li>
              <li class="list-group-item"><strong>AI ETA:</strong> <span id="previewAiTime" class="small fw-bold text-info"></span></li>
            </ul>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-success" id="finalConfirmBtn">Confirm & Book</button>
          </div>
        </div>
      </div>
    </div>

  </div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.js"></script>
  <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script src="ph_locations.js?v=2"></script>

 <script>
    // 1. UI INITIALIZATION
    if (typeof initDarkMode === "function") initDarkMode("userThemeToggle", "userDarkMode");

    document.getElementById('hamburger').addEventListener('click', function() {
      const sidebar = document.getElementById('sidebar');
      if (window.innerWidth <= 992) {
        sidebar.classList.toggle('mobile-open');
        document.getElementById('sidebarOverlay').classList.toggle('show');
      } else {
        sidebar.classList.toggle('collapsed');
        document.getElementById('mainContent').classList.toggle('expanded');
      }
    });

    document.getElementById('sidebarOverlay').addEventListener('click', function() {
      document.getElementById('sidebar').classList.remove('mobile-open');
      this.classList.remove('show');
    });

    // 2. LOCATION HIERARCHY LOGIC
    const islandMapping = {
      "Luzon": ["NCR", "CAR", "Region I", "Region II", "Region III", "Region IV-A", "Region IV-B", "Region V"],
      "Visayas": ["Region VI", "Region VII", "Region VIII"],
      "Mindanao": ["Region IX", "Region X", "Region XI", "Region XII", "Region XIII", "BARMM"]
    };

    function resetDropdowns(ids) {
      ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
          el.innerHTML = '<option value="" disabled selected>Select...</option>';
          el.disabled = true;
        }
      });
    }

    function updateRegionDropdown(island, regionSelectId) {
      const regionSelect = document.getElementById(regionSelectId);
      regionSelect.innerHTML = '<option value="" disabled selected>Select Region</option>';
      regionSelect.disabled = false;
      const allowedRegions = islandMapping[island];
      if (allowedRegions && typeof philippineLocations !== 'undefined') {
        allowedRegions.forEach(regionName => {
          if (philippineLocations[regionName]) regionSelect.add(new Option(regionName, regionName));
        });
      }
    }

    function loadProvinces(regionSelect, provSelectId) {
      const provSelect = document.getElementById(provSelectId);
      provSelect.innerHTML = '<option value="" disabled selected>Select Province</option>';
      provSelect.disabled = false;
      const regionData = philippineLocations[regionSelect.value];
      if (regionData) Object.keys(regionData).forEach(p => provSelect.add(new Option(p, p)));
    }

    function loadMunicipalities(provSelect, muniSelectId, regionSelectId) {
      const muniSelect = document.getElementById(muniSelectId);
      const regionVal = document.getElementById(regionSelectId).value;
      muniSelect.innerHTML = '<option value="" disabled selected>Select City</option>';
      muniSelect.disabled = false;
      const provData = philippineLocations[regionVal][provSelect.value];
      if (provData) Object.keys(provData).forEach(m => muniSelect.add(new Option(m, m)));
    }

    function loadBarangays(muniSelect, brgySelectId, regionSelectId, provSelectId) {
      const brgySelect = document.getElementById(brgySelectId);
      const regionVal = document.getElementById(regionSelectId).value;
      const provVal = document.getElementById(provSelectId).value;
      brgySelect.innerHTML = '<option value="" disabled selected>Select Barangay</option>';
      brgySelect.disabled = false;
      const brgyData = philippineLocations[regionVal][provVal][muniSelect.value];
      if (brgyData) brgyData.forEach(b => brgySelect.add(new Option(b, b)));
    }

    // Origin Listeners
    document.getElementById('originIsland').addEventListener('change', function() {
      document.getElementById('hiddenOriginIsland').value = this.value;
      updateRegionDropdown(this.value, 'originRegion');
      resetDropdowns(['originProvince', 'originMunicipality', 'originBarangay']);
      checkSLA();
    });
    document.getElementById('originRegion').addEventListener('change', function() { loadProvinces(this, 'originProvince'); resetDropdowns(['originMunicipality', 'originBarangay']); });
    document.getElementById('originProvince').addEventListener('change', function() { loadMunicipalities(this, 'originMunicipality', 'originRegion'); resetDropdowns(['originBarangay']); });
    document.getElementById('originMunicipality').addEventListener('change', function() { loadBarangays(this, 'originBarangay', 'originRegion', 'originProvince'); });

    // Destination Listeners
    document.getElementById('destIsland').addEventListener('change', function() {
      document.getElementById('hiddenDestIsland').value = this.value;
      updateRegionDropdown(this.value, 'destRegion');
      resetDropdowns(['destProvince', 'destMunicipality', 'destBarangay']);
      checkSLA();
    });
    document.getElementById('destRegion').addEventListener('change', function() { loadProvinces(this, 'destProvince'); resetDropdowns(['destMunicipality', 'destBarangay']); });
    document.getElementById('destProvince').addEventListener('change', function() { loadMunicipalities(this, 'destMunicipality', 'destRegion'); resetDropdowns(['destBarangay']); });
    document.getElementById('destMunicipality').addEventListener('change', function() { loadBarangays(this, 'destBarangay', 'destRegion', 'destProvince'); });


    // 3. MAP LOGIC
    const map = L.map('map').setView([14.5995, 120.9842], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    const greenPinHtml = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#198754" width="40" height="40" stroke="black" stroke-width="1.5"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5" fill="white"/></svg>`;
    const redPinHtml = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#dc3545" width="40" height="40" stroke="black" stroke-width="1.5"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5" fill="white"/></svg>`;
    const pinStyle = document.createElement('style');
    pinStyle.innerHTML = `.custom-pin-icon { background: transparent !important; border: none !important; }`;
    document.head.appendChild(pinStyle);

    const geocoder = L.Control.Geocoder.photon();

    const routingControl = L.Routing.control({
      waypoints: [],
      routeWhileDragging: true,
      geocoder: L.Control.Geocoder.photon(),
      show: false, 
      addWaypoints: false, 
      lineOptions: {
        styles: [{ color: '#0d6efd', opacity: 0.8, weight: 6 }] 
      },
      createMarker: function(i, wp, n) {
        let iconHtml = (i === 0) ? greenPinHtml : ((i === n - 1) ? redPinHtml : null);
        if (iconHtml) {
          return L.marker(wp.latLng, {
            draggable: true,
            icon: L.divIcon({ className: 'custom-pin-icon', html: iconHtml, iconSize: [40, 40], iconAnchor: [20, 40], popupAnchor: [0, -40] })
          }).bindPopup(i === 0 ? "<b>Pickup</b>" : "<b>Drop-off</b>");
        }
        return null; 
      }
    }).addTo(map);

    // EVENT: Route Found
    routingControl.on('routesfound', function(e) {
      const routes = e.routes;
      if (routes && routes.length > 0) {
        const km = routes[0].summary.totalDistance / 1000.0;
        
        document.getElementById('distance_km').value = km.toFixed(2);
        document.getElementById('distanceKmDisplay').textContent = km.toFixed(2);
        
        const wp = routingControl.getWaypoints();
        if (wp[0].name) document.getElementById('originField').value = wp[0].name;
        if (wp[1].name) document.getElementById('destinationField').value = wp[1].name;

        // Trigger Calc & AI
        calculateTotal(); 
        getAiPrediction(document.getElementById('originField').value, document.getElementById('destinationField').value, km);
      }
    });

    routingControl.on('routingerror', function(e) {
      console.error('Routing Error:', e);
      alert("Route not found. Please try searchable city names.");
    });

    document.getElementById('routeBtn').addEventListener('click', () => {
      const getLoc = (p) => {
        const m = document.getElementById(p + 'Municipality').value;
        const pr = document.getElementById(p + 'Province').value;
        if (!m || !pr || m === "Select City" || pr === "Select Province") return null;
        return `${m}, ${pr}, Philippines`;
      };

      const orig = getLoc('origin');
      const dest = getLoc('dest');

      if (!orig || !dest) {
        alert("Please select complete Origin and Destination.");
        return;
      }

      const btn = document.getElementById('routeBtn');
      const oldText = btn.innerText;
      btn.innerText = "Searching...";
      btn.disabled = true;

      Promise.all([
        new Promise((resolve) => geocoder.geocode(orig, (results) => resolve(results && results.length > 0 ? results[0] : null))),
        new Promise((resolve) => geocoder.geocode(dest, (results) => resolve(results && results.length > 0 ? results[0] : null)))
      ]).then(([o, d]) => {
        btn.innerText = oldText;
        btn.disabled = false;
        if (o && d) {
          routingControl.setWaypoints([L.latLng(o.center), L.latLng(d.center)]);
          document.getElementById('originField').value = o.name;
          document.getElementById('destinationField').value = d.name;
        } else {
          alert("Locations not found on map.");
        }
      }).catch(err => {
        console.error(err);
        btn.innerText = oldText;
        btn.disabled = false;
        alert("Map Error.");
      });
    });

    document.getElementById('clearRouteBtn').addEventListener('click', () => {
      routingControl.setWaypoints([]);
      document.getElementById('originField').value = '';
      document.getElementById('destinationField').value = '';
      document.getElementById('distanceKmDisplay').textContent = '0.000';
      document.getElementById('priceDisplay').textContent = '₱0.00';
    });


    // -----------------------------------------------------------
    // 4. INTEGRATED PRICING LOGIC (FIXED)
    // -----------------------------------------------------------
    
    // Default values (Fallback)
    let currentRates = { base: 60, km: 15, kg: 5 }; 

    // FIXED PATH VERSION
    async function fetchLiveRates() {
        const itemSelect = document.getElementById('itemHelper'); 
        let type = 'parcel'; 

        if(itemSelect && itemSelect.value) {
            const selectedOption = itemSelect.options[itemSelect.selectedIndex];
            if (selectedOption.getAttribute('data-type')) {
                type = selectedOption.getAttribute('data-type');
            }
        } else {
            const manualType = document.getElementById('packageType').value;
            if(manualType) type = manualType;
        }

        console.log("Targeting Core 2 API for:", type);

        try {
            // --- DITO ANG PAGBABAGO ---
            // Tumatarget na ito sa folder na 'db_core2_freight' sa localhost
            const response = await fetch(`/db_core2_freight/api_get_rates.php?type=${type}`);
            
            if (!response.ok) throw new Error(`HTTP Error! status: ${response.status}`);
            
            const data = await response.json();

            if(data.status === 'success') {
                const basePrice = parseFloat(data.rates.base);
                const kmRate = parseFloat(data.rates.km);
                const kgRate = parseFloat(data.rates.kg);

                currentRates.base = basePrice;
                currentRates.km = kmRate;
                currentRates.kg = kgRate;

                const slaText = document.getElementById('slaPromiseText');
                if(slaText) {
                    slaText.innerHTML = 
                    `<i class="bi bi-truck text-primary"></i> Fulfilled by: <strong>${data.provider}</strong> (${data.vehicle}) <br> 
                     <span class="badge bg-light text-dark border">Base: ₱${basePrice}</span> 
                     <span class="badge bg-light text-dark border">Rate: ₱${kmRate}/km</span>`;
                }

                const rateDisplay = document.getElementById('ratePerKmDisplay');
                if(rateDisplay) {
                    if (!isNaN(kmRate)) {
                        rateDisplay.innerHTML = '₱' + kmRate.toFixed(2);
                        rateDisplay.style.color = '#0d6efd';
                    } else {
                        rateDisplay.innerHTML = 'Err';
                    }
                }
            } else {
                console.warn("API Error:", data.message);
                document.getElementById('ratePerKmDisplay').textContent = "Offline";
            }
            
            calculateTotal();

        } catch (err) {
            console.error("Path Error:", err);
            // Visual cue pag hindi pa rin mahanap
            document.getElementById('slaPromiseText').innerHTML = "<span class='text-danger fw-bold'>Error: Cannot find API in /db_core2_freight/ folder</span>";
        }
    }

    function calculateTotal() {
      const distKm = parseFloat(document.getElementById('distance_km').value) || 0;
      const actualW = parseFloat(document.getElementById('actualWeight').value) || 0;
      const L = parseFloat(document.getElementById('dimL').value) || 0;
      const W = parseFloat(document.getElementById('dimW').value) || 0;
      const H = parseFloat(document.getElementById('dimH').value) || 0;

      // Volumetric
      const volW = (L * W * H) / 3500;
      const chargeableW = Math.max(actualW, volW);

      // UI Weight Update
      if (document.getElementById('volWeightDisplay')) document.getElementById('volWeightDisplay').innerText = volW.toFixed(2);
      if (document.getElementById('chargeableWeightDisplay')) document.getElementById('chargeableWeightDisplay').innerText = chargeableW.toFixed(2);

      // COST COMPUTATION (USING API RATES)
      const distCost = distKm * currentRates.km;
      const weightCost = chargeableW * currentRates.kg;
      
      let totalPrice = currentRates.base + distCost + weightCost;
      
      // Minimum Fare
      if (totalPrice < currentRates.base) totalPrice = currentRates.base;

      // Display Price
      document.getElementById('priceDisplay').innerText = '₱' + totalPrice.toLocaleString(undefined, { minimumFractionDigits: 2 });
      document.getElementById('priceDisplaySmall').innerText = '₱' + totalPrice.toLocaleString(undefined, { minimumFractionDigits: 2 });
      document.getElementById('price_php').value = totalPrice.toFixed(2);
    }

    // Listeners
    ['actualWeight', 'dimL', 'dimW', 'dimH', 'packageType'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.addEventListener('input', calculateTotal);
    });
    
    // Trigger API fetch when package type changes
    document.getElementById('packageType').addEventListener('change', fetchLiveRates);

    // Auto-Fill Helper
    document.getElementById('itemHelper').addEventListener('change', function() {
      const selected = this.options[this.selectedIndex];
      if (selected.value !== "") {
        document.getElementById('dimL').value = selected.getAttribute('data-l');
        document.getElementById('dimW').value = selected.getAttribute('data-w');
        document.getElementById('dimH').value = selected.getAttribute('data-h');
        document.getElementById('actualWeight').value = selected.getAttribute('data-k');
        
        const t = selected.getAttribute('data-type');
        const typeSelect = document.getElementById('packageType');
        if (typeSelect) typeSelect.value = t;

        fetchLiveRates(); // Fetch new pricing
      }
    });

    // 5. OTHER LOGIC (SLA, AI, PAYMENTS)
    async function getAiPrediction(origin, destination, distanceKm) {
      const timeDisplay = document.getElementById('aiPredictionTime');
      const scoreDisplay = document.getElementById('aiConfidenceScore');
      const reasonDisplay = document.getElementById('aiReasoning');
      timeDisplay.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

      try {
        const res = await fetch('get_prediction.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ origin, destination, distance_km: distanceKm })
        });
        if (res.ok) {
          const data = await res.json();
          if (!data.error) {
            timeDisplay.textContent = data.prediction;
            scoreDisplay.textContent = data.confidence + "%";
            reasonDisplay.textContent = data.reasoning;
            document.getElementById('aiConfidenceBar').style.width = data.confidence + "%";
          }
        } else {
          timeDisplay.textContent = "Est. " + (distanceKm / 40).toFixed(1) + " hrs";
        } 
      } catch (e) {}
    }

    function selectPayment(method, element) {
      document.querySelectorAll('.payment-option-card').forEach(el => el.classList.remove('selected'));
      element.classList.add('selected');
      document.getElementById('selectedPaymentMethod').value = method;
      document.getElementById('selectedBankName').value = "";
    }

    function selectEwallet(name) {
      document.getElementById('ewalletForm').classList.remove('d-none');
      document.getElementById('selectedBankName').value = name;
    }

    function simulateProcessing(source) {
      document.getElementById('paymentTabs').classList.add('d-none');
      document.querySelector('.tab-content').classList.add('d-none');
      document.getElementById('paymentProcessing').classList.remove('d-none');
      if (source === 'Credit Card') document.getElementById('selectedBankName').value = "Credit Card";
      setTimeout(() => {
        document.getElementById('paymentProcessing').classList.add('d-none');
        document.getElementById('paymentSuccess').classList.remove('d-none');
        setTimeout(() => {
          bootstrap.Modal.getInstance(document.getElementById('paymentGatewayModal')).hide();
          showPreviewModal();
        }, 1500);
      }, 2000);
    }

    document.getElementById("shipmentForm").addEventListener("submit", function(e) {
      e.preventDefault();
      const method = document.getElementById('selectedPaymentMethod').value;
      if (!method) { alert("Please select a payment method."); return; }
      if (!document.getElementById('contractAgree').checked) { alert("Please accept the contract."); return; }
      if (parseFloat(document.getElementById('distance_km').value) <= 0) { alert("Please search for a route first."); return; }
      
      if (method === 'online') {
        const pgModal = new bootstrap.Modal(document.getElementById('paymentGatewayModal'));
        document.getElementById('pgAmount').textContent = document.getElementById('priceDisplaySmall').textContent;
        document.getElementById('paymentTabs').classList.remove('d-none');
        document.querySelector('.tab-content').classList.remove('d-none');
        document.getElementById('paymentProcessing').classList.add('d-none');
        document.getElementById('paymentSuccess').classList.add('d-none');
        document.getElementById('ewalletForm').classList.add('d-none');
        pgModal.show();
      } else {
        showPreviewModal();
      }
    });

    function showPreviewModal() {
      const fd = new FormData(document.getElementById('shipmentForm'));
      const p = Object.fromEntries(fd.entries());
      document.getElementById("previewContractNumber").textContent = p.contract_number;
      document.getElementById("previewSenderName").textContent = p.sender_name;
      document.getElementById("previewSenderContact").textContent = p.sender_contact;
      document.getElementById("previewReceiverName").textContent = p.receiver_name;
      document.getElementById("previewReceiverContact").textContent = p.receiver_contact;
      document.getElementById("previewOrigin").textContent = p.origin_address;
      document.getElementById("previewDestination").textContent = p.destination_address;
      document.getElementById("previewPrice").textContent = '₱' + p.price_php;
      document.getElementById("previewAiTime").textContent = document.getElementById('aiPredictionTime').textContent;
      document.getElementById("previewPaymentMethod").textContent = document.getElementById('selectedPaymentMethod').value === 'online' ? 'Online Paid' : 'COD';
      new bootstrap.Modal(document.getElementById("inputPreviewModal")).show();
    }

    document.getElementById("finalConfirmBtn").addEventListener("click", async function() {
      const payload = Object.fromEntries(new FormData(document.getElementById('shipmentForm')).entries());
      payload.origin_island = document.getElementById('hiddenOriginIsland').value;
      payload.destination_island = document.getElementById('hiddenDestIsland').value;
      payload.payment_method = document.getElementById('selectedPaymentMethod').value;
      payload.bank_name = document.getElementById('selectedBankName').value;
      payload.sla_rules = ["Standard Terms"];
      payload.ai_estimated_time = document.getElementById('aiPredictionTime').textContent;

      bootstrap.Modal.getInstance(document.getElementById("inputPreviewModal")).hide();

      try { // Send to booking API
        const res = await fetch("api/bookshipment_api.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload)
        });
        const result = await res.json();
        const msg = document.getElementById("responseMessage");
        msg.classList.remove('d-none');
        if (result.success) {
          msg.className = "alert alert-success mt-3";
          msg.innerHTML = "✅ " + result.message + "<br>Shipment ID: " + result.shipment_id;
        } else {
          msg.className = "alert alert-danger mt-3";
          msg.innerHTML = "❌ Error: " + (result.error || 'Unknown');
        }
      } catch (e) { console.error(e); }
    });

    document.getElementById("acceptContract").addEventListener("click", () => {
      bootstrap.Modal.getInstance(document.getElementById("contractModal")).hide();
      document.getElementById("contractAgree").checked = true;
      document.getElementById("contractAgree").disabled = false;
    });

    function checkSLA() {
      const origin = document.getElementById('originIsland').value;
      const dest = document.getElementById('destIsland').value;
      if (!origin || !dest) return;
      const fd = new FormData();
      fd.append('origin_island', origin);
      fd.append('dest_island', dest);
      fetch('get_contract_logic.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
          document.getElementById('contractNumber').value = data.contract_number;
          document.getElementById('slaMaxDays').value = data.sla_days;
          document.getElementById('targetDeliveryDate').value = data.target_date;
          const badge = document.getElementById('contractStatusBadge');
          if (data.is_contracted) {
            badge.className = "badge bg-primary"; badge.innerText = "Contract Active";
          } else {
            badge.className = "badge bg-secondary"; badge.innerText = "No Contract";
          }
        }).catch(err => console.error(err));
    }

    function fetchNotifications() {
      fetch('api/get_notifications.php').then(r => r.json()).then(data => {
        const badge = document.getElementById('notifBadge');
        const list = document.getElementById('notifList');
        if (data.count > 0) { badge.innerText = data.count; badge.style.display = 'inline-block'; } 
        else { badge.style.display = 'none'; }
        let html = '';
        if (data.data.length > 0) {
          data.data.forEach(notif => {
            let bgClass = notif.is_read == 0 ? 'bg-light' : '';
            html += `<li><a class="dropdown-item ${bgClass} p-2 border-bottom" href="${notif.link}"><small class="fw-bold d-block">${notif.title}</small><small class="text-muted">${notif.message}</small></a></li>`;
          });
        } else { html = '<li class="text-center p-3 text-muted small">No notifications</li>'; }
        list.innerHTML = html;
      });
    }
    function markRead() {
      fetch('api/get_notifications.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=read_all' })
      .then(() => document.getElementById('notifBadge').style.display = 'none');
    }

    // 6. INITIAL RUN
    document.addEventListener("DOMContentLoaded", () => {
        checkSLA();
        fetchLiveRates(); // Pre-load rates para may laman na agad
        fetchNotifications();
    });
    setInterval(fetchNotifications, 5000);
  </script>
</body>

</html>