<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';
require_once '../config/vehiculos_functions.php';

checkAuth();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { 
    $_SESSION['error'] = "Vehículo no válido";
    header("Location: vehiculos.php");
    exit;
}

$vehiculo = getVehiculoById($id);

if (!$vehiculo) {
    $_SESSION['error'] = "Vehículo no encontrado";
    header("Location: vehiculos.php");
    exit;
}

$isOwner = $vehiculo['user_id'] == $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Vehículo - <?php echo SITIO; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/vehiculos.css" rel="stylesheet">

    <style>
        .vehiculo-header {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
        }
    </style>
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>

<div class="veh-container">
    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card veh-card shadow-lg">
                    <div class="card-header vehiculo-header text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="fas fa-car"></i>
                                <?= htmlspecialchars($vehiculo['marca'].' '.$vehiculo['modelo']); ?>
                            </h4>

                            <div>
                                <?php if ($isOwner): ?>
                                <a href="vehiculos_edit.php?id=<?= $vehiculo['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>
                                <?php endif; ?>
                                <a href="vehiculos.php" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left"></i> Volver </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="text-center mb-4">
                            <img src="<?= $vehiculo['foto'] ? '../uploads/vehiculos/'.$vehiculo['foto'] : '../assets/img/no-image.png' ?>" class="veh-img shadow"alt="Foto del vehículo">
                        </div>

                        <div class="text-center mb-3">
                            <span class="badge px-3 py-2
                                <?= $vehiculo['estado'] === 'pendiente' 
                                    ? 'badge-veh-pendiente' 
                                    : ($vehiculo['estado'] === 'aprobado' 
                                        ? 'badge-veh-aprobado' 
                                        : 'badge-veh-rechazado') ?>">
                                <?= ucfirst($vehiculo['estado']); ?>
                            </span>
                        </div>

                        <hr>

                        <h5><i class="fas fa-info-circle text-primary"></i> Información del Vehículo</h5>

                        <table class="table table-bordered table-sm">
                            <tr>
                                <td><strong>Marca:</strong></td>
                                <td><?= htmlspecialchars($vehiculo['marca']); ?></td>
                            </tr>

                            <tr>
                                <td><strong>Modelo:</strong></td>
                                <td><?= htmlspecialchars($vehiculo['modelo']); ?></td>
                            </tr>

                            <tr>
                                <td><strong>Año:</strong></td>
                                <td><?= $vehiculo['ano']; ?></td>
                            </tr>

                            <tr>
                                <td><strong>Color:</strong></td>
                                <td><?= htmlspecialchars($vehiculo['color']); ?></td>
                            </tr>

                            <tr>
                                <td><strong>Placa:</strong></td>
                                <td><span class="badge bg-primary p-2"><?= $vehiculo['placa']; ?></span></td>
                            </tr>

                            <tr>
                                <td><strong>Registrado el:</strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($vehiculo['fecha_registro'])); ?></td>
                            </tr>

                            <?php if ($vehiculo['fecha_aprobacion']): ?>
                            <tr>
                                <td><strong>Aprobado el:</strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($vehiculo['fecha_aprobacion'])); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                        <hr>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">

                            <?php if ($isOwner): ?>
                            <button class="btn btn-outline-danger btn-lg"onclick="confirmDelete(<?= $vehiculo['id']; ?>)"><i class="fas fa-trash"></i> Eliminar Vehículo</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Eliminar Vehículo</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>¿Estás seguro de eliminar este vehículo?</p>
                <p class="text-danger">Esta acción no se puede deshacer.</p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                <form id="deleteForm" method="POST" action="vehiculos_delete.php">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                </form>
            </div>

        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function confirmDelete(id) {
    document.getElementById("deleteId").value = id;
    new bootstrap.Modal(document.getElementById("deleteModal")).show();
}
</script>

</body>
</html>