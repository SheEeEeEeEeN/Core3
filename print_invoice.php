<?php
// FILE: print_invoice.php
// PURPOSE: Invoice Printout (Formerly Waybill)

// 👇 ANTI-CACHE HEADERS (Para sure na hindi mag-save ang browser)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include("connection.php");
session_start();

if (!isset($_GET['id'])) { die("<h3>Error: No Shipment ID provided.</h3>"); }
$id = intval($_GET['id']);

$sql = "SELECT s.*, p.invoice_number, p.payment_date, p.status as payment_status_real, p.amount as amount_paid
        FROM shipments s
        LEFT JOIN payments p ON s.id = p.shipment_id
        WHERE s.id = '$id' LIMIT 1";

$query = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($query);

if (!$row) { die("<h3>Error: Shipment not found.</h3>"); }

$trackingNo = !empty($row['contract_number']) ? $row['contract_number'] : "TRK-" . str_pad($row['id'], 8, "0", STR_PAD_LEFT);
$dateUsed = !empty($row['payment_date']) ? $row['payment_date'] : $row['created_at'];
$date = date('F d, Y', strtotime($dateUsed)); 
$paymentMethod = !empty($row['payment_method']) ? strtoupper($row['payment_method']) : "UNSPECIFIED";
$totalAmount = $row['price']; 
$vatableSales = $totalAmount / 1.12; 
$vatAmount = $totalAmount - $vatableSales;

if (!empty($row['invoice_number'])) {
    $orNumber = $row['invoice_number'];
    $orColor = "#c0392b"; 
    $statusStamp = "PAID";
    $statusColor = "#d9534f"; 
} else {
    $orNumber = "PENDING";
    $orColor = "#7f8c8d"; 
    $statusStamp = "UNPAID";
    $statusColor = "#95a5a6"; 
}

