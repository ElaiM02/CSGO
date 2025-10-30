<?php

session_start();
require_once '../config/database.php';

if (isset($_POST['login'])) {
    $username = $_POST['user'];
    $password = $_POST['password'];

    $query = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verificar contraseña
        if (password_verify($password, $user['password'])) {
            $_SESSION['usuario'] = $user['usuario'];
            header("Location: prueba.php");
            exit;
        } else {
            echo "<script>alert('Contraseña incorrecta');</script>";
        }
    } else {
        echo "<script>alert('Usuario no encontrado');</script>";
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
    <title>Login Aventones</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>

    <div class="container">
        <div class="form-box active">
            <form action="login.php" method="POST">
                <h2>Login</h2>
                <input type="text" name="user" placeholder="Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" name="login">Ingresar</button>
                <p>¿No tienes una cuenta? <a href="registro.php">Registrarse</a></p>
            </form>
        </div>
    </div>

</body>
</html>
