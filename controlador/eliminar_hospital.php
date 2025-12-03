<?php
session_start();
require_once '../modelo/Conexion.php';

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

// Verificar si el usuario es administrador
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['rol'] !== 'administrador') {
    $_SESSION['mensaje'] = "No tiene permisos para realizar esta acción.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: ../vista/medicosPublic.php");
    exit();
}

// Verificar que se recibió el ID del hospital
if (!isset($_POST['id_hospital']) || empty($_POST['id_hospital'])) {
    $_SESSION['mensaje'] = "No se especificó el hospital a eliminar.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: ../vista/medicosPublic.php");
    exit();
}

$id_hospital = $_POST['id_hospital'];

try {
    
    // Primero, eliminar los médicos asociados al hospital
    $stmt = $pdo->prepare("DELETE FROM medico WHERE id_hospital = ?");
    $stmt->execute([$id_hospital]);
    
    // Luego, eliminar el hospital
    $stmt = $pdo->prepare("DELETE FROM hospital WHERE id_hospital = ?");
    $stmt->execute([$id_hospital]);
    
    // Confirmar transacción
    $pdo->commit();
    
    $_SESSION['mensaje'] = "Hospital eliminado correctamente junto con sus médicos asociados.";
    $_SESSION['tipo_mensaje'] = "success";
    
} catch (PDOException $e) {
    // Revertir transacción en caso de error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $_SESSION['mensaje'] = "Error al eliminar el hospital: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

// Redirigir de vuelta
header("Location: ../vistas/Medicos.php");
exit();
?>