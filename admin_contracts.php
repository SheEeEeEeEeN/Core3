<?php
// Admin_contracts.php - NO UPLOAD VERSION
include("connection.php");

// Fetch Data
$usersQ = mysqli_query($conn, "SELECT id, email FROM accounts WHERE role='user' OR role='admin'");
$contractsQ = mysqli_query($conn, "SELECT c.*, a.email FROM contracts c LEFT JOIN accounts a ON c.user_id = a.id ORDER BY c.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        /* CORE STYLES */
        :root { --primary: #4e73df; --secondary: #f8f9fc; --dark-bg: #1e1e2f; --dark-card: #2b2b40; --light-text: #f8f9fa; --radius: 1rem; --shadow: 0 0.35rem 1.2rem rgba(0, 0, 0, 0.15); }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background-color: var(--secondary); transition: all 0.3s ease; }
        body.dark-mode { background-color: var(--dark-bg); color: var(--light-text); }
        
        /* SIDEBAR */
        .sidebar { width: 250px; height: 100vh; background: #2c3e50; color: white; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease; z-index: 1000; }
        .sidebar.collapsed { transform: translateX(-100%); }
        .sidebar .logo { text-align: center; padding: 1.2rem; border-bottom: 1px solid rgba(255, 255, 255, 0.2); }
        .sidebar .logo img { width: 100px; }
        .sidebar a { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.2rem; text-decoration: none; color: rgba(255, 255, 255, 0.9); transition: 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255, 255, 255, 0.2); color: #fff; border-left: 4px solid #fff; }

        .content { margin-left: 250px; padding: 2rem; transition: margin-left 0.3s ease; }
        .content.expanded { margin-left: 0; }
        .header { background: white; border-radius: var(--radius); box-shadow: var(--shadow); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        body.dark-mode .header { background: var(--dark-card); color: var(--light-text); }
        .card { border: none; border-radius: var(--radius); padding: 1.5rem; background: white; box-shadow: var(--shadow); }
        body.dark-mode .card { background: var(--dark-card); color: var(--light-text); }
        
        /* TABLE */
        body.dark-mode .table { color: var(--light-text); border-color: #444; }
        body.dark-mode .table thead th { background-color: #3b3b52; color: white; border-color: #444; }
        body.dark-mode .table td { border-color: #444; }
        
        /* TOGGLE */
        .theme-switch { position: relative; display: inline-block; width: 50px; height: 25px; }
        .theme-switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; inset: 0; background-color: #ccc; border-radius: 34px; transition: .4s; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 3px; background-color: white; border-radius: 50%; transition: .4s; }
        input:checked+.slider { background-color: var(--primary); }
        input:checked+.slider:before { transform: translateX(24px); }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .content { margin-left: 0; }
        }
    </style>
</head>

<body>

    <div class="sidebar" id="sidebar">
        <div>
            <div class="logo">
                <img src="Remorig.png" alt="Logo">
                <h6 class="mt-2 mb-0 text-light fw-normal">CORE TRANSACTION 3</h6>
            </div>
            <nav class="mt-3">
                <a href="admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="#crmSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people"></i> CRM</span> <i class="bi bi-chevron-down" style="font-size: 0.8em;"></i>
                </a>
                <div class="collapse" id="crmSubmenu" style="background: rgba(0,0,0,0.2);">
                    <a href="CRM.php" class="ps-4"><i class="bi bi-dot"></i> CRM Dashboard</a>
                    <a href="customer_feedback.php" class="ps-4"><i class="bi bi-dot"></i> Customer Feedback</a>
                </div>
                <a href="#csmSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-file-text"></i> Contract & SLA</span> <i class="bi bi-chevron-down" style="font-size: 0.8em;"></i>
                </a>
                <div class="collapse show" id="csmSubmenu" style="background: rgba(0,0,0,0.2);">
                    <a href="admin_contracts.php" class="ps-4 active"><i class="bi bi-dot"></i> Manage Contracts</a>
                    <a href="admin_shipments.php" class="ps-4"><i class="bi bi-dot"></i> SLA Monitoring</a>
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
                <h4 class="fw-bold mb-0">Contracts <span class="text-primary">| Digital Registry</span></h4>
            </div>
            <div class="d-flex align-items-center gap-4">
                <div class="theme-toggle-container">
                    <small>Dark Mode</small>
                    <label class="theme-switch">
                        <input type="checkbox" id="adminThemeToggle">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold"><i class="bi bi-files"></i> Active Contracts</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createContractModal">
                    <i class="bi bi-plus-lg"></i> New Contract
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Contract #</th>
                            <th>Client / User</th>
                            <th>Validity Period</th>
                            <th>Status</th>
                            <th>SLA Rules</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($contractsQ) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($contractsQ)): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?php echo $row['contract_number']; ?></td>
                                    <td>
                                        <strong><?php echo $row['client_name']; ?></strong><br>
                                        <small class="opacity-75"><?php echo $row['email']; ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($row['start_date'])); ?> to <br>
                                        <?php echo date('M d, Y', strtotime($row['end_date'])); ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $badgeClass = ($row['status'] == 'Active') ? 'bg-success' : 'bg-danger';
                                            echo '<span class="badge '.$badgeClass.'">'.$row['status'].'</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cnt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM sla_policies WHERE contract_id='{$row['id']}'"));
                                        echo '<span class="badge bg-secondary">' . $cnt['c'] . ' Rules</span>';
                                        ?>
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-info" onclick="openRulesModal(<?php echo $row['id']; ?>, '<?php echo $row['contract_number']; ?>')">
                                                <i class="bi bi-gear"></i> Rules
                                            </button>

                                            <a href="contract_print.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Generate Contract">
                                                <i class="bi bi-printer"></i> Print
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4">No contracts found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createContractModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Create New Contract</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createContractForm">
                        <input type="hidden" name="action" value="create_contract">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select User Account</label>
                            <select class="form-select" name="user_id" required>
                                <option value="" disabled selected>Choose User...</option>
                                <?php 
                                mysqli_data_seek($usersQ, 0); 
                                while ($u = mysqli_fetch_assoc($usersQ)): ?>
                                    <option value="<?php echo $u['id']; ?>"><?php echo $u['email']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Client Company Name</label>
                            <input type="text" class="form-control" name="client_name" required placeholder="e.g. Lazada Logistics">
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                        </div>

                        </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitContract()">Create Contract</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rulesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">SLA Rules: <span id="ruleContractNum"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addRuleForm">
                        <input type="hidden" name="action" value="add_rule">
                        <input type="hidden" name="contract_id" id="ruleContractId">
                        
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="small fw-bold">Origin</label>
                                <select class="form-select form-select-sm" name="origin_group" required>
                                    <option value="" disabled selected>Select...</option>
                                    <option value="Luzon">Luzon</option>
                                    <option value="Visayas">Visayas</option>
                                    <option value="Mindanao">Mindanao</option>
                                    <option value="Metro Manila">Metro Manila</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="small fw-bold">Destination</label>
                                <select class="form-select form-select-sm" name="destination_group" required>
                                    <option value="" disabled selected>Select...</option>
                                    <option value="Luzon">Luzon</option>
                                    <option value="Visayas">Visayas</option>
                                    <option value="Mindanao">Mindanao</option>
                                    <option value="Metro Manila">Metro Manila</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="small fw-bold">Max Days</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" name="max_days" value="3" required>
                                    <button type="button" class="btn btn-success" onclick="submitRule()">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="alert alert-light text-center small border">
                        Example: <strong>Metro Manila</strong> to <strong>Visayas</strong> should be <strong>5 Days</strong>.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- UI LOGIC ---
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

        // --- SUBMIT CONTRACT (Debug Version) ---
        function submitContract() {
            const form = document.getElementById('createContractForm');
            if(!form.checkValidity()){
                form.reportValidity();
                return;
            }

            const fd = new FormData(form);

            fetch('admin_contracts_api.php', {
                method: 'POST',
                body: fd
            })
            .then(response => response.text())
            .then(text => {
                console.log("Raw Server Response:", text);
                try {
                    const d = JSON.parse(text);
                    if (d.success) {
                        alert("✅ SUCCESS: " + d.message);
                        location.reload();
                    } else {
                        alert("❌ API ERROR: " + d.error);
                    }
                } catch (e) {
                    console.error("Parse Error:", e);
                    alert("⚠️ SYSTEM ERROR:\n" + text.substring(0, 500)); 
                }
            })
            .catch(e => {
                console.error("Fetch Error:", e);
                alert("Network or Server Error.");
            });
        }

        // --- SUBMIT RULE (Debug Version) ---
        const rulesModal = new bootstrap.Modal(document.getElementById('rulesModal'));

        function openRulesModal(id, num) {
            document.getElementById('ruleContractId').value = id;
            document.getElementById('ruleContractNum').innerText = num;
            rulesModal.show();
        }

        function submitRule() {
            const form = document.getElementById('addRuleForm');
            if(!form.checkValidity()){
                form.reportValidity();
                return;
            }

            const fd = new FormData(form);

            fetch('admin_contracts_api.php', {
                method: 'POST',
                body: fd
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const d = JSON.parse(text);
                    if (d.success) {
                        alert("✅ SUCCESS: " + d.message);
                        location.reload();
                    } else {
                        alert("❌ API ERROR: " + d.error);
                    }
                } catch (e) {
                    alert("⚠️ SYSTEM ERROR:\n" + text);
                }
            })
            .catch(e => {
                alert("Network Error.");
            });
        }
    </script>
</body>
</html>