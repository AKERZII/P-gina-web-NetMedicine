
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login - Red Médica</title>
  <link rel="stylesheet" href="./css/Principal.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { 
      margin: 0;
      padding: 0;
      font-family: 'Times New Roman', serif;
      background-color: #f0f4f7;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-container {
      width: 450px;
      background: linear-gradient(to bottom, #ffffff, #e8eff5);
      padding: 30px 35px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      border: 1px solid #cdd6df;
    }
    h2 {
      text-align: center;
      font-size: 28px;
      font-weight: bold;
      color: #2e3a44;
      margin-bottom: 20px;
      text-shadow: 1px 1px 2px rgba(180, 180, 180, 0.4);
    }
    label {
      font-weight: bold;
      color: #2e3a44;
      font-size: 16px;
    }
    input, select {
      width: 100%;
      padding: 10px;
      margin: 6px 0 12px;
      border: 1px solid #aebac5;
      border-radius: 8px;
      font-size: 15px;
      background-color: #f9fbfd;
      transition: border 0.3s ease, background 0.3s ease;
    }
    button {
      width: 100%;
      padding: 12px;
      background-color: #4c7ea6;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 17px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    button:hover {
      background-color: #365b7d;
    }
    .toggle-form {
      text-align: center;
      margin-top: 15px;
    }
    .toggle-form a {
      color: #4c7ea6;
      text-decoration: none;
      font-weight: bold;
      cursor: pointer;
    }
    .toggle-form a:hover {
      text-decoration: underline;
    }
    #mensaje {
      margin-top: 15px;
      text-align: center;
      font-size: 16px;
      font-weight: bold;
      min-height: 20px;
    }
    .form-section {
      display: none;
    }
    .form-section.active {
      display: block;
    }
    .password-requirements {
      font-size: 12px;
      color: #666;
      margin-top: -8px;
      margin-bottom: 10px;
    }
    .alert {
      padding: 10px;
      margin: 10px 0;
      border-radius: 5px;
      text-align: center;
    }
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <!-- FORMULARIO LOGIN -->
    <div id="login-section" class="form-section active">
      <h2>Iniciar Sesión</h2>
      
      <form id="login-form" action="../controlador/login.php" method="POST">
        <div class="mb-2">
          <input type="text" id="login-user" name="correo" class="form-control" placeholder="Correo o Teléfono" required>
        </div>

        <div class="mb-2">
          <input type="password" id="login-pass" name="password" class="form-control" placeholder="Contraseña" required>
        </div>

        <button type="submit">Entrar</button>
      </form>

      <div class="toggle-form">
        <p>¿No tienes cuenta? <a id="show-register">Regístrate aquí</a></p>
      </div>
    </div>

    <!-- FORMULARIO REGISTRO -->
    <div id="register-section" class="form-section">
      <h2>Registrarse</h2>
      
      <form id="register-form" action="../controlador/Registro.php" method="POST">
        <div class="row">
          <div class="col-md-6">
            <input type="text" id="reg-nombre" name="nombre" class="form-control" placeholder="Nombre" required>
          </div>
          <div class="col-md-6">
            <input type="text" id="reg-apellido" name="apellido" class="form-control" placeholder="Apellidos" required>
          </div>
        </div>

        <input type="email" id="reg-correo" name="correo" class="form-control" placeholder="Correo electrónico" required>
        
        <input type="tel" id="reg-telefono" name="telefono" class="form-control" placeholder="Teléfono" required>
        
        <input type="password" id="reg-password" name="password" class="form-control" placeholder="Contraseña" required>

        <div class="password-requirements">
          Mínimo 6 caracteres
        </div>
        
        <input type="password" id="reg-confirm-password" name="confirm_password" class="form-control" placeholder="Confirmar Contraseña" required>

        <select id="reg-rol" name="rol" class="form-control" required>
          <option value="">Seleccionar rol</option>
          <option value="administrador">Administrador</option>
          <option value="medico">Médico</option>
          <option value="paciente">Paciente</option>
        </select>

        <button type="submit" style="background-color: #28a745;">Registrarse</button>
      </form>

      <div class="toggle-form">
        <p>¿Ya tienes cuenta? <a id="show-login">Inicia sesión aquí</a></p>
      </div>
    </div>

    <div id="mensaje">
      <?php
      // Mostrar mensajes de éxito o error
      if (isset($_GET['message'])) {
          $message = htmlspecialchars($_GET['message']);
          $type = isset($_GET['success']) && $_GET['success'] == 'true' ? 'alert-success' : 'alert-error';
          echo "<div class='alert $type'>$message</div>";
      }
      ?>
    </div>
  </div>

  <script>
    // Toggle entre formularios
    document.getElementById('show-register').addEventListener('click', function() {
      document.getElementById('login-section').classList.remove('active');
      document.getElementById('register-section').classList.add('active');
      document.getElementById('mensaje').innerHTML = '';
    });

    document.getElementById('show-login').addEventListener('click', function() {
      document.getElementById('register-section').classList.remove('active');
      document.getElementById('login-section').classList.add('active');
      document.getElementById('mensaje').innerHTML = '';
    });

    // Validación en tiempo real para confirmar contraseña
    document.getElementById('reg-confirm-password').addEventListener('input', function() {
      const password = document.getElementById('reg-password').value;
      const confirmPassword = this.value;
      
      if (confirmPassword && password !== confirmPassword) {
        this.style.border = '2px solid #e74c3c';
        this.style.background = '#fdeaea';
      } else if (confirmPassword && password === confirmPassword) {
        this.style.border = '2px solid #4CAF50';
        this.style.background = '#eaf7ea';
      } else {
        this.style.border = '';
        this.style.background = '';
      }
    });

    // Validación del formulario de registro antes de enviar
    document.getElementById('register-form').addEventListener('submit', function(e) {
      const password = document.getElementById('reg-password').value;
      const confirmPassword = document.getElementById('reg-confirm-password').value;
      
      if (password.length < 6) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 6 caracteres');
        return false;
      }
      
      if (password !== confirmPassword) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
        return false;
      }
    });
  </script>
</body>
</html>