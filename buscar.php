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

$search_value = $termino;
$page_title = 'Buscar películas';
$show_banner = false;
include __DIR__ . '/includes/header.php';
?>

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
            <?php if (!empty($pelicula['imagen_path'])): ?>
              <img src="<?php echo htmlspecialchars($pelicula['imagen_path']); ?>" alt="<?php echo htmlspecialchars($pelicula['nombre']); ?>">
            <?php else: ?>
              <img src="data:image/jpeg;base64,<?php echo base64_encode($pelicula['imagen']); ?>" alt="<?php echo htmlspecialchars($pelicula['nombre']); ?>">
            <?php endif; ?>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
