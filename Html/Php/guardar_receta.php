<?php
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'Conexion.php';

    if (!$mysql) {
        echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos"]);
        exit;
    }

    // Recibir datos del formulario
    $medico = $_POST['medico'] ?? '';
    $cedula = $_POST['cedula'] ?? '';
    $paciente_nombre = $_POST['paciente_nombre'] ?? '';
    $edad = $_POST['edad'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $instrucciones = $_POST['instrucciones'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $lugar = $_POST['lugar'] ?? '';
    $medicamentos_json = $_POST['medicamentos'] ?? '[]';

    // Validaciones
    if (empty($medico) || empty($paciente_nombre) || empty($edad) || empty($correo) || empty($fecha)) {
        echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
        exit;
    }

    // Convertir JSON de medicamentos a array
    $medicamentos = json_decode($medicamentos_json, true);
    
    if (!is_array($medicamentos) || empty($medicamentos)) {
        echo json_encode(["success" => false, "message" => "Debe agregar al menos un medicamento"]);
        exit;
    }

    // Insertar cada medicamento en la base de datos
    $stmt = $mysql->prepare("INSERT INTO recetas (correo, medicamento, cantidad, admi, periodo, instrucciones, paciente_nombre, medico_nombre, cedula_profesional, fecha_prescripcion, lugar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Error al preparar la consulta: " . $mysql->error]);
        exit;
    }

    $successCount = 0;
    $errorCount = 0;

    foreach ($medicamentos as $medicamento) {
        $nombre_med = $medicamento['nombre'] ?? '';
        $cantidad = $medicamento['cantidad'] ?? '';
        $frecuencia = $medicamento['frecuencia'] ?? '';
        $duracion = $medicamento['duracion'] ?? '';
        $instrucciones_med = $medicamento['instrucciones'] ?? '';

        $stmt->bind_param("sssssssssss", 
            $correo, 
            $nombre_med, 
            $cantidad, 
            $frecuencia, 
            $duracion, 
            $instrucciones_med,
            $paciente_nombre,
            $medico,
            $cedula,
            $fecha,
            $lugar
        );

        if ($stmt->execute()) {
            $successCount++;
        } else {
            $errorCount++;
            error_log("Error al guardar medicamento: " . $stmt->error);
        }
    }

    $stmt->close();
    $mysql->close();

    if ($successCount > 0) {
        $message = "Receta guardada correctamente. " . $successCount . " medicamento(s) registrado(s).";
        if ($errorCount > 0) {
            $message .= " " . $errorCount . " error(es).";
        }
        echo json_encode(["success" => true, "message" => $message]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al guardar todos los medicamentos"]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
}
?>