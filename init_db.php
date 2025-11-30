<?php
/**
 * Script de inicialización de la base de datos
 * 
 * Uso: Accede a http://localhost/cinecritix/init_db.php en el navegador
 * 
 * Este script:
 * 1. Crea la base de datos 'peliculas_proyecto' si no existe
 * 2. Crea la tabla 'peliculas' con la estructura necesaria
 * 3. Inserta datos de ejemplo
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$servidor = 'localhost';
$usuario = 'root';
$contraseña = '';
$basedatos = 'peliculas_proyecto';

// Conexión sin especificar BD
$conexion = new mysqli($servidor, $usuario, $contraseña);

if ($conexion->connect_errno) {
    die("Error de conexión: " . $conexion->connect_error);
}

echo "<h2>Inicializando base de datos...</h2>";

// Crear BD
$sql_crear_bd = "CREATE DATABASE IF NOT EXISTS $basedatos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conexion->query($sql_crear_bd)) {
    echo "✓ Base de datos '$basedatos' lista<br>";
} else {
    die("✗ Error creando BD: " . $conexion->error);
}

// Usar la BD
$conexion->select_db($basedatos);

// Crear tabla peliculas
$sql_tabla = "CREATE TABLE IF NOT EXISTS peliculas (
    id_peliculas INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    genero VARCHAR(100),
    duracion INT,
    fecha_estreno DATE,
    descripcion LONGTEXT,
    imagen LONGBLOB,
    imagen_path VARCHAR(255),
    imagen_thumb VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conexion->query($sql_tabla)) {
    echo "✓ Tabla 'peliculas' lista<br>";
} else {
    die("✗ Error creando tabla: " . $conexion->error);
}

// Crear tabla comentarios
$sql_comentarios = "CREATE TABLE IF NOT EXISTS comentarios (
    id_comentario INT AUTO_INCREMENT PRIMARY KEY,
    id_pelicula INT NOT NULL,
    id_usuario INT,
    usuario VARCHAR(255) NOT NULL,
    comentario TEXT NOT NULL,
    id_comentario_padre INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pelicula) REFERENCES peliculas(id_peliculas) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conexion->query($sql_comentarios)) {
    echo "✓ Tabla 'comentarios' lista<br>";
} else {
    die("✗ Error creando tabla comentarios: " . $conexion->error);
}

// Crear tabla usuarios
$sql_usuarios = "CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(255) UNIQUE NOT NULL,
    correo VARCHAR(255) UNIQUE NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conexion->query($sql_usuarios)) {
    echo "✓ Tabla 'usuarios' lista<br>";
} else {
    die("✗ Error creando tabla usuarios: " . $conexion->error);
}

// Verificar si ya hay datos
$resultado = $conexion->query("SELECT COUNT(*) as total FROM peliculas");
$row = $resultado->fetch_assoc();

if ($row['total'] == 0) {
    echo "<p>Insertando datos de ejemplo...</p>";
    echo "✓ Estructura lista. Ahora puedes insertar películas desde la aplicación.<br>";
} else {
    echo "✓ Base de datos ya contiene " . $row['total'] . " películas<br>";
}

echo "<h2>✓ Inicialización completada</h2>";
echo "<p><a href='index.php'>Volver a la página principal</a></p>";

$conexion->close();
?>
