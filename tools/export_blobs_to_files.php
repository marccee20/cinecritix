<?php
// Script seguro para exportar imágenes BLOB de la tabla `peliculas` a archivos en disco.
// Ejecutar desde navegador o CLI. Hacer backup antes.

require_once __DIR__ . '/../includes/db.php';

$targetDir = __DIR__ . '/../imagenes/exportadas';
$thumbDir = $targetDir . '/thumbs';
if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);

// Añadir columnas imagen_path e imagen_thumb si no existen
$alter1 = "ALTER TABLE peliculas ADD COLUMN IF NOT EXISTS imagen_path VARCHAR(255) NULL";
$alter2 = "ALTER TABLE peliculas ADD COLUMN IF NOT EXISTS imagen_thumb VARCHAR(255) NULL";
// Algunos MySQL no aceptan IF NOT EXISTS en ALTER TABLE ADD COLUMN, intentar de forma segura
try {
    $conexion->query($alter1);
    $conexion->query($alter2);
} catch (Exception $e) {
    // Ignorar errores de alter
}

$sql = "SELECT id_peliculas, nombre, imagen, imagen_path FROM peliculas WHERE imagen IS NOT NULL";
$res = $conexion->query($sql);

if (!$res) {
    die('Error en consulta: ' . $conexion->error);
}

$updated = 0;
while ($row = $res->fetch_assoc()) {
    $id = $row['id_peliculas'];
    // Si ya tiene imagen_path, saltar
    if (!empty($row['imagen_path'])) continue;

    $name = preg_replace('/[^a-z0-9]+/i', '-', strtolower($row['nombre']));
    $filename = $id . '_' . $name . '.jpg';
    $filepath = $targetDir . '/' . $filename;
    $thumbpath = $thumbDir . '/' . $filename;

    // Escribir contenido binario
    file_put_contents($filepath, $row['imagen']);

    // Generar miniatura (max 200x200) si GD está disponible
    $imgData = $row['imagen'];
    $thumbCreated = false;
    if (function_exists('imagecreatefromstring')) {
        $srcImg = @imagecreatefromstring($imgData);
        if ($srcImg !== false) {
            $w = imagesx($srcImg);
            $h = imagesy($srcImg);
            $max = 200;
            $ratio = min($max / $w, $max / $h, 1);
            $tw = (int)($w * $ratio);
            $th = (int)($h * $ratio);
            $thumb = imagecreatetruecolor($tw, $th);
            imagecopyresampled($thumb, $srcImg, 0, 0, 0, 0, $tw, $th, $w, $h);
            imagejpeg($thumb, $thumbpath, 85);
            imagedestroy($thumb);
            imagedestroy($srcImg);
            $thumbCreated = true;
        }
    }

    // Si no se creó miniatura, copiar la imagen original como thumb
    if (!$thumbCreated) {
        copy($filepath, $thumbpath);
    }

    // Guardar rutas relativas en la BD
    $relativePath = 'imagenes/exportadas/' . $filename;
    $relativeThumb = 'imagenes/exportadas/thumbs/' . $filename;
    $stmt = $conexion->prepare("UPDATE peliculas SET imagen_path = ?, imagen_thumb = ? WHERE id_peliculas = ?");
    $stmt->bind_param('ssi', $relativePath, $relativeThumb, $id);
    $stmt->execute();
    $stmt->close();

    $updated++;
}

echo "Exportadas $updated imágenes a: $targetDir\n";
$conexion->close();
?>