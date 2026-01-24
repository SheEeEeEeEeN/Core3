<?php
header("Content-Type: application/json");

// --- Dummy shipment trend ---
$data = [
    ["date"=>"2025-09-12","shipments"=>15],
    ["date"=>"2025-09-13","shipments"=>20],
    ["date"=>"2025-09-14","shipments"=>18],
    ["date"=>"2025-09-15","shipments"=>25],
    ["date"=>"2025-09-16","shipments"=>22],
];
echo json_encode($data);
