<?php

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}


function showError() {
    if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
        echo '<div style="background:#f8d7da; color:#721c24; padding:12px; border-radius:6px; margin-bottom:20px; text-align:center; font-size:14px;">';
        echo htmlspecialchars($_SESSION['error']);
        echo '</div>';
        unset($_SESSION['error']);
    }
}

function showSuccess() {
    if (isset($_SESSION['success']) && !empty($_SESSION['success'])) {
        echo '<div style="background:#d4edda; color:#155724; padding:12px; border-radius:6px; margin-bottom:20px; text-align:center; font-size:14px;">';
        echo htmlspecialchars($_SESSION['success']);
        echo '</div>';
        unset($_SESSION['success']);
    }
}

function checkRole($required_role) {
    checkAuth();
    if ($_SESSION['rol'] !== $required_role) {
        $_SESSION['error'] = "Acceso denegado.";
        header("Location: index.php"); exit;
    }
}

function getRol() {return isset($_SESSION['rol']) ? ucfirst($_SESSION['rol']) : 'Desconocido';}
function isChofer() {return isset($_SESSION['rol']) && $_SESSION['rol'] === 'chofer';}
function isAdmin() {return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';}
function isPasajero() {return isset($_SESSION['rol']) && $_SESSION['rol'] === 'pasajero';}


function redirectByAuth($loginPage = 'login.php', $homePage = 'index.php') {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['usuario'])) {
        header("Location: $homePage");
        exit();
    }
}

 // Usuario loguado
 
function getUserName() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['usuario'] ?? null;
}



// Solicitudes de vehiculo
function aprobarRechazarVehiculo(int $vehiculo_id, string $accion, mysqli $conn): bool
{
    if (!in_array($accion, ['aprobar', 'rechazar'])) {
        $_SESSION['error'] = "Acción inválida.";
        return false;
    }

    try {
        $conn->begin_transaction();

        $sql = "SELECT v.user_id, u.rol FROM vehiculos v JOIN usuarios u ON v.user_id = u.id WHERE v.id = ? AND v.estado = 'pendiente' LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $vehiculo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $vehiculo = $result->fetch_assoc();

        if (!$vehiculo) {
            $_SESSION['error'] = "Vehículo no encontrado o ya procesado.";
            $conn->rollback();
            return false;
        }

        $nuevo_estado = $accion === 'aprobar' ? 'aprobado' : 'rechazado';
        $fecha_aprobacion = $accion === 'aprobar' ? ', fecha_aprobacion = NOW()' : '';

        $sql = "UPDATE vehiculos SET estado = ? $fecha_aprobacion WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nuevo_estado, $vehiculo_id);
        $stmt->execute();

        // Cambio de pasajero a chofer
        if ($accion === 'aprobar' && $vehiculo['rol'] === 'pasajero') {
            $sql = "UPDATE usuarios SET rol = 'chofer' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $vehiculo['user_id']);
            $stmt->execute();
        }

        $_SESSION['success'] = "Vehículo " . ($accion === 'aprobar' ? 'aprobado' : 'rechazado') . " correctamente.";
        $conn->commit();
        return true;

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error aprobarRechazarVehiculo: " . $e->getMessage());
        $_SESSION['error'] = "Error del sistema.";
        return false;
    }
}
?>