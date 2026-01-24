<?php
// contract_print.php - GENERATOR NG KONTRATA
include("connection.php");

if(!isset($_GET['id'])){ die("No Contract ID provided."); }

$id = $_GET['id'];

// 1. KUNIN ANG CONTRACT DETAILS + ACCOUNT EMAIL
$sql = "SELECT c.*, a.email 
        FROM contracts c 
        LEFT JOIN accounts a ON c.user_id = a.id 
        WHERE c.id = '$id'";
$res = mysqli_query($conn, $sql);
$contract = mysqli_fetch_assoc($res);

if(!$contract){ die("Contract not found."); }

// 2. KUNIN ANG SLA RULES NG KONTRATANG ITO
$sla_sql = "SELECT * FROM sla_policies WHERE contract_id = '$id'";
$sla_res = mysqli_query($conn, $sla_sql);

// Calculate Duration
$date1 = new DateTime($contract['start_date']);
$date2 = new DateTime($contract['end_date']);
$interval = $date1->diff($date2);
$duration = $interval->m + ($interval->y * 12); // Total months
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contract - <?php echo $contract['contract_number']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #525659; font-family: 'Times New Roman', serif; }
        .page {
            background: white;
            width: 21cm;
            min-height: 29.7cm;
            display: block;
            margin: 0 auto;
            margin-bottom: 0.5cm;
            box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);
            padding: 2.5cm;
        }
        .header { text-align: center; margin-bottom: 40px; }
        .header img { width: 80px; }
        .contract-title { text-align: center; font-weight: bold; font-size: 24px; text-decoration: underline; margin-bottom: 30px; }
        .section-title { font-weight: bold; margin-top: 20px; font-size: 16px; text-transform: uppercase; }
        p { text-align: justify; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: center; font-size: 14px; }
        th { background: #f0f0f0; }
        .signature-section { margin-top: 100px; display: flex; justify-content: space-between; }
        .sign-line { width: 200px; border-top: 1px solid black; text-align: center; padding-top: 5px; }

        @media print {
            body, .page { margin: 0; box-shadow: none; background: white; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print text-center p-3">
        <button onclick="window.print()" class="btn btn-primary">Print / Save as PDF</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>

    <div class="page">
        <div class="header">
            <img src="Remorig.png" alt="Company Logo"><br>
            <strong>CORE TRANSACTION 3 LOGISTICS</strong><br>
            <small>123 Shipping Lane, Metro Manila, Philippines</small>
        </div>

        <div class="contract-title">SERVICE LEVEL AGREEMENT (SLA) CONTRACT</div>

        <p>This Agreement is made and entered into this <strong><?php echo date('F d, Y'); ?></strong>, by and between:</p>

        <p>
            <strong>CORE TRANSACTION 3 LOGISTICS</strong>, a logistics service provider with principal office at Metro Manila (hereinafter referred to as the "PROVIDER");
        </p>
        <p style="text-align: center;">- AND -</p>
        <p>
            <strong><?php echo strtoupper($contract['client_name']); ?></strong>, with registered account email <u><?php echo $contract['email']; ?></u> (hereinafter referred to as the "CLIENT").
        </p>

        <div class="section-title">1. EFFECTIVITY AND DURATION</div>
        <p>
            This Contract shall be effective from <strong><?php echo date('F d, Y', strtotime($contract['start_date'])); ?></strong> 
            to <strong><?php echo date('F d, Y', strtotime($contract['end_date'])); ?></strong>.
            This agreement covers a period of approximately <?php echo $duration; ?> months unless terminated earlier by either party.
        </p>

        <div class="section-title">2. SERVICE LEVEL STANDARDS (SLA)</div>
        <p>The PROVIDER agrees to deliver goods within the following committed lead times. Failure to meet these timelines will result in the penalties described in Section 3.</p>
        
        <table>
            <thead>
                <tr>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>Committed Lead Time (Max Days)</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($sla_res) > 0): ?>
                    <?php while($rule = mysqli_fetch_assoc($sla_res)): ?>
                    <tr>
                        <td><?php echo $rule['origin_group']; ?></td>
                        <td><?php echo $rule['destination_group']; ?></td>
                        <td><strong><?php echo $rule['max_days']; ?> Days</strong></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">No specific SLA rules defined. Standard shipping applies.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="section-title">3. BREACH OF SLA & PENALTIES</div>
        <p>
            In the event that the PROVIDER fails to deliver within the agreed Max Days (SLA Breach), the following terms shall apply:
        </p>
        <ul>
            <li><strong>Minor Delay (1-2 Days Late):</strong> The PROVIDER shall issue a refund of <strong>10%</strong> of the shipping fee for the affected tracking number.</li>
            <li><strong>Major Delay (3+ Days Late):</strong> The PROVIDER shall issue a refund of <strong>50%</strong> of the shipping fee.</li>
            <li><strong>Lost/Damaged Items:</strong> Full refund plus insurance coverage value as declared.</li>
        </ul>
        <p>
            All incidents must be reported via the <strong>Incident Report Module</strong> within the System Dashboard.
        </p>

        <div class="section-title">4. CONFIDENTIALITY & TERMINATION</div>
        <p>Both parties agree to keep all transaction data confidential. This contract may be terminated by either party with a 30-day written notice.</p>

        <div class="signature-section">
            <div class="sign-line">
                <strong>Core Logistics Rep.</strong><br>
                Service Provider
            </div>
            <div class="sign-line">
                <strong><?php echo $contract['client_name']; ?></strong><br>
                Client Representative
            </div>
        </div>

        <div style="margin-top: 50px; font-size: 10px; color: gray; text-align: center;">
            System Generated Contract ID: <?php echo $contract['contract_number']; ?> | Date Generated: <?php echo date('Y-m-d H:i:s'); ?>
        </div>
    </div>

</body>
</html>