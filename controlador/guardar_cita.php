<?php
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../modelo/Conexion.php';

    if (!$mysql) {
        echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos"]);
        exit;
    }

    // Recibir datos del formulario
    $nombre = $_POST["nombre"] ?? '';
    $medico = $_POST["medico"] ?? '';
    $medicoId = $_POST["medicoId"] ?? '';
    $especialidad = $_POST["especialidad"] ?? '';
    $hospital = $_POST["hospital"] ?? '';
    $hora = $_POST["hora"] ?? '';
    $correo = $_POST["correo"] ?? '';
    $titulo = $_POST["titulo"] ?? '';
    $descripcion = $_POST["descripcion"] ?? '';
    $tipo = $_POST["tipo"] ?? '';
    $fecha = $_POST["fecha"] ?? '';

    // Validaciones
    if (empty($nombre) || empty($medico) || empty($hora) || empty($correo) || empty($fecha)) {
        echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
        exit;
    }

    // Insertar en base de datos
    $stmt = $mysql->prepare("INSERT INTO agenda (nombre_paciente, medico, medico_id, especialidad, hospital, hora, correo, titulo, descripcion, tipo, fecha) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Error al preparar la consulta: " . $mysql->error]);
        exit;
    }

    $stmt->bind_param("sssssssssss", $nombre, $medico, $medicoId, $especialidad, $hospital, $hora, $correo, $titulo, $descripcion, $tipo, $fecha);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Cita guardada correctamente en la base de datos", "id" => $stmt->insert_id]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al guardar la cita: " . $stmt->error]);
    }

    $stmt->close();
    $mysql->close();
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
}
?>