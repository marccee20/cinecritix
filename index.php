<?php 
session_start();
include("conexion.php");

// Helper: obtener URL de avatar (busca archivos en imagenes/avatars/{id}.{ext})
function avatar_url($id) {
  $baseDir = __DIR__ . '/imagenes/avatars/';
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
    <title>cinecritx</title>
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
              <?php $avatar = avatar_url($_SESSION['id_usuarios'] ?? 0); ?>
              <a href="perfil2.php" class="avatar-link">
                <span class="nav-username"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                <?php if ($avatar): ?>
                  <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="user-avatar" />
                <?php endif; ?>
              </a>
              <a href="logout.php">Cerrar sesión</a>
              <a href="#peliculas">Películas</a>
            <?php else: ?>
              <!-- Si el usuario NO está logueado -->
              <a href="login.php">Iniciar sesión</a>
              <a href="cuenta.php">Crear cuenta</a>
              <a href="#peliculas">Películas</a>
            <?php endif; ?>
          </div>
        </div>
      </nav>

      <div class="banner">
        <div class="container">
          <h1 class="banner-title">cinecritx</h1>
          <p>siempre hay algo nuevo para ver</p>
          <form action="buscar.php" method="GET" id="search-form">
            <div class="search-container">
              <input type="text" class="search-input" id="search-input" name="q" placeholder="buscar..." autocomplete="off">
              <div id="suggestions-list" class="suggestions-list"></div>
            </div>
            <button type="submit" class="search-btn">
              <i class="fas fa-search"></i>
            </button>
          </form>
        </div>
      </div>
    </header>

    <!-- películas -->
    <div class="title">
      <h2 id="peliculas">Películas populares</h2>
    </div>

    <div class="blog-content">
      <?php
        $sql = "SELECT * FROM peliculas";
        $resultado = $conexion->query($sql);

        while ($pelicula = $resultado->fetch_assoc()) {
      ?>
        <div class="blog-item">
          <div class="blog-img">
            <img 
              src="data:image/jpeg;base64,<?php echo base64_encode($pelicula['imagen']); ?>" 
              alt="<?php echo htmlspecialchars($pelicula['nombre']); ?>"
            >
          </div>
          <div class="blog-text">
            <h2><?php echo $pelicula['nombre']; ?></h2>
            <a href="info.php?id=<?php echo urlencode($pelicula['id_peliculas']); ?>">Leer más</a>
          </div>
        </div>
      <?php } ?>
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

    <script>
      const searchInput = document.getElementById('search-input');
      const suggestionsList = document.getElementById('suggestions-list');
      let debounceTimer;

      // Función para obtener sugerencias
      function getSuggestions(query) {
        if (query.length < 2) {
          suggestionsList.innerHTML = '';
          return;
        }

        fetch('api_buscar.php?q=' + encodeURIComponent(query))
          .then(response => response.json())
          .then(data => {
            displaySuggestions(data);
          })
          .catch(error => console.error('Error:', error));
      }

      // Función para mostrar sugerencias
      function displaySuggestions(results) {
        suggestionsList.innerHTML = '';

        if (results.length === 0) {
          suggestionsList.innerHTML = '<div class="suggestion-item no-results">No se encontraron películas</div>';
          return;
        }

        results.forEach(movie => {
          const item = document.createElement('div');
          item.className = 'suggestion-item';
          // Construir contenido con miniatura si existe
          let thumbHtml = '';
          if (movie.imagen) {
            thumbHtml = `<img src="data:image/jpeg;base64,${movie.imagen}" class="suggestion-thumb" alt="${escapeHtml(movie.nombre)}">`;
          } else {
            thumbHtml = `<div class="suggestion-thumb" style="background:#eee"></div>`;
          }

          item.innerHTML = `<a href="info.php?id=${encodeURIComponent(movie.id_peliculas)}">${thumbHtml}<span class="suggestion-name">${escapeHtml(movie.nombre)}</span></a>`;

          item.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'info.php?id=' + encodeURIComponent(movie.id_peliculas);
          });

          suggestionsList.appendChild(item);
        });
      }

      // Función para escapar caracteres HTML
      function escapeHtml(text) {
        const map = {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
      }

      // Event listener para el input con debounce
      searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
          getSuggestions(this.value);
        }, 300);
      });

      // Cerrar sugerencias al hacer clic fuera
      document.addEventListener('click', function(e) {
        if (e.target !== searchInput) {
          suggestionsList.innerHTML = '';
        }
      });

      // Permitir navegar con Enter
      searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && this.value.length > 0) {
          document.getElementById('search-form').submit();
        }
      });
    </script>

  </body>
</html>
