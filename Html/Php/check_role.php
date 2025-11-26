<?php
session_start();
header('Content-Type: application/json');

function checkRole($requiredRole) {
    $userRole = $_SESSION['rol'] ?? '';
    
    if (empty($userRole)) {
        // Intentar obtener del localStorage via JavaScript
        echo json_encode(["authorized" => false, "message" => "No autenticado"]);
        exit;
    }
    
    $authorized = false;
    
    switch($requiredRole) {
        case 'admin':
            $authorized = ($userRole === 'admin');
            break;
        case 'medico':
            $authorized = ($userRole === 'medico' || $userRole === 'admin');
            break;
        case 'paciente':
            $authorized = true; // Todos los roles pueden acceder
            break;
        default:
            $authorized = false;
    }
    
    return json_encode(["authorized" => $authorized, "role" => $userRole]);
}

// Uso ejemplo: 
// echo checkRole('admin');
?>