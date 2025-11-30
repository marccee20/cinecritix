<?php
header('Content-Type: application/json; charset=utf-8');
include("conexion.php");

// Obtener el término de búsqueda
$termino = isset($_GET['q']) ? trim($_GET['q']) : '';
$resultados = [];

// Si hay un término, buscar en la base de datos
if (!empty($termino) && strlen($termino) >= 2) {
    $termino_escapado = $conexion->real_escape_string($termino);
    // Traemos también la imagen (binaria) para convertirla a base64 y mostrar miniaturas
    $sql = "SELECT id_peliculas, nombre, imagen FROM peliculas WHERE nombre LIKE '%{$termino_escapado}%' ORDER BY nombre LIMIT 10";
    $resultado = $conexion->query($sql);
    
    if ($resultado) {
        while ($pelicula = $resultado->fetch_assoc()) {
            // Convertir imagen binaria a base64 para enviarla en JSON (si existe)
            if (!empty($pelicula['imagen'])) {
                $pelicula['imagen'] = base64_encode($pelicula['imagen']);
            } else {
                $pelicula['imagen'] = null;
            }
            $resultados[] = $pelicula;
        }
    }
}

echo json_encode($resultados);
$conexion->close();
?>
