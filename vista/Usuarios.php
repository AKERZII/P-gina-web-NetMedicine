<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Usuarios | Red Médica</title>
  <link rel="stylesheet" href="./css/Principal.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { animation: fadeInPage 0.8s ease-in-out; }
    @keyframes fadeInPage { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .container {
      max-width: 900px;
      margin: 50px auto;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      padding: 30px;
    }
    h1 {
      color: #1b3a57;
      margin-bottom: 30px;
      text-align: center;
    }
    .btn-danger {
      font-size: 14px;
      padding: 4px 10px;
    }
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
      <li><a href="./Principal.php">Inicio</a></li>
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
    <h1>Usuarios Registrados</h1>
    <div class="table-responsive">
      <table class="table table-striped table-custom">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Rol</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody id="tabla-usuarios">
          <!-- Se llenará con JS -->
        </tbody>
      </table>
    </div>
  </div>

  <script src="./js/navbar.js"></script>
  <script>
    const tabla = document.getElementById("tabla-usuarios");
    let usuarios = JSON.parse(localStorage.getItem("usuarios")) || [];

    function renderUsuarios() {
      tabla.innerHTML = "";
      usuarios.forEach((u, index) => {
        const fila = document.createElement("tr");
        fila.innerHTML = `
          <td>${u.nombre}</td>
          <td>${u.apellido}</td>
          <td>${u.correo}</td>
          <td>${u.telefono}</td>
          <td>${u.rol}</td>
          <td><button class="btn btn-danger" onclick="eliminarUsuario(${index})">Eliminar</button></td>
        `;
        tabla.appendChild(fila);
      });
    }

    function eliminarUsuario(index) {
      if (confirm("¿Eliminar este usuario?")) {
        usuarios.splice(index, 1);
        localStorage.setItem("usuarios", JSON.stringify(usuarios));
        renderUsuarios();
      }
    }

    renderUsuarios();
  </script>
</body>
</html>