<?php
include("darkmode.php");
include("connection.php");
include('session.php');
requireRole('user');

if (isset($_POST['book_shipment'])) {
    $sender_name   = $conn->real_escape_string(trim($_POST['sender_name']));
    $receiver_name = $conn->real_escape_string(trim($_POST['receiver_name']));
    $origin        = $conn->real_escape_string(trim($_POST['origin']));
    $destination   = $conn->real_escape_string(trim($_POST['destination']));
    $weight        = $conn->real_escape_string(trim($_POST['weight']));
    $package       = $conn->real_escape_string(trim($_POST['package']));
    $user_id       = $_SESSION['user_id'];

    $sql = "INSERT INTO shipments (user_id, sender_name, receiver_name, origin, destination, weight, package_description, status, created_at) 
            VALUES ('$user_id', '$sender_name', '$receiver_name', '$origin', '$destination', '$weight', '$package', 'Pending', NOW())";

    if ($conn->query($sql) === TRUE) {
        header("Location: shiphistory.php?success=1");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}
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
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }

        .sidebar a .icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
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

        /*Booking Shipment form*/

        .Shipment_form {
            background-color: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            position: relative;
        }

        .dark-mode .Shipment_form {
            background-color: var(--dark-card);
            color: var(--text-light);
        }

        h2 {
            margin-bottom: 0.5rem;
        }

        .ship {
            margin-bottom: 1rem;
        }

        .ship input,
        .ship textarea {
            width: 160vh;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
            background-color: white;
            margin-top: 0.5rem;
        }

        .dark-mode .ship input,
        .dark-mode .ship textarea {
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
            margin-top: 0.5rem;
        }

        .book_shipment {
            background-color: var(--primary-color);
            color: white;
        }

        .book_shipment:hover {
            background-color: #3a5bc7;
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
            <img src="Icons/rem.png" alt="SLATE Logo">
        </div>
        <a href="user.php">
            <img src="Icons/usericon.png" alt="user" class="icon">Dashboard</a>
        <a href="trackship.php">
            <img src="Icons/track.png" alt="track" class="icon">Track Shipment</a>
        <a href="bookship.php" class="active">
            <img src="Icons/booking.png" alt="book" class="icon">Book Shipment</a>
        <a href="shiphistory.php">
            <img src="Icons/history.png" alt="history" class="icon">Shipment History</a>
        <a href="CPN.php">
            <img src="Icons/customer.png" alt="customer" class="icon">Customer Portal & Notification Hub</a>
    </div>

    <div class="content" id="mainContent">
        <div class="header">
            <div class="hamburger" id="hamburger">☰</div>
            <div>
                <h1>Book Shipment <span class="system-title"></span></h1>
            </div>
            <div class="theme-toggle-container">
                <div class="user_icon" id="userIcon">
                    <img src="Icons/user.png" alt="User">
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


        <div class="Shipment_form">
            <h2>Book a shipment</h2>
            <div class="form_content">
                <form method="POST">
                    <div class="ship">
                        <h4>Sender Name</h4>
                        <input type="text" class="form-control" id="sender_name" name="sender_name" placeholder="Enter sender name" required>
                    </div>
                    <div class="ship">
                        <h4>Receiver Name</h4>
                        <input type="text" class="form-control" id="receiver_name" name="receiver_name" placeholder="Enter receiver name" required>
                    </div>
                    <div class="ship">
                        <h4>Origin</h4>
                        <input type="text" class="form-control" id="origin" name="origin" placeholder="Enter origin" required>
                    </div>
                    <div class="ship">
                        <h4>Destination</h4>
                        <input type="text" class="form-control" id="destination" name="destination" placeholder="Enter destination" required>
                    </div>
                    <div class="ship">
                        <h4>Weight (kg)</h4>
                        <input type="text" class="form-control" id="weight" name="weight" placeholder="Enter weight" required>
                    </div>
                    <div class="ship">
                        <h4>Package Description</h4>
                        <textarea class="form-control" id="package" name="package" placeholder="Enter package details" required></textarea>
                    </div>
                    <button type="submit" name="book_shipment" class="btn book_shipment">Book Shipment</button>
                </form>
            </div>
        </div>
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