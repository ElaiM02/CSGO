<?php
require_once '../config/start_app.php';
require_once '../config/database.php';
require_once '../config/functions.php';

checkAuth();
if ($_SESSION['rol'] !== 'pasajero') {
    $_SESSION['error'] = "Acceso denegado.";
    header("Location: dashboard.php");
    exit;
}

$pasajero_id = $_SESSION['user_id'];

global $conn;

// Obtener viajes aceptados del pasajero
$sql = "
    SELECT s.id AS solicitud_id, s.estado, s.fecha_solicitud,
           v.id AS viaje_id, v.nombre_viaje, v.origen, v.destino, v.fecha_hora_salida,
           u.nombre AS chofer_nombre
    FROM solicitudes s
    JOIN viajes v ON s.viaje_id = v.id
    JOIN usuarios u ON v.chofer_id = u.id
    WHERE s.pasajero_id = ? AND s.estado = 'aceptada'
    ORDER BY v.fecha_hora_salida ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pasajero_id);
$stmt->execute();
$result = $stmt->get_result();
$viajes = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Viajes - Aventones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <style>
        .viaje-card { transition: all 0.2s; }
        .viaje-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="text-center mb-4">
            <h2 class="text-primary fw-bold">Mis Viajes Reservados</h2>
            <p class="text-muted">Gestiona tus rides: confirma o cancela</p>
        </div>

        <?php if (empty($viajes)): ?>
            <div class="alert alert-info text-center p-5">
                <h5>No tienes viajes reservados</h5>
                <p>Ve a <a href="buscar_rides.php">Buscar Viajes</a> para reservar uno.</p>
            </div>
        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <?php foreach ($viajes as $v): ?>
                        <div class="card mb-3 viaje-card shadow-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="card-title mb-1 text-primary">
                                            <?= htmlspecialchars($v['nombre_viaje']) ?>
                                        </h5>
                                        <p class="mb-1">
                                            <strong>Ruta:</strong> <?= htmlspecialchars($v['origen']) ?> → <?= htmlspecialchars($v['destino']) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Salida:</strong> <?= date('d/m/Y H:i', strtotime($v['fecha_hora_salida'])) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Chofer:</strong> <?= htmlspecialchars($v['chofer_nombre']) ?>
                                        </p>
                                        <small class="text-success">
                                            Reservado: <?= date('d/m/Y H:i', strtotime($v['fecha_solicitud'])) ?>
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <form method="POST" action="cancelar_reserva.php" class="d-inline">
                                            <input type="hidden" name="solicitud_id" value="<?= $v['solicitud_id'] ?>">
                                            <input type="hidden" name="viaje_id" value="<?= $v['viaje_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm px-4"
                                                    onclick="return confirm('¿Cancelar esta reserva? Se liberará el cupo.')">
                                                Cancelar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>