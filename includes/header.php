<?php
if (session_status() == PHP_SESSION_NONE) session_start();
// Aseguramos la conexión
require_once __DIR__ . '/../conexion.php';
// `$search_value` puede venir de la página que incluye este header
$search_value = isset($search_value) ? $search_value : '';
// Control del banner: usar $show_banner desde la página que incluye este header
// Por defecto no mostrar el banner grande salvo que la página lo solicite explícitamente
if (!isset($show_banner)) {
  $show_banner = false;
}
// Helper: obtener URL de avatar (busca archivos en imagenes/avatars/{id}.{ext})
function avatar_url($id) {
  $baseDir = __DIR__ . '/../imagenes/avatars/';
  $webBase = 'imagenes/avatars/';
  $exts = ['png','jpg','jpeg','webp','gif'];
  foreach ($exts as $e) {
    $f = $baseDir . $id . '.' . $e;
    if (file_exists($f)) return $webBase . $id . '.' . $e;
  }
  return false;
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'cinecritx'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous" />
    <link rel="stylesheet" href="css/style.css">
    <?php
    // Permitir inyectar CSS/JS extra desde la página que incluye este header
    if (isset($extra_head)) echo $extra_head;
    ?>
  </head>
  <body<?php if (isset($body_style)) echo ' style="' . htmlspecialchars($body_style) . '"'; ?>>

    <!-- header -->
    <header class="<?php echo ($show_banner) ? '' : 'no-banner'; ?>">
      <nav class="navbar">
        <div class="container">
          <a href="index.php" class="navbar-brand">cinecritx</a>
          <div class="navbar-nav">
            <?php if (isset($_SESSION['usuario'])): ?>
              <?php $avatar = avatar_url($_SESSION['id_usuarios'] ?? 0); ?>
              <a href="perfil2.php" class="avatar-link">
                <span class="nav-username"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                <?php if ($avatar): ?>
                  <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="user-avatar" />
                <?php endif; ?>
              </a>
              <a href="logout.php">Cerrar sesión</a>
              <?php 
                $peliculas_link = (basename($_SERVER['PHP_SELF']) == 'index.php') ? '#peliculas' : 'peliculas.php';
              ?>
              <a href="<?php echo $peliculas_link; ?>">Películas</a>
            <?php else: ?>
              <a href="login.php">Iniciar sesión</a>
              <a href="cuenta.php">Crear cuenta</a>
              <?php 
                $peliculas_link = (basename($_SERVER['PHP_SELF']) == 'index.php') ? '#peliculas' : 'peliculas.php';
              ?>
              <a href="<?php echo $peliculas_link; ?>">Películas</a>
            <?php endif; ?>
          </div>
        </div>
      </nav>

      <?php if ($show_banner): ?>
      <div class="banner">
        <div class="container">
          <h1 class="banner-title">cinecritx</h1>
          <p>siempre hay algo nuevo para ver</p>
          <form action="buscar.php" method="GET" id="search-form">
            <div class="search-container">
              <input type="text" class="search-input" id="search-input" name="q" placeholder="buscar..." autocomplete="off" value="<?php echo htmlspecialchars($search_value); ?>">
              <div id="suggestions-list" class="suggestions-list"></div>
            </div>
            <button type="submit" class="search-btn">
              <i class="fas fa-search"></i>
            </button>
          </form>
        </div>
      </div>
      <?php endif; ?>
    </header>
