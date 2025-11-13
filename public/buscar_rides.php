<?php
require_once '../config/start_app.php';
require_once '../config/ride_functions.php';
require_once '../config/functions.php';

checkAuth();

if (!in_array($_SESSION['rol'], ['pasajero', 'chofer'])) {
    $_SESSION['error'] = "Acceso denegado.";
    header("Location: dashboard.php");
    exit;
}

// Viajes Activos
$sql = "SELECT v.*, u.nombre AS chofer_nombre, veh.marca, veh.modelo, veh.placa FROM viajes v JOIN usuarios u ON v.chofer_id = u.id
        JOIN vehiculos veh ON v.vehiculo_id = veh.id WHERE v.estado = 'activo' AND v.cupos_disponibles > 0 ORDER BY v.fecha_hora_salida ASC";

$pdo = getConnection();
$stmt = $pdo->prepare($sql);
$stmt->execute();
$viajes = $stmt->fetchAll();
$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viajes Disponibles - Aventones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <style>
        .card-viaje { transition: transform 0.2s; }
        .card-viaje:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .badge-dia { font-size: 0.7rem; }
        .page-title { font-weight: 700; color: #0056b3; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="text-center mb-4">
            <h2 class="page-title">Viajes Disponibles</h2>
            <p class="text-muted">Encuentra un viaje compartido y solicita tu espacio</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <h4 class="mb-3 text-primary">
                    <?= count($viajes) ?> Viaje(s) disponible(s)
                </h4>

                <?php if (empty($viajes)): ?>
                    <div class="alert alert-info text-center p-5">
                        <h5>No hay viajes disponibles en este momento.</h5>
                        <p>Vuelve más tarde o contacta a un chofer.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($viajes as $v): 
                        $dias_json = json_decode($v['dias_semana'] ?? '[]', true);
                        $dias_nombres = array_map(fn($d) => $dias_semana[array_search($d, $dias_semana)], $dias_json);
                    ?>
                        <div class="card mb-3 card-viaje shadow-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="card-title mb-1 text-primary">
                                            <?= htmlspecialchars($v['nombre_viaje']) ?>
                                        </h5>
                                        <p class="text-muted mb-2">
                                            <strong>De:</strong> <?= htmlspecialchars($v['origen']) ?> → 
                                            <strong>A:</strong> <?= htmlspecialchars($v['destino']) ?>
                                        </p>
                                        <p class="mb-2"><strong>Salida:</strong> 
                                            <?= date('d/m/Y H:i', strtotime($v['fecha_hora_salida'])) ?>
                                            <?php if ($v['hora_llegada']): ?>
                                                → <?= date('H:i', strtotime($v['hora_llegada'])) ?>
                                            <?php endif; ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Chofer:</strong> <?= htmlspecialchars($v['chofer_nombre']) ?><br>
                                            <strong>Vehículo:</strong> <?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?> - <?= $v['placa'] ?></p>
                                        <div>
                                            <?php foreach ($dias_nombres as $d): ?>
                                                <span class="badge bg-success badge-dia me-1"><?= substr($d, 0, 3) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <h4 class="text-primary fw-bold">₡<?= number_format($v['precio_por_asiento'], 0) ?></h4>
                                        <p class="text-success"><strong><?= $v['cupos_disponibles'] ?></strong> cupo(s) disponible(s)</p>
                                        <a href="solicitar_viaje.php?id=<?= $v['id'] ?>" class="btn btn-success btn-sm px-4">Solicitar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>