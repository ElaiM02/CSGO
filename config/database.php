<?php
    $host     = 'localhost';
    $db       = 'csgo';
    $user     = 'csgouser';
    $password = 'secret';

    
    $conn = new mysqli($host, $user, $password, $db);

    if ($conn->connect_error) {
        die("Error en la conexión: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
?>