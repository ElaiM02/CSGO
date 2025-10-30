<?php
    $host     = 'Localhost';
    $db       = 'csgo';
    $user     = 'csgouser';
    $password = 'secret';
    
    $conn = new mysqli($host, $user, $password, $db);

    if ($conn->connect_error) {
        die("Error en la conexión: " . $conn->connect_error);
    }
?>