<?php
session_start();
date_default_timezone_set("America/Costa_Rica");
$fecha = new DateTime();?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>csgo.com</title>
</head>
<body>
    <h1><?php echo "Bienvenido a CSGO, hoy es -> ",  $fecha->format("d-m-Y H:i:s"); ?> </h1>
</body>
</html>

