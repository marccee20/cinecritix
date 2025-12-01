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
    director VARCHAR(255),
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
    echo "<p>Insertando películas de ejemplo...</p>";
    
    // Películas de ejemplo con imágenes BLOB desde archivos locales
        $peliculas = [
        [
            'nombre' => 'Deadpool',
            'genero' => 'Acción, Comedia',
                'director' => 'Tim Miller',
            'duracion' => 108,
            'fecha_estreno' => '2016-02-12',
            'descripcion' => 'Un ex soldado toma venganza contra el hombre que lo dejó desfigurado.',
            'pais' => 'USA',
            'idioma' => 'Inglés',
            'archivo' => 'deadpool.webp'
        ],
        [
            'nombre' => 'Alien',
            'genero' => 'Ciencia Ficción, Terror',
                'director' => 'Ridley Scott',
            'duracion' => 117,
            'fecha_estreno' => '1979-05-25',
            'descripcion' => 'La tripulación de un comerciante espacial se enfrenta a una criatura alienígena letal.',
            'pais' => 'USA',
            'idioma' => 'Inglés',
            'archivo' => 'alien.jpg'
        ],
        [
            'nombre' => 'Lilo y Stitch Live Action',
            'genero' => 'Familia, Aventura',
                'director' => 'Filmmaker Ejemplo',
            'duracion' => 120,
            'fecha_estreno' => '2025-01-01',
            'descripcion' => 'Una chica hawaiana adopta a una criatura extraña que resulta ser un clon alienígena.',
            'pais' => 'USA',
            'idioma' => 'Inglés',
            'archivo' => 'LILO-Y-STITCH-LIVE-ACTION.jpg'
        ],
        [
            'nombre' => 'El Planeta de los Simios',
            'genero' => 'Ciencia Ficción',
                'director' => 'Franklin J. Schaffner',
            'duracion' => 112,
            'fecha_estreno' => '1968-04-03',
            'descripcion' => 'Un astronauta aterriza en un planeta donde los simios han evolucionado.',
            'pais' => 'USA',
            'idioma' => 'Inglés',
            'archivo' => 'planetadesimios.avif'
        ]
    ];
    
    $insertadas = 0;
    foreach ($peliculas as $peli) {
        $archivo = 'imagenes/' . $peli['archivo'];
        if (file_exists($archivo)) {
            $imagen_blob = file_get_contents($archivo);
            
            $stmt = $conexion->prepare(
                "INSERT INTO peliculas (nombre, genero, duracion, `fecha_estreno`, descripcion, director, imagen, pais, idioma) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->bind_param(
                'ssissssss',
                $peli['nombre'],
                $peli['genero'],
                $peli['duracion'],
                $peli['fecha_estreno'],
                $peli['descripcion'],
                $peli['director'],
                $imagen_blob,
                $peli['pais'],
                $peli['idioma']
            );
            
            if ($stmt->execute()) {
                echo "✓ Insertada: " . $peli['nombre'] . "<br>";
                $insertadas++;
            } else {
                echo "✗ Error insertando " . $peli['nombre'] . ": " . $stmt->error . "<br>";
            }
            $stmt->close();
        } else {
            echo "⚠ Archivo no encontrado: $archivo<br>";
        }
    }
    
    echo "<p>Películas insertadas: <strong>$insertadas</strong></p>";
} else {
    echo "✓ Base de datos ya contiene " . $row['total'] . " películas<br>";
}

echo "<h2>✓ Inicialización completada</h2>";
echo "<p><a href='index.php'>Volver a la página principal</a></p>";

$conexion->close();
?>
