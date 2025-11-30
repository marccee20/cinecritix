# CineCritix - Guía de Configuración

## Requisitos
- XAMPP (Apache + MySQL/MariaDB + PHP 7.4+)
- Navegador web moderno

## Instalación Rápida

### 1. Clonar el repositorio
```bash
git clone https://github.com/marccee20/cinecritix.git
cd cinecritix
```

### 2. Configurar XAMPP
- Coloca el proyecto en `C:\xampp\htdocs\cinecritix`
- Inicia Apache y MySQL desde el Panel de Control de XAMPP

### 3. Inicializar la base de datos
- Abre en el navegador: `http://localhost/cinecritix/init_db.php`
- El script creará automáticamente:
  - Base de datos `peliculas_proyecto`
  - Tablas necesarias (`peliculas`, `comentarios`, `usuarios`)

### 4. ¡Listo!
- Accede a `http://localhost/cinecritix/` para usar la aplicación

## Estructura del Proyecto

```
cinecritix/
├── index.php              # Página principal
├── buscar.php             # Página de búsqueda
├── login.php              # Login de usuarios
├── cuenta.php             # Registro de usuarios
├── info.php               # Detalles de película
├── init_db.php            # Inicialización de BD
│
├── includes/              # Templates compartidos
│   ├── header.php         # Encabezado con navbar
│   ├── footer.php         # Pie de página
│   ├── config.php         # Configuración (credenciales)
│   └── db.php             # Conexión a BD
│
├── assets/
│   ├── js/
│   │   └── search.js      # Autocompletado del buscador
│   └── css/
│       ├── style.css      # Estilos principales
│       ├── login.css      # Estilos login/registro
│       ├── informacion.css # Estilos página de info
│       └── cuenta.css     # Estilos cuenta
│
├── imagenes/
│   ├── exportadas/        # Imágenes de películas (generadas)
│   └── [archivos png/jpg] # Recursos visuales
│
├── api_buscar.php         # API para autocompletado
├── guardar_comentario.php # API para guardar comentarios
├── logout.php             # Cierre de sesión
├── conexion.php           # Conexión de BD (backward compatibility)
└── .git/                  # Control de versiones
```

## Características Principales

### 🔍 Búsqueda con Autocompletado
- Búsqueda en tiempo real mientras escribes
- Miniaturas en el dropdown
- Sugerencias basadas en coincidencias de nombre

### 🎬 Catálogo de Películas
- Vista de películas populares
- Información detallada (géneros, duración, descripción)
- Sistema de comentarios anidados

### 👤 Gestión de Usuarios
- Registro con validación
- Login seguro (password_hash)
- Sesiones PHP

### 💬 Comentarios
- Comentarios en las películas
- Respuestas anidadas a comentarios
- Validación básica

## Variables de Configuración

Edita `includes/config.php` para cambiar credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'peliculas_proyecto');
```

## Datos de Ejemplo

El sistema viene con películas de ejemplo en la BD. Para agregar más:

1. Ve a la sección "Películas" en la página principal
2. Las películas se cargan desde la tabla `peliculas`
3. Cada película tiene imagen, descripción, género, etc.

## Troubleshooting

### "Error: MySQL shutdown unexpectedly"
- Puerto 3306 ocupado: Reinicia XAMPP o mata el proceso en ese puerto

### "Unknown database 'peliculas_proyecto'"
- Ejecuta `init_db.php` primero

### Las imágenes no cargan
- Verifica que `imagenes/exportadas/` contenga las miniaturas
- Revisa permisos de carpeta

## Desarrollo Futuro

- [ ] Panel administrativo
- [ ] Calificaciones de películas
- [ ] Listas favoritas
- [ ] Notificaciones de respuestas
- [ ] Mejora de UI/UX

## Licencia
Proyecto educativo - CineCritix

---

**¿Necesitas ayuda?** Revisa los comentarios en el código o contacta al equipo de desarrollo.
