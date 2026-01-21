<!DOCTYPE html>
<html>
<head>
    <title>Payment Gateway Simulator</title>
    <style>
        body { font-family: sans-serif; padding: 50px; background: #f4f6f9; }
        form { background: white; padding: 30px; width: 300px; margin: 0 auto; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input, select, button { width: 100%; padding: 10px; margin-bottom: 10px; box-sizing: border-box; }
        button { background: #28a745; color: white; border: none; cursor: pointer; font-weight: bold; }
        button:hover { background: #218838; }
    </style>
</head>
<body>

    <h2 style="text-align:center;">💳 Pay Now (GCash Simulator)</h2>

    <form action="api/pay_shipment.php" method="POST">
        
        <label>Shipment ID:</label>
        <input type="number" name="shipment_id" placeholder="Enter Shipment ID (e.g. 143)" required>

        <label>Amount to Pay:</label>
        <input type="text" name="amount" placeholder="e.g. 500.00" required>

        <label>Payment Method:</label>
        <select name="method">
            <option value="GCash">GCash</option>
            <option value="Maya">Maya</option>
            <option value="Credit Card">Credit Card</option>
        </select>

        <button type="submit">CONFIRM PAYMENT</button>
    </form>

</body>
</html>