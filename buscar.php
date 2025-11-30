<?php 
session_start();
include("conexion.php");

// Obtener el término de búsqueda
$termino = isset($_GET['q']) ? trim($_GET['q']) : '';
$resultados = [];

// Si hay un término, buscar en la base de datos
if (!empty($termino)) {
    $termino_escapado = $conexion->real_escape_string($termino);
    $sql = "SELECT * FROM peliculas WHERE nombre LIKE '%{$termino_escapado}%' ORDER BY nombre";
    $resultado = $conexion->query($sql);
    
    if ($resultado) {
        while ($pelicula = $resultado->fetch_assoc()) {
            $resultados[] = $pelicula;
        }
    }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Búsqueda - cinecritx</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous" />
    <link rel="stylesheet" href="css/style.css">
  </head>
  <body>

    <!-- header -->
    <header>
      <nav class="navbar">
        <div class="container">
          <a href="index.php" class="navbar-brand">cinecritx</a>
          <div class="navbar-nav">
            <?php if (isset($_SESSION['usuario'])): ?>
              <!-- Si el usuario está logueado -->
              <a href=""><?php echo htmlspecialchars($_SESSION['usuario']); ?></a>
              <a href="logout.php">Cerrar sesión</a>
              <a href="index.php#peliculas">Películas</a>
            <?php else: ?>
              <!-- Si el usuario NO está logueado -->
              <a href="login.php">Iniciar sesión</a>
              <a href="cuenta.php">Crear cuenta</a>
              <a href="index.php#peliculas">Películas</a>
            <?php endif; ?>
          </div>
        </div>
      </nav>

      <div class="banner">
        <div class="container">
          <h1 class="banner-title">cinecritx</h1>
          <p>siempre hay algo nuevo para ver</p>
          <form action="buscar.php" method="GET">
            <input type="text" class="search-input" name="q" placeholder="buscar..." value="<?php echo htmlspecialchars($termino); ?>" required>
            <button type="submit" class="search-btn">
              <i class="fas fa-search"></i>
            </button>
          </form>
        </div>
      </div>
    </header>

    <!-- resultados de búsqueda -->
    <div class="title">
      <h2>
        <?php 
          if (!empty($termino)) {
              echo "Resultados para: <strong>" . htmlspecialchars($termino) . "</strong>";
          } else {
              echo "Ingresa un término para buscar";
          }
        ?>
      </h2>
    </div>

    <div class="blog-content">
      <?php
        if (!empty($termino)) {
            if (count($resultados) > 0) {
                foreach ($resultados as $pelicula) {
      ?>
        <div class="blog-item">
          <div class="blog-img">
            <img 
              src="data:image/jpeg;base64,<?php echo base64_encode($pelicula['imagen']); ?>" 
              alt="<?php echo htmlspecialchars($pelicula['nombre']); ?>"
            >
          </div>
          <div class="blog-text">
            <h2><?php echo htmlspecialchars($pelicula['nombre']); ?></h2>
            <a href="info.php?id=<?php echo urlencode($pelicula['id_peliculas']); ?>">Leer más</a>
          </div>
        </div>
      <?php 
                }
            } else {
      ?>
        <div style="text-align: center; padding: 50px; color: #666;">
          <p><strong>No se encontraron películas que coincidan con tu búsqueda.</strong></p>
          <p><a href="index.php">Volver a películas populares</a></p>
        </div>
      <?php 
            }
        }
      ?>
    </div>

    <!-- footer -->
    <footer>
      <div class="social-links">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-pinterest"></i></a>
      </div>
      <span>cinecritx</span>
    </footer>

  </body>
</html>
