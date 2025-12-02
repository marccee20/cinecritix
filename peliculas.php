<?php
session_start();
require_once __DIR__ . '/conexion.php';

$page_title = 'Listado de Películas';
$extra_head = '<style>header{background:none!important;background-image:none!important;min-height:auto!important;}</style>';
$no_banner = true;
include __DIR__ . '/includes/header.php';

// Obtener películas ordenadas por título (nombre) ascendente
$sql = "SELECT id_peliculas, nombre, director, fecha_estreno, imagen, imagen_path FROM peliculas ORDER BY nombre ASC";
$result = $conexion->query($sql);
?>

<div class="container" style="padding: 24px 0;">
  <h2 style="font-family: var(--Playfair); font-size: 32px; margin-bottom: 16px;">Listado de Películas</h2>
  <?php if ($result && $result->num_rows > 0): ?>
    <table style="width:100%; border-collapse: collapse; background:#fff; border-radius:8px; overflow:hidden;">
      <thead>
        <tr style="background:#f8f9fa;">
          <th style="padding:12px; text-align:left; border-bottom:1px solid #ddd;">Portada</th>
          <th style="padding:12px; text-align:left; border-bottom:1px solid #ddd;">Título</th>
          <th style="padding:12px; text-align:left; border-bottom:1px solid #ddd;">Director</th>
          <th style="padding:12px; text-align:left; border-bottom:1px solid #ddd;">Estreno</th>
          <th style="padding:12px; text-align:left; border-bottom:1px solid #ddd;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td style="padding:12px; border-bottom:1px solid #eee; width:80px;">
              <?php if (!empty($row['imagen_path'])): ?>
                <img src="<?php echo htmlspecialchars($row['imagen_path']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>" style="width:64px; height:64px; object-fit:cover; border-radius:6px;" />
              <?php elseif (!empty($row['imagen'])): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['imagen']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>" style="width:64px; height:64px; object-fit:cover; border-radius:6px;" />
              <?php else: ?>
                <span style="color:#888;">Sin imagen</span>
              <?php endif; ?>
            </td>
            <td style="padding:12px; border-bottom:1px solid #eee; font-weight:600;">
              <?php echo htmlspecialchars($row['nombre']); ?>
            </td>
            <td style="padding:12px; border-bottom:1px solid #eee;">
              <?php echo htmlspecialchars($row['director'] ?? 'N/A'); ?>
            </td>
            <td style="padding:12px; border-bottom:1px solid #eee;">
              <?php echo htmlspecialchars($row['fecha_estreno'] ?? ''); ?>
            </td>
            <td style="padding:12px; border-bottom:1px solid #eee;">
              <a href="info.php?id=<?php echo (int)$row['id_peliculas']; ?>" style="color:#007bff; text-decoration:none;">Ver</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No hay películas cargadas.</p>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
