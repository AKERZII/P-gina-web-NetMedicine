<?php
require_once '../modelo/conexion.php';

$especialidad = $_POST['especialidad'];
$horario = $_POST['horario'];
$id_usuario = $_POST['id_usuario'];
$id_hospital = $_POST['id_hospital'];

// Por default dejamos activo = 1
$activo = 1;

$sql = "INSERT INTO medico (especialidad, horario, id_usuario, id_hospital, activo)
        VALUES (:especialidad, :horario, :id_usuario, :id_hospital, :activo)";

$stmt = $pdo->prepare($sql);

$ok = $stmt->execute([
    ':especialidad' => $especialidad,
    ':horario' => $horario,
    ':id_usuario' => $id_usuario,
    ':id_hospital' => $id_hospital,
    ':activo' => $activo
]);

if ($ok) {
    echo "<script>alert('Médico registrado correctamente'); window.location='../vista/src/principal.php';</script>";
} else {
    echo "Error al registrar médico";
}
