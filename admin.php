<?php
/**
 * Panel de administración simple
 * Para agregar nuevas películas a la base de datos
 */
session_start();
include("conexion.php");
$no_banner = true;

// Procesar formulario
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $genero = isset($_POST['genero']) ? trim($_POST['genero']) : '';
    $duracion = isset($_POST['duracion']) ? (int)$_POST['duracion'] : 0;
    $fecha_estreno = isset($_POST['fecha_estreno']) ? trim($_POST['fecha_estreno']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $director = isset($_POST['director']) ? trim($_POST['director']) : '';
    $pais = isset($_POST['pais']) ? trim($_POST['pais']) : '';
    $idioma = isset($_POST['idioma']) ? trim($_POST['idioma']) : '';

    // Procesar imagen
    $imagen_blob = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen_blob = file_get_contents($_FILES['imagen']['tmp_name']);
    }

    if (!empty($nombre) && $imagen_blob) {
        $stmt = $conexion->prepare(
            "INSERT INTO peliculas (nombre, genero, duracion, `fecha_estreno`, descripcion, director, imagen, pais, idioma) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        $stmt->bind_param(
            'ssissssss',
            $nombre,
            $genero,
            $duracion,
            $fecha_estreno,
            $descripcion,
            $director,
            $imagen_blob,
            $pais,
            $idioma
        );
        
        if ($stmt->execute()) {
            $mensaje = '<div style="padding:10px; background:#d4edda; color:#155724; border-radius:4px; margin-bottom:20px;">&#10003; Película agregada exitosamente</div>';
        } else {
            $mensaje = '<div style="padding:10px; background:#f8d7da; color:#721c24; border-radius:4px; margin-bottom:20px;">&#10007; Error: ' . htmlspecialchars($stmt->error) . '</div>';
        }
        $stmt->close();
    } else {
        $mensaje = '<div style="padding:10px; background:#f8d7da; color:#721c24; border-radius:4px; margin-bottom:20px;">&#10007; Completa todos los campos (nombre, descripción e imagen)</div>';
    }
}

// Obtener lista de películas
$sql = "SELECT id_peliculas, nombre, genero, duracion FROM peliculas ORDER BY nombre";
$resultado = $conexion->query($sql);
$peliculas = $resultado->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Panel de Administración - CineCritix</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        h1, h2 { margin: 20px 0 10px; color: #333; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { 
            width: 100%; 
            padding: 8px; 
            border: 1px solid #ddd; 
            border-radius: 4px;
            font-family: Arial, sans-serif;
        }
        textarea { resize: vertical; min-height: 100px; }
        button { 
            background: #007bff; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer;
            font-size: 14px;
        }
        button:hover { background: #0056b3; }
        .form-section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        tr:hover { background: #f9f9f9; }
        .btn-delete {
            background: #dc3545;
            padding: 5px 10px;
            font-size: 12px;
        }
        .btn-delete:hover { background: #c82333; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Volver a la página principal</a>
        
        <h1>Panel de Administración</h1>
        
        <?php echo $mensaje; ?>
        
        <div style="margin-bottom: 20px; text-align: right;">
            <a href="sync_data.php" style="background: #28a745; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; display: inline-block;">
                🔄 Sincronizar Datos
            </a>
        </div>
        
        <div class="form-section">
            <h2>Agregar Nueva Película</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nombre de la película *</label>
                    <input type="text" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label>Género</label>
                    <input type="text" name="genero" placeholder="Ej: Acción, Ciencia Ficción">
                </div>
                
                <div class="form-group">
                    <label>Director</label>
                    <input type="text" name="director" placeholder="Nombre del director">
                </div>
                
                <div class="form-group">
                    <label>Duración (minutos)</label>
                    <input type="number" name="duracion" min="0">
                </div>
                
                <div class="form-group">
                    <label>Fecha de estreno</label>
                    <input type="date" name="fecha_estreno">
                </div>
                
                <div class="form-group">
                    <label>País</label>
                    <input type="text" name="pais" placeholder="Ej: USA, México">
                </div>
                
                <div class="form-group">
                    <label>Idioma</label>
                    <input type="text" name="idioma" placeholder="Ej: Español, Inglés">
                </div>
                
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" placeholder="Breve descripción de la película..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Imagen (JPG, PNG, WebP) *</label>
                    <input type="file" name="imagen" accept="image/*" required>
                </div>
                
                <button type="submit">Agregar Película</button>
            </form>
        </div>
        
        <div class="form-section">
            <h2>Películas en la Base de Datos (<?php echo count($peliculas); ?>)</h2>
            
            <?php if (count($peliculas) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Género</th>
                            <th>Duración</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($peliculas as $peli): ?>
                            <tr>
                                <td><?php echo $peli['id_peliculas']; ?></td>
                                <td><?php echo htmlspecialchars($peli['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($peli['genero'] ?? 'N/A'); ?></td>
                                <td><?php echo $peli['duracion'] ? $peli['duracion'] . ' min' : 'N/A'; ?></td>
                                <td>
                                    <a href="info.php?id=<?php echo $peli['id_peliculas']; ?>" style="color: #007bff;">Ver</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay películas en la base de datos. Agrega la primera.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
