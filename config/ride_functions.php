<?php
require_once 'database.php';

function getConnection() {
    global $host, $db, $user, $password;
    try {
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

// Obtener todos los Rides
function getViajesDisponibles() {
    $pdo = getConnection();
    $sql = "SELECT v.*, u.nombre AS chofer_nombre, u.apellido AS chofer_apellido,veh.marca, veh.modelo, veh.color, veh.placa FROM viajes v
            JOIN usuarios u ON v.chofer_id = u.id JOIN vehiculos veh ON v.vehiculo_id = veh.id WHERE v.fecha_hora_salida > NOW() 
            AND v.cupos_disponibles > 0 AND v.estado = 'activo' ORDER BY v.fecha_hora_salida ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

// Obtener rides por el ID
function getViajeById($id) {
    $pdo = getConnection();
    $sql = "SELECT v.*, u.nombre AS chofer_nombre, u.apellido AS chofer_apellido, u.telefono, veh.marca, veh.modelo, veh.color, veh.placa FROM viajes v
            JOIN usuarios u ON v.chofer_id = u.id JOIN vehiculos veh ON v.vehiculo_id = veh.id WHERE v.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Obtener rides del chofer
function getViajesByChofer($chofer_id) {
    $pdo = getConnection();
    $sql = "SELECT v.*, veh.marca, veh.modelo, veh.placa FROM viajes v JOIN vehiculos veh ON v.vehiculo_id = veh.id WHERE v.chofer_id = ? ORDER BY v.fecha_hora_salida DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$chofer_id]);
    return $stmt->fetchAll();
}

// Crear nuevo ride
function createViaje($data) {
    $pdo = getConnection();
    $sql = "INSERT INTO viajes (chofer_id, vehiculo_id, nombre_viaje, origen, destino, fecha_hora_salida, hora_llegada, precio_por_asiento, 
             cupos_totales, cupos_disponibles, notas, dias_semana) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['chofer_id'],
        $data['vehiculo_id'],
        $data['nombre_viaje'],
        $data['origen'],
        $data['destino'],
        $data['fecha_hora_salida'],
        $data['hora_llegada'] ?? null,
        $data['precio_por_asiento'],
        $data['cupos_totales'],
        $data['cupos_totales'],
        $data['notas'] ?? null,
        $data['dias_semana'] ?? null
    ]);
}

// Actualizar rides
function updateViaje($id, $data) {
    $pdo = getConnection();
    $sql = "UPDATE viajes SET nombre_viaje = ?, origen = ?, destino = ?, fecha_hora_salida = ?, hora_llegada = ?, 
            precio_por_asiento = ?, cupos_totales = ?, notas = ?, vehiculo_id = ?, dias_semana = ? WHERE id = ? AND chofer_id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $data['nombre_viaje'],
        $data['origen'],
        $data['destino'],
        $data['fecha_hora_salida'],
        $data['hora_llegada'] ?? null,
        $data['precio_por_asiento'],
        $data['cupos_totales'],
        $data['notas'] ?? null,
        $data['vehiculo_id'],
        $data['dias_semana'] ?? null,
        $id,
        $data['chofer_id']
    ]);

    // Ajustar cupos disponibles si se reducen
    if ($result && $data['cupos_totales'] < $data['cupos_anteriores']) {
        $pdo->prepare("UPDATE viajes SET cupos_disponibles = ? WHERE id = ?")
            ->execute([$data['cupos_totales'], $id]);
    }
    return $result;
}

// Eliminar rides
function deleteViaje($id, $chofer_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("DELETE FROM viajes WHERE id = ? AND chofer_id = ?");
    return $stmt->execute([$id, $chofer_id]);
}

// Validar datos del rides
function validateViaje($data) {
    $errors = [];

    if (empty(trim($data['nombre_viaje'] ?? ''))) {
        $errors[] = "El nombre del viaje es obligatorio";
    }
    if (empty(trim($data['origen'] ?? ''))) {
        $errors[] = "El lugar de salida es obligatorio";
    }
    if (empty(trim($data['destino'] ?? ''))) {
        $errors[] = "El lugar de llegada es obligatorio";
    }
    if (empty($data['fecha_hora_salida'] ?? '')) {
        $errors[] = "La fecha y hora de salida es obligatoria";
    } elseif (strtotime($data['fecha_hora_salida']) < time()) {
        $errors[] = "La fecha de salida debe ser futura";
    }
    if (empty($data['precio_por_asiento']) || !is_numeric($data['precio_por_asiento']) || $data['precio_por_asiento'] < 100) {
        $errors[] = "El precio debe ser mayor a ₡100";
    }
    if (empty($data['cupos_totales']) || $data['cupos_totales'] < 1 || $data['cupos_totales'] > 8) {
        $errors[] = "Los cupos deben estar entre 1 y 8";
    }
    if (empty($data['vehiculo_id'])) {
        $errors[] = "Debes seleccionar un vehículo";
    }

    return $errors;
}

// Obtener vehículos del chofer
function getVehiculosByChofer($chofer_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM vehiculos WHERE user_id = ? AND estado = 'aprobado' ORDER BY marca, modelo");
    $stmt->execute([$chofer_id]);
    return $stmt->fetchAll();
}

// Crear vehículo
function createVehiculo($chofer_id, $marca, $modelo, $placa, $color = null, $año = null) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("INSERT INTO vehiculos (chofer_id, marca, modelo, placa, color, año) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$chofer_id, $marca, $modelo, $placa, $color, $año]);
}