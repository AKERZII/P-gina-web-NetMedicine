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
  <title>Farmacia | Red Médica</title>
  <link rel="stylesheet" href="./css/Principal.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { animation: fadeInPage 0.8s ease-in-out; }
    @keyframes fadeInPage { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .container { max-width: 900px; margin: 50px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 30px; }
  </style>
</head>
<body class="fade-in">
  <header class="top-header">
    <div class="logo"><img src="./img/Logo.jpg" alt="Logo Red Médica"></div>
    <div class="contacto"><p>Tel: +52 (33) 1234 5678 | contacto@redmedica.mx</p></div>
    <div class="login" id="loginArea"><a href="./login.php" class="btn-login">Iniciar Sesión</a></div>
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
    </ul>
  </nav>

  <div class="container">
    <h1 class="mb-3">Catálogo de Medicamentos</h1>
    <p class="mb-4">
      Este listado es únicamente de referencia para recetas médicas.  
      <strong>Nota:</strong> Red Médica no distribuye ni vende medicamentos, los pacientes deben adquirirlos en su farmacia de preferencia.
    </p>

    <div class="table-responsive">
      <table class="table table-striped table-custom">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Presentación</th>
            <th>Administración</th>
          </tr>
        </thead>
        <tbody id="listaMedicamentos"></tbody>
      </table>
    </div>
  </div>

  <script src="./js/navbar.js"></script>
  <script>
    const seedMedicamentos = [
      { id:"med-001", nombre:"Paracetamol", presentacion:"500 mg tabletas", admin:"Oral" },
      { id:"med-002", nombre:"Ibuprofeno", presentacion:"400 mg tabletas", admin:"Oral" },
      { id:"med-003", nombre:"Amoxicilina", presentacion:"500 mg cápsulas", admin:"Oral" },
      { id:"med-004", nombre:"Omeprazol", presentacion:"20 mg cápsulas", admin:"Oral" },
      { id:"med-005", nombre:"Loratadina", presentacion:"10 mg tabletas", admin:"Oral" },
      { id:"med-006", nombre:"Salbutamol", presentacion:"Inhalador 100 mcg", admin:"Inhalada" },
      { id:"med-007", nombre:"Metformina", presentacion:"850 mg tabletas", admin:"Oral" },
      { id:"med-008", nombre:"Losartán", presentacion:"50 mg tabletas", admin:"Oral" },
      { id:"med-009", nombre:"Diclofenaco", presentacion:"50 mg tabletas", admin:"Oral" },
      { id:"med-010", nombre:"Azitromicina", presentacion:"500 mg tabletas", admin:"Oral" },
      { id:"med-011", nombre:"Prednisona", presentacion:"5 mg tabletas", admin:"Oral" },
      { id:"med-012", nombre:"Ranitidina", presentacion:"150 mg tabletas", admin:"Oral" }
    ];

    (function initMedicamentos(){
      localStorage.setItem("medicamentos", JSON.stringify(seedMedicamentos));
    })();

    function renderMedicamentos(){
      const meds = JSON.parse(localStorage.getItem("medicamentos")) || [];
      const tbody = document.getElementById("listaMedicamentos");
      tbody.innerHTML = meds.map(m => `
        <tr>
          <td>${m.nombre}</td>
          <td>${m.presentacion}</td>
          <td>${m.admin}</td>
        </tr>
      `).join('');
    }

    renderMedicamentos();
  </script>
  <script src="./js/navbar.js"></script>
</body>
</html>