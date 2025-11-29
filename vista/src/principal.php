<?php
session_start();
// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Red Médica - Inicio</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/Principal.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="fade-in">
  
 
  <header class="top-header">
    <div class="logo">
      <img src="../img/Logo.jpg" alt="Red Médica - Logo" loading="lazy">
    </div>
    <div class="contacto">
      <p>Tel: +52 (33) 1234 5678 | contacto@redmedica.mx</p>
    </div>
    <div class="login" id="loginArea">
       <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
        <div class="welcome">
            <h1>¡Bienvenido, <?php echo $_SESSION['nombre']; ?>!</h1>
            <p>Has iniciado sesión correctamente como: <?php echo $_SESSION['rol']?></p>
            <a href="../../controlador/login.php"  class="btn-login">Cerrar Sesión</a>
        </div>
    <?php else: ?>
        <div class="not-logged">
            <p>No has iniciado sesión.</p>
            <a href="../../controlador/login.php"  class="btn-login">Ir al Login</a>
        </div>
    <?php endif; ?>
    </div>
  </header>

  <nav class="navbar">
    <ul class="menu">
      <li><a href="../src/principal.php">Inicio</a></li>
      <li><a href="../Medicos.php">Hospitales & Médicos</a></li>
      <li><a href="../Agenda.php">Agenda</a></li>
      <li><a href="../Consultas.php">Consultas</a></li>
      <li class="dropdown">
        <a href="#">Servicios ▾</a>
        <ul class="submenu">
          <li><a href="../Hospitalizacion.php">Hospitalización</a></li>
          <li><a href="../Laboratorio.php">Laboratorio Clínico</a></li>
          <li><a href="../Rehabilitacion.php">Rehabilitación</a></li>
          <li><a href="../SaludMental.php">Salud Mental</a></li>
          <li><a href="../Farmacia.php">Farmacia</a></li>
          <li><a href="../Urgencias.php">Urgencias</a></li>
          <li><a href="../Planificacion.php">Planificación Familiar</a></li>
        </ul>
      </li>
      <li><a href="../Recetas.php">Recetas</a></li>
    </ul>
  </nav>


  <section class="hero">
    <div class="slider">
      <div class="slides">
        <img src="../img/images.jpg" alt="Hospital moderno" loading="lazy">
        <img src="../img/post_WhatsApp_Image_2023-03-12_at_10.06.19_AM__1_.jpeg" alt="Atención médica" loading="lazy">
        <img src="../img/0_Fachada-HAMexico.jpg" alt="Instalaciones médicas" loading="lazy">
      </div>
      <h1 class="titulo-hero">Red Médica</h1>
    </div>
  </section>

  <main class="container my-5">
    <div class="row">
      <div class="col-md-8 mx-auto text-center">
        <h2 class="mb-4">Bienvenido a Red Médica</h2>
        <p class="lead mb-4">
          Tu plataforma integral de servicios médicos. Conectamos pacientes con los mejores 
          profesionales de la salud y hospitales de la región.
        </p>
        
        <div class="row mt-5">
          <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
              <div class="card-body">
                <h5 class="card-title">Médicos</h5>
                <p class="card-text">Encuentra especialistas calificados en todas las áreas médicas.</p>
                <a href="../Medicos.php" class="btn-learn">Ver Médicos</a>
              </div>
            </div>
          </div>
          
          <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
              <div class="card-body">
                <h5 class="card-title">Citas</h5>
                <p class="card-text">Agenda tus consultas médicas de forma rápida y sencilla.</p>
                <a href="../Agenda.php" class="btn-learn">Agendar Cita</a>
              </div>
            </div>
          </div>
          
          <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
              <div class="card-body">
                <h5 class="card-title">Recetas</h5>
                <p class="card-text">Gestiona y consulta tus recetas médicas digitalmente.</p>
                <a href="../Recetas.php" class="btn-learn">Ver Recetas</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="../js/navbar.js"></script>
  <script>
    // Slider automático
    const slides = document.querySelectorAll(".slides img");
    let currentSlide = 0;

    function nextSlide() {
      slides.forEach(img => img.classList.remove("active"));
      currentSlide = (currentSlide + 1) % slides.length;
      slides[currentSlide].classList.add("active");
    }

    // Iniciar slider
    if (slides.length > 0) {
      slides[0].classList.add("active");
      setInterval(nextSlide, 5000);
    }
  </script>
</body>
</html>