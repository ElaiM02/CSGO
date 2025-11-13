<?php
// public/navbar.php
require_once '../config/start_app.php';
require_once '../config/functions.php';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="../index.php">
            <img src="../imagenes/logo.png" alt="Aventones" width="40" class="me-2">
            <span class="fw-bold"><?php echo SITIO; ?></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto align-items-center">

                <?php if (!isset($_SESSION['usuario'])): ?>
                    <li class="nav-item"><a class="nav-link" href="../index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="../login.php">Iniciar sesión</a></li>
                    <li class="nav-item"><a class="nav-link" href="../registro.php">Registrarse</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="../dashboard.php">Dashboard</a></li>

                <?php if($_SESSION['rol'] == 'pasajero'): ?>
                     <a class="nav-link btn btn-success text-white px-3" href="registroVehiculos.php">
                    Convertirme en Chofer
                    </a>
                    <?php endif; ?>

                    <?php if ($_SESSION['rol'] === 'pasajero'): ?>
                    <li class="nav-item"><a class="nav-link" href="../buscar_rides.php">Buscar Viajes</a></li>
                    <?php endif; ?>

                    <?php if (isChofer()): ?>
                        <li class="nav-item"><a class="nav-link" href="../registroVehiculos.php">Mis Vehículos</a></li>
                        <li class="nav-item"><a class="nav-link" href="../rides_create.php">Publicar Viaje</a></li>
                    <?php endif; ?>

                    <?php if (isAdmin()): ?>
                        <li class="nav-item"><a class="nav-link text-warning fw-bold" href="../pendientes.php">Panel Admin</a></li>
                    <?php endif; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['usuario']); ?>
                            <span class="badge bg-<?php echo $_SESSION['rol']=='admin'?'danger':($_SESSION['rol']=='chofer'?'success':'secondary'); ?> ms-1">
                                <?php echo ucfirst($_SESSION['rol'] ?? 'pasajero'); ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../perfil.php">Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><form action="../logout.php" method="post" class="d-inline">
                                <button type="submit" class="dropdown-item text-danger">Cerrar sesión</button>
                            </form></li>
                        </ul>
                    </li>
                <?php endif; ?>

            </ul>
            <span class="text-white ms-3"><?php echo $hora; ?></span>
        </div>
    </div>
</nav>