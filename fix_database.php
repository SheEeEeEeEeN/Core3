
<?php
// fix_database.php
// PURPOSE: Hanapin ang matching shipment para sa mga payments na walang shipment_id
include("connection.php");

echo "<h2>🔄 Starting Database Fix...</h2>";

// 1. Kunin lahat ng Payments na WALANG Shipment ID (o 0 ang ID)
$sql = "SELECT id, user_id, amount FROM payments WHERE shipment_id = 0 OR shipment_id IS NULL";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($payment = $result->fetch_assoc()) {
        $p_id = $payment['id'];
        $u_id = $payment['user_id'];
        $amount = $payment['amount'];

        // 2. Hanapin sa Shipments table kung may kaparehong User at Presyo
        // Kukunin natin yung shipment na 'Pending' o 'In Transit' na match ang presyo
        $find_shipment = "SELECT id FROM shipments 
                          WHERE user_id = '$u_id' 
                          AND price = '$amount' 
                          LIMIT 1"; // Kumuha ng isa lang
        
        $ship_res = $conn->query($find_shipment);

        if ($ship_res->num_rows > 0) {
            $shipment = $ship_res->fetch_assoc();
            $s_id = $shipment['id'];

            // 3. I-update ang Payment table, ilagay ang nahanap na Shipment ID
            // Mag generate din tayo ng OR Number para maging realistic
            $or_num = "OR-" . date('Y') . "-" . str_pad($p_id, 4, "0", STR_PAD_LEFT);
            
            $update = "UPDATE payments SET shipment_id = '$s_id', invoice_number = '$or_num', status = 'Paid' WHERE id = '$p_id'";
            
            if ($conn->query($update)) {
                echo "✅ Match Found! Payment ID <b>$p_id</b> linked to Shipment ID <b>$s_id</b> (Amount: $amount)<br>";
            }
        } else {
            echo "❌ No match found for Payment ID $p_id (User: $u_id, Amount: $amount)<br>";
        }
    }
} else {
    echo "👍 Walang broken records. All goods na!";
}

echo "<hr><h3>DONE! Try mo na mag-print ng resibo.</h3>";
?>