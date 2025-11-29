<?php
require_once '../modelo/Conexion.php';
require_once './modelo/login.php';
try{

    $sqlHospital=""
    $sqlReceta="SELECT m.id_medico, m.nombre, m.especialidad, m.horario FROM medico m INNER JOIN hospital h ON m.id_hospital = h.id_hospital
WHERE h.id_hospital = X;   
 ";
}   
catch(PDOException $e) {
    header('Location: ../vista/src/principal.php?success=false&message=' . urlencode('Error en el sistema'));
    exit;
}

?>