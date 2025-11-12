<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: registro.php");
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$cedula = trim($_POST['cedula'] ?? '');
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
$correo = trim($_POST['correo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$usuario = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($nombre) || empty($apellidos) || empty($cedula) || empty($fecha_nacimiento) || 
    empty($correo) || empty($usuario) || empty($password) || empty($confirm_password)) {
    $_SESSION['error'] = "Todos los campos obligatorios son requeridos.";
    header("Location: registro.php");
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = "Las contraseñas no coinciden.";
    header("Location: registro.php");
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = "La contraseña debe tener al menos 6 caracteres.";
    header("Location: registro.php");
    exit;
}

if (!preg_match("/^[0-9]{9,10}$/", $cedula)) {
    $_SESSION['error'] = "Cédula inválida. Debe tener 9 o 10 dígitos.";
    header("Location: registro.php");
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Correo electrónico inválido.";
    header("Location: registro.php");
    exit;
}

// Subida de foto (opcional)
$foto = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['foto']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        $_SESSION['error'] = "Formato de imagen no permitido. Usa JPG, PNG o GIF.";
        header("Location: registro.php");
        exit;
    }
    
    if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
        $_SESSION['error'] = "La imagen no debe exceder 2MB.";
        header("Location: registro.php");
        exit;
    }
    
    $upload_dir = '../uploads/usuarios/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    
    $foto = 'usuarios/' . uniqid() . '.' . $ext;
    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . basename($foto))) {
        $_SESSION['error'] = "Error al subir la imagen.";
        header("Location: registro.php");
        exit;
    }
}

try {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO usuarios 
              (nombre, apellido, cedula, fecha_nacimiento, email, telefono, foto, usuario, password, rol) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pasajero')";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssss", $nombre, $apellidos, $cedula, $fecha_nacimiento, $correo, $telefono, $foto, $usuario, $hashed_password);

    if ($stmt->execute()) {
        $user_id = $conn->insert_id; 
        $_SESSION['usuario'] = $usuario;
        $_SESSION['rol'] = 'pasajero';
        $_SESSION['user_id'] = $user_id;
        $_SESSION['success'] = "¡Registro exitoso! Ya puedes iniciar sesión como pasajero.";
        header("Location: login.php");
        exit;
    } else {
        if ($conn->errno == 1062) {
            $error = $conn->error;
            if (strpos($error, 'cedula') !== false) {
                $_SESSION['error'] = "Esta cédula ya está registrada.";
            } elseif (strpos($error, 'email') !== false) {
                $_SESSION['error'] = "Este correo ya está en uso.";
            } elseif (strpos($error, 'usuario') !== false) {
                $_SESSION['error'] = "Este nombre de usuario ya existe.";
            } else {
                $_SESSION['error'] = "Datos duplicados.";
            }
        } else {
            $_SESSION['error'] = "Error al registrar. Intente más tarde.";
        }
        header("Location: registro.php");
        exit;
    }
} catch (Exception $e) {
    error_log("Error en registro: " . $e->getMessage());
    $_SESSION['error'] = "Error del sistema. Intente más tarde.";
    header("Location: registro.php");
    exit;
}
?>