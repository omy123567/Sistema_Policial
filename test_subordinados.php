<?php
header('Content-Type: application/json');
require_once 'backend/config.php';

try {
    $db = getDB();
    
    // Consulta directa a la base de datos
    $stmt = $db->prepare("SELECT id, valor, orden, dependencia_id FROM catalogos WHERE tipo = 'oficinas' AND activo = 1 ORDER BY orden, valor");
    $stmt->execute();
    $subordinados = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'count' => count($subordinados),
        'data' => $subordinados
    ], JSON_PRETTY_PRINT);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>