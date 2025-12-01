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
            // Solo administradores
            $authorized = ($userRole === 'administrador');
            break;
        case 'medico':
            // Solo médicos y administradores
            $authorized = ($userRole === 'medico' || $userRole === 'administrador');
            break;
        case 'medico_only':
            // Solo médicos (no administradores)
            $authorized = ($userRole === 'medico');
            break;
        case 'admin_only':
            // Solo administradores (no médicos)
            $authorized = ($userRole === 'administrador');
            break;
        case 'paciente':
            // NO PERMITIR pacientes - redirigir
            $authorized = false;
            break;
        default:
            $authorized = false;
    }
    
    if (!$authorized) {
        return json_encode([
            "authorized" => false,
            "message" => "Acceso denegado. Solo personal médico y administradores pueden acceder a esta función.",
            "redirect" => "../vista/principal.php", // Redirigir al inicio si no tiene permiso
            "user_role" => $userRole,
            "required_role" => $requiredRole
        ]);
    }
    
    return [
        "authorized" => $authorized, 
        "role" => $userRole,
        "required" => $requiredRole
    ];
}

// Versión que hace redirección automática (para usar en otros scripts)
function requireRole($requiredRole) {
    $result = checkRole($requiredRole);
    
    // Si el resultado es un JSON string, decodificarlo
    if (is_string($result)) {
        $result = json_decode($result, true);
    }
    
    if (!$result['authorized']) {
        // Si el usuario es paciente, mostrar mensaje específico
        if ($_SESSION['rol'] === 'paciente') {
            $_SESSION['error_message'] = "Los pacientes no pueden acceder a esta función.";
        }
        header('Location: ' . $result['redirect']);
        exit;
    }
    
    return $result;
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