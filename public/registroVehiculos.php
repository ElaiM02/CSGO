<?php  
require_once '../config/start_app.php';  
require_once '../config/functions.php';  

//checkAuth(); // Descomenta cuando lo integres  
if (!isset($_SESSION['user_id'])) {  
    header("Location: login.php");  
    exit;  
}  

?>  

<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Registrar Vehículo - <?php echo SITIO; ?></title> 
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <link rel="stylesheet" href="css/main.css">  
</head>  
<body>  
<?php include 'navbar.php'; ?>
    <div class="container">  
        <div class="form-box">  
            <h2>Registrar Vehículo (para ser Chofer)</h2>  

            <?php showError(); ?>  
            <?php showSuccess(); ?>  

            <form action="procesar_vehiculos.php" method="POST" enctype="multipart/form-data">  
                <input type="text" name="marca" placeholder="Marca " required>  
                <input type="text" name="modelo" placeholder="Modelo " required>  
                <input type="number" name="ano" placeholder="Año " min="1900" max="<?php echo date('Y'); ?>" required>  
                <input type="text" name="color" placeholder="Color " required>  
                <input type="text" name="placa" placeholder="Placa" pattern="[A-Z0-9]{6,10}" title="Placa válida" required>  
                <label for="foto">Foto del vehículo (opcional, JPG/PNG/GIF, máx 2MB)</label>  
                <input type="file" name="foto" accept="image/*">  
                <button type="submit">Registrar Vehículo</button>  
                <p><a href="dashboard.php">Volver al Inicio</a></p>  
            </form>  
        </div>  
    </div>  
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>  
</html>  