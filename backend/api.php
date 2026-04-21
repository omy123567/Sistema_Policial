<?php
// ==================== CONFIGURACIÓN INICIAL ====================
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

// ==================== FUNCIÓN PARA OBTENER DEPENDENCIAS PERMITIDAS ====================
function getDependenciasPermitidas($db, $usuario) {
    if (!$usuario || !isset($usuario['user_id'])) return [];
    
    // Si es admin o puede ver todas
    if (isset($usuario['puede_ver_todas']) && $usuario['puede_ver_todas']) {
        $stmt = $db->query("SELECT id FROM dependencias WHERE activo = 1");
        return array_column($stmt->fetchAll(), 'id');
    }
    
    $dependencia_id = $usuario['dependencia_id'] ?? null;
    if (!$dependencia_id) return [];
    
    // Si nivel de acceso es 'delegacion', incluir todas las hijas
    if (isset($usuario['nivel_acceso']) && $usuario['nivel_acceso'] == 'delegacion') {
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
        return array_column($stmt->fetchAll(), 'id');
    }
    
    return [$dependencia_id];
}

// ==================== OBTENER USUARIO AUTENTICADO ====================
$authUser = null;
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if ($token) {
    $authUser = verifyJWT($token);
}

$endpoint = $_GET['endpoint'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

// Endpoints públicos
$publicEndpoints = ['test', 'login'];
if (!in_array($endpoint, $publicEndpoints) && !$authUser) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    $db = getDB();
    
    // Obtener dependencias permitidas para el usuario
    $dependenciasPermitidas = $authUser ? getDependenciasPermitidas($db, $authUser) : [];
    
    switch($endpoint) {
        // ==================== TEST ====================
        case 'test':
            echo json_encode(['success' => true, 'message' => 'API funcionando correctamente']);
            break;
        
        // ==================== DEPENDENCIAS ====================
        case 'dependencias':
            if ($method == 'GET') {
                $sql = "SELECT d.*, p.nombre as padre_nombre FROM dependencias d 
                        LEFT JOIN dependencias p ON d.padre_id = p.id 
                        WHERE d.activo = 1";
                if (!empty($dependenciasPermitidas)) {
                    $placeholders = implode(',', array_fill(0, count($dependenciasPermitidas), '?'));
                    $sql .= " AND d.id IN ($placeholders)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($dependenciasPermitidas);
                } else {
                    $stmt = $db->prepare($sql);
                    $stmt->execute();
                }
                echo json_encode($stmt->fetchAll());
            }
            break;
        
        // ==================== CATÁLOGOS ====================
        case 'catalogos':
            $tipo = $_GET['tipo'] ?? '';
            if ($method == 'GET') {
                if ($tipo) {
                    $stmt = $db->prepare("SELECT * FROM catalogos WHERE tipo = ? AND activo = 1 ORDER BY orden, valor");
                    $stmt->execute([$tipo]);
                    echo json_encode($stmt->fetchAll());
                } else {
                    $stmt = $db->query("SELECT DISTINCT tipo FROM catalogos ORDER BY tipo");
                    echo json_encode($stmt->fetchAll());
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("INSERT INTO catalogos (tipo, valor, orden) VALUES (?, ?, ?)");
                $stmt->execute([$data['tipo'], $data['valor'], $data['orden'] ?? 0]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("UPDATE catalogos SET valor = ?, orden = ? WHERE id = ?");
                $stmt->execute([$data['valor'], $data['orden'] ?? 0, $id]);
                echo json_encode(['success' => true]);
            } elseif ($method == 'DELETE') {
                $stmt = $db->prepare("DELETE FROM catalogos WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== PERSONAL ====================
        case 'personal':
            if ($method == 'GET') {
                $sql = "SELECT p.*, d.nombre as dependencia_nombre 
                        FROM personal p 
                        LEFT JOIN dependencias d ON p.dependencia_id = d.id 
                        WHERE 1=1";
                $params = [];
                
                if (!empty($dependenciasPermitidas)) {
                    $placeholders = implode(',', array_fill(0, count($dependenciasPermitidas), '?'));
                    $sql .= " AND p.dependencia_id IN ($placeholders)";
                    $params = $dependenciasPermitidas;
                }
                
                if ($id) {
                    $sql .= " AND p.id = ?";
                    $params[] = $id;
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    echo json_encode($stmt->fetch());
                } else {
                    if (isset($_GET['search']) && $_GET['search']) {
                        $search = "%{$_GET['search']}%";
                        $sql .= " AND (p.legajo LIKE ? OR p.apellido LIKE ? OR p.nombre LIKE ? OR p.dni LIKE ?)";
                        $params = array_merge($params, [$search, $search, $search, $search]);
                    }
                    $sql .= " ORDER BY p.apellido, p.nombre";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    echo json_encode($stmt->fetchAll());
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $dependencia_id = $data['dependencia_id'] ?? ($authUser['dependencia_id'] ?? null);
                
                $stmt = $db->prepare("INSERT INTO personal (legajo, jerarquia, apellido, nombre, dni, sexo, oficina, fecha_nacimiento, tiene_arma, arma_marca, arma_modelo, arma_serie, sin_arma_justificacion, nro_credencial, nro_licencia_conducir, fecha_vencimiento_licencia, obra_social, nro_afiliado, telefono, email, direccion, dependencia_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['legajo'], $data['jerarquia'], $data['apellido'], $data['nombre'],
                    $data['dni'], $data['sexo'], $data['oficina'], $data['fecha_nacimiento'],
                    isset($data['tiene_arma']) ? (int)$data['tiene_arma'] : 0,
                    $data['arma_marca'] ?? null, $data['arma_modelo'] ?? null, $data['arma_serie'] ?? null,
                    $data['sin_arma_justificacion'] ?? null, $data['nro_credencial'] ?? null,
                    $data['nro_licencia_conducir'] ?? null, $data['fecha_vencimiento_licencia'] ?? null,
                    $data['obra_social'] ?? null, $data['nro_afiliado'] ?? null,
                    $data['telefono'] ?? null, $data['email'] ?? null, $data['direccion'] ?? null,
                    $dependencia_id
                ]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("UPDATE personal SET legajo=?, jerarquia=?, apellido=?, nombre=?, dni=?, sexo=?, oficina=?, fecha_nacimiento=?, tiene_arma=?, arma_marca=?, arma_modelo=?, arma_serie=?, sin_arma_justificacion=?, nro_credencial=?, nro_licencia_conducir=?, fecha_vencimiento_licencia=?, obra_social=?, nro_afiliado=?, telefono=?, email=?, direccion=?, dependencia_id=? WHERE id=?");
                $stmt->execute([
                    $data['legajo'], $data['jerarquia'], $data['apellido'], $data['nombre'],
                    $data['dni'], $data['sexo'], $data['oficina'], $data['fecha_nacimiento'],
                    isset($data['tiene_arma']) ? (int)$data['tiene_arma'] : 0,
                    $data['arma_marca'] ?? null, $data['arma_modelo'] ?? null, $data['arma_serie'] ?? null,
                    $data['sin_arma_justificacion'] ?? null, $data['nro_credencial'] ?? null,
                    $data['nro_licencia_conducir'] ?? null, $data['fecha_vencimiento_licencia'] ?? null,
                    $data['obra_social'] ?? null, $data['nro_afiliado'] ?? null,
                    $data['telefono'] ?? null, $data['email'] ?? null, $data['direccion'] ?? null,
                    $data['dependencia_id'] ?? ($authUser['dependencia_id'] ?? null), $id
                ]);
                echo json_encode(['success' => true]);
            } elseif ($method == 'DELETE') {
                $stmt = $db->prepare("DELETE FROM personal WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== RECARGOS ====================
        case 'recargos':
            if ($method == 'GET') {
                $sql = "SELECT r.*, p.apellido, p.nombre, p.legajo, d.nombre as dependencia_nombre 
                        FROM recargos r 
                        LEFT JOIN personal p ON r.personal_id = p.id 
                        LEFT JOIN dependencias d ON r.dependencia_id = d.id
                        WHERE 1=1";
                $params = [];
                
                if (!empty($dependenciasPermitidas)) {
                    $placeholders = implode(',', array_fill(0, count($dependenciasPermitidas), '?'));
                    $sql .= " AND r.dependencia_id IN ($placeholders)";
                    $params = $dependenciasPermitidas;
                }
                
                if ($id) {
                    $sql .= " AND r.id = ?";
                    $params[] = $id;
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    echo json_encode($stmt->fetch());
                } else {
                    if (isset($_GET['fecha_desde']) && $_GET['fecha_desde']) {
                        $sql .= " AND r.fecha >= ?";
                        $params[] = $_GET['fecha_desde'];
                    }
                    if (isset($_GET['fecha_hasta']) && $_GET['fecha_hasta']) {
                        $sql .= " AND r.fecha <= ?";
                        $params[] = $_GET['fecha_hasta'];
                    }
                    if (isset($_GET['tipo_recargo']) && $_GET['tipo_recargo']) {
                        $sql .= " AND r.tipo_recargo = ?";
                        $params[] = $_GET['tipo_recargo'];
                    }
                    $sql .= " ORDER BY r.fecha DESC, r.hora DESC";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    echo json_encode($stmt->fetchAll());
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $dependencia_id = $data['dependencia_id'] ?? ($authUser['dependencia_id'] ?? null);
                
                $stmt = $db->prepare("INSERT INTO recargos (fecha, hora, tipo_recargo, oficina, personal_id, observaciones, dependencia_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$data['fecha'], $data['hora'], $data['tipo_recargo'], $data['oficina'], $data['personal_id'], $data['observaciones'] ?? null, $dependencia_id]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'DELETE') {
                $stmt = $db->prepare("DELETE FROM recargos WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== EXPEDIENTES ====================
        case 'expedientes':
            if ($method == 'GET') {
                $sql = "SELECT e.*, d.nombre as dependencia_nombre 
                        FROM expedientes e 
                        LEFT JOIN dependencias d ON e.dependencia_id = d.id 
                        WHERE 1=1";
                $params = [];
                
                if (!empty($dependenciasPermitidas)) {
                    $placeholders = implode(',', array_fill(0, count($dependenciasPermitidas), '?'));
                    $sql .= " AND e.dependencia_id IN ($placeholders)";
                    $params = $dependenciasPermitidas;
                }
                
                if ($id) {
                    $sql .= " AND e.id = ?";
                    $params[] = $id;
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    echo json_encode($stmt->fetch());
                } else {
                    if (isset($_GET['nro_expediente']) && $_GET['nro_expediente']) {
                        $sql .= " AND e.nro_expediente LIKE ?";
                        $params[] = "%{$_GET['nro_expediente']}%";
                    }
                    if (isset($_GET['fecha_desde']) && $_GET['fecha_desde']) {
                        $sql .= " AND e.fecha >= ?";
                        $params[] = $_GET['fecha_desde'];
                    }
                    if (isset($_GET['fecha_hasta']) && $_GET['fecha_hasta']) {
                        $sql .= " AND e.fecha <= ?";
                        $params[] = $_GET['fecha_hasta'];
                    }
                    if (isset($_GET['tipo_oficio']) && $_GET['tipo_oficio']) {
                        $sql .= " AND e.tipo_oficio = ?";
                        $params[] = $_GET['tipo_oficio'];
                    }
                    if (isset($_GET['estado']) && $_GET['estado']) {
                        $sql .= " AND e.estado = ?";
                        $params[] = $_GET['estado'];
                    }
                    $sql .= " ORDER BY e.fecha DESC";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    echo json_encode($stmt->fetchAll());
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $dependencia_id = $data['dependencia_id'] ?? ($authUser['dependencia_id'] ?? null);
                
                $stmt = $db->prepare("INSERT INTO expedientes (nro_expediente, fecha, tipo_oficio, juzgado_origen, dependencia, tipo_requerimiento, resumen, nro_informe_tecnico, dependencia_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['nro_expediente'], $data['fecha'], $data['tipo_oficio'],
                    $data['juzgado_origen'], $data['dependencia'] ?? null, $data['tipo_requerimiento'],
                    $data['resumen'] ?? null, $data['nro_informe_tecnico'] ?? null, $dependencia_id
                ]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'DELETE') {
                $stmt = $db->prepare("DELETE FROM expedientes WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== LICENCIAS ====================
        case 'licencias':
            if ($method == 'GET') {
                $sql = "SELECT l.*, p.apellido, p.nombre, p.legajo, d.nombre as dependencia_nombre 
                        FROM licencias l 
                        LEFT JOIN personal p ON l.agente_id = p.id 
                        LEFT JOIN dependencias d ON l.dependencia_id = d.id
                        WHERE 1=1";
                $params = [];
                
                if (!empty($dependenciasPermitidas)) {
                    $placeholders = implode(',', array_fill(0, count($dependenciasPermitidas), '?'));
                    $sql .= " AND l.dependencia_id IN ($placeholders)";
                    $params = $dependenciasPermitidas;
                }
                
                if ($id) {
                    $sql .= " AND l.id = ?";
                    $params[] = $id;
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    echo json_encode($stmt->fetch());
                } else {
                    if (isset($_GET['fecha_desde']) && $_GET['fecha_desde']) {
                        $sql .= " AND l.fecha_inicio >= ?";
                        $params[] = $_GET['fecha_desde'];
                    }
                    if (isset($_GET['fecha_hasta']) && $_GET['fecha_hasta']) {
                        $sql .= " AND l.fecha_inicio <= ?";
                        $params[] = $_GET['fecha_hasta'];
                    }
                    if (isset($_GET['tipo_licencia']) && $_GET['tipo_licencia']) {
                        $sql .= " AND l.tipo_licencia = ?";
                        $params[] = $_GET['tipo_licencia'];
                    }
                    if (isset($_GET['estado']) && $_GET['estado']) {
                        $sql .= " AND l.estado = ?";
                        $params[] = $_GET['estado'];
                    }
                    $sql .= " ORDER BY l.fecha_inicio DESC";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    echo json_encode($stmt->fetchAll());
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $dependencia_id = $data['dependencia_id'] ?? ($authUser['dependencia_id'] ?? null);
                
                $stmt = $db->prepare("INSERT INTO licencias (agente_id, tipo_licencia, estado, fecha_inicio, dias_habiles, dias_viaje, contar_fines_semana, fecha_fin, observaciones, dependencia_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$data['agente_id'], $data['tipo_licencia'], $data['estado'], $data['fecha_inicio'], $data['dias_habiles'], $data['dias_viaje'], $data['contar_fines_semana'], $data['fecha_fin'], $data['observaciones'] ?? null, $dependencia_id]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'DELETE') {
                $stmt = $db->prepare("DELETE FROM licencias WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== USUARIOS ====================
        case 'usuarios':
            if ($method == 'GET') {
                if ($id) {
                    $stmt = $db->prepare("SELECT u.id, u.nombre_completo, u.username, u.email, u.rol_id, u.estado, r.nombre as rol_nombre FROM usuarios u LEFT JOIN roles r ON u.rol_id = r.id WHERE u.id = ?");
                    $stmt->execute([$id]);
                    $user = $stmt->fetch();
                    unset($user['password']);
                    echo json_encode($user);
                } else {
                    $stmt = $db->query("SELECT u.id, u.nombre_completo, u.username, u.email, u.rol_id, u.estado, r.nombre as rol_nombre FROM usuarios u LEFT JOIN roles r ON u.rol_id = r.id");
                    $users = $stmt->fetchAll();
                    foreach($users as &$user) {
                        unset($user['password']);
                    }
                    echo json_encode($users);
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO usuarios (nombre_completo, username, email, password, rol_id, estado) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$data['nombre_completo'], $data['username'], $data['email'], $hashedPassword, $data['rol_id'], $data['estado']]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'DELETE') {
                $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== FERIADOS ====================
        case 'feriados':
            if ($method == 'GET') {
                $stmt = $db->query("SELECT * FROM feriados ORDER BY fecha");
                echo json_encode($stmt->fetchAll());
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("INSERT INTO feriados (fecha, motivo, tipo) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE motivo = VALUES(motivo), tipo = VALUES(tipo)");
                $stmt->execute([$data['fecha'], $data['motivo'], $data['tipo']]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'DELETE') {
                $stmt = $db->prepare("DELETE FROM feriados WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== CONFIGURACIÓN ====================
        case 'configuracion':
            if ($method == 'GET') {
                $stmt = $db->query("SELECT * FROM configuracion");
                $config = [];
                while($row = $stmt->fetch()) {
                    $config[$row['clave']] = $row['valor'];
                }
                echo json_encode($config);
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("INSERT INTO configuracion (clave, valor, tipo) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor), tipo = VALUES(tipo)");
                $stmt->execute([$data['clave'], $data['valor'], $data['tipo'] ?? 'text']);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== DASHBOARD ====================
case 'dashboard':
    $stats = [];
    
    // Total personal
    $sql = "SELECT COUNT(*) as total FROM personal";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['total_personal'] = $stmt->fetch()['total'];
    
    // Jerarquías
    $sql = "SELECT jerarquia, COUNT(*) as cantidad FROM personal WHERE jerarquia IS NOT NULL AND jerarquia != '' GROUP BY jerarquia ORDER BY cantidad DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['jerarquias'] = $stmt->fetchAll();
    
    // Últimos recargos
    $sql = "SELECT r.*, p.apellido, p.nombre FROM recargos r LEFT JOIN personal p ON r.personal_id = p.id ORDER BY r.created_at DESC LIMIT 5";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['ultimos_recargos'] = $stmt->fetchAll();
    
    // Expedientes recientes
    $sql = "SELECT * FROM expedientes ORDER BY created_at DESC LIMIT 5";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['expedientes_recientes'] = $stmt->fetchAll();
    
    // Licencias activas
    $sql = "SELECT l.*, p.apellido, p.nombre FROM licencias l LEFT JOIN personal p ON l.agente_id = p.id WHERE l.estado IN ('Pendiente', 'Aprobada') ORDER BY l.fecha_inicio LIMIT 5";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['licencias_activas'] = $stmt->fetchAll();
    
    echo json_encode($stats);
    break;

    // ==================== CONFIGURACIÓN DEL DASHBOARD ====================
case 'dashboard_config':
    $usuario_id = $authUser['user_id'];
    
    if ($method == 'GET') {
        $stmt = $db->prepare("SELECT widgets FROM dashboard_config WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $config = $stmt->fetch();
        
        if (!$config) {
            // Widgets por defecto
            $defaultWidgets = ['stats', 'jerarquias', 'recargos', 'expedientes', 'licencias'];
            echo json_encode($defaultWidgets);
        } else {
            echo json_encode(json_decode($config['widgets'], true));
        }
    } elseif ($method == 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $widgets = json_encode($data['widgets']);
        
        $stmt = $db->prepare("INSERT INTO dashboard_config (usuario_id, widgets) VALUES (?, ?) ON DUPLICATE KEY UPDATE widgets = VALUES(widgets)");
        $stmt->execute([$usuario_id, $widgets]);
        
        echo json_encode(['success' => true]);
    }
    break;

// ==================== ESTADÍSTICAS POR TIPO ====================
case 'dashboard_stats':
    $tipo = $_GET['tipo'] ?? '';
    $fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-d', strtotime('-30 days'));
    $fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
    
    if ($tipo == 'tipos_requerimiento') {
        $stmt = $db->prepare("SELECT tipo_requerimiento as nombre, COUNT(*) as cantidad FROM expedientes WHERE fecha BETWEEN ? AND ? GROUP BY tipo_requerimiento");
        $stmt->execute([$fecha_desde, $fecha_hasta]);
        echo json_encode($stmt->fetchAll());
    } elseif ($tipo == 'juzgados') {
        $stmt = $db->prepare("SELECT juzgado_origen as nombre, COUNT(*) as cantidad FROM expedientes WHERE fecha BETWEEN ? AND ? AND juzgado_origen IS NOT NULL GROUP BY juzgado_origen");
        $stmt->execute([$fecha_desde, $fecha_hasta]);
        echo json_encode($stmt->fetchAll());
    } elseif ($tipo == 'dependencias') {
        $stmt = $db->prepare("SELECT dependencia as nombre, COUNT(*) as cantidad FROM expedientes WHERE fecha BETWEEN ? AND ? AND dependencia IS NOT NULL GROUP BY dependencia");
        $stmt->execute([$fecha_desde, $fecha_hasta]);
        echo json_encode($stmt->fetchAll());
    } elseif ($tipo == 'cumpleanos') {
        $mes = $_GET['mes'] ?? date('m');
        $stmt = $db->prepare("SELECT id, apellido, nombre, legajo, fecha_nacimiento FROM personal WHERE MONTH(fecha_nacimiento) = ? ORDER BY DAY(fecha_nacimiento)");
        $stmt->execute([$mes]);
        echo json_encode($stmt->fetchAll());
    } else {
        echo json_encode([]);
    }
    break;
    
        // ==================== DEFAULT ====================
        default:
            echo json_encode(['error' => 'Endpoint no encontrado: ' . $endpoint]);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch(Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>