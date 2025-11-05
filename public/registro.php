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
    <title>Registrarse - <?php echo SITIO; ?></title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<?php include 'navbar.php'; ?>

    <div class="container">
        <div class="form-box" style="display: block; !important">
            <h2>Registrarse</h2>

            <?php showError(); ?>
            <?php showSuccess(); ?>

            <form action="procesar_registro.php" method="POST" enctype="multipart/form-data">
                <input type="text" name="nombre" placeholder="Nombre" value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" required>
                <input type="text" name="apellidos" placeholder="Apellidos" value="<?php echo htmlspecialchars($_POST['apellidos'] ?? ''); ?>" required>
                <input type="text" name="cedula" placeholder="Cédula" value="<?php echo htmlspecialchars($_POST['cedula'] ?? ''); ?>" pattern="[0-9]{9,10}" title="Cédula de 9 o 10 dígitos" required>
                <input type="date" name="fecha_nacimiento" max="<?php echo date('Y-m-d'); ?>" required>
                <input type="email" name="correo" placeholder="Correo electrónico"  value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>" required>
                <input type="tel" name="telefono"placeholder="Número de teléfono" value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
                <input type="text" name="usuario" placeholder="Nombre de usuario" value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>" minlength="4" maxlength="50" required>
                <input type="password" name="password" placeholder="Contraseña" minlength="6" required>
                <input type="password" name="confirm_password" placeholder="Confirmar contraseña" minlength="6"  required>
                <button type="submit">Registrarse</button>
                <p>¿Ya tienes una cuenta? <a href="login.php">Iniciar sesión</a></p>
            </form>
        </div>
    </div>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>