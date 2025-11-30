<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión</title>
    <link rel="stylesheet" href="css/login.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    
    <!-- Barra de navegación -->
<nav class="navbar">
    <a href="index.php">cinecritix</a>
    <ul class="nav-links">
        <li><a href="login.php">INICIAR SESION</a></li>
        <li><a href="cuenta.php">CREAR UNA CUENTA</a></li>
        
    </ul>
</nav>


    <div class="wrapper">
        <form method="POST">
            <h1>Inicio de sesión</h1>
            <?php
include("conexion.php");

if (isset($_POST['btningresar'])) {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    if (empty($usuario) || empty($password)) {
        echo "<p style='color:red;'>Por favor complete todos los campos.</p>";
    } else {
        // Buscar solo el usuario
        $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            $hashGuardado = $row['contraseña'];

            // Aquí sí usas password_verify
            if (password_verify($password, $hashGuardado)) {
                session_start();
                $_SESSION['id_usuarios'] = $row['id_usuarios']; // ← agrega el ID del usuario
                $_SESSION['usuario'] = $row['usuario'];         // guardá también el nombre
                header("Location: index.php");
                exit();


            } else {
                echo "<p style='color:red;'>Contraseña incorrecta.</p>";
            }
        } else {
            echo "<p style='color:red;'>Usuario no encontrado.</p>";
        }

        $stmt->close();
    }
}
?>

            <div class="input-box">
                <input id="usuario" name="usuario" type="text" placeholder="Usuario" required>
                
            </div>
            <div class="input-box">
                <input name="password" type="password" placeholder="Contraseña" required>
                
            </div>

            <div class="remember-forgot">
                <label><input type="checkbox">Recordar</label>
                <a href="#">Recuperar contraseña</a>
            </div>

            <button name="btningresar" type="submit" class="btn">Iniciar sesión</button>

            <div class="register-link">
                <p>¿No tienes una cuenta? <a href="cuenta.php">Regístrate</a></p>
            </div>
        </form>
    </div>
</body>
</html>
