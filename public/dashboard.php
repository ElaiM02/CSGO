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
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <img src="imagenes/logo.png" alt="Logo" class="logo me-2" width="40">
                <span class="fw-bold"><?php echo SITIO; ?></span>
            </a>
            <div class="collapse navbar-collapse justify-content-end">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="rides.php">Inicio</a></li>

                    <?php if (isChofer()): ?>
                        <li class="nav-item"><a class="nav-link" href="registroVehiculos.php">Mis Vehículos</a></li>
                        <li class="nav-item"><a class="nav-link" href="rides_create.php">Publicar Viaje</a></li>
                    <?php endif; ?>

                    <?php if (isAdmin()): ?>
                        <li class="nav-item"><a class="nav-link text-warning" href="pendientes.php">Panel Admin</a></li>
                    <?php endif; ?>

                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle text-light" href="#" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars(getUserName()); ?>
                            <span class="badge bg-light text-primary ms-2">
                                <?php echo getRol(); ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="perfil.php">Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="logout.php" method="post" class="d-inline">
                                    <button type="submit" class="dropdown-item text-danger">Cerrar sesión</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
                <span class="text-light ms-3 hora"><?php echo $hora; ?></span>
            </div>
        </div>
    </nav>

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
                            <a href="rides.php" class="btn btn-primary btn-lg">Buscar Viajes</a>
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