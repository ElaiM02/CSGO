<?php
// Verifica si el usuario está autenticado

/*function checkAuth() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['usuario'])) {
        header("Location: index.php");
        exit();
    }
}*/


// Muestra mensaje de error si existe

function showError() {
    if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
        echo '<div style="background:#f8d7da; color:#721c24; padding:12px; border-radius:6px; margin-bottom:20px; text-align:center; font-size:14px;">';
        echo htmlspecialchars($_SESSION['error']);
        echo '</div>';
        unset($_SESSION['error']);
    }
}

function showSuccess() {
    if (isset($_SESSION['success']) && !empty($_SESSION['success'])) {
        echo '<div style="background:#d4edda; color:#155724; padding:12px; border-radius:6px; margin-bottom:20px; text-align:center; font-size:14px;">';
        echo htmlspecialchars($_SESSION['success']);
        echo '</div>';
        unset($_SESSION['success']);
    }
}

function checkRole($required_role) {
    checkAuth();
    if ($_SESSION['rol'] !== $required_role) {
        $_SESSION['error'] = "Acceso denegado.";
        header("Location: index.php"); exit;
    }
}

function isChofer() {return isset($_SESSION['rol']) && $_SESSION['rol'] === 'chofer';}
function isAdmin() {return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';}
function isPasajero() {return isset($_SESSION['rol']) && $_SESSION['rol'] === 'pasajero';}

function getRol() {return isset($_SESSION['rol']) ? ucfirst($_SESSION['rol']) : 'Desconocido';}

// Redirige según estado de autenticación

function redirectByAuth($loginPage = 'login.php', $homePage = 'index.php') {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['usuario'])) {
        header("Location: $homePage");
        exit();
    }
}

 // Obtiene el nombre del usuario logueado
 
function getUserName() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['usuario'] ?? null;
}
?>