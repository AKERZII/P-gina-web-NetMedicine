<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$userRole = $_SESSION['rol'] ?? 'paciente';
$userName = $_SESSION['nombre'] ?? 'Usuario';

// Permitir solo médicos
if ($userRole !== 'medico') {
    header('Location: principal.php?error=sin_permiso');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recetas Médicas | Red Médica</title>
  <link rel="stylesheet" href="./css/Principal.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { animation: fadeInPage 0.8s ease-in-out; }
    @keyframes fadeInPage { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .container { max-width: 800px; margin: 50px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 30px; }
    label { margin-top: 10px; font-weight: 600; }
    button { margin-top: 20px; }
    .btn-generar { background-color: #28a745; color: white; }
    .btn-generar:hover { background-color: #218838; }
    .receta-preview { border: 2px dashed #dee2e6; padding: 20px; border-radius: 8px; margin-top: 20px; display: none; }
    .error-fecha { color: #dc3545; font-size: 0.875em; margin-top: 0.25rem; display: none; }
  </style>
  <!-- Incluir jsPDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
</head>
<body>
  <!-- ENCABEZADO -->
  <header class="top-header">
    <div class="logo"><img src="./img/Logo.jpg" alt="Logo"></div>
    <div class="contacto"><p>Tel: +52 (33) 1234 5678 | contacto@redmedica.mx</p></div>
   <div class="login" id="loginArea">
       <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
        <div class="welcome">
            <h1>¡Bienvenido, <?php echo $_SESSION['nombre']; ?>!</h1>
            <p>Has iniciado sesión correctamente como: <?php echo $_SESSION['rol']?></p>
            <a href="../../controlador/logout.php"  class="btn-login">Cerrar Sesión</a>
        </div>
    <?php else: ?>
        <div class="not-logged">
            <p>No has iniciado sesión.</p>
            <a href="../../controlador/login.php"  class="btn-login">Iniciar sesión</a>
        </div>
    <?php endif; ?>
    </div>
    
  </header>

  <!-- NAVBAR -->
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
      <li><a href="Recetas.php">Recetas</a></li>
    </ul>
  </nav>


  <!-- FORMULARIO DE RECETAS -->
  <div class="container">
    <h2 class="mb-4">Registrar Receta Médica</h2>

    <form id="receta-form" class="needs-validation" method="post" action="../controlador/guardar_receta.php" novalidate>
      <!-- Información del Médico -->
      <div class="row mb-4">
        <div class="col-12">
          <h5>Información del Médico</h5>
        </div>
        <div class="col-md-6">
          <label class="form-label">Médico que prescribe</label>
            <p><?php echo $_SESSION['nombre']; ?></p>
        </div>
        <div class="col-md-6">
          <label class="form-label">Cédula profesional</label>
          <input type="text" id="cedulaProfesional" class="form-control" placeholder="Ingrese cédula profesional">
        </div>
      </div>

      <!-- Información del Paciente -->
      <div class="row mb-4">
        <div class="col-12">
          <h5>Información del Paciente</h5>
        </div>
        <div class="col-md-6">
          <label class="form-label">Nombre del paciente</label>
          <input type="text" name="nombrePaciente"id="nombrePaciente" class="form-control" placeholder="Juan Pérez Hernández" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">genero</label>
          <input type="text" name="generoPaciente" id="genero" class="form-control" placeholder="No especifico" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">altura</label>
          <input type="text" name="alturaPaciente" id="nombrePaciente" class="form-control" placeholder="1.70 cm" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">peso</label>
          <input type="text" name="pesoPaciente" id="peso" class="form-control" placeholder="54 kg" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Edad</label>
          <input type="number" name="edadPaciente" id="edadPaciente" class="form-control" placeholder="35" required>
        </div>
        <div class="col-12 mt-3">
          <label class="form-label">Correo del paciente</label>
          <input type="email" name="correoPaciente" id="correoPaciente" class="form-control" placeholder="paciente@ejemplo.com" required>
        </div>

      </div>

      <!-- Medicamentos -->
      <div class="row mb-4">
        <div class="col-12">
          <h5>Medicamentos Prescritos</h5>
          <div id="medicamentos-container">
            <!-- Primer medicamento -->
            <div class="medicamento-item border p-3 mb-3 rounded">
              <div class="row">
                <div class="col-md-4">
                  <label class="form-label">Medicamento</label>
                  <input type="text" class="form-control medicamento-nombre" list="lista-medicamentos" placeholder="Paracetamol" required>
                </div>
                <div class="col-md-2">
                  <label class="form-label">Cantidad</label>
                  <input type="text" class="form-control medicamento-cantidad" placeholder="1 tableta" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Frecuencia</label>
                  <input type="text" class="form-control medicamento-frecuencia" placeholder="Cada 8 horas" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Duración</label>
                  <input type="text" class="form-control medicamento-duracion" placeholder="7 días" required>
                </div>
              </div>
              <div class="row mt-2">
                <div class="col-12">
                  <label class="form-label">Instrucciones especiales</label>
                  <input type="text" class="form-control medicamento-instrucciones" placeholder="Tomar después de los alimentos">
                  <button type="button" class="btn btn-outline-danger btn-sm mt-2" onclick="eliminarMedicamento(this)">
                    Eliminar Medicamento
                  </button>
                </div>
              </div>
            </div>
          </div>
          <button type="button" class="btn btn-outline-primary btn-sm" onclick="agregarMedicamento()">
            Agregar otro medicamento
          </button>
        </div>
      </div>

      <!-- Instrucciones adicionales -->
      <div class="mb-4">
        <label class="form-label">Instrucciones adicionales</label>
        <textarea id="instruccionesAdicionales" class="form-control" rows="3" placeholder="Recomendaciones generales, dieta, etc."></textarea>
      </div>

      <!-- Fecha y firma -->
      <div class="row mb-4">
        <div class="col-md-6">
          <label class="form-label">Fecha de prescripción</label>
          <input type="date" id="fechaPrescripcion" class="form-control" required min="">
          <div id="errorFecha" class="error-fecha">
            No se puede seleccionar una fecha anterior al día actual.
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Lugar de expedición</label>
          <input type="text" id="lugarExpedicion" class="form-control" value="Guadalajara, Jalisco" required>
        </div>
      </div>

      <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <button type="button" class="btn btn-secondary me-md-2" onclick="previsualizarReceta()">
          Previsualizar
        </button>
        <button type="submit" class="btn btn-primary">
          Guardar Receta
        </button>
      </div>
    </form>

    <!-- Previsualización -->
    <div id="receta-preview" class="receta-preview">
      <h5>Vista Previa de la Receta</h5>
      <div id="preview-content"></div>
    </div>

    <p id="mensaje" class="mt-3"></p>
  </div>

  <datalist id="lista-medicamentos"></datalist>

  <script src="./js/navbar.js"></script>
  <script src="./js/Recetas.js"></script>
</body>
</html>