<?php
// ==================== CONFIGURACIÓN INICIAL ====================
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config.php';

// ==================== FUNCIONES PARA FILTRO DE SUBORDINADO ====================

function getSubordinadoFilter($authUser, $tabla, $campo = 'oficina') {
    if (!$authUser) return '';
    
    $puede_ver_todas = $authUser['puede_ver_todas'] ?? false;
    $nivel_acceso = $authUser['nivel_acceso'] ?? 'solo_propio';
    $subordinado_valor = $authUser['subordinado_valor'] ?? null;
    
    // ADMIN o nivel 'todas' - sin filtro
    if ($puede_ver_todas || $nivel_acceso === 'todas') {
        return '';
    }
    
    // Usuario con subordinado específico
    if ($subordinado_valor) {
        return " AND $tabla.$campo = '" . addslashes($subordinado_valor) . "'";
    }
    
    // Usuario sin subordinado - no ver nada
    return " AND 1=0";
}

function getSubordinadoFilterSecciones($authUser) {
    if (!$authUser) return '';
    
    $puede_ver_todas = $authUser['puede_ver_todas'] ?? false;
    $nivel_acceso = $authUser['nivel_acceso'] ?? 'solo_propio';
    $subordinado_id = $authUser['subordinado_id'] ?? null;
    
    if ($puede_ver_todas || $nivel_acceso === 'todas') {
        return '';
    }
    
    if ($subordinado_id) {
        return " AND s.subordinado_id = " . intval($subordinado_id);
    }
    
    return " AND 1=0";
}

// ==================== OBTENER USUARIO AUTENTICADO ====================
$authUser = null;
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if ($token) {
    $authUser = verifyJWT($token);
    // Asegurar que subordinado_valor esté presente (por si acaso)
    if ($authUser && isset($authUser['subordinado_id']) && $authUser['subordinado_id'] && !isset($authUser['subordinado_valor'])) {
        try {
            $dbTemp = getDB();
            $stmt = $dbTemp->prepare("SELECT valor FROM catalogos WHERE id = ? AND tipo = 'oficinas' AND activo = 1");
            $stmt->execute([$authUser['subordinado_id']]);
            $sub = $stmt->fetch();
            if ($sub) {
                $authUser['subordinado_valor'] = $sub['valor'];
            }
        } catch(Exception $e) {}
    }
}

