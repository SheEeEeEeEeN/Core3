<?php
// contract_print.php - Enhanced Version for Realistic Contract UI
include("connection.php");
include('session.php'); 

if (!isset($_GET['id'])) { die("Contract ID missing."); }

$contract_id = intval($_GET['id']);

// 1. GET CONTRACT & CLIENT DETAILS
$contractQ = mysqli_query($conn, "
    SELECT c.*, a.email, a.username 
    FROM contracts c 
    LEFT JOIN accounts a ON c.user_id = a.id 
    WHERE c.id = '$contract_id'
");

if (!$contractQ || mysqli_num_rows($contractQ) == 0) {
    die("Contract not found or database error.");
}

$contract = mysqli_fetch_assoc($contractQ);

// 2. GET SLA RULES
$rulesQ = mysqli_query($conn, "SELECT * FROM sla_policies WHERE contract_id = '$contract_id'");
if (mysqli_num_rows($rulesQ) == 0) {
    $rulesQ = mysqli_query($conn, "SELECT * FROM sla_policies WHERE contract_id = 0");
}

// 3. DEFINE DISPLAY VARIABLES
$clientName = !empty($contract['client_name']) ? $contract['client_name'] : $contract['username'];
$clientAddress = isset($contract['address']) && !empty($contract['address']) ? $contract['address'] : "Address on File";
$contractDate = date('F d, Y', strtotime($contract['created_at'] ?? $contract['start_date']));
$startDate = date('F d, Y', strtotime($contract['start_date']));
$endDate = date('F d, Y', strtotime($contract['end_date']));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Agreement - <?php echo $contract['contract_number']; ?></title>
    <!-- Google Fonts for Professional Look -->
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;1,300&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --accent-color: #34495e;
            --text-color: #222;
        }
        body {
            background: #525659;
            font-family: 'Merriweather', 'Times New Roman', serif;
            color: var(--text-color);
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
        }
        .page-container {
            background: white;
            width: 210mm; /* A4 width */
            min-height: 297mm; /* A4 height */
            display: block;
            margin: 30px auto;
            padding: 25mm; /* Standard margins */
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            position: relative;
            box-sizing: border-box;
        }
        
        /* Print Optimization */
        @media print {
            body { background: white; margin: 0; }
            .page-container { 
                margin: 0; 
                box-shadow: none; 
                width: 100%; 
                height: auto; 
                padding: 20mm; 
                page-break-after: always;
            }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }

        /* Typography */
        h1, h2, h3, h4 { margin: 0; color: var(--primary-color); text-transform: uppercase; }
        h1 { font-size: 20pt; text-align: center; margin-bottom: 20px; font-weight: 700; letter-spacing: 1px; border-bottom: 3px double #333; padding-bottom: 15px; }
        h2 { font-size: 14pt; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 25px; margin-bottom: 15px; }
        p, li, td { font-size: 11pt; line-height: 1.6; text-align: justify; }
        
        /* Header */
        .header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 40px; border-bottom: 1px solid #ddd; padding-bottom: 20px; }
        .logo { width: 140px; height: auto; }
        .company-info { text-align: right; font-size: 9pt; color: #555; }
        .company-info strong { font-size: 11pt; color: #000; }

        /* Sections */
        .contract-meta { background: #f9f9f9; padding: 15px; border: 1px solid #eee; margin-bottom: 20px; display: flex; justify-content: space-between; }
        .meta-group { display: flex; flex-direction: column; }
        .meta-label { font-size: 8pt; text-transform: uppercase; color: #666; font-weight: bold; }
        .meta-value { font-size: 11pt; font-weight: bold; color: #000; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; font-size: 10.5pt; }
        table th, table td { border: 1px solid #ccc; padding: 8px 12px; }
        table th { background-color: #f4f4f4; text-align: left; font-weight: bold; text-transform: uppercase; font-size: 9pt; }
        
        /* Signature Area */
        .signature-section { margin-top: 60px; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .signature-block { width: 45%; }
        .signature-line { border-top: 1px solid #000; margin-top: 50px; padding-top: 10px; text-align: center; font-weight: bold; }
        .signature-role { text-align: center; font-size: 10pt; color: #555; font-style: italic; }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            color: rgba(0,0,0,0.03);
            font-weight: bold;
            text-transform: uppercase;
            z-index: 0;
            pointer-events: none;
        }

        /* Buttons */
        .action-bar { position: fixed; top: 20px; right: 20px; z-index: 1000; background: white; padding: 10px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; text-transform: uppercase; font-size: 9pt; margin-left: 5px; }
        .btn-print { background: #2c3e50; color: white; }
        .btn-close { background: #e74c3c; color: white; }
        .btn:hover { opacity: 0.9; }

        /* Paragraph Numbering */
        .clause { margin-bottom: 15px; }
        .clause-title { font-weight: bold; display: block; margin-bottom: 5px; }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn btn-print">🖨️ Print Agreement</button>
        <button onclick="window.close()" class="btn btn-close">Close</button>
    </div>

    <div class="page-container">
        
        <div class="watermark">OFFICIAL COPY</div>

        <!-- Header -->
        <div class="header">
            <img src="Remorig.png" alt="Logo" class="logo">
            <div class="company-info">
                <strong>SLATE LOGISTICS INC.</strong><br>
                123 Cargo Avenue, Port Area, Manila<br>
                Philippines, 1018<br>
                Tel: +63 2 8123 4567 | Email: legal@slatelogistics.com<br>
                Tax ID: 000-123-456-789
            </div>
        </div>

        <h1>Logistics Service Agreement</h1>
        
        <div class="contract-meta">
            <div class="meta-group">
                <span class="meta-label">Contract Reference No.</span>
                <span class="meta-value"><?php echo $contract['contract_number']; ?></span>
            </div>
            <div class="meta-group">
                <span class="meta-label">Date of Execution</span>
                <span class="meta-value"><?php echo $contractDate; ?></span>
            </div>
            <div class="meta-group">
                <span class="meta-label">Status</span>
                <span class="meta-value"><?php echo ucfirst($contract['status']); ?></span>
            </div>
        </div>

        <p>This <strong>SERVICE AGREEMENT</strong> (the "Agreement") is made and entered into on <strong><?php echo $contractDate; ?></strong> (the "Effective Date"), by and between:</p>

        <div style="margin-left: 20px; border-left: 3px solid #ddd; padding-left: 15px; margin-bottom: 20px;">
            <p><strong>SLATE LOGISTICS INC.</strong>, a corporation duly organized and existing under the laws of the Republic of the Philippines, with principal office address at 123 Cargo Avenue, Port Area, Manila, represented herein by its Authorized Representative (hereinafter referred to as the "<strong>PROVIDER</strong>");</p>
            <p style="text-align: center; font-style: italic; font-weight: bold;">- AND -</p>
            <p><strong><?php echo strtoupper($clientName); ?></strong>, with principal address at <?php echo $clientAddress; ?> (hereinafter referred to as the "<strong>CLIENT</strong>").</p>
        </div>

        <p>The PROVIDER and the CLIENT may be referred to individually as a "Party" and collectively as the "Parties".</p>

        <p><strong>WHEREAS</strong>, the PROVIDER is engaged in the business of logistics, freight forwarding, and supply chain solutions;</p>
        <p><strong>WHEREAS</strong>, the CLIENT desires to engage the services of the PROVIDER for the transportation and management of its goods;</p>
        <p><strong>NOW, THEREFORE</strong>, for and in consideration of the mutual covenants and agreements set forth herein, the Parties agree as follows:</p>

        <h2>1. Scope of Services</h2>
        <p>The PROVIDER agrees to provide logistics and freight services to the CLIENT as detailed in the attached Service Level Agreement (SLA) and Pricing Schedule. Services may include, but are not limited to, pickup, transportation, warehousing, and delivery of goods to designated locations.</p>

        <h2>2. Term of Agreement</h2>
        <p>This Agreement shall commence on <strong><?php echo $startDate; ?></strong> and shall remain in full force and effect until <strong><?php echo $endDate; ?></strong> (the "Term"), unless earlier terminated as provided herein. Renewal of this Agreement shall be subject to mutual written consent of both Parties.</p>

        <h2>3. Service Level Agreement (SLA)</h2>
        <p>The PROVIDER commits to adhere to the following delivery lead times (Lead Time) based on the origin and destination of shipments:</p>
        
        <table>
            <thead>
                <tr>
                    <th width="30%">Origin</th>
                    <th width="30%">Destination</th>
                    <th width="40%">Committed Lead Time (SLA)</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($rulesQ) > 0): ?>
                    <?php while($rule = mysqli_fetch_assoc($rulesQ)): ?>
                    <tr>
                        <td style="text-align:center;"><?php echo $rule['origin_group']; ?></td>
                        <td style="text-align:center;"><?php echo $rule['destination_group']; ?></td>
                        <td style="text-align:center; font-weight:bold;"><?php echo $rule['max_days']; ?> Days</td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; font-style: italic;">Standard Slate Logistics shipment terms apply.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <p><em>Note: Lead times are estimated in working days and exclude Sundays and Public Holidays.</em></p>

        <h2>4. Compensation and Billing</h2>
        <p>4.1. The CLIENT agrees to pay the PROVIDER the rates specified in the agreed Pricing Schedule or as quoted per shipment.</p>
        <p>4.2. In the event of a delay in delivery exceeding the SLA caused solely by the PROVIDER (excluding Force Majeure), the PROVIDER agrees to a penalty credit of <strong>10% of the freight cost</strong> for the affected shipment.</p>
        <p>4.3. Payment Service Provider terms generally require settlement within thirty (30) days from invoice date.</p>

        <div class="page-break"></div> <!-- Force new page for standard terms if needed, mainly for printing -->

        <h2>5. Responsibilities and Liability</h2>
        <p>5.1. <strong>Client's Responsibility:</strong> The CLIENT warrants that all goods tendered for shipment are properly packed, labeled, and do not contain illegal or prohibited items.</p>
        <p>5.2. <strong>Provider's Liability:</strong> The PROVIDER'S liability for loss or damage to goods shall be limited to the declared value of the goods or the standard carrier liability limit, whichever is lower, unless additional insurance is purchased by the CLIENT.</p>
        <p>5.3. <strong>Force Majeure:</strong> Neither Party shall be liable for any failure to perform its obligations where such failure is a result of Acts of God, war, strikes, or other causes beyond reasonable control.</p>

        <h2>6. Confidentiality</h2>
        <p>Both Parties agree to keep confidential all proprietary information, trade secrets, and pricing details exchanged during the term of this Agreement and for a period of two (2) years thereafter.</p>

        <h2>7. Governing Law</h2>
        <p>This Agreement shall be governed by and construed in accordance with the laws of the Republic of the Philippines. Any disputes arising from this Agreement shall be settled amicably; failing which, the courts of Manila shall have exclusive jurisdiction.</p>
                    
        <div class="signature-section">
            <div class="signature-block">
                <p><strong>SIGNED for and on behalf of:<br>SLATE LOGISTICS INC.</strong></p>
                <div class="signature-line">SLATE CORE3</div>
                <div class="signature-role">Operations Director</div>
            </div>
            <div class="signature-block">
                <p><strong>SIGNED for and on behalf of:<br><?php echo strtoupper($clientName); ?></strong></p>
                <div class="signature-line">Authorized Signatory</div>
                <div class="signature-role">Name & Designation</div>
            </div>
        </div>

        <div style="margin-top: 50px; border-top: 1px solid #ccc; padding-top: 10px; font-size: 8pt; color: #999; text-align: center;">
            <p style="margin:0;">Slate Logistics Inc. | Logistics Service Agreement | Page 1 of 1</p>
            <p style="margin:0;">System Ref: <?php echo md5($contract_id . 'salt'); ?></p>
        </div>

    </div>

</body>
</html>