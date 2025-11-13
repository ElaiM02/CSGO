<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';
require_once '../config/ride_functions.php';

checkAuth();
if ($_SESSION['rol'] !== 'chofer') {
    $_SESSION['error'] = "Solo choferes pueden ver solicitudes.";
    header("Location: dashboard.php");
    exit;
}

$chofer_id = $_SESSION['user_id'];

// Obtener solicitudes pendientes de los viajes del chofer
$sql = "
    SELECT s.id AS solicitud_id, s.estado, s.fecha_solicitud,
           v.id AS viaje_id, v.nombre_viaje, v.origen, v.destino, v.cupos_disponibles,
           u.nombre AS pasajero_nombre, u.email AS pasajero_email
    FROM solicitudes s
    JOIN viajes v ON s.viaje_id = v.id
    JOIN usuarios u ON s.pasajero_id = u.id
    WHERE v.chofer_id = ? AND s.estado = 'pendiente'
    ORDER BY s.fecha_solicitud DESC
";

$pdo = getConnection();
$stmt = $pdo->prepare($sql);
$stmt->execute([$chofer_id]);
$solicitudes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes Pendientes - Aventones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <style>
        .solicitud-card { transition: all 0.2s; }
        .solicitud-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .btn-action { min-width: 100px; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="text-center mb-4">
            <h2 class="text-primary fw-bold">Solicitudes Pendientes</h2>
            <p class="text-muted">Revisa y gestiona las solicitudes de pasajeros</p>
        </div>

        <?php if (empty($solicitudes)): ?>
            <div class="alert alert-info text-center p-5">
                <h5>No tienes solicitudes pendientes</h5>
                <p>Cuando un pasajero solicite un viaje, aparecerá aquí.</p>
            </div>
        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <?php foreach ($solicitudes as $s): ?>
                        <div class="card mb-3 solicitud-card shadow-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="card-title mb-1">
                                            <?= htmlspecialchars($s['pasajero_nombre']) ?>
                                        </h5>
                                        <p class="text-muted mb-1">
                                            <strong>Email:</strong> <?= htmlspecialchars($s['pasajero_email']) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Viaje:</strong> <?= htmlspecialchars($s['nombre_viaje']) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Ruta:</strong> <?= htmlspecialchars($s['origen']) ?> → <?= htmlspecialchars($s['destino']) ?>
                                        </p>
                                        <small class="text-muted">
                                            Solicitado: <?= date('d/m/Y H:i', strtotime($s['fecha_solicitud'])) ?>
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <p class="text-success mb-2">
                                            <strong><?= $s['cupos_disponibles'] ?></strong> cupo(s) disponible(s)
                                        </p>
                                        <div class="btn-group" role="group">
                                            <form method="POST" action="procesar_solicitudes.php" class="d-inline">
                                                <input type="hidden" name="solicitud_id" value="<?= $s['solicitud_id'] ?>">
                                                <input type="hidden" name="viaje_id" value="<?= $s['viaje_id'] ?>">
                                                <input type="hidden" name="accion" value="aceptar">
                                                <button type="submit" class="btn btn-success btn-action btn-sm" 
                                                        onclick="return confirm('¿Aceptar esta solicitud?')">
                                                    Aceptar
                                                </button>
                                            </form>
                                            <form method="POST" action="procesar_solicitudes.php" class="d-inline">
                                                <input type="hidden" name="solicitud_id" value="<?= $s['solicitud_id'] ?>">
                                                <input type="hidden" name="viaje_id" value="<?= $s['viaje_id'] ?>">
                                                <input type="hidden" name="accion" value="rechazar">
                                                <button type="submit" class="btn btn-danger btn-action btn-sm" 
                                                        onclick="return confirm('¿Rechazar esta solicitud?')">
                                                    Rechazar
                                                </button>
                                            </form>
                                        </div>
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