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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: rides_pasajero.php");
    exit;
}

$solicitud_id = intval($_POST['solicitud_id'] ?? 0);
$viaje_id = intval($_POST['viaje_id'] ?? 0);

if ($solicitud_id <= 0 || $viaje_id <= 0) {
    $_SESSION['error'] = "Datos inválidos.";
    header("Location: rides_pasajero.php");
    exit;
}

global $conn;

// Comprobacion de verificacion 
$stmt = $conn->prepare("SELECT id FROM solicitudes WHERE id = ? AND pasajero_id = ? AND estado = 'aceptada'");
$stmt->bind_param("ii", $solicitud_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Reserva no encontrada o ya cancelada.";
    header("Location: rides_pasajero.php");
    exit;
}

$conn->autocommit(FALSE);

try {
    // Estado Cancelada
    $stmt = $conn->prepare("UPDATE solicitudes SET estado = 'cancelada' WHERE id = ?");
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();

    // Libera Cupos
    $stmt = $conn->prepare("UPDATE viajes SET cupos_disponibles = cupos_disponibles + 1 WHERE id = ?");
    $stmt->bind_param("i", $viaje_id);
    $stmt->execute();

    $conn->commit();
    $_SESSION['success'] = "¡Reserva cancelada! El cupo ha sido liberado.";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

$conn->autocommit(TRUE);
header("Location: rides_pasajero.php");
exit;