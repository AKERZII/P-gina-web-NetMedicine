<?php
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'Conexion.php';

    if (!$mysql) {
        echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos"]);
        exit;
    }

    $correo      = $_POST["correoInput"]      ?? '';
    $titulo      = $_POST["tituloInput"]      ?? '';
    $descripcion = $_POST["descripcionInput"] ?? '';
    $fecha       = $_POST["fecha"]            ?? '';
    $tipo        = $_POST["tipoSelect"]       ?? '';

    if (empty($correo) || empty($titulo) || empty($descripcion) || empty($fecha) || empty($tipo)) {
        echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
        exit;
    }

  
    $stmt = $mysql->prepare("INSERT INTO agenda (Correo, Titulo, Descripcion, Fecha, Tipo) 
                             VALUES (?, ?, ?, ?, ?)");

    $stmt->bind_param("sssss", $correo, $titulo, $descripcion, $fecha, $tipo);

    if ($stmt->execute()) {
         $mensaje = "¡Registro guardado con exito!";
      echo "<script>alert('$mensaje');</script>";
       header("Location:Agenda.html");
      
    } else {
        echo json_encode(["success" => false, "message" => "Error al insertar cita: " . $stmt->error]);
    }

    $stmt->close();
    $mysql->close();
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
}
?>
