<?php
require __DIR__ . '/../config/auth.php';

// Get JWT Token from Header
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

// Only Super Admin can access
$superAdmin = authorizeUser($token, 'super_admin');

echo json_encode(["message" => "Welcome, Super Admin!", "user" => $superAdmin]);
?>
