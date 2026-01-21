<?php
// receipt_print.php
// PURPOSE: Official Receipt Generator (Standard PH Layout)
// CONNECTED SA: shipments table at payments table

include("connection.php");
session_start();

// 1. SECURITY CHECK
if (!isset($_GET['id'])) { 
    die("<h3>Error: No Shipment ID provided.</h3>"); 
}

$id = intval($_GET['id']);

// 2. DATABASE QUERY (JOIN SHIPMENTS & PAYMENTS)
// Kinukuha natin ang shipment details AT ang payment details (OR Number)
$sql = "SELECT 
            s.*, 
            p.invoice_number, 
            p.payment_date, 
            p.status as payment_status_real,
            p.amount as amount_paid
        FROM shipments s
        LEFT JOIN payments p ON s.id = p.shipment_id
        WHERE s.id = '$id'
        LIMIT 1";

$query = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($query);

if (!$row) { 
    die("<h3>Error: Shipment not found.</h3>"); 
}

// 3. DATA PREPARATION & FORMATTING
$trackingNo = !empty($row['contract_number']) ? $row['contract_number'] : "TRK-" . str_pad($row['id'], 8, "0", STR_PAD_LEFT);

// Kung bayad na, gamitin ang payment date. Kung hindi, gamitin ang created date.
$dateUsed = !empty($row['payment_date']) ? $row['payment_date'] : $row['created_at'];
$date = date('F d, Y', strtotime($dateUsed)); 

$paymentMethod = !empty($row['payment_method']) ? strtoupper($row['payment_method']) : "UNSPECIFIED";

// --- VAT CALCULATION (Standard PH 12%) ---
// Formula: Amount / 1.12 = Vatable Sales
$totalAmount = $row['price']; 
$vatableSales = $totalAmount / 1.12; 
$vatAmount = $totalAmount - $vatableSales;

// --- OR NUMBER LOGIC ---
// Ito ang pinaka-importante. Kukunin niya sa database.
if (!empty($row['invoice_number'])) {
    $orNumber = $row['invoice_number'];
    $orColor = "#c0392b"; // Red pag may OR na
    $statusStamp = "PAID";
    $statusColor = "#d9534f"; // Red Stamp
} else {
    $orNumber = "PENDING (NO OR YET)";
    $orColor = "#7f8c8d"; // Gray pag wala pa
    $statusStamp = "UNPAID";
    $statusColor = "#95a5a6"; // Gray Stamp
}

// Helper para maganda ang formatting ng address
function formatAddr($addr) {
    return ucwords(strtolower($addr));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #<?php echo $orNumber; ?></title>
    <style>
        /* RESET & GENERAL STYLES */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            background: #555; 
            font-family: 'Courier New', Courier, monospace; /* Typewriter Font para sa Data */
            display: flex;
            justify-content: center;
            padding: 30px;
        }

        /* PAPER CONTAINER (Mukhang Bond Paper) */
        .receipt-container {
            width: 850px; /* Wide format */
            background: white;
            padding: 40px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            position: relative;
            border: 1px solid #ccc;
        }

        /* HEADER SECTION */
        .header { text-align: center; border-bottom: 3px double #333; padding-bottom: 20px; margin-bottom: 30px; }
        .company-name { font-family: 'Arial', sans-serif; font-size: 28px; font-weight: bold; color: #2c3e50; }
        .company-info { font-family: 'Arial', sans-serif; font-size: 12px; color: #555; margin-top: 5px; line-height: 1.4; }
        .doc-title { font-family: 'Arial', sans-serif; font-size: 22px; font-weight: bold; margin-top: 20px; text-decoration: underline; letter-spacing: 2px; }

        /* INFO GRID (Customer & Transaction Details) */
        .info-section { display: flex; justify-content: space-between; margin-bottom: 30px; font-family: 'Arial', sans-serif; }
        .info-left { width: 60%; }
        .info-right { width: 35%; text-align: right; }

        .row { margin-bottom: 8px; font-size: 14px; }
        .label { font-weight: bold; color: #555; display: inline-block; width: 110px; }
        .data { border-bottom: 1px solid #ccc; display: inline-block; min-width: 250px; padding: 0 5px; color: #000; font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        
        .or-text { font-size: 18px; font-weight: bold; color: <?php echo $orColor; ?>; }

        /* TABLE STYLES */
        table { width: 100%; border-collapse: collapse; border: 2px solid #000; margin-bottom: 30px; }
        th { background: #eee; border: 1px solid #000; padding: 12px; font-family: 'Arial', sans-serif; font-size: 12px; text-align: left; }
        td { border: 1px solid #000; padding: 15px; font-size: 14px; vertical-align: top; }
        .amount-col { text-align: right; width: 25%; }
        .desc-col { width: 75%; }
        
        .total-row td { background: #f9f9f9; font-weight: bold; font-family: 'Arial', sans-serif; }

        /* FOOTER & BREAKDOWN */
        .footer-section { display: flex; justify-content: space-between; align-items: flex-start; }
        
        .breakdown-box { width: 300px; border: 1px solid #ccc; padding: 15px; font-size: 12px; font-family: 'Arial', sans-serif; }
        .break-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .break-total { border-top: 2px solid #333; margin-top: 5px; padding-top: 5px; font-weight: bold; font-size: 14px; }

        .signature-box { width: 350px; text-align: center; margin-top: 40px; font-family: 'Arial', sans-serif; }
        .sign-line { border-top: 1px solid #000; margin-top: 40px; width: 80%; margin-left: auto; margin-right: auto; }

        /* STAMP STYLE */
        .stamp {
            position: absolute;
            top: 180px;
            right: 50px;
            border: 5px solid <?php echo $statusColor; ?>;
            color: <?php echo $statusColor; ?>;
            font-size: 50px;
            font-weight: bold;
            padding: 10px 30px;
            text-transform: uppercase;
            transform: rotate(-15deg);
            opacity: 0.3; /* Transparent effect */
            font-family: 'Arial Black', sans-serif;
            border-radius: 10px;
            pointer-events: none;
        }

        /* BUTTONS & PRINT SETTINGS */
        .no-print { position: fixed; top: 20px; right: 20px; z-index: 100; }
        .btn { background: #2c3e50; color: white; padding: 10px 20px; border: none; cursor: pointer; font-family: sans-serif; font-weight: bold; box-shadow: 0 4px 5px rgba(0,0,0,0.3); border-radius: 5px; text-decoration: none; display: inline-block;}
        .btn:hover { background: #1a252f; }

        @media print {
            body { background: white; padding: 0; }
            .receipt-container { border: none; box-shadow: none; width: 100%; height: 100%; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn">🖨️ PRINT RECEIPT</button>
    </div>

    <div class="receipt-container">
        
        <div class="stamp"><?php echo $statusStamp; ?></div>

        <div class="header">
            <div class="company-name">SLATE LOGISTICS CORP.</div>
            <div class="company-info">
                Block 1 Lot 2, Logistics Ave., Metro Manila, Philippines<br>
                VAT REG TIN: 000-123-456-789 | Tel: (02) 8-7000-123<br>
                Email: finance@slatelogistics.com | Web: www.slatelogistics.com
            </div>
            <div class="doc-title">OFFICIAL RECEIPT</div>
        </div>

        <div class="info-section">
            <div class="info-left">
                <div class="row">
                    <span class="label">Received From:</span>
                    <span class="data"><?php echo strtoupper($row['sender_name']); ?></span>
                </div>
                <div class="row">
                    <span class="label">Address:</span>
                    <span class="data" style="font-size: 12px;"><?php echo formatAddr($row['origin_address']); ?></span>
                </div>
                <div class="row">
                    <span class="label">TIN / Style:</span>
                    <span class="data">N/A</span>
                </div>
            </div>
            
            <div class="info-right">
                <div class="row">
                    <span class="label">OR Number:</span>
                    <span class="data or-text"><?php echo $orNumber; ?></span>
                </div>
                <div class="row">
                    <span class="label">Date:</span>
                    <span class="data"><?php echo $date; ?></span>
                </div>
                <div class="row">
                    <span class="label">Terms:</span>
                    <span class="data"><?php echo $paymentMethod; ?></span>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="desc-col">PARTICULARS / DESCRIPTION</th>
                    <th class="amount-col">AMOUNT (PHP)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Freight Service Charge</strong><br><br>
                        <small>Tracking No: <?php echo $trackingNo; ?></small><br>
                        <small>Route: <?php echo $row['origin_address']; ?> <br>to <?php echo $row['destination_address']; ?></small><br><br>
                        <small>Details: <?php echo $row['weight']; ?>kg | <?php echo $row['distance_km']; ?>km | <?php echo ucfirst($row['package_type']); ?></small>
                    </td>
                    <td style="text-align: right;">
                        <?php echo number_format($totalAmount, 2); ?>
                    </td>
                </tr>
                <tr style="height: 50px;"><td colspan="2" style="border:none; border-left:1px solid #000; border-right:1px solid #000;"></td></tr>
                
                <tr class="total-row">
                    <td style="text-align: right;">TOTAL AMOUNT DUE</td>
                    <td style="text-align: right;">₱ <?php echo number_format($totalAmount, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="footer-section">
            
            <div class="breakdown-box">
                <div class="break-row">
                    <span>Vatable Sales:</span>
                    <span><?php echo number_format($vatableSales, 2); ?></span>
                </div>
                <div class="break-row">
                    <span>VAT (12%):</span>
                    <span><?php echo number_format($vatAmount, 2); ?></span>
                </div>
                <div class="break-row">
                    <span>VAT Exempt:</span>
                    <span>0.00</span>
                </div>
                <div class="break-row">
                    <span>Zero Rated:</span>
                    <span>0.00</span>
                </div>
                <div class="break-row break-total">
                    <span>TOTAL AMOUNT:</span>
                    <span>₱ <?php echo number_format($totalAmount, 2); ?></span>
                </div>
            </div>

            <div class="signature-box">
                <div style="font-weight: bold; font-size: 14px;">Authorized Representative</div>
                <div class="sign-line"></div>
                <div style="font-size: 12px; color: #555;">Cashier / Finance Department</div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 40px; font-size: 10px; color: #888; font-family: 'Arial', sans-serif;">
            "THIS DOCUMENT IS SYSTEM GENERATED AND VALID FOR CLAIMING EXPENSES."<br>
            Logistics Core System v2.0 - Developed by Slate
        </div>

    </div>

</body>
</html>