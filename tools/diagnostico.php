<?php
echo "=== DIAGNOSTICO DE EXPORTACION ===\n\n";

echo "1. Verificando rutas...\n";
echo "   __DIR__: " . __DIR__ . "\n";
echo "   Archivo esperado: " . __DIR__ . '/../includes/db.php' . "\n";
echo "   ¿Existe? " . (file_exists(__DIR__ . '/../includes/db.php') ? "SÍ" : "NO") . "\n\n";

echo "2. Intentando conexión...\n";
try {
    require_once __DIR__ . '/../includes/db.php';
    echo "   ✓ Incluido db.php\n";
    echo "   ¿\$conexion existe? " . (isset($conexion) ? "SÍ" : "NO") . "\n";
    
    if (isset($conexion)) {
        echo "   Tipo: " . gettype($conexion) . "\n";
        if ($conexion instanceof mysqli) {
            echo "   ✓ Conexión MySQLi válida\n";
            
            // Probar query simple
            $result = $conexion->query("SELECT COUNT(*) as total FROM peliculas");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "   ✓ Películas en BD: " . $row['total'] . "\n";
                
                // Verificar si existen las columnas
                $cols = $conexion->query("DESCRIBE peliculas");
                echo "\n   Columnas en tabla:\n";
                while ($col = $cols->fetch_assoc()) {
                    echo "      - " . $col['Field'] . " (" . $col['Type'] . ")\n";
                }
            } else {
                echo "   ✗ Error en query: " . $conexion->error . "\n";
            }
        }
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n3. Verificando carpetas...\n";
echo "   imagenes/: " . (is_dir(__DIR__ . '/../imagenes') ? "EXISTS" : "NO EXISTE") . "\n";
echo "   imagenes/exportadas/: " . (is_dir(__DIR__ . '/../imagenes/exportadas') ? "EXISTS" : "NO EXISTE") . "\n";

echo "\n=== FIN DIAGNOSTICO ===\n";
?>
