<?php
session_start();
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuarios'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_POST['id_peliculas'])) {
    header('Location: index.php');
    exit;
}

$id_usuario = intval($_SESSION['id_usuarios']);
$id_pelicula = intval($_POST['id_peliculas']);

// Crear tabla favoritos si no existe (sin claves foráneas para evitar errores en esquemas distintos)
$create = "CREATE TABLE IF NOT EXISTS favoritos (
    id_favorito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuarios INT NOT NULL,
    id_peliculas INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_movie (id_usuarios, id_peliculas)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conexion->query($create);

// Comprobar si ya está en favoritos
$check = $conexion->prepare("SELECT id_favorito FROM favoritos WHERE id_usuarios = ? AND id_peliculas = ?");
$check->bind_param('ii', $id_usuario, $id_pelicula);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // eliminar
    $del = $conexion->prepare("DELETE FROM favoritos WHERE id_usuarios = ? AND id_peliculas = ?");
    $del->bind_param('ii', $id_usuario, $id_pelicula);
    $del->execute();
    $del->close();
} else {
    // insertar
    $ins = $conexion->prepare("INSERT INTO favoritos (id_usuarios, id_peliculas) VALUES (?, ?)");
    $ins->bind_param('ii', $id_usuario, $id_pelicula);
    $ins->execute();
    $ins->close();
}

$check->close();

// Volver a la ficha
header('Location: info.php?id=' . $id_pelicula);
exit;

?>
