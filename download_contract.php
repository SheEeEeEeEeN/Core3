<?php
include 'connection.php';
include 'session.php';
requireRole('admin');
require 'fpdf/fpdf.php';

if (!isset($_GET['contract_id']) || empty($_GET['contract_id'])) {
    echo "Error: Contract ID not specified.";
    exit;
}

$contract_id = $_GET['contract_id'];

// Fetch contract details
$stmt = $conn->prepare("SELECT * FROM csm WHERE contract_id = ?");
$stmt->bind_param('s', $contract_id);
$stmt->execute();
$result = $stmt->get_result();
$contract = $result->fetch_assoc();
$stmt->close();

if (!$contract) {
    echo "Error: Contract not found.";
    exit;
}

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, "Contract Details", 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 12);

$pdf->Cell(50, 10, "Contract ID:", 0, 0);
$pdf->Cell(0, 10, $contract['contract_id'], 0, 1);

$pdf->Cell(50, 10, "Client Name:", 0, 0);
$pdf->Cell(0, 10, $contract['client_name'], 0, 1);

$pdf->Cell(50, 10, "Start Date:", 0, 0);
$pdf->Cell(0, 10, $contract['start_date'], 0, 1);

$pdf->Cell(50, 10, "End Date:", 0, 0);
$pdf->Cell(0, 10, $contract['end_date'], 0, 1);

$pdf->Cell(50, 10, "Status:", 0, 0);
$pdf->Cell(0, 10, $contract['status'], 0, 1);

$pdf->Cell(50, 10, "SLA Compliance:", 0, 0);
$pdf->Cell(0, 10, $contract['sla_compliance'], 0, 1);

// Output PDF for download
$pdf->Output('D', "Contract_{$contract['contract_id']}.pdf");
exit;
