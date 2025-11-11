<?php
require_once '../config/start_app.php';
require_once '../config/database.php';
require_once '../config/functions.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$usuario = trim($_POST["user"] ?? '');  
$contrasena = $_POST["password"] ?? '';

if (empty($usuario) || empty($contrasena)) {
    $_SESSION["error"] = "Por favor completa todos los campos.";
    header("Location: login.php");
    exit;
}

try {
    $query = "SELECT id, usuario, password, rol FROM usuarios WHERE usuario = ? AND activo = 1 LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && (
        password_verify($contrasena, $user['password']) ||     // ← usuarios del formulario
        $contrasena === $user['password']
        )) {
        $_SESSION["usuario"] = $user['usuario'];
        $_SESSION["user_id"] = $user['id'];
        $_SESSION["rol"] = $user['rol'];

        unset($_SESSION["error"]);

        if (isAdmin()) {
            header("Location: dashboard.php");
        } elseif (isChofer()) {
            header("Location: dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $_SESSION["error"] = "Usuario o contraseña incorrectos.";
    }
} catch (Exception $e) {
    error_log("Error de login: " . $e->getMessage());
    $_SESSION["error"] = "Error del sistema. Intente más tarde.";
}

header("Location: login.php");
exit;
?>