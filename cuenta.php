<?php
// Procesar creación de cuenta antes de renderizar
require_once __DIR__ . '/conexion.php';

$create_message = '';
if (isset($_POST['btncrear'])) {
    $correo = trim($_POST['correo']);
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    if (empty($correo) || empty($usuario) || empty($password)) {
        $create_message = "<p style='color:red;'>Por favor complete todos los campos.</p>";
    } else {
        // Verificar si el usuario ya existe
        $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $create_message = "<p style='color:red;'>El nombre de usuario ya existe. Elija otro.</p>";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conexion->prepare("INSERT INTO usuarios (correo, usuario, contraseña) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $correo, $usuario, $passwordHash);

            if ($stmt->execute()) {
                $create_message = "<p style='color:green;'>Cuenta creada correctamente. <a href='login.php'>Iniciar sesión</a></p>";
            } else {
                $create_message = "<p style='color:red;'>Error al crear la cuenta. Intente nuevamente.</p>";
            }
        }

        $stmt->close();
    }
}

$search_value = '';
$page_title = 'Crear cuenta';
$no_banner = true;
$v = time();
$extra_head = '<link rel="stylesheet" href="css/cuenta.css?v='.$v.'">\n<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">\n<style>header{background:none!important;background-image:none!important;min-height:auto!important;}</style>';
include __DIR__ . '/includes/header.php';
?>

    <div class="cuenta-container">
        <div class="wrapper">
        <form method="POST">
            <h1>Crear cuenta</h1>

            <?php echo $create_message; ?>

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
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
