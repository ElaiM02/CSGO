<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';
require_once '../config/ride_functions.php';

checkAuth();
if (!isChofer()) { $_SESSION['error'] = "Acceso denegado."; header("Location: ../dashboard.php"); exit; }

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { $_SESSION['error'] = 'Viaje no válido'; header('Location: ../dashboard.php'); exit; }

$viaje = getViajeById($id);
if (!$viaje || $viaje['chofer_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = 'No tienes permiso';
    header('Location: ../dashboard.php');
    exit;
}

$errors = [];
$vehiculos = getVehiculosByChofer($_SESSION['user_id']);

$nombre_viaje = $viaje['nombre_viaje'];
$origen = $viaje['origen'];
$destino = $viaje['destino'];
$fecha_hora_salida = date('Y-m-d\TH:i', strtotime($viaje['fecha_hora_salida']));
$hora_llegada = $viaje['hora_llegada'] ? date('H:i', strtotime($viaje['hora_llegada'])) : '';
$precio_por_asiento = $viaje['precio_por_asiento'];
$cupos_totales = $viaje['cupos_totales'];
$notas = $viaje['notas'];
$vehiculo_id = $viaje['vehiculo_id'];
$dias_semana = $viaje['dias_semana'] ? json_decode($viaje['dias_semana'], true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$nombre_viaje = trim($_POST['nombre_viaje'] ?? '');
$origen = trim($_POST['origen'] ?? '');
$destino = trim($_POST['destino'] ?? '');
$fecha_hora_salida = $_POST['fecha_hora_salida'] ?? '';
$hora_llegada = !empty($_POST['hora_llegada']) ? $_POST['hora_llegada'] . ':00' : null;
$precio_por_asiento = (float)($_POST['precio_por_asiento'] ?? 0);
$cupos_totales = (int)($_POST['cupos_totales'] ?? 0);
$notas = trim($_POST['notas'] ?? '');
$vehiculo_id = (int)($_POST['vehiculo_id'] ?? 0);
$dias_semana = $_POST['dias_semana'] ?? [];


    $data = [
        'nombre_viaje' => $nombre_viaje,
        'origen' => $origen,
        'destino' => $destino,
        'fecha_hora_salida' => $fecha_hora_salida,
        'hora_llegada' => $hora_llegada,
        'precio_por_asiento' => $precio_por_asiento,
        'cupos_totales' => $cupos_totales,
        'notas' => $notas,
        'vehiculo_id' => $vehiculo_id,
        'dias_semana' => !empty($dias_semana) ? json_encode($dias_semana) : null,
        'chofer_id' => $_SESSION['user_id'],
        'cupos_anteriores' => $viaje['cupos_totales']
    ];

    $errors = validateViaje($data);

    if (empty($errors)) {
        if (updateViaje($id, $data)) {
            $_SESSION['success'] = "¡Viaje actualizado!";
            header("Location: rides.php");
            exit;
        } else {
            $errors[] = "Error al actualizar";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Viaje - <?php echo SITIO; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-header { background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; }
        .form-icon { color: #ffc107; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>
    
    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-edit"></i> Editar Viaje
                        </h4>
                        <div>
                            <a href="rides_view.php?id=<?php echo $id; ?>" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-eye"></i> Ver Detalle
                            </a>
                            <a href="rides.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Mis Viajes
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>Por favor corrige los siguientes errores:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-route form-icon"></i> Nombre del Viaje <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="nombre_viaje" class="form-control" 
                                           value="<?php echo htmlspecialchars($nombre_viaje); ?>" 
                                           maxlength="100" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-car form-icon"></i> Vehículo <span class="text-danger">*</span>
                                    </label>
                                    <select name="vehiculo_id" class="form-select" required>
                                        <option value="">Selecciona tu vehículo</option>
                                        <?php foreach ($vehiculos as $veh): ?>
                                            <option value="<?php echo $veh['id']; ?>" 
                                                    <?php echo $vehiculo_id == $veh['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($veh['marca'] . ' ' . $veh['modelo'] . ' - ' . $veh['placa']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-map-marker-alt text-danger form-icon"></i> Lugar de Salida <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="origen" class="form-control" 
                                           value="<?php echo htmlspecialchars($origen); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-map-marker-check text-success form-icon"></i> Lugar de Llegada <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="destino" class="form-control" 
                                           value="<?php echo htmlspecialchars($destino); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-alt form-icon"></i> Fecha y Hora de Salida <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" name="fecha_hora_salida" class="form-control" 
                                           value="<?php echo htmlspecialchars($fecha_hora_salida); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-clock form-icon"></i> Hora Estimada de Llegada
                                    </label>
                                    <input type="time" name="hora_llegada" class="form-control" 
                                           value="<?php echo htmlspecialchars($hora_llegada); ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-money-bill-wave form-icon"></i> Precio por Asiento (₡) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">₡</span>
                                        <input type="number" name="precio_por_asiento" class="form-control" 
                                               value="<?php echo htmlspecialchars($precio_por_asiento); ?>" 
                                               min="100" step="100" required>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-users form-icon"></i> Cupos Totales <span class="text-danger">*</span>
                                    </label>
                                    <select name="cupos_totales" class="form-select">
                                        <?php for ($i = 1; $i <= 8; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo $cupos_totales == $i ? 'selected' : ''; ?>>
                                                <?php echo $i; ?> <?php echo $i == 1 ? 'cupo' : 'cupos'; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-week form-icon"></i> Días Recurrentes
                                    </label>
                                    <div>
                                        <?php $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']; ?>
                                        <?php foreach ($dias as $dia): ?>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="dias_semana[]" value="<?php echo $dia; ?>"
                                                       id="dia_edit_<?php echo $dia; ?>"
                                                       <?php echo in_array($dia, $dias_semana) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="dia_edit_<?php echo $dia; ?>">
                                                    <?php echo substr($dia, 0, 3); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-sticky-note form-icon"></i> Notas Adicionales
                                </label>
                                <textarea name="notas" class="form-control" rows="3" maxlength="500">
                                    <?php echo htmlspecialchars($notas); ?>
                                </textarea>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Información del viaje:</strong><br>
                                <small>
                                    ID: #<?php echo $viaje['id']; ?> | 
                                    Creado: <?php echo date('d/m/Y H:i', strtotime($viaje['creado_en'])); ?> |
                                    Cupos disponibles: <?php echo $viaje['cupos_disponibles']; ?>
                                </small>
                            </div>

                            <hr>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="rides.php" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-warning btn-lg">
                                    <i class="fas fa-save"></i> Actualizar Viaje
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>