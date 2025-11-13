<?php 
require_once '../config/start_app.php'; 
require_once '../config/functions.php';
redirectByAuth();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aventones</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body class= "login-bg">

    <div class="container">
        <div class="form-box active">
            <h2>Login</h2>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-error>
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="procesar_login.php" method="POST">
                <input type="text" name="user" placeholder="Usuario" value="<?php echo isset($_POST['user']) ? htmlspecialchars($_POST['user']) : ''; ?>" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" name="login">Ingresar</button>
                <p>¿No tienes una cuenta? <a href="registro.php">Registrarse</a></p>
            </form>
        </div>
    </div>

</body>
</html>