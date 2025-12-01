<?php
/**
 * Panel de administración simple
 * Para agregar nuevas películas a la base de datos
 */
session_start();
include("conexion.php");

// Manejar eliminación antes de cualquier otro POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del_id = (int)$_POST['delete_id'];
    if ($del_id > 0) {
        $d = $conexion->prepare("DELETE FROM peliculas WHERE id_peliculas = ?");
        $d->bind_param('i', $del_id);
        if ($d->execute()) {
            $d->close();
            header('Location: admin.php');
            exit;
        } else {
            $mensaje = '<div style="padding:10px; background:#f8d7da; color:#721c24; border-radius:4px; margin-bottom:20px;">&#10007; Error al eliminar: ' . htmlspecialchars($d->error) . '</div>';
            $d->close();
        }
    }
}

// Procesar formulario
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pelicula = isset($_POST['id_peliculas']) ? (int)$_POST['id_peliculas'] : 0;
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $genero = isset($_POST['genero']) ? trim($_POST['genero']) : '';
    $duracion = isset($_POST['duracion']) ? (int)$_POST['duracion'] : 0;
    $fecha_estreno = isset($_POST['fecha_estreno']) ? trim($_POST['fecha_estreno']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $director = isset($_POST['director']) ? trim($_POST['director']) : '';
    $pais = isset($_POST['pais']) ? trim($_POST['pais']) : '';
    $idioma = isset($_POST['idioma']) ? trim($_POST['idioma']) : '';

    // Procesar imagen (si se sube una nueva)
    $imagen_blob = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen_blob = file_get_contents($_FILES['imagen']['tmp_name']);
    }

    // Si viene id_peliculas > 0 -> actualizar, si no -> insertar
    if ($id_pelicula > 0) {
        // Si no subieron una nueva imagen, recuperar la existente
        if ($imagen_blob === null) {
            $q = $conexion->prepare("SELECT imagen FROM peliculas WHERE id_peliculas = ? LIMIT 1");
            $q->bind_param('i', $id_pelicula);
            $q->execute();
            $q->bind_result($existing_img);
            if ($q->fetch()) {
                $imagen_blob = $existing_img;
            } else {
                $imagen_blob = null;
            }
            $q->close();
        }

        if (!empty($nombre)) {
            $stmt = $conexion->prepare(
                "UPDATE peliculas SET nombre = ?, genero = ?, duracion = ?, fecha_estreno = ?, descripcion = ?, director = ?, imagen = ?, pais = ?, idioma = ? WHERE id_peliculas = ?"
            );
            if ($stmt) {
                $stmt->bind_param(
                    'ssissssssi',
                    $nombre,
                    $genero,
                    $duracion,
                    $fecha_estreno,
                    $descripcion,
                    $director,
                    $imagen_blob,
                    $pais,
                    $idioma,
                    $id_pelicula
                );

                if ($stmt->execute()) {
                    $mensaje = '<div style="padding:10px; background:#d4edda; color:#155724; border-radius:4px; margin-bottom:20px;">&#10003; Película actualizada correctamente</div>';
                } else {
                    $mensaje = '<div style="padding:10px; background:#f8d7da; color:#721c24; border-radius:4px; margin-bottom:20px;">&#10007; Error al actualizar: ' . htmlspecialchars($stmt->error) . '</div>';
                }
                $stmt->close();
            }
        } else {
            $mensaje = '<div style="padding:10px; background:#f8d7da; color:#721c24; border-radius:4px; margin-bottom:20px;">&#10007; El nombre no puede estar vacío</div>';
        }
    } else {
        // Insert
        if (!empty($nombre) && $imagen_blob) {
            $stmt = $conexion->prepare(
                "INSERT INTO peliculas (nombre, genero, duracion, fecha_estreno, descripcion, director, imagen, pais, idioma) 
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
}

// Obtener lista de películas
$sql = "SELECT id_peliculas, nombre, genero, duracion, director FROM peliculas ORDER BY nombre";
$resultado = $conexion->query($sql);
$peliculas = $resultado->fetch_all(MYSQLI_ASSOC);

// Preparar valores para el formulario (edición)
$form = [
    'id_peliculas' => 0,
    'nombre' => '',
    'genero' => '',
    'director' => '',
    'duracion' => '',
    'fecha_estreno' => '',
    'pais' => '',
    'idioma' => '',
    'descripcion' => ''
];

if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    if ($edit_id > 0) {
        $q = $conexion->prepare("SELECT id_peliculas, nombre, genero, director, duracion, fecha_estreno, pais, idioma, descripcion FROM peliculas WHERE id_peliculas = ? LIMIT 1");
        $q->bind_param('i', $edit_id);
        $q->execute();
        $res = $q->get_result();
        if ($res && $res->num_rows > 0) {
            $form = $res->fetch_assoc();
        }
        $q->close();
    }
}
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
            <h2><?php echo $form['id_peliculas'] ? 'Editar Película' : 'Agregar Nueva Película'; ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_peliculas" value="<?php echo (int)$form['id_peliculas']; ?>">
                <div class="form-group">
                    <label>Nombre de la película *</label>
                    <input type="text" name="nombre" required value="<?php echo htmlspecialchars($form['nombre']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Género</label>
                    <input type="text" name="genero" placeholder="Ej: Acción, Ciencia Ficción">
                </div>
                
                <div class="form-group">
                    <label>Director</label>
                    <input type="text" name="director" placeholder="Nombre del director" value="<?php echo htmlspecialchars($form['director']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Duración (minutos)</label>
                    <input type="number" name="duracion" min="0" value="<?php echo htmlspecialchars($form['duracion']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Fecha de estreno</label>
                    <input type="date" name="fecha_estreno" value="<?php echo htmlspecialchars($form['fecha_estreno']); ?>">
                </div>
                
                <div class="form-group">
                    <label>País</label>
                    <input type="text" name="pais" placeholder="Ej: USA, México" value="<?php echo htmlspecialchars($form['pais']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Idioma</label>
                    <input type="text" name="idioma" placeholder="Ej: Español, Inglés" value="<?php echo htmlspecialchars($form['idioma']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" placeholder="Breve descripción de la película..."><?php echo htmlspecialchars($form['descripcion']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Imagen (JPG, PNG, WebP) <?php echo $form['id_peliculas'] ? '(dejar en blanco para mantener la actual)' : '*'; ?></label>
                    <input type="file" name="imagen" accept="image/*" <?php echo $form['id_peliculas'] ? '' : 'required'; ?>>
                </div>
                
                <button type="submit"><?php echo $form['id_peliculas'] ? 'Guardar cambios' : 'Agregar Película'; ?></button>
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
                            <th>Director</th>
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
                                <td><?php echo htmlspecialchars($peli['director'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($peli['genero'] ?? 'N/A'); ?></td>
                                <td><?php echo $peli['duracion'] ? $peli['duracion'] . ' min' : 'N/A'; ?></td>
                                <td>
                                    <a href="info.php?id=<?php echo $peli['id_peliculas']; ?>" style="color: #007bff; margin-right:8px;">Ver</a>
                                    <a href="admin.php?edit=<?php echo $peli['id_peliculas']; ?>" style="color: #28a745; margin-right:8px;">Editar</a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta película? Esta acción no se puede deshacer.');">
                                        <input type="hidden" name="delete_id" value="<?php echo $peli['id_peliculas']; ?>">
                                        <button type="submit" class="btn-delete">Eliminar</button>
                                    </form>
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
