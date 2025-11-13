<?php
require_once 'database.php';


function getConnection() {
    global $host, $db, $user, $password;
    try {
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

/* Vehiculos de chofer */
function getVehiculosByChofer($user_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * 
                           FROM vehiculos 
                           WHERE user_id = ? 
                           ORDER BY fecha_registro DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

/* Vehiculos aprobados*/ 
function getVehiculosAprobadosByChofer($user_id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * 
                           FROM vehiculos 
                           WHERE user_id = ? AND estado = 'aprobado'
                           ORDER BY marca, modelo");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

/* Vehicuo por ID*/
function getVehiculoById($id) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM vehiculos WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}


 /* Crear vehículo */
function createVehiculo($data) {
    $pdo = getConnection();

    $sql = "INSERT INTO vehiculos 
            (user_id, marca, modelo, ano, color, placa, foto, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente')";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['user_id'],
        $data['marca'],
        $data['modelo'],
        $data['ano'],
        $data['color'],
        $data['placa'],
        $data['foto'] ?? null
    ]);
}

/* Editar vehiculo */
function updateVehiculo($id, $data) {
    $pdo = getConnection();

    $sql = "UPDATE vehiculos SET marca = ?, modelo = ?, ano = ?, color = ?, placa = ?, foto = ? WHERE id = ? AND estado = 'pendiente'";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['marca'],
        $data['modelo'],
        $data['ano'],
        $data['color'],
        $data['placa'],
        $data['foto'],
        $id
    ]);
}

/* Eliminar vehiculo */
function deleteVehiculo($id, $user_id) {
    $pdo = getConnection();

    $stmt = $pdo->prepare("DELETE FROM vehiculos 
                           WHERE id = ? AND user_id = ? AND estado = 'pendiente'");
    return $stmt->execute([$id, $user_id]);
}

function validateVehiculo($data) {
    $errors = [];

    if (empty(trim($data['marca'] ?? '')))
        $errors[] = "La marca es obligatoria";

    if (empty(trim($data['modelo'] ?? '')))
        $errors[] = "El modelo es obligatorio";

    if (empty(trim($data['color'] ?? '')))
        $errors[] = "El color es obligatorio";

    if (empty(trim($data['placa'] ?? '')))
        $errors[] = "La placa es obligatoria";

    if (empty($data['ano']) || $data['ano'] < 1990 || $data['ano'] > (intval(date("Y")) + 1))
        $errors[] = "El año ingresado no es válido";

    return $errors;
}