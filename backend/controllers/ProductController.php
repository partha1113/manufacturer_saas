<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

class ProductController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // ✅ Add a new product
    public function addProduct($data, $user) {
        $name = $data['name'] ?? null;
        $price = $data['price'] ?? null;
        $description = $data['description'] ?? null;
        $manufacturer_id = $user['id']; // Extracted from JWT token
        $tenant_id = $user['tenant_id'] ?? null; // ✅ Extract tenant_id from JWT
    
        if (!$name || !$price || !$manufacturer_id || !$tenant_id) {
            return ["status" => "error", "message" => "Missing required fields"];
        }
    
        try {
            $stmt = $this->db->prepare("INSERT INTO products (name, price, description, manufacturer_id, tenant_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $price, $description, $manufacturer_id, $tenant_id]);
    
            return ["status" => "success", "message" => "Product added successfully"];
        } catch (PDOException $e) {
            return ["status" => "error", "message" => "Database error: " . $e->getMessage()];
        }
    }
    

    // ✅ Retrieve all products for a manufacturer
    public function getProducts() {
        $user = authorizeUser(['tenant', 'super_admin']); // ✅ Ensure correct roles
    
        if (!$user || !isset($user['tenant_id'])) {
            return ["status" => "error", "message" => "Unauthorized or missing tenant ID"];
        }
    
        try {
            $stmt = $this->db->prepare("SELECT id, name, description, price, created_at, updated_at FROM products WHERE tenant_id = ?");
            $stmt->execute([$user['tenant_id']]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if (empty($products)) {
                return ["status" => "success", "message" => "No products found", "products" => []]; // ✅ Handle empty products
            }
    
            return ["status" => "success", "products" => $products];
        } catch (PDOException $e) {
            return ["status" => "error", "message" => "Database error: " . $e->getMessage()];
        }
    }
    
    

    // ✅ Update a product (Only by the manufacturer who owns it)
    public function updateProduct($data) {
        $user = authorizeUser(['tenant']); // ✅ Ensure only tenants can update
    
        if (!$user || !isset($user['tenant_id'])) {
            return ["status" => "error", "message" => "Unauthorized or Tenant ID missing"];
        }
    
        if (!isset($data['id'], $data['name'], $data['description'], $data['price'])) {
            return ["status" => "error", "message" => "Missing required fields"];
        }
    
        try {
            // Ensure the product belongs to the logged-in tenant
            $stmt = $this->db->prepare("UPDATE products SET name = ?, description = ?, price = ? WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$data['name'], $data['description'], $data['price'], $data['id'], $user['tenant_id']]);
    
            if ($stmt->rowCount() > 0) {
                return ["status" => "success", "message" => "Product updated successfully"];
            } else {
                return ["status" => "error", "message" => "Product not found or no changes made"];
            }
        } catch (PDOException $e) {
            return ["status" => "error", "message" => "Database error: " . $e->getMessage()];
        }
    }
    
    
    // ✅ Delete a product (Only by the manufacturer who owns it)
    public function deleteProduct($data) {
        $user = authorizeUser(['tenant']); // ✅ Ensure only tenants can delete
    
        if (!$user || !isset($user['tenant_id'])) {
            return ["status" => "error", "message" => "Unauthorized or Tenant ID missing"];
        }
    
        if (!isset($data['id'])) {
            return ["status" => "error", "message" => "Product ID is required"];
        }
    
        try {
            // Ensure the product belongs to the logged-in tenant
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$data['id'], $user['tenant_id']]);
    
            if ($stmt->rowCount() > 0) {
                return ["status" => "success", "message" => "Product deleted successfully"];
            } else {
                return ["status" => "error", "message" => "Product not found or unauthorized action"];
            }
        } catch (PDOException $e) {
            return ["status" => "error", "message" => "Database error: " . $e->getMessage()];
        }
    }    
    
}
?>
