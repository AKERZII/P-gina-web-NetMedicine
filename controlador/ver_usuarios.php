<?php
// Conexión a la base de datos
require_once '../modelo/Conexion.php'

// Consulta para obtener usuarios
$sql = "SELECT * FROM usuario";
$resultado = $conexion->query($sql);

$usuarios = array();
if ($resultado->num_rows > 0) {
    while($fila = $resultado->fetch_assoc()) {
        // Obtener información adicional según el rol
        $detalles = "";
        
        if ($fila["rol"] == "medico") {
            // Consultar información de médico
            $sql_medico = "SELECT especialidad, id_hospital FROM medico WHERE id_usuario = " . $fila["id_usuario"];
            $result_medico = $conexion->query($sql_medico);
            if ($result_medico->num_rows > 0) {
                $medico = $result_medico->fetch_assoc();
                $detalles = "Especialidad: " . $medico["especialidad"];
            }
        } elseif ($fila["rol"] == "paciente") {
            // Consultar información de paciente
            $sql_paciente = "SELECT genero, altura, peso FROM paciente WHERE id_usuario = " . $fila["id_usuario"];
            $result_paciente = $conexion->query($sql_paciente);
            if ($result_paciente->num_rows > 0) {
                $paciente = $result_paciente->fetch_assoc();
                $detalles = "Género: " . $paciente["genero"] . ", Altura: " . $paciente["altura"] . "m, Peso: " . $paciente["peso"] . "kg";
            }
        } elseif ($fila["rol"] == "administrador") {
            $detalles = "Usuario con acceso completo al sistema";
        }
        
        $fila["detalles"] = $detalles;
        $usuarios[] = $fila;
    }
}

$conexion->close();
?>