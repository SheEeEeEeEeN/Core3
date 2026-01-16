<?php
// contract_print.php - FIXED VERSION (No fullname error)
include("connection.php");
include('session.php'); 

if (!isset($_GET['id'])) { die("Contract ID missing."); }

$contract_id = intval($_GET['id']);

// 1. GET CONTRACT & CLIENT DETAILS (Fixed Query)
// Tinanggal natin ang 'a.fullname', 'a.phone', etc. para iwas error.
// Umaasa tayo sa c.* (contracts table) para sa client_name.
$contractQ = mysqli_query($conn, "
    SELECT c.*, a.email, a.username 
    FROM contracts c 
    LEFT JOIN accounts a ON c.user_id = a.id 
    WHERE c.id = '$contract_id'
");

if (!$contractQ) {
    die("Database Error: " . mysqli_error($conn));
}

$contract = mysqli_fetch_assoc($contractQ);

if (!$contract) { die("Contract not found."); }

// 2. GET SLA RULES
$rulesQ = mysqli_query($conn, "SELECT * FROM sla_policies WHERE contract_id = '$contract_id'");
if (mysqli_num_rows($rulesQ) == 0) {
    $rulesQ = mysqli_query($conn, "SELECT * FROM sla_policies WHERE contract_id = 0");
}

// 3. DEFINE DISPLAY VARIABLES (Para hindi mag-error kung walang laman)
// Kung may 'client_name' sa contracts table, yun ang gagamitin. Kung wala, username.
$displayName = !empty($contract['client_name']) ? $contract['client_name'] : $contract['username'];

// Address Logic: Kung may 'address' column sa contracts, yun gagamitin.
$displayAddress = isset($contract['address']) && !empty($contract['address']) ? $contract['address'] : "Address on File";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contract <?php echo $contract['contract_number']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #525659; font-family: 'Times New Roman', serif; }
        .page { background: white; width: 210mm; min-height: 297mm; display: block; margin: 20px auto; padding: 25mm; box-shadow: 0 0 10px rgba(0,0,0,0.3); position: relative; }
        
        @media print {
            body { background: white; margin: 0; }
            .page { margin: 0; box-shadow: none; width: 100%; height: auto; padding: 20mm; }
            .no-print { display: none !important; }
        }

        .header-logo { width: 120px; }
        .title { font-size: 24px; font-weight: bold; text-transform: uppercase; text-align: center; margin-bottom: 5px; color: #1a1a2e; }
        .subtitle { text-align: center; font-size: 14px; margin-bottom: 30px; color: #555; }
        
        .section-title { font-size: 14px; font-weight: bold; text-transform: uppercase; border-bottom: 2px solid #333; margin-top: 25px; margin-bottom: 10px; padding-bottom: 5px; }
        
        p { font-size: 12pt; line-height: 1.5; text-align: justify; margin-bottom: 10px; }
        
        .info-table td { padding: 4px 10px; font-size: 12pt; }
        .fw-bold { font-weight: bold; }

        .sla-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11pt; }
        .sla-table th, .sla-table td { border: 1px solid #333; padding: 8px; text-align: center; }
        .sla-table th { background-color: #f0f0f0; }
        .sla-table td:first-child, .sla-table td:nth-child(2) { text-align: left; }

        .signature-box { margin-top: 50px; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .sig-line { border-top: 1px solid black; width: 200px; margin-top: 40px; text-align: center; padding-top: 5px; font-size: 12pt; }
    </style>
</head>
<body>

    <div class="text-center no-print py-3">
        <button onclick="window.print()" class="btn btn-primary fw-bold">🖨️ Print / Save as PDF</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>

    <div class="page">
        
        <div class="text-center mb-4">
            <img src="Remorig.png" alt="SLATE Logo" class="header-logo mb-2">
            <div class="title">Service Level Agreement</div>
            <div class="subtitle">Contract Reference: <strong><?php echo $contract['contract_number']; ?></strong></div>
        </div>

        <div class="section-title">1. Contracting Parties</div>
        <p>This agreement is made and entered into on <strong><?php echo date('F d, Y', strtotime($contract['start_date'])); ?></strong> by and between:</p>
        
        <table class="info-table w-100 mb-3">
            <tr>
                <td width="15%" class="fw-bold align-top">PROVIDER:</td>
                <td>
                    <strong>SLATE LOGISTICS INC.</strong><br>
                    123 Cargo Avenue, Port Area, Manila<br>
                    support@slatelogistics.com
                </td>
            </tr>
            <tr><td colspan="2">&nbsp;</td></tr> <tr>
                <td class="fw-bold align-top">CLIENT:</td>
                <td>
                    <strong><?php echo strtoupper($displayName); ?></strong><br>
                    <?php echo $displayAddress; ?><br>
                    <?php echo $contract['email']; ?>
                </td>
            </tr>
        </table>

        <div class="section-title">2. Agreement Period</div>
        <p>This contract is valid from <strong><?php echo date('F d, Y', strtotime($contract['start_date'])); ?></strong> to <strong><?php echo date('F d, Y', strtotime($contract['end_date'])); ?></strong>, unless earlier terminated in accordance with the provisions hereof.</p>

        <div class="section-title">3. Service Commitments (Lead Time)</div>
        <p>The Provider commits to deliver shipments within the following maximum lead times based on origin and destination:</p>

        <table class="sla-table">
            <thead>
                <tr>
                    <th width="30%">Origin Group</th>
                    <th width="30%">Destination Group</th>
                    <th width="40%">Committed Lead Time (SLA)</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($rulesQ) > 0): ?>
                    <?php while($rule = mysqli_fetch_assoc($rulesQ)): ?>
                    <tr>
                        <td><?php echo $rule['origin_group']; ?></td>
                        <td><?php echo $rule['destination_group']; ?></td>
                        <td><strong><?php echo $rule['max_days']; ?> Days</strong></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; font-style: italic; padding: 20px;">
                            Standard Shipping Rates and Times apply as per general terms.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="section-title">4. Penalties for Breach</div>
        <p>In the event of delayed deliveries exceeding the Committed Lead Time, the Provider agrees to a penalty refund of <strong>10% of the shipping fee</strong> per delayed shipment, provided the delay is not caused by Force Majeure.</p>

        <div class="section-title">5. Signatures</div>
        <p>IN WITNESS WHEREOF, the parties have executed this Agreement as of the day and year first above written.</p>

        <div class="signature-box">
            <div>
                <div class="sig-line">
                    <strong>SLATE LOGISTICS INC.</strong><br>
                    <small>Service Provider</small>
                </div>
            </div>
            <div>
                <div class="sig-line">
                    <strong><?php echo strtoupper($displayName); ?></strong><br>
                    <small>Client / Representative</small>
                </div>
            </div>
        </div>

        <div style="margin-top: 50px; font-size: 9pt; color: #888; text-align: center; border-top: 1px solid #ccc; padding-top: 10px;">
            System Generated Contract • ID: <?php echo $contract['id']; ?> • Date Printed: <?php echo date('Y-m-d H:i:s'); ?>
        </div>

    </div>

</body>
</html>