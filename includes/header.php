<?php
if (session_status() == PHP_SESSION_NONE) session_start();
// Aseguramos la conexión
require_once __DIR__ . '/../conexion.php';
// `$search_value` puede venir de la página que incluye este header
$search_value = isset($search_value) ? $search_value : '';
// Permitir ocultar el banner (fondo gladiador) con $no_banner = true
$no_banner = isset($no_banner) ? (bool)$no_banner : false;
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
  <body>

    <!-- header -->
    <header class="<?php echo $no_banner ? 'no-banner' : ''; ?>">
      <nav class="navbar">
        <div class="container">
          <a href="index.php" class="navbar-brand">cinecritx</a>
          <div class="navbar-nav">
            <?php if (isset($_SESSION['usuario'])): ?>
              <a href=""><?php echo htmlspecialchars($_SESSION['usuario']); ?></a>
              <a href="logout.php">Cerrar sesión</a>
              <a href="http://localhost/cinecritix/peliculas.php">Películas</a>
            <?php else: ?>
              <a href="login.php">Iniciar sesión</a>
              <a href="cuenta.php">Crear cuenta</a>
              <a href="http://localhost/cinecritix/peliculas.php">Películas</a>
            <?php endif; ?>
          </div>
        </div>
      </nav>


    </header>
