<?php
session_start();
require_once '../modelo/Conexion.php';

$correo = trim($_POST['correo'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($correo) || empty($password)) {
    header('Location: ../vista/login.php?success=false&message=' . urlencode('Correo y contraseña son obligatorios'));
    exit;
}

try {
    // Buscar en tabla usuario
    $sqlUser = "SELECT id_usuario, correo, password, telefono, rol, nombre 
                FROM usuario 
                WHERE correo = ? OR telefono = ?";
    
    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->execute([$correo, $correo]);
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        header('Location: ../vista/login.php?success=false&message=' . urlencode('Usuario no encontrado'));
        exit;
    }


    // ✔️ Todo correcto: guardar sesión
    $_SESSION['id_usuario'] = $userData['id_usuario'];
    $_SESSION['correo'] = $userData['correo'];
    $_SESSION['rol'] = $userData['rol'];
    $_SESSION['nombre'] = $userData['nombre'];
    $_SESSION['logged_in'] = true;

    header('Location: ../vista/src/principal.php');
    exit;

} catch(PDOException $e) {
    header('Location: ../vista/login.php?success=false&message=' . urlencode('Error en el sistema'));
    exit;
}
?>
<?php
session_start();
require_once '../modelo/Conexion.php';

$correo = trim($_POST['correo'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($correo) || empty($password)) {
    header('Location: ../vista/login.php?success=false&message=' . urlencode('Correo y contraseña son obligatorios'));
    exit;
}

try {
    // Buscar en tabla usuario
    $sqlUser = "SELECT id_usuario, correo, password, telefono, rol, nombre 
                FROM usuario 
                WHERE correo = ? OR telefono = ?";
    
    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->execute([$correo, $correo]);
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        header('Location: ../vista/login.php?success=false&message=' . urlencode('Usuario no encontrado'));
        exit;
    }

    if (!password_verify($password, $userData['password'])) {
        header('Location: ../vista/login.php?success=false&message=' . urlencode('Contraseña incorrecta'));
        exit;
    }

    // ✔️ Todo correcto: guardar sesión
    $_SESSION['id_usuario'] = $userData['id_usuario'];
    $_SESSION['correo'] = $userData['correo'];
    $_SESSION['rol'] = $userData['rol'];
    $_SESSION['nombre'] = $userData['nombre'];
    $_SESSION['logged_in'] = true;

    header('Location: ../vista/src/Principal.php');
    exit;

} catch(PDOException $e) {
    header('Location: ../vista/login.php?success=false&message=' . urlencode('Error en el sistema'));
    exit;
}
?>
