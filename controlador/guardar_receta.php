<?php
session_start();
require_once '../modelo/Conexion.php';

// Verificar si el usuario está logueado y es médico
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['rol'] !== 'medico') {
    header('Location: ../vista/login.php?success=false&message=' . urlencode('No autorizado'));
    exit;
}

// Recibir datos del formulario
$nombrePaciente = trim($_POST['nombrePaciente'] ?? '');
$generoPaciente = trim($_POST['generoPaciente'] ?? '');
$pesoPaciente = trim($_POST['pesoPaciente'] ?? '');
$alturaPaciente = trim($_POST['alturaPaciente'] ?? '');
$edadPaciente = trim($_POST['edadPaciente'] ?? '');
$correoPaciente = trim($_POST['correoPaciente'] ?? '');
$cedulaProfesional = trim($_POST['cedulaProfesional'] ?? '');
$instruccionesAdicionales = trim($_POST['instruccionesAdicionales'] ?? '');
$fechaPrescripcion = trim($_POST['fechaPrescripcion'] ?? '');
$lugarExpedicion = trim($_POST['lugarExpedicion'] ?? '');

// Datos del médico desde la sesión
$id_usuario = $_SESSION['id_usuario'] ?? null;
$nombreMedico = $_SESSION['nombre'] ?? '';

// Validaciones básicas
if (empty($nombrePaciente)) {
    header('Location: ../vista/src/Recetas.php?success=false&message=' . urlencode('Datos del paciente incompletos'));
    exit;
}

if (!$id_usuario) {
    header('Location: ../vista/src/Recetas.php?success=false&message=' . urlencode('Error: Médico no identificado'));
    exit;
}

try {
    // Obtener ID del médico
    $id_medico = getIdMedico($pdo, $id_usuario);
    
    if (!$id_medico) {
        throw new Exception('Error: No se pudo identificar al médico');
    }

    // Buscar o crear paciente
    $id_paciente = obtenerOCrearPaciente($pdo, $nombrePaciente, $generoPaciente, $alturaPaciente, $pesoPaciente);

    // Procesar medicamentos
    $medicamentos = procesarMedicamentos($_POST);

    // Guardar receta en la base de datos
    guardarReceta($pdo, $id_medico, $id_paciente, $medicamentos, [
        'fecha_prescripcion' => $fechaPrescripcion,
        'cedula_profesional' => $cedulaProfesional,
        'instrucciones_adicionales' => $instruccionesAdicionales,
        'lugar_expedicion' => $lugarExpedicion
    ]);

    header('Location: ../vista/Recetas.php?success=true&message=' . urlencode('Receta guardada exitosamente'));
    exit;

} catch(PDOException $e) {
    error_log("Error al guardar receta: " . $e->getMessage());
    header('Location: ../vista/Recetas.php?success=false&message=' . urlencode('Error al guardar la receta'));
    exit;
} catch(Exception $e) {
    header('Location: ../vista/Recetas.php?success=false&message=' . urlencode($e->getMessage()));
    exit;
}

// FUNCIONES AUXILIARES

function obtenerOCrearPaciente($pdo, $nombre, $genero, $altura, $peso) {
    // Primero buscar si el paciente ya existe
    $sqlBuscar = "SELECT id_paciente FROM paciente WHERE nombre = ?";
    $stmtBuscar = $pdo->prepare($sqlBuscar);
    $stmtBuscar->execute([$nombre]);
    $paciente = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

    if ($paciente) {
        return $paciente['id_paciente'];
    }

    // Si no existe, crear nuevo paciente
    $sqlCrear = "INSERT INTO paciente (nombre, genero, altura, peso) VALUES (?, ?, ?, ?)";
    $stmtCrear = $pdo->prepare($sqlCrear);
    
    $stmtCrear->execute([
        $nombre, 
        $genero ?: 'No especificado', 
        $altura ?: NULL, 
        $peso ?: NULL
    ]);
    
    return $pdo->lastInsertId();
}

function procesarMedicamentos($postData) {
    $medicamentos = [];
    
    // Buscar todos los medicamentos en el POST
    foreach ($postData as $key => $value) {
        if (strpos($key, 'medicamento_nombre_') === 0) {
            $index = substr($key, strlen('medicamento_nombre_'));
            
            $medicamentos[] = [
                'nombre' => $value,
                'cantidad' => $postData['medicamento_cantidad_' . $index] ?? '',
                'frecuencia' => $postData['medicamento_frecuencia_' . $index] ?? '',
                'duracion' => $postData['medicamento_duracion_' . $index] ?? '',
                'instrucciones' => $postData['medicamento_instrucciones_' . $index] ?? ''
            ];
        }
    }
    
    return $medicamentos;
}

function getIdMedico($pdo, $id_usuario) {
    $sqlBuscar = "SELECT m.id_medico 
                  FROM medico m 
                  WHERE m.id_usuario = ?";
    $stmtBuscar = $pdo->prepare($sqlBuscar);
    $stmtBuscar->execute([$id_usuario]);
    $medico = $stmtBuscar->fetch(PDO::FETCH_ASSOC);
    
    if ($medico) {
        return $medico['id_medico'];
    }
    
    return null;
}

function guardarReceta($pdo, $id_medico, $id_paciente, $medicamentos, $datosAdicionales) {
    // Insertar cada medicamento como una receta individual
    foreach ($medicamentos as $medicamento) {
        $sql = "INSERT INTO receta (
                    fecha_prescripcion, 
                    id_medico, 
                    id_paciente, 
                    medicamento, 
                    cantidad, 
                    frequencia, 
                    duracion, 
                    instruccion
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        
        // Preparar instrucciones completas
        $instruccionesCompletas = $medicamento['instrucciones'];
        if (!empty($datosAdicionales['instrucciones_adicionales'])) {
            $instruccionesCompletas .= "\n\nInstrucciones adicionales: " . $datosAdicionales['instrucciones_adicionales'];
        }
        
        $stmt->execute([
            $datosAdicionales['fecha_prescripcion'] ?: date('Y-m-d'),
            $id_medico,
            $id_paciente,
            $medicamento['nombre'],
            $medicamento['cantidad'],
            $medicamento['frecuencia'],
            $medicamento['duracion'],
            $instruccionesCompletas

        ]);
    }
}
?>