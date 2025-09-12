<?php
include("darkmode.php");
include("connection.php");
include('session.php');
requireRole('admin');

// Fetch feedback messages
$unread = [];
$read   = [];

$sql = "SELECT f.id, f.comment, f.created_at, a.username, f.status 
        FROM feedback f 
        JOIN accounts a ON f.account_id = a.id 
        ORDER BY f.created_at DESC";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['status'] === 'unread') {
            $unread[] = $row;
        } else {
            $read[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | CORE3 Customer Relationship & Business Control</title>
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

        /* Table Section */
        .searchnotif-section {
            position: relative;
            background-color: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 1rem;
        }

        .dark-mode .searchnotif-section {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .portalcontent {
            display: flex;
        }

        .search-control input {
            width: 770px;
            padding: 0.4rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            margin-right: 1.5rem;
        }

        .dark-mode .search-control input {
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
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

        .notif-section {
            margin-top: 1rem;
        }

        .dark-mode .notif_section {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .notif-section h2 {
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .notif-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .notif-item {
            background: #fff;
            padding: 1rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 1rem;
        }

        body.dark-mode .notif-item {
            background: #1e2b53ff;
            color: var(--text-light);
        }

        .Ureply {
            font-size: 1rem;
            color: #2d7ff2ff;
        }

        .notif-item small {
            font-size: 0.8rem;
            color: gray;
        }

        .notif-comment {
            margin: 0.5rem 0;
        }

        .reply-box {
            margin-top: 0.5rem
        }

        .reply-box textarea {
            width: 100%;
            min-height: 60px;
            padding: 0.5rem;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        body.dark-mode .reply-box textarea {
            background: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }

        .reply-box button {
            margin-top: 0.5rem;
            background: var(--primary-color);
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
        }

        .reply-box button:hover {
            background: #3c5ac2;
        }

        .replies {
            margin-top: 0.5rem;
            padding: 0.5rem 0 0 1rem;
            border-left: 3px solid var(--primary-color);
            font-size: 0.9rem;
        }

        .replies ul {
            list-style: none;
            padding-left: 0;
        }

        .replies li {
            margin-bottom: 0.3rem;
        }

        /* Scrollable unread container */
        .unread-container {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .unread-container::-webkit-scrollbar {
            width: 8px;
        }

        .unread-container::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        .unread-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
    </style>
</head>

<body>
    <div class="sidebar" id="sidebar">
        <div class="logo"> <img src="rem.png" alt="SLATE Logo"> </div>
        <div class="system-name">CORE TRANSACTION 3</div>
        <a href="admin.php">Dashboard</a>
        <a href="CRM.php">Customer Relationship Management</a>
        <a href="CSM.php">Contract & SLA Monitoring</a>
        <a href="E-Doc.php">E-Documentations & Compliance Manager</a>
        <a href="BIFA.php">Business Intelligence & Freight Analytics</a>
        <a href="CPN.php" class="active">Customer Portal & Notification Hub</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content" id="mainContent">
        <div class="header">
            <div class="hamburger" id="hamburger">☰</div>
            <h1>Customer Portal & Notification Hub</h1>
            <div class="theme-toggle-container">
                <span class="theme-label">Dark Mode</span>
                <label class="theme-switch">
                    <input type="checkbox" id="adminThemeToggle">
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <!-- Unread Notifications -->
        <div class="searchnotif-section">
            <div class="notif-section">
                <h2>Unread Notifications</h2>
                <?php if (!empty($unread)): ?>
                    <div class="unread-container">
                        <ul class="notif-list">
                            <?php foreach ($unread as $fb): ?>
                                <li class="notif-item">
                                    <strong><?= htmlspecialchars($fb['username']) ?></strong>
                                    <small>(<?= date("M d, Y H:i", strtotime($fb['created_at'])) ?>)</small>
                                    <p class="notif-comment"><?= nl2br(htmlspecialchars($fb['comment'])) ?></p>

                                    <!-- Reply Box -->
                                    <form class="reply-box" method="post" action="reply.php">
                                        <input type="hidden" name="feedback_id" value="<?= $fb['id'] ?>">
                                        <textarea name="reply_message" placeholder="Write your reply..." required></textarea>
                                        <button type="submit">Send Reply</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <p>No unread notifications.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Read Notifications -->
        <div class="searchnotif-section">
            <div class="notif-section">
                <h2>Read Notification</h2>
                <?php if (!empty($read)): ?>
                    <ul class="notif-list">
                        <?php foreach ($read as $fb): ?>
                            <li class="notif-item">
                                <strong class="Ureply"><?= htmlspecialchars($fb['username']) ?></strong>
                                <small>(<?= date("M d, Y H:i", strtotime($fb['created_at'])) ?>)</small>
                                <p class="notif-comment"><?= nl2br(htmlspecialchars($fb['comment'])) ?></p>

                                <?php
                                $sqlReplies = "SELECT r.reply_message, r.created_at, a.username AS admin_name
                                   FROM replies r
                                   JOIN accounts a ON r.admin_id = a.id
                                   WHERE r.feedback_id = ?
                                   ORDER BY r.created_at ASC";
                                $replyStmt = $conn->prepare($sqlReplies);
                                $replyStmt->bind_param("i", $fb['id']);
                                $replyStmt->execute();
                                $replyResult = $replyStmt->get_result();

                                if ($replyResult->num_rows > 0): ?>
                                    <div class="replies">
                                        <strong>Replies:</strong>
                                        <ul>
                                            <?php while ($r = $replyResult->fetch_assoc()): ?>
                                                <li>
                                                    <em><?= htmlspecialchars($r['admin_name']) ?></em>
                                                    (<?= date("M d, Y H:i", strtotime($r['created_at'])) ?>):
                                                    <?= nl2br(htmlspecialchars($r['reply_message'])) ?>
                                                </li>
                                            <?php endwhile; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No read notifications yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <script>
            initDarkMode("adminThemeToggle", "adminDarkMode");
            document.getElementById('hamburger').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('collapsed');
                document.getElementById('mainContent').classList.toggle('expanded');
            });
        </script>
    </div>
</body>

</html>