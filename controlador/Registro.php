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
    // Obtener conexión PDO (asumiendo que Conexion.php tiene una clase que la provee)
    // Esto depende de cómo esté implementado tu Conexion.php
    $conexion = new Conexion();
    $pdo = $conexion->getConexion(); // o el método que uses para obtener PDO

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

    // Hash de la contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar en tabla usuario
    $sqlUsuario = "INSERT INTO usuario (nombre, correo, password, telefono, rol) 
                   VALUES (?, ?, ?, ?, ?)";
    $stmtUsuario = $pdo->prepare($sqlUsuario);
    
    $nombreCompleto = $nombre . ' ' . $apellido;
    
    // CORREGIDO: Orden correcto de parámetros
    $stmtUsuario->execute([
        $nombreCompleto, 
        $correo, 
        $password_hash,  // Contraseña hasheada
        $telefono, 
        $rol
    ]);
    
    $id_usuario = $pdo->lastInsertId();

    // Confirmar transacción
    $pdo->commit();

    header('Location: ../vista/login.php?success=true&message=' . urlencode('Registro exitoso. Ahora puedes iniciar sesión.'));
    exit;

} catch(PDOException $e) {
    // Revertir transacción en caso de error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error en registro: " . $e->getMessage());

    header('Location: ../vista/login.php?success=false&message=' . urlencode('Error en el registro. Intenta nuevamente.'));
    exit;
}
?>