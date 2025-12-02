<?php
session_start();
require_once '../modelo/Conexion.php';

// Verificar permisos
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['rol'] !== 'administrador') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = trim($_POST['nombre'] ?? '');
        $ubicacion = trim($_POST['ubicacion'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $horario = trim($_POST['horario'] ?? '');
        
        // Validaciones
        if (empty($nombre) || empty($ubicacion) || empty($telefono)) {
            throw new Exception('Todos los campos obligatorios son requeridos');
        }
        
        // Insertar en la base de datos
        $stmt = $pdo->prepare("
            INSERT INTO hospitales (ubicacion, telefono)
            VALUES (?, ?,)
        ");
        
        $stmt->execute([$ubicacion, $telefono]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Hospital guardado correctamente',
            'id' => $pdo->lastInsertId()
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>