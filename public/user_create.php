<?php
require_once '../config/start_app.php';
require_once '../config/database.php';
require_once '../config/functions.php';

checkAuth();
if ($_SESSION['rol'] !== 'admin') { header("Location: ../dashboard.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $cedula = trim($_POST['cedula']);
    $fecha_nac = $_POST['fecha_nacimiento'];
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $usuario = trim($_POST['usuario']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];

    global $conn;
    $stmt = $conn->prepare("
        INSERT INTO usuarios 
        (nombre, apellido, cedula, fecha_nacimiento, email, telefono, usuario, password, rol) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssssss", $nombre, $apellido, $cedula, $fecha_nac, $email, $telefono, $usuario, $password, $rol);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Usuario creado exitosamente.";
        header("Location: users.php");
        exit;
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <h2 class="text-primary">Crear Nuevo Usuario</h2>
        <?php if (isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Apellido</label>
                <input type="text" name="apellido" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Cédula</label>
                <input type="text" name="cedula" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-control">
            </div>
            <div class="col-md-6">
                <label>Usuario</label>
                <input type="text" name="usuario" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Rol</label>
                <select name="rol" class="form-select">
                    <option value="pasajero">Pasajero</option>
                    <option value="chofer">Chofer</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-success">Crear Usuario</button>
                <a href="users.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>