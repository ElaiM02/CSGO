<?php
require_once '../config/start_app.php';
require_once '../config/database.php';
require_once '../config/functions.php';

checkAuth();
if ($_SESSION['rol'] !== 'admin') { header("Location: ../dashboard.php"); exit; }

$id = intval($_GET['id'] ?? 0);
global $conn;

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) { header("Location: users.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $cedula = trim($_POST['cedula']);
    $fecha_nac = $_POST['fecha_nacimiento'];
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $usuario = trim($_POST['usuario']);
    $rol = $_POST['rol'];
    $activo = isset($_POST['activo']) ? 1 : 0;

    $sql = "UPDATE usuarios SET nombre=?, apellido=?, cedula=?, fecha_nacimiento=?, email=?, telefono=?, usuario=?, rol=?, activo=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssii", $nombre, $apellido, $cedula, $fecha_nac, $email, $telefono, $usuario, $rol, $activo, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Usuario actualizado.";
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
    <title>Editar Usuario - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <h2 class="text-primary">Editar Usuario</h2>
        <?php if (isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
            </div>
            <div class="col-md-6">
                <label>Apellido</label>
                <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($usuario['apellido']) ?>" required>
            </div>
            <div class="col-md-6">
                <label>Cédula</label>
                <input type="text" name="cedula" class="form-control" value="<?= htmlspecialchars($usuario['cedula']) ?>" required>
            </div>
            <div class="col-md-6">
                <label>Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento" class="form-control" value="<?= $usuario['fecha_nacimiento'] ?>" required>
            </div>
            <div class="col-md-6">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
            </div>
            <div class="col-md-6">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label>Usuario</label>
                <input type="text" name="usuario" class="form-control" value="<?= htmlspecialchars($usuario['usuario']) ?>" required>
            </div>
            <div class="col-md-6">
                <label>Rol</label>
                <select name="rol" class="form-select">
                    <option value="pasajero" <?= $usuario['rol'] === 'pasajero' ? 'selected' : '' ?>>Pasajero</option>
                    <option value="chofer" <?= $usuario['rol'] === 'chofer' ? 'selected' : '' ?>>Chofer</option>
                    <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" name="activo" class="form-check-input" id="activo" <?= $usuario['activo'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="activo">Usuario Activo</label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="users.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>