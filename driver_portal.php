<?php
// shipment_updater.php - SLATE UI FINAL (FIXED IMAGE LOGO)
include("connection.php");
include('session.php');
requireRole('admin');

// 1. ISAMA ANG MAILER
include('mailer_function.php'); 

$msg = ""; 

// 2. LOGIC: UPDATE STATUS
if (isset($_POST['btn_update'])) {
    $id = $_POST['shipment_id'];
    $new_status = $_POST['status_select'];
    
    // A. Update Shipment Status
    $query = "UPDATE shipments SET status='$new_status' WHERE id='$id'";
    
    if(mysqli_query($conn, $query)) {
        
        $emailMsg = "";
        
        // B. HANAPIN ANG EMAIL
        $shipQ = mysqli_query($conn, "SELECT sender_name FROM shipments WHERE id='$id'");
        $shipRow = mysqli_fetch_assoc($shipQ);
        $senderValue = $shipRow['sender_name']; 
        
        $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'accounts'");
        if(mysqli_num_rows($checkTable) > 0) {
            $userQ = mysqli_query($conn, "SELECT email, username FROM accounts WHERE username = '$senderValue' OR email = '$senderValue'");
            if(mysqli_num_rows($userQ) > 0) {
                $userRow = mysqli_fetch_assoc($userQ);
                $clientEmail = $userRow['email'];
                $clientName = $userRow['username'];
                $trackDisplay = "SHIP-" . str_pad($id, 8, "0", STR_PAD_LEFT);
                
                if (!empty($clientEmail)) {
                    $sent = sendStatusEmail($clientEmail, $clientName, $trackDisplay, $new_status);
                    if($sent) {
                        $emailMsg = "<br><small class='text-success fw-bold'><i class='bi bi-check-circle-fill'></i> Email Sent!</small>";
                    } else {
                        $emailMsg = "<br><small class='text-danger'> (Email Failed)</small>";
                    }
                }
            }
        }
        $msg = "Status updated to <b>$new_status</b>.$emailMsg";
    } else {
        $msg = "Error: " . mysqli_error($conn);
    }
}

// 3. FETCH DATA
$result = mysqli_query($conn, "SELECT * FROM shipments WHERE status != 'Cancelled' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipments - Slate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;1,800&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Roboto', sans-serif; 
            background-color: #f4f6f9; 
            margin: 0;
            overflow-x: hidden;
        }

        /* SIDEBAR STYLING */
        .sidebar {
            width: 260px;
            height: 100vh;
            background-color: #2c3e50;
            color: #ecf0f1;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        /* LOGO AREA - FIXED FOR IMAGE */
        .logo-area {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background-color: #243342; /* Slightly darker for logo contrast */
        }
        
        /* CSS PARA SA IMAGE LOGO */
        .sidebar-logo-img {
            max-width: 180px; /* Limitahan ang lapad para di sumabog */
            width: 100%;      /* Responsive */
            height: auto;     /* Maintain aspect ratio */
            display: block;
            margin: 0 auto 10px auto; /* Center at lagyan ng space sa baba */
            object-fit: contain;
        }

        .logo-sub {
            font-size: 0.65rem;
            color: #bdc3c7;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
            opacity: 0.8;
            font-weight: bold;
        }

        .nav-category {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #7f8c8d;
            padding: 20px 25px 10px 25px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .nav-link {
            color: #bdc3c7;
            padding: 12px 25px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 400;
            transition: 0.2s;
            border-left: 4px solid transparent;
        }

        .nav-link:hover {
            background-color: #34495e;
            color: white;
        }

        .nav-link.active {
            background-color: #34495e;
            color: white;
            border-left: 4px solid #3498db;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 260px;
            padding: 30px;
        }

        .page-header h2 {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        /* TABLE */
        .slate-table-container {
            background: white;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid #eaedf1;
        }

        .table thead th {
            background-color: #f8f9fa;
            color: #576574;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 15px;
            border-bottom: 1px solid #dfe6e9;
        }

        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            color: #555;
            font-size: 0.9rem;
            border-bottom: 1px solid #f1f2f6;
        }

        /* BADGE */
        .badge-cyan {
            background-color: #00cec9;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* ACTION BUTTONS */
        .update-group {
            display: flex;
        }
        .update-group .form-select {
            border-radius: 4px 0 0 4px;
            border: 1px solid #b2bec3;
            font-size: 0.85rem;
            padding: 5px 10px;
            cursor: pointer;
        }
        .update-group .btn {
            border-radius: 0 4px 4px 0;
            background-color: #4834d4; /* Deep Blue */
            border: none;
            font-size: 0.85rem;
            padding: 5px 15px;
            font-weight: 500;
        }
        .update-group .btn:hover {
            background-color: #30336b;
        }

    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo-area">
            <img src="slateLogo.png" alt="Slate Logo" class="sidebar-logo-img">
            <div class="logo-sub">Core Transaction 1</div>
        </div>
        
        <div class="nav-category">Main Menu</div>
        
        <nav>
            <a href="admin.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="#" class="nav-link"><i class="bi bi-cart"></i> Purchase Orders</a>
            <a href="shipment_updater.php" class="nav-link active"><i class="bi bi-box-seam"></i> Shipment Booking</a>
            <a href="#" class="nav-link"><i class="bi bi-layers"></i> Consolidation</a>
            <a href="#" class="nav-link"><i class="bi bi-file-earmark-text"></i> BL Generator</a>
        </nav>
    </div>

    <div class="main-content">
        
        <div class="page-header">
            <h2>Shipments</h2>
        </div>

        <?php if($msg): ?>
            <div class="alert alert-info shadow-sm border-0"><?php echo $msg; ?></div>
        <?php endif; ?>

        <div class="slate-table-container">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 25%;">Tracking</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 25%;">User / Route</th>
                        <th style="width: 30%;">Change Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($r = mysqli_fetch_assoc($result)): 
                        $st = $r['status'];
                        $trackID = "SHIP-" . str_pad($r['id'], 8, "0", STR_PAD_LEFT);
                    ?>
                    <tr>
                        <td class="fw-bold text-muted"><?php echo $r['id']; ?></td>
                        <td class="fw-bold" style="color: #2d3436;"><?php echo $trackID; ?></td>
                        <td><span class="badge-cyan"><?php echo $st; ?></span></td>
                        <td class="text-muted small">
                            <div class="fw-bold text-dark"><?php echo $r['sender_name']; ?></div>
                            <?php echo ($r['origin_island'] ?? '-') . ' <i class="bi bi-arrow-right"></i> ' . ($r['destination_island'] ?? '-'); ?>
                        </td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="shipment_id" value="<?php echo $r['id']; ?>">
                                <div class="update-group">
                                    <select name="status_select" class="form-select">
                                        <option value="Pending" <?php if($st=='Pending') echo 'selected'; ?>>PENDING</option>
                                        <option value="Consolidated" <?php if($st=='Consolidated') echo 'selected'; ?>>CONSOLIDATED</option>
                                        <option value="In Transit" <?php if($st=='In Transit') echo 'selected'; ?>>IN_TRANSIT</option>
                                        <option value="Arrived" <?php if($st=='Arrived') echo 'selected'; ?>>ARRIVED</option>
                                        <option value="Delivered" <?php if($st=='Delivered') echo 'selected'; ?>>DELIVERED</option>
                                        <option value="Cancelled" <?php if($st=='Cancelled') echo 'selected'; ?>>CANCELLED</option>
                                    </select>
                                    <button type="submit" name="btn_update" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>