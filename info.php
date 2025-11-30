<?php
// ======================================
// Conexión y obtención de datos
// ======================================
include("conexion.php");
session_start(); // Para identificar usuario logueado

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($pelicula['nombre']); ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" crossorigin="anonymous" />
<link rel="stylesheet" href="css/informacion.css?v=6">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="container">
        <a href="index.php" class="navbar-brand">cinecritx</a>
        <div class="navbar-nav">
            <a href="login.php">iniciar sesión</a>
            <a href="cuenta.php">crear cuenta</a>
            <a href="#peliculas">películas</a>
        </div>
    </div>
</nav>

<!-- DETALLES DE LA PELÍCULA -->
<div class="detalles">
    <img src="data:image/jpeg;base64,<?php echo base64_encode($pelicula['imagen']); ?>" alt="<?php echo htmlspecialchars($pelicula['nombre']); ?>">
    <div class="info">
        <h1><?php echo htmlspecialchars($pelicula['nombre']); ?></h1>
        <div class="meta">
            <span><strong>Género:</strong> <?php echo htmlspecialchars($pelicula['genero']); ?></span>
            <span><strong>Duración:</strong> <?php echo htmlspecialchars($pelicula['duracion']); ?> min</span>
            <span><strong>Estreno:</strong> <?php echo htmlspecialchars($pelicula['fecha-estreno']); ?></span>
        </div>
        <p class="descripcion"><?php echo htmlspecialchars($pelicula['descripcion']); ?></p>
    </div>
</div>

<!-- =============================== -->
<!-- SECCIÓN DE OPINIONES DE USUARIOS -->
<!-- =============================== -->
<div class="comentarios-section">
    <h2>Opiniones de los usuarios</h2>

    <?php
    // Si el usuario está logueado, mostrar formulario principal
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
    // ===============================
    // FUNCIÓN RECURSIVA PARA MOSTRAR COMENTARIOS ANIDADOS
    // ===============================
    function mostrar_comentarios($conexion, $id_pelicula, $id_padre = null, $nivel = 0) {
        $id_pelicula = intval($id_pelicula);

        // NOTA: obtenemos la puntuación solo si queremos mostrarla en nivel 0.
        // Para evitar joins innecesarios en niveles > 0, usamos una consulta diferente según el nivel.
        if ($nivel === 0) {
            // Trae comentarios principales y la posible puntuación del autor (si existe)
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

                // Mostrar estrellas SOLO si estamos en nivel 0 y existe puntuación
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

                // Llamada recursiva para mostrar respuestas de este comentario
                // Si $nivel === 0 pasamos $fila['id'] como id_padre; si nivel>0 la consulta ya filtra por id_padre.
                mostrar_comentarios($conexion, $id_pelicula, $fila['id'], $nivel + 1);

                echo "</div>"; // cierre comentario
            }

            echo "</div>"; // cierre nivel
        }
    }

    // ===============================
    // MOSTRAR COMENTARIOS DE LA PELÍCULA
    // ===============================
    echo "<div class='lista-comentarios'>";
    mostrar_comentarios($conexion, $pelicula['id_peliculas']);
    echo "</div>";
    ?>
</div>

<footer>
    <div class="social-links">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-pinterest"></i></a>
    </div>
    <span>cinecritx</span>
</footer>

<script>
// Mostrar/ocultar textarea al presionar "Responder"
document.querySelectorAll('.btn-responder').forEach(btn => {
    btn.addEventListener('click', () => {
        const form = btn.nextElementSibling;
        // Si el form está inline en el DOM y oculto por CSS, lo alternamos.
        form.style.display = (form.style.display === "block") ? "none" : "block";
        if (form.style.display === "block") {
            const ta = form.querySelector('textarea');
            if (ta) ta.focus();
        }
    });
});
</script>

</body>
</html>
