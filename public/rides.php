<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';

checkAuth();

// SOLO CHOFERES PUEDEN ACCEDER
if (!isChofer()) {
    $_SESSION['error'] = "Acceso denegado. Solo choferes pueden gestionar viajes.";
    header("Location: ../dashboard.php");
    exit;
}

// Obtener viajes del chofer actual
$query = "SELECT v.*, veh.marca, veh.modelo, veh.placa 
          FROM viajes v 
          JOIN vehiculos veh ON v.vehiculo_id = veh.id 
          WHERE v.chofer_id = ? 
          ORDER BY v.fecha_hora_salida DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$viajes = $result->fetch_all(MYSQLI_ASSOC);

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
    <title>Mis Viajes - <?php echo SITIO; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/rides.css" rel="stylesheet">
    <style>
        .viaje-card { transition: all 0.3s; }
        .viaje-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important; }
    </style>
</head>
<body>

<?php include __DIR__ . '/navbar.php'; ?>

<!-- 🔥 ESTE WRAPPER ES EL QUE ARREGLA TODO -->
<div class="ride-container">
    <div class="container mt-4 mb-5">

        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="fas fa-route text-primary"></i> 
                        Mis Viajes Publicados
                    </h2>
                    <a href="rides_create.php" class="btn btn-success btn-lg">
                        <i class="fas fa-plus-circle"></i> Publicar Nuevo Viaje
                    </a>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($viajes)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-car-side fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted">Aún no has publicado ningún viaje</h4>
                        <p class="text-muted">¡Empieza a ganar dinero compartiendo tu carro!</p>
                        <a href="rides_create.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus"></i> Publicar mi primer viaje
                        </a>
                    </div>
                <?php else: ?>

                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php foreach ($viajes as $v): ?>
                            <div class="col">
                                <div class="card h-100 viaje-card shadow-sm">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="badge bg-<?php echo strtotime($v['fecha_hora_salida']) > time() ? 'primary' : 'secondary'; ?>">
                                                <?php echo strtotime($v['fecha_hora_salida']) > time() ? 'Activo' : 'Finalizado'; ?>
                                            </span>
                                            <small class="text-muted">ID: #<?php echo $v['id']; ?></small>
                                        </div>

                                        <h5 class="card-title">
                                            <i class="fas fa-map-marker-alt text-danger"></i>
                                            <?php echo htmlspecialchars($v['origen']); ?>
                                        </h5>

                                        <p class="card-text mb-2">
                                            <i class="fas fa-arrow-down text-muted"></i><br>
                                            <i class="fas fa-map-marker-check text-success"></i>
                                            <?php echo htmlspecialchars($v['destino']); ?>
                                        </p>

                                        <hr class="my-2">

                                        <div class="small text-muted mb-2">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('d/m/Y', strtotime($v['fecha_hora_salida'])); ?><br>

                                            <i class="fas fa-clock"></i>
                                            Salida: <?php echo date('h:i A', strtotime($v['fecha_hora_salida'])); ?>
                                            <?php if ($v['hora_llegada']): ?>
                                                → <?php echo date('h:i A', strtotime($v['hora_llegada'])); ?>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mt-auto">
                                            <div class="row text-center mb-3">
                                                <div class="col">
                                                    <strong>₡<?php echo number_format($v['precio_por_asiento'], 0); ?></strong><br>
                                                    <small>por asiento</small>
                                                </div>
                                                <div class="col">
                                                    <strong><?php echo $v['cupos_disponibles']; ?>/<?php echo $v['cupos_totales']; ?></strong><br>
                                                    <small>cupos libres</small>
                                                </div>
                                            </div>

                                            <div class="btn-group w-100" role="group">
                                                <a href="rides_view.php?id=<?php echo $v['id']; ?>" class="btn btn-outline-info btn-sm">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
                                                <a href="rides_edit.php?id=<?php echo $v['id']; ?>" class="btn btn-outline-warning btn-sm">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                                        onclick="confirmDelete(<?php echo $v['id']; ?>, '<?php echo htmlspecialchars($v['origen'] . ' → ' . $v['destino']); ?>')">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-footer text-muted small">
                                        <i class="fas fa-car"></i> 
                                        <?php echo htmlspecialchars($v['marca'] . ' ' . $v['modelo']); ?> 
                                        (<?php echo $v['placa']; ?>)
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Eliminar Viaje</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de eliminar el viaje:</p>
                <strong id="viajeRuta"></strong>?
                <p class="text-danger mt-2">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" action="rides_delete.php" style="display:inline;">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmDelete(id, ruta) {
        document.getElementById('deleteId').value = id;
        document.getElementById('viajeRuta').textContent = ruta;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>

</body>
</html>
