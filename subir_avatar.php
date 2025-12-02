<?php
session_start();
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuarios'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    header('Location: perfil.php?error=upload');
    exit;
}

$file = $_FILES['avatar'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif'
];

if (!isset($allowed[$mime])) {
    header('Location: perfil.php?error=type');
    exit;
}

$ext = $allowed[$mime];
$userId = intval($_SESSION['id_usuarios']);
$uploadDir = __DIR__ . '/imagenes/avatars/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Eliminar posibles avatares anteriores con otra extensión
foreach (['png','jpg','jpeg','webp','gif'] as $e) {
    $old = $uploadDir . $userId . '.' . $e;
    if (file_exists($old) && !in_array($e, [$ext])) @unlink($old);
}

$target = $uploadDir . $userId . '.' . $ext;

if (move_uploaded_file($file['tmp_name'], $target)) {
    header('Location: perfil.php?ok=1');
    exit;
} else {
    header('Location: perfil.php?error=save');
    exit;
}

?>
