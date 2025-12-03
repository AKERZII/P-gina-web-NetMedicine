<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solo administradores - Red Médica</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/usuarios.css">
    <link rel="stylesheet" href="./css/Principal.css">
</head>
<style>
  .content {
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .icon-container {
            width: 150px;
            height: 150px;
            background-color: #f0f5ff;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
            border: 5px solid #e3ecff;
        }
        
        .lock-icon {
            font-size: 70px;
            color: #4a6491;
        }
        
        .message {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .message h2 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .message p {
            font-size: 1.2rem;
            line-height: 1.6;
            color: #555;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .admin-requirements {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            width: 100%;
            max-width: 600px;
            margin-bottom: 30px;
            border-left: 5px solid #4a6491;
        }
        
        .admin-requirements h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-requirements ul {
            list-style-type: none;
            padding-left: 5px;
        }
        
        .admin-requirements li {
            padding: 10px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .admin-requirements i {
            color: #4a6491;
            width: 20px;
        }
        
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .btn {
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary {
            background-color: #4a6491;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #3a547e;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(74, 100, 145, 0.3);
        }
        
        .btn-secondary {
            background-color: #f0f5ff;
            color: #4a6491;
            border: 2px solid #c3cfe2;
        }
        
        .btn-secondary:hover {
            background-color: #e3ecff;
            transform: translateY(-3px);
        }
        
        .contact-info {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #eee;
            width: 100%;
            text-align: center;
        }
        
        .contact-info p {
            color: #666;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .contact-info a {
            color: #4a6491;
            text-decoration: none;
            font-weight: 600;
        }
        
        .contact-info a:hover {
            text-decoration: underline;
        }
</style>
<body>
<!-- Encabezado -->
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
        
        <div class="content">
            <div class="icon-container">
                <i class="fas fa-user-shield lock-icon"></i>
            </div>
            
            <div class="message">
                <h2>Acceso Permitido Solo para Administradores</h2>
                <p>Esta área del sistema está restringida exclusivamente a usuarios con privilegios de administrador. Si necesitas acceso, por favor contacta con el administrador del sistema o verifica tus credenciales.</p>
            </div>
            
            <div class="admin-requirements">
                <h3><i class="fas fa-clipboard-check"></i> Requisitos para Acceso de Administrador</h3>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Credenciales válidas de administrador</li>
                    <li><i class="fas fa-check-circle"></i> Permisos específicos asignados a tu cuenta</li>
                    <li><i class="fas fa-check-circle"></i> Acceso autorizado por el supervisor del sistema</li>
                    <li><i class="fas fa-check-circle"></i> Cuenta verificada y activa en el sistema</li>
                </ul>
            </div>
            
            <div class="actions">
                <button class="btn btn-primary" onclick="window.location.href='login.html'">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión como Administrador
                </button>
                <button class="btn btn-secondary" onclick="window.location.href='index.html'">
                    <i class="fas fa-home"></i> Volver a la Página Principal
                </button>
            </div>
            
            <div class="contact-info">
                <p><i class="fas fa-info-circle"></i> Si crees que deberías tener acceso a esta área, por favor contacta al equipo de soporte:</p>
                <p><i class="fas fa-envelope"></i> Email: <a href="mailto:admin@sistema.com">admin@sistema.com</a></p>
                <p><i class="fas fa-phone"></i> Teléfono: <a href="tel:+1234567890">+1 (234) 567-890</a></p>
            </div>
        </div>
    </div>
    
    <script>
        // Simulación de temporizador para redireccionar automáticamente después de 60 segundos
        let tiempoRestante = 60;
        const tiempoRedireccion = document.createElement('p');
        tiempoRedireccion.style.marginTop = '15px';
        tiempoRedireccion.style.fontWeight = '600';
        tiempoRedireccion.style.color = '#4a6491';
        document.querySelector('.actions').appendChild(tiempoRedireccion);
        
        const intervalo = setInterval(() => {
            tiempoRestante--;
            tiempoRedireccion.textContent = `Redireccionando a la página principal en ${tiempoRestante} segundos...`;
            
            if (tiempoRestante <= 0) {
                clearInterval(intervalo);
                window.location.href = './src/principal.php';
            }
        }, 1000);
        
        // Manejo del botón de volver a la página principal
        document.querySelector('.btn-secondary').addEventListener('click', function() {
            clearInterval(intervalo);
            window.location.href = './src/principal.php';
        });
        
        // Manejo del botón de inicio de sesión
        document.querySelector('.btn-primary').addEventListener('click', function() {
            clearInterval(intervalo);
            window.location.href = './login.php';
        });
    </script>
</body>
</html>