$endpoint = $_GET['endpoint'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

$publicEndpoints = ['test', 'login', 'feriados'];
if (!in_array($endpoint, $publicEndpoints) && !$authUser) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    $db = getDB();
    
    switch($endpoint) {
        
        // ==================== JUZGADOS ====================
        case 'juzgados':
            if ($method == 'GET') {
                if ($id) {
                    $stmt = $db->prepare("SELECT * FROM juzgados WHERE id = ?");
                    $stmt->execute([$id]);
                    echo json_encode($stmt->fetch());
                } else {
                    $stmt = $db->query("SELECT * FROM juzgados WHERE activo = 1 ORDER BY nombre");
                    echo json_encode($stmt->fetchAll());
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("INSERT INTO juzgados (nombre, direccion, telefono, email, contacto) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['nombre'], 
                    $data['direccion'] ?? null, 
                    $data['telefono'] ?? null, 
                    $data['email'] ?? null, 
                    $data['contacto'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("UPDATE juzgados SET nombre=?, direccion=?, telefono=?, email=?, contacto=? WHERE id=?");
                $stmt->execute([
                    $data['nombre'], 
                    $data['direccion'] ?? null, 
                    $data['telefono'] ?? null, 
                    $data['email'] ?? null, 
                    $data['contacto'] ?? null, 
                    $id
                ]);
                echo json_encode(['success' => true]);
            } elseif ($method == 'DELETE') {
                $stmt = $db->prepare("DELETE FROM juzgados WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== CONFIGURACIÓN DEL SISTEMA ====================
        case 'configuracion_sistema':
            if ($method == 'GET') {
                $stmt = $db->query("SELECT clave, valor, tipo FROM configuracion");
                $config = [];
                while ($row = $stmt->fetch()) {
                    $config[$row['clave']] = $row['valor'];
                }
                if (!isset($config['nombre_sistema'])) $config['nombre_sistema'] = 'Sistema de Gestión Policial';
                if (!isset($config['logo_url'])) $config['logo_url'] = '';
                echo json_encode($config);
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                foreach ($data as $clave => $valor) {
                    $stmt = $db->prepare("INSERT INTO configuracion (clave, valor, tipo) VALUES (?, ?, 'text') ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
                    $stmt->execute([$clave, $valor]);
                }
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== SUBIR LOGO ====================
        case 'subir_logo':
            if ($method == 'POST') {
                if (!isset($_FILES['logo'])) {
                    echo json_encode(['error' => 'No se recibió archivo']);
                    break;
                }
                
                $archivo = $_FILES['logo'];
                $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                $tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($extension, $tiposPermitidos)) {
                    echo json_encode(['error' => 'Tipo de archivo no permitido. Use JPG, PNG, GIF o WEBP']);
                    break;
                }
                
                $uploadDir = __DIR__ . '/../uploads/logo/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $nombreArchivo = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
                $rutaCompleta = $uploadDir . $nombreArchivo;
                $rutaRelativa = 'uploads/logo/' . $nombreArchivo;
                
                if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                    $stmt = $db->prepare("INSERT INTO configuracion (clave, valor, tipo) VALUES ('logo_url', ?, 'image') ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
                    $stmt->execute([$rutaRelativa]);
                    echo json_encode(['success' => true, 'logo_url' => $rutaRelativa]);
                } else {
                    echo json_encode(['error' => 'Error al subir archivo']);
                }
            }
            break;
        
        // ==================== ELIMINAR LOGO ====================
        case 'eliminar_logo':
            if ($method == 'DELETE') {
                $stmt = $db->query("SELECT valor FROM configuracion WHERE clave = 'logo_url'");
                $logo = $stmt->fetch();
                
                if ($logo && $logo['valor']) {
                    $rutaCompleta = __DIR__ . '/../' . $logo['valor'];
                    if (file_exists($rutaCompleta)) {
                        unlink($rutaCompleta);
                    }
                }
                
                $stmt = $db->prepare("UPDATE configuracion SET valor = '' WHERE clave = 'logo_url'");
                $stmt->execute();
                
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== TEST ====================
        case 'test':
            echo json_encode(['success' => true, 'message' => 'API funcionando correctamente']);
            break;
        
        // ==================== LOGIN ====================
        case 'login':
            // Este endpoint se maneja en auth.php, pero lo dejamos para compatibilidad
            echo json_encode(['error' => 'Use /backend/auth.php para login']);
            break;
        
        // ==================== DEPENDENCIAS ====================
        case 'dependencias':
            if ($method == 'GET') {
                $sql = "SELECT d.*, p.nombre as padre_nombre FROM dependencias d 
                        LEFT JOIN dependencias p ON d.padre_id = p.id 
                        WHERE d.activo = 1 ORDER BY d.id";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                echo json_encode($stmt->fetchAll());
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("INSERT INTO dependencias (nombre, nivel, padre_id, codigo, direccion, telefono, email, activo) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([
                    $data['nombre'],
                    $data['nivel'] ?? 'seccion',
                    $data['padre_id'] ?? null,
                    $data['codigo'] ?? null,
                    $data['direccion'] ?? null,
                    $data['telefono'] ?? null,
                    $data['email'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("UPDATE dependencias SET nombre=?, nivel=?, padre_id=?, codigo=?, direccion=?, telefono=?, email=? WHERE id=?");
                $stmt->execute([
                    $data['nombre'],
                    $data['nivel'] ?? 'seccion',
                    $data['padre_id'] ?? null,
                    $data['codigo'] ?? null,
                    $data['direccion'] ?? null,
                    $data['telefono'] ?? null,
                    $data['email'] ?? null,
                    $id
                ]);
                echo json_encode(['success' => true]);
            } elseif ($method == 'DELETE') {
                function eliminarDependenciaRecursiva($db, $id) {
                    $stmt = $db->prepare("SELECT id FROM dependencias WHERE padre_id = ?");
                    $stmt->execute([$id]);
                    $hijas = $stmt->fetchAll();
                    
                    foreach ($hijas as $hija) {
                        eliminarDependenciaRecursiva($db, $hija['id']);
                    }
                    
                    $stmt = $db->prepare("UPDATE personal SET dependencia_id = NULL WHERE dependencia_id = ?");
                    $stmt->execute([$id]);
                    
                    $stmt = $db->prepare("DELETE FROM dependencias WHERE id = ?");
                    $stmt->execute([$id]);
                }
                
                try {
                    eliminarDependenciaRecursiva($db, $id);
                    echo json_encode(['success' => true, 'message' => 'Dependencia y sus hijas eliminadas correctamente']);
                } catch (Exception $e) {
                    echo json_encode(['error' => 'Error al eliminar: ' . $e->getMessage()]);
                }
            }
            break;
        
        // ==================== CATÁLOGOS ====================
        case 'catalogos':
            $tipo = $_GET['tipo'] ?? '';
            if ($method == 'GET') {
                if ($tipo) {
                    $sql = "SELECT c.id, c.valor, c.orden, c.dependencia_id, d.nombre as dependencia_nombre 
                            FROM catalogos c 
                            LEFT JOIN dependencias d ON c.dependencia_id = d.id 
                            WHERE c.tipo = ? AND c.activo = 1 
                            ORDER BY c.orden, c.valor";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$tipo]);
                    $result = $stmt->fetchAll();
                    echo json_encode($result ?: []);
                } else {
                    $stmt = $db->query("SELECT DISTINCT tipo FROM catalogos ORDER BY tipo");
                    echo json_encode($stmt->fetchAll());
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $dependencia_id = $data['dependencia_id'] ?? null;
                $stmt = $db->prepare("INSERT INTO catalogos (tipo, valor, orden, dependencia_id, activo) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute([$data['tipo'], $data['valor'], $data['orden'] ?? 0, $dependencia_id]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                $dependencia_id = $data['dependencia_id'] ?? null;
                $stmt = $db->prepare("UPDATE catalogos SET valor = ?, orden = ?, dependencia_id = ? WHERE id = ?");
                $stmt->execute([$data['valor'], $data['orden'] ?? 0, $dependencia_id, $id]);
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
                $filtro_subordinado = getSubordinadoFilter($authUser, 'p', 'oficina');
                
                // Permitir filtro manual para admin
                $subordinado_filter = $_GET['subordinado'] ?? '';
                $subordinado_sql = '';
                if (!empty($subordinado_filter) && ($authUser['puede_ver_todas'] ?? false)) {
                    $subordinado_sql = " AND p.oficina = '" . addslashes($subordinado_filter) . "'";
                }
                
                if ($id) {
                    $sql = "SELECT p.*, d.nombre as dependencia_nombre, s.nombre as seccion_guardia_nombre
                            FROM personal p 
                            LEFT JOIN dependencias d ON p.dependencia_id = d.id 
                            LEFT JOIN secciones_guardia s ON p.seccion_guardia_id = s.id
                            WHERE p.id = ? $filtro_subordinado $subordinado_sql";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$id]);
                    echo json_encode($stmt->fetch());
                } else {
                    $sql = "SELECT p.*, d.nombre as dependencia_nombre, s.nombre as seccion_guardia_nombre
                            FROM personal p 
                            LEFT JOIN dependencias d ON p.dependencia_id = d.id 
                            LEFT JOIN secciones_guardia s ON p.seccion_guardia_id = s.id
                            WHERE 1=1 $filtro_subordinado $subordinado_sql
                            ORDER BY p.apellido, p.nombre";
                    $stmt = $db->prepare($sql);
                    $stmt->execute();
                    echo json_encode($stmt->fetchAll());
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $dependencia_id = $data['dependencia_id'] ?? ($authUser['dependencia_id'] ?? null);
                $seccion_guardia_id = $data['seccion_guardia_id'] ?? null;
                $oficina = $data['oficina'] ?? null;
                
                // FORZAR el subordinado del usuario si no es admin
                if (!($authUser['puede_ver_todas'] ?? false) && $authUser['subordinado_valor']) {
                    $oficina = $authUser['subordinado_valor'];
                }
                
                if (empty($data['legajo']) || empty($data['apellido']) || empty($data['nombre']) || empty($data['dni'])) {
                    echo json_encode(['error' => 'Faltan campos requeridos: legajo, apellido, nombre, dni son obligatorios']);
                    break;
                }
                
                $check = $db->prepare("SELECT id FROM personal WHERE legajo = ?");
                $check->execute([$data['legajo']]);
                if ($check->fetch()) {
                    echo json_encode(['error' => 'El legajo ya existe']);
                    break;
                }
                
                $check = $db->prepare("SELECT id FROM personal WHERE dni = ?");
                $check->execute([$data['dni']]);
                if ($check->fetch()) {
                    echo json_encode(['error' => 'El DNI ya existe']);
                    break;
                }
                
                $stmt = $db->prepare("INSERT INTO personal (
                    legajo, jerarquia, apellido, nombre, dni, sexo, oficina, fecha_nacimiento, 
                    tiene_arma, sin_arma_motivo,
                    nro_licencia_conducir, licencia_categoria, fecha_vencimiento_licencia, es_chofer,
                    obra_social, obra_social_numero, telefono, email, direccion, dependencia_id, seccion_guardia_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $result = $stmt->execute([
                    $data['legajo'], 
                    $data['jerarquia'] ?? null, 
                    $data['apellido'], 
                    $data['nombre'],
                    $data['dni'], 
                    $data['sexo'] ?? null, 
                    $oficina, 
                    $data['fecha_nacimiento'] ?? null,
                    isset($data['tiene_arma']) ? (int)$data['tiene_arma'] : 0,
                    $data['sin_arma_motivo'] ?? null,
                    $data['nro_licencia_conducir'] ?? null,
                    $data['licencia_categoria'] ?? null, 
                    $data['fecha_vencimiento_licencia'] ?? null,
                    isset($data['es_chofer']) ? (int)$data['es_chofer'] : 0,
                    $data['obra_social'] ?? null, 
                    $data['obra_social_numero'] ?? null,
                    $data['telefono'] ?? null,
                    $data['email'] ?? null, 
                    $data['direccion'] ?? null, 
                    $dependencia_id, 
                    $seccion_guardia_id
                ]);
                
                if ($result) {
                    echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
                } else {
                    echo json_encode(['error' => 'Error al insertar personal']);
                }
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $fields = [];
                $params = [];
                
                $allowedFields = [
                    'legajo', 'jerarquia', 'apellido', 'nombre', 'dni', 'sexo', 'oficina', 'fecha_nacimiento',
                    'tiene_arma', 'sin_arma_motivo',
                    'nro_licencia_conducir', 'licencia_categoria', 'fecha_vencimiento_licencia', 
                    'es_chofer', 'obra_social', 'obra_social_numero', 'telefono', 'email', 'direccion', 
                    'dependencia_id', 'seccion_guardia_id'
                ];
                
                foreach ($allowedFields as $field) {
                    if (array_key_exists($field, $data)) {
                        $fields[] = "$field = ?";
                        $params[] = $data[$field];
                    }
                }
                
                if (empty($fields)) {
                    echo json_encode(['error' => 'No hay campos para actualizar']);
                    break;
                }
                
                $params[] = $id;
                $sql = "UPDATE personal SET " . implode(', ', $fields) . " WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                
                echo json_encode(['success' => true]);
            } elseif ($method == 'DELETE') {
                $stmt = $db->prepare("DELETE FROM personal WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== DOCUMENTOS DEL PERSONAL ====================
        case 'personal_documentos':
            if ($method == 'GET') {
                $personal_id = $_GET['personal_id'] ?? 0;
                if ($personal_id) {
                    $stmt = $db->prepare("SELECT * FROM personal_documentos WHERE personal_id = ? ORDER BY created_at DESC");
                    $stmt->execute([$personal_id]);
                    echo json_encode($stmt->fetchAll());
                } else {
                    echo json_encode([]);
                }
            } elseif ($method == 'POST') {
                $personal_id = $_POST['personal_id'] ?? 0;
                $titulo = $_POST['titulo'] ?? '';
                $descripcion = $_POST['descripcion'] ?? '';
                
                if (!$personal_id || !isset($_FILES['archivo'])) {
                    echo json_encode(['error' => 'Datos incompletos']);
                    break;
                }
                
                $archivo = $_FILES['archivo'];
                $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
                $tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
                
                if (!in_array(strtolower($extension), $tiposPermitidos)) {
                    echo json_encode(['error' => 'Tipo de archivo no permitido']);
                    break;
                }
                
                $uploadDir = __DIR__ . '/../uploads/personal/' . $personal_id . '/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $nombreArchivo = time() . '_' . uniqid() . '.' . $extension;
                $rutaCompleta = $uploadDir . $nombreArchivo;
                $rutaRelativa = 'uploads/personal/' . $personal_id . '/' . $nombreArchivo;
                
                if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                    $stmt = $db->prepare("INSERT INTO personal_documentos (personal_id, titulo, descripcion, archivo_ruta, tipo_archivo, tamano) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$personal_id, $titulo, $descripcion, $rutaRelativa, $extension, $archivo['size']]);
                    echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
                } else {
                    echo json_encode(['error' => 'Error al subir archivo']);
                }
            } elseif ($method == 'DELETE') {
                $doc_id = $id;
                $stmt = $db->prepare("SELECT archivo_ruta FROM personal_documentos WHERE id = ?");
                $stmt->execute([$doc_id]);
                $doc = $stmt->fetch();
                
                if ($doc) {
                    $rutaCompleta = __DIR__ . '/../' . $doc['archivo_ruta'];
                    if (file_exists($rutaCompleta)) {
                        unlink($rutaCompleta);
                    }
                    $stmt = $db->prepare("DELETE FROM personal_documentos WHERE id = ?");
                    $stmt->execute([$doc_id]);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'Documento no encontrado']);
                }
            }
            break;
        
        // ==================== DOCUMENTOS BASE64 PARA PDF ====================
        case 'personal_documentos_base64':
            $personal_id = $_GET['personal_id'] ?? 0;
            if (!$personal_id) {
                echo json_encode([]);
                break;
            }
            
            $stmt = $db->prepare("SELECT id, titulo, descripcion, archivo_ruta, tipo_archivo FROM personal_documentos WHERE personal_id = ? ORDER BY created_at DESC");
            $stmt->execute([$personal_id]);
            $documentos = $stmt->fetchAll();
            
            $result = [];
            foreach ($documentos as $doc) {
                $rutaCompleta = __DIR__ . '/../' . $doc['archivo_ruta'];
                $base64 = null;
                if (file_exists($rutaCompleta)) {
                    $tipoMime = mime_content_type($rutaCompleta);
                    $contenido = file_get_contents($rutaCompleta);
                    $base64 = 'data:' . $tipoMime . ';base64,' . base64_encode($contenido);
                }
                $result[] = [
                    'id' => $doc['id'],
                    'titulo' => $doc['titulo'],
                    'descripcion' => $doc['descripcion'],
                    'tipo_archivo' => $doc['tipo_archivo'],
                    'base64' => $base64
                ];
            }
            echo json_encode($result);
            break;
        
        // ==================== RECARGOS ====================
        case 'recargos':
            if ($method == 'GET') {
                $filtro_subordinado = getSubordinadoFilter($authUser, 'r', 'oficina');
                
                $subordinado_filter = $_GET['subordinado'] ?? '';
                $subordinado_sql = '';
                if (!empty($subordinado_filter) && ($authUser['puede_ver_todas'] ?? false)) {
                    $subordinado_sql = " AND r.oficina = '" . addslashes($subordinado_filter) . "'";
                }
                
                if ($id) {
                    $sql = "SELECT r.*, p.apellido, p.nombre, p.legajo, s.nombre as seccion_nombre
                            FROM recargos r 
                            LEFT JOIN personal p ON r.personal_id = p.id 
                            LEFT JOIN secciones_guardia s ON r.seccion_guardia_id = s.id
                            WHERE r.id = ? $filtro_subordinado $subordinado_sql";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$id]);
                    echo json_encode($stmt->fetch());
                } else {
                    $sql = "SELECT r.*, p.apellido, p.nombre, p.legajo, s.nombre as seccion_nombre
                            FROM recargos r 
                            LEFT JOIN personal p ON r.personal_id = p.id 
                            LEFT JOIN secciones_guardia s ON r.seccion_guardia_id = s.id
                            WHERE 1=1 $filtro_subordinado $subordinado_sql
                            ORDER BY r.fecha DESC, r.hora DESC";
                    $stmt = $db->prepare($sql);
                    $stmt->execute();
                    echo json_encode($stmt->fetchAll());
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $dependencia_id = $authUser['dependencia_id'] ?? null;
                $personal_id = $data['personal_id'] ?? null;
                $oficina = $data['oficina'] ?? null;
                
                // FORZAR el subordinado del usuario si no es admin
                if (!($authUser['puede_ver_todas'] ?? false) && $authUser['subordinado_valor']) {
                    $oficina = $authUser['subordinado_valor'];
                }
                
                $stmt = $db->prepare("INSERT INTO recargos (fecha, hora, tipo_recargo, oficina, dependencia_id, personal_id, observaciones, seccion_guardia_id, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente')");
                $result = $stmt->execute([
                    $data['fecha'], 
                    $data['hora'], 
                    $data['tipo_recargo'], 
                    $oficina,
                    $dependencia_id, 
                    $personal_id, 
                    $data['observaciones'] ?? null,
                    $data['seccion_guardia_id'] ?? null
                ]);
                
                if ($result) {
                    echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
                } else {
                    echo json_encode(['error' => 'Error al insertar recargo']);
                }
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("UPDATE recargos SET fecha=?, hora=?, tipo_recargo=?, oficina=?, personal_id=?, observaciones=?, seccion_guardia_id=? WHERE id=?");
                $stmt->execute([
                    $data['fecha'], $data['hora'], $data['tipo_recargo'], 
                    $data['oficina'] ?? null,
                    $data['personal_id'], $data['observaciones'] ?? null,
                    $data['seccion_guardia_id'] ?? null, $id
                ]);
                echo json_encode(['success' => true]);
            } elseif ($method == 'DELETE') {
                $stmt = $db->prepare("DELETE FROM recargos WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== EXPEDIENTES ====================
        case 'expedientes':
            if ($method == 'GET') {
                $filtro_subordinado = getSubordinadoFilter($authUser, 'e', 'oficina');
                
                $subordinado_filter = $_GET['subordinado'] ?? '';
                $subordinado_sql = '';
                if (!empty($subordinado_filter) && ($authUser['puede_ver_todas'] ?? false)) {
                    $subordinado_sql = " AND e.oficina = '" . addslashes($subordinado_filter) . "'";
                }
                
                if ($id) {
                    $sql = "SELECT e.*, 
                            p.id as responsable_id,
                            p.apellido as responsable_apellido, 
                            p.nombre as responsable_nombre,
                            p.legajo as responsable_legajo,
                            d.nombre as dependencia_nombre,
                            j.nombre as juzgado_nombre
                            FROM expedientes e 
                            LEFT JOIN personal p ON e.responsable_id = p.id 
                            LEFT JOIN dependencias d ON e.dependencia_id = d.id
                            LEFT JOIN juzgados j ON e.juzgado_origen = j.id
                            WHERE e.id = ? $filtro_subordinado $subordinado_sql";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$id]);
                    echo json_encode($stmt->fetch());
                } else {
                    $sql = "SELECT e.*, 
                            p.id as responsable_id,
                            p.apellido as responsable_apellido, 
                            p.nombre as responsable_nombre,
                            p.legajo as responsable_legajo,
                            d.nombre as dependencia_nombre,
                            j.nombre as juzgado_nombre
                            FROM expedientes e 
                            LEFT JOIN personal p ON e.responsable_id = p.id 
                            LEFT JOIN dependencias d ON e.dependencia_id = d.id
                            LEFT JOIN juzgados j ON e.juzgado_origen = j.id
                            WHERE 1=1 $filtro_subordinado $subordinado_sql
                            ORDER BY e.fecha DESC";
                    $stmt = $db->prepare($sql);
                    $stmt->execute();
                    echo json_encode($stmt->fetchAll());
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $juzgado_origen = $data['juzgado_origen'] ?? null;
                $juzgado_id = null;
                if ($juzgado_origen) {
                    $stmt = $db->prepare("SELECT id FROM juzgados WHERE nombre = ? LIMIT 1");
                    $stmt->execute([$juzgado_origen]);
                    $juzgado = $stmt->fetch();
                    if ($juzgado) {
                        $juzgado_id = $juzgado['id'];
                    }
                }
                
                $dependencia_id = $data['dependencia_id'] ?? ($authUser['dependencia_id'] ?? null);
                $oficina = $data['oficina'] ?? null;
                
                // FORZAR el subordinado del usuario si no es admin
                if (!($authUser['puede_ver_todas'] ?? false) && $authUser['subordinado_valor']) {
                    $oficina = $authUser['subordinado_valor'];
                }
                
                $stmt = $db->prepare("INSERT INTO expedientes (
                    nro_expediente, anio, expediente_origen, anio_origen, fecha, 
                    tipo_oficio, juzgado_origen, dependencia_id, oficina, 
                    tipo_requerimiento, responsable_id, nro_informe_tecnico, 
                    resumen, observaciones, estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $data['nro_expediente'], 
                    $data['anio'] ?? date('Y'),
                    $data['expediente_origen'] ?? null, 
                    $data['anio_origen'] ?? null,
                    $data['fecha'], 
                    $data['tipo_oficio'],
                    $juzgado_id,
                    $dependencia_id,
                    $oficina,
                    $data['tipo_requerimiento'] ?? null, 
                    $data['responsable_id'] ?? null,
                    $data['nro_informe_tecnico'] ?? null, 
                    $data['resumen'] ?? null,
                    $data['observaciones'] ?? null,
                    $data['estado'] ?? 'Pendiente'
                ]);
                
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $juzgado_origen = $data['juzgado_origen'] ?? null;
                $juzgado_id = null;
                if ($juzgado_origen) {
                    $stmt = $db->prepare("SELECT id FROM juzgados WHERE nombre = ? LIMIT 1");
                    $stmt->execute([$juzgado_origen]);
                    $juzgado = $stmt->fetch();
                    if ($juzgado) {
                        $juzgado_id = $juzgado['id'];
                    }
                }
                
                $dependencia_id = $data['dependencia_id'] ?? null;
                $oficina = $data['oficina'] ?? null;
                
                $stmt = $db->prepare("UPDATE expedientes SET 
                    nro_expediente = ?, anio = ?, expediente_origen = ?, anio_origen = ?,
                    fecha = ?, tipo_oficio = ?, juzgado_origen = ?, dependencia_id = ?, 
                    oficina = ?, tipo_requerimiento = ?, responsable_id = ?, 
                    nro_informe_tecnico = ?, resumen = ?, observaciones = ?, estado = ?
                    WHERE id = ?");
                    
                $stmt->execute([
                    $data['nro_expediente'], 
                    $data['anio'] ?? date('Y'),
                    $data['expediente_origen'] ?? null, 
                    $data['anio_origen'] ?? null,
                    $data['fecha'], 
                    $data['tipo_oficio'],
                    $juzgado_id,
                    $dependencia_id,
                    $oficina,
                    $data['tipo_requerimiento'] ?? null, 
                    $data['responsable_id'] ?? null,
                    $data['nro_informe_tecnico'] ?? null, 
                    $data['resumen'] ?? null,
                    $data['observaciones'] ?? null,
                    $data['estado'] ?? 'Pendiente',
                    $id
                ]);
                
                echo json_encode(['success' => true]);
            } elseif ($method == 'DELETE') {
                $stmt = $db->prepare("DELETE FROM expedientes WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== EXPEDIENTE DOCUMENTOS ====================
        case 'expediente_documentos':
            if ($method == 'GET') {
                $expediente_id = $_GET['expediente_id'] ?? 0;
                if ($expediente_id) {
                    $stmt = $db->prepare("SELECT * FROM expediente_documentos WHERE expediente_id = ? ORDER BY created_at DESC");
                    $stmt->execute([$expediente_id]);
                    echo json_encode($stmt->fetchAll());
                } else {
                    echo json_encode([]);
                }
            } elseif ($method == 'POST') {
                $expediente_id = $_POST['expediente_id'] ?? 0;
                $titulo = $_POST['titulo'] ?? '';
                $descripcion = $_POST['descripcion'] ?? '';
                
                if (!$expediente_id || !isset($_FILES['archivo'])) {
                    echo json_encode(['error' => 'Datos incompletos']);
                    break;
                }
                
                $archivo = $_FILES['archivo'];
                $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
                $tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
                
                if (!in_array(strtolower($extension), $tiposPermitidos)) {
                    echo json_encode(['error' => 'Tipo de archivo no permitido']);
                    break;
                }
                
                $uploadDir = __DIR__ . '/../uploads/expedientes/' . $expediente_id . '/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $nombreArchivo = time() . '_' . uniqid() . '.' . $extension;
                $rutaCompleta = $uploadDir . $nombreArchivo;
                $rutaRelativa = 'uploads/expedientes/' . $expediente_id . '/' . $nombreArchivo;
                
                if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                    $stmt = $db->prepare("INSERT INTO expediente_documentos (expediente_id, titulo, descripcion, archivo_ruta, tipo_archivo, tamano) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$expediente_id, $titulo, $descripcion, $rutaRelativa, $extension, $archivo['size']]);
                    echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
                } else {
                    echo json_encode(['error' => 'Error al subir archivo']);
                }
            } elseif ($method == 'DELETE') {
                $doc_id = $id;
                $stmt = $db->prepare("SELECT archivo_ruta FROM expediente_documentos WHERE id = ?");
                $stmt->execute([$doc_id]);
                $doc = $stmt->fetch();
                
                if ($doc) {
                    $rutaCompleta = __DIR__ . '/../' . $doc['archivo_ruta'];
                    if (file_exists($rutaCompleta)) {
                        unlink($rutaCompleta);
                    }
                    $stmt = $db->prepare("DELETE FROM expediente_documentos WHERE id = ?");
                    $stmt->execute([$doc_id]);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'Documento no encontrado']);
                }
            }
            break;
        
        // ==================== EXPEDIENTE ELEVACION ====================
        case 'expediente_elevacion':
            if ($method == 'GET') {
                $expediente_id = $_GET['expediente_id'] ?? 0;
                $stmt = $db->prepare("SELECT * FROM expediente_elevaciones WHERE expediente_id = ? ORDER BY created_at DESC LIMIT 1");
                $stmt->execute([$expediente_id]);
                echo json_encode($stmt->fetch());
            }
            break;
        
        // ==================== EXPEDIENTE HISTORIAL ELEVACIONES ====================
        case 'expediente_historial_elevaciones':
            if ($method == 'GET') {
                $expediente_id = $_GET['expediente_id'] ?? 0;
                $stmt = $db->prepare("SELECT * FROM expediente_elevaciones WHERE expediente_id = ? ORDER BY fecha_elevacion DESC, created_at DESC");
                $stmt->execute([$expediente_id]);
                echo json_encode($stmt->fetchAll());
            }
            break;
        
        // ==================== EXPEDIENTE ELEVAR ====================
        case 'expediente_elevar':
            if ($method == 'POST') {
                $expediente_id = $_POST['expediente_id'] ?? 0;
                $fecha_elevacion = $_POST['fecha_elevacion'] ?? date('Y-m-d');
                $hora_elevacion = $_POST['hora_elevacion'] ?? null;
                $persona_recibio = $_POST['persona_recibio'] ?? null;
                $instancia_destino = $_POST['instancia_destino'] ?? null;
                $observaciones = $_POST['observaciones'] ?? null;
                
                $recibo_ruta = null;
                if (isset($_FILES['recibo_archivo']) && $_FILES['recibo_archivo']['error'] == 0) {
                    $archivo = $_FILES['recibo_archivo'];
                    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
                    $uploadDir = __DIR__ . '/../uploads/expedientes/' . $expediente_id . '/recibos/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $nombreArchivo = 'recibo_' . time() . '_' . uniqid() . '.' . $extension;
                    $rutaCompleta = $uploadDir . $nombreArchivo;
                    $recibo_ruta = 'uploads/expedientes/' . $expediente_id . '/recibos/' . $nombreArchivo;
                    move_uploaded_file($archivo['tmp_name'], $rutaCompleta);
                }
                
                $stmt = $db->prepare("INSERT INTO expediente_elevaciones (expediente_id, fecha_elevacion, hora_elevacion, persona_recibio, instancia_destino, recibo_ruta, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$expediente_id, $fecha_elevacion, $hora_elevacion, $persona_recibio, $instancia_destino, $recibo_ruta, $observaciones]);
                
                $stmt = $db->prepare("UPDATE expedientes SET estado = 'Elevado' WHERE id = ?");
                $stmt->execute([$expediente_id]);
                
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            }
            break;
        
        // ==================== EXPEDIENTE ELEVACION RECIBO ====================
        case 'expediente_elevacion_recibo':
            if ($method == 'DELETE') {
                $expediente_id = $_GET['expediente_id'] ?? 0;
                $stmt = $db->prepare("SELECT recibo_ruta FROM expediente_elevaciones WHERE expediente_id = ? ORDER BY created_at DESC LIMIT 1");
                $stmt->execute([$expediente_id]);
                $elevacion = $stmt->fetch();
                if ($elevacion && $elevacion['recibo_ruta']) {
                    $rutaCompleta = __DIR__ . '/../' . $elevacion['recibo_ruta'];
                    if (file_exists($rutaCompleta)) {
                        unlink($rutaCompleta);
                    }
                    $stmt = $db->prepare("UPDATE expediente_elevaciones SET recibo_ruta = NULL WHERE expediente_id = ?");
                    $stmt->execute([$expediente_id]);
                }
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== LICENCIAS ====================
        case 'licencias':
            if ($method == 'GET') {
                $filtro_subordinado = getSubordinadoFilter($authUser, 'l', 'oficina');
                
                $subordinado_filter = $_GET['subordinado'] ?? '';
                $subordinado_sql = '';
                if (!empty($subordinado_filter) && ($authUser['puede_ver_todas'] ?? false)) {
                    $subordinado_sql = " AND p.oficina = '" . addslashes($subordinado_filter) . "'";
                }
                
                if ($id) {
                    $sql = "SELECT l.*, p.apellido, p.nombre, p.legajo, p.oficina
                            FROM licencias l 
                            LEFT JOIN personal p ON l.agente_id = p.id 
                            WHERE l.id = ? $filtro_subordinado $subordinado_sql";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$id]);
                    echo json_encode($stmt->fetch());
                } else {
                    $sql = "SELECT l.*, p.apellido, p.nombre, p.legajo, p.oficina
                            FROM licencias l 
                            LEFT JOIN personal p ON l.agente_id = p.id 
                            WHERE 1=1 $filtro_subordinado $subordinado_sql
                            ORDER BY l.fecha_inicio DESC";
                    $stmt = $db->prepare($sql);
                    $stmt->execute();
                    echo json_encode($stmt->fetchAll());
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $dependencia_id = $data['dependencia_id'] ?? ($authUser['dependencia_id'] ?? null);
                $oficina = $data['oficina'] ?? null;
                
                // FORZAR el subordinado del usuario si no es admin
                if (!($authUser['puede_ver_todas'] ?? false) && $authUser['subordinado_valor']) {
                    $oficina = $authUser['subordinado_valor'];
                }
                
                $stmt = $db->prepare("INSERT INTO licencias (agente_id, tipo_licencia, estado, fecha_inicio, dias_habiles, dias_viaje, contar_fines_semana, fecha_fin, observaciones, dependencia_id, oficina) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['agente_id'], $data['tipo_licencia'], $data['estado'],
                    $data['fecha_inicio'], $data['dias_habiles'] ?? 0, $data['dias_viaje'] ?? 0,
                    $data['contar_fines_semana'] ?? 0, $data['fecha_fin'], $data['observaciones'] ?? null,
                    $dependencia_id, $oficina
                ]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("UPDATE licencias SET agente_id=?, tipo_licencia=?, estado=?, fecha_inicio=?, dias_habiles=?, dias_viaje=?, contar_fines_semana=?, fecha_fin=?, observaciones=?, dependencia_id=?, oficina=? WHERE id=?");
                $stmt->execute([
                    $data['agente_id'], $data['tipo_licencia'], $data['estado'],
                    $data['fecha_inicio'], $data['dias_habiles'] ?? 0, $data['dias_viaje'] ?? 0,
                    $data['contar_fines_semana'] ?? 0, $data['fecha_fin'], $data['observaciones'] ?? null,
                    $data['dependencia_id'] ?? null, $data['oficina'] ?? null, $id
                ]);
                echo json_encode(['success' => true]);
            } elseif ($method == 'DELETE') {
                $stmt = $db->prepare("DELETE FROM licencias WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== USUARIOS (solo admin) ====================
        case 'usuarios':
            $esAdmin = ($authUser['rol_id'] == 1 || $authUser['rol'] == 'Administrador Central' || ($authUser['puede_ver_todas'] ?? false));
            
            if (!$esAdmin) {
                http_response_code(403);
                echo json_encode(['error' => 'No tiene permisos para gestionar usuarios']);
                break;
            }
            
            if ($method == 'GET') {
                if ($id) {
                    $stmt = $db->prepare("
                        SELECT u.id, u.nombre_completo, u.username, u.email, u.rol_id, u.estado, u.dependencia_id, u.subordinado_id, u.nivel_acceso, 
                               r.nombre as rol_nombre, d.nombre as dependencia_nombre, c.valor as subordinado_nombre 
                        FROM usuarios u 
                        LEFT JOIN roles r ON u.rol_id = r.id 
                        LEFT JOIN dependencias d ON u.dependencia_id = d.id
                        LEFT JOIN catalogos c ON u.subordinado_id = c.id AND c.tipo = 'oficinas'
                        WHERE u.id = ?
                    ");
                    $stmt->execute([$id]);
                    $user = $stmt->fetch();
                    unset($user['password']);
                    echo json_encode($user);
                } else {
                    $stmt = $db->query("
                        SELECT u.id, u.nombre_completo, u.username, u.email, u.rol_id, u.estado, u.dependencia_id, u.subordinado_id, u.nivel_acceso,
                               r.nombre as rol_nombre, d.nombre as dependencia_nombre, c.valor as subordinado_nombre 
                        FROM usuarios u 
                        LEFT JOIN roles r ON u.rol_id = r.id 
                        LEFT JOIN dependencias d ON u.dependencia_id = d.id
                        LEFT JOIN catalogos c ON u.subordinado_id = c.id AND c.tipo = 'oficinas'
                        ORDER BY u.id
                    ");
                    $users = $stmt->fetchAll();
                    foreach ($users as &$user) {
                        unset($user['password']);
                    }
                    echo json_encode($users);
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO usuarios (nombre_completo, username, email, password, rol_id, dependencia_id, subordinado_id, nivel_acceso, estado, permisos) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['nombre_completo'], $data['username'], $data['email'], 
                    $hashedPassword, $data['rol_id'], $data['dependencia_id'] ?? null,
                    $data['subordinado_id'] ?? null, $data['nivel_acceso'] ?? 'solo_propio', 
                    $data['estado'], json_encode($data['permisos'] ?? [])
                ]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $fields = [];
                $params = [];
                
                $allowedFields = ['nombre_completo', 'username', 'email', 'rol_id', 'dependencia_id', 'subordinado_id', 'nivel_acceso', 'estado', 'permisos'];
                
                foreach ($allowedFields as $field) {
                    if (array_key_exists($field, $data)) {
                        $fields[] = "$field = ?";
                        if ($field === 'permisos') {
                            $params[] = json_encode($data[$field]);
                        } else {
                            $params[] = $data[$field];
                        }
                    }
                }
                
                if (!empty($data['password'])) {
                    $fields[] = "password = ?";
                    $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
                }
                
                if (empty($fields)) {
                    echo json_encode(['error' => 'No hay campos para actualizar']);
                    break;
                }
                
                $params[] = $id;
                $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                
                echo json_encode(['success' => true]);
            } elseif ($method == 'DELETE') {
                if ($id == $authUser['user_id']) {
                    echo json_encode(['error' => 'No puede eliminar su propio usuario']);
                    break;
                }
                $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== DASHBOARD ====================
        case 'dashboard':
            // Determinar el filtro de subordinado
            $subordinado_filter = $_GET['subordinado'] ?? '';
            
            // Si el usuario no es admin y tiene subordinado, forzar su valor
            if (!($authUser['puede_ver_todas'] ?? false) && $authUser['subordinado_valor']) {
                $subordinado_filter = $authUser['subordinado_valor'];
            }
            
            $subordinado_sql_personal = '';
            $subordinado_sql_recargos = '';
            $subordinado_sql_expedientes = '';
            $subordinado_sql_licencias = '';
            
            if (!empty($subordinado_filter)) {
                $subordinado_sql_personal = " AND p.oficina = '" . addslashes($subordinado_filter) . "'";
                $subordinado_sql_recargos = " AND r.oficina = '" . addslashes($subordinado_filter) . "'";
                $subordinado_sql_expedientes = " AND e.oficina = '" . addslashes($subordinado_filter) . "'";
                $subordinado_sql_licencias = " AND p.oficina = '" . addslashes($subordinado_filter) . "'";
            }
            
            $stats = [];
            
            // Total personal
            $stmt = $db->query("SELECT COUNT(*) as total FROM personal p WHERE 1=1 $subordinado_sql_personal");
            $stats['total_personal'] = $stmt->fetch()['total'];
            
            // Estadísticas por sexo
            $stmt = $db->query("SELECT sexo, COUNT(*) as cantidad FROM personal p WHERE sexo IS NOT NULL AND sexo != '' AND 1=1 $subordinado_sql_personal GROUP BY sexo");
            $sexoData = $stmt->fetchAll();
            $stats['estadisticas_sexo'] = [];
            foreach ($sexoData as $s) $stats['estadisticas_sexo'][$s['sexo']] = (int)$s['cantidad'];
            
            // Distribución por jerarquía
            $stmt = $db->query("SELECT jerarquia, COUNT(*) as cantidad FROM personal p WHERE jerarquia IS NOT NULL AND jerarquia != '' AND 1=1 $subordinado_sql_personal GROUP BY jerarquia ORDER BY cantidad DESC");
            $stats['jerarquias'] = $stmt->fetchAll();
            
            // Últimos recargos
            $stmt = $db->query("SELECT r.*, p.apellido, p.nombre FROM recargos r LEFT JOIN personal p ON r.personal_id = p.id WHERE 1=1 $subordinado_sql_recargos ORDER BY r.created_at DESC LIMIT 5");
            $stats['ultimos_recargos'] = $stmt->fetchAll();
            
            // Expedientes recientes
            $stmt = $db->query("SELECT e.* FROM expedientes e WHERE 1=1 $subordinado_sql_expedientes ORDER BY e.created_at DESC LIMIT 5");
            $stats['expedientes_recientes'] = $stmt->fetchAll();
            
            // Licencias activas
            $stmt = $db->query("SELECT l.*, p.apellido, p.nombre FROM licencias l LEFT JOIN personal p ON l.agente_id = p.id WHERE l.estado IN ('Pendiente', 'Aprobada') AND 1=1 $subordinado_sql_licencias ORDER BY l.fecha_inicio LIMIT 5");
            $stats['licencias_activas'] = $stmt->fetchAll();
            
            echo json_encode($stats);
            break;
        
        // ==================== FERIADOS ====================
        case 'feriados':
            if ($method == 'GET') {
                $stmt = $db->prepare("SELECT * FROM feriados ORDER BY fecha DESC");
                $stmt->execute();
                $feriados = $stmt->fetchAll();
                echo json_encode($feriados);
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
        
        // ==================== DASHBOARD CONFIG ====================
        case 'dashboard_config':
            $usuario_id = $authUser['user_id'];
            $defaultWidgets = ['stats', 'jerarquias', 'personal_sexo', 'secciones_guardia', 'recargos', 'expedientes', 'licencias', 'tipos_requerimiento', 'juzgados', 'cumpleanos'];
            
            if ($method == 'GET') {
                $stmt = $db->prepare("SELECT widgets FROM dashboard_config WHERE usuario_id = ?");
                $stmt->execute([$usuario_id]);
                $config = $stmt->fetch();
                
                if (!$config) {
                    echo json_encode($defaultWidgets);
                } else {
                    $widgets = json_decode($config['widgets'], true);
                    echo json_encode($widgets ?: $defaultWidgets);
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $widgets = json_encode($data['widgets']);
                
                $stmt = $db->prepare("INSERT INTO dashboard_config (usuario_id, widgets) VALUES (?, ?) ON DUPLICATE KEY UPDATE widgets = VALUES(widgets)");
                $stmt->execute([$usuario_id, $widgets]);
                
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== DASHBOARD STATS ====================
        case 'dashboard_stats':
            $tipo = $_GET['tipo'] ?? '';
            $fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-d', strtotime('-30 days'));
            $fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
            
            $subordinado_filter = $_GET['subordinado'] ?? '';
            if (!($authUser['puede_ver_todas'] ?? false) && $authUser['subordinado_valor']) {
                $subordinado_filter = $authUser['subordinado_valor'];
            }
            
            $subordinado_sql = '';
            if (!empty($subordinado_filter)) {
                $subordinado_sql = " AND e.oficina = '" . addslashes($subordinado_filter) . "'";
            }
            
            if ($tipo == 'tipos_requerimiento') {
                $stmt = $db->prepare("SELECT tipo_requerimiento as nombre, COUNT(*) as cantidad FROM expedientes e WHERE fecha BETWEEN ? AND ? AND tipo_requerimiento IS NOT NULL AND tipo_requerimiento != '' AND 1=1 $subordinado_sql GROUP BY tipo_requerimiento");
                $stmt->execute([$fecha_desde, $fecha_hasta]);
                echo json_encode($stmt->fetchAll());
            } elseif ($tipo == 'juzgados') {
                $stmt = $db->prepare("SELECT juzgado_origen as nombre, COUNT(*) as cantidad FROM expedientes e WHERE fecha BETWEEN ? AND ? AND juzgado_origen IS NOT NULL AND juzgado_origen != '' AND 1=1 $subordinado_sql GROUP BY juzgado_origen");
                $stmt->execute([$fecha_desde, $fecha_hasta]);
                echo json_encode($stmt->fetchAll());
            } elseif ($tipo == 'tipos_oficio') {
                $stmt = $db->prepare("SELECT tipo_oficio as nombre, COUNT(*) as cantidad FROM expedientes e WHERE fecha BETWEEN ? AND ? AND tipo_oficio IS NOT NULL AND tipo_oficio != '' AND 1=1 $subordinado_sql GROUP BY tipo_oficio");
                $stmt->execute([$fecha_desde, $fecha_hasta]);
                echo json_encode($stmt->fetchAll());
            } elseif ($tipo == 'cumpleanos') {
                $mes = $_GET['mes'] ?? date('m');
                $subordinado_sql_personal = '';
                if (!empty($subordinado_filter)) {
                    $subordinado_sql_personal = " AND p.oficina = '" . addslashes($subordinado_filter) . "'";
                }
                $stmt = $db->prepare("SELECT id, apellido, nombre, legajo, fecha_nacimiento FROM personal p WHERE MONTH(fecha_nacimiento) = ? AND fecha_nacimiento IS NOT NULL AND 1=1 $subordinado_sql_personal ORDER BY DAY(fecha_nacimiento)");
                $stmt->execute([$mes]);
                echo json_encode($stmt->fetchAll());
            } else {
                echo json_encode([]);
            }
            break;
        
        // ==================== TABLE CONFIG ====================
        case 'table_config':
            $usuario_id = $authUser['user_id'];
            $tabla = $_GET['tabla'] ?? '';
            
            if ($method == 'GET') {
                $stmt = $db->prepare("SELECT columnas FROM user_table_config WHERE usuario_id = ? AND tabla = ?");
                $stmt->execute([$usuario_id, $tabla]);
                $config = $stmt->fetch();
                
                if (!$config) {
                    echo json_encode([]);
                } else {
                    echo json_encode(json_decode($config['columnas'], true));
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $columnas = json_encode($data['columnas']);
                
                $stmt = $db->prepare("INSERT INTO user_table_config (usuario_id, tabla, columnas) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE columnas = VALUES(columnas)");
                $stmt->execute([$usuario_id, $tabla, $columnas]);
                
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== PERFIL USUARIO ====================
        case 'perfil':
            $usuario_id = $authUser['user_id'];
            
            if ($method == 'GET') {
                $stmt = $db->prepare("
                    SELECT u.*, r.nombre as rol_nombre, d.nombre as dependencia_nombre, c.valor as subordinado_nombre 
                    FROM usuarios u 
                    LEFT JOIN roles r ON u.rol_id = r.id 
                    LEFT JOIN dependencias d ON u.dependencia_id = d.id
                    LEFT JOIN catalogos c ON u.subordinado_id = c.id AND c.tipo = 'oficinas'
                    WHERE u.id = ?
                ");
                $stmt->execute([$usuario_id]);
                $user = $stmt->fetch();
                unset($user['password']);
                echo json_encode($user);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (isset($data['nombre_completo']) && isset($data['email'])) {
                    $stmt = $db->prepare("UPDATE usuarios SET nombre_completo = ?, email = ? WHERE id = ?");
                    $stmt->execute([$data['nombre_completo'], $data['email'], $usuario_id]);
                    echo json_encode(['success' => true]);
                } elseif (isset($data['password_actual']) && isset($data['nueva_password'])) {
                    $stmt = $db->prepare("SELECT password FROM usuarios WHERE id = ?");
                    $stmt->execute([$usuario_id]);
                    $user = $stmt->fetch();
                    
                    if ($user && password_verify($data['password_actual'], $user['password'])) {
                        $nuevaPassword = password_hash($data['nueva_password'], PASSWORD_DEFAULT);
                        $stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                        $stmt->execute([$nuevaPassword, $usuario_id]);
                        echo json_encode(['success' => true]);
                    } else {
                        http_response_code(401);
                        echo json_encode(['error' => 'Contraseña actual incorrecta']);
                    }
                } else {
                    echo json_encode(['error' => 'Datos incompletos']);
                }
            }
            break;
        
        // ==================== SECCIONES DE GUARDIA ====================
        case 'secciones_guardia':
            if ($method == 'GET') {
                $esAdmin = ($authUser['rol_id'] == 1 || $authUser['rol'] == 'Administrador Central' || ($authUser['puede_ver_todas'] ?? false));
                $subordinado_id = $authUser['subordinado_id'] ?? null;
                $subordinado_valor = $authUser['subordinado_valor'] ?? null;
                
                $subordinado_filter = $_GET['subordinado'] ?? '';
                $subordinado_filter_id = null;
                
                // Admin con filtro específico
                if (!empty($subordinado_filter) && $esAdmin) {
                    $stmt = $db->prepare("SELECT id FROM catalogos WHERE valor = ? AND tipo = 'oficinas'");
                    $stmt->execute([$subordinado_filter]);
                    $sub = $stmt->fetch();
                    if ($sub) {
                        $subordinado_filter_id = $sub['id'];
                    }
                }
                
                if ($id) {
                    $sql = "SELECT s.*, c.valor as subordinado_nombre 
                            FROM secciones_guardia s
                            LEFT JOIN catalogos c ON s.subordinado_id = c.id
                            WHERE s.id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$id]);
                    echo json_encode($stmt->fetch());
                } else {
                    $where = "WHERE s.activo = 1";
                    $params = [];
                    
                    if ($esAdmin && $subordinado_filter_id) {
                        $where .= " AND s.subordinado_id = ?";
                        $params[] = $subordinado_filter_id;
                    } elseif ($esAdmin) {
                        // Admin sin filtro - ve todas
                    } elseif (!$esAdmin && $subordinado_id) {
                        $where .= " AND s.subordinado_id = ?";
                        $params[] = $subordinado_id;
                    } elseif (!$esAdmin && !$subordinado_id) {
                        echo json_encode([]);
                        break;
                    }
                    
                    $sql = "SELECT s.*, c.valor as subordinado_nombre 
                            FROM secciones_guardia s
                            LEFT JOIN catalogos c ON s.subordinado_id = c.id
                            $where 
                            ORDER BY s.orden, s.id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    $secciones = $stmt->fetchAll();
                    
                    echo json_encode($secciones);
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $esAdmin = ($authUser['rol_id'] == 1 || $authUser['rol'] == 'Administrador Central' || ($authUser['puede_ver_todas'] ?? false));
                $subordinado_id = $authUser['subordinado_id'] ?? null;
                
                $asignarSubordinado = null;
                if (!$esAdmin && $subordinado_id) {
                    $asignarSubordinado = $subordinado_id;
                } elseif ($esAdmin && isset($data['subordinado_id']) && $data['subordinado_id']) {
                    $asignarSubordinado = $data['subordinado_id'];
                }
                
                $stmt = $db->prepare("INSERT INTO secciones_guardia (nombre, descripcion, orden, subordinado_id) VALUES (?, ?, ?, ?)");
                $result = $stmt->execute([
                    $data['nombre'], 
                    $data['descripcion'] ?? null, 
                    $data['orden'] ?? 0,
                    $asignarSubordinado
                ]);
                
                if ($result) {
                    echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
                } else {
                    echo json_encode(['error' => 'Error al insertar sección']);
                }
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $esAdmin = ($authUser['rol_id'] == 1 || $authUser['rol'] == 'Administrador Central' || ($authUser['puede_ver_todas'] ?? false));
                $subordinado_id = $authUser['subordinado_id'] ?? null;
                
                if (!$esAdmin) {
                    $check = $db->prepare("SELECT id FROM secciones_guardia WHERE id = ? AND subordinado_id = ?");
                    $check->execute([$id, $subordinado_id]);
                    if (!$check->fetch()) {
                        http_response_code(403);
                        echo json_encode(['error' => 'No tiene permisos para modificar esta sección']);
                        break;
                    }
                }
                
                $subordinado_asignar = isset($data['subordinado_id']) ? $data['subordinado_id'] : null;
                
                $stmt = $db->prepare("UPDATE secciones_guardia SET nombre = ?, descripcion = ?, orden = ?, subordinado_id = ? WHERE id = ?");
                $result = $stmt->execute([
                    $data['nombre'], 
                    $data['descripcion'] ?? null, 
                    $data['orden'] ?? 0,
                    $subordinado_asignar,
                    $id
                ]);
                
                if ($result) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'Error al actualizar sección']);
                }
            } elseif ($method == 'DELETE') {
                $esAdmin = ($authUser['rol_id'] == 1 || $authUser['rol'] == 'Administrador Central' || ($authUser['puede_ver_todas'] ?? false));
                $subordinado_id = $authUser['subordinado_id'] ?? null;
                
                if (!$esAdmin) {
                    $check = $db->prepare("SELECT id FROM secciones_guardia WHERE id = ? AND subordinado_id = ?");
                    $check->execute([$id, $subordinado_id]);
                    if (!$check->fetch()) {
                        http_response_code(403);
                        echo json_encode(['error' => 'No tiene permisos para eliminar esta sección']);
                        break;
                    }
                }
                
                $stmt = $db->prepare("SELECT COUNT(*) as total FROM personal WHERE seccion_guardia_id = ?");
                $stmt->execute([$id]);
                $count = $stmt->fetch();
                if ($count['total'] > 0) {
                    echo json_encode(['error' => 'No se puede eliminar, hay personal asignado a esta sección']);
                } else {
                    $stmt = $db->prepare("DELETE FROM secciones_guardia WHERE id = ?");
                    $stmt->execute([$id]);
                    echo json_encode(['success' => true]);
                }
            }
            break;
        
        // ==================== REORDENAR SECCIONES ====================
        case 'reordenar_secciones':
            if ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $ordenes = $data['ordenes'] ?? [];
                foreach ($ordenes as $item) {
                    $stmt = $db->prepare("UPDATE secciones_guardia SET orden = ? WHERE id = ?");
                    $stmt->execute([$item['orden'], $item['id']]);
                }
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== ESTADÍSTICAS SECCIONES GUARDIA ====================
        case 'stats_secciones':
            $esAdmin = ($authUser['rol_id'] == 1 || $authUser['rol'] == 'Administrador Central' || ($authUser['puede_ver_todas'] ?? false));
            $subordinado_valor = $authUser['subordinado_valor'] ?? null;
            $subordinado_filter = $_GET['subordinado'] ?? '';
            
            if (!$esAdmin && $subordinado_valor) {
                $stmt = $db->prepare("
                    SELECT s.id, s.nombre, s.descripcion, COUNT(p.id) as total_personal
                    FROM secciones_guardia s
                    LEFT JOIN personal p ON p.seccion_guardia_id = s.id AND p.estado IS NULL
                    WHERE s.activo = 1 AND s.subordinado_id = (SELECT id FROM catalogos WHERE valor = ? AND tipo = 'oficinas')
                    GROUP BY s.id
                    ORDER BY s.orden
                ");
                $stmt->execute([$subordinado_valor]);
            } elseif ($esAdmin && !empty($subordinado_filter)) {
                $stmt = $db->prepare("
                    SELECT s.id, s.nombre, s.descripcion, COUNT(p.id) as total_personal
                    FROM secciones_guardia s
                    LEFT JOIN personal p ON p.seccion_guardia_id = s.id AND p.estado IS NULL
                    WHERE s.activo = 1 AND s.subordinado_id = (SELECT id FROM catalogos WHERE valor = ? AND tipo = 'oficinas')
                    GROUP BY s.id
                    ORDER BY s.orden
                ");
                $stmt->execute([$subordinado_filter]);
            } else {
                $stmt = $db->query("
                    SELECT s.id, s.nombre, s.descripcion, COUNT(p.id) as total_personal
                    FROM secciones_guardia s
                    LEFT JOIN personal p ON p.seccion_guardia_id = s.id AND p.estado IS NULL
                    WHERE s.activo = 1
                    GROUP BY s.id
                    ORDER BY s.orden
                ");
            }
            echo json_encode($stmt->fetchAll());
            break;
        
        // ==================== EQUIPAMIENTO - ARMAS ====================
        case 'equipamiento_armas':
            if ($method == 'GET') {
                if ($id) {
                    $stmt = $db->prepare("SELECT * FROM equipamiento_armas WHERE id = ?");
                    $stmt->execute([$id]);
                    echo json_encode($stmt->fetch());
                } else {
                    $personal_id = $_GET['personal_id'] ?? 0;
                    if ($personal_id) {
                        $stmt = $db->prepare("SELECT * FROM equipamiento_armas WHERE personal_id = ? ORDER BY fecha_asignacion DESC");
                        $stmt->execute([$personal_id]);
                        echo json_encode($stmt->fetchAll());
                    } else {
                        $stmt = $db->query("SELECT a.*, p.apellido, p.nombre, p.legajo FROM equipamiento_armas a LEFT JOIN personal p ON a.personal_id = p.id ORDER BY a.created_at DESC");
                        echo json_encode($stmt->fetchAll());
                    }
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("INSERT INTO equipamiento_armas (personal_id, tipo, marca, modelo, serie, calibre, fecha_asignacion, estado, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['personal_id'], $data['tipo'] ?? 'Arma de fuego', $data['marca'] ?? null, $data['modelo'] ?? null,
                    $data['serie'], $data['calibre'] ?? null, $data['fecha_asignacion'] ?? date('Y-m-d'), 
                    $data['estado'] ?? 'Asignada', $data['observaciones'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("UPDATE equipamiento_armas SET tipo=?, marca=?, modelo=?, serie=?, calibre=?, fecha_asignacion=?, estado=?, observaciones=? WHERE id=?");
                $stmt->execute([
                    $data['tipo'] ?? 'Arma de fuego', $data['marca'] ?? null, $data['modelo'] ?? null, $data['serie'],
                    $data['calibre'] ?? null, $data['fecha_asignacion'] ?? date('Y-m-d'), $data['estado'] ?? 'Asignada', 
                    $data['observaciones'] ?? null, $id
                ]);
                echo json_encode(['success' => true]);
            } elseif ($method == 'DELETE') {
                $stmt = $db->prepare("DELETE FROM equipamiento_armas WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== EQUIPAMIENTO - CHALECOS ====================
        case 'equipamiento_chalecos':
            if ($method == 'GET') {
                if ($id) {
                    $stmt = $db->prepare("SELECT * FROM equipamiento_chalecos WHERE id = ?");
                    $stmt->execute([$id]);
                    echo json_encode($stmt->fetch());
                } else {
                    $personal_id = $_GET['personal_id'] ?? 0;
                    if ($personal_id) {
                        $stmt = $db->prepare("SELECT * FROM equipamiento_chalecos WHERE personal_id = ? ORDER BY fecha_asignacion DESC");
                        $stmt->execute([$personal_id]);
                        echo json_encode($stmt->fetchAll());
                    } else {
                        $stmt = $db->query("SELECT c.*, p.apellido, p.nombre, p.legajo FROM equipamiento_chalecos c LEFT JOIN personal p ON c.personal_id = p.id ORDER BY c.fecha_vencimiento ASC");
                        echo json_encode($stmt->fetchAll());
                    }
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("INSERT INTO equipamiento_chalecos (personal_id, numero_serie, talla, nivel_proteccion, fecha_asignacion, fecha_vencimiento, estado, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['personal_id'], $data['numero_serie'], $data['talla'] ?? null, $data['nivel_proteccion'] ?? null,
                    $data['fecha_asignacion'] ?? date('Y-m-d'), $data['fecha_vencimiento'], 
                    $data['estado'] ?? 'Activo', $data['observaciones'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("UPDATE equipamiento_chalecos SET numero_serie=?, talla=?, nivel_proteccion=?, fecha_asignacion=?, fecha_vencimiento=?, estado=?, observaciones=? WHERE id=?");
                $stmt->execute([
                    $data['numero_serie'], $data['talla'] ?? null, $data['nivel_proteccion'] ?? null,
                    $data['fecha_asignacion'] ?? date('Y-m-d'), $data['fecha_vencimiento'], $data['estado'] ?? 'Activo',
                    $data['observaciones'] ?? null, $id
                ]);
                echo json_encode(['success' => true]);
            } elseif ($method == 'DELETE') {
                $stmt = $db->prepare("DELETE FROM equipamiento_chalecos WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            }
            break;
        
        // ==================== ESTADÍSTICAS EQUIPAMIENTO ====================
        case 'stats_equipamiento':
            $stats = [];
            
            $stmt = $db->query("SELECT COUNT(*) as total_armas FROM equipamiento_armas");
            $stats['total_armas'] = $stmt->fetch()['total_armas'];
            
            $stmt = $db->query("SELECT estado, COUNT(*) as cantidad FROM equipamiento_armas GROUP BY estado");
            $stats['armas_por_estado'] = $stmt->fetchAll();
            
            $stmt = $db->query("SELECT COUNT(*) as total_chalecos FROM equipamiento_chalecos");
            $stats['total_chalecos'] = $stmt->fetch()['total_chalecos'];
            
            $stmt = $db->query("SELECT COUNT(*) as chalecos_vencidos FROM equipamiento_chalecos WHERE fecha_vencimiento < CURDATE()");
            $stats['chalecos_vencidos'] = $stmt->fetch()['chalecos_vencidos'];
            
            $stmt = $db->query("SELECT COUNT(*) as chalecos_por_vencer FROM equipamiento_chalecos WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
            $stats['chalecos_por_vencer'] = $stmt->fetch()['chalecos_por_vencer'];
            
            echo json_encode($stats);
            break;
        
        // ==================== BITÁCORA DE ACTIVIDAD ====================
        case 'bitacora':
            if ($method == 'GET') {
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
                $usuario_id = $authUser['user_id'];
                $stmt = $db->prepare("SELECT * FROM bitacora WHERE usuario_id = ? ORDER BY created_at DESC LIMIT ?");
                $stmt->execute([$usuario_id, $limit]);
                echo json_encode($stmt->fetchAll());
            }
            break;
        
        // ==================== DEFAULT ====================
        default:
            echo json_encode(['error' => 'Endpoint no encontrado: ' . $endpoint]);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>