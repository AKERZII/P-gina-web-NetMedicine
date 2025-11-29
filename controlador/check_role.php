<?php
session_start();
header('Content-Type: application/json');

function checkRole($requiredRole) {
    // Verificar si la sesión está activa y tiene rol
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        return json_encode([
            "authorized" => false, 
            "message" => "No autenticado",
            "redirect" => "../vista/login.php"
        ]);
    }
    
    $userRole = $_SESSION['rol'] ?? 'paciente';
    
    if (empty($userRole)) {
        return json_encode([
            "authorized" => false, 
            "message" => "Rol no definido",
            "redirect" => "../vista/login.php"
        ]);
    }
    
    $authorized = false;
    
    switch($requiredRole) {
        case 'administrador':
            $authorized = ($userRole === 'administrador');
            break;
        case 'medico':
            $authorized = ($userRole === 'medico' || $userRole === 'administrador');
            break;
        case 'paciente':
            $authorized = true; // Todos los roles pueden acceder
            break;
        default:
            $authorized = false;
    }
    
    return [
        "authorized" => $authorized, 
        "role" => $userRole,
        "required" => $requiredRole
    ];
}

// Para uso directo via GET/POST
if (isset($_GET['required_role'])) {
    $result = checkRole($_GET['required_role']);
    echo json_encode($result);
    exit;
}

// Para uso en otros archivos PHP
return true;
?>