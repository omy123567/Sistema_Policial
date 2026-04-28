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

// ==================== FUNCIÓN PARA OBTENER DEPENDENCIAS PERMITIDAS ====================
function getDependenciasPermitidas($db, $usuario) {
    if (!$usuario || !isset($usuario['user_id'])) return [];
    
    $stmt = $db->query("SELECT id FROM dependencias WHERE activo = 1");
    return array_column($stmt->fetchAll(), 'id');
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

// Endpoints públicos (incluyendo feriados para pruebas)
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
                // Asegurar valores por defecto
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
                
                // Crear carpeta si no existe
                $uploadDir = __DIR__ . '/../uploads/logo/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Generar nombre único
                $nombreArchivo = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
                $rutaCompleta = $uploadDir . $nombreArchivo;
                $rutaRelativa = 'uploads/logo/' . $nombreArchivo;
                
                // Mover archivo
                if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                    // Guardar en BD
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
                // Obtener logo actual
                $stmt = $db->query("SELECT valor FROM configuracion WHERE clave = 'logo_url'");
                $logo = $stmt->fetch();
                
                if ($logo && $logo['valor']) {
                    $rutaCompleta = __DIR__ . '/../' . $logo['valor'];
                    if (file_exists($rutaCompleta)) {
                        unlink($rutaCompleta);
                    }
                }
                
                // Eliminar de BD
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
            if ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $username = $data['username'] ?? '';
                $password = $data['password'] ?? '';
                
                $stmt = $db->prepare("SELECT u.*, r.nombre as rol_nombre FROM usuarios u LEFT JOIN roles r ON u.rol_id = r.id WHERE u.username = ? AND u.estado = 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    unset($user['password']);
                    $token = generateJWT([
                        'user_id' => $user['id'],
                        'username' => $user['username'],
                        'rol_id' => $user['rol_id'],
                        'nombre' => $user['nombre_completo']
                    ]);
                    echo json_encode(['success' => true, 'token' => $token, 'user' => $user]);
                } else {
                    http_response_code(401);
                    echo json_encode(['error' => 'Credenciales inválidas']);
                }
            }
            break;
        
        // ==================== DEPENDENCIAS ====================
        case 'dependencias':
            if ($method == 'GET') {
                $sql = "SELECT d.*, p.nombre as padre_nombre FROM dependencias d 
                        LEFT JOIN dependencias p ON d.padre_id = p.id 
                        WHERE d.activo = 1";
                $stmt = $db->prepare($sql);
                $stmt->execute();
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
                    $result = $stmt->fetchAll();
                    echo json_encode($result ?: []);
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
                $sql = "SELECT p.*, d.nombre as dependencia_nombre, s.nombre as seccion_guardia_nombre
                        FROM personal p 
                        LEFT JOIN dependencias d ON p.dependencia_id = d.id 
                        LEFT JOIN secciones_guardia s ON p.seccion_guardia_id = s.id
                        ORDER BY p.apellido, p.nombre";
                
                if ($id) {
                    $sql = "SELECT p.*, d.nombre as dependencia_nombre, s.nombre as seccion_guardia_nombre
                            FROM personal p 
                            LEFT JOIN dependencias d ON p.dependencia_id = d.id 
                            LEFT JOIN secciones_guardia s ON p.seccion_guardia_id = s.id
                            WHERE p.id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$id]);
                    echo json_encode($stmt->fetch());
                } else {
                    if (isset($_GET['search']) && $_GET['search']) {
                        $search = "%{$_GET['search']}%";
                        $sql = "SELECT p.*, d.nombre as dependencia_nombre, s.nombre as seccion_guardia_nombre
                                FROM personal p 
                                LEFT JOIN dependencias d ON p.dependencia_id = d.id 
                                LEFT JOIN secciones_guardia s ON p.seccion_guardia_id = s.id
                                WHERE p.legajo LIKE ? OR p.apellido LIKE ? OR p.nombre LIKE ? OR p.dni LIKE ?
                                ORDER BY p.apellido, p.nombre";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$search, $search, $search, $search]);
                    } else {
                        $stmt = $db->prepare($sql);
                        $stmt->execute();
                    }
                    echo json_encode($stmt->fetchAll());
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $dependencia_id = $data['dependencia_id'] ?? ($authUser['dependencia_id'] ?? null);
                $seccion_guardia_id = $data['seccion_guardia_id'] ?? null;
                
                $stmt = $db->prepare("INSERT INTO personal (
                    legajo, jerarquia, apellido, nombre, dni, sexo, oficina, fecha_nacimiento, 
                    tiene_arma, arma_marca, arma_modelo, arma_serie, chaleco_numero, sin_arma_motivo,
                    nro_credencial, nro_licencia_conducir, licencia_categoria, fecha_vencimiento_licencia, es_chofer,
                    obra_social, obra_social_numero, nro_afiliado, telefono, email, direccion, dependencia_id, seccion_guardia_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['legajo'], $data['jerarquia'], $data['apellido'], $data['nombre'],
                    $data['dni'], $data['sexo'], $data['oficina'], $data['fecha_nacimiento'],
                    isset($data['tiene_arma']) ? (int)$data['tiene_arma'] : 0,
                    $data['arma_marca'] ?? null, $data['arma_modelo'] ?? null, $data['arma_serie'] ?? null,
                    $data['chaleco_numero'] ?? null, $data['sin_arma_motivo'] ?? null,
                    $data['nro_credencial'] ?? null, $data['nro_licencia_conducir'] ?? null,
                    $data['licencia_categoria'] ?? null, $data['fecha_vencimiento_licencia'] ?? null,
                    isset($data['es_chofer']) ? (int)$data['es_chofer'] : 0,
                    $data['obra_social'] ?? null, $data['obra_social_numero'] ?? null,
                    $data['nro_afiliado'] ?? null, $data['telefono'] ?? null,
                    $data['email'] ?? null, $data['direccion'] ?? null, $dependencia_id, $seccion_guardia_id
                ]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $fields = [];
                $params = [];
                
                $allowedFields = [
                    'legajo', 'jerarquia', 'apellido', 'nombre', 'dni', 'sexo', 'oficina', 'fecha_nacimiento',
                    'tiene_arma', 'arma_marca', 'arma_modelo', 'arma_serie', 'chaleco_numero', 'sin_arma_motivo',
                    'nro_credencial', 'nro_licencia_conducir', 'licencia_categoria', 'fecha_vencimiento_licencia', 
                    'es_chofer', 'obra_social', 'obra_social_numero', 'nro_afiliado', 'telefono', 'email', 'direccion', 
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
                $sql = "SELECT r.*, p.apellido, p.nombre, p.legajo, s.nombre as seccion_nombre
                        FROM recargos r 
                        LEFT JOIN personal p ON r.personal_id = p.id 
                        LEFT JOIN secciones_guardia s ON r.seccion_guardia_id = s.id
                        ORDER BY r.fecha DESC, r.hora DESC";
                
                if ($id) {
                    $sql = "SELECT r.*, p.apellido, p.nombre, p.legajo, s.nombre as seccion_nombre
                            FROM recargos r 
                            LEFT JOIN personal p ON r.personal_id = p.id 
                            LEFT JOIN secciones_guardia s ON r.seccion_guardia_id = s.id
                            WHERE r.id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$id]);
                    echo json_encode($stmt->fetch());
                } else {
                    $stmt = $db->prepare($sql);
                    $stmt->execute();
                    echo json_encode($stmt->fetchAll());
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("INSERT INTO recargos (fecha, hora, tipo_recargo, seccion_guardia_id, personal_id, observaciones) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['fecha'], $data['hora'], $data['tipo_recargo'], 
                    $data['seccion_guardia_id'], $data['personal_id'], $data['observaciones'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("UPDATE recargos SET fecha=?, hora=?, tipo_recargo=?, seccion_guardia_id=?, personal_id=?, observaciones=? WHERE id=?");
                $stmt->execute([
                    $data['fecha'], $data['hora'], $data['tipo_recargo'], 
                    $data['seccion_guardia_id'], $data['personal_id'], $data['observaciones'] ?? null, $id
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
                $sql = "SELECT e.*, 
                        p.id as responsable_id,
                        p.apellido as responsable_apellido, 
                        p.nombre as responsable_nombre,
                        p.legajo as responsable_legajo,
                        d.nombre as dependencia_nombre 
                        FROM expedientes e 
                        LEFT JOIN personal p ON e.responsable_id = p.id 
                        LEFT JOIN dependencias d ON e.dependencia_id = d.id 
                        ORDER BY e.fecha DESC";
                
                if ($id) {
                    $sql = "SELECT e.*, 
                            p.id as responsable_id,
                            p.apellido as responsable_apellido, 
                            p.nombre as responsable_nombre,
                            p.legajo as responsable_legajo,
                            d.nombre as dependencia_nombre 
                            FROM expedientes e 
                            LEFT JOIN personal p ON e.responsable_id = p.id 
                            LEFT JOIN dependencias d ON e.dependencia_id = d.id 
                            WHERE e.id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$id]);
                    echo json_encode($stmt->fetch());
                } else {
                    $stmt = $db->prepare($sql);
                    $stmt->execute();
                    $expedientes = $stmt->fetchAll();
                    echo json_encode($expedientes);
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("INSERT INTO expedientes (nro_expediente, anio, expediente_origen, anio_origen, fecha, tipo_oficio, juzgado_origen, dependencia_id, tipo_requerimiento, responsable_id, nro_informe_tecnico, resumen, observaciones, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['nro_expediente'], 
                    $data['anio'] ?? date('Y'),
                    $data['expediente_origen'] ?? null, 
                    $data['anio_origen'] ?? null,
                    $data['fecha'], 
                    $data['tipo_oficio'],
                    $data['juzgado_origen'], 
                    $data['dependencia_id'] ?? null,
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
                $stmt = $db->prepare("UPDATE expedientes SET nro_expediente=?, anio=?, expediente_origen=?, anio_origen=?, fecha=?, tipo_oficio=?, juzgado_origen=?, dependencia_id=?, tipo_requerimiento=?, responsable_id=?, nro_informe_tecnico=?, resumen=?, observaciones=?, estado=? WHERE id=?");
                $stmt->execute([
                    $data['nro_expediente'], 
                    $data['anio'] ?? date('Y'),
                    $data['expediente_origen'] ?? null, 
                    $data['anio_origen'] ?? null,
                    $data['fecha'], 
                    $data['tipo_oficio'],
                    $data['juzgado_origen'], 
                    $data['dependencia_id'] ?? null,
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
                $sql = "SELECT l.*, p.apellido, p.nombre, p.legajo 
                        FROM licencias l 
                        LEFT JOIN personal p ON l.agente_id = p.id 
                        ORDER BY l.fecha_inicio DESC";
                
                if ($id) {
                    $sql = "SELECT l.*, p.apellido, p.nombre, p.legajo 
                            FROM licencias l 
                            LEFT JOIN personal p ON l.agente_id = p.id 
                            WHERE l.id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$id]);
                    echo json_encode($stmt->fetch());
                } else {
                    $stmt = $db->prepare($sql);
                    $stmt->execute();
                    echo json_encode($stmt->fetchAll());
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("INSERT INTO licencias (agente_id, tipo_licencia, estado, fecha_inicio, dias_habiles, dias_viaje, contar_fines_semana, fecha_fin, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['agente_id'], $data['tipo_licencia'], $data['estado'],
                    $data['fecha_inicio'], $data['dias_habiles'], $data['dias_viaje'],
                    $data['contar_fines_semana'], $data['fecha_fin'], $data['observaciones'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("UPDATE licencias SET agente_id=?, tipo_licencia=?, estado=?, fecha_inicio=?, dias_habiles=?, dias_viaje=?, contar_fines_semana=?, fecha_fin=?, observaciones=? WHERE id=?");
                $stmt->execute([
                    $data['agente_id'], $data['tipo_licencia'], $data['estado'],
                    $data['fecha_inicio'], $data['dias_habiles'], $data['dias_viaje'],
                    $data['contar_fines_semana'], $data['fecha_fin'], $data['observaciones'] ?? null, $id
                ]);
                echo json_encode(['success' => true]);
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
                    foreach($users as &$user) unset($user['password']);
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
        
        // ==================== DASHBOARD ====================
        case 'dashboard':
            $stats = [];
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM personal");
            $stats['total_personal'] = $stmt->fetch()['total'];
            
            $stmt = $db->query("SELECT sexo, COUNT(*) as cantidad FROM personal WHERE sexo IS NOT NULL AND sexo != '' GROUP BY sexo");
            $sexoData = $stmt->fetchAll();
            $stats['estadisticas_sexo'] = [];
            foreach ($sexoData as $s) $stats['estadisticas_sexo'][$s['sexo']] = (int)$s['cantidad'];
            
            $stmt = $db->query("SELECT COUNT(*) as total_chofer FROM personal WHERE es_chofer = 1");
            $stats['total_choferes'] = $stmt->fetch()['total_chofer'];
            
            $stmt = $db->query("SELECT COUNT(*) as con_arma FROM personal WHERE tiene_arma = 1");
            $stats['con_arma'] = $stmt->fetch()['con_arma'];
            
            $stmt = $db->query("SELECT jerarquia, COUNT(*) as cantidad FROM personal WHERE jerarquia IS NOT NULL AND jerarquia != '' GROUP BY jerarquia ORDER BY cantidad DESC");
            $stats['jerarquias'] = $stmt->fetchAll();
            
            $stmt = $db->query("SELECT r.*, p.apellido, p.nombre FROM recargos r LEFT JOIN personal p ON r.personal_id = p.id ORDER BY r.created_at DESC LIMIT 5");
            $stats['ultimos_recargos'] = $stmt->fetchAll();
            
            $stmt = $db->query("SELECT * FROM expedientes ORDER BY created_at DESC LIMIT 5");
            $stats['expedientes_recientes'] = $stmt->fetchAll();
            
            $stmt = $db->query("SELECT l.*, p.apellido, p.nombre FROM licencias l LEFT JOIN personal p ON l.agente_id = p.id WHERE l.estado IN ('Pendiente', 'Aprobada') ORDER BY l.fecha_inicio LIMIT 5");
            $stats['licencias_activas'] = $stmt->fetchAll();
            
            echo json_encode($stats);
            break;
        
         // ==================== FERIADOS ====================
        case 'feriados':
            if ($method == 'GET') {
                // Mostrar TODOS los feriados sin filtrar por año
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
            
            if ($tipo == 'tipos_requerimiento') {
                $stmt = $db->prepare("SELECT tipo_requerimiento as nombre, COUNT(*) as cantidad FROM expedientes WHERE fecha BETWEEN ? AND ? AND tipo_requerimiento IS NOT NULL AND tipo_requerimiento != '' GROUP BY tipo_requerimiento");
                $stmt->execute([$fecha_desde, $fecha_hasta]);
                echo json_encode($stmt->fetchAll());
            } elseif ($tipo == 'juzgados') {
                $stmt = $db->prepare("SELECT juzgado_origen as nombre, COUNT(*) as cantidad FROM expedientes WHERE fecha BETWEEN ? AND ? AND juzgado_origen IS NOT NULL AND juzgado_origen != '' GROUP BY juzgado_origen");
                $stmt->execute([$fecha_desde, $fecha_hasta]);
                echo json_encode($stmt->fetchAll());
            } elseif ($tipo == 'tipos_oficio') {
                $stmt = $db->prepare("SELECT tipo_oficio as nombre, COUNT(*) as cantidad FROM expedientes WHERE fecha BETWEEN ? AND ? AND tipo_oficio IS NOT NULL AND tipo_oficio != '' GROUP BY tipo_oficio");
                $stmt->execute([$fecha_desde, $fecha_hasta]);
                echo json_encode($stmt->fetchAll());
            } elseif ($tipo == 'cumpleanos') {
                $mes = $_GET['mes'] ?? date('m');
                $stmt = $db->prepare("SELECT id, apellido, nombre, legajo, fecha_nacimiento FROM personal WHERE MONTH(fecha_nacimiento) = ? AND fecha_nacimiento IS NOT NULL ORDER BY DAY(fecha_nacimiento)");
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
        
        // ==================== SECCIONES DE GUARDIA ====================
        case 'secciones_guardia':
            if ($method == 'GET') {
                if ($id) {
                    $stmt = $db->prepare("SELECT * FROM secciones_guardia WHERE id = ?");
                    $stmt->execute([$id]);
                    echo json_encode($stmt->fetch());
                } else {
                    $stmt = $db->query("SELECT * FROM secciones_guardia WHERE activo = 1 ORDER BY orden, id");
                    echo json_encode($stmt->fetchAll());
                }
            } elseif ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("INSERT INTO secciones_guardia (nombre, descripcion, orden) VALUES (?, ?, ?)");
                $stmt->execute([$data['nombre'], $data['descripcion'] ?? null, $data['orden'] ?? 0]);
                echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            } elseif ($method == 'PUT') {
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("UPDATE secciones_guardia SET nombre = ?, descripcion = ?, orden = ? WHERE id = ?");
                $stmt->execute([$data['nombre'], $data['descripcion'] ?? null, $data['orden'] ?? 0, $id]);
                echo json_encode(['success' => true]);
            } elseif ($method == 'DELETE') {
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
            $stmt = $db->query("
                SELECT s.id, s.nombre, s.descripcion, COUNT(p.id) as total_personal
                FROM secciones_guardia s
                LEFT JOIN personal p ON p.seccion_guardia_id = s.id AND p.estado IS NULL
                WHERE s.activo = 1
                GROUP BY s.id
                ORDER BY s.orden
            ");
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
                    } else {
                        $stmt = $db->query("SELECT a.*, p.apellido, p.nombre, p.legajo FROM equipamiento_armas a LEFT JOIN personal p ON a.personal_id = p.id ORDER BY a.created_at DESC");
                    }
                    echo json_encode($stmt->fetchAll());
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
                    } else {
                        $stmt = $db->query("SELECT c.*, p.apellido, p.nombre, p.legajo FROM equipamiento_chalecos c LEFT JOIN personal p ON c.personal_id = p.id ORDER BY c.fecha_vencimiento ASC");
                    }
                    echo json_encode($stmt->fetchAll());
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