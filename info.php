<?php
session_start(); // Para identificar usuario logueado
// Conexión y obtención de datos
require_once __DIR__ . '/conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM peliculas WHERE id_peliculas = $id";
    $resultado = $conexion->query($sql);
    if ($resultado->num_rows > 0) {
        $pelicula = $resultado->fetch_assoc();
    } else {
        echo "Película no encontrada.";
        exit;
    }
} else {
    echo "ID no especificado.";
    exit;
}

$search_value = '';
$page_title = isset($pelicula['nombre']) ? $pelicula['nombre'] : 'Información';
$extra_head = '<link rel="stylesheet" href="css/informacion.css?v=6">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" crossorigin="anonymous" />
<style>header{background:none!important;background-image:none!important;min-height:auto!important;}</style>';
$no_banner = true;
include __DIR__ . '/includes/header.php';
?>

<!-- info peli -->
<div class="detalles">
    <?php if (!empty($pelicula['imagen_path'])): ?>
        <img src="<?php echo htmlspecialchars($pelicula['imagen_path']); ?>" alt="<?php echo htmlspecialchars($pelicula['nombre']); ?>">
    <?php else: ?>
        <img src="data:image/jpeg;base64,<?php echo base64_encode($pelicula['imagen']); ?>" alt="<?php echo htmlspecialchars($pelicula['nombre']); ?>">
    <?php endif; ?>
    <div class="info">
        <h1><?php echo htmlspecialchars($pelicula['nombre']); ?></h1>
        <div class="meta">
            <span><strong>Género:</strong> <?php echo htmlspecialchars($pelicula['genero']); ?></span>
            <span><strong>Director:</strong> <?php echo htmlspecialchars($pelicula['director'] ?? 'N/A'); ?></span>
            <span><strong>Duración:</strong> <?php echo htmlspecialchars($pelicula['duracion']); ?> min</span>
            <span><strong>Estreno:</strong> <?php echo htmlspecialchars($pelicula['fecha_estreno']); ?></span>
        </div>
        <p class="descripcion"><?php echo htmlspecialchars($pelicula['descripcion']); ?></p>
        
        <?php
        // Comprobar si la película está en favoritos del usuario logueado
        $is_fav = false;
        if (isset($_SESSION['id_usuarios'])) {
            $fav_stmt = $conexion->prepare("SELECT id_favorito FROM favoritos WHERE id_usuarios = ? AND id_peliculas = ?");
            if ($fav_stmt) {
                $fav_stmt->bind_param('ii', $_SESSION['id_usuarios'], $pelicula['id_peliculas']);
                $fav_stmt->execute();
                $fav_stmt->store_result();
                if ($fav_stmt->num_rows > 0) $is_fav = true;
                $fav_stmt->close();
            }
        }
        ?>
        
        <?php if (isset($_SESSION['id_usuarios'])): ?>
            <form method="POST" action="toggle_favorito.php" style="margin-top:16px; display:inline;">
                <input type="hidden" name="id_peliculas" value="<?php echo intval($pelicula['id_peliculas']); ?>">
                <button type="submit" class="btn-fav">
                    <?php echo $is_fav ? '❤️ Quitar de favoritos' : '🤍 Agregar a favoritos'; ?>
                </button>
            </form>
        <?php else: ?>
            <p style="margin-top:16px; color:#ff0040; font-weight:bold;"><i class="fas fa-lock"></i> Inicia sesión para marcar como favorito</p>
        <?php endif; ?>
    </div>
</div>


<!-- seccion comentario -->

