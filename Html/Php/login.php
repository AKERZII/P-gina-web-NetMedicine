<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

// Debug
error_log("=== INICIO LOGIN ===");

$conexion = mysqli_connect("localhost", "root", "", "redmedica");

if (!$conexion) {
    error_log("❌ Error conexión BD");
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos"]);
    exit;
}

$usuario = trim($_POST['usuario'] ?? '');
$password = trim($_POST['password'] ?? '');

error_log("Usuario recibido: '$usuario'");
error_log("Password recibido: '$password'");

if (empty($usuario) || empty($password)) {
    error_log("❌ Campos vacíos");
    echo json_encode(["success" => false, "message" => "Usuario y contraseña son obligatorios"]);
    exit;
}

// BUSCAR EN USUARIOS NORMALES
$sqlUser = "SELECT * FROM usuario WHERE (Usuario = ? OR Correo = ?) AND Contraseña = ?";
$stmtUser = mysqli_prepare($conexion, $sqlUser);

if (!$stmtUser) {
    error_log("❌ Error preparar consulta usuario: " . mysqli_error($conexion));
    echo json_encode(["success" => false, "message" => "Error en el sistema"]);
    exit;
}

mysqli_stmt_bind_param($stmtUser, "sss", $usuario, $usuario, $password);
mysqli_stmt_execute($stmtUser);
$resultadoUser = mysqli_stmt_get_result($stmtUser);
$numUsers = mysqli_num_rows($resultadoUser);

error_log("Usuarios encontrados: $numUsers");

if ($numUsers > 0) {
    $userData = mysqli_fetch_assoc($resultadoUser);
    error_log("✅ Usuario encontrado: " . $userData['Nombre'] . " - Rol: " . ($userData['Rol'] ?? 'N/A'));
    
    $_SESSION['usuario'] = $userData['Nombre'];
    $_SESSION['rol'] = $userData['Rol'] ?? 'paciente';
    
    echo json_encode([
        "success" => true, 
        "message" => "Login exitoso",
        "usuario" => $userData['Nombre'],
        "rol" => $userData['Rol'] ?? 'paciente'
    ]);
    
    mysqli_stmt_close($stmtUser);
    mysqli_close($conexion);
    exit;
}

mysqli_stmt_close($stmtUser);

// BUSCAR EN ADMINISTRADORES
$sqlAdmin = "SELECT * FROM administradores WHERE Usuario = ? AND Contraseña = ?";
$stmtAdmin = mysqli_prepare($conexion, $sqlAdmin);

if (!$stmtAdmin) {
    error_log("❌ Error preparar consulta admin");
    echo json_encode(["success" => false, "message" => "Error en el sistema"]);
    exit;
}

mysqli_stmt_bind_param($stmtAdmin, "ss", $usuario, $password);
mysqli_stmt_execute($stmtAdmin);
$resultadoAdmin = mysqli_stmt_get_result($stmtAdmin);
$numAdmins = mysqli_num_rows($resultadoAdmin);

error_log("Administradores encontrados: $numAdmins");

if ($numAdmins > 0) {
    error_log("✅ Administrador encontrado: $usuario");
    
    $_SESSION['usuario'] = $usuario;
    $_SESSION['rol'] = 'admin';
    
    echo json_encode([
        "success" => true, 
        "message" => "Login exitoso como administrador",
        "usuario" => $usuario,
        "rol" => "admin"
    ]);
} else {
    error_log("❌ No se encontró en ninguna tabla");
    echo json_encode([
        "success" => false, 
        "message" => "Usuario o contraseña incorrectos"
    ]);
}

mysqli_stmt_close($stmtAdmin);
mysqli_close($conexion);

error_log("=== FIN LOGIN ===");
?>