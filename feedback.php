<?php
include("darkmode.php");
include("connection.php");
include('session.php');
requireRole('user');

$account_id = $_SESSION['account_id'];

/*Handle feedback submission*/
if (isset($_POST['Send_feedback'])) {
    $comment = trim($_POST['comment']);
    $account_id = $_SESSION['account_id']; // make sure this is set at login

    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO feedback (account_id, comment, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $account_id, $comment);
        $stmt->execute();
        $stmt->close();
        $success = "Feedback submitted successfully!";
    } else {
        $error = "Please enter feedback.";
    }
}

// Fetch feedback with reply from admin
$feedbacks = [];

$sql = "SELECT f.id, f.comment, f.created_at, a.username 
        FROM feedback f 
        JOIN accounts a ON f.account_id = a.id
        WHERE f.account_id = ? 
        ORDER BY f.created_at DESC LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Fetch replies for this feedback
        $replySql = "SELECT r.reply_message, r.created_at, u.username AS admin_name
                     FROM replies r
                     JOIN accounts u ON r.admin_id = u.id
                     WHERE r.feedback_id = ?
                     ORDER BY r.created_at ASC";
        $stmt2 = $conn->prepare($replySql);
        $stmt2->bind_param("i", $row['id']);
        $stmt2->execute();
        $replies = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();

        $row['replies'] = $replies;
        $feedbacks[] = $row;
    }
}
$stmt->close();


?>


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



        /* Feedback Section */
        .Feedback_section {
            background-color: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 1rem;
        }

        .dark-mode .Feedback_section {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        .Feedback_content textarea {
            width: 160vh;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
            background-color: white;
            margin-top: 0.5rem;
        }

        .dark-mode .Feedback_content textarea {
            background-color: #2a3a5a;
            border-color: #3a4b6e;
            color: var(--text-light);
        }

        .btn {
            width: 200px;
            padding: 0.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 0.8rem;
        }

        .Send_feedback {
            background-color: var(--primary-color);
            color: white;
        }

        .Send_feedback:hover {
            background-color: #3a5bc7;
        }

        /* Admin Replies Box */
        .admin-replies {
            margin-top: 0.5rem;
            padding: 0.6rem;
            background: #f8f9fc;
            border-left: 4px solid var(--primary-color);
            border-radius: 6px;
            color: #333;
        }

        .admin-replies ul {
            list-style: none;
            padding-left: 0;
            margin: 0.3rem 0 0;
        }

        .admin-replies li {
            margin-bottom: 0.4rem;
            padding: 0.4rem;
            background: #fff;
            border-radius: 4px;
        }

        /* Dark Mode Styling */
        body.dark-mode .admin-replies {
            background: #2c2f33;
            border-left: 4px solid #7289da;
            color: #ddd;
        }

        body.dark-mode .admin-replies li {
            background: #23272a;
            color: #ccc;
        }

        .Userfeed {
            font-size: 1rem;
            color: #2d7ff2ff;
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
        <a href="user.php">Dashboard</a>
        <a href="trackship.php">Track Shipment</a>
        <a href="bookship.php">Book Shipment</a>
        <a href="shiphistory.php">Shipment History</a>
        <a href="CPN.php">Customer Portal & Notification Hub</a>
        <a href="feedback.php" class="active">Feedback</a>
    </div>

    <div class="content" id="mainContent">
        <div class="header">
            <div class="hamburger" id="hamburger">☰</div>
            <div>
                <h1>Feedback <span class="system-title"></span></h1>
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


        <div class="Feedback_section">
            <h2>Send Feedback/Concern</h2>
            <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form method="POST">
                <div class="Feedback_content">
                    <textarea class="comment" id="comment" name="comment" placeholder="Enter your feedback" required></textarea>
                </div>
                <button type="submit" name="Send_feedback" class="btn Send_feedback">Send</button>
            </form>
        </div>

        <div class="Feedback_section">
            <h2>Recent Feedback</h2>
            <?php if (!empty($feedbacks)): ?>
                <ul style="list-style:none; padding:0;">
                    <?php foreach ($feedbacks as $fb): ?>
                        <li style="margin-bottom:1rem; padding:0.8rem; border-bottom:1px solid #ddd;">
                            <strong class="Userfeed"><?= htmlspecialchars($fb['username']) ?></strong>
                            <small>(<?= date("M d, Y H:i", strtotime($fb['created_at'])) ?>)</small><br>

                            <p style="margin:0.5rem 0;"><?= nl2br(htmlspecialchars($fb['comment'])) ?></p>

                            <?php if (!empty($fb['replies'])): ?>
                                <div class="admin-replies">
                                    <strong>Admin Replies:</strong>
                                    <ul>
                                        <?php foreach ($fb['replies'] as $r): ?>
                                            <li>
                                                <em><?= htmlspecialchars($r['admin_name']) ?></em>
                                                (<?= date("M d, Y H:i", strtotime($r['created_at'])) ?>):<br>
                                                <?= nl2br(htmlspecialchars($r['reply_message'])) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                        </li>
                    <?php endforeach; ?>

                </ul>
            <?php else: ?>
                <p>No feedback yet.</p>
            <?php endif; ?>
        </div>


        <script>
            initDarkMode("userThemeToggle", "userDarkMode");

            document.getElementById('hamburger').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('collapsed');
                document.getElementById('mainContent').classList.toggle('expanded');
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