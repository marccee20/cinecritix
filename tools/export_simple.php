<?php
// Script simplificado para exportar imágenes BLOB de la tabla `peliculas` a archivos en disco.
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Iniciando exportación de imágenes...\n";

try {
    require_once __DIR__ . '/../includes/db.php';
    echo "✓ Conexión a BD establecida\n";
} catch (Exception $e) {
    die("Error al conectar: " . $e->getMessage() . "\n");
}

$targetDir = __DIR__ . '/../imagenes/exportadas';
$thumbDir = $targetDir . '/thumbs';

// Crear carpetas
if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0755, true)) {
        die("Error: No se pudo crear $targetDir\n");
    }
}
echo "✓ Carpeta exportadas lista\n";

if (!is_dir($thumbDir)) {
    if (!mkdir($thumbDir, 0755, true)) {
        die("Error: No se pudo crear $thumbDir\n");
    }
}
echo "✓ Carpeta thumbs lista\n";

// Intentar añadir columnas (ignorar si ya existen)
@$conexion->query("ALTER TABLE peliculas ADD COLUMN imagen_path VARCHAR(255) NULL");
@$conexion->query("ALTER TABLE peliculas ADD COLUMN imagen_thumb VARCHAR(255) NULL");
echo "✓ BD preparada\n";

// Obtener películas con imagen BLOB
$sql = "SELECT id_peliculas, nombre, imagen FROM peliculas WHERE imagen IS NOT NULL AND imagen != ''";
$res = $conexion->query($sql);

if (!$res) {
    die("Error en consulta: " . $conexion->error . "\n");
}

$rowCount = $res->num_rows;
echo "Encontradas $rowCount películas con imágenes\n";

$updated = 0;
$errors = 0;

while ($row = $res->fetch_assoc()) {
    $id = $row['id_peliculas'];
    $nombre = $row['nombre'];
    $imagenData = $row['imagen'];
    
    // Crear nombre seguro para archivo
    $nameSlug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($nombre));
    $nameSlug = trim($nameSlug, '-');
    $filename = $id . '_' . $nameSlug . '.jpg';
    
    $filepath = $targetDir . '/' . $filename;
    $thumbpath = $thumbDir . '/' . $filename;
    
    // Escribir imagen original
    if (!file_put_contents($filepath, $imagenData)) {
        echo "⚠ Error escribiendo: $filepath\n";
        $errors++;
        continue;
    }
    
    // Generar miniatura (máx 200x200)
    $thumbCreated = false;
    if (function_exists('imagecreatefromstring')) {
        $srcImg = @imagecreatefromstring($imagenData);
        if ($srcImg !== false) {
            $w = imagesx($srcImg);
            $h = imagesy($srcImg);
            $max = 200;
            $ratio = min($max / $w, $max / $h, 1);
            $tw = (int)($w * $ratio);
            $th = (int)($h * $ratio);
            
            $thumb = imagecreatetruecolor($tw, $th);
            imagecopyresampled($thumb, $srcImg, 0, 0, 0, 0, $tw, $th, $w, $h);
            
            if (imagejpeg($thumb, $thumbpath, 85)) {
                $thumbCreated = true;
            }
            imagedestroy($thumb);
            imagedestroy($srcImg);
        }
    }
    
    // Si no se creó miniatura, copiar original
    if (!$thumbCreated) {
        copy($filepath, $thumbpath);
    }
    
    // Actualizar BD con rutas relativas
    $relativePath = 'imagenes/exportadas/' . $filename;
    $relativeThumb = 'imagenes/exportadas/thumbs/' . $filename;
    
    $stmt = $conexion->prepare("UPDATE peliculas SET imagen_path = ?, imagen_thumb = ? WHERE id_peliculas = ?");
    if ($stmt) {
        $stmt->bind_param('ssi', $relativePath, $relativeThumb, $id);
        if ($stmt->execute()) {
            $updated++;
            echo "✓ [$updated/$rowCount] $nombre\n";
        } else {
            echo "⚠ Error actualizando BD para ID $id\n";
            $errors++;
        }
        $stmt->close();
    } else {
        echo "⚠ Error preparando statement para ID $id\n";
        $errors++;
    }
}

echo "\n========== RESULTADO ==========\n";
echo "Exportadas: $updated imágenes\n";
echo "Errores: $errors\n";
echo "Ubicación: $targetDir\n";
echo "Miniaturas: $thumbDir\n";
echo "================================\n";

$conexion->close();
?>
