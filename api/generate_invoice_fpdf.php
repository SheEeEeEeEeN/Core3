<?php
require('../fpdf/fpdf.php');
class PDF_Invoice extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',20);
        $this->Cell(0,10,'SLATE LOGISTICS',0,1,'C');
        $this->SetFont('Arial','',9);
        $this->Cell(0,5,'Block 1 Lot 2, Logistics Ave., Metro Manila',0,1,'C');
        $this->Cell(0,5,'VAT REG TIN: 000-123-456-789',0,1,'C');
        $this->Ln(5);
        $this->SetDrawColor(0,0,0);
        $this->Line(10, 35, 200, 35); 
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'System Generated Invoice - Page '.$this->PageNo(),0,0,'C');
    }
}

function generateInvoicePDF($data, $shipmentId) {
    // 1. Filename Setup
    $filename = 'INV_' . $shipmentId . '_' . time() . '.pdf';
    $filepath = '../invoices_img/' . $filename; 

    // Check folder
    if (!is_dir('../invoices_img/')) { mkdir('../invoices_img/', 0777, true); }

    // 2. Create PDF
    $pdf = new PDF_Invoice();
    $pdf->AddPage();
    
    // Header Info
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(130, 10, 'OFFICIAL RECEIPT', 0, 0);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(60, 10, 'INV #: ' . $data['invoice_number'], 0, 1, 'R');

    $pdf->SetFont('Arial','',10);
    $pdf->Cell(130, 5, 'Date: ' . date('F d, Y'), 0, 0);
    $pdf->Cell(60, 5, 'Method: ' . strtoupper($data['method']), 0, 1, 'R');
    
    $pdf->Ln(10);

    // Shipper / Consignee
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(95, 8, 'SHIPPER', 1, 0, 'L', true);
    $pdf->Cell(95, 8, 'CONSIGNEE', 1, 1, 'L', true);

    $pdf->SetFont('Arial','',10);
    $pdf->MultiCell(95, 6, strtoupper($data['sender_name']) . "\n" . $data['origin_address'] . "\nCP: " . $data['sender_contact'], 'L R B', 'L');
    
    $y = $pdf->GetY();
    $pdf->SetXY(105, $y - 18); // Adjust position manually for 2nd column based on multicell height
    $pdf->MultiCell(95, 6, strtoupper($data['receiver_name']) . "\n" . $data['destination_address'] . "\nCP: " . $data['receiver_contact'], 'R B', 'L');
    
    $pdf->Ln(10);

    // Table
    $pdf->SetFont('Arial','B',10);
    $pdf->SetFillColor(50, 50, 50);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(100, 10, 'DESCRIPTION', 1, 0, 'L', true);
    $pdf->Cell(30, 10, 'WEIGHT', 1, 0, 'C', true);
    $pdf->Cell(60, 10, 'AMOUNT', 1, 1, 'R', true);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial','',10);
    $pdf->Ln();
    
    $pdf->Cell(100, 10, $data['package_type'] . ' (' . $data['distance_km'] . 'km)', 1, 0);
    $pdf->Cell(30, 10, $data['weight'] . ' kg', 1, 0, 'C');
    $pdf->Cell(60, 10, 'PHP ' . number_format($data['price'], 2), 1, 1, 'R');

    $pdf->Ln(20);

    // Total
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(130, 10, '', 0, 0);
    $pdf->Cell(30, 10, 'TOTAL DUE:', 0, 0, 'R');
    $pdf->SetTextColor(200, 0, 0);
    $pdf->Cell(30, 10, 'PHP ' . number_format($data['price'], 2), 0, 1, 'R');

    // 3. Save
    $pdf->Output('F', $filepath);

    return $filename;
}
?>