<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';
require_once '../config/ride_functions.php';

checkAuth();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { $_SESSION['error'] = 'Viaje no válido'; header('Location: ../dashboard.php'); exit; }

$viaje = getViajeById($id);

if (!$viaje) {
    $_SESSION['error'] = 'Viaje no encontrado';
    header('Location: ../dashboard.php');
    exit;
}

$isOwner = isChofer() && $viaje['chofer_id'] == $_SESSION['user_id'];
$isPassenger = isPasajero();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Viaje - <?php echo SITIO; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/rides.css" rel="stylesheet">

    <style>
        .viaje-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }
    </style>
</head>

<body>
<?php include __DIR__ . '/navbar.php'; ?>

<div class="ride-container">
    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow-lg
                    <div class="card-header viaje-header text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="fas fa-route"></i> 
                                <?php echo htmlspecialchars($viaje['nombre_viaje']); ?>
                            </h4>

                            <div>
                                <?php if ($isOwner): ?>
                                <a href="rides_edit.php?id=<?php echo $viaje['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Editar</a>
                                <?php endif; ?>
                                <a href="<?php echo $isOwner ? 'rides.php' : '../rides.php'; ?>" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left"></i> Volver</a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="text-center mb-4 p-4 bg-light rounded">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5><i class="fas fa-map-marker-alt text-danger fa-2x"></i><br>
                                        <strong><?php echo htmlspecialchars($viaje['origen']); ?></strong>
                                    </h5>
                                </div>

                                <div class="col-2"><i class="fas fa-arrow-right text-primary fa-3x"></i></div>

                                <div class="col">
                                    <h5><i class="fas fa-map-marker-check text-success fa-2x"></i><br>
                                        <strong><?php echo htmlspecialchars($viaje['destino']); ?></strong>
                                    </h5>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-user-tie fa-4x text-primary mb-3"></i>

                                        <h5 class="card-title">
                                            <?php echo htmlspecialchars($viaje['chofer_nombre'].' '.$viaje['chofer_apellido']); ?>
                                        </h5>

                                        <p class="text-muted">
                                            <i class="fas fa-car"></i> 
                                            <?php echo htmlspecialchars($viaje['marca'].' '.$viaje['modelo']); ?>
                                            <span class="text-secondary">(<?php echo $viaje['color']; ?>)</span>
                                        </p>

                                        <p class="small text-muted"><i class="fas fa-id-card"></i> Placa: <?php echo $viaje['placa']; ?></p>

                                        <?php if ($isPassenger): ?>
                                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($viaje['chofer_telefono']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6><i class="fas fa-info-circle text-primary"></i> Información del Viaje</h6>
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <td><strong>Fecha y Hora:</strong></td>
                                        <td>
                                            <i class="fas fa-calendar-alt"></i>
                                            <?php echo date('d/m/Y', strtotime($viaje['fecha_hora_salida'])); ?><br>

                                            <i class="fas fa-clock"></i>
                                            <?php echo date('h:i A', strtotime($viaje['fecha_hora_salida'])); ?>

                                            <?php if ($viaje['hora_llegada']): ?>
                                                → <?php echo date('h:i A', strtotime($viaje['hora_llegada'])); ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><strong>Cupos:</strong></td>
                                        <td>
                                            <span class="badge bg-<?php echo $viaje['cupos_disponibles'] > 0 ? 'success':'danger'; ?>">
                                                <?php echo $viaje['cupos_disponibles']; ?> disponibles de <?php echo $viaje['cupos_totales']; ?>
                                            </span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><strong>Precio por asiento:</strong></td>
                                        <td><h4 class="text-success mb-0">₡<?php echo number_format($viaje['precio_por_asiento'], 0); ?></h4></td>
                                    </tr>

                                    <?php if ($viaje['dias_semana']): ?>
                                    <tr>
                                        <td><strong>Días:</strong></td>
                                        <td>
                                            <?php 
                                            $dias = json_decode($viaje['dias_semana'], true);
                                            echo is_array($dias) ? implode(', ', $dias) : htmlspecialchars($viaje['dias_semana']);
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>

                                    <tr>
                                        <td><strong>Creado:</strong></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($viaje['creado_en'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <?php if (!empty($viaje['notas'])): ?>
                        <hr class="my-4">
                        <h6><i class="fas fa-sticky-note text-warning"></i> Notas del Chofer</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <?php echo nl2br(htmlspecialchars($viaje['notas'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <hr class="my-4">

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">

                            <?php if ($isPassenger && $viaje['cupos_disponibles']>0 && strtotime($viaje['fecha_hora_salida'])>time()): ?>
                                <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#reserveModal">
                                    <i class="fas fa-ticket-alt"></i> Reservar Asiento
                                </button>
                            <?php endif; ?>

                            <?php if ($isOwner): ?>
                                <button class="btn btn-outline-danger btn-lg" onclick="confirmDelete(<?php echo $viaje['id']; ?>)">
                                    <i class="fas fa-trash"></i> Eliminar Viaje
                                </button>
                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function confirmDelete(id) {
    document.getElementById('deleteId').value = id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

</body>
</html>