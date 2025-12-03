<?php
require_once '../modelo/conexion.php';

$especialidad = $_POST['especialidad'];
$horario = $_POST['horario'];
$id_usuario = $_POST['id_usuario'];
$id_hospital = $_POST['id_hospital'];

$activo = 1;

try {

    // INSERTAR MEDICO
    $sql = "INSERT INTO medico (especialidad, horario, id_usuario, id_hospital, activo)
            VALUES (:especialidad, :horario, :id_usuario, :id_hospital, :activo)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':especialidad' => $especialidad,
        ':horario' => $horario,
        ':id_usuario' => $id_usuario,
        ':id_hospital' => $id_hospital,
        ':activo' => $activo
    ]);


    // ACTUALIZAR ROL DEL USUARIO
    $sql2 = "UPDATE usuario SET rol = 'medico' WHERE id_usuario = :id_usuario";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([':id_usuario' => $id_usuario]);

    if ($stmt2->rowCount() == 0) {
        echo "<script>alert('Advertencia: el usuario ya tenía ese rol o no se actualizó.');</script>";
    }

    echo "<script>alert('Médico registrado correctamente'); 
            window.location='../vista/src/principal.php';</script>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}