<?php
/**
 * Script de migración: añade la columna `director` a la tabla `peliculas` si no existe.
 * Uso (desde navegador): http://localhost/cinecritix/tools/add_director_column.php
 */
require_once __DIR__ . '/../conexion.php';

// Comprobar existencia
$res = $conexion->query("SHOW COLUMNS FROM peliculas LIKE 'director'");
if ($res && $res->num_rows > 0) {
    echo "La columna 'director' ya existe.\n";
    exit;
}

$sql = "ALTER TABLE peliculas ADD COLUMN director VARCHAR(255) DEFAULT NULL";
if ($conexion->query($sql) === TRUE) {
    echo "Columna 'director' añadida correctamente.\n";
} else {
    echo "Error al añadir columna: " . $conexion->error . "\n";
}

$conexion->close();
?>