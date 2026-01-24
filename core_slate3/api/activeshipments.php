<?php
header("Content-Type: application/json; charset=UTF-8");

$core1_url = "https://core1.slatefreight-ph.com/api/activeshipments.php";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $core1_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false || $http_code >= 400) {
    echo json_encode(["error" => "Failed to fetch data from Core 1", "details" => curl_error($ch)]);
} else {
    echo $response; // forward Core 1 JSON directly
}

curl_close($ch);
