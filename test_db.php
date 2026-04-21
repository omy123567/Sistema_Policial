<?php
require_once 'backend/config.php';

try {
    $db = getDB();
    echo "✅ Conexión exitosa a la base de datos<br>";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    echo "✅ Usuarios encontrados: " . $result['total'] . "<br>";
    
    echo "✅ Sistema funcionando correctamente";
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>