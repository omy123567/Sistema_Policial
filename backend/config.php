<?php
// ==================== CONFIGURACIÓN DE BASE DE DATOS ====================
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_policial');
define('DB_USER', 'root');
define('DB_PASS', '');

// ==================== CONFIGURACIÓN JWT ====================
define('JWT_SECRET', 's3gUr1d4d_p0l1c14l_2024_sup3r_s3gUr4');
define('JWT_EXPIRATION', 86400); // 24 horas

// ==================== CONFIGURACIÓN GENERAL ====================
define('APP_NAME', 'Sistema de Gestión Policial');
define('APP_VERSION', '1.0.0');
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');

// ==================== ZONA HORARIA ====================
date_default_timezone_set('America/Argentina/Buenos_Aires');

// ==================== CONFIGURACIÓN DE ERRORES ====================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==================== HEADERS CORS ====================
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ==================== FUNCIÓN DE CONEXIÓN A BD ====================
function getDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
        exit;
    }
}

// ==================== FUNCIONES JWT ====================
function generateJWT($payload) {
    $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload_encoded = base64_encode(json_encode($payload));
    $signature = hash_hmac('sha256', $header . '.' . $payload_encoded, JWT_SECRET, true);
    $signature_encoded = base64_encode($signature);
    return $header . '.' . $payload_encoded . '.' . $signature_encoded;
}

function verifyJWT($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    
    $header = $parts[0];
    $payload_encoded = $parts[1];
    $signature = $parts[2];
    
    $expected_signature = base64_encode(hash_hmac('sha256', $header . '.' . $payload_encoded, JWT_SECRET, true));
    
    if ($signature !== $expected_signature) return null;
    
    $payload = json_decode(base64_decode($payload_encoded), true);
    if ($payload['exp'] < time()) return null;
    
    return $payload;
}

function getAuthUser() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) return null;
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    return verifyJWT($token);
}

// ==================== FUNCIONES DE SANITIZACIÓN ====================
function sanitizeInput($data) {
    if (is_null($data)) return null;
    if (is_array($data)) return array_map('sanitizeInput', $data);
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
?>