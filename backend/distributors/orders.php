<?php
require __DIR__ . '/../config/auth.php';

// Get JWT Token from Header
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

// Only Distributors can access
$distributor = authorizeUser($token, 'distributor');

echo json_encode(["message" => "Welcome, Distributor!", "user" => $distributor]);
?>
