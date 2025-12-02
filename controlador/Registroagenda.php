<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    require_once '../modelo/Conexion.php';
    
    try {
        // Recibir y limpiar datos
        $nombre = trim($_POST["nombreInput"] ?? '');
        $correo = trim($_POST["correoInput"] ?? '');
        $fecha = trim($_POST["fechaInput"] ?? '');
        $titulo = trim($_POST["tituloInput"] ?? '');
        $descripcion = trim($_POST["descripcionInput"] ?? '');
        $tipo = trim($_POST["tipoInput"] ?? '');
        
        // Validaciones básicas
        $errores = [];
        
        if (empty($nombre)) {
            $errores[] = "El nombre del paciente es obligatorio";
        }
        
        if (empty($correo)) {
            $errores[] = "El correo electrónico es obligatorio";
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El correo electrónico no es válido";
        }
        
        if (empty($fecha)) {
            $errores[] = "La fecha es obligatoria";
        } else {
            // Validar formato de fecha
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$fechaObj) {
                $errores[] = "Formato de fecha inválido";
            } else {
                // Validar que no sea fecha pasada
                $hoy = new DateTime();
                $hoy->setTime(0, 0, 0);
                $fechaObj->setTime(0, 0, 0);
                
                if ($fechaObj < $hoy) {
                    $errores[] = "No se pueden agendar citas en fechas pasadas";
                }
            }
        }
        
        if (empty($titulo)) {
            $errores[] = "El título de la cita es obligatorio";
        }
        
        if (empty($tipo)) {
            $errores[] = "El tipo de cita es obligatorio";
        }
        
        // Si hay errores, lanzar excepción
        if (!empty($errores)) {
            throw new Exception(implode(". ", $errores));
        }
        
        // Buscar usuario por correo
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            throw new Exception("No existe un usuario registrado con el correo: $correo");
        }
        
        $id_usuario = $usuario['id_usuario'];
        
        // Insertar en la tabla agenda
        $stmt = $pdo->prepare("
            INSERT INTO agenda (
                titulo, 
                descripcion, 
                tipo, 
                fecha, 
                id_usuario
            ) VALUES (?, ?, ?, ?, ?)
        ");
        
        // Usar descripcion si está vacío, poner texto por defecto
        $descripcionFinal = empty($descripcion) ? "Cita médica programada" : $descripcion;
        
        $stmt->execute([
            $titulo,
            $descripcionFinal,
            $tipo,
            $fecha,
            $id_usuario
        ]);
        
        $id_agenda = $pdo->lastInsertId();
        
        // Guardar mensaje de éxito en sesión
        $_SESSION['ok_cita'] = "Cita agendada correctamente para el " . date('d/m/Y', strtotime($fecha)) . ".";
        
        // Redirigir de vuelta a la agenda
        header("Location: ../vista/Agenda.php");
        exit;
        
    } catch (Exception $e) {
        // Guardar error en sesión
        $_SESSION['error_cita'] = "Error: " . $e->getMessage();
        
        // Redirigir de vuelta con error
        header("Location: ../vista/Agenda.php?error=1");
        exit;
    }
    
} else {
    // Si alguien intenta acceder directamente
    header("Location: ../vista/Agenda.php");
    exit;
}
?>