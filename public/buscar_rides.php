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

$origen = trim($_GET['origen'] ?? '');
$destino = trim($_GET['destino'] ?? '');
$dia = $_GET['dia'] ?? '';
$fecha = $_GET['fecha'] ?? '';

// Construir consulta
$where = [];
$params = [];

if ($origen) {
    $where[] = "origen LIKE ?";
    $params[] = "%$origen%";
}
if ($destino) {
    $where[] = "destino LIKE ?";
    $params[] = "%$destino%";
}
if ($dia) {
    $where[] = "JSON_CONTAINS(dias_semana, ?)";
    $params[] = json_encode([(int)$dia]);
}
if ($fecha) {
    $where[] = "DATE(fecha_hora_salida) = ?";
    $params[] = $fecha;
}

$where[] = "v.estado = 'activo'";
$where[] = "cupos_disponibles > 0";

$sql = "SELECT v.*, u.nombre AS chofer_nombre, veh.marca, veh.modelo, veh.placa
        FROM viajes v
        JOIN usuarios u ON v.chofer_id = u.id
        JOIN vehiculos veh ON v.vehiculo_id = veh.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY fecha_hora_salida ASC";

$pdo = getConnection();
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$viajes = $stmt->fetchAll();

$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Viajes - Aventones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <style>
        .card-viaje { transition: transform 0.2s; }
        .card-viaje:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .badge-dia { font-size: 0.7rem; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5>Filtros de Búsqueda</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET">
                            <div class="mb-3">
                                <label class="form-label">Origen</label>
                                <input type="text" name="origen" class="form-control" 
                                       value="<?= htmlspecialchars($origen) ?>" placeholder="Ej: San José">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Destino</label>
                                <input type="text" name="destino" class="form-control" 
                                       value="<?= htmlspecialchars($destino) ?>" placeholder="Ej: Alajuela">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Día de la semana</label>
                                <select name="dia" class="form-select">
                                    <option value="">Cualquier día</option>
                                    <?php foreach ($dias_semana as $i => $dia): ?>
                                        <option value="<?= $i+1 ?>" <?= $dia == $dia ? 'selected' : '' ?>>
                                            <?= $dia ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fecha específica</label>
                                <input type="date" name="fecha" class="form-control" value="<?= $fecha ?>">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                Buscar Viajes jejej
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <h4 class="mb-3">
                    <?= $viajes ? count($viajes) : '0' ?> Viaje(s) encontrado(s)
                </h4>

                <?php if (empty($viajes)): ?>
                    <div class="alert alert-info text-center">
                        <strong>No se encontraron viajes.</strong><br>
                        Intenta con otros filtros.
                    </div>
                <?php else: ?>
                    <?php foreach ($viajes as $v): 
                        $dias_json = json_decode($v['dias_semana'] ?? '[]', true);
                        $dias_nombres = array_map(fn($d) => $dias_semana[array_search($d, $dias_semana)], $dias_json);                    ?>
                        <div class="card mb-3 card-viaje shadow-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="card-title mb-1">
                                            <?= htmlspecialchars($v['nombre_viaje']) ?>
                                        </h5>
                                        <p class="text-muted mb-2">
                                            <strong>De:</strong> <?= htmlspecialchars($v['origen']) ?> → 
                                            <strong>A:</strong> <?= htmlspecialchars($v['destino']) ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Salida:</strong> 
                                            <?= date('d/m/Y H:i', strtotime($v['fecha_hora_salida'])) ?>
                                            <?php if ($v['hora_llegada']): ?>
                                                → <?= date('H:i', strtotime($v['hora_llegada'])) ?>
                                            <?php endif; ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Chofer:</strong> <?= htmlspecialchars($v['chofer_nombre']) ?><br>
                                            <strong>Vehículo:</strong> <?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?> - <?= $v['placa'] ?>
                                        </p>
                                        <div>
                                            <?php foreach ($dias_nombres as $d): ?>
                                                <span class="badge bg-success badge-dia me-1"><?= substr($d, 0, 3) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <h4 class="text-primary fw-bold">₡<?= number_format($v['precio_por_asiento'], 0) ?></h4>
                                        <p class="text-success">
                                            <strong><?= $v['cupos_disponibles'] ?></strong> cupo(s) disponible(s)
                                        </p>
                                        <a href="solicitar_viaje.php?id=<?= $v['id'] ?>" 
                                           class="btn btn-success btn-sm">
                                            Solicitar
                                        </a>
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