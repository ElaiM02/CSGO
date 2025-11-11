<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';
require_once '../config/ride_functions.php';

checkAuth();

if (!isChofer()) {
    $_SESSION['error'] = "Solo los choferes pueden publicar viajes.";
    header("Location: ../dashboard.php");
    exit;
}

$errors = [];
$nombre_viaje = $origen = $destino = $fecha_hora_salida = $hora_llegada = $precio_por_asiento = $notas = '';
$cupos_totales = 4;
$vehiculo_id = '';
$dias_semana = [];

// Obtener vehículos del chofer
$vehiculos = getVehiculosByChofer($_SESSION['user_id']);

if (empty($vehiculos)) {
    $_SESSION['error'] = "Debes registrar un vehículo primero.";
    header("Location: ../registroVehiculos.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_viaje = trim($_POST['nombre_viaje'] ?? '');
    $origen = trim($_POST['origen'] ?? '');
    $destino = trim($_POST['destino'] ?? '');
    $fecha_hora_salida = $_POST['fecha_hora_salida'] ?? '';
    $hora_llegada = $_POST['hora_llegada'] ?? '';
    $precio_por_asiento = trim($_POST['precio_por_asiento'] ?? '');
    $cupos_totales = intval($_POST['cupos_totales'] ?? 4);
    $notas = trim($_POST['notas'] ?? '');
    $vehiculo_id = intval($_POST['vehiculo_id'] ?? 0);
    $dias_semana = $_POST['dias_semana'] ?? [];

    $data = compact('nombre_viaje', 'origen', 'destino', 'fecha_hora_salida', 'hora_llegada', 'precio_por_asiento', 'cupos_totales', 'notas', 'vehiculo_id');
    $data['chofer_id'] = $_SESSION['user_id'];
    $data['dias_semana'] = !empty($dias_semana) ? json_encode($dias_semana) : null;

    $errors = validateViaje($data);

    if (empty($errors)) {
        if (createViaje($data)) {
            $_SESSION['success'] = "¡Viaje publicado exitosamente!";
            header("Location: gestion.php");
            exit;
        } else {
            $errors[] = "Error al publicar el viaje.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar Viaje - <?php echo SITIO; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-icon { color: #0d6efd; }
        .card-header { background: linear-gradient(135deg, #198754, #157347); color: white; }
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
                            <i class="fas fa-plus-circle"></i> Publicar Nuevo Viaje
                        </h4>
                        <a href="rides_create.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Mis Viajes
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
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
                                           maxlength="100" required placeholder="Ej: San José → Liberia">
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
                                           value="<?php echo htmlspecialchars($origen); ?>" required
                                           placeholder="Ej: Parque La Sabana, San José">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-map-marker-check text-success form-icon"></i> Lugar de Llegada <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="destino" class="form-control" 
                                           value="<?php echo htmlspecialchars($destino); ?>" required
                                           placeholder="Ej: Terminal de Liberia">
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
                                                       id="dia_<?php echo $dia; ?>"
                                                       <?php echo in_array($dia, $dias_semana) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="dia_<?php echo $dia; ?>">
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
                                <textarea name="notas" class="form-control" rows="3" maxlength="500"
                                          placeholder="Ej: Salida puntual, llevar cédula, no se permite comida fuerte...">
                                    <?php echo htmlspecialchars($notas); ?>
                                </textarea>
                                <div class="form-text">Máximo 500 caracteres</div>
                            </div>

                            <hr>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="gestion.php" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-paper-plane"></i> Publicar Viaje
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