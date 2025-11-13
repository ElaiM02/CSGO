<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';
require_once '../config/vehiculos_functions.php';

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Método no permitido';
    header('Location: vehiculos.php');
    exit();
}

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['error'] = 'ID inválido';
    header('Location: vehiculos.php');
    exit();
}

$vehiculo = getVehiculoById($id);

if (!$vehiculo || $vehiculo['user_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = 'No tienes permiso para eliminar este vehículo';
    header('Location: vehiculos.php');
    exit();
}

// Solo eliminar si está pendiente
if ($vehiculo['estado'] !== 'pendiente') {
    $_SESSION['error'] = 'Solo los vehículos en estado pendiente pueden eliminarse';
    header('Location: vehiculos.php');
    exit();
}

if (deleteVehiculo($id, $_SESSION['user_id'])) {

    // Eliminar foto si existe
    if ($vehiculo['foto'] && file_exists("../uploads/vehiculos/" . $vehiculo['foto'])) {
        unlink("../uploads/vehiculos/" . $vehiculo['foto']);
    }

    $_SESSION['success'] = 'Vehículo eliminado: ' . htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']);
} else {
    $_SESSION['error'] = 'Error al eliminar el vehículo';
}

header('Location: vehiculos.php');
exit();
?>
