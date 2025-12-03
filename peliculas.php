<?php
session_start();
require_once __DIR__ . '/conexion.php';

$page_title = 'Listado de Películas';
$show_banner = false;
$body_style = 'background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url(/cinecritix/imagenes/R.jpg) center/cover no-repeat fixed; min-height: 100vh;';
$extra_head = '<style>
html, body {
    background: transparent;
}
.search-box {
    margin: 20px 0 30px;
    display: flex;
    justify-content: center;
    gap: 10px;
}
.search-box input {
    padding: 12px 20px;
    border: none;
    border-radius: 25px;
    background: rgba(255, 255, 255, 0.9);
    width: 100%;
    max-width: 500px;
    font-size: 16px;
    outline: none;
    transition: all 0.3s ease;
}
.search-box input:focus {
    background: rgba(255, 255, 255, 1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}
.search-box button {
    padding: 12px 30px;
    border: none;
    border-radius: 25px;
    background: rgba(0, 123, 255, 0.9);
    color: white;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}
.search-box button:hover {
    background: rgba(0, 123, 255, 1);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
}
.clear-search {
    padding: 12px 20px;
    border: none;
    border-radius: 25px;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}
.clear-search:hover {
    background: rgba(220, 53, 69, 1);
    transform: translateY(-2px);
}
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin: 30px 0;
    flex-wrap: wrap;
}
.pagination a, .pagination span {
    padding: 10px 15px;
    background: rgba(255, 255, 255, 0.9);
    color: #333;
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s ease;
    font-weight: 500;
}
.pagination a:hover {
    background: rgba(255, 255, 255, 1);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
.pagination .current {
    background: rgba(0, 123, 255, 0.9);
    color: white;
    font-weight: bold;
}
.pagination .disabled {
    background: rgba(200, 200, 200, 0.5);
    color: #999;
    cursor: not-allowed;
}
</style>';
include __DIR__ . '/includes/header.php';

// Obtener término de búsqueda
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

// Configuración de paginación
$peliculas_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_actual - 1) * $peliculas_por_pagina;

// Preparar consultas con o sin búsqueda
if (!empty($busqueda)) {
    $busqueda_sql = $conexion->real_escape_string($busqueda);
    $sql_count = "SELECT COUNT(*) as total FROM peliculas WHERE nombre LIKE '%$busqueda_sql%' OR director LIKE '%$busqueda_sql%' OR genero LIKE '%$busqueda_sql%'";
    $sql = "SELECT id_peliculas, nombre, director, fecha_estreno, imagen, imagen_path FROM peliculas WHERE nombre LIKE '%$busqueda_sql%' OR director LIKE '%$busqueda_sql%' OR genero LIKE '%$busqueda_sql%' ORDER BY nombre ASC LIMIT $offset, $peliculas_por_pagina";
} else {
    $sql_count = "SELECT COUNT(*) as total FROM peliculas";
    $sql = "SELECT id_peliculas, nombre, director, fecha_estreno, imagen, imagen_path FROM peliculas ORDER BY nombre ASC LIMIT $offset, $peliculas_por_pagina";
}

// Contar total de películas
$result_count = $conexion->query($sql_count);
$total_peliculas = $result_count->fetch_assoc()['total'];
$total_paginas = ceil($total_peliculas / $peliculas_por_pagina);

// Obtener películas de la página actual
$result = $conexion->query($sql);
?>

<div class="container" style="padding: 24px 0; background: transparent;">
  <h2 style="font-family: var(--Playfair); font-size: 32px; margin-bottom: 16px; color: #fff; text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">Listado de Películas</h2>
  
  <!-- Formulario de búsqueda -->
  <form method="GET" action="peliculas.php" class="search-box">
    <input type="text" name="buscar" placeholder="Buscar por título, director o género..." value="<?php echo htmlspecialchars($busqueda); ?>" />
    <button type="submit"><i class="fas fa-search"></i> Buscar</button>
    <?php if (!empty($busqueda)): ?>
      <a href="peliculas.php" class="clear-search"><i class="fas fa-times"></i> Limpiar</a>
    <?php endif; ?>
  </form>

  <?php if (!empty($busqueda)): ?>
    <p style="color: #fff; text-shadow: 1px 1px 2px rgba(0,0,0,0.8); text-align: center; margin-bottom: 20px;">
      Resultados para: <strong>"<?php echo htmlspecialchars($busqueda); ?>"</strong>
      <?php if ($total_peliculas == 0): ?>
        - No se encontraron películas
      <?php else: ?>
        - <?php echo $total_peliculas; ?> película(s) encontrada(s)
      <?php endif; ?>
    </p>
  <?php endif; ?>

  <?php if ($result && $result->num_rows > 0): ?>
    <table style="width:100%; border-collapse: collapse; background:rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px); border-radius:8px; overflow:hidden; box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);">
      <thead>
        <tr style="background:rgba(248, 249, 250, 0.95);">
          <th style="padding:12px; text-align:left; border-bottom:1px solid rgba(221,221,221,0.5);">Portada</th>
          <th style="padding:12px; text-align:left; border-bottom:1px solid rgba(221,221,221,0.5);">Título</th>
          <th style="padding:12px; text-align:left; border-bottom:1px solid rgba(221,221,221,0.5);">Director</th>
          <th style="padding:12px; text-align:left; border-bottom:1px solid rgba(221,221,221,0.5);">Estreno</th>
          <th style="padding:12px; text-align:left; border-bottom:1px solid rgba(221,221,221,0.5);">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr style="background:rgba(255,255,255,0.5);">

            <td style="padding:12px; border-bottom:1px solid rgba(238,238,238,0.3); width:80px;">
              <?php if (!empty($row['imagen_path'])): ?>
                <img src="<?php echo htmlspecialchars($row['imagen_path']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>" style="width:64px; height:64px; object-fit:cover; border-radius:6px;" />
              <?php elseif (!empty($row['imagen'])): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['imagen']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>" style="width:64px; height:64px; object-fit:cover; border-radius:6px;" />
              <?php else: ?>
                <span style="color:#888;">Sin imagen</span>
              <?php endif; ?>
            </td>
            <td style="padding:12px; border-bottom:1px solid rgba(238,238,238,0.3); font-weight:600;">
              <?php echo htmlspecialchars($row['nombre']); ?>
            </td>
            <td style="padding:12px; border-bottom:1px solid rgba(238,238,238,0.3);">
              <?php echo htmlspecialchars($row['director'] ?? 'N/A'); ?>
            </td>
            <td style="padding:12px; border-bottom:1px solid rgba(238,238,238,0.3);">
              <?php echo htmlspecialchars($row['fecha_estreno'] ?? ''); ?>
            </td>
            <td style="padding:12px; border-bottom:1px solid rgba(238,238,238,0.3);">
              <a href="info.php?id=<?php echo (int)$row['id_peliculas']; ?>" style="color:#007bff; text-decoration:none;">Ver</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <!-- Paginación -->
    <?php if ($total_paginas > 1): ?>
      <div class="pagination">
        <?php 
        $url_params = !empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '';
        ?>
        <?php if ($pagina_actual > 1): ?>
          <a href="?pagina=1<?php echo $url_params; ?>">« Primera</a>
          <a href="?pagina=<?php echo $pagina_actual - 1; ?><?php echo $url_params; ?>">‹ Anterior</a>
        <?php else: ?>
          <span class="disabled">« Primera</span>
          <span class="disabled">‹ Anterior</span>
        <?php endif; ?>

        <?php
        // Mostrar números de página
        $rango = 2; // Mostrar 2 páginas antes y después de la actual
        $inicio = max(1, $pagina_actual - $rango);
        $fin = min($total_paginas, $pagina_actual + $rango);

        if ($inicio > 1) {
          echo '<a href="?pagina=1' . $url_params . '">1</a>';
          if ($inicio > 2) echo '<span>...</span>';
        }

        for ($i = $inicio; $i <= $fin; $i++) {
          if ($i == $pagina_actual) {
            echo '<span class="current">' . $i . '</span>';
          } else {
            echo '<a href="?pagina=' . $i . $url_params . '">' . $i . '</a>';
          }
        }

        if ($fin < $total_paginas) {
          if ($fin < $total_paginas - 1) echo '<span>...</span>';
          echo '<a href="?pagina=' . $total_paginas . $url_params . '">' . $total_paginas . '</a>';
        }
        ?>

        <?php if ($pagina_actual < $total_paginas): ?>
          <a href="?pagina=<?php echo $pagina_actual + 1; ?><?php echo $url_params; ?>">Siguiente ›</a>
          <a href="?pagina=<?php echo $total_paginas; ?><?php echo $url_params; ?>">Última »</a>
        <?php else: ?>
          <span class="disabled">Siguiente ›</span>
          <span class="disabled">Última »</span>
        <?php endif; ?>
      </div>

      <p style="text-align: center; color: #fff; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
        Mostrando página <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?> 
        (<?php echo $total_peliculas; ?> películas en total)
      </p>
    <?php endif; ?>

  <?php else: ?>
    <p style="color: #fff; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">No hay películas cargadas.</p>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
