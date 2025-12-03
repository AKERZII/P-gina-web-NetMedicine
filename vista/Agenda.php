<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$userRole = $_SESSION['rol'] ?? 'paciente';

// Permitir solo médicos y administradores
if ($userRole !== 'medico' && $userRole !== 'administrador') {
    header('Location: ./soloAdmin.php');
    exit;
}

// Mostrar mensajes de éxito/error
$mensaje = '';
$tipoMensaje = '';
if (isset($_SESSION['ok_cita'])) {
    $mensaje = $_SESSION['ok_cita'];
    $tipoMensaje = 'success';
    unset($_SESSION['ok_cita']);
}
if (isset($_SESSION['error_cita'])) {
    $mensaje = $_SESSION['error_cita'];
    $tipoMensaje = 'danger';
    unset($_SESSION['error_cita']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamiento de Citas Médicas | Red Médica</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/Principal.css">
    <link rel="stylesheet" href="./css/Agenda.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* --- ESTILOS CONSISTENTES CON EL PROYECTO --- */
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            animation: fadeInPage 0.8s ease-in-out;
        }

        @keyframes fadeInPage {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Títulos y encabezados */
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h1 i {
            color: #3498db;
        }

        .section-title {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #3498db;
        }

        /* Calendario con colores consistentes */
        .calendar-container {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: 1px solid #dee2e6;
        }

        .calendar-header {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
        }

        .month-year {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.2rem;
        }

        .nav-btn {
            background: #3498db;
            color: white;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .calendar-weekdays {
            background: #e8f4fc;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .calendar-weekdays div {
            color: #2c3e50;
            font-weight: 600;
        }

        .calendar-day {
            transition: all 0.2s ease;
            border: 1px solid #e0e0e0;
        }

        .calendar-day:hover:not(.empty):not(.past-day) {
            background: #3498db;
            color: white;
            transform: scale(1.05);
        }

        .selected-date {
            background-color: #27ae60 !important;
            color: white !important;
            font-weight: bold;
            border-color: #27ae60 !important;
        }

        .today {
            background-color: #e3f2fd !important;
            font-weight: bold;
            border-color: #3498db !important;
        }

        .past-day {
            background-color: #f8f9fa !important;
            color: #95a5a6 !important;
            cursor: not-allowed !important;
            border-color: #e9ecef !important;
        }

        .empty {
            background: transparent;
            border: none;
        }

        /* Formulario con estilo consistente */
        .form-container {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: 1px solid #dee2e6;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: #4c7ea6;
            width: 20px;
        }

        .form-control, .form-select {
            border: 2px solid #dee2e6;
            border-radius: 6px;
            padding: 10px 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .form-control::placeholder {
            color: #95a5a6;
        }

        /* Botones con estilo consistente */
        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
            color: white;
            padding: 10px 25px;
            font-weight: 600;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }

        .btn-secondary {
            background-color: #95a5a6;
            border-color: #95a5a6;
            color: white;
            padding: 10px 25px;
            font-weight: 600;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
            border-color: #7f8c8d;
            transform: translateY(-2px);
        }

        .button-group {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        /* Mensajes */
        .info-message {
            background-color: #e8f4fc;
            border-left: 4px solid #3498db;
            color: #2c3e50;
        }

        .info-message i {
            color: #3498db;
        }

        .selected-date-info {
            background-color: #e8f4fc;
            border-left: 4px solid #27ae60;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        #selected-date-display {
            font-weight: bold;
            color: #27ae60;
        }

        /* Alertas Bootstrap mejoradas */
        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
            margin: 20px 0;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        /* Footer */
        footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 0.9rem;
        }

        footer p {
            margin-bottom: 10px;
        }

        footer i {
            color: #3498db;
            margin-right: 8px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .appointment-section {
                flex-direction: column;
            }
            
            .calendar-container,
            .form-container {
                width: 100%;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
                justify-content: center;
            }
        }

        /* Validación Bootstrap SIN mensajes debajo */
        .was-validated .form-control:valid,
        .was-validated .form-select:valid {
            border-color: #28a745;
            padding-right: calc(1.5em + .75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(.375em + .1875rem) center;
            background-size: calc(.75em + .375rem) calc(.75em + .375rem);
        }

        .was-validated .form-control:invalid,
        .was-validated .form-select:invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + .75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(.375em + .1875rem) center;
            background-size: calc(.75em + .375rem) calc(.75em + .375rem);
        }

        /* Estados de validación SIN mensajes */
        .is-valid {
            border-color: #28a745 !important;
            background-color: #fff !important;
        }

        .is-invalid {
            border-color: #dc3545 !important;
            background-color: #fff !important;
        }

        /* Ocultar los mensajes de feedback */
        .invalid-feedback,
        .valid-feedback {
            display: none !important;
        }
    </style>
</head>
<body>
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
        <h1><i class="fas fa-calendar-check"></i> Sistema de Agendamiento de Citas</h1>
        <p class="lead text-muted">Seleccione una fecha en el calendario y complete el formulario para agendar su cita médica</p>
        
        <!-- Mostrar mensajes -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
                <i class="fas <?php echo $tipoMensaje === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
                <?php echo htmlspecialchars($mensaje); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="appointment-section">
            <div class="calendar-container mb-4">
                <h2 class="section-title"><i class="far fa-calendar-alt"></i> Calendario de Citas</h2>
                <div class="alert alert-info info-message">
                    <i class="fas fa-info-circle"></i>
                    <span>Haga clic en un día disponible para seleccionar la fecha de su cita</span>
                </div>
                
                <div class="calendar-header">
                    <button class="nav-btn" id="prev-month"><i class="fas fa-chevron-left"></i></button>
                    <div class="month-year text-center flex-grow-1" id="month-year"></div>
                    <button class="nav-btn" id="next-month"><i class="fas fa-chevron-right"></i></button>
                </div>
                
                <div class="calendar-weekdays">
                    <div>Dom</div>
                    <div>Lun</div>
                    <div>Mar</div>
                    <div>Mié</div>
                    <div>Jue</div>
                    <div>Vie</div>
                    <div>Sáb</div>
                </div>
                
                <div class="calendar-days" id="calendar-days">
                    <!-- Los días se generarán con JavaScript -->
                </div>
            </div>

            <!-- Formulario de cita -->            
            <div class="form-container" id="form-container">
                <h2 class="section-title"><i class="far fa-edit"></i> Formulario de Cita</h2>
                
                <div class="alert alert-primary selected-date-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Fecha seleccionada:</strong> 
                            <span id="selected-date-display" class="ms-2">No se ha seleccionado ninguna fecha</span>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" id="change-date" style="display:none;">
                            <i class="fas fa-exchange-alt me-1"></i>Cambiar
                        </button>
                    </div>
                </div>
                
                <form id="formCita" class="needs-validation" action="../controlador/Registroagenda.php" method="POST" novalidate>
                    <input type="hidden" name="fechaInput" id="fechaInputHidden">
                    
                    <div class="form-group">
                        <label for="nombreInput" class="form-label">
                            <i class="fas fa-user"></i> Nombre del paciente *
                        </label>
                        <input type="text" 
                               id="nombreInput" 
                               name="nombreInput" 
                               class="form-control" 
                               required 
                               placeholder="Ingrese su nombre completo"
                               minlength="3"
                               maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="correoInput" class="form-label">
                            <i class="fas fa-envelope"></i> Correo electrónico *
                        </label>
                        <input type="email" 
                               id="correoInput" 
                               name="correoInput" 
                               class="form-control" 
                               required 
                               placeholder="ejemplo@correo.com"
                               pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                    </div>
                    
                    <div class="form-group">
                        <label for="tituloInput" class="form-label">
                            <i class="fas fa-stethoscope"></i> Título de la cita *
                        </label>
                        <input type="text" 
                               id="tituloInput" 
                               name="tituloInput" 
                               class="form-control" 
                               required 
                               placeholder="Ej: Consulta de rutina, Revisión, etc."
                               minlength="5"
                               maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="descripcionInput" class="form-label">
                            <i class="fas fa-comment-medical"></i> Descripción de la cita
                        </label>
                        <textarea id="descripcionInput" 
                                  name="descripcionInput" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Describa brevemente el motivo de la cita"
                                  maxlength="500"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="tipoSelect" class="form-label">
                            <i class="fas fa-tag"></i> Tipo de cita *
                        </label>
                        <select id="tipoSelect" 
                                name="tipoInput" 
                                class="form-select" 
                                required>
                            <option value="">Selecciona...</option>
                            <option value="Consulta">Consulta General</option>
                            <option value="Examen">Examen Médico</option>
                            <option value="Urgencia">Urgencia</option>
                            <option value="Especialista">Especialista</option>
                            <option value="Control">Control</option>
                        </select>
                    </div>
          
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calendar-check me-1"></i> Agendar Cita
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancel-btn">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <footer>
            <p>© 2023 Sistema de Agendamiento de Citas Médicas | Red Médica. Todos los derechos reservados.</p>
            <p><i class="fas fa-phone"></i> Para asistencia, contacte al: 01-800-123-4567</p>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // ========== CALENDARIO ==========
        let currentDate = new Date();
        let selectedDate = null;
        
        const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
                          "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        
        const dayNames = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
        
        function updateCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            // Actualizar mes y año en el encabezado
            document.getElementById('month-year').textContent = `${monthNames[month]} ${year}`;
            
            // Obtener primer día del mes
            const firstDay = new Date(year, month, 1);
            // Obtener último día del mes
            const lastDay = new Date(year, month + 1, 0);
            // Días en el mes
            const daysInMonth = lastDay.getDate();
            // Día de la semana del primer día (0 = Domingo, 6 = Sábado)
            const startingDay = firstDay.getDay();
            
            // Limpiar calendario
            const calendarDays = document.getElementById('calendar-days');
            calendarDays.innerHTML = '';
            
            // Añadir días vacíos al inicio
            for (let i = 0; i < startingDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day empty';
                calendarDays.appendChild(emptyDay);
            }
            
            // Obtener fecha actual
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            // Añadir días del mes
            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                dayElement.textContent = day;
                
                const currentDay = new Date(year, month, day);
                currentDay.setHours(0, 0, 0, 0);
                
                // Verificar si es hoy
                if (currentDay.getTime() === today.getTime()) {
                    dayElement.classList.add('today');
                }
                
                // Verificar si es una fecha pasada
                if (currentDay < today) {
                    dayElement.classList.add('past-day');
                    dayElement.style.cursor = 'not-allowed';
                    dayElement.title = 'No se pueden agendar citas en fechas pasadas';
                } else {
                    // Hacer clicable solo fechas futuras o hoy
                    dayElement.addEventListener('click', () => selectDate(currentDay));
                }
                
                // Resaltar fecha seleccionada
                if (selectedDate && 
                    currentDay.getDate() === selectedDate.getDate() &&
                    currentDay.getMonth() === selectedDate.getMonth() &&
                    currentDay.getFullYear() === selectedDate.getFullYear()) {
                    dayElement.classList.add('selected-date');
                }
                
                calendarDays.appendChild(dayElement);
            }
        }
        
        function selectDate(date) {
            selectedDate = date;
            updateCalendar();
            
            // Formatear fecha para mostrar
            const formattedDate = `${dayNames[date.getDay()]}, ${date.getDate()} de ${monthNames[date.getMonth()]} de ${date.getFullYear()}`;
            document.getElementById('selected-date-display').textContent = formattedDate;
            
            // Formatear fecha para el input hidden (YYYY-MM-DD)
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            document.getElementById('fechaInputHidden').value = `${year}-${month}-${day}`;
            
            // Mostrar botón para cambiar fecha
            document.getElementById('change-date').style.display = 'inline-block';
            
            // Enfocar el primer campo del formulario
            document.getElementById('nombreInput').focus();
        }
        
        function changeDate() {
            selectedDate = null;
            document.getElementById('selected-date-display').textContent = 'No se ha seleccionado ninguna fecha';
            document.getElementById('fechaInputHidden').value = '';
            document.getElementById('change-date').style.display = 'none';
            updateCalendar();
        }
        
        // ========== VALIDACIÓN BOOTSTRAP ==========
        function initBootstrapValidation() {
            // Obtener el formulario
            const form = document.getElementById('formCita');
            
            // Validación Bootstrap
            form.addEventListener('submit', function(event) {
                // Validar que se haya seleccionado una fecha
                if (!selectedDate) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Mostrar alerta Bootstrap
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-warning alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Por favor seleccione una fecha en el calendario.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    
                    // Insertar después del primer alert si existe, sino al inicio
                    const existingAlert = document.querySelector('.alert');
                    if (existingAlert) {
                        existingAlert.parentNode.insertBefore(alertDiv, existingAlert.nextSibling);
                    } else {
                        form.parentNode.insertBefore(alertDiv, form);
                    }
                    
                    // Desplazarse al calendario
                    document.querySelector('.calendar-container').scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                    
                    return;
                }
                
                // Validar que no sea fecha pasada
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const selected = new Date(document.getElementById('fechaInputHidden').value);
                
                if (selected < today) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Mostrar alerta Bootstrap
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        <i class="fas fa-exclamation-circle me-2"></i>
                        No se pueden agendar citas en fechas pasadas.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    
                    form.parentNode.insertBefore(alertDiv, form);
                    return;
                }
                
                // Validación Bootstrap nativa
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
            
            // Validación en tiempo real - solo borde rojo/verde
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.checkValidity()) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    }
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid')) {
                        this.classList.remove('is-invalid');
                    }
                    if (this.checkValidity()) {
                        this.classList.add('is-valid');
                    }
                });
            });
        }
        
        // ========== INICIALIZACIÓN ==========
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar calendario
            updateCalendar();
            
            // Inicializar validación Bootstrap
            initBootstrapValidation();
            
            // Botones de navegación del calendario
            document.getElementById('prev-month').addEventListener('click', function() {
                currentDate.setMonth(currentDate.getMonth() - 1);
                updateCalendar();
            });
            
            document.getElementById('next-month').addEventListener('click', function() {
                currentDate.setMonth(currentDate.getMonth() + 1);
                updateCalendar();
            });
            
            // Botón cambiar fecha
            document.getElementById('change-date').addEventListener('click', changeDate);
            
            // Botón cancelar
            document.getElementById('cancel-btn').addEventListener('click', function() {
                if (confirm('¿Está seguro de que desea cancelar? Se perderán los datos ingresados.')) {
                    changeDate();
                    document.getElementById('formCita').reset();
                    document.getElementById('formCita').classList.remove('was-validated');
                    document.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
                        el.classList.remove('is-valid', 'is-invalid');
                    });
                }
            });
        });
    </script>
</body>
</html>