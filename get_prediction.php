<?php
// filename: get_prediction.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

// Set timezone
date_default_timezone_set('Asia/Manila');
$currentDate = date("l, F j, Y"); 
$currentTime = date("g:i A");     

// 1. Receive Data
$input = json_decode(file_get_contents('php://input'), true);
$origin = $input['origin'] ?? 'Unknown';
$destination = $input['destination'] ?? 'Unknown';
$distance = $input['distance_km'] ?? 0;

// ---------------------------------------------------------
// 🚨 ILAGAY MO DITO YUNG API KEY MO 🚨
// ---------------------------------------------------------
$apiKey = "AIzaSyArsHtI21_Lk59Xt4kUvYQWAIO67zUxmRo1"; // <--- DOUBLE CHECK MO KUNG TAMA TO -> 

// 2. Prepare Prompt
$prompt = "
You are a logistics expert in the Philippines.
Current Date/Time: $currentDate at $currentTime.
Route: From $origin to $destination.
Distance: $distance km.

TASK:
1. Analyze Traffic: Predict congestion based on time/location.
2. Analyze Logistics: Is this inter-island (requires ferry/RORO)?
3. Estimate Time: Calculate total travel time.

Return ONLY raw JSON (no markdown):
{
    \"prediction\": \"e.g. 4 hrs 30 mins\",
    \"confidence\": \"e.g. 90\",
    \"reasoning\": \"Short sentence summarizing traffic/route.\"
}";

$requestData = [
    "contents" => [ [ "parts" => [ ["text" => $prompt] ] ] ]
];

// 3. THE "TANK MODE" LOADER (UPDATED WITH YOUR MODELS)
$modelsToTry = [
    "gemini-2.5-flash",      // Priority 1: Pinaka-bago mo
    "gemini-2.0-flash",      // Priority 2: Stable v2
    "gemini-exp-1206"        // Priority 3: Experimental
];

$finalResponse = null;
$lastError = "No attempts made.";
$usedModel = "None";

foreach ($modelsToTry as $model) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=" . $apiKey;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    // SSL FIX (Para sa XAMPP/Localhost)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch); 
    curl_close($ch);

    if ($httpCode == 200) {
        $finalResponse = $response;
        $usedModel = $model;
        break; // Success! Stop loop.
    } else {
        $jsonErr = json_decode($response, true);
        $apiMsg = $jsonErr['error']['message'] ?? "HTTP $httpCode";
        $lastError = "Model $model failed: " . ($curlErr ? $curlErr : $apiMsg);
    }
}

// 4. PROCESS RESULT
if ($finalResponse) {
    $decoded = json_decode($finalResponse, true);
    if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
        $rawText = $decoded['candidates'][0]['content']['parts'][0]['text'];
        
        // Clean JSON using Regex
        if (preg_match('/\{.*\}/s', $rawText, $matches)) {
            echo $matches[0]; // SUCCESS!
            exit;
        }
    }
}

// 5. FALLBACK
$fallbackTime = round($distance / 40, 1) . " hrs"; 

echo json_encode([
    'prediction' => "Est. $fallbackTime",
    'confidence' => '50',
    'reasoning' => "Offline. Error: $lastError" 
]);
?>