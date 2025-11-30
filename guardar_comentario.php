<?php
include("conexion.php");
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuarios'])) {
    header("Location: login.php");
    exit;
}

// Recibir datos del formulario
$id_usuario = $_SESSION['id_usuarios'];
$id_pelicula = intval($_POST['id_peliculas']);
$comentario = trim($_POST['comentario']);
$puntuacion = isset($_POST['puntuacion']) ? intval($_POST['puntuacion']) : null;
$id_comentario_padre = isset($_POST['id_comentario_padre']) ? intval($_POST['id_comentario_padre']) : null;

// ==============================
// GUARDAR O ACTUALIZAR VALORACIÓN
// ==============================
if ($puntuacion !== null) {
    // Verificar si el usuario ya valoró esta película
    $check = $conexion->prepare("SELECT id_valoracion FROM valoraciones WHERE id_usuarios = ? AND id_peliculas = ?");
    $check->bind_param("ii", $id_usuario, $id_pelicula);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Actualiza la puntuación existente
        $update = $conexion->prepare("UPDATE valoraciones SET puntuacion = ?, fecha = CURRENT_TIMESTAMP WHERE id_usuarios = ? AND id_peliculas = ?");
        $update->bind_param("iii", $puntuacion, $id_usuario, $id_pelicula);
        $update->execute();
        $update->close();
    } else {
        // Inserta una nueva valoración
        $insert = $conexion->prepare("INSERT INTO valoraciones (id_peliculas, id_usuarios, puntuacion) VALUES (?, ?, ?)");
        $insert->bind_param("iii", $id_pelicula, $id_usuario, $puntuacion);
        $insert->execute();
        $insert->close();
    }
    $check->close();
}

// ==============================
// GUARDAR COMENTARIO
// ==============================
if (!empty($comentario)) {
    if ($id_comentario_padre) {
        // Es una respuesta a otro comentario
        $sql = $conexion->prepare("INSERT INTO comentarios (id_peliculas, id_usuarios, comentario, id_comentario_padre, fecha) VALUES (?, ?, ?, ?, NOW())");
        $sql->bind_param("iisi", $id_pelicula, $id_usuario, $comentario, $id_comentario_padre);
    } else {
        // Comentario principal
        $sql = $conexion->prepare("INSERT INTO comentarios (id_peliculas, id_usuarios, comentario, fecha) VALUES (?, ?, ?, NOW())");
        $sql->bind_param("iis", $id_pelicula, $id_usuario, $comentario);
    }

    $sql->execute();
    $sql->close();
}

// ==============================
// REDIRECCIONAR A LA PÁGINA DE LA PELÍCULA
// ==============================
header("Location: info.php?id=" . $id_pelicula);
exit;
?>
