<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';
require_once '../config/vehiculos_functions.php';

checkAuth();

// SOLO CHOFERES
if (!isChofer()) {
    $_SESSION["error"] = "Acceso denegado. Solo choferes pueden gestionar vehículos.";
    header("Location: ../dashboard.php");
    exit;
}

// OBTENER VEHÍCULOS DEL USUARIO
$vehiculos = getVehiculosByChofer($_SESSION['user_id']);

// Mensajes
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Vehículos - <?php echo SITIO; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- CSS PRINCIPAL -->
    <link href="../css/vehiculos.css" rel="stylesheet">
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>

<!-- CONTENEDOR PRINCIPAL -->
<div class="veh-container">
    <div class="container mt-4 mb-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="veh-title">
                <i class="fas fa-car text-primary"></i>
                Mis Vehículos
            </h2>

            <a href="registroVehiculos.php" class="btn btn-success btn-lg">
                <i class="fas fa-plus-circle"></i> Registrar Nuevo Vehículo
            </a>
        </div>

        <!-- Mensajes -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- SIN VEHÍCULOS -->
        <?php if (empty($vehiculos)): ?>
            <div class="text-center py-5">
                <i class="fas fa-car-side fa-4x text-muted mb-4"></i>
                <h4 class="text-muted">Aún no has registrado ningún vehículo</h4>
                <p class="text-muted">Registra tu vehículo para publicar viajes.</p>
                <a href="registroVehiculos.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus"></i> Registrar Vehículo
                </a>
            </div>

        <?php else: ?>

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

                <?php foreach ($vehiculos as $v): ?>
                    <div class="col">
                        <div class="card veh-card shadow-sm h-100">

                            <!-- FOTO -->
                            <img 
                                src="<?= $v['foto'] ? '../uploads/vehiculos/' . $v['foto'] : '../assets/img/no-image.png' ?>" 
                                class="veh-img"
                                alt="Foto del vehículo"
                            >

                            <div class="card-body d-flex flex-column">

                                <!-- ESTADO -->
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge 
                                        <?= $v['estado'] == 'pendiente' ? 'badge-veh-pendiente' : ($v['estado'] == 'aprobado' ? 'badge-veh-aprobado' : 'badge-veh-rechazado') ?>">
                                        <?= ucfirst($v['estado']) ?>
                                    </span>

                                    <small class="text-muted">ID: #<?= $v['id'] ?></small>
                                </div>

                                <h5 class="card-title">
                                    <?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?>
                                </h5>

                                <p class="text-muted mb-1">
                                    <i class="fas fa-id-card"></i> Placa: <strong><?= htmlspecialchars($v['placa']) ?></strong>
                                </p>

                                <p class="text-muted mb-1">
                                    <i class="fas fa-calendar"></i> Año: <?= $v['ano'] ?>
                                </p>

                                <p class="text-muted mb-3">
                                    <i class="fas fa-palette"></i> Color: <?= htmlspecialchars($v['color']) ?>
                                </p>

                                <div class="mt-auto">

                                    <div class="btn-group w-100">
                                        <a href="vehiculos_view.php?id=<?= $v['id'] ?>" class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>

                                        <a href="vehiculos_edit.php?id=<?= $v['id'] ?>" class="btn btn-outline-warning btn-sm">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>

                                        <button 
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="confirmDelete(<?= $v['id'] ?>, '<?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?>')"
                                        >
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </div>

                                </div>
                            </div>

                            <div class="card-footer text-muted small">
                                <i class="fas fa-clock"></i>
                                Registrado el <?= date("d/m/Y", strtotime($v['fecha_registro'])) ?>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Eliminar Vehículo</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>¿Deseas eliminar el vehículo:</p>
                <strong id="vehiculoNombre"></strong>?
                <p class="text-danger mt-2">Esta acción es permanente.</p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                <form id="deleteForm" action="vehiculos_delete.php" method="POST">
                    <input type="hidden" name="id" id="deleteId">
                    <button class="btn btn-danger" type="submit">Sí, eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function confirmDelete(id, nombre) {
    document.getElementById("deleteId").value = id;
    document.getElementById("vehiculoNombre").textContent = nombre;
    new bootstrap.Modal(document.getElementById("deleteModal")).show();
}
</script>

</body>
</html>
