<?php 
$search_value = '';
$page_title = 'cinecritx';
$show_banner = true;
include __DIR__ . '/includes/header.php';
?>

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

<?php include __DIR__ . '/includes/footer.php'; ?>
