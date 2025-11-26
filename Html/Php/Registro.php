<?php
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conexion = mysqli_connect("localhost", "root", "", "redmedica");
    
    if (!$conexion) {
        echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos: " . mysqli_connect_error()]);
        exit;
    }

    // Recibir datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'paciente';

    // Debug: Ver los datos recibidos (puedes comentar esto después)
    error_log("Datos recibidos - Nombre: $nombre, Apellido: $apellido, Correo: $correo, Tel: $telefono, User: $usuario, Rol: $rol");

    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($telefono) || empty($usuario) || empty($password)) {
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
    $rolesPermitidos = ['paciente', 'medico'];
    if (!in_array($rol, $rolesPermitidos)) {
        echo json_encode(["success" => false, "message" => "Rol no válido. Use 'paciente' o 'medico'"]);
        exit;
    }

    // Verificar si el usuario o correo ya existen
    $verificar = $conexion->prepare("SELECT idUsuario FROM usuario WHERE Usuario = ? OR Correo = ?");
    if (!$verificar) {
        echo json_encode(["success" => false, "message" => "Error en la consulta de verificación: " . $conexion->error]);
        exit;
    }
    
    $verificar->bind_param("ss", $usuario, $correo);
    $verificar->execute();
    $verificar->store_result();

    if ($verificar->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "El usuario o correo electrónico ya están registrados"]);
        $verificar->close();
        exit;
    }
    $verificar->close();

    // Insertar nuevo usuario - USANDO LOS NOMBRES EXACTOS DE LAS COLUMNAS DE TU BD
    $stmt = $conexion->prepare("INSERT INTO usuario (Nombre, Apellido, Telefono, Correo, Usuario, Contraseña, Rol) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Error al preparar la consulta: " . $conexion->error]);
        exit;
    }
    
    $stmt->bind_param("sssssss", $nombre, $apellido, $telefono, $correo, $usuario, $password, $rol);

    if ($stmt->execute()) {
        $idUsuario = $stmt->insert_id;
        echo json_encode([
            "success" => true, 
            "message" => "Usuario registrado exitosamente como " . ($rol === 'medico' ? 'médico' : 'paciente'),
            "id" => $idUsuario
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al registrar usuario: " . $stmt->error]);
    }

    $stmt->close();
    $conexion->close();
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
}
?>