<?php
session_start();
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuarios'])) {
    header('Location: login.php');
    exit;
}

$id = intval($_SESSION['id_usuarios']);

// Datos del usuario
$stmt = $conexion->prepare("SELECT usuario, correo FROM usuarios WHERE id_usuarios = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: login.php');
    exit;
}

// Comentarios del usuario
$comments_stmt = $conexion->prepare("SELECT c.id, c.comentario, c.fecha, p.id_peliculas, p.nombre FROM comentarios c JOIN peliculas p ON c.id_peliculas = p.id_peliculas WHERE c.id_usuarios = ? ORDER BY c.fecha DESC");
$comments_stmt->bind_param('i', $id);
$comments_stmt->execute();
$comments = $comments_stmt->get_result();
$comments_stmt->close();

// Valoraciones del usuario
$ratings_stmt = $conexion->prepare("SELECT v.id_valoracion, v.puntuacion, v.fecha, p.id_peliculas, p.nombre FROM valoraciones v JOIN peliculas p ON v.id_peliculas = p.id_peliculas WHERE v.id_usuarios = ? ORDER BY v.fecha DESC");
$ratings_stmt->bind_param('i', $id);
$ratings_stmt->execute();
$ratings = $ratings_stmt->get_result();
$ratings_stmt->close();

// Favoritos (si existe la tabla 'favoritos')
$has_fav = false;
$fav_list = null;
$check = $conexion->query("SHOW TABLES LIKE 'favoritos'");
if ($check && $check->num_rows > 0) {
    $has_fav = true;
    $fav_stmt = $conexion->prepare("SELECT f.id_favorito, p.id_peliculas, p.nombre FROM favoritos f JOIN peliculas p ON f.id_peliculas = p.id_peliculas WHERE f.id_usuarios = ?");
    $fav_stmt->bind_param('i', $id);
    $fav_stmt->execute();
    $fav_list = $fav_stmt->get_result();
    $fav_stmt->close();
}

$extra_head = '<link rel="stylesheet" href="css/perfil2.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" crossorigin="anonymous" />
<style>header{background:none!important;background-image:none!important;min-height:auto!important;}</style>';
$page_title = 'Mi perfil';
$show_banner = false;
include __DIR__ . '/includes/header.php';
?>

<main class="perfil-container">
    <section class="perfil-header">
        <div class="avatar-box">
            <?php $avatar = avatar_url($id); ?>
            <?php if ($avatar): ?>
                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="perfil-avatar">
            <?php else: ?>
                <div class="perfil-avatar--placeholder"><?php echo strtoupper(substr($user['usuario'], 0, 1)); ?></div>
            <?php endif; ?>
        </div>
        <div class="perfil-info">
            <h2><?php echo htmlspecialchars($user['usuario']); ?></h2>
            <p><?php echo htmlspecialchars($user['correo']); ?></p>

            <form action="subir_avatar.php" method="POST" enctype="multipart/form-data" class="form-avatar">
                <label for="avatar">Cambiar avatar</label>
                <input type="file" name="avatar" id="avatar" accept="image/*" required>
                <button type="submit">Subir</button>
            </form>
        </div>
    </section>

    <section class="perfil-seccion">
        <h3>Películas favoritas</h3>
        <?php if ($has_fav && $fav_list && $fav_list->num_rows > 0): ?>
            <ul class="lista-favoritos">
                <?php while ($f = $fav_list->fetch_assoc()): ?>
                    <li><a href="info.php?id=<?php echo intval($f['id_peliculas']); ?>"><?php echo htmlspecialchars($f['nombre']); ?></a></li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="muted">Aún no tienes películas favoritas. Marca películas como favoritas desde su ficha para que aparezcan aquí.</p>
        <?php endif; ?>
    </section>

    <section class="perfil-seccion">
        <h3>Mis valoraciones</h3>
        <?php if ($ratings && $ratings->num_rows > 0): ?>
            <ul class="lista-valoraciones">
                <?php while ($r = $ratings->fetch_assoc()): ?>
                    <li>
                        <a href="info.php?id=<?php echo intval($r['id_peliculas']); ?>"><?php echo htmlspecialchars($r['nombre']); ?></a>
                        <span class="puntuacion"><?php echo intval($r['puntuacion']); ?>/5</span>
                        <span class="fecha"><?php echo htmlspecialchars($r['fecha']); ?></span>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="muted">No has valorado películas aún.</p>
        <?php endif; ?>
    </section>

    <section class="perfil-seccion">
        <h3>Mis comentarios</h3>
        <?php if ($comments && $comments->num_rows > 0): ?>
            <ul class="lista-comentarios">
                <?php while ($c = $comments->fetch_assoc()): ?>
                    <li>
                        <a href="info.php?id=<?php echo intval($c['id_peliculas']); ?>"><?php echo htmlspecialchars($c['nombre']); ?></a>
                        <p class="comentario-text"><?php echo nl2br(htmlspecialchars($c['comentario'])); ?></p>
                        <span class="fecha"><?php echo htmlspecialchars($c['fecha']); ?></span>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="muted">No has publicado comentarios todavía.</p>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
