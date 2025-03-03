<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/db.php';  
require_once __DIR__ . '/../config/auth.php'; 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;

class UserController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function registerUser($data, $loggedInUserRole = null, $loggedInUserId = null) {
        error_log("Register User Request: " . json_encode($data));

        if (empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($data['role'])) {
            return ['status' => 'error', 'message' => 'Missing required fields'];
        }

        $name = $data['name'];
        $email = $data['email'];
        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        $role = $data['role'];
        $tenantId = null;

        if (!in_array($role, ['super_admin', 'tenant', 'distributor', 'customer'])) {
            return ['status' => 'error', 'message' => 'Invalid role'];
        }

        if ($role === 'super_admin') {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'super_admin'");
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                return ['status' => 'error', 'message' => 'Super Admin already exists'];
            }
        } elseif ($role === 'tenant' && $loggedInUserRole !== 'super_admin') {
            return ['status' => 'error', 'message' => 'Only Super Admin can create Tenants'];
        } elseif ($role === 'distributor' && $loggedInUserRole !== 'tenant') {
            return ['status' => 'error', 'message' => 'Only Tenants can create Distributors'];
        } elseif ($role === 'customer') {
            if (empty($data['tenant_id'])) {
                return ['status' => 'error', 'message' => 'Customer registration requires a valid tenant_id'];
            }
            $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ? AND role = 'tenant'");
            $stmt->execute([$data['tenant_id']]);
            if (!$stmt->fetchColumn()) {
                return ['status' => 'error', 'message' => 'Invalid tenant_id'];
            }
        }        

        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            return ['status' => 'error', 'message' => 'Email already exists'];
        }

        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, tenant_id) VALUES (?, ?, ?, ?, ?)");
        try {
            $result = $stmt->execute([$name, $email, $password, $role, $tenantId]);
            if ($role === 'tenant') {
                $tenantId = $this->db->lastInsertId();
                $stmt = $this->db->prepare("UPDATE users SET tenant_id = ? WHERE id = ?");
                $stmt->execute([$tenantId, $tenantId]);
            } elseif ($role === 'customer') {
                $tenantId = $data['tenant_id'] ?? null;
            }            
            return ['status' => 'success', 'message' => 'User registered successfully'];
        } catch (PDOException $e) {
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    public function getTenantCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total_tenants FROM users WHERE role = 'tenant'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return [
            "status" => "success",
            "total_tenants" => $result['total_tenants']
        ];
    }
    
    public function login($data) {
        error_log("Login Attempt: " . json_encode($data));

        if (empty($data['email']) || empty($data['password'])) {
            return ["status" => "error", "message" => "Email and Password required"];
        }

        $stmt = $this->db->prepare("SELECT id, name, email, role, password FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            error_log("Invalid login attempt for email: " . $data['email']);
            return ["status" => "error", "message" => "Invalid credentials"];
        }

        $token = generateJWT($user);
        unset($user['password']); // Remove password before sending response
        return ["status" => "success", "message" => "Login successful", "token" => $token, "user" => $user];

    }
}
