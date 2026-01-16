<?php
// admin_contracts.php - PROFESSIONAL UI VERSION (UPDATED SIDEBAR)
include("connection.php");
include("darkmode.php");
include('session.php');
requireRole('admin');

// 1. FETCH MASTER RULES (Contract ID = 0)
$masterRulesQ = mysqli_query($conn, "SELECT * FROM sla_policies WHERE contract_id = 0");

// 2. FETCH CLIENT CONTRACTS (Generated ones)
$contractsQ = mysqli_query($conn, "SELECT c.*, a.email, a.username, a.profile_image 
                                   FROM contracts c 
                                   LEFT JOIN accounts a ON c.user_id = a.id 
                                   WHERE c.id != 0 ORDER BY c.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Management | Core Transaction</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        /* --- ADMIN TEMPLATE STYLES --- */
        :root {
            --primary: #4e73df; --secondary: #f8f9fc; --dark-bg: #1e1e2f; --dark-card: #2b2b40;
            --light-text: #f8f9fa; --radius: 1rem; --shadow: 0 0.35rem 1.2rem rgba(0, 0, 0, 0.15);
            --sidebar-width: 250px;
            
            /* CONTRACT SPECIFIC */
            --freight-blue: #2563eb;
            --freight-bg: #f8fafc;
            --freight-card: #ffffff;
            --border-color: #e2e8f0;
        }

        body { font-family: 'Segoe UI', system-ui, sans-serif; background-color: var(--secondary); transition: all 0.3s ease; }
        
        /* DARK MODE */
        body.dark-mode { background-color: var(--dark-bg); color: var(--light-text); }
        body.dark-mode .card, body.dark-mode .header, body.dark-mode .nav-tabs .nav-link.active { background-color: var(--dark-card); border-color: #444; color: white; }
        body.dark-mode .table { color: var(--light-text); border-color: #444; }
        body.dark-mode .table thead th { background-color: #1e1e2f; color: #ccc; border-bottom: 1px solid #444; }
        body.dark-mode td { border-bottom: 1px solid #444; }
        body.dark-mode .nav-tabs { border-bottom-color: #444; }
        body.dark-mode .nav-link { color: #94a3b8; }
        body.dark-mode .nav-link:hover { color: white; }
        body.dark-mode .form-control, body.dark-mode .form-select { background-color: #334155; border-color: #475569; color: white; }

        /* SIDEBAR STYLES (MATCHING OTHER PAGES) */
        .sidebar { width: var(--sidebar-width); height: 100vh; background: #2c3e50; color: white; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; transition: 0.3s; z-index: 1000; }
        .sidebar.collapsed { transform: translateX(-100%); }
        .content { margin-left: var(--sidebar-width); padding: 2rem; transition: 0.3s; }
        .content.expanded { margin-left: 0; }
        
        .sidebar a { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.2rem; text-decoration: none; color: rgba(255,255,255,0.9); }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.2); border-left: 4px solid #fff; color: white; }

        /* COMPONENTS */
        .header { background: white; border-radius: var(--radius); box-shadow: var(--shadow); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .card { border: none; border-radius: var(--radius); padding: 1.5rem; background: white; box-shadow: var(--shadow); margin-bottom: 1.5rem; }
        
        /* TABS Styling */
        .nav-tabs { border-bottom: 2px solid var(--border-color); margin-bottom: 1.5rem; }
        .nav-link { color: #64748b; font-weight: 600; padding: 10px 20px; border: none; border-bottom: 3px solid transparent; }
        .nav-link.active { color: var(--freight-blue); background: transparent; border-bottom-color: var(--freight-blue); }
        .nav-link:hover { border-color: transparent; color: var(--freight-blue); }

        /* Table Styling */
        .table-responsive { border-radius: 8px; }
        thead th { background: #f8fafc; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; padding: 1rem; border-bottom: 2px solid var(--border-color); }
        tbody td { padding: 1rem; vertical-align: middle; font-size: 0.95rem; }
        
        .client-avatar { width: 35px; height: 35px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #64748b; }

        /* Toggle */
        .theme-switch { position: relative; width: 44px; height: 22px; display: inline-block; }
        .theme-switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; inset: 0; background-color: #ccc; border-radius: 34px; transition: .4s; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 2px; background-color: white; border-radius: 50%; transition: .4s; }
        input:checked+.slider { background-color: var(--primary); }
        input:checked+.slider:before { transform: translateX(20px); }
    </style>
</head>

<body>

    <div class="sidebar" id="sidebar">
        <div>
            <div class="text-center p-3 border-bottom border-secondary">
                <img src="Remorig.png" alt="Logo" style="width: 100px;">
                <h6 class="mt-2 mb-0 text-light">CORE ADMIN</h6>
            </div>
            <nav class="mt-3">
                <a href="admin.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                
                <a href="#crmSubmenu" data-bs-toggle="collapse" class="d-flex justify-content-between">
                    <span><i class="bi bi-people"></i> CRM</span><i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse" id="crmSubmenu" style="background: rgba(0,0,0,0.2);">
                    <a href="CRM.php" class="ps-4"><i class="bi bi-dot"></i> CRM Dashboard</a>
                    <a href="customer_feedback.php" class="ps-4"><i class="bi bi-dot"></i> Customer Feedback</a>
                </div>

                <a href="#csmSubmenu" data-bs-toggle="collapse" class="d-flex justify-content-between active" aria-expanded="true">
                    <span><i class="bi bi-file-text"></i> Contract & SLA</span><i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse show" id="csmSubmenu" style="background: rgba(0,0,0,0.2);">
                    <a href="admin_contracts.php" class="ps-4 active"><i class="bi bi-dot"></i> Manage Contracts</a>
                    <a href="Admin_shipments.php" class="ps-4"><i class="bi bi-dot"></i> SLA Monitoring</a>
                </div>

                <a href="E-Doc.php"><i class="bi bi-folder2-open"></i> E-Docs</a>
                <a href="BIFA.php"><i class="bi bi-graph-up"></i> BI & Analytics</a>
                <a href="admin_reports.php"><i class="bi bi-file-earmark-bar-graph"></i> Reports Generation</a>
                <a href="activity-log.php"><i class="bi bi-clock-history"></i> Activity Log</a>
                <a href="Archive.php"><i class="bi bi-archive"></i> Archives</a>
                <a href="logout.php" class="border-top mt-3"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </nav>
        </div>
    </div>

    <div class="content" id="mainContent">
        
        <div class="header">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-list fs-4" id="hamburger" style="cursor: pointer;"></i>
                <h4 class="fw-bold m-0">Contract Registry</h4>
            </div>
            <div class="d-flex align-items-center gap-2">
                <small class="fw-bold" style="font-size: 0.75rem;">DARK MODE</small>
                <label class="theme-switch">
                    <input type="checkbox" id="adminThemeToggle">
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <div class="card border-0 shadow-none bg-transparent p-0">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="contracts-tab" data-bs-toggle="tab" data-bs-target="#contracts-pane" type="button" role="tab">
                        <i class="bi bi-folder2-open me-2"></i> Client Contracts
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings-pane" type="button" role="tab">
                        <i class="bi bi-sliders me-2"></i> Master SLA Configuration
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                
                <div class="tab-pane fade show active" id="contracts-pane" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold text-secondary m-0">Active Agreements</h5>
                                <button class="btn btn-primary" onclick="syncUsers()">
                                    <i class="bi bi-arrow-repeat me-1"></i> Sync / Generate All
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Contract Ref</th>
                                            <th>Client Name</th>
                                            <th>Validity</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(mysqli_num_rows($contractsQ) > 0): ?>
                                            <?php while($row = mysqli_fetch_assoc($contractsQ)): ?>
                                            <tr>
                                                <td class="fw-bold text-primary font-monospace">
                                                    <?php echo $row['contract_number']; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="client-avatar">
                                                            <?php echo strtoupper(substr($row['client_name'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold"><?php echo $row['client_name']; ?></div>
                                                            <div class="small text-muted"><?php echo $row['email']; ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="small">
                                                    <div>From: <?php echo date('M d, Y', strtotime($row['start_date'])); ?></div>
                                                    <div class="text-muted">To: <?php echo date('M d, Y', strtotime($row['end_date'])); ?></div>
                                                </td>
                                                <td><span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Active</span></td>
                                                <td class="text-end">
                                                    <a href="contract_print.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-sm btn-outline-dark">
                                                        <i class="bi bi-printer me-1"></i> Print
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" class="text-center py-5 text-muted">No contracts found. Click Sync to generate.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="settings-pane" role="tabpanel">
                    <div class="card border-primary border-opacity-25">
                        <div class="card-body">
                            <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                                <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                                <div>
                                    <strong>Master Configuration:</strong> Rules set here will be automatically applied to <u>ALL</u> newly generated contracts.
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold m-0">Standard Shipping Rules</h6>
                                <button class="btn btn-outline-primary btn-sm" onclick="openRulesModal()">
                                    <i class="bi bi-plus-lg"></i> Add New Rule
                                </button>
                            </div>

                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Origin Group</th>
                                        <th>Destination Group</th>
                                        <th>Committed Lead Time</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($masterRulesQ) > 0): ?>
                                        <?php while($rule = mysqli_fetch_assoc($masterRulesQ)): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo $rule['origin_group']; ?></td>
                                            <td><i class="bi bi-arrow-right text-muted me-2"></i> <?php echo $rule['destination_group']; ?></td>
                                            <td><span class="badge bg-warning text-dark"><?php echo $rule['max_days']; ?> Days</span></td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-light text-danger" onclick="deleteRule(<?php echo $rule['id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center py-3 text-muted">No global rules configured.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="rulesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Configure Global Rule</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addRuleForm">
                        <input type="hidden" name="action" value="add_rule">
                        <input type="hidden" name="contract_id" value="0">
                        
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="small fw-bold text-muted">Origin</label>
                                <select class="form-select" name="origin_group" required>
                                    <option value="Metro Manila">Metro Manila</option>
                                    <option value="Luzon">Luzon</option>
                                    <option value="Visayas">Visayas</option>
                                    <option value="Mindanao">Mindanao</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="small fw-bold text-muted">Destination</label>
                                <select class="form-select" name="destination_group" required>
                                    <option value="Metro Manila">Metro Manila</option>
                                    <option value="Luzon">Luzon</option>
                                    <option value="Visayas">Visayas</option>
                                    <option value="Mindanao">Mindanao</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="small fw-bold text-muted">Max Lead Time (Days)</label>
                                <input type="number" class="form-control" name="max_days" value="3" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary w-100" onclick="submitRule()">Save Configuration</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Init UI
        if (typeof initDarkMode === 'function') initDarkMode("adminThemeToggle", "adminDarkMode");
        
        // Sidebar Toggle
        document.getElementById('hamburger').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('expanded');
        });

        // Rules Logic
        const rulesModal = new bootstrap.Modal(document.getElementById('rulesModal'));

        function openRulesModal() { rulesModal.show(); }

        function submitRule() {
            const form = document.getElementById('addRuleForm');
            if(!form.checkValidity()) { form.reportValidity(); return; }

            const fd = new FormData(form);
            fetch('admin_contracts_api.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if(d.success) { alert("✅ Rule Added!"); location.reload(); }
                else { alert("❌ Error: " + d.error); }
            })
            .catch(e => alert("Network Error"));
        }

        function syncUsers() {
            if(confirm("Generate contracts for ALL new users based on Master Rules?")) {
                const fd = new FormData();
                fd.append('action', 'sync_all_users');
                fetch('admin_contracts_api.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    alert(d.message);
                    location.reload();
                });
            }
        }

        function deleteRule(id) {
            if(confirm("Delete this rule?")) {
                const fd = new FormData();
                fd.append('action', 'delete_rule');
                fd.append('rule_id', id);
                fetch('admin_contracts_api.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => { if(d.success) location.reload(); });
            }
        }
    </script>
</body>
</html>