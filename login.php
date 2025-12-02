<?php
// Procesar login antes de enviar cualquier salida
require_once __DIR__ . '/conexion.php';

$login_error = '';
if (isset($_POST['btningresar'])) {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    if (empty($usuario) || empty($password)) {
        $login_error = "Por favor complete todos los campos.";
    } else {
        $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            $hashGuardado = $row['contraseña'];

            if (password_verify($password, $hashGuardado)) {
                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION['id_usuarios'] = $row['id_usuarios'];
                $_SESSION['usuario'] = $row['usuario'];
                header("Location: index.php");
                exit();
            } else {
                $login_error = "Contraseña incorrecta.";
            }
        } else {
            $login_error = "Usuario no encontrado.";
        }

        $stmt->close();
    }
}

$search_value = '';
$page_title = 'Inicio de sesión';
$no_banner = true;
$extra_head = '<link rel="stylesheet" href="css/login.css">\n<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">\n<style>header{background:none!important;background-image:none!important;min-height:auto!important;}</style>';
include __DIR__ . '/includes/header.php';
?>

    <div class="login-container">
        <div class="wrapper">
            <form method="POST">
                <h1>Inicio de sesión</h1>
                <?php if (!empty($login_error)) echo "<p style='color:red;'>" . htmlspecialchars($login_error) . "</p>"; ?>

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
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