<div class="comentarios-section">
    <h2>Opiniones de los usuarios</h2>

    <?php
    if (isset($_SESSION['id_usuarios'])) {
    ?>
        <form action="guardar_comentario.php" method="POST" class="form-comentario">
            <input type="hidden" name="id_peliculas" value="<?php echo intval($pelicula['id_peliculas']); ?>">
            <label class="valoracion-label">Tu valoración:</label>
            <div class="estrellas-input">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="estrella<?php echo $i; ?>" name="puntuacion" value="<?php echo $i; ?>">
                    <label for="estrella<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                <?php endfor; ?>
            </div>
            <textarea name="comentario" placeholder="Escribe tu opinión sobre la película..." required></textarea>
            <button type="submit">Enviar comentario</button>
        </form>
    <?php
    } else {
        echo '<p class="aviso-login"><a href="login.php">Iniciá sesión</a> para dejar tu comentario.</p>';
    }
    ?>

    <?php
    // funcion para mostrar comentarios anidados 
    function mostrar_comentarios($conexion, $id_pelicula, $id_padre = null, $nivel = 0) {
        $id_pelicula = intval($id_pelicula);

        if ($nivel === 0) {
            // Trae comentarios principales y la posible puntuación del autor
            $query = "SELECT c.id, c.comentario, c.fecha, c.id_usuarios, u.usuario, v.puntuacion
                      FROM comentarios c
                      JOIN usuarios u ON c.id_usuarios = u.id_usuarios
                      LEFT JOIN valoraciones v ON c.id_usuarios = v.id_usuarios AND c.id_peliculas = v.id_peliculas
                      WHERE c.id_peliculas = $id_pelicula AND c.id_comentario_padre IS NULL
                      ORDER BY c.fecha ASC";
        } else {
            // Para respuestas no necesitamos la puntuación (la mostramos sólo en nivel 0)
            $query = "SELECT c.id, c.comentario, c.fecha, c.id_usuarios, u.usuario
                      FROM comentarios c
                      JOIN usuarios u ON c.id_usuarios = u.id_usuarios
                      WHERE c.id_peliculas = $id_pelicula AND c.id_comentario_padre = $id_padre
                      ORDER BY c.fecha ASC";
        }

        $res = mysqli_query($conexion, $query);

        if ($res && mysqli_num_rows($res) > 0) {
            echo "<div class='nivel-$nivel'>";

            while ($fila = mysqli_fetch_assoc($res)) {
                echo "<div class='comentario' data-id='".intval($fila['id'])."'>";
                echo "<div class='comentario-header'>";
                echo "<strong>" . htmlspecialchars($fila['usuario']) . "</strong>";
                echo "<span class='fecha'> (" . $fila['fecha'] . ")</span>";

                // Mostrar estrellas solo si estamos en nivel 0 y existe puntuación
                if ($nivel === 0 && !empty($fila['puntuacion'])) {
                    echo "<span class='valoracion'>";
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $fila['puntuacion'] ? "<i class='fas fa-star'></i>" : "<i class='far fa-star'></i>";
                    }
                    echo "</span>";
                }

                echo "</div>"; // cierre header
                echo "<p>" . nl2br(htmlspecialchars($fila['comentario'])) . "</p>";

                // Formulario para responder (si está logueado)
                if (isset($_SESSION['id_usuarios'])) {
                    echo '<button type="button" class="btn-responder">Responder</button>';
                    echo '<form action="guardar_comentario.php" method="POST" class="form-respuesta" style="display:none;">
                            <input type="hidden" name="id_peliculas" value="'.intval($id_pelicula).'">
                            <input type="hidden" name="id_comentario_padre" value="'.intval($fila['id']).'">
                            <textarea name="comentario" placeholder="Responder..." required></textarea>
                            <button type="submit">Responder</button>
                          </form>';
                }

                mostrar_comentarios($conexion, $id_pelicula, $fila['id'], $nivel + 1);

                echo "</div>"; 
            }

            echo "</div>"; 
        }
    }

    echo "<div class='lista-comentarios'>";
    mostrar_comentarios($conexion, $pelicula['id_peliculas']);
    echo "</div>";
    ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
//muestra o oculta textarea 
document.querySelectorAll('.btn-responder').forEach(btn => {
    btn.addEventListener('click', () => {
        const form = btn.nextElementSibling;
        
        form.style.display = (form.style.display === "block") ? "none" : "block";
        if (form.style.display === "block") {
            const ta = form.querySelector('textarea');
            if (ta) ta.focus();
        }
    });
});
</script>
