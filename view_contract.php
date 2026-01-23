<?php
include("connection.php");

// Validate Request
if(!isset($_GET['ref'])) die("Error: Contract Reference Missing");
$ref = mysqli_real_escape_string($conn, $_GET['ref']);

// Fetch Shipment Details
$q = $conn->query("SELECT * FROM shipments WHERE contract_number='$ref'");
if($q->num_rows == 0) die("Error: Contract Not Found");
$data = $q->fetch_assoc();

// Format Data
$clientName = $data['sender_name'];
$dateCreated = date('F d, Y', strtotime($data['created_at']));
$price = number_format($data['price'], 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract <?php echo $ref; ?> - Slate Freight</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --paper-bg: #ffffff;
            --ink-color: #2c3e50;
            --line-color: #bdc3c7;
        }

        body {
            background-color: #f0f2f5;
            padding: 40px 0;
            font-family: 'Inter', sans-serif;
            color: var(--ink-color);
            -webkit-print-color-adjust: exact;
        }

        /* The Paper Sheet */
        .contract-paper {
            background: var(--paper-bg);
            width: 100%;
            max-width: 850px; /* A4 width approx */
            margin: 0 auto;
            padding: 60px 70px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            border: 1px solid #e1e4e8;
            position: relative;
        }

        /* Typography */
        h1, h2, h3, h4 { font-family: 'Merriweather', serif; }
        
        .header-title {
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
            border-bottom: 2px solid var(--ink-color);
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .contract-meta {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 40px;
        }

        .legal-text {
            font-size: 0.95rem;
            line-height: 1.6;
            text-align: justify;
            margin-bottom: 20px;
        }

        /* Data Tables */
        .details-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        .details-table th, .details-table td {
            padding: 12px 15px;
            border: 1px solid #dee2e6;
        }
        .details-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            width: 30%;
        }

        /* Signature Section */
        .signature-area {
            margin-top: 60px;
            page-break-inside: avoid;
        }
        .sign-box {
            position: relative;
            padding-top: 20px;
        }
        .sign-line {
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
            position: relative;
            height: 40px; /* Space for image */
        }
        
        /* The Realistic Signature Image Styling */
        .digital-signature {
            position: absolute;
            bottom: 5px; /* Sit slightly on the line */
            left: 50%;
            transform: translateX(-50%) rotate(-3deg); /* Slight rotation for realism */
            width: 160px; /* Adjust based on image resolution */
            height: auto;
            mix-blend-mode: multiply; /* Makes white background transparent if not PNG */
            opacity: 0.9;
        }

        /* Terms Box */
        .terms-box {
            background: #fdfdfd;
            border: 1px solid #eee;
            padding: 15px;
            font-size: 0.75rem;
            color: #7f8c8d;
            margin-top: 30px;
            margin-bottom: 40px;
        }

        /* Print Settings */
        @media print {
            body { background: none; padding: 0; }
            .contract-paper { 
                box-shadow: none; 
                border: none; 
                margin: 0; 
                padding: 40px; 
                width: 100%;
                max-width: 100%;
            }
            .no-print { display: none !important; }
            .btn { display: none; }
        }
    </style>
</head>
<body>

    <div class="contract-paper">
        <div class="row align-items-center mb-4">
            <div class="col-6">
                <img src="Remorig.png" alt="Company Logo" style="height: 60px; object-fit:contain;">
            </div>
            <div class="col-6 text-end">
                <h4 class="mb-0 fw-bold">SLATE FREIGHT</h4>
                <small class="text-muted">Logistics & Supply Chain Solutions</small>
            </div>
        </div>

        <div class="text-center">
            <h2 class="header-title">Logistics Service Agreement</h2>
        </div>

        <div class="row contract-meta">
            <div class="col-6">
                <strong>Ref No:</strong> <span class="font-monospace text-dark"><?php echo $ref; ?></span>
            </div>
            <div class="col-6 text-end">
                <strong>Date Issued:</strong> <?php echo $dateCreated; ?>
            </div>
        </div>

        <p class="legal-text">
            This Service Agreement ("Agreement") is entered into on <strong><?php echo $dateCreated; ?></strong>, by and between <strong>Slate Freight Logistics</strong> ("Service Provider") and <strong><?php echo $clientName; ?></strong> ("Client").
        </p>

        <h5 class="mt-4 mb-3 fw-bold" style="font-family: 'Merriweather'">1. Shipment Particulars</h5>
        <table class="details-table">
            <tr>
                <th>Tracking Reference</th>
                <td class="font-monospace fw-bold"><?php echo $ref; ?></td>
            </tr>
            <tr>
                <th>Point of Origin</th>
                <td><?php echo $data['origin_address']; ?></td>
            </tr>
            <tr>
                <th>Destination</th>
                <td><?php echo $data['destination_address']; ?></td>
            </tr>
            <tr>
                <th>Package Content</th>
                <td><?php echo $data['package_description']; ?></td>
            </tr>
            <tr>
                <th>Weight / Dims</th>
                <td><?php echo $data['weight']; ?> kg</td>
            </tr>
            <tr>
                <th>Declared Value</th>
                <td>₱<?php echo $price; ?></td>
            </tr>
        </table>

        <h5 class="mt-4 mb-2 fw-bold" style="font-family: 'Merriweather'">2. Standard Terms of Carriage</h5>
        <div class="terms-box">
            <p class="mb-2"><strong>2.1 Liability:</strong> Slate Freight Logistics shall be liable for loss or damage to the shipment only while in its actual custody and control. Liability is limited to the declared value indicated above.</p>
            <p class="mb-2"><strong>2.2 Delivery Timelines:</strong> While every effort is made to deliver within estimated schedules, the Service Provider is not liable for delays caused by Force Majeure, customs clearance, or incomplete address details provided by the Client.</p>
            <p class="mb-0"><strong>2.3 Prohibited Items:</strong> The Client warrants that the package contains no illegal, hazardous, or prohibited items as defined by local laws.</p>
        </div>

        <div class="row signature-area">
            <div class="col-6 text-center">
                <div class="sign-box mx-4">
                    <div class="sign-line">
                        <img src="admin_signature.png" alt="Signed" class="digital-signature">
                    </div>
                    <p class="mb-0 fw-bold">Slate Freight Authorized Rep.</p>
                    <small class="text-muted">Service Provider</small>

                    
                </div>
            </div>

            <div class="col-6 text-center">
                <div class="sign-box mx-4">
                    <div class="sign-line">
                        </div>
                    <p class="mb-0 fw-bold"><?php echo $clientName; ?></p>
                    <small class="text-muted">Client / Consignor</small>
                </div>
            </div>
        </div>

        <div class="text-center mt-5 pt-4 border-top">
            <small class="text-muted">Slate Freight Logistics Inc. | www.slatefreight.com | Generated via Core Admin</small>
        </div>

        <div class="position-fixed bottom-0 end-0 p-4 no-print">
            <button onclick="window.print()" class="btn btn-dark shadow-lg rounded-pill px-4 py-2">
                <span class="me-2">🖨️</span> Print / Save as PDF
            </button>
        </div>

    </div>
</body>
</html>