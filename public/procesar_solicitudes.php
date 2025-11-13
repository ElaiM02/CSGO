<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';
require_once '../config/database.php';

checkAuth();
if ($_SESSION['rol'] !== 'chofer') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: solicitudes_chofer.php");
    exit;
}

$solicitud_id = intval($_POST['solicitud_id'] ?? 0);
$viaje_id = intval($_POST['viaje_id'] ?? 0);
$accion = $_POST['accion'] ?? '';

if ($solicitud_id <= 0 || $viaje_id <= 0 || !in_array($accion, ['aceptar', 'rechazar'])) {
    $_SESSION['error'] = "Datos inválidos.";
    header("Location: solicitudes_chofer.php");
    exit;
}

global $conn;

// Verificar que el viaje pertenece al chofer
$stmt = $conn->prepare("SELECT chofer_id, cupos_disponibles FROM viajes WHERE id = ?");
$stmt->bind_param("i", $viaje_id);
$stmt->execute();
$result = $stmt->get_result();
$viaje = $result->fetch_assoc();

if (!$viaje || $viaje['chofer_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "No tienes permiso.";
    header("Location: solicitudes_chofer.php");
    exit;
}

$conn->begin_Transaction();

try {
    if ($accion === 'aceptar') {
        if ($viaje['cupos_disponibles'] <= 0) {
            throw new Exception("No hay cupos disponibles.");
        }

        // Estado Aceptado
        $stmt = $conn->prepare("UPDATE solicitudes SET estado = 'aceptada' WHERE id = ?");
        $stmt->execute([$solicitud_id]);

        // Reduce cupos
        $stmt = $conn->prepare("UPDATE viajes SET cupos_disponibles = cupos_disponibles - 1 WHERE id = ?");
        $stmt->execute([$viaje_id]);

        $_SESSION['success'] = "¡Solicitud aceptada! Se redujo un cupo.";
    } else {
        // Estado Rechazodo
        $stmt = $conn->prepare("UPDATE solicitudes SET estado = 'rechazada' WHERE id = ?");
        $stmt->execute([$solicitud_id]);

        $_SESSION['success'] = "Solicitud rechazada.";
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

header("Location: solicitudes_chofer.php");
exit;