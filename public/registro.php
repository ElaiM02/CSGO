<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php'; 

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    $id = $_POST['id'];
    $email = $_POST['email'];
    $number = $_POST['number'];
    $username = $_POST['user'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validar contraseñas
    if ($password !== $confirm_password) {
        echo "<script>alert('Las contraseñas no coinciden'); window.history.back();</script>";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO usuarios (nombre, apellido, cedula, email, telefono, usuario, password)
              VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssss", $name, $lastname, $id, $email, $number, $username, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('Usuario registrado exitosamente'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Error al registrar el usuario: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Aventones</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

    <div class="container">
        <div class="form-box active">
            <form action="registro.php" method="POST">
                <h2>Registrarse</h2>
                <input type="text" name="name" placeholder="Nombre" required>
                <input type="text" name="lastname" placeholder="Apellido" required>
                <input type="text" name="id" placeholder="Cédula" required>
                <input type="email" name="email" placeholder="Correo electrónico" required>
                <input type="tel" name="number" placeholder="Teléfono" required>
                <input type="text" name="user" placeholder="Nombre de Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <input type="password" name="confirm_password" placeholder="Confirmar Contraseña" required>
                <button type="submit" name="register">Registrarse</button>
                <p>¿Ya tienes una cuenta? <a href="login.php">Iniciar sesión</a></p> 
            </form>
        </div>
    </div>

</body>
</html>
