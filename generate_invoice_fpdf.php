<?php
require('fpdf/fpdf.php');

function generateInvoicePDF($data, $shipmentId) {
    // 1. Setup File Path
    $filename = "Invoice_PO-C3-" . str_pad($shipmentId, 5, "0", STR_PAD_LEFT) . ".pdf";
    $filepath = __DIR__ . '/invoices_img/' . $filename;

    // 2. Initialize PDF
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    // --- Header ---
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetTextColor(44, 62, 80); // Dark Blue
    $pdf->Cell(0, 10, 'SLATE LOGISTICS', 0, 1, 'L');
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 5, 'Block 1 Lot 2, Logistics Avenue, Taguig City', 0, 1, 'L');
    $pdf->Cell(0, 5, 'TIN: 000-123-456-789 | VAT Registered', 0, 1, 'L');
    $pdf->Cell(0, 5, 'finance@slatelogistics.com | (02) 8888-1234', 0, 1, 'L');

    // Invoice Title & Status
    $pdf->SetXY(120, 10);
    $pdf->SetFont('Arial', 'B', 24);
    $pdf->SetTextColor(192, 57, 43); // Red
    $pdf->Cell(80, 10, 'INVOICE', 0, 1, 'R');
    
    $statusColor = ($data['status'] == 'PAID') ? [217, 83, 79] : [149, 165, 166];
    $pdf->SetXY(150, 22);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor($statusColor[0], $statusColor[1], $statusColor[2]);
    $pdf->Cell(50, 8, $data['status'], 1, 1, 'C');

    $pdf->Ln(20);

    // --- Info Grid ---
    $pdf->SetDrawColor(220, 220, 220);
    $pdf->Line(10, 45, 200, 45);
    
    $y = 50;
    // Column 1: Billed To
    $pdf->SetXY(10, $y);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(44, 62, 80);
    $pdf->Cell(90, 8, 'BILLED TO:', 0, 1, 'L');
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(25, 6, 'Name:', 0, 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(65, 6, strtoupper($data['sender_name']), 0, 1);
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(25, 6, 'Address:', 0, 0);
    $pdf->MultiCell(65, 6, $data['origin_address']);
    
    // Column 2: Details
    $pdf->SetXY(110, $y);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(44, 62, 80);
    $pdf->Cell(90, 8, 'DETAILS:', 0, 1, 'L');
    
    $pdf->SetXY(110, $y+8);
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(30, 6, 'Invoice No:', 0, 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(192, 57, 43);
    $pdf->Cell(60, 6, $data['invoice_number'], 0, 1);
    
    $pdf->SetXY(110, $y+14);
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(30, 6, 'Date:', 0, 0);
    $pdf->Cell(60, 6, date("F d, Y"), 0, 1);
    
    $pdf->SetXY(110, $y+20);
    $pdf->Cell(30, 6, 'Payment:', 0, 0);
    $pdf->Cell(60, 6, strtoupper($data['method']), 0, 1);

    $pdf->Ln(20);

    // --- Table ---
    $pdf->SetFillColor(44, 62, 80);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(140, 10, 'DESCRIPTION', 0, 0, 'L', true);
    $pdf->Cell(50, 10, 'AMOUNT', 0, 1, 'R', true);

    // Items
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);
    
    $pdf->Cell(140, 10, 'Logistics Service Fee', 'L R', 0);
    $pdf->Cell(50, 10, number_format($data['price'], 2), 'R', 1, 'R');

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(140, 6, 'Origin: ' . $data['origin_address'], 'L R', 0);
    $pdf->Cell(50, 6, '', 'R', 1);
    
    $pdf->Cell(140, 6, 'Destination: ' . $data['destination_address'], 'L R', 0);
    $pdf->Cell(50, 6, '', 'R', 1);

    $pdf->Cell(140, 6, 'Details: ' . ucfirst($data['package_type']) . ' | ' . $data['weight'] . 'kg | ' . $data['distance_km'] . 'km', 'L R B', 0);
    $pdf->Cell(50, 6, '', 'R B', 1);

    // Total
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(192, 57, 43);
    $pdf->Cell(140, 12, 'TOTAL AMOUNT DUE', 1, 0, 'R');
    $pdf->Cell(50, 12, 'PHP ' . number_format($data['price'], 2), 1, 1, 'R');

    $pdf->Ln(10);

    // --- Breakdown & Footer ---
    $vatable = $data['price'] / 1.12;
    $vat = $data['price'] - $vatable;

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(130, 5, '', 0, 0);
    $pdf->Cell(30, 5, 'Vatable Sales:', 0, 0, 'R');
    $pdf->Cell(30, 5, number_format($vatable, 2), 0, 1, 'R');

    $pdf->Cell(130, 5, '', 0, 0);
    $pdf->Cell(30, 5, 'VAT (12%):', 0, 0, 'R');
    $pdf->Cell(30, 5, number_format($vat, 2), 0, 1, 'R');

    $pdf->Ln(20);
    
    // Signatures
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->Line(10, 240, 90, 240);
    $pdf->Line(110, 240, 190, 240);
    
    $pdf->SetY(242);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(80, 5, 'RECEIVED BY', 0, 0, 'C');
    $pdf->Cell(20, 5, '', 0, 0); // spacer
    $pdf->Cell(80, 5, 'AUTHORIZED SIGNATURE', 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(80, 5, 'Customer / Representative', 0, 0, 'C');
    $pdf->Cell(20, 5, '', 0, 0);
    $pdf->Cell(80, 5, 'Finance Dept.', 0, 1, 'C');

    // 3. Save File
    $pdf->Output('F', $filepath);

    return $filename;
}
?>
