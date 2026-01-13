<?php
include("connection.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Shipment Monitoring</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4e73df;
            --secondary: #f8f9fc;
            --dark-bg: #1e1e2f;
            --dark-card: #2b2b40;
            --light-text: #f8f9fa;
            --radius: 1rem;
            --shadow: 0 0.35rem 1.2rem rgba(0, 0, 0, 0.15);
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: var(--secondary);
            transition: all 0.3s ease;
        }

        body.dark-mode {
            background-color: var(--dark-bg);
            color: var(--light-text);
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: #2c3e50;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar .logo {
            text-align: center;
            padding: 1.2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar .logo img {
            width: 100px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.85rem 1.2rem;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.9);
            transition: 0.2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            border-left: 4px solid #fff;
        }

        .content {
            margin-left: 250px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        .content.expanded {
            margin-left: 0;
        }

        .header {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        body.dark-mode .header {
            background: var(--dark-card);
            color: var(--light-text);
        }

        .hamburger {
            cursor: pointer;
            font-size: 1.5rem;
        }

        .card {
            border: none;
            border-radius: var(--radius);
            padding: 1.5rem;
            background: white;
            box-shadow: var(--shadow);
        }

        body.dark-mode .card {
            background: var(--dark-card);
            color: var(--light-text);
        }

        /* Table Styles for Dark Mode */
        body.dark-mode .table {
            color: var(--light-text);
            border-color: #444;
        }

        body.dark-mode .table thead th {
            background-color: #3b3b52;
            color: white;
            border-color: #444;
        }

        body.dark-mode .table td {
            border-color: #444;
        }

        body.dark-mode .table-hover tbody tr:hover {
            background-color: #3b3b52;
            color: white;
        }

        .theme-switch {
            width: 50px;
            height: 25px;
            position: relative;
            display: inline-block;
        }

        .theme-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background-color: #ccc;
            border-radius: 34px;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 3px;
            background-color: white;
            border-radius: 50%;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: var(--primary);
        }

        input:checked+.slider:before {
            transform: translateX(24px);
        }

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
        <div>
            <div class="logo">
                <img src="Remorig.png" alt="Logo" style="width: 80px;">
                <h6 class="mt-2 mb-0 text-light fw-normal">CORE TRANSACTION 3</h6>
            </div>
           <nav class="mt-3">
    <a href="admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <a href="#crmSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people"></i> CRM</span>
        <i class="bi bi-chevron-down" style="font-size: 0.8em;"></i>
    </a>
    
    <div class="collapse <?php echo (basename($_SERVER['PHP_SELF']) == 'CRM.php' || basename($_SERVER['PHP_SELF']) == 'customer_feedback.php') ? 'show' : ''; ?>" id="crmSubmenu" style="background: rgba(0,0,0,0.2);">
        
        <a href="CRM.php" class="ps-4 <?php echo basename($_SERVER['PHP_SELF']) == 'CRM.php' ? 'active' : ''; ?>" style="font-size: 0.9em;">
            <i class="bi bi-dot"></i> CRM Dashboard
        </a>
        
        <a href="customer_feedback.php" class="ps-4 <?php echo basename($_SERVER['PHP_SELF']) == 'customer_feedback.php' ? 'active' : ''; ?>" style="font-size: 0.9em;">
            <i class="bi bi-dot"></i> Customer Feedback
        </a>
        
    </div>
    <a href="#csmSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-text"></i> Contract & SLA</span>
        <i class="bi bi-chevron-down" style="font-size: 0.8em;"></i>
    </a>
    <div class="collapse <?php echo (basename($_SERVER['PHP_SELF']) == 'Admin_contracts.php' || basename($_SERVER['PHP_SELF']) == 'Admin_shipments.php') ? 'show' : ''; ?>" id="csmSubmenu" style="background: rgba(0,0,0,0.2);">
        <a href="Admin_contracts.php" class="ps-4 <?php echo basename($_SERVER['PHP_SELF']) == 'Admin_contracts.php' ? 'active' : ''; ?>" style="font-size: 0.9em;">
            <i class="bi bi-dot"></i> Manage Contracts
        </a>
        <a href="Admin_shipments.php" class="ps-4 <?php echo basename($_SERVER['PHP_SELF']) == 'Admin_shipments.php' ? 'active' : ''; ?>" style="font-size: 0.9em;">
            <i class="bi bi-dot"></i> SLA Monitoring
        </a>
    </div>

    <a href="E-Doc.php"><i class="bi bi-folder2-open"></i> E-Docs</a>
    <a href="BIFA.php"><i class="bi bi-graph-up"></i> BI & Freight Analytics</a>
    <a href="activity-log.php"><i class="bi bi-clock-history"></i> Activity Log</a>
    <a href="Archive.php"><i class="bi bi-archive"></i> Archived Docs</a>

    <a href="logout.php" class="border-top mt-3"><i class="bi bi-box-arrow-right"></i> Logout</a>
</nav>
        </div>
    </div>

    <div class="content" id="mainContent">
        <div class="header">
            <div class="d-flex align-items-center gap-3">
                <div class="hamburger" id="hamburger"><i class="bi bi-list"></i></div>
                <h4 class="fw-bold mb-0">Shipments <span class="text-primary">| Operations</span></h4>
            </div>
            <div class="theme-toggle-container d-flex align-items-center gap-2">
                <small>Dark Mode</small>
                <label class="theme-switch">
                    <input type="checkbox" id="adminThemeToggle">
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <div class="card">
            <h5 class="fw-bold mb-3"><i class="bi bi-truck"></i> Live Shipments & SLA Status</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Tracking ID</th>
                            <th>Status</th>
                            <th>Target Date (SLA)</th>
                            <th>Performance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($conn, "SELECT * FROM shipments ORDER BY id DESC");
                        while ($row = mysqli_fetch_assoc($q)):
                            $status = $row['status'];
                            $badge = 'bg-secondary';
                            if ($status == 'Pending') $badge = 'bg-warning text-dark';
                            elseif ($status == 'In Transit') $badge = 'bg-info text-white';
                            elseif ($status == 'Delivered') $badge = 'bg-success';

                            // SLA Computation
                            $slaText = "No SLA Record";
                            $slaBadge = "";
                            $target = $row['target_delivery_date'];

                            if (!empty($target) && $target != '0000-00-00') {
                                $daysLeft = (strtotime($target) - time()) / (60 * 60 * 24);
                                $daysLeft = round($daysLeft);

                                if ($status == 'Delivered') {
                                    if ($row['sla_status'] == 'Met') $slaText = "✅ On Time";
                                    elseif ($row['sla_status'] == 'Breached') $slaText = "❌ Late Delivery";
                                    else $slaText = "Delivered";
                                } else {
                                    if ($daysLeft < 0) $slaText = "<span class='text-danger fw-bold'>Overdue by " . abs($daysLeft) . " days</span>";
                                    elseif ($daysLeft <= 1) $slaText = "<span class='text-warning fw-bold'>Due Tomorrow!</span>";
                                    else $slaText = "<span class='text-success'>$daysLeft Days Left</span>";
                                }
                            }
                        ?>
                            <tr>
                                <td class="fw-bold">
                                    <?php echo "TRK-" . str_pad($row['id'], 6, "0", STR_PAD_LEFT); ?><br>
                                    <small class="text-muted fw-normal">To: <?php echo $row['receiver_name']; ?></small>
                                </td>
                                <td><span class="badge <?php echo $badge; ?>"><?php echo $status; ?></span></td>
                                <td><?php echo (!empty($target) && $target != '0000-00-00') ? date('M d, Y', strtotime($target)) : '-'; ?></td>
                                <td><?php echo $slaText; ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Manage</button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $row['id']; ?>, 'In Transit')">Set: In Transit</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $row['id']; ?>, 'Delivered')">Set: Delivered</a></li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li><a class="dropdown-item" href="waybill.php?id=<?php echo $row['id']; ?>" target="_blank">🖨️ Print Waybill</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="podModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-camera"></i> Proof of Delivery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="podForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="podShipmentId">
                    <input type="hidden" name="status" value="Delivered">
                    
                    <div class="text-center mb-3">
                        <i class="bi bi-box-seam display-1 text-success"></i>
                        <p class="fw-bold">Mark as Delivered</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload Proof (Photo/Signature)</label>
                        <input type="file" class="form-control" name="proof_image" accept="image/*" required>
                        <div class="form-text small">Required for verification.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitPOD()">Confirm Upload</button>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- DARK MODE & UI LOGIC ---
        const toggle = document.getElementById('adminThemeToggle');
        const body = document.body;
        if (localStorage.getItem('theme') === 'dark') {
            body.classList.add('dark-mode');
            toggle.checked = true;
        }
        toggle.addEventListener('change', () => {
            if (toggle.checked) {
                body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            }
        });
        document.getElementById('hamburger').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('expanded');
        });

        // --- FUNCTIONAL LOGIC (Shipments) ---
        // Initialize Modal
    const podModal = new bootstrap.Modal(document.getElementById('podModal'));

    // 1. Main Update Function
    function updateStatus(id, status) {
        if (status === 'Delivered') {
            // Pag Delivered, buksan ang Modal para sa Picture
            document.getElementById('podShipmentId').value = id;
            podModal.show();
        } else {
            // Pag iba (In Transit), diretso save
            if(!confirm("Update status to " + status + "?")) return;
            const fd = new FormData();
            fd.append('id', id);
            fd.append('status', status);
            sendUpdate(fd);
        }
    }

    // 2. Submit POD (Pag click ng Confirm sa modal)
    function submitPOD() {
        const form = document.getElementById('podForm');
        const fileInput = form.querySelector('input[type="file"]');
        
        if(fileInput.files.length === 0) {
            alert("Please upload a picture first!");
            return;
        }

        const fd = new FormData(form);
        sendUpdate(fd);
    }

    // 3. AJAX Sender
    function sendUpdate(formData) {
        fetch('update_status_pod.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(d => {
            if(d.success) {
                alert(d.message);
                location.reload();
            } else {
                alert("Error: " + d.message);
            }
        })
        .catch(e => {
            console.error(e);
            alert("System Error. Check console.");
        });
    }
    </script>
</body>

</html>