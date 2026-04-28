<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

require_once 'backend/config.php';

echo "<h1>🔍 Diagnóstico de Base de Datos</h1>";

try {
    $db = getDB();
    
    // Verificar tablas
    $tables = ['personal', 'recargos', 'expedientes', 'licencias', 'catalogos'];
    echo "<h2>📊 Verificación de Tablas:</h2>";
    foreach ($tables as $table) {
        $stmt = $db->query("SELECT COUNT(*) as total FROM $table");
        $count = $stmt->fetch();
        echo "$table: " . $count['total'] . " registros<br>";
    }
    
    // Verificar recargos específicamente
    echo "<h2>📋 Datos en Recargos:</h2>";
    $stmt = $db->query("SELECT * FROM recargos LIMIT 5");
    $recargos = $stmt->fetchAll();
    
    if (count($recargos) > 0) {
        echo "<pre>";
        print_r($recargos);
        echo "</pre>";
    } else {
        echo "<span style='color:orange'>⚠️ No hay datos en la tabla recargos</span><br>";
        
        // Verificar si hay personal para asignar
        $stmt = $db->query("SELECT id, legajo, apellido, nombre FROM personal LIMIT 3");
        $personal = $stmt->fetchAll();
        
        if (count($personal) > 0) {
            echo "<h3>✅ Personal disponible para asignar recargos:</h3>";
            echo "<pre>";
            print_r($personal);
            echo "</pre>";
        } else {
            echo "<span style='color:red'>❌ No hay personal registrado. Primero agrega personal.</span><br>";
        }
    }
    
    // Verificar catálogos de tipos de recargo
    echo "<h2>📚 Catálogo de Tipos de Recargo:</h2>";
    $stmt = $db->query("SELECT * FROM catalogos WHERE tipo = 'tipos_recargo'");
    $tipos = $stmt->fetchAll();
    
    if (count($tipos) > 0) {
        echo "<pre>";
        print_r($tipos);
        echo "</pre>";
    } else {
        echo "<span style='color:orange'>⚠️ No hay tipos de recargo configurados</span><br>";
        echo "Insertando tipos por defecto...<br>";
        
        $db->exec("INSERT INTO catalogos (tipo, valor, orden) VALUES 
            ('tipos_recargo', 'Llegada tarde', 1),
            ('tipos_recargo', 'Falta injustificada', 2),
            ('tipos_recargo', 'Incumplimiento de deberes', 3),
            ('tipos_recargo', 'Mal desempeño', 4),
            ('tipos_recargo', 'Falta de respeto', 5)
        ");
        echo "<span style='color:green'>✅ Tipos de recargo insertados</span><br>";
    }
    
} catch(Exception $e) {
    echo "<span style='color:red'>❌ Error: " . $e->getMessage() . "</span>";
}
?>