<?php
include("connection.php");

$method = $_SERVER['REQUEST_METHOD'];
$uri = explode("/", trim($_SERVER['REQUEST_URI'], "/"));

if (count($uri) < 2) {
    echo json_encode(["error" => "Invalid endpoint"]);
    exit;
}

$resource = strtolower($uri[1]); 

switch ($resource) {
    case "auth": include("routes/auth.php"); break;
    case "users": include("routes/user.php"); break;
    case "crm": include("routes/CRM.php"); break;
    case "csm": include("routes/CSM.php"); break;
    case "edoc": include("routes/E-Doc.php"); break;
    case "bifa": include("routes/BIFA.php"); break;
    case "cpn": include("routes/CPN.php"); break;
    default:
        http_response_code(404);
        echo json_encode(["error" => "Resource not found"]);
}
?>
