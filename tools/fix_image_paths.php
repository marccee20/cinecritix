<?php
// Script para corregir rutas de imágenes en la BD
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Iniciando corrección de rutas...\n";
flush();

// Conexión directa
$servidor = 'localhost';
$usuario = 'root';
$contraseña = '';
$basedatos = 'peliculas_proyecto';

$conexion = new mysqli($servidor, $usuario, $contraseña, $basedatos);

if ($conexion->connect_errno) {
    die("ERROR: No se pudo conectar a BD: " . $conexion->connect_error . "\n");
}

echo "✓ Conectado a BD\n";
flush();

// Obtener todas las películas que tienen imagen_path
$sql = "SELECT id_peliculas, nombre, imagen_path, imagen_thumb FROM peliculas 
        WHERE imagen_path IS NOT NULL AND LENGTH(imagen_path) > 0 LIMIT 100";
$res = $conexion->query($sql);

if (!$res) {
    die("ERROR en query: " . $conexion->error . "\n");
}

$total = $res->num_rows;
echo "Encontradas $total películas con rutas\n";
flush();

$updated = 0;

while ($row = $res->fetch_assoc()) {
    $id = (int)$row['id_peliculas'];
    $nombre = $row['nombre'];
    $oldPath = $row['imagen_path'];
    $oldThumb = $row['imagen_thumb'];
    
    // Extraer nombre del archivo
    $fname = basename($oldPath);
    
    // Nuevas rutas con /cinecritix
    $newPath = "/cinecritix/imagenes/exportadas/$fname";
    $newThumb = "/cinecritix/imagenes/exportadas/thumbs/$fname";
    
    // Update BD
    $stmt = $conexion->prepare("UPDATE peliculas SET imagen_path = ?, imagen_thumb = ? WHERE id_peliculas = ?");
    $stmt->bind_param('ssi', $newPath, $newThumb, $id);
    if ($stmt->execute()) {
        $updated++;
        echo "✓ [$updated/$total] $nombre -> $fname\n";
        flush();
    }
    $stmt->close();
}

echo "\n=== LISTO ===\n";
echo "Actualizadas: $updated rutas\n";

$conexion->close();
?>
