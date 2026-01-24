<?php
include("darkmode.php");
include("connection.php");
include('session.php');
include('loading.html');
requireRole('admin');

$admin_id = $_SESSION['account_id'];

// --- 1. HANDLE REPLY SUBMISSION ---
if (isset($_POST['send_reply'])) {
    $feedback_id = $_POST['feedback_id'];
    $reply_msg = trim($_POST['reply_message']);

    if (!empty($reply_msg)) {
        $stmt = $conn->prepare("INSERT INTO replies (feedback_id, admin_id, reply_message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $feedback_id, $admin_id, $reply_msg);
        
        if ($stmt->execute()) {
            $success_msg = "Reply sent successfully!";
            
            // NOTIFY THE USER
            $get_user = $conn->query("SELECT account_id FROM feedback WHERE id = '$feedback_id'");
            if($row = $get_user->fetch_assoc()){
                $user_to_notify = $row['account_id'];
                $n_title = "Support Update";
                $n_msg = "Admin replied to ticket #$feedback_id";
                $n_link = "feedback.php";
                $conn->query("INSERT INTO notifications (user_id, title, message, link, created_at) VALUES ('$user_to_notify', '$n_title', '$n_msg', '$n_link', NOW())");
            }

        } else {
            $error_msg = "Error sending reply.";
        }
        $stmt->close();
    }
}

// --- 2. FETCH TICKETS ---
$sql = "SELECT f.id, f.comment, f.attachment, f.created_at, a.username, a.email, a.profile_image 
        FROM feedback f 
        JOIN accounts a ON f.account_id = a.id 
        ORDER BY f.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Support & Feedback</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  
  <style>
    /* ADMIN STYLES */
    :root { --primary-color: #4e73df; --secondary-color: #f8f9fc; --dark-bg: #1a1a2e; --dark-card: #16213e; --text-light: #f8f9fa; --text-dark: #212529; --border-radius: 0.5rem; }
    body { font-family: 'Segoe UI', sans-serif; background-color: var(--secondary-color); color: var(--text-dark); }
    body.dark-mode { background-color: var(--dark-bg); color: var(--text-light); }
    
    .sidebar { width: 250px; height: 100vh; background: #2c3e50; color: white; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; transition: 0.3s; z-index: 1000; }
    .sidebar.collapsed { transform: translateX(-100%); }
    .content { margin-left: 250px; padding: 2rem; transition: 0.3s; }
    .content.expanded { margin-left: 0; }
    
    .sidebar a { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.2rem; text-decoration: none; color: rgba(255,255,255,0.9); }
    .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.2); border-left: 4px solid #fff; color: white; }
    
    .header { background: white; padding: 1rem; border-radius: var(--border-radius); box-shadow: 0 0.25rem 1rem rgba(0,0,0,0.1); margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; }
    .dark-mode .header { background-color: var(--dark-card); color: var(--text-light); }
    
    .ticket-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; border-left: 5px solid var(--primary-color); }
    .dark-mode .ticket-card { background: var(--dark-card); color: white; border-color: #6c8cff; }
    .ticket-header { padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
    .dark-mode .ticket-header { border-color: #2c3e50; }
    .ticket-body { padding: 20px; }
    .ticket-footer { padding: 15px 20px; background-color: #f8f9fc; border-top: 1px solid #eee; border-radius: 0 0 10px 10px; }
    .dark-mode .ticket-footer { background-color: #1f293a; border-color: #2c3e50; }
    .reply-thread { margin-top: 15px; padding: 15px; background-color: #f1f5f9; border-radius: 8px; border-left: 3px solid #1cc88a; }
    .dark-mode .reply-thread { background-color: #24344d; border-color: #1cc88a; }
    .reply-input { resize: none; border-radius: 5px; font-size: 0.9rem; }
    .attachment-img { max-width: 150px; height: auto; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; transition: 0.2s; margin-top: 10px; }
    .attachment-img:hover { opacity: 0.8; border-color: var(--primary-color); }

    .theme-switch { width: 50px; height: 25px; position: relative; display: inline-block; }
    .theme-switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; inset: 0; background-color: #ccc; border-radius: 34px; transition: .4s; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 3px; background-color: white; border-radius: 50%; transition: .4s; }
    input:checked+.slider { background-color: var(--primary-color); }
    input:checked+.slider:before { transform: translateX(24px); }
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
        <div class="collapse show" id="crmSubmenu" style="background: rgba(0,0,0,0.2);">
            <a href="CRM.php" class="ps-4"><i class="bi bi-dot"></i> CRM Dashboard</a>
            <a href="customer_feedback.php" class="ps-4 active"><i class="bi bi-dot"></i> Customer Feedback</a>
        </div>
        <a href="#csmSubmenu" data-bs-toggle="collapse" class="d-flex justify-content-between">
            <span><i class="bi bi-file-text"></i> Contract & SLA</span><i class="bi bi-chevron-down small"></i>
        </a>
        <div class="collapse" id="csmSubmenu" style="background: rgba(0,0,0,0.2);">
            <a href="admin_contracts.php" class="ps-4"><i class="bi bi-dot"></i> Manage Contracts</a>
            <a href="Admin_shipments.php" class="ps-4"><i class="bi bi-dot"></i> SLA Monitoring</a>
        </div>
        <a href="E-Doc.php"><i class="bi bi-folder2-open"></i> E-Docs</a>
        <a href="BIFA.php"><i class="bi bi-graph-up"></i> BI & Analytics</a>
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
        <h4 class="fw-bold mb-0">Helpdesk & Support Tickets</h4>
      </div>
      <div class="d-flex align-items-center gap-3">
        
        <div class="dropdown">
            <a href="#" class="text-dark position-relative" id="notifDropdown" data-bs-toggle="dropdown" onclick="markRead()">
                <i class="bi bi-bell fs-4"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="display: none;">0</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 300px; max-height: 400px; overflow-y: auto;">
                <li class="p-2 border-bottom fw-bold bg-light">Notifications</li>
                <div id="notifList">
                    <li class="text-center p-3 text-muted small">No new notifications</li>
                </div>
            </ul>
        </div>

        <div class="theme-toggle-container d-flex align-items-center gap-2">
            <small>Dark Mode</small>
            <label class="theme-switch">
            <input type="checkbox" id="adminThemeToggle"><span class="slider"></span>
            </label>
        </div>
      </div>
    </div>

    <?php if(isset($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= $success_msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="container-fluid p-0">
      
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($ticket = $result->fetch_assoc()): ?>
            
            <?php 
                // FETCH REPLIES
                $repliesQ = $conn->query("SELECT r.*, a.username as admin_name FROM replies r JOIN accounts a ON r.admin_id = a.id WHERE r.feedback_id = " . $ticket['id'] . " ORDER BY r.created_at ASC");
                $has_reply = ($repliesQ->num_rows > 0);
            ?>

            <div class="ticket-card">
                <div class="ticket-header">
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?= !empty($ticket['profile_image']) ? $ticket['profile_image'] : 'user.png' ?>" class="rounded-circle border" width="40" height="40" style="object-fit:cover;">
                        <div>
                            <h6 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($ticket['username']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($ticket['email']) ?></small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-secondary mb-1">Ticket #<?= $ticket['id'] ?></span>
                        <br>
                        <small class="text-muted" style="font-size: 0.75rem;"><?= date("M d, Y h:i A", strtotime($ticket['created_at'])) ?></small>
                    </div>
                </div>

                <div class="ticket-body">
                    <p class="mb-0 fs-6">
                        <i class="bi bi-chat-quote-fill text-muted me-2"></i>
                        <?= nl2br(htmlspecialchars($ticket['comment'])) ?>
                    </p>

                    <?php if(!empty($ticket['attachment'])): ?>
                        <div class="mt-3">
                            <small class="text-muted fw-bold d-block"><i class="bi bi-paperclip"></i> Attached Proof:</small>
                            <img src="uploads/<?= $ticket['attachment'] ?>" class="attachment-img" onclick="viewImage('uploads/<?= $ticket['attachment'] ?>')" alt="User Attachment">
                        </div>
                    <?php endif; ?>

                    <?php if($has_reply): ?>
                        <div class="mt-4">
                            <small class="text-uppercase fw-bold text-muted" style="font-size:0.7rem;">Conversation History</small>
                            <?php while($reply = $repliesQ->fetch_assoc()): ?>
                                <div class="reply-thread">
                                    <div class="d-flex justify-content-between mb-1">
                                        <strong class="text-success small"><i class="bi bi-headset me-1"></i> Admin <?= htmlspecialchars($reply['admin_name']) ?></strong>
                                        <small class="text-muted" style="font-size:0.7rem;"><?= date("M d, h:i A", strtotime($reply['created_at'])) ?></small>
                                    </div>
                                    <p class="mb-0 small text-dark"><?= nl2br(htmlspecialchars($reply['reply_message'])) ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="mt-3">
                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Pending Reply</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="ticket-footer">
                    <form method="POST">
                        <input type="hidden" name="feedback_id" value="<?= $ticket['id'] ?>">
                        <div class="input-group">
                            <textarea name="reply_message" class="form-control reply-input" rows="1" placeholder="Type your response here..." required></textarea>
                            <button type="submit" name="send_reply" class="btn btn-primary"><i class="bi bi-send-fill"></i> Reply</button>
                        </div>
                    </form>
                </div>
            </div>

        <?php endwhile; ?>
      <?php else: ?>
        <div class="text-center py-5">
            <h4 class="text-muted">No tickets found.</h4>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body text-center position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 bg-white" data-bs-dismiss="modal"></button>
                <img src="" id="modalImage" class="img-fluid rounded shadow-lg" style="max-height: 90vh;">
            </div>
        </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    initDarkMode("adminThemeToggle", "adminDarkMode");
    document.getElementById('hamburger').addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    });

    function viewImage(src) {
        document.getElementById('modalImage').src = src;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    }

    // --- ADMIN NOTIFICATION SCRIPT ---
    function fetchNotifications() {
        fetch('api/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            const list = document.getElementById('notifList');
            if (data.count > 0) { badge.innerText = data.count; badge.style.display = 'inline-block'; } 
            else { badge.style.display = 'none'; }
            let html = '';
            if (data.data.length > 0) {
                data.data.forEach(notif => {
                    let bgClass = notif.is_read == 0 ? 'bg-light' : '';
                    let icon = notif.is_read == 0 ? 'bi-circle-fill text-primary' : 'bi-check-circle text-muted';
                    html += `<li><a class="dropdown-item ${bgClass} p-2 border-bottom" href="${notif.link}"><div class="d-flex align-items-start"><i class="bi ${icon} me-2 mt-1" style="font-size: 10px;"></i><div><small class="fw-bold d-block">${notif.title}</small><small class="text-muted text-wrap">${notif.message}</small><br><small class="text-secondary" style="font-size: 0.7rem;">${new Date(notif.created_at).toLocaleString()}</small></div></div></a></li>`;
                });
            } else { html = '<li class="text-center p-3 text-muted small">No notifications</li>'; }
            list.innerHTML = html;
        });
    }
    function markRead() {
        fetch('api/get_notifications.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=read_all' })
        .then(() => { document.getElementById('notifBadge').style.display = 'none'; });
    }
    fetchNotifications();
    setInterval(fetchNotifications, 5000);
  </script>
</body>
</html>