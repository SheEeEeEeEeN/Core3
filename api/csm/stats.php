<?php
// api/csm/stats.php
require_once __DIR__ . '/../../connection.php';
header('Content-Type: application/json; charset=utf-8');

// small helper
function getCount($conn, $sql) {
    $res = $conn->query($sql);
    if (!$res) return 0;
    $r = $res->fetch_row();
    return (int)$r[0];
}

$totalContracts = getCount($conn, "SELECT COUNT(*) FROM csm");
$totalActive = getCount($conn, "SELECT COUNT(*) FROM csm WHERE status = 'Active'");
$expiringSoon = getCount($conn, "SELECT COUNT(*) FROM csm WHERE end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)");
$totalCompliant = getCount($conn, "SELECT COUNT(*) FROM csm WHERE sla_compliance = 'Compliant'");

echo json_encode([
    'success' => true,
    'totalContracts' => $totalContracts,
    'totalActive' => $totalActive,
    'expiringSoon' => $expiringSoon,
    'totalCompliant' => $totalCompliant,
]);
