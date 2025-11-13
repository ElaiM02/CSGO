<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';

checkAuth(); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITIO; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
</head>
<body class = "login-bg">
    <?php include __DIR__ . '/navbar.php'; ?>


    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-body text-center p-5">
                        <h3>¡Bienvenido, <strong><?php echo htmlspecialchars(getUserName()); ?></strong>!</h3>
                        <p class="lead">
                            <span class="badge bg-primary fs-5">Rol: <?php echo getRol(); ?></span>
                        </p>
                        <hr>
                        <?php if (isPasajero()): ?>
                            <a href="buscar_rides.php" class="btn btn-primary btn-lg">Buscar Viajes</a>
                            <a href="registroVehiculos.php" class="btn btn-success btn-lg">Registrar Vehículo</a>
                        <?php elseif (isChofer()): ?>
                            <a href="registroVehiculos.php" class="btn btn-success btn-lg">Registrar Vehículo</a>
                        <?php elseif (isAdmin()): ?>
                            <a href="pendientes.php" class="btn btn-warning btn-lg">Panel de Administración</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="bg-primary text-white text-center py-3 mt-auto">
        <p class="mb-0">Aventones | UTN - ISW-811 | <?php echo date('Y-m-d H:i'); ?></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>