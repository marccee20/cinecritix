<?php
/**
 * Script para sincronizar datos de películas entre equipos
 * 
 * Uso:
 * - Exportar: http://localhost/cinecritix/sync_data.php?action=export
 * - Importar: http://localhost/cinecritix/sync_data.php?action=import (sube un archivo JSON)
 */

session_start();
include("conexion.php");

$accion = isset($_GET['action']) ? $_GET['action'] : '';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sincronizar Datos - CineCritix</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { margin-bottom: 20px; color: #333; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 4px; }
        h2 { color: #007bff; font-size: 18px; margin-bottom: 10px; }
        button, input[type="submit"] { 
            background: #007bff; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer;
            font-size: 14px;
        }
        button:hover { background: #0056b3; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin: 10px 0; }
        .error { color: #721c24; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        input[type="file"] { padding: 8px; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        code { background: #eee; padding: 2px 5px; border-radius: 2px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Sincronizar Datos de Películas</h1>
        
        <div class="section">
            <h2>📥 Exportar Películas (Crear backup)</h2>
            <p>Descarga todas tus películas en formato JSON para compartir con tu equipo.</p>
            <form method="GET">
                <input type="hidden" name="action" value="export">
                <button type="submit">Descargar Películas (JSON)</button>
            </form>
        </div>
        
        <div class="section">
            <h2>📤 Importar Películas (Restaurar backup)</h2>
            <p>Carga un archivo JSON de películas que tu compañero haya exportado.</p>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="import">
                <input type="file" name="archivo_json" accept=".json" required>
                <button type="submit" style="display: block; margin-top: 10px;">Importar Películas</button>
            </form>
        </div>
        
        <div class="section">
            <h2>ℹ️ Instrucciones</h2>
            <ol>
                <li>Una persona: <strong>Exporta</strong> sus películas (descarga el JSON)</li>
                <li>Comparte el archivo JSON con tu compañero (email, WhatsApp, etc)</li>
                <li>Tu compañero: <strong>Importa</strong> el archivo JSON en su sistema</li>
                <li>¡Listo! Ambos tienen los mismos datos</li>
            </ol>
        </div>
        
        <p style="text-align: center; margin-top: 30px;">
            <a href="admin.php">← Volver al panel de admin</a>
        </p>
    </div>

<?php

// EXPORTAR
if ($accion === 'export') {
    $sql = "SELECT id_peliculas, nombre, genero, duracion, `fecha-estreno`, descripcion, pais, idioma, imagen FROM peliculas";
    $resultado = $conexion->query($sql);
    
    $peliculas = [];
    while ($row = $resultado->fetch_assoc()) {
        // Convertir BLOB a base64 para transportarlo
        $row['imagen'] = base64_encode($row['imagen']);
        $peliculas[] = $row;
    }
    
    // Descargar como JSON
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="cinecritix_peliculas_' . date('Y-m-d_H-i-s') . '.json"');
    echo json_encode($peliculas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// IMPORTAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_json'])) {
    $archivo = $_FILES['archivo_json']['tmp_name'];
    $contenido = file_get_contents($archivo);
    $peliculas = json_decode($contenido, true);
    
    if (!$peliculas) {
        echo '<div class="error">✗ El archivo JSON no es válido</div>';
    } else {
        $importadas = 0;
        $errores = [];
        
        foreach ($peliculas as $peli) {
            // Decodificar imagen desde base64
            $imagen_blob = base64_decode($peli['imagen']);
            
            $stmt = $conexion->prepare(
                "INSERT INTO peliculas (nombre, genero, duracion, `fecha-estreno`, descripcion, imagen, pais, idioma) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE imagen=VALUES(imagen)"
            );
            
            if ($stmt) {
                $stmt->bind_param(
                    'ssisssss',
                    $peli['nombre'],
                    $peli['genero'],
                    $peli['duracion'],
                    $peli['fecha-estreno'],
                    $peli['descripcion'],
                    $imagen_blob,
                    $peli['pais'],
                    $peli['idioma']
                );
                
                if ($stmt->execute()) {
                    $importadas++;
                } else {
                    $errores[] = $peli['nombre'] . ': ' . $stmt->error;
                }
                $stmt->close();
            }
        }
        
        echo '<div class="success">✓ Importadas ' . $importadas . ' películas</div>';
        if (!empty($errores)) {
            echo '<div class="error">Errores:<ul>';
            foreach ($errores as $err) {
                echo '<li>' . htmlspecialchars($err) . '</li>';
            }
            echo '</ul></div>';
        }
    }
}

$conexion->close();
?>

    </body>
</html>
