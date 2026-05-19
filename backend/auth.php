<?php
require_once 'config.php';

$action = $_GET['action'] ?? '';

switch($action) {
    case 'login':
        handleLogin();
        break;
    case 'verify':
        handleVerify();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Acción no encontrada']);
}

function handleLogin() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT u.*, r.nombre as rol_nombre, r.nivel as rol_nivel,
                   d.nombre as dependencia_nombre, d.nivel as dependencia_nivel,
                   c.valor as subordinado_nombre
            FROM usuarios u 
            LEFT JOIN roles r ON u.rol_id = r.id 
            LEFT JOIN dependencias d ON u.dependencia_id = d.id
            LEFT JOIN catalogos c ON u.subordinado_id = c.id AND c.tipo = 'oficinas'
            WHERE u.username = ? AND u.estado = 'Activo'
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        $validPassword = false;
        
        if ($user && password_verify($password, $user['password'])) {
            $validPassword = true;
        }
        
        // Usuarios predefinidos para pruebas (solo para desarrollo)
        $predefinedUsers = [
            'admin' => ['password' => 'admin123', 'rol' => 'Administrador Central', 'rol_id' => 1, 'id' => 1, 'dependencia_id' => 1, 'subordinado_id' => null, 'nivel_acceso' => 'todas'],
        ];
        
        if (!$validPassword && isset($predefinedUsers[$username]) && $predefinedUsers[$username]['password'] === $password) {
            $validPassword = true;
            if (!$user) {
                $predef = $predefinedUsers[$username];
                $user = [
                    'id' => $predef['id'],
                    'nombre_completo' => ucfirst(str_replace('.', ' ', $username)),
                    'username' => $username,
                    'email' => $username . '@policia.gob.ar',
                    'rol_nombre' => $predef['rol'],
                    'rol_id' => $predef['rol_id'],
                    'rol_nivel' => $predef['rol_id'] == 1 ? 100 : 50,
                    'dependencia_id' => $predef['dependencia_id'],
                    'subordinado_id' => $predef['subordinado_id'],
                    'nivel_acceso' => $predef['nivel_acceso'],
                    'permisos' => null
                ];
            }
        }
        
        if ($validPassword && $user) {
            // Obtener el valor del subordinado desde catalogos (dinámico)
            $subordinado_valor = null;
            $subordinado_id = $user['subordinado_id'] ?? null;
            
            if ($subordinado_id) {
                $stmt2 = $db->prepare("SELECT valor FROM catalogos WHERE id = ? AND tipo = 'oficinas' AND activo = 1");
                $stmt2->execute([$subordinado_id]);
                $sub = $stmt2->fetch();
                if ($sub) {
                    $subordinado_valor = $sub['valor'];
                }
            }
            
            // Obtener permisos del rol
            $permisos = [];
            if (isset($user['rol_id'])) {
                $stmt = $db->prepare("
                    SELECT p.modulo, p.accion 
                    FROM roles_permisos rp 
                    JOIN permisos p ON rp.permiso_id = p.id 
                    WHERE rp.rol_id = ?
                ");
                $stmt->execute([$user['rol_id']]);
                $permisosList = $stmt->fetchAll();
                
                foreach ($permisosList as $p) {
                    if (!isset($permisos[$p['modulo']])) $permisos[$p['modulo']] = [];
                    $permisos[$p['modulo']][] = $p['accion'];
                }
            }
            
            $payload = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'nombre' => $user['nombre_completo'],
                'rol' => $user['rol_nombre'],
                'rol_id' => $user['rol_id'],
                'rol_nivel' => $user['rol_nivel'] ?? 0,
                'dependencia_id' => $user['dependencia_id'] ?? null,
                'dependencia_nombre' => $user['dependencia_nombre'] ?? null,
                'subordinado_id' => $subordinado_id,
                'subordinado_nombre' => $subordinado_valor,
                'subordinado_valor' => $subordinado_valor,
                'puede_ver_todas' => ($user['nivel_acceso'] ?? 'solo_propio') === 'todas',
                'nivel_acceso' => $user['nivel_acceso'] ?? 'solo_propio',
                'permisos' => $permisos,
                'exp' => time() + JWT_EXPIRATION
            ];
            
            $token = generateJWT($payload);
            
            echo json_encode([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'nombre' => $user['nombre_completo'],
                    'username' => $user['username'],
                    'rol' => $user['rol_nombre'],
                    'rol_id' => $user['rol_id'],
                    'dependencia_id' => $user['dependencia_id'],
                    'dependencia_nombre' => $user['dependencia_nombre'],
                    'subordinado_id' => $subordinado_id,
                    'subordinado_nombre' => $subordinado_valor,
                    'subordinado_valor' => $subordinado_valor,
                    'puede_ver_todas' => ($user['nivel_acceso'] ?? 'solo_propio') === 'todas',
                    'nivel_acceso' => $user['nivel_acceso'] ?? 'solo_propio',
                    'permisos' => $permisos
                ]
            ]);
            return;
        }
    } catch(Exception $e) {
        error_log("Error en login: " . $e->getMessage());
    }
    
    http_response_code(401);
    echo json_encode(['error' => 'Credenciales inválidas']);
}

function handleVerify() {
    $authUser = getAuthUser();
    if ($authUser) {
        echo json_encode(['valid' => true, 'user' => $authUser]);
    } else {
        echo json_encode(['valid' => false]);
    }
}

function handleLogout() {
    echo json_encode(['success' => true]);
}
?>