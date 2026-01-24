<?php
// receipt.php - FINAL REALISTIC VERSION
ini_set('display_errors', 0); // Hide errors para malinis, pero siguraduhing tama ang connection
include("connection.php"); 

if (!isset($_GET['id'])) { die("Error: No Payment ID provided."); }
$payment_id = intval($_GET['id']);

// KUNIN ANG DATA (Connected na tables)
$sql = "SELECT 
            p.invoice_number,
            p.payment_date,
            p.amount,
            p.method,
            p.status as payment_status,
            s.id as shipment_id,
            s.contract_number,       
            s.sender_name,
            s.sender_contact,
            s.receiver_name,
            s.receiver_contact,
            s.origin_address,
            s.destination_address,
            s.weight,
            s.distance_km,
            s.package_type
        FROM payments p
        JOIN shipments s ON p.shipment_id = s.id 
        WHERE p.id = $payment_id";

$result = $conn->query($sql);
$data = $result->fetch_assoc();

if (!$data) { die("Record not found. Run fix_database.php first."); }

// FORMATTING DATA
$or_no = $data['invoice_number'];
$date = date('M d, Y h:i A', strtotime($data['payment_date']));
$track_no = $data['contract_number'] ? $data['contract_number'] : 'TRK-' . $data['shipment_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt - <?php echo $or_no; ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; background: #f0f2f5; padding: 30px; }
        .receipt-container { 
            background: #fff; width: 400px; margin: 0 auto; padding: 0; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;
        }
        .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
        .header h2 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0; font-size: 12px; opacity: 0.8; }
        
        .body { padding: 20px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; color: #555; }
        .row strong { color: #333; }
        
        .divider { border-bottom: 1px dashed #ddd; margin: 15px 0; }
        
        .box-info { background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 13px; margin-bottom: 15px; }
        .box-info div { margin-bottom: 5px; }
        
        .total-area { background: #e8f5e9; padding: 15px; text-align: center; color: #2e7d32; }
        .total-amount { font-size: 28px; font-weight: bold; }
        
        .footer { text-align: center; padding: 15px; font-size: 11px; color: #aaa; background: #fafafa; }
        
        @media print {
            body { background: white; padding: 0; }
            .receipt-container { box-shadow: none; width: 100%; border-radius: 0; }
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="header">
        <h2>LOGISTICS CORE</h2>
        <p>Invoice</p>
    </div>

    <div class="body">
        <div class="row">
            <span>OR Number</span>
            <strong><?php echo $or_no; ?></strong>
        </div>
        <div class="row">
            <span>Date Paid</span>
            <strong><?php echo $date; ?></strong>
        </div>
        <div class="row">
            <span>Payment Ref</span>
            <strong><?php echo "PAY-" . str_pad($payment_id, 6, "0", STR_PAD_LEFT); ?></strong>
        </div>

        <div class="divider"></div>

        <div class="box-info">
            <div style="font-weight:bold; color:#2c3e50; margin-bottom:8px;">SHIPMENT DETAILS</div>
            <div>📦 <b>Tracking:</b> <?php echo $track_no; ?></div>
            <div>👤 <b>Sender:</b> <?php echo $data['sender_name']; ?></div>
            <div>📍 <b>From:</b> <?php echo substr($data['origin_address'], 0, 30); ?>...</div>
            <div>🏁 <b>To:</b> <?php echo substr($data['destination_address'], 0, 30); ?>...</div>
            <div>⚖️ <b>Details:</b> <?php echo $data['weight']; ?>kg (<?php echo $data['distance_km']; ?>km)</div>
        </div>

        <div class="row">
            <span>Package Type</span>
            <strong><?php echo ucfirst($data['package_type']); ?></strong>
        </div>
        <div class="row">
            <span>Payment Method</span>
            <strong><?php echo strtoupper($data['method']); ?></strong>
        </div>
    </div>

    <div class="total-area">
        <div>TOTAL AMOUNT PAID</div>
        <div class="total-amount">₱<?php echo number_format($data['amount'], 2); ?></div>
    </div>

    <div class="footer">
        System Generated Receipt.<br>
        Logistics Core System 2026
    </div>
</div>

<script>
    // Auto print pagbukas (Optional)
    // window.print();
</script>
</body>
</html>