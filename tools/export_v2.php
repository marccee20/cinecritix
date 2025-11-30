<?php
// Script de exportación inline - sin dependencias externas excepto conexión
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Iniciando...\n";
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

$targetDir = __DIR__ . '/../imagenes/exportadas';
$thumbDir = $targetDir . '/thumbs';

if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
    die("ERROR: No se pudo crear $targetDir\n");
}
echo "✓ Carpeta exportadas lista\n";
flush();

if (!is_dir($thumbDir) && !mkdir($thumbDir, 0755, true)) {
    die("ERROR: No se pudo crear $thumbDir\n");
}
echo "✓ Carpeta thumbs lista\n";
flush();

// Crear columnas si no existen
@$conexion->query("ALTER TABLE peliculas ADD COLUMN imagen_path VARCHAR(255) NULL");
@$conexion->query("ALTER TABLE peliculas ADD COLUMN imagen_thumb VARCHAR(255) NULL");
echo "✓ BD lista\n";
flush();

// Consulta
$sql = "SELECT id_peliculas, nombre, imagen FROM peliculas WHERE imagen IS NOT NULL AND LENGTH(imagen) > 0 LIMIT 100";
$res = $conexion->query($sql);

if (!$res) {
    die("ERROR en query: " . $conexion->error . "\n");
}

$total = $res->num_rows;
echo "Encontradas $total películas con imágenes\n";
flush();

$updated = 0;

while ($row = $res->fetch_assoc()) {
    $id = (int)$row['id_peliculas'];
    $nombre = $row['nombre'];
    $img = $row['imagen'];
    
    $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($nombre));
    $slug = trim($slug, '-');
    $fname = $id . '_' . $slug . '.jpg';
    
    $fpath = $targetDir . '/' . $fname;
    $tpath = $thumbDir . '/' . $fname;
    
    // Escribir imagen
    file_put_contents($fpath, $img);
    
    // Thumbnail - copiar si GD no está
    if (function_exists('imagecreatefromstring')) {
        $im = @imagecreatefromstring($img);
        if ($im) {
            $w = imagesx($im);
            $h = imagesy($im);
            $s = min(200 / $w, 200 / $h, 1);
            $nw = (int)($w * $s);
            $nh = (int)($h * $s);
            $thumb = imagecreatetruecolor($nw, $nh);
            imagecopyresampled($thumb, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagejpeg($thumb, $tpath, 85);
            imagedestroy($thumb);
            imagedestroy($im);
        } else {
            copy($fpath, $tpath);
        }
    } else {
        copy($fpath, $tpath);
    }
    
    // Update BD - ruta desde la raíz del servidor web
    $relpath = "/cinecritix/imagenes/exportadas/$fname";
    $relthumb = "/cinecritix/imagenes/exportadas/thumbs/$fname";
    
    $stmt = $conexion->prepare("UPDATE peliculas SET imagen_path = ?, imagen_thumb = ? WHERE id_peliculas = ?");
    $stmt->bind_param('ssi', $relpath, $relthumb, $id);
    if ($stmt->execute()) {
        $updated++;
        echo "✓ [$updated/$total] $nombre\n";
        flush();
    }
    $stmt->close();
}

echo "\n=== LISTO ===\n";
echo "Exportadas: $updated\n";
echo "Ubicación: $targetDir\n";

$conexion->close();
?>
