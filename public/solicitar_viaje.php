<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';
require_once '../config/ride_functions.php';

checkAuth();
if ($_SESSION['rol'] !== 'pasajero') {
    $_SESSION['error'] = "Solo pasajeros pueden solicitar.";
    header("Location: buscar_viajes.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['error'] = "Viaje no válido.";
    header("Location: buscar_viajes.php");
    exit;
}

$viaje = getViajeById($id);
if (!$viaje || $viaje['estado'] !== 'activo' || $viaje['cupos_disponibles'] <= 0) {
    $_SESSION['error'] = "Este viaje no está disponible.";
    header("Location: buscar_rides.php");
    exit;
}

// Verificar si ya solicitó
$pdo = getConnection();
$stmt = $pdo->prepare("SELECT id FROM solicitudes WHERE viaje_id = ? AND pasajero_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
if ($stmt->fetch()) {
    $_SESSION['error'] = "Ya has solicitado este viaje.";
    header("Location: buscar_rides.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        INSERT INTO solicitudes (viaje_id, pasajero_id) 
        VALUES (?, ?)
    ");
    if ($stmt->execute([$id, $_SESSION['user_id']])) {
        $_SESSION['success'] = "¡Solicitud enviada! El chofer te notificará.";
        header("Location: buscar_rides.php");
        exit;
    } else {
        $error = "No se pudo enviar la solicitud.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Viaje - Aventones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4>Solicitar Viaje</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <div class="alert alert-info">
                            <strong>Viaje:</strong> <?= htmlspecialchars($viaje['nombre_viaje']) ?><br>
                            <strong>Ruta:</strong> <?= htmlspecialchars($viaje['origen']) ?> → <?= htmlspecialchars($viaje['destino']) ?><br>
                            <strong>Salida:</strong> <?= date('d/m/Y H:i', strtotime($viaje['fecha_hora_salida'])) ?><br>
                            <strong>Precio:</strong> ₡<?= number_format($viaje['precio_por_asiento']) ?><br>
                            <strong>Cupos disponibles:</strong> <?= $viaje['cupos_disponibles'] ?>
                        </div>

                        <form method="POST">
                            <p class="text-center">
                                <strong>¿Confirmas que deseas solicitar este viaje?</strong>
                            </p>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="buscar_rides.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-success">Confirmar Solicitud</button>
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