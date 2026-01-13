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

<!-- <?php
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'core3_slate');
// define('DB_USER', 'core3_core3slateph');
// define('DB_PASS', 'corerakot3'); 

// $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// if ($conn->connect_error) {
//     error_log("Database Connection Failed: " . $conn->connect_error);
//     die("Sorry, we're having some technical difficulties. Please try again later.");
// }

// // ✅ Set timezone for PHP
// date_default_timezone_set('Asia/Manila');

// // ✅ Set timezone for MySQL connection
// $conn->query("SET time_zone = '+08:00'");
?> -->