function formatAddr($addr) { return ucwords(strtolower($addr)); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $orNumber; ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #e0e0e0; font-family: 'Roboto', sans-serif; display: flex; justify-content: center; padding: 40px; -webkit-print-color-adjust: exact; }
        
        .invoice-box {
            width: 850px;
            background: #fff;
            padding: 40px 50px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            border: 1px solid #dcdcdc;
            position: relative;
        }
        
        /* Header Section */
        .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; border-bottom: 2px solid #2c3e50; padding-bottom: 20px; }
        .company-details h1 { font-size: 28px; color: #2c3e50; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px; }
        .company-details p { font-size: 12px; color: #555; line-height: 1.5; }
        .invoice-title { text-align: right; }
        .invoice-title h2 { font-size: 32px; color: #c0392b; margin-bottom: 5px; }
        .invoice-title .status { font-size: 14px; font-weight: bold; color: <?php echo $statusColor; ?>; border: 2px solid <?php echo $statusColor; ?>; padding: 5px 10px; display: inline-block; border-radius: 4px; text-transform: uppercase; letter-spacing: 1px; }

        /* Info Grid */
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 30px; font-size: 13px; }
        .info-col { width: 48%; }
        .info-row { display: flex; margin-bottom: 8px; border-bottom: 1px dotted #ccc; padding-bottom: 2px; }
        .label { font-weight: bold; width: 100px; color: #555; }
        .value { flex: 1; color: #000; font-weight: 500; }
        .highlight { color: #c0392b; font-weight: 700; font-size: 14px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #2c3e50; color: white; padding: 12px 15px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { border: 1px solid #eee; padding: 15px; font-size: 13px; vertical-align: top; color: #333; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .amount-col { text-align: right; width: 25%; font-family: 'Courier New', monospace; font-weight: bold; }
        
        .total-row td { background: #fdf2f0 !important; font-weight: bold; font-size: 16px; border-top: 2px solid #2c3e50; color: #c0392b; }

        /* Footer & Signatures */
        .footer-grid { display: flex; justify-content: space-between; margin-top: 40px; }
        .breakdown { width: 35%; background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px solid #eee; }
        .breakdown-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px; color: #555; }
        .breakdown .final-total { border-top: 1px solid #ccc; margin-top: 8px; padding-top: 8px; font-weight: bold; font-size: 14px; color: #000; }

        .signatures { width: 60%; display: flex; justify-content: space-between; align-items: flex-end; }
        .sign-box { width: 45%; text-align: center; }
        .sign-line { border-bottom: 1px solid #000; height: 30px; margin-bottom: 5px; position: relative; }
        .sign-line::after { content: "✍️"; position: absolute; top: -15px; left: 50%; transform: translateX(-50%); opacity: 0.1; font-size: 40px; }
        .sign-label { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #555; }
        .sign-sub { font-size: 10px; color: #888; }

        /* Watermark & Print */
        .watermark { position: absolute; top: 40%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 100px; font-weight: 900; color: <?php echo $statusColor; ?>; opacity: 0.05; pointer-events: none; border: 10px solid <?php echo $statusColor; ?>; padding: 20px 50px; border-radius: 20px; z-index: 0; }
        .no-print { position: fixed; top: 20px; right: 20px; z-index: 100; }
        .btn-print { background: #2c3e50; color: white; padding: 12px 25px; border: none; cursor: pointer; border-radius: 50px; font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.2); transition: all 0.2s; display: flex; align-items: center; gap: 8px; }
        .btn-print:hover { background: #34495e; transform: translateY(-2px); }

        @media print {
            body { background: none; padding: 0; }
            .invoice-box { box-shadow: none; border: none; width: 100%; height: 100vh; padding: 20px; }
            .no-print { display: none; }
            .watermark { opacity: 0.03; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg> 
            PRINT INVOICE
        </button>
    </div>

    <div class="invoice-box">
        <div class="watermark"><?php echo $statusStamp; ?></div>
        
        <header class="invoice-header">
            <div class="company-details">
                <h1>Slate Logistics</h1>
                <p>
                    Block 1 Lot 2, Logistics Avenue, Taguig City, Philippines<br>
                    TIN: 000-123-456-789 | VAT Registered<br>
                    finance@slatelogistics.com | (02) 8888-1234
                </p>
            </div>
            <div class="invoice-title">
                <h2>INVOICE</h2>
                <div class="status"><?php echo $statusStamp; ?></div>
            </div>
        </header>

        <section class="info-grid">
            <div class="info-col">
                <div style="margin-bottom: 10px; border-bottom: 2px solid #eee; padding-bottom: 5px; font-weight: bold;">BILLED TO:</div>
                <div class="info-row"><span class="label">Name:</span><span class="value"><?php echo strtoupper($row['sender_name']); ?></span></div>
                <div class="info-row"><span class="label">Address:</span><span class="value"><?php echo formatAddr($row['origin_address']); ?></span></div>
                <div class="info-row"><span class="label">Pay Method:</span><span class="value"><?php echo $paymentMethod; ?></span></div>
            </div>
            <div class="info-col">
                <div style="margin-bottom: 10px; border-bottom: 2px solid #eee; padding-bottom: 5px; font-weight: bold;">INVOICE DETAILS:</div>
                <div class="info-row"><span class="label">Invoice No:</span><span class="value highlight"><?php echo $orNumber; ?></span></div>
                <div class="info-row"><span class="label">Date:</span><span class="value"><?php echo $date; ?></span></div>
                <div class="info-row"><span class="label">Tracking #:</span><span class="value" style="font-family:'Courier New';"><?php echo $trackingNo; ?></span></div>
            </div>
        </section>

        <table>
            <thead>
                <tr>
                    <th style="width: 70%;">DESCRIPTION</th>
                    <th style="text-align:right;">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Logistics Service Fee</strong><br>
                        <span style="color:#555; font-size:12px;">Route: <?php echo formatAddr($row['origin_address']); ?> &rarr; <?php echo formatAddr($row['destination_address']); ?></span><br>
                        <span style="color:#777; font-size:11px;">Details: <?php echo ucfirst($row['package_type']); ?> | <?php echo $row['weight']; ?>kg | <?php echo $row['distance_km']; ?>km distance</span>
                    </td>
                    <td class="amount-col">₱ <?php echo number_format($totalAmount, 2); ?></td>
                </tr>
                <!-- Spacer -->
                <tr style="height: 100px;"><td colspan="2" style="border:none;"></td></tr>
                
                <tr class="total-row">
                    <td style="text-align:right; text-transform:uppercase;">Total Amount Due</td>
                    <td class="amount-col">₱ <?php echo number_format($totalAmount, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="footer-grid">
            <div class="breakdown">
                <div class="breakdown-row"><span>Vatable Sales:</span><span><?php echo number_format($vatableSales, 2); ?></span></div>
                <div class="breakdown-row"><span>VAT (12%):</span><span><?php echo number_format($vatAmount, 2); ?></span></div>
                <div class="breakdown-row"><span>VAT Exempt:</span><span>0.00</span></div>
                <div class="breakdown-row final-total"><span>Total:</span><span>₱ <?php echo number_format($totalAmount, 2); ?></span></div>
            </div>

            <div class="signatures">
                <div class="sign-box">
                    <div class="sign-line"></div>
                    <div class="sign-label">Received By</div>
                    <div class="sign-sub">Customer / Representative</div>
                </div>
                <div class="sign-box">
                    <div class="sign-line"></div>
                    <div class="sign-label">Authorized Signature</div>
                    <div class="sign-sub">Finance Dept.</div>
                </div>
            </div>
        </div>

        <div style="margin-top: 50px; text-align: center; color: #999; font-size: 10px;">
            This document is system-generated by Core Transaction 3 System. Valid for claiming official receipts.
            <br>&copy; <?php echo date('Y'); ?> Slate Logistics Corp. All rights reserved.
        </div>
    </div>
</body>
</html>