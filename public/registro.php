<!DOCTYPE html>
<html lang="en">
  
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Aventones</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>

    <div class="container">
        <div class="form-box active" id="login-form">
            <form action="">
                <h2>Login</h2>
                <input type="user" name="user" placeholder="Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" name="login">Ingresar</button>
                <p>No tienes una cuenta? <a href="#" onclick="showForm('register-form')">Registrarse</a></p>
            </form>
        </div>

        <div class="form-box" id="register-form">
            <form action="">
                <h2>Registrarse</h2>
                <input type="text" name="name" placeholder="Nombre" required>
                <input type="text" name="lastname" placeholder="Apellido" required>
                <input type="text" name="id" placeholder="Cedula" required>
                <input type="text" name="email" placeholder="Correo electronico" required>
                <input type="tel" name="number" placeholder="Telefono" required>
                <input type="text" name="user" placeholder="Nombre de Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" name="register">Registrarse</button>
                <p>Ya tienes una cuenta? <a href="#" onclick="showForm('login-form')">Login</a></p> 
            </form>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>

</html>
