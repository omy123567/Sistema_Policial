<?php
header('Content-Type: application/json');
require_once 'backend/config.php';

try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM feriados ORDER BY fecha");
    $feriados = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'count' => count($feriados),
        'data' => $feriados
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>