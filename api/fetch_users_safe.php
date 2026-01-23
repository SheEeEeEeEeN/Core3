<?php
// api/fetch_users_safe.php
// PURPOSE: Fetch ONLY 'user' role accounts without sensitive data.
// for financial

// 1. HEADERS (Para payagan ang ibang system kumonekta)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// 2. INCLUDE CONNECTION
// ==========================================
// PALITAN MO YUNG LUMANG CONNECTION PART NITO:
// ==========================================

// 2. SMART CONNECTION LOCATOR
$found = false;
$paths_to_check = [
    __DIR__ . "/connection.php",           // 1. Check same folder
    dirname(__DIR__) . "/connection.php",  // 2. Check parent folder (../connection.php)
    $_SERVER['DOCUMENT_ROOT'] . "/last/connection.php" // 3. Check absolute path
];

foreach ($paths_to_check as $path) {
    if (file_exists($path)) {
        include($path);
        $found = true;
        break;
    }
}

if (!$found) {
    echo json_encode([
        "status" => "error", 
        "message" => "Connection file not found. Checked paths: " . implode(", ", $paths_to_check)
    ]);
    exit;
}

// ==========================================


// 3. SECURITY CHECK (Optional pero Recommended)
// Lagyan natin ng Secret Key para hindi basta-basta mahigop ng kung sino lang
// Pwede mong tanggalin to kung gusto mong public access.
if (isset($_GET['secret_key'])) {
    if ($_GET['secret_key'] !== "SLATE_SECRET_123") {
        echo json_encode(["status" => "error", "message" => "Unauthorized: Wrong Secret Key"]);
        exit;
    }
} else {
    // Uncomment mo ito kung gusto mong strictly required ang key
    // echo json_encode(["status" => "error", "message" => "Unauthorized: Missing Secret Key"]);
    // exit;
}

// 4. THE QUERY
// Kukunin lang natin ang mga column na SAFE. Walang 'password' o 'otp'.
$sql = "SELECT 
            *
        FROM accounts a
        JOIN payments p ON a.id = p.user_id 
        join feedback f on a.id = f.account_id
        WHERE role = 'user'  -- <--- ITO ANG FILTER (Users lang)
        ORDER BY a.created_at DESC";

$result = $conn->query($sql);

if ($result) {
    $users = [];
    while($row = $result->fetch_assoc()) {
        
        // OPTIONAL: Ayusin ang Profile Image URL kung kailangan
        // Kung ang nasa DB ay 'uploads/pic.jpg', gagawin nating full URL
        if (!empty($row['profile_image'])) {
            // Palitan mo ang 'http://localhost/last/' base sa actual domain mo
            $base_url = "http://localhost/last/api/upload" . $_SERVER['SERVER_NAME'] . "/last/uploads/";
            // Simple check kung full url na o filename lang
            if (!filter_var($row['profile_image'], FILTER_VALIDATE_URL)) {
                 $row['profile_image_url'] = $base_url . $row['profile_image'];
            } else {
                 $row['profile_image_url'] = $row['profile_image'];
            }
        } else {
            $row['profile_image_url'] = null; // O default image url
        }

        $users[] = $row;
    }

    // 5. RETURN JSON
    echo json_encode([
        "status" => "success",
        "count" => count($users),
        "data" => $users
    ], JSON_PRETTY_PRINT);

} else {
    echo json_encode([
        "status" => "error",
        "message" => "SQL Error: " . $conn->error
    ]);
}
?>