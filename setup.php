<?php
$server = 'localhost';
$user = 'root';
$pass = '';

// Conectar sin especificar base de datos para crearla
$conexion = new mysqli($server, $user, $pass);

if ($conexion->connect_errno) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Crear base de datos
$sql_db = "CREATE DATABASE IF NOT EXISTS peliculas_proyecto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conexion->query($sql_db) === TRUE) {
    echo "✓ Base de datos 'peliculas_proyecto' creada o ya existe.<br>";
} else {
    die("Error al crear base de datos: " . $conexion->error);
}

// Seleccionar la base de datos
$conexion->select_db("peliculas_proyecto");

// Crear tabla 'peliculas'
$sql_table = "CREATE TABLE IF NOT EXISTS peliculas (
  id_peliculas INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL,
  imagen LONGBLOB
)";

if ($conexion->query($sql_table) === TRUE) {
    echo "✓ Tabla 'peliculas' creada o ya existe.<br>";
} else {
    die("Error al crear tabla: " . $conexion->error);
}

// Insertar datos de prueba (si la tabla está vacía)
$resultado = $conexion->query("SELECT COUNT(*) as count FROM peliculas");
$row = $resultado->fetch_assoc();

if ($row['count'] == 0) {
    $sql_insert = "INSERT INTO peliculas (nombre, imagen) VALUES 
    ('Destino Final', NULL),
    ('Los Extraños', NULL),
    ('El Planeta de los Simios', NULL)";
    
    if ($conexion->query($sql_insert) === TRUE) {
        echo "✓ Datos de prueba insertados.<br>";
    } else {
        echo "⚠ Error al insertar datos: " . $conexion->error . "<br>";
    }
} else {
    echo "✓ La tabla ya contiene datos (" . $row['count'] . " películas).<br>";
}

echo "<br><strong>¡Setup completado!</strong><br>";
echo "Puedes ahora acceder a tu proyecto en: <a href='index.php'>http://localhost/cinecritix/</a><br>";
echo "Para eliminar este archivo después de verificar, ejecuta:<br>";
echo "<code>rm setup.php</code>";

$conexion->close();
?>
