<?php
    require_once '../config/start_app.php';
    session_destroy();
    header("Location: login.php");
    exit();
?>