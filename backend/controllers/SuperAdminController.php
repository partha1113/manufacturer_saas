<?php
require_once __DIR__ . '/../config/db.php';  // Ensure the database file is included

class SuperAdminController {
    private $pdo;

    public function __construct() {
        $database = new Database();  // Create an instance of Database class
        $this->pdo = $database->connect();  // Get PDO connection

        if (!$this->pdo) {
            throw new Exception("Database connection is missing.");
        }
    }

    public function getStats() {
        try {
            // Get total tenants
            $stmt1 = $this->pdo->prepare("SELECT COUNT(*) as total_tenants FROM users WHERE role = 'tenant'");
            $stmt1->execute();
            $tenantCount = $stmt1->fetch(PDO::FETCH_ASSOC)['total_tenants'];

            // Get active tenants (assuming is_active column exists)
            $stmt2 = $this->pdo->prepare("SELECT COUNT(*) as active_tenants FROM users WHERE role = 'tenant' AND is_active = 1");
            $stmt2->execute();
            $activeTenantCount = $stmt2->fetch(PDO::FETCH_ASSOC)['active_tenants'];

            return [
                "status" => "success",
                "total_tenants" => $tenantCount,
                "active_tenants" => $activeTenantCount
            ];
        } catch (Exception $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }
}
