<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../config/auth.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// User Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($input['email']) || !isset($input['password'])) {
        echo json_encode(["error" => "Email and password are required"]);
        exit;
    }

    $email = $input['email'];
    $password = $input['password'];

    // Verify user credentials
    $stmt = $pdo->prepare("SELECT id, name, email, password, role, tenant_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $token = generateJWT($user);
        echo json_encode(["token" => $token, "user" => $user]);
    } else {
        echo json_encode(["error" => "Invalid credentials"]);
    }
}
?>
