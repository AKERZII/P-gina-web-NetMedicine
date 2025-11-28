<?php
session_start();

require_once '../modelo/Conexion.php';

$correo = trim($_POST['correo'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($correo) || empty($password)) {
    header('Location: ../vista/login.php?success=false&message=' . urlencode('Correo/teléfono y contraseña son obligatorios'));
    exit;
}

try {
    // BUSCAR EN TABLA USUARIO
    $sqlUser = "SELECT id_usuario, correo, password, telefono, rol FROM usuario WHERE (correo = ? OR telefono = ?)";
    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->execute([$correo, $correo]);
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

        $_SESSION['id_usuario'] = $userData['id_usuario'];
        $_SESSION['correo'] = $userData['correo'];
        $_SESSION['rol'] = $userData['rol'] ?? 'paciente';
        
        // Redirigir según el rol
        switch($userData['rol']) {
            case 'administrador':
                header('Location: ../vista/src/principal.php');
                break;
            case 'medico':
                header('Location: ../vista/src/principal.php');
                break;
            case 'paciente':
                header('Location: ../vista/src/principal.php');
                break;
            default:
                header('Location: ../vista/src/principal.php');
                break;
        }
        exit;

} catch(PDOException $e) {
    header('Location: ../vista/login.php?success=false&message=' . urlencode('Error en el sistema'));
    exit;
}
?>