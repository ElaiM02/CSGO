<?php
require_once '../config/start_app.php';
require_once '../config/database.php';
require_once '../config/functions.php';

checkAuth();
if ($_SESSION['rol'] !== 'admin') {
    $_SESSION['error'] = "Acceso denegado. Solo administradores.";
    header("Location: ../dashboard.php");
    exit;
}

global $conn;

// === ELIMINAR USUARIO ===
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ? AND rol != 'admin'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['success'] = "Usuario eliminado.";
    header("Location: users.php");
    exit;
}

// === LISTAR USUARIOS ===
$stmt = $conn->prepare("
    SELECT id, nombre, apellido, cedula, fecha_nacimiento, email, telefono, 
           usuario, foto, rol, activo 
    FROM usuarios 
    ORDER BY id
");
$stmt->execute();
$result = $stmt->get_result();
$usuarios = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/main.css" rel="stylesheet">
    <style>
        .table-actions { width: 130px; }
        .foto-perfil { width: 40px; height: 40px; object-fit: cover; border-radius: 50%; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary fw-bold">Gestión de Usuarios</h2>
            <a href="user_create.php" class="btn btn-success">
                + Crear Usuario
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Foto</th>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Cédula</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td>
                                <?php if ($u['foto']): ?>
                                    <img src="../uploads/<?= htmlspecialchars($u['foto']) ?>" 
                                         alt="Foto" class="foto-perfil">
                                <?php else: ?>
                                    <div class="bg-secondary rounded-circle d-inline-block" 
                                         style="width:40px;height:40px;"></div>
                                <?php endif; ?>
                            </td>
                            <td><?= $u['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></strong>
                                <br><small class="text-muted">
                                    <?= date('d/m/Y', strtotime($u['fecha_nacimiento'])) ?>
                                </small>
                            </td>
                            <td><?= htmlspecialchars($u['cedula']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['telefono'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($u['usuario']) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $u['rol'] === 'admin' ? 'danger' : 
                                    ($u['rol'] === 'chofer' ? 'warning' : 'secondary') 
                                ?>">
                                    <?= ucfirst($u['rol']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $u['activo'] ? 'success' : 'secondary' ?>">
                                    <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="user_edit.php?id=<?= $u['id'] ?>" 
                                   class="btn btn-primary btn-sm" title="Editar">
                                    Editar
                                </a>
                                <?php if ($u['rol'] !== 'admin'): ?>
                                    <a href="?delete=<?= $u['id'] ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('¿Eliminar este usuario?')">
                                        Eliminar
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>