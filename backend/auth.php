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
                   d.nombre as dependencia_nombre, d.nivel as dependencia_nivel
            FROM usuarios u 
            LEFT JOIN roles r ON u.rol_id = r.id 
            LEFT JOIN dependencias d ON u.dependencia_id = d.id
            WHERE u.username = ? AND u.estado = 'Activo'
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        // Usuarios predefinidos para pruebas
        $predefinedUsers = [
            'admin' => ['password' => 'admin123', 'rol' => 'Administrador Central', 'id' => 1],
            'supervisor.lp' => ['password' => 'admin123', 'rol' => 'Supervisor Delegación', 'id' => 2],
            'jefe.crim.lp' => ['password' => 'admin123', 'rol' => 'Jefe Sección', 'id' => 3]
        ];
        
        $validPassword = false;
        
        if ($user && password_verify($password, $user['password'])) {
            $validPassword = true;
        } elseif (isset($predefinedUsers[$username]) && $predefinedUsers[$username]['password'] === $password) {
            $validPassword = true;
            if (!$user) {
                $user = [
                    'id' => $predefinedUsers[$username]['id'],
                    'nombre_completo' => ucfirst(str_replace('.', ' ', $username)),
                    'username' => $username,
                    'email' => $username . '@policia.gob.ar',
                    'rol_nombre' => $predefinedUsers[$username]['rol'],
                    'rol_nivel' => $predefinedUsers[$username]['rol'] === 'Administrador Central' ? 100 : 50,
                    'dependencia_id' => $username === 'admin' ? 1 : ($username === 'supervisor.lp' ? 2 : 5),
                    'dependencia_nombre' => $username === 'admin' ? 'Central' : ($username === 'supervisor.lp' ? 'Delegación La Plata' : 'Sección Criminalística LP'),
                    'puede_ver_todas' => $username === 'admin' ? 1 : 0,
                    'nivel_acceso' => $username === 'admin' ? 'todas' : ($username === 'supervisor.lp' ? 'delegacion' : 'solo_propio'),
                    'permisos' => null
                ];
            }
        }
        
        if ($validPassword && $user) {
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
            } else {
                $permisos = [
                    'dashboard' => ['ver'],
                    'personal' => ['ver', 'crear', 'editar', 'eliminar'],
                    'recargos' => ['ver', 'crear', 'editar', 'eliminar'],
                    'expedientes' => ['ver', 'crear', 'editar', 'eliminar'],
                    'licencias' => ['ver', 'crear', 'editar', 'eliminar'],
                    'configuracion' => ['ver', 'editar']
                ];
                if ($user['rol_nombre'] === 'Administrador Central') {
                    $permisos['usuarios'] = ['ver', 'crear', 'editar', 'eliminar'];
                }
            }
            
            $payload = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'nombre' => $user['nombre_completo'],
                'rol' => $user['rol_nombre'],
                'rol_nivel' => $user['rol_nivel'] ?? 0,
                'dependencia_id' => $user['dependencia_id'] ?? null,
                'dependencia_nombre' => $user['dependencia_nombre'] ?? null,
                'puede_ver_todas' => ($user['puede_ver_todas'] ?? 0) == 1,
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
                    'dependencia_id' => $user['dependencia_id'],
                    'dependencia_nombre' => $user['dependencia_nombre'],
                    'puede_ver_todas' => ($user['puede_ver_todas'] ?? 0) == 1,
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