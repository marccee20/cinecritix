<?php
require_once __DIR__ . '/config.php';

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conexion->connect_errno) {
    die("error de conexion: " . $conexion->connect_error);
}
?>