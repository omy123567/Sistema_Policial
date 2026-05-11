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
        
        // Usuarios predefinidos para pruebas
        $predefinedUsers = [
            'admin' => ['password' => 'admin123', 'rol' => 'Administrador Central', 'rol_id' => 1, 'id' => 1, 'dependencia_id' => 1, 'subordinado_id' => null, 'nivel_acceso' => 'todas'],
            'supervisor.lp' => ['password' => 'admin123', 'rol' => 'Supervisor Delegación', 'rol_id' => 2, 'id' => 2, 'dependencia_id' => 1, 'subordinado_id' => null, 'nivel_acceso' => 'delegacion'],
            'jefe.lb' => ['password' => 'admin123', 'rol' => 'Jefe Sección', 'rol_id' => 3, 'id' => 4, 'dependencia_id' => 1, 'subordinado_id' => 194, 'nivel_acceso' => 'solo_subordinado']
        ];
        
        $validPassword = false;
        
        if ($user && password_verify($password, $user['password'])) {
            $validPassword = true;
        } elseif (isset($predefinedUsers[$username]) && $predefinedUsers[$username]['password'] === $password) {
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
            
            // Obtener dependencias y subordinados permitidos
            $dependenciasPermitidas = getDependenciasPermitidas($db, $user);
            $subordinadosPermitidos = getSubordinadosPermitidos($db, $user);
            
            $payload = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'nombre' => $user['nombre_completo'],
                'rol' => $user['rol_nombre'],
                'rol_id' => $user['rol_id'],
                'rol_nivel' => $user['rol_nivel'] ?? 0,
                'dependencia_id' => $user['dependencia_id'] ?? null,
                'dependencia_nombre' => $user['dependencia_nombre'] ?? null,
                'subordinado_id' => $user['subordinado_id'] ?? null,
                'subordinado_nombre' => $user['subordinado_nombre'] ?? null,
                'puede_ver_todas' => ($user['nivel_acceso'] ?? 'solo_propio') === 'todas',
                'nivel_acceso' => $user['nivel_acceso'] ?? 'solo_propio',
                'dependencias_permitidas' => $dependenciasPermitidas,
                'subordinados_permitidos' => $subordinadosPermitidos,
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
                    'subordinado_id' => $user['subordinado_id'],
                    'subordinado_nombre' => $user['subordinado_nombre'],
                    'puede_ver_todas' => ($user['nivel_acceso'] ?? 'solo_propio') === 'todas',
                    'nivel_acceso' => $user['nivel_acceso'] ?? 'solo_propio',
                    'dependencias_permitidas' => $dependenciasPermitidas,
                    'subordinados_permitidos' => $subordinadosPermitidos,
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

// Función para obtener dependencias permitidas
function getDependenciasPermitidas($db, $usuario) {
    $puede_ver_todas = ($usuario['nivel_acceso'] ?? 'solo_propio') === 'todas';
    $nivel_acceso = $usuario['nivel_acceso'] ?? 'solo_propio';
    $dependencia_id = $usuario['dependencia_id'] ?? null;
    
    // Administrador puede ver todo
    if ($puede_ver_todas || $nivel_acceso === 'todas') {
        $stmt = $db->query("SELECT id FROM dependencias WHERE activo = 1");
        return array_column($stmt->fetchAll(), 'id');
    }
    
    // Nivel solo_propio: solo su dependencia
    if ($nivel_acceso === 'solo_propio' || !$dependencia_id) {
        return $dependencia_id ? [$dependencia_id] : [];
    }
    
    // Nivel delegacion: su dependencia y todas las hijas
    $ids = [$dependencia_id];
    $stmt = $db->prepare("
        WITH RECURSIVE dependencias_hijas AS (
            SELECT id FROM dependencias WHERE id = ?
            UNION ALL
            SELECT d.id FROM dependencias d
            INNER JOIN dependencias_hijas dh ON d.padre_id = dh.id
        )
        SELECT id FROM dependencias_hijas
    ");
    $stmt->execute([$dependencia_id]);
    $hijas = $stmt->fetchAll();
    
    foreach ($hijas as $hija) {
        if (!in_array($hija['id'], $ids)) {
            $ids[] = $hija['id'];
        }
    }
    
    return $ids;
}

// Función para obtener subordinados permitidos
function getSubordinadosPermitidos($db, $usuario) {
    $nivel_acceso = $usuario['nivel_acceso'] ?? 'solo_propio';
    $puede_ver_todas = ($usuario['nivel_acceso'] ?? 'solo_propio') === 'todas';
    $subordinado_id = $usuario['subordinado_id'] ?? null;
    
    // Administrador o nivel 'todas' puede ver todos los subordinados
    if ($puede_ver_todas || $nivel_acceso === 'todas') {
        $stmt = $db->query("SELECT id FROM catalogos WHERE tipo = 'oficinas' AND activo = 1");
        return array_column($stmt->fetchAll(), 'id');
    }
    
    // Nivel 'solo_subordinado' - solo su subordinado específico
    if ($nivel_acceso === 'solo_subordinado' && $subordinado_id) {
        return [$subordinado_id];
    }
    
    // Nivel 'solo_propio' o 'delegacion' - basado en dependencia
    $dependenciasPermitidas = getDependenciasPermitidas($db, $usuario);
    if (empty($dependenciasPermitidas)) {
        return [];
    }
    
    $placeholders = implode(',', array_fill(0, count($dependenciasPermitidas), '?'));
    $stmt = $db->prepare("SELECT id FROM catalogos WHERE tipo = 'oficinas' AND dependencia_id IN ($placeholders) AND activo = 1");
    $stmt->execute($dependenciasPermitidas);
    return array_column($stmt->fetchAll(), 'id');
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