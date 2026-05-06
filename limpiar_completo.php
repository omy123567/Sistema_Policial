<?php
header('Content-Type: text/html; charset=utf-8');

require_once 'backend/config.php';

echo "<h1>🗑️ Limpiando todos los datos del sistema</h1>";

try {
    $db = getDB();
    
    // Deshabilitar foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "<p style='color: blue;'>🔓 Foreign key checks deshabilitados</p>";
    
    // Lista de tablas en orden correcto (hijas primero)
    $tablas = [
        'expediente_documentos',
        'expediente_elevaciones',
        'elevaciones',
        'personal_documentos',
        'recargos',
        'licencias',
        'expedientes',
        'personal'
    ];
    
    $eliminados = [];
    
    foreach ($tablas as $tabla) {
        // Verificar si la tabla existe
        $check = $db->query("SHOW TABLES LIKE '$tabla'");
        if ($check->rowCount() > 0) {
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM $tabla");
            $stmt->execute();
            $count = $stmt->fetch()['total'];
            
            $stmt = $db->prepare("TRUNCATE TABLE $tabla");
            $stmt->execute();
            
            $eliminados[$tabla] = $count;
            echo "<p style='color: green;'>✅ Tabla <strong>$tabla</strong>: <strong>$count</strong> registros eliminados</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Tabla <strong>$tabla</strong> no existe, omitida</p>";
        }
    }
    
    // Reiniciar AUTO_INCREMENT
    $tablasAuto = ['personal', 'recargos', 'expedientes', 'licencias', 'expediente_documentos', 'expediente_elevaciones', 'elevaciones', 'personal_documentos'];
    foreach ($tablasAuto as $tabla) {
        $check = $db->query("SHOW TABLES LIKE '$tabla'");
        if ($check->rowCount() > 0) {
            $db->exec("ALTER TABLE $tabla AUTO_INCREMENT = 1");
            echo "<p style='color: blue;'>🔄 AUTO_INCREMENT reiniciado para <strong>$tabla</strong></p>";
        }
    }
    
    // Reactivar foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<p style='color: blue;'>🔒 Foreign key checks reactivados</p>";
    
    echo "<hr>";
    echo "<h2>📊 Resumen final:</h2>";
    $total = array_sum($eliminados);
    echo "<p style='font-size: 1.2rem; font-weight: bold;'>✅ Total de registros eliminados: <span style='color: green;'>$total</span></p>";
    
    // Verificar que quedaron vacías
    echo "<hr><h3>🔍 Verificación:</h3>";
    foreach ($tablas as $tabla) {
        $check = $db->query("SHOW TABLES LIKE '$tabla'");
        if ($check->rowCount() > 0) {
            $stmt = $db->query("SELECT COUNT(*) as total FROM $tabla");
            $count = $stmt->fetch()['total'];
            $icono = $count == 0 ? '✅' : '❌';
            $color = $count == 0 ? 'green' : 'red';
            echo "<p style='color: $color;'>$icono Tabla <strong>$tabla</strong>: $count registros</p>";
        }
    }
    
    echo "<hr>";
    echo "<a href='dashboard.html' style='display: inline-block; background: #1d4ed8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-top: 20px;'>← Volver al Dashboard</a>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    // Asegurarse de reactivar foreign keys incluso si hay error
    try {
        $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    } catch(Exception $ex) {}
}
?>