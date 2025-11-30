<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta</title>
    <link rel="stylesheet" href="css/cuenta.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

    <nav class="navbar">
    <a href="index.php">cinecritix</a>
    <ul class="nav-links">
        <li><a href="login.php">INICIAR SESION</a></li>
        <li><a href="cuenta.php">CREAR UNA CUENTA</a></li>
        
    </ul>
</nav>

    <div class="wrapper">
        <form method="POST">
            <h1>Crear cuenta</h1>

            <?php
            include("conexion.php");

            if (isset($_POST['btncrear'])) {
                $correo = trim($_POST['correo']);
                $usuario = trim($_POST['usuario']);
                $password = trim($_POST['password']);

                if (empty($correo) || empty($usuario) || empty($password)) {
                    echo "<p style='color:red;'>Por favor complete todos los campos.</p>";
                } else {
                    // Verificar si el usuario ya existe
                    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ?");
                    $stmt->bind_param("s", $usuario);
                    $stmt->execute();
                    $resultado = $stmt->get_result();

                    if ($resultado->num_rows > 0) {
                        echo "<p style='color:red;'>El nombre de usuario ya existe. Elija otro.</p>";
                    } else {
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                        $stmt = $conexion->prepare("INSERT INTO usuarios (correo, usuario, contraseña) VALUES (?, ?, ?)");
                        $stmt->bind_param("sss", $correo, $usuario, $passwordHash);

                        if ($stmt->execute()) {
                            echo "<p style='color:green;'>Cuenta creada correctamente. <a href='login.php'>Iniciar sesión</a></p>";
                        } else {
                            echo "<p style='color:red;'>Error al crear la cuenta. Intente nuevamente.</p>";
                        }
                    }

                    $stmt->close();
                }
            }
            ?>

            <div class="input-box">
                <input name="correo" type="email" placeholder="Correo electrónico" required>
                <i class='bx bx-envelope'></i>
            </div>

            <div class="input-box">
                <input name="usuario" type="text" placeholder="Nombre de usuario" required>
                <i class='bx bx-user'></i>
            </div>

            <div class="input-box">
                <input name="password" type="password" placeholder="Contraseña" required>
                <i class='bx bxs-lock-alt'></i>
            </div>

            <div class="remember-forgot">
                <label><input type="checkbox" required>Acepto términos y condiciones</label>
            </div>

            <button type="submit" name="btncrear" class="btn">Crear cuenta</button>

            <div class="register-link">
                <p>¿Ya tienes una cuenta? <a href="login.php">Iniciar sesión</a></p>
            </div>
        </form>
    </div>
</body>
</html>
