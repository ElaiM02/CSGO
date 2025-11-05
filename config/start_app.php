<?php
    session_start();
    
    define("SITIO", "Aventones");
    date_default_timezone_set('America/Costa_Rica');
    $fecha = new DateTime();
    $hora = $fecha->format('H:i'); 

    require_once 'database.php';

    // Mostrar errores (solo en desarrollo)
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>