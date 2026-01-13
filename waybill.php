<?php
// waybill.php
include("connection.php");
session_start();

// Security Check
if (!isset($_GET['id'])) { die("No Shipment ID provided."); }

$id = intval($_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM shipments WHERE id = '$id'");
$row = mysqli_fetch_assoc($query);

if (!$row) { die("Shipment not found."); }

// Format Data
$trackingNo = "TRK-" . str_pad($row['id'], 8, "0", STR_PAD_LEFT);
$date = date('Y-m-d', strtotime($row['created_at']));
$slaDate = !empty($row['target_delivery_date']) ? date('Y-m-d', strtotime($row['target_delivery_date'])) : 'N/A';
$payment = strtoupper($row['payment_method']);
$codAmount = ($payment === 'COD') ? 'PHP ' . number_format($row['price'], 2) : 'PHP 0.00';
$weight = $row['weight'] . ' kg';

// Origin & Dest Codes (Simple extraction)
function getCode($addr) {
    $parts = explode(',', $addr);
    // Kukunin ang Province o City at gagawing uppercase code (e.g., MANILA -> MNL)
    $city = trim($parts[0] ?? 'UNK');
    return strtoupper(substr($city, 0, 3));
}

$originCode = getCode($row['origin_address']);
$destCode = getCode($row['destination_address']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Waybill <?php echo $trackingNo; ?></title>
    <style>
        /* General Reset */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Arial', sans-serif; }
        
        body { background: #555; display: flex; justify-content: center; align-items: center; min-height: 100vh; }

        /* The Sticker (A6 Size: 105mm x 148mm) */
        .waybill-container {
            width: 105mm;
            height: 148mm;
            background: white;
            padding: 5mm;
            border: 1px solid #000;
            position: relative;
        }

        /* Layout Sections */
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 5px; }
        .logo { font-weight: bold; font-size: 14px; text-transform: uppercase; }
        .date { font-size: 10px; }

        .route-codes { display: flex; justify-content: space-between; font-size: 20px; font-weight: 500; margin-bottom: 10px; text-transform: uppercase; border-bottom: 2px solid #000; padding-bottom: 5px; }
        
        .barcode-section { text-align: center; margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
        .tracking-text { font-size: 12px; letter-spacing: 2px; font-weight: bold; margin-top: 5px; }

        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 11px; margin-bottom: 10px; }
        .box { border: 1px solid #000; padding: 5px; }
        .box-title { font-weight: bold; font-size: 9px; text-transform: uppercase; display: block; margin-bottom: 3px; color: #555; }

        .address-section { margin-bottom: 10px; font-size: 11px; border-bottom: 1px solid #000; padding-bottom: 5px; }
        .addr-role { font-weight: bold; font-size: 9px; text-transform: uppercase; margin-right: 5px; }

        .footer { display: flex; justify-content: space-between; align-items: flex-end; }
        .cod-box { border: 2px solid #000; padding: 5px 10px; font-weight: bold; font-size: 16px; }
        .signature { border-top: 1px solid #000; width: 40%; text-align: center; font-size: 9px; padding-top: 2px; }

        /* Print Settings */
        @media print {
            body { background: white; }
            .no-print { display: none; }
            .waybill-container { border: none; width: 100%; height: 100%; }
        }

        .btn-print {
            position: absolute; top: 20px; right: 20px;
            background: #4e73df; color: white; border: none; padding: 10px 20px;
            cursor: pointer; font-weight: bold; border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-print:hover { background: #2e59d9; }
    </style>
</head>
<body>

    <button onclick="window.print()" class="btn-print no-print">🖨️ Print Waybill</button>

    <div class="waybill-container">
        <div class="header">
            <div class="logo">CORE TRANS 3 LOGISTICS</div>
            <div class="date">Created: <?php echo $date; ?></div>
        </div>

        <div class="route-codes">
            <span><?php echo $originCode; ?></span>
            <span style="font-size: 14px; align-self: center;">➔</span>
            <span><?php echo $destCode; ?></span>
        </div>

        <div class="barcode-section">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?php echo $trackingNo; ?>" alt="QR">
            <div class="tracking-text"><?php echo $trackingNo; ?></div>
        </div>

        <div class="address-section">
            <div><span class="addr-role">FROM:</span> <strong><?php echo strtoupper($row['sender_name']); ?></strong></div>
            <div><?php echo $row['sender_contact']; ?></div>
            <div style="margin-top: 2px; font-size: 10px;"><?php echo $row['origin_address']; ?></div>
        </div>

        <div class="address-section">
            <div><span class="addr-role">TO:</span> <strong><?php echo strtoupper($row['receiver_name']); ?></strong></div>
            <div><?php echo $row['receiver_contact']; ?></div>
            <div style="margin-top: 2px; font-size: 10px;"><?php echo $row['destination_address']; ?></div>
            <div style="margin-top: 3px; font-style: italic;">Note: <?php echo $row['specific_address']; ?></div>
        </div>

        <div class="details-grid">
            <div class="box">
                <span class="box-title">Weight</span>
                <strong><?php echo $weight; ?></strong>
            </div>
            <div class="box">
                <span class="box-title">Type</span>
                <?php echo ucfirst($row['package_type']); ?>
            </div>
            <div class="box">
                <span class="box-title">SLA Deadline</span>
                <?php echo $slaDate; ?>
            </div>
            <div class="box">
                <span class="box-title">Payment</span>
                <?php echo $payment; ?>
            </div>
        </div>

        <div class="footer" style="margin-top: 20px;">
            <div class="signature">
                <br>Consignee Signature
            </div>
            <div class="cod-box">
                <?php echo $codAmount; ?>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 10px; font-size: 9px;">
            Thank you for shipping with us!
        </div>
    </div>

</body>
</html>