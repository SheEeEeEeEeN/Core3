<?php
// client_view_contract.php
// PURPOSE: Handles viewing of specific Trip Contracts (Auto-Gen) OR Redirects to Permanent Service Agreement
include("connection.php");
include("session.php"); 

// CHECK IF SPECIFIC REFERENCE IS PROVIDED (Galing sa E-Doc or Booking)
if (isset($_GET['ref']) && !empty($_GET['ref'])) {
    $ref = mysqli_real_escape_string($conn, $_GET['ref']);

    // 1. HANAPIN SA SHIPMENTS TABLE (Trip Contract)
    $sQ = mysqli_query($conn, "SELECT * FROM shipments WHERE contract_number='$ref' LIMIT 1");

    if (mysqli_num_rows($sQ) > 0) {
        $shipment = mysqli_fetch_assoc($sQ);
        // Render TRIP CONTRACT UI directly here
        renderTripContract($shipment);
        exit;
    } else {
        echo "<script>alert('Contract Reference not found.'); window.close();</script>";
        exit;
    }
}

// KUNG WALANG 'REF', HANAPIN ANG PERMANENT CONTRACT (Yung original logic mo)
$email = $_SESSION['email'];
$uQ = mysqli_query($conn, "SELECT id FROM accounts WHERE email='$email'");
$user = mysqli_fetch_assoc($uQ);
$user_id = $user['id'];

$cQ = mysqli_query($conn, "SELECT id FROM contracts WHERE user_id='$user_id' AND status='Active' LIMIT 1");

if(mysqli_num_rows($cQ) > 0) {
    $row = mysqli_fetch_assoc($cQ);
    // REDIRECT SA OFFICIAL PRINT LAYOUT
    header("Location: contract_print.php?id=" . $row['id']);
    exit;
} else {
    echo "<script>
            alert('You do not have an active service contract yet. Please contact Admin.');
            window.location.href = 'bookshipment.php';
          </script>";
    exit;
}

// =================================================================================
//  FUNCTION: RENDER TRIP CONTRACT (HTML LAYOUT)
// =================================================================================
function renderTripContract($data) {
    $date = date('F d, Y', strtotime($data['created_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trip Contract - <?php echo $data['contract_number']; ?></title>
    <style>
        body { font-family: 'Georgia', serif; background: #525659; margin: 0; padding: 20px; }
        .page { background: white; width: 210mm; min-height: 297mm; margin: 0 auto; padding: 20mm; box-shadow: 0 0 10px rgba(0,0,0,0.5); position: relative; }
        h1 { text-align: center; text-transform: uppercase; border-bottom: 2px solid #333; padding-bottom: 10px; font-size: 18pt; margin-bottom: 30px; }
        .header-row { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .section-title { font-weight: bold; background: #eee; padding: 5px; margin-top: 20px; border: 1px solid #ccc; font-family: sans-serif; font-size: 10pt; }
        p, td { font-size: 10pt; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        
        .watermark {
            position: absolute; top: 40%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60pt; color: rgba(0,0,0,0.05); font-weight: bold; border: 5px solid rgba(0,0,0,0.05); padding: 20px;
        }
        @media print {
            body { background: white; padding: 0; }
            .page { box-shadow: none; margin: 0; width: 100%; }
            .no-print { display: none; }
        }
        .btn-print { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #2c3e50; color: white; border: none; cursor: pointer; font-weight: bold; border-radius: 5px; }
    </style>
</head>
<body>
    <button onclick="window.print()" class="btn-print no-print">🖨️ Print Contract</button>

    <div class="page">
        <div class="watermark">TRIP CONTRACT</div>

        <div class="header-row">
            <div>
                <strong>SLATE LOGISTICS INC.</strong><br>
                123 Cargo Ave, Manila<br>
                TIN: 000-123-456-000
            </div>
            <div style="text-align: right;">
                <strong>Contract Ref:</strong> <?php echo $data['contract_number']; ?><br>
                <strong>Date:</strong> <?php echo $date; ?>
            </div>
        </div>

        <h1>Contract of Carriage (Trip Lease)</h1>

        <p>This Contract of Carriage is entered into by <strong>Slate Logistics Inc.</strong> (Carrier) and <strong><?php echo $data['sender_name']; ?></strong> (Shipper) for the transportation of the cargo described below, subject to the Standard Trading Conditions.</p>

        <div class="section-title">1. SHIPMENT DETAILS</div>
        <table>
            <tr>
                <td width="20%"><strong>Tracking No:</strong></td>
                <td>PO-C3-<?php echo str_pad($data['id'], 5, "0", STR_PAD_LEFT); ?></td>
                <td width="20%"><strong>Service Type:</strong></td>
                <td>Standard Freight (<?php echo ucfirst($data['package_type']); ?>)</td>
            </tr>
            <tr>
                <td><strong>Origin:</strong></td>
                <td><?php echo $data['origin_address']; ?></td>
                <td><strong>Destination:</strong></td>
                <td><?php echo $data['destination_address']; ?></td>
            </tr>
            <tr>
                <td><strong>Weight:</strong></td>
                <td><?php echo $data['weight']; ?> kg</td>
                <td><strong>Declared Value:</strong></td>
                <td>N/A</td>
            </tr>
        </table>

        <div class="section-title">2. CHARGES & PAYMENT</div>
        <table>
            <tr>
                <td><strong>Freight Cost:</strong></td>
                <td>PHP <?php echo number_format($data['price'], 2); ?></td>
            </tr>
            <tr>
                <td><strong>Payment Method:</strong></td>
                <td><?php echo strtoupper($data['payment_method']); ?></td>
            </tr>
        </table>

        <div class="section-title">3. TERMS AND CONDITIONS (SUMMARY)</div>
        <div style="font-size: 9pt; text-align: justify; padding: 10px; border: 1px solid #ccc; background: #f9f9f9;">
            <p><strong>3.1 Liability:</strong> The Carrier's liability is limited to PHP 2,000 or the actual value of the goods, whichever is lower, unless a higher value is declared and insurance paid.</p>
            <p><strong>3.2 Prohibited Items:</strong> The Shipper warrants that the package does not contain illegal drugs, explosives, firearms, or live animals.</p>
            <p><strong>3.3 Delivery:</strong> Delivery dates are estimates only. Carrier is not liable for delays due to Force Majeure (Acts of God, Strikes, etc.).</p>
            <p><strong>3.4 Claims:</strong> Claims for damage/loss must be filed within 24 hours of delivery.</p>
        </div>

        <div style="margin-top: 50px; display: flex; justify-content: space-between;">
            <div style="width: 40%; text-align: center;">
                <div style="border-bottom: 1px solid black; margin-bottom: 5px;"></div>
                <strong>Authorized Signature</strong><br>
                Slate Logistics Inc.
            </div>
            <div style="width: 40%; text-align: center;">
                <div style="border-bottom: 1px solid black; margin-bottom: 5px; font-weight:bold; padding-top:10px;">
                    <?php echo strtoupper($data['sender_name']); ?> (Electronically Signed)
                </div>
                <strong>Shipper / Representative</strong>
            </div>
        </div>

        <div style="position: absolute; bottom: 20mm; left: 20mm; right: 20mm; text-align: center; font-size: 8pt; color: #888;">
            This document is system-generated and serves as a binding contract for the specific shipment referenced above.
        </div>
    </div>
</body>
</html>
<?php
}
?>