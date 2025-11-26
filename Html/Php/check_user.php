<?php
header('Content-Type: application/json; charset=UTF-8');

$conexion = mysqli_connect("localhost", "root", "", "redmedica");

if (!$conexion) {
    echo json_encode(["error" => "Conexión fallida"]);
    exit;
}

// Cambia estos valores por un usuario que hayas registrado
$test_user = "tu_usuario_registrado";
$test_pass = "tu_password";

// Buscar en usuario
$sql = "SELECT * FROM usuario WHERE Usuario = ? AND Contraseña = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "ss", $test_user, $test_pass);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    echo json_encode([
        "encontrado" => true,
        "usuario" => $user['Usuario'],
        "nombre" => $user['Nombre'],
        "rol" => $user['Rol'] ?? 'paciente'
    ]);
} else {
    echo json_encode(["encontrado" => false, "mensaje" => "No encontrado en tabla usuario"]);
}

mysqli_close($conexion);
?>