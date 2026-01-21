<?php
include("connection.php");
if(!isset($_GET['ref'])) die("Contract Not Found");
$ref = mysqli_real_escape_string($conn, $_GET['ref']);

// Kunin details
$q = $conn->query("SELECT * FROM shipments WHERE contract_number='$ref'");
$data = $q->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contract - <?php echo $ref; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{padding:40px; background:#eee;} .paper{background:white; padding:50px; max-width:800px; margin:0 auto; box-shadow:0 0 15px rgba(0,0,0,0.1);}</style>
</head>
<body>
    <div class="paper">
        <div class="text-center mb-5">
            <img src="Remorig.png" width="100">
            <h3>LOGISTICS SERVICE AGREEMENT</h3>
            <p class="text-muted">Contract Ref: <?php echo $ref; ?></p>
        </div>
        
        <p>This agreement is made between <strong>Slate Freight Logistics</strong> and:</p>
        <div class="alert alert-light border">
            <strong>Client Name:</strong> <?php echo $data['sender_name']; ?><br>
            <strong>Date:</strong> <?php echo date('F d, Y', strtotime($data['created_at'])); ?>
        </div>

        <p>The service provider agrees to transport the package described below:</p>
        <ul>
            <li><strong>Origin:</strong> <?php echo $data['origin_address']; ?></li>
            <li><strong>Destination:</strong> <?php echo $data['destination_address']; ?></li>
            <li><strong>Item:</strong> <?php echo $data['package_description']; ?> (<?php echo $data['weight']; ?>kg)</li>
            <li><strong>Declared Value:</strong> ₱<?php echo number_format($data['price'], 2); ?></li>
        </ul>

        <br><br>
        <div class="row mt-5">
            <div class="col-6 text-center">
                <div style="border-bottom:1px solid #000; height:50px;"></div>
                <small>Authorized Signature</small>
            </div>
            <div class="col-6 text-center">
                <div style="border-bottom:1px solid #000; height:50px;"></div>
                <small>Client Signature</small>
            </div>
        </div>

        <div class="text-center mt-5 no-print">
            <button onclick="window.print()" class="btn btn-primary btn-lg">🖨️ Print Contract</button>
        </div>
    </div>
</body>
</html>