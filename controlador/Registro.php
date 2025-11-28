<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../modelo/Conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   //Recibir datos del formulario
   $nombre = trim($_POST['nombre'] ?? '');
   $apellido = trim($_POST['apellido'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? ''; // CORREGIDO: Faltaba el operador ??

    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($telefono) || empty($password) || empty($rol)) {
        echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios"]);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(["success" => false, "message" => "La contraseña debe tener al menos 6 caracteres"]);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "El correo electrónico no es válido"]);
        exit;
    }

    // Validar roles permitidos
    $rolesPermitidos = ['administrador', 'paciente', 'medico'];
    if (!in_array($rol, $rolesPermitidos)) {
        echo json_encode(["success" => false, "message" => "Rol no válido. Use 'administrador', 'paciente' o 'medico'"]);
        exit;
    }

    try {
        // Verificar si el correo o teléfono ya existen
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE telefono = ? OR correo = ?");
        $stmt->execute([$telefono, $correo]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            echo json_encode(["success" => false, "message" => "El teléfono o correo electrónico ya están registrados"]);
            exit;
        }

        // Insertar nuevo usuario
        $stmt = $pdo->prepare("INSERT INTO usuario (correo, password, telefono, rol) VALUES (?, ?, ?, ?)");
        $stmt->execute([$correo, $password, $telefono, $rol]);

        $id_usuario = $pdo->lastInsertId(); // CORREGIDO: Usar lastInsertId() de PDO
        
        
        header('Location: ../vista/login.php');

    } catch(PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error en la base de datos: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
}
?>