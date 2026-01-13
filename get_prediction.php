<?php
// filename: get_prediction.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');
$currentDate = date("l, F j, Y"); // e.g., Friday, December 19, 2025
$currentTime = date("g:i A");     // e.g., 5:30 PM

// 1. Receive Data
$input = json_decode(file_get_contents('php://input'), true);
$origin = $input['origin'] ?? 'Unknown';
$destination = $input['destination'] ?? 'Unknown';
$distance = $input['distance_km'] ?? 0;

// 2. API KEY SETUP
$apiKey = "AIzaSyBqbf4jgzucpaHLK3y7upcZcyvuaGq8-Z4"; 

// 3. Prepare AI Prompt with TIME & DATE Context
$prompt = "
You are a logistics expert in the Philippines.
Current Date/Time: $currentDate at $currentTime.
Route: From $origin to $destination.
Distance: $distance km.

TASK:
1. Analyze Traffic: Based on the current time ($currentTime) and day ($currentDate), predict if it is rush hour or light traffic.
2. Analyze Weather: Based on the current month, predict typical weather patterns and calendar events and calculate the distance if destination is need a airplane or sea cargo (e.g. Typhoon season vs Dry season).
3. Estimate Time: Combine distance + predicted traffic + predicted weather to give a realistic delivery time.

Return ONLY raw JSON:
{
    \"prediction\": \"Estimated time (e.g. 3 hours - Heavy Traffic)\",
    \"confidence\": \"Confidence score number only (e.g. 85)\",
    \"reasoning\": \"Summarize the traffic and weather impact in one sentence based on the current time and season.\"
}";

// Use Gemini 2.5 Flash
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$data = [
    "contents" => [
        [ "parts" => [ ["text" => $prompt] ] ]
    ]
    // Removed 'tools' section to prevent API errors
];

// 4. Send Request
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);

if(curl_errno($ch)) {
    echo json_encode(['error' => 'Connection Error: ' . curl_error($ch)]);
    exit;
}
curl_close($ch);

// 5. Process Response
$decoded = json_decode($response, true);

if (isset($decoded['error'])) {
    // If model version error, fallback to gemini-1.5-flash
    $fallbackUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;
    // (Logic for fallback could go here, but for now just showing the error to debug)
    echo json_encode(['error' => 'Gemini API Error: ' . $decoded['error']['message']]);
    exit;
}

if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
    $rawText = $decoded['candidates'][0]['content']['parts'][0]['text'];
    $cleanJson = str_replace(['```json', '```', 'json'], '', $rawText);
    $finalData = json_decode($cleanJson, true);
    
    if ($finalData) {
        echo json_encode($finalData);
    } else {
        echo json_encode([
            'prediction' => 'Estimate Unavailable',
            'confidence' => '0',
            'reasoning' => 'AI returned text but not valid JSON format.'
        ]);
    }
} else {
    echo json_encode(['error' => 'No text returned from AI. Response was empty.']);
}
?>