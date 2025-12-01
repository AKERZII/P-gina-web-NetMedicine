<?php
session_start();
require_once '../modelo/Conexion.php';

// Verificar si el usuario está logueado y es médico
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || ($_SESSION['rol'] !== 'medico' && $_SESSION['rol'] !== 'admin')) {
    header('Location: ../src/vista/login.php');
    exit;
}

try {
    // Primero, asegurarnos de que tenemos la conexión a la base de datos
    // Si Conexion.php ya crea $pdo, úsala; si no, crea la conexión aquí
    if (!isset($pdo)) {
        // Configuración de conexión si no viene de Conexion.php
        $host = 'localhost';
        $dbname = 'redmedica';
        $username = 'root';
        $password = '';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();

    // 1. Obtener el ID del médico basado en el usuario logueado
    $correo_usuario_medico = $_SESSION['correo'];
    
    // ERROR CORREGIDO: La consulta estaba incompleta
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
    $stmt->execute([$correo_usuario_medico]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        throw new Exception("Usuario no encontrado");
    }
    
    $id_usuario_medico = $usuario['id_usuario'];

    // ERROR CORREGIDO: Variable incorrecta - usar $id_usuario_medico
    $stmt = $pdo->prepare("SELECT id_medico FROM medico WHERE id_usuario = ?");
    $stmt->execute([$id_usuario_medico]);
    $medico = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$medico) {
        // Si el médico no existe, redirigir
        header('Location: ../vista/Recetas.php?error=medico_no_encontrado');
        exit;
    } else {
        $id_medico = $medico['id_medico'];
    }

    // 2. Buscar o crear el paciente
    $nombre_paciente = $_POST['nombrePaciente'] ?? '';
    $correo_paciente = $_POST['correoPaciente'] ?? '';
    $genero_paciente = $_POST['generoPaciente'] ?? 'No especificado';
    $altura_paciente = $_POST['alturaPaciente'] ?? 0;
    $peso_paciente = $_POST['pesoPaciente'] ?? 0;
    $edad_paciente = $_POST['edadPaciente'] ?? 0;

    // Validar datos requeridos
    if (empty($nombre_paciente) || empty($correo_paciente)) {
        throw new Exception("Nombre y correo del paciente son obligatorios");
    }

    // Buscar si el paciente ya existe por correo
    $stmt_paciente = $pdo->prepare("SELECT id_paciente, id_usuario FROM paciente WHERE id_usuario IN (SELECT id_usuario FROM usuario WHERE correo = ?)");
    $stmt_paciente->execute([$correo_paciente]);
    $paciente_existente = $stmt_paciente->fetch(PDO::FETCH_ASSOC);

    if ($paciente_existente) {
        $id_paciente = $paciente_existente['id_paciente'];
        
        // Actualizar datos del paciente existente
        $stmt_actualizar_paciente = $pdo->prepare("UPDATE paciente SET genero = ?, altura = ?, peso = ? WHERE id_paciente = ?");
        $stmt_actualizar_paciente->execute([$genero_paciente, $altura_paciente, $peso_paciente, $id_paciente]);
    } else {
        // Crear usuario para el paciente si no existe
        $stmt_usuario = $pdo->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
        $stmt_usuario->execute([$correo_paciente]);
        $usuario_existente = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

        if ($usuario_existente) {
            $id_usuario_paciente = $usuario_existente['id_usuario'];
        } else {
            // Crear nuevo usuario para el paciente
            $password_temp = password_hash('temp123', PASSWORD_DEFAULT);
            $stmt_nuevo_usuario = $pdo->prepare("INSERT INTO usuario (nombre, correo, password, telefono, rol) VALUES (?, ?, ?, ?, ?)");
            $stmt_nuevo_usuario->execute([$nombre_paciente, $correo_paciente, $password_temp, '', 'paciente']);
            $id_usuario_paciente = $pdo->lastInsertId();
        }

        // Crear paciente - ERROR CORREGIDO: Falta el campo 'nombre' en el INSERT
        $stmt_nuevo_paciente = $pdo->prepare("INSERT INTO paciente (nombre, genero, altura, peso, id_usuario) VALUES (?, ?, ?, ?, ?)");
        $stmt_nuevo_paciente->execute([$nombre_paciente, $genero_paciente, $altura_paciente, $peso_paciente, $id_usuario_paciente]);
        $id_paciente = $pdo->lastInsertId();
    }

    // 3. Procesar medicamentos - BUSCAR EN LUGAR DE INSERTAR
    $ids_medicamentos = [];
    
    // Debug: Ver qué datos llegan del formulario
    error_log("Datos POST recibidos: " . print_r($_POST, true));
    
    // Recorrer todos los campos POST para encontrar medicamentos
    foreach ($_POST as $key => $value) {
        // Buscar campos que empiecen con "medicamento_"
        if (preg_match('/^medicamento_(\d+)_nombre$/', $key, $matches)) {
            $index = $matches[1];
            
            $nombre = trim($_POST["medicamento_{$index}_nombre"] ?? '');
            $cantidad = trim($_POST["medicamento_{$index}_cantidad"] ?? '');
            $frecuencia = trim($_POST["medicamento_{$index}_frecuencia"] ?? '');
            $duracion = trim($_POST["medicamento_{$index}_duracion"] ?? '');
            $instrucciones = trim($_POST["medicamento_{$index}_instrucciones"] ?? '');

            if (!empty($nombre) && !empty($cantidad)) {
                error_log("Procesando medicamento: $nombre, cantidad: $cantidad");
                
                // BUSCAR si el medicamento ya existe en la base de datos
                $stmt_buscar_medicamento = $pdo->prepare("SELECT id_medicamento FROM medicamento WHERE nombre = ?");
                $stmt_buscar_medicamento->execute([$nombre]);
                $medicamento_existente = $stmt_buscar_medicamento->fetch(PDO::FETCH_ASSOC);
                
                if ($medicamento_existente) {
                    // Si existe, usar el ID existente
                    $ids_medicamentos[] = $medicamento_existente['id_medicamento'];
                    error_log("Medicamento encontrado, ID: " . $medicamento_existente['id_medicamento']);
                    
                    // Opcional: Actualizar la información si es diferente
                    $stmt_actualizar_med = $pdo->prepare("UPDATE medicamento SET cantidad = ?, frecuencia = ?, duracion = ?, instruccion = ? WHERE id_medicamento = ?");
                    $stmt_actualizar_med->execute([$cantidad, $frecuencia, $duracion, $instrucciones, $medicamento_existente['id_medicamento']]);
                } else {
                    // Si no existe, insertar nuevo medicamento
                    $stmt_medicamento = $pdo->prepare("INSERT INTO medicamento (nombre, cantidad, frecuencia, duracion, instruccion) VALUES (?, ?, ?, ?, ?)");
                    $stmt_medicamento->execute([$nombre, $cantidad, $frecuencia, $duracion, $instrucciones]);
                    $nuevo_id = $pdo->lastInsertId();
                    $ids_medicamentos[] = $nuevo_id;
                    error_log("Nuevo medicamento insertado, ID: $nuevo_id");
                }
            }
        }
    }

    // Si no se encontraron medicamentos con el formato anterior, intentar con array
    if (empty($ids_medicamentos)) {
        error_log("No se encontraron medicamentos en formato medicamento_X_nombre, intentando formato array");
        
        // Método alternativo para arrays
        if (isset($_POST['medicamento_nombre']) && is_array($_POST['medicamento_nombre'])) {
            foreach ($_POST['medicamento_nombre'] as $index => $nombre) {
                $nombre = trim($nombre);
                if (!empty($nombre)) {
                    $cantidad = trim($_POST['medicamento_cantidad'][$index] ?? '');
                    $frecuencia = trim($_POST['medicamento_frecuencia'][$index] ?? '');
                    $duracion = trim($_POST['medicamento_duracion'][$index] ?? '');
                    $instrucciones = trim($_POST['medicamento_instrucciones'][$index] ?? '');

                    // BUSCAR medicamento existente
                    $stmt_buscar_medicamento = $pdo->prepare("SELECT id_medicamento FROM medicamento WHERE nombre = ?");
                    $stmt_buscar_medicamento->execute([$nombre]);
                    $medicamento_existente = $stmt_buscar_medicamento->fetch(PDO::FETCH_ASSOC);
                    
                    if ($medicamento_existente) {
                        $ids_medicamentos[] = $medicamento_existente['id_medicamento'];
                    } else {
                        $stmt_medicamento = $pdo->prepare("INSERT INTO medicamento (nombre, cantidad, frecuencia, duracion, instruccion) VALUES (?, ?, ?, ?, ?)");
                        $stmt_medicamento->execute([$nombre, $cantidad, $frecuencia, $duracion, $instrucciones]);
                        $ids_medicamentos[] = $pdo->lastInsertId();
                    }
                }
            }
        }
    }

    // Verificar que se hayan agregado medicamentos
    if (empty($ids_medicamentos)) {
        throw new Exception("No se encontraron medicamentos para guardar. Asegúrate de completar al menos un medicamento.");
    }

    error_log("Total medicamentos procesados: " . count($ids_medicamentos));

    // 4. Crear las recetas
    $fecha_prescripcion = $_POST['fechaPrescripcion'] ?? date('Y-m-d');
    $instrucciones_adicionales = $_POST['instruccionesAdicionales'] ?? '';
    $lugar_expedicion = $_POST['lugarExpedicion'] ?? 'Guadalajara, Jalisco';

    // Insertar una receta por cada medicamento
    foreach ($ids_medicamentos as $id_medicamento) {
        $stmt_receta = $pdo->prepare("INSERT INTO receta (fecha_prescripcion, id_medicamento, id_medico, id_paciente) VALUES (NOW(), ?, ?, ?)");
        $stmt_receta->execute([$id_medicamento, $id_medico, $id_paciente]);
        error_log("Receta insertada para medicamento ID: $id_medicamento");
    }

    // Confirmar transacción
    $pdo->commit();

    // Éxito - redirigir con mensaje
    $_SESSION['mensaje_exito'] = "Receta guardada correctamente. Se registraron " . count($ids_medicamentos) . " medicamentos.";
    header('Location: ../vista/Recetas.php?exito=1');
    exit;

} catch (Exception $e) {
    // Revertir transacción en caso de error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log del error
    error_log("Error al guardar receta: " . $e->getMessage() . " en línea " . $e->getLine());
    
    // Error - redirigir con mensaje de error
    $_SESSION['error_receta'] = "Error al guardar la receta: " . $e->getMessage();
    header('Location: ../vista/Recetas.php?error=1');
    exit;
}
?>