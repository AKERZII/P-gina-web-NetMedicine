<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'Conexion.php';

    if (!$mysql) {
        echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos"]);
        exit;
    }

    // Recibir datos del formulario de agenda
    $nombre = $_POST["nombreInput"] ?? '';
    $medico = $_POST["medicoSelect"] ?? '';
    $hora = $_POST["horaInput"] ?? '';
    $correo = $_POST["correoInput"] ?? '';
    $fecha = $_POST["fechaInput"] ?? '';
    $titulo = $_POST["tituloInput"] ?? '';
    $descripcion = $_POST["descripcionInput"] ?? '';
    $tipo = $_POST["tipoSelect"] ?? '';

    // Validaciones
    if (empty($nombre) || empty($medico) || empty($hora) || empty($correo) || empty($fecha)) {
        echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
        exit;
    }

    // Insertar en base de datos
    $stmt = $mysql->prepare("INSERT INTO agenda (nombre_paciente, medico, hora, correo, fecha, titulo, descripcion, tipo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $nombre, $medico, $hora, $correo, $fecha, $titulo, $descripcion, $tipo);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Cita agendada correctamente"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al guardar la cita: " . $stmt->error]);
    }

    $stmt->close();
    $mysql->close();
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
}
?>