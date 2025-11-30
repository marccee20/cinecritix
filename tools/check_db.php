<?php
$c = new mysqli('localhost', 'root', '', 'peliculas_proyecto');
$r = $c->query("SELECT id_peliculas, nombre, imagen_path, imagen_thumb FROM peliculas WHERE imagen_path IS NOT NULL LIMIT 5");
while ($row = $r->fetch_assoc()) {
    echo "ID " . $row['id_peliculas'] . " | " . $row['nombre'] . " | Path: " . $row['imagen_path'] . " | Thumb: " . $row['imagen_thumb'] . "\n";
}
$c->close();
?>
