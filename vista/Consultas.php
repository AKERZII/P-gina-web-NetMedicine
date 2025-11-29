<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$userRole = $_SESSION['rol'] ?? 'paciente';
$userName = $_SESSION['nombre'] ?? 'Usuario';

// Permitir solo médicos y administradores
if ($userRole !== 'medico' && $userRole !== 'administrador') {
    header('Location: principal.php?error=sin_permiso');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Consultas | Red Médica</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="./css/Principal.css">
</head>
<body class="fade-in">
  <header class="top-header">
    <div class="logo"><img src="./img/Logo.jpg" alt="Logo Red Médica"></div>
    <div class="contacto"><p>Tel: +52 (33) 1234 5678 | contacto@redmedica.mx</p></div>
    <div class="login" id="loginArea">
      <a href="./login.php" class="btn-login">Iniciar Sesión</a>
    </div>
  </header>

  <nav class="navbar">
    <ul class="menu">
      <li><a href="./src/principal.php">Inicio</a></li>
      <li><a href="./Medicos.php">Hospitales & Médicos</a></li>
      <li><a href="./Agenda.php">Agenda</a></li>
      <li><a href="./Consultas.php">Consultas</a></li>
      <li class="dropdown">
        <a href="#">Servicios ▾</a>
        <ul class="submenu">
          <li><a href="./Hospitalizacion.php">Hospitalización</a></li>
          <li><a href="./Laboratorio.php">Laboratorio Clínico</a></li>
          <li><a href="./Rehabilitacion.php">Rehabilitación</a></li>
          <li><a href="./SaludMental.php">Salud Mental</a></li>
          <li><a href="./Farmacia.php">Farmacia</a></li>
          <li><a href="./Urgencias.php">Urgencias</a></li>
          <li><a href="./Planificacion.php">Planificación Familiar</a></li>
        </ul>
      </li>
      <li><a href="./Recetas.php">Recetas</a></li>
      <li><a href="./Reportes.php">Reportes</a></li>
    </ul>
  </nav>

  <div class="container">
    <h1 class="text-center mb-4">Listado de Citas Registradas</h1>
    <div class="table-responsive">
      <table class="table table-striped table-custom">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Médico</th>
            <th>Hora</th>
            <th>Correo</th>
            <th>Fecha</th>
            <th>Título</th>
            <th>Descripción</th>
            <th>Tipo</th>
          </tr>
        </thead>
        <tbody id="listaCitas">
          <!-- Se llenará dinámicamente -->
        </tbody>
      </table>
    </div>
  </div>

  <script src="./js/navbar.js"></script>
  <script>
    const citas = JSON.parse(localStorage.getItem("citas")) || [];
    const lista = document.getElementById("listaCitas");
    if (citas.length === 0) {
      lista.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">No hay citas registradas</td></tr>`;
    } else {
      lista.innerHTML = citas.map(cita => `
        <tr>
          <td>${cita.nombre}</td>
          <td>${cita.medico}</td>
          <td>${cita.hora}</td>
          <td>${cita.correo}</td>
          <td>${cita.fecha}</td>
          <td>${cita.titulo || ''}</td>
          <td>${cita.descripcion || ''}</td>
          <td>${cita.tipo || ''}</td>
        </tr>
      `).join('');
    }
  </script>
</body>
</html>