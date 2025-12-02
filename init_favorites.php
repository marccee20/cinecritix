<?php
/**
 * Script para crear la tabla `favoritos` de forma explícita.
 * - Detecta el nombre real de las columnas de usuario/película si difieren
 * - Añade FOREIGN KEYS si las columnas detectadas existen
 * Uso: abrir en el navegador: http://localhost/php/init_favorites.php
 */
require_once __DIR__ . '/conexion.php';

// Obtener nombre de la base de datos actual
$dbRow = $conexion->query("SELECT DATABASE()");
$dbName = $dbRow ? $dbRow->fetch_row()[0] : null;

function find_column($conexion, $dbName, $table, $candidates) {
    foreach ($candidates as $c) {
        $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . $conexion->real_escape_string($dbName) . "' AND TABLE_NAME = '" . $conexion->real_escape_string($table) . "' AND COLUMN_NAME = '" . $conexion->real_escape_string($c) . "'";
        $res = $conexion->query($sql);
        if ($res) {
            $row = $res->fetch_row();
            if (intval($row[0]) > 0) return $c;
        }
    }
    return null;
}

// Detectar columnas en tablas existentes
$userCol = find_column($conexion, $dbName, 'usuarios', ['id_usuarios','id_usuario','idUser','id']);
$movieCol = find_column($conexion, $dbName, 'peliculas', ['id_peliculas','id_pelicula','id','id_peliculas']);

// Construir SQL para crear tabla favoritos (columnas usadas por la app: id_usuarios y id_peliculas)
$sql = "CREATE TABLE IF NOT EXISTS favoritos (
    id_favorito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuarios INT NOT NULL,
    id_peliculas INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_movie (id_usuarios, id_peliculas)";

// Si detectamos columnas válidas, añadimos las constraints referenciando los nombres detectados
if ($userCol) {
    $sql .= ",\n    CONSTRAINT fk_fav_user FOREIGN KEY (id_usuarios) REFERENCES usuarios(`" . $conexion->real_escape_string($userCol) . "`) ON DELETE CASCADE";
}
if ($movieCol) {
    $sql .= ",\n    CONSTRAINT fk_fav_movie FOREIGN KEY (id_peliculas) REFERENCES peliculas(`" . $conexion->real_escape_string($movieCol) . "`) ON DELETE CASCADE";
}

$sql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// Ejecutar creación
if ($conexion->query($sql) === TRUE) {
    echo "✓ Tabla `favoritos` creada o ya existe.<br>";
    if ($userCol) echo "- FK hacia usuarios({$userCol}) añadida.<br>";
    else echo "- No se detectó columna de usuarios para FK; la tabla se creó sin FK.<br>";
    if ($movieCol) echo "- FK hacia peliculas({$movieCol}) añadida.<br>";
    else echo "- No se detectó columna de películas para FK; la tabla se creó sin FK.<br>";
} else {
    echo "✗ Error creando tabla favoritos: " . htmlspecialchars($conexion->error) . "<br>";
}

echo "<p><a href='index.php'>Volver al sitio</a></p>";

$conexion->close();

?>
