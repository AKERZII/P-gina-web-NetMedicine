<?php
require_once '../modelo/conexion.php';

// Traer hospitales
$stmt = $pdo->query("SELECT id_hospital, ubicacion FROM hospital");
$hospitales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traer usuarios
$stmt = $pdo->query("SELECT id_usuario, nombre FROM usuario");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Médico</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="p-4">

<div class="container">
    <h2 class="mb-4">Formulario para Agregar Médico</h2>

    <form action="../controlador/registrarmedico.php" method="POST">

        <div class="mb-3">
            <label class="form-label">Especialidad</label>
            <input type="text" name="especialidad" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Horario</label>
            <input type="text" name="horario" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Hospital</label>
            <select name="id_hospital" class="form-select" required>
                <option value="">Seleccione un hospital</option>
                <?php foreach ($hospitales as $h): ?>
                    <option value="<?= $h['id_hospital'] ?>"><?= $h['ubicacion'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Usuario asignado</label>
            <select name="id_usuario" class="form-select" required>
                <option value="">Seleccione usuario</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u['id_usuario'] ?>"><?= $u['nombre'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Médico</button>
    </form>
</div>

</body>
</html>
