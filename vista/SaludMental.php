<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Salud Mental | Red Médica</title>
  <link rel="stylesheet" href="./css/Principal.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { animation: fadeInPage 0.8s ease-in-out; }
    @keyframes fadeInPage { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .container { max-width: 1000px; margin: 50px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 30px; }
    .hero-img { width: 100%; border-radius: 12px; margin-bottom: 20px; }
    h2 { margin-top: 28px; }
  </style>
</head>
<body class="fade-in">
  <header class="top-header">
    <div class="logo"><img src="./img/Logo.jpg" alt="Logo Red Médica"></div>
    <div class="contacto"><p>Tel: +52 (33) 1234 5678 | contacto@redmedica.mx</p></div>
    <div class="login" id="loginArea">
      <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
        <div class="welcome">
            <h1>¡Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h1>
            <p>Has iniciado sesión correctamente como: <?php echo htmlspecialchars($_SESSION['rol']); ?></p>
            <a href="../controlador/login.php" class="btn-login">Cerrar Sesión</a>
        </div>
    <?php else: ?>
        <div class="not-logged">
            <p>No has iniciado sesión.</p>
            <a href="../controlador/login.php" class="btn-login">Ir al Login</a>
        </div>
    <?php endif; ?>
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
      <li><a href="./Usuarios.php">Reportes</a></li>
    </ul>
  </nav>

  <div class="container">
    <h1 class="mb-4">Salud Mental</h1>
    <h2>Áreas comunes de atención</h2>
    <ul>
      <li>Psicología: apoyo en procesos emocionales, terapia individual o grupal.</li>
      <li>Psiquiatría: diagnóstico y tratamiento de trastornos mentales.</li>
      <li>Psicoterapia: técnicas para mejorar la gestión de pensamientos y emociones.</li>
      <li>Programas de prevención: promoción de hábitos saludables y resiliencia.</li>
    </ul>

    <h2>Importancia</h2>
    <p>Cuidar la salud mental es tan importante como atender la salud física. Un buen estado mental favorece relaciones sanas, toma de decisiones adecuadas y calidad de vida.</p>

    <h2>Factores que influyen</h2>
    <p>La salud mental puede verse afectada por factores biológicos, experiencias de vida, entorno social y hábitos cotidianos. Reconocer señales de alerta y buscar apoyo profesional es clave para una atención temprana.</p>

    <h2>Nota importante</h2>
    <p><em>Red Médica no ofrece atención en salud mental directamente.</em> Nuestra función es conectar a los usuarios con hospitales, clínicas y profesionales especializados. Los <strong>horarios, modalidades de atención y requisitos</strong> pueden variar según la institución elegida.</p>

    <h2>Más información</h2>
    <p>Para conocer detalles específicos sobre programas de salud mental, te recomendamos contactar directamente con el hospital o clínica de tu preferencia.</p>
  </div>

  <script src="./js/navbar.js"></script>
</body>
</html>