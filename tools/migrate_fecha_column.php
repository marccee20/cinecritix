<?php
/**
 * Migra la columna `fecha-estreno` -> `fecha_estreno` si existe.
 * Uso: php tools/migrate_fecha_column.php
 */
require_once __DIR__ . '/../conexion.php';

$res = $conexion->query("SHOW COLUMNS FROM peliculas LIKE 'fecha-estreno'");
if ($res && $res->num_rows > 0) {
    echo "Encontrada columna `fecha-estreno`. Renombrando a `fecha_estreno`...\n";
    $sql = "ALTER TABLE peliculas CHANGE `fecha-estreno` `fecha_estreno` DATE";
    if ($conexion->query($sql) === TRUE) {
        echo "Renombrada correctamente a `fecha_estreno`.\n";
    } else {
        echo "Error al renombrar columna: " . $conexion->error . "\n";
    }
} else {
    echo "No existe columna `fecha-estreno`. Nada que renombrar.\n";
}

$conexion->close();
?>