<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle CORS preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit;
}

require __DIR__ . '/db.php';
require __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$jwt_secret = $_ENV['JWT_SECRET'] ?? 'default_secret_key';
// Load environment variables (JWT Secret)
// $jwt_secret = "your_secret_key_here"; // Ideally, load this from .env
if (!isset($_ENV['JWT_SECRET'])) {
    die("⚠️ Error: JWT_SECRET not found in .env");
}

$jwt_secret = $_ENV['JWT_SECRET'];
// Function to generate JWT Token
if (!function_exists('generateJWT')) {
    function generateJWT($user) {
        global $jwt_secret;
        
        // Debug: Log the user array before generating JWT
        file_put_contents("debug_jwt.log", "Generating JWT for user: " . json_encode($user) . "\n", FILE_APPEND);
    
        $payload = [
            "iss" => "manufacturer_saas",
            "aud" => "manufacturer_saas_users",
            "iat" => time(),
            "exp" => time() + (60 * 60 * 24), // Token expires in 24 hours
            "user" => [
                "id" => $user['id'],
                "name" => $user['name'],
                "email" => $user['email'],
                "role" => $user['role'],
                "tenant_id" => $user['tenant_id'] ?? null
            ]
        ];
    
        // Debug: Log the final payload before encoding
        file_put_contents("debug_jwt.log", "Payload before encoding: " . json_encode($payload) . "\n", FILE_APPEND);
    
        return JWT::encode($payload, $jwt_secret, 'HS256');
    }
    }
    

// Function to validate JWT Token
if (!function_exists('validateJWT')) {
    function validateJWT($token) {
        global $jwt_secret;
        try {
            $decoded = JWT::decode($token, new Key($jwt_secret, 'HS256'));
    
            // Debug: Log the decoded payload
            file_put_contents("debug_jwt.log", "Decoded JWT: " . json_encode($decoded) . "\n", FILE_APPEND);
    
            return (array) $decoded->user;
        } catch (Exception $e) {
            file_put_contents("debug_jwt.log", "JWT Validation Error: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }    
    
}

// ✅ FIX: Allow multiple roles in authorization
if (!function_exists('authorizeUser')) {
    function authorizeUser($allowedRoles) {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            file_put_contents("debug_auth.log", "No valid token found in headers\n", FILE_APPEND);
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Unauthorized: No token provided"]);
            exit;
        }
    
        $token = $matches[1]; // Extract JWT
        $user = validateJWT($token);
    
        if (!$user) {
            file_put_contents("debug_auth.log", "Invalid token: $token\n", FILE_APPEND);
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid token"]);
            exit;
        }
    
        // ✅ Log user role and allowed roles for debugging
        file_put_contents("debug_auth.log", "User Role: " . $user['role'] . " | Allowed Roles: " . json_encode($allowedRoles) . "\n", FILE_APPEND);
    
        // ✅ Ensure role is correctly checked
        if (!in_array($user['role'], (array) $allowedRoles)) {
            file_put_contents("debug_auth.log", "Forbidden access by user role: " . $user['role'] . "\n", FILE_APPEND);
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Forbidden: Insufficient permissions"]);
            exit;
        }
    
        return $user;
    }
    
}
?>
