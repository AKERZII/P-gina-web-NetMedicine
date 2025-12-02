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
    header('Location: ./medicosPublic.php');
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
    $tipoMensaje = 'error';
    unset($_SESSION['error_cita']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamiento de Citas Médicas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/Principal.css">
    <link rel="stylesheet" href="./css/Agenda.css">
    <style>
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-weight: bold;
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
        .selected-date {
            background-color: #4CAF50 !important;
            color: white !important;
            font-weight: bold;
        }
        .today {
            background-color: #e3f2fd !important;
            font-weight: bold;
        }
        .past-day {
            color: #ccc !important;
            cursor: not-allowed !important;
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
        <p>Seleccione una fecha en el calendario y complete el formulario para agendar su cita médica</p>
        
        <!-- Mostrar mensajes -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipoMensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        
        <div class="appointment-section">
            <div class="calendar-container">
                <h2 class="section-title"><i class="far fa-calendar-alt"></i> Calendario de Citas</h2>
                <div class="info-message">
                    <i class="fas fa-info-circle"></i>
                    <span>Haga clic en un día disponible para seleccionar la fecha de su cita</span>
                </div>
                
                <div class="calendar-header">
                    <button class="nav-btn" id="prev-month"><i class="fas fa-chevron-left"></i></button>
                    <div class="month-year" id="month-year"></div>
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
                
                <div class="selected-date-info">
                    <div>Fecha seleccionada: <span id="selected-date-display">No se ha seleccionado ninguna fecha</span></div>
                    <button class="btn-secondary" id="change-date" style="display:none;">Cambiar fecha</button>
                </div>
                
                <form id="formCita" action="../controlador/Registroagenda.php" method="POST">
                    <input type="hidden" name="fechaInput" id="fechaInputHidden">
                    
                    <div class="form-group">
                        <label for="nombreInput"><i class="fas fa-user"></i> Nombre del paciente *</label>
                        <input type="text" id="nombreInput" name="nombreInput" required placeholder="Ingrese su nombre completo">
                    </div>
                    
                    <div class="form-group">
                        <label for="correoInput"><i class="fas fa-envelope"></i> Correo electrónico *</label>
                        <input type="email" id="correoInput" name="correoInput" required placeholder="ejemplo@correo.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="tituloInput"><i class="fas fa-stethoscope"></i> Título de la cita *</label>
                        <input type="text" id="tituloInput" name="tituloInput" required placeholder="Ej: Consulta de rutina, Revisión, etc.">
                    </div>

                    <div class="form-group">
                        <label for="descripcionInput"><i class="fas fa-comment-medical"></i> Descripción de la cita</label>
                        <textarea id="descripcionInput" name="descripcionInput" rows="3" placeholder="Describa brevemente el motivo de la cita"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="tipoSelect"><i class="fas fa-tag"></i> Tipo de cita *</label>
                        <select id="tipoSelect" name="tipoInput" class="form-select" required>
                            <option value="">Selecciona...</option>
                            <option value="Consulta">Consulta General</option>
                            <option value="Examen">Examen Médico</option>
                            <option value="Urgencia">Urgencia</option>
                            <option value="Especialista">Especialista</option>
                            <option value="Control">Control</option>
                        </select>
                    </div>
          
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Agendar Cita</button>
                        <button type="button" class="btn btn-secondary" id="cancel-btn">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
        
        <footer>
            <p>© 2023 Sistema de Agendamiento de Citas Médicas. Todos los derechos reservados.</p>
            <p><i class="fas fa-phone"></i> Para asistencia, contacte al: 01-800-123-4567</p>
        </footer>
    </div>

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
        
        // ========== INICIALIZACIÓN ==========
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar calendario
            updateCalendar();
            
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
                }
            });
            
            // Validación del formulario
            document.getElementById('formCita').addEventListener('submit', function(event) {
                if (!selectedDate) {
                    event.preventDefault();
                    alert('Por favor seleccione una fecha en el calendario.');
                    return;
                }
                
                const fechaHidden = document.getElementById('fechaInputHidden').value;
                if (!fechaHidden) {
                    event.preventDefault();
                    alert('Error: No se ha seleccionado una fecha válida.');
                    return;
                }
                
                // Validar que no sea fecha pasada
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const selected = new Date(fechaHidden);
                
                if (selected < today) {
                    event.preventDefault();
                    alert('No se pueden agendar citas en fechas pasadas.');
                    return;
                }
                
                // Si todo está bien, el formulario se envía
            });
        });
    </script>
</body>
</html>