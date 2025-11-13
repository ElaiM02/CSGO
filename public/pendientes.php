<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';

checkAuth();
if (!isAdmin()) {
    $_SESSION['error'] = "Acceso denegado. Solo administradores.";
    header("Location: ../dashboard.php");
    exit;
}

// Aprobar o Rechazo de solicitudes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vehiculo_id'], $_POST['accion'])) {
    $vehiculo_id = (int)$_POST['vehiculo_id'];
    $accion = $_POST['accion']; // 'aprobar' o 'rechazar'

    if (aprobarRechazarVehiculo($vehiculo_id, $accion, $conn)) {
        header("Location: pendientes.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Vehículos Pendientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .table th { background-color: #f8f9fa; }
        .btn-sm i { margin-right: 4px; }
    </style>
</head>
<body class = "login-bg">
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-car"></i> Vehículos Pendientes de Aprobación
                        </h4>
                        <span class="badge bg-light text-dark fs-6">
                            <?php
                            $result = $conn->query("SELECT COUNT(*) as total FROM vehiculos WHERE estado = 'pendiente'");
                            $count = $result->fetch_assoc()['total'];
                            echo $count . " pendiente" . ($count != 1 ? 's' : '');
                            ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <?php showError(); showSuccess(); ?>

                        <?php if ($count == 0): ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-check-circle"></i>No hay vehículos pendientes de aprobación.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Vehículo</th>
                                            <th>Placa</th>
                                            <th>Foto</th>
                                            <th>Registrado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                       $sql = "SELECT v.*, u.usuario, u.email, u.rol, u.id AS usuario_idFROM vehiculos v JOIN usuarios u ON v.user_id = u.id 
                                        WHERE v.estado = 'pendiente' ORDER BY v.fecha_registro DESC";
                                        $result = $conn->query($sql);
                                        while ($v = $result->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($v['usuario']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($v['email']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($v['marca'] . ' ' . $v['modelo']); ?><br>
                                                <small>Año: <?php echo $v['ano']; ?>, Color: <?php echo $v['color']; ?></small>
                                            </td>
                                            <td><span class="badge bg-dark"><?php echo $v['placa']; ?></span></td>
                                            <td>
                                                <?php if ($v['foto']): ?>
                                                    <a href="../uploads/<?php echo $v['foto']; ?>" target="_blank">
                                                        <img src="../uploads/<?php echo $v['foto']; ?>" 
                                                             alt="Foto" width="60" class="rounded">
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin foto</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y H:i', strtotime($v['fecha_registro'] ?? 'now')); ?>
                                            </td>
                                            <td>
                                                <!-- Aprovar Cofer -->
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="vehiculo_id" value="<?php echo $v['id']; ?>">
                                                    <button type="submit" name="accion" value="aprobar"
                                                            class="btn btn-success btn-sm" 
                                                            onclick="return confirm('¿Aprobar este vehículo?')">
                                                        <i class="fas fa-check"></i> Aprobar
                                                    </button>
                                                </form>

                                                <!-- Rechazar Chofer -->
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="vehiculo_id" value="<?php echo $v['id']; ?>">
                                                    <button type="submit" name="accion" value="rechazar"
                                                            class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('¿Rechazar este vehículo?')">
                                                        <i class="fas fa-times"></i> Rechazar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>