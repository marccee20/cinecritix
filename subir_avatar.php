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

$allowed = [
    'image/png' => 'png',
    'image/jpeg' => 'jpg',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['avatar']['tmp_name']);
finfo_close($finfo);

if (!isset($allowed[$mime])) {
    header('Location: perfil.php?error=type');
    exit;
}

$ext = $allowed[$mime];
$uid = intval($_SESSION['id_usuarios']);
$targetDir = __DIR__ . '/imagenes/avatars/';
if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
$target = $targetDir . $uid . '.' . $ext;

// Eliminar avatars previos con otras extensiones
foreach (['png','jpg','jpeg','webp','gif'] as $e) {
    $f = $targetDir . $uid . '.' . $e;
    if (file_exists($f) && $e !== $ext) @unlink($f);
}

if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
    header('Location: perfil.php?error=save');
    exit;
}

header('Location: perfil.php?ok=1');
exit;
