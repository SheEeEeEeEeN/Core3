<?php
// filename: get_prediction.php
// FIXED: Valid Models + Placeholder for NEW KEY

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

date_default_timezone_set('Asia/Manila');
$currentDate = date("l, F j, Y"); 
$currentTime = date("g:i A");     

// 1. Receive Data
$input = json_decode(file_get_contents('php://input'), true);
$origin = $input['origin'] ?? 'Unknown';
$destination = $input['destination'] ?? 'Unknown';
$distance = $input['distance_km'] ?? 0;

// =========================================================
// 🚨 PALITAN MO ITO NG BAGO MONG KEY GALING GOOGLE AI STUDIO 🚨
// =========================================================
$apiKey = "AIzaSyDESoXVyLm0X2ZtEXfUnpHmwMzttOhyr0Q"; 

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

// 3. UPDATED MODEL LIST (Gamitin natin ang mga SURE na gumagana ngayon)
$modelsToTry = [
    "gemini-1.5-flash",      // Pinakamabilis at mura (Stable)
    "gemini-2.0-flash-exp",  // Experimental fast model
    "gemini-1.5-pro"         // Mas matalino pero mas mabagal
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
    
    // SSL FIX
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch); 
    curl_close($ch);

    if ($httpCode == 200) {
        $finalResponse = $response;
        $usedModel = $model;
        break; // Success!
    } else {
        $jsonErr = json_decode($response, true);
        $apiMsg = $jsonErr['error']['message'] ?? "HTTP $httpCode";
        // Log lang natin pero try pa sa susunod na model
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

// 5. FALLBACK (Pag ayaw talaga gumana ng API Key)
$hours = floor($distance / 40); // Avg speed 40km/h
$mins = round(($distance / 40 - $hours) * 60);
$fallbackTime = "{$hours} hrs {$mins} mins";

echo json_encode([
    'prediction' => "Est. $fallbackTime",
    'confidence' => '50',
    'reasoning' => "Offline Mode. API Error: $lastError. (Check your API Key!)" 
]);
?>