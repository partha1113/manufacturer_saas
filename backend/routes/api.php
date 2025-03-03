<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle CORS preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With");
    http_response_code(200);
    exit;
}

// Debugging
error_log("Request: " . $_SERVER['REQUEST_METHOD'] . " - " . $_SERVER['REQUEST_URI']);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../controllers/SuperAdminController.php';

$superAdminController = new SuperAdminController();
$response = $superAdminController->getStats();

echo json_encode($response);
exit;

// Debugging: Log the route being accessed
error_log("Requested route: " . ($_GET['routes'] ?? 'No route provided'));

if (!isset($_GET['routes'])) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    echo json_encode(["status" => "error", "message" => "Invalid request - No route provided"]);
    exit;
}

$routes = $_GET['routes'];
error_log("Processing route: $routes");
error_log("All Routes Received: " . json_encode($_GET));

$userController = new UserController();
$public_routes = ['login', 'register'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents("debug_request.log", date("Y-m-d H:i:s") . " - " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);

    $data = json_decode(file_get_contents("php://input"), true);

    if ($routes === 'login') {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        echo json_encode($userController->login($data));
        exit;
    }
    
    if ($routes === 'register') {
        // Allow customers to register without a token
        if ($data['role'] === 'customer') {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
            echo json_encode($userController->registerUser($data)); // ✅ Customer registration allowed
            exit;
        }

        // Extract token from headers
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;
        $token = null;
        $loggedInUser = null;

        // Validate token if provided
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            $loggedInUser = validateJWT($token);
        }

        // Allow super_admin registration only if no admin exists
        if ($data['role'] === 'super_admin') {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
            echo json_encode($userController->registerUser($data)); // ✅ No token needed for first super admin
        } else {
            if (!$loggedInUser) {
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
                header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Token missing']);
                exit;
            }
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
            echo json_encode($userController->registerUser($data, $loggedInUser['role']));
        }
        exit;
    }

    // ✅ Apply Authorization for Protected Routes
    if (!in_array($routes, $public_routes)) {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
            echo json_encode(["status" => "error", "message" => "Unauthorized: No token provided"]);
            exit;
        }

        $token = str_replace("Bearer ", "", $headers['Authorization']);
        $user = validateJWT($token);

        if (!$user) {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
            echo json_encode(["status" => "error", "message" => "Invalid token"]);
            exit;
        }
    }
}

$productController = new ProductController();

switch ($routes) {
    case 'get_products':
        require_once '../controllers/ProductController.php';
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        echo json_encode($productController->getProducts());
        exit; 

    case 'add_product': // ✅ Add Product (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);

            // ✅ Get the Authorization header and validate JWT
            $headers = getallheaders();
            if (!isset($headers['Authorization'])) {
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
                header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
                echo json_encode(["status" => "error", "message" => "Unauthorized: No token provided"]);
                exit;
            }

            $token = str_replace("Bearer ", "", $headers['Authorization']);
            $user = validateJWT($token); // ✅ Extract user from token

            if (!$user) {
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
                header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
                echo json_encode(["status" => "error", "message" => "Invalid token"]);
                exit;
            }

            // ✅ Pass the user object (with tenant_id) to ProductController
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
            echo json_encode($productController->addProduct($data, $user));
            exit;
        }
        break;

    case 'update_product': // ✅ Update Product (PUT)
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $data = json_decode(file_get_contents("php://input"), true);
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
            echo json_encode($productController->updateProduct($data));
            exit;
        }
        break;

    case 'delete_product': // ✅ Delete Product (DELETE)
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $data = json_decode(file_get_contents("php://input"), true);
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
            echo json_encode($productController->deleteProduct($data));
            exit;
        }
        break;
    case 'tenant_count': // ✅ Fetch Tenant Count for Super Admin
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Extract token from headers
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
            echo json_encode(["status" => "error", "message" => "Unauthorized: No token provided"]);
            exit;
        }

        $token = str_replace("Bearer ", "", $headers['Authorization']);
        $user = validateJWT($token);

        if (!$user || $user['role'] !== 'super_admin') {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
            echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
            exit;
        }

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        echo json_encode($userController->getTenantCount());
        exit;
    }
    break;
    case 'super-admin/stats': // ✅ Fetch Super Admin Stats
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Extract token from headers
            $headers = getallheaders();
            if (!isset($headers['Authorization'])) {
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
                header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
                echo json_encode(["status" => "error", "message" => "Unauthorized: No token provided"]);
                exit;
            }
    
            $token = str_replace("Bearer ", "", $headers['Authorization']);
            $user = validateJWT($token);
    
            if (!$user || $user['role'] !== 'super_admin') {
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
                header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
                echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
                exit;
            }
    
            // ✅ Call the controller method for stats
            require_once '../controllers/SuperAdminController.php';
            $superAdminController = new SuperAdminController();
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
            echo json_encode($superAdminController->getStats());
            exit;
        }
        break;
    
    default:
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        echo json_encode(["status" => "error", "message" => "Invalid request - Route not found"]);
        exit;
}

// If no valid route matched
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
echo json_encode(["status" => "error", "message" => "Invalid request"]);
exit;