<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle CORS preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit;
}

define("SECRET_KEY", "2a4904720a3c15d3c372f3b97cef43bba27079007f1f82279a4cf87892985790"); // Change this to a strong secret
?>
