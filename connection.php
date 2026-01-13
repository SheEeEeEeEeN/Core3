<?php
// Siguraduhin na tama ang database name mo dito (pinalitan ko ng 'freight_db' as example, ibalik mo sa dati mong db name)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "core_slate1"; // <--- PALITAN MO ITO NG DATABASE NAME MO

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// BAWAL ANG "echo 'Connected';" DITO. DAPAT TAHIMIK LANG ITO.
// HUWAG KANG MAGLAGAY NG CLOSING TAG (?>