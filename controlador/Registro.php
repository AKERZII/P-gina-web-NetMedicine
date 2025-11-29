<?php
session_start();
require_once '../modelo/Conexion.php';

// Recibir datos del formulario
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');
$rol = trim($_POST['rol'] ?? '');

// Validaciones básicas
if (empty($nombre) || empty($apellido) || empty($correo) || empty($password) || empty($rol)) {
    header('Location: ../vista/login.php?success=false&message=' . urlencode('Todos los campos son obligatorios'));
    exit;
}

if ($password !== $confirm_password) {
    header('Location: ../vista/login.php?success=false&message=' . urlencode('Las contraseñas no coinciden'));
    exit;
}

if (strlen($password) < 6) {
    header('Location: ../vista/login.php?success=false&message=' . urlencode('La contraseña debe tener al menos 6 caracteres'));
    exit;
}

try {
    // Verificar si el correo o teléfono ya existen
    $sqlCheck = "SELECT id_usuario FROM usuario WHERE correo = ? OR telefono = ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$correo, $telefono]);
    
    if ($stmtCheck->fetch()) {
        header('Location: ../vista/login.php?success=false&message=' . urlencode('El correo o teléfono ya están registrados'));
        exit;
    }

    // Iniciar transacción
    $pdo->beginTransaction();

    // Insertar en tabla usuario
    $sqlUsuario = "INSERT INTO usuario (nombre, correo, telefono, password, rol) 
                   VALUES (?, ?, ?, ?, ?)";
    $stmtUsuario = $pdo->prepare($sqlUsuario);
    
    $nombreCompleto = $nombre . ' ' . $apellido;
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmtUsuario->execute([$nombreCompleto, $correo, $telefono, $passwordHash, $rol]);
    $id_usuario = $pdo->lastInsertId();

   
    // Insertar en tabla específica según el rol
    switch($rol) {
        case 'paciente':
            $sqlPaciente = "INSERT INTO paciente (id_usuario, nombre, telefono) VALUES (?, ?, ?)";
            $stmtPaciente = $pdo->prepare($sqlPaciente);
            $stmtPaciente->execute([$id_usuario, $nombreCompleto, $telefono]);
            break;
            
        case 'medico':
            $sqlMedico = "INSERT INTO medico (id_usuario, nombre, especialidad, cedula) VALUES (?, ?, ?, ?)";
            $stmtMedico = $pdo->prepare($sqlMedico);
            // Puedes solicitar estos datos en el formulario o establecer valores por defecto
            $stmtMedico->execute([$id_usuario, $nombreCompleto, 'General', '']);
            break;
            
        case 'administrador':
            $sqlAdmin = "INSERT INTO administrador (id_usuario) VALUES (?)";
            $stmtAdmin = $pdo->prepare($sqlAdmin);
            $stmtAdmin->execute([$id_usuario]);
            break;
    }

    // Confirmar transacción
    $pdo->commit();

    header('Location: ../vista/login.php?success=true&message=' . urlencode('Registro exitoso. Ahora puedes iniciar sesión.'));
    exit;

} catch(PDOException $e) {
    // Revertir transacción en caso de error
    $pdo->rollBack();
    error_log("Error en registro: " . $e->getMessage());
    header('Location: ../vista/login.php?success=false&message=' . urlencode('Error en el registro. Intenta nuevamente.'));
    exit;
}
?>