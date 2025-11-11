<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';
require_once '../config/rides_functions.php';

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Método no permitido';
    header('Location: rides.php');
    exit();
}

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['error'] = 'ID inválido';
    header('Location: rides.php');
    exit();
}

$viaje = getViajeById($id);

if (!$viaje || $viaje['chofer_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = 'No tienes permiso para eliminar este viaje';
    header('Location: rides.php');
    exit();
}

if (deleteViaje($id, $_SESSION['user_id'])) {
    $_SESSION['success'] = 'Viaje eliminado: ' . htmlspecialchars($viaje['nombre_viaje']);
} else {
    $_SESSION['error'] = 'Error al eliminar el viaje';
}

header('Location: rides.php');
exit();
?>