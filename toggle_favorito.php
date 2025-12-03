<?php
session_start();
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuarios'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id_pelicula = isset($_POST['id_peliculas']) ? intval($_POST['id_peliculas']) : 0;
$user_id = intval($_SESSION['id_usuarios']);

if ($id_pelicula <= 0) {
    header('Location: index.php');
    exit;
}

// Crear tabla favoritos si no existe
$create_sql = "CREATE TABLE IF NOT EXISTS favoritos (
    id_favorito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuarios INT NOT NULL,
    id_peliculas INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user_peli (id_usuarios, id_peliculas)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conexion->query($create_sql);

// Verificar si ya existe
if ($stmt = $conexion->prepare("SELECT id_favorito FROM favoritos WHERE id_usuarios = ? AND id_peliculas = ?")) {
    $stmt->bind_param('ii', $user_id, $id_pelicula);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id_fav);
        $stmt->fetch();
        $stmt->close();
        $del = $conexion->prepare("DELETE FROM favoritos WHERE id_favorito = ?");
        if ($del) {
            $del->bind_param('i', $id_fav);
            $del->execute();
            $del->close();
        }
    } else {
        $stmt->close();
        $ins = $conexion->prepare("INSERT INTO favoritos (id_usuarios, id_peliculas) VALUES (?, ?)");
        if ($ins) {
            $ins->bind_param('ii', $user_id, $id_pelicula);
            $ins->execute();
            $ins->close();
        }
    }
}

header('Location: info.php?id=' . $id_pelicula);
exit;
