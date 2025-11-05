<?php
require_once '../config/start_app.php';
require_once '../config/functions.php';


if (isset($_SESSION['usuario'])) {
    header("Location: prueba.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITIO; ?> - Carpooling Seguro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>

<?php include 'navbar.php'; ?>

    <header class="hero d-flex flex-column justify-content-center align-items-center text-center text-white">
        <div class="overlay"></div>
        <div class="content">
            <h1 class="fw-bold display-4">Conduce seguro, Gana oportunidades</h1>
            <p class="lead mt-3 mb-4">Aventones te conecta con personas que van en tu misma dirección.</p>
            <a href="login.php" class="btn btn-primary btn-comenzar btn-lg fw-bold">Comenzar</a>
        </div>
    </header>

    <footer class="bg-primary text-light text-center py-3 mt-auto">
        <p class="mb-0">Aventones | UTN - ISW-811</p>
        <p class="mb-0"><?php echo "Hoy: " . $fecha->format("Y-m-d H:i"); ?></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>