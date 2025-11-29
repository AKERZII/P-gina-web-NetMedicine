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
  <title>Agenda de Citas | Red Médica</title>
  <link rel="stylesheet" href="./css/Principal.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <style>
    body { animation: fadeInPage 0.8s ease-in-out; }
    @keyframes fadeInPage { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    #calendar {
      max-width: 1000px; margin: 50px auto; background: #fff; border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 20px;
    }
    .horario-info {
      background-color: #e8f4fd;
      border-left: 4px solid #4c7ea6;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 15px;
    }
    .error-horario, .error-dia {
      color: #dc3545;
      font-size: 0.875em;
      margin-top: 0.25rem;
      display: none;
    }
    .dias-info {
      background-color: #f0f8ff;
      border-left: 4px solid #87ceeb;
      padding: 8px;
      border-radius: 4px;
      margin-top: 5px;
    }
  </style>
</head>
<body>

  <!-- ENCABEZADO -->
  <header class="top-header">
    <div class="logo"><img src="./img/Logo.jpg" alt="Logo"></div>
    <div class="contacto"><p>Tel: +52 (33) 1234 5678 | ✉ contacto@redmedica.mx</p></div>
    <div class="login" id="loginArea"><a href="./login.php" class="btn-login">Iniciar Sesión</a></div>
    <script>
      const usuarioActual = localStorage.getItem("usuarioActual");
      const rolUsuario = localStorage.getItem("rolUsuario");
      if (usuarioActual) {
        document.getElementById("loginArea").innerHTML = `
          <p>Bienvenido, <strong>${usuarioActual}</strong> ${rolUsuario ? "(" + rolUsuario + ")" : ""}</p>
          <button id="logoutBtn" class="btn-login">Cerrar Sesión</button>
        `;
        document.getElementById("logoutBtn").addEventListener("click", () => {
          localStorage.removeItem("usuarioActual");
          localStorage.removeItem("rolUsuario");
          window.location.reload();
        });
      }
    </script>
  </header>

  <!-- NAVBAR -->
  <nav class="navbar">
    <ul class="menu">
      <li><a href="./src/principal.php">Inicio</a></li>
      <li><a href="./Medicos.php">Hospitales & Médicos</a></li>
      <li><a href="./Agenda.php">Agenda</a></li>
      <li><a href="./Consultas.php">Consultas</a></li>
      <li class="dropdown">
        <a href="">Servicios ▾</a>
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

  <!-- CALENDARIO -->
  <div id="calendar"></div>

  <!-- MODAL PARA AGENDAR -->
  <div class="modal fade" id="citaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content" style="border-radius:12px;">
        <div class="modal-header">
          <h5 class="modal-title">Agendar cita</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <form id="formCita" class="needs-validation" novalidate>
            <input type="hidden" id="fechaInput">
            
            <!-- Información del médico seleccionado -->
            <div id="infoMedico" class="horario-info" style="display: none;">
              <strong>Médico seleccionado:</strong>
              <span id="nombreMedicoTexto"></span><br>
              <strong>Horario:</strong>
              <span id="horarioMedicoTexto"></span><br>
              <strong>Días de trabajo:</strong>
              <span id="diasMedicoTexto"></span>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Nombre del paciente</label>
              <input type="text" id="nombreInput" class="form-control" required>
              <div class="invalid-feedback">Por favor ingresa el nombre del paciente.</div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Seleccionar Médico</label>
              <select id="medicoSelect" class="form-select" required>
                <option value="">Seleccione un médico...</option>
              </select>
              <div class="invalid-feedback">Selecciona un médico.</div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Hospital Asignado</label>
              <input type="text" id="hospitalAsignado" class="form-control" readonly>
              <small class="text-muted">Este campo se llena automáticamente al seleccionar el médico</small>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Hora de la cita</label>
              <input type="time" id="horaInput" class="form-control" required>
              <div class="invalid-feedback">Selecciona una hora válida.</div>
              <div id="errorHorario" class="error-horario">
                El horario seleccionado está fuera del horario de atención del médico.
              </div>
              <div id="errorDia" class="error-dia">
                El médico no trabaja este día de la semana.
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Correo electrónico</label>
              <input type="email" id="correoInput" class="form-control" required>
              <div class="invalid-feedback">Ingresa un correo válido.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Título de la cita</label>
              <input type="text" id="tituloInput" class="form-control" required>
              <div class="invalid-feedback">Ingresa un título para la cita.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Descripción</label>
              <textarea id="descripcionInput" class="form-control" rows="3" required></textarea>
              <div class="invalid-feedback">Agrega una breve descripción.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Tipo de cita</label>
              <select id="tipoSelect" class="form-select" required>
                <option value="">Selecciona...</option>
                <option>Consulta</option>
                <option>Examen</option>
                <option>Urgencia</option>
              </select>
              <div class="invalid-feedback">Selecciona el tipo de cita.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Confirmar cita</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- SCRIPTS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <script>
    // ========== SISTEMA DE VALIDACIÓN DE HORARIOS Y DÍAS ==========

    // Función para validar horario de cita vs horario del médico
    function validarHorarioCita(medicoId, horaCita) {
        const medicos = JSON.parse(localStorage.getItem("medicos")) || [];
        const medico = medicos.find(m => m.id === medicoId);
        
        if (!medico || !medico.horarioInicio || !medico.horarioFin) {
            return { valido: false, mensaje: "No se pudo obtener el horario del médico" };
        }
        
        // Convertir horarios a minutos para comparación
        const horaCitaMinutos = convertirHoraAMinutos(horaCita);
        const horarioInicioMinutos = convertirHoraAMinutos(medico.horarioInicio);
        const horarioFinMinutos = convertirHoraAMinutos(medico.horarioFin);
        
        if (horaCitaMinutos >= horarioInicioMinutos && horaCitaMinutos <= horarioFinMinutos) {
            return { valido: true, mensaje: "Horario válido" };
        } else {
            return { 
                valido: false, 
                mensaje: `El médico atiende de ${formatearHoraDisplay(medico.horarioInicio)} a ${formatearHoraDisplay(medico.horarioFin)}` 
            };
        }
    }

    // Función para validar día de la cita vs días de trabajo del médico
    function validarDiaCita(medicoId, fechaCita) {
        const medicos = JSON.parse(localStorage.getItem("medicos")) || [];
        const medico = medicos.find(m => m.id === medicoId);
        
        if (!medico || !medico.diasTrabajo || !Array.isArray(medico.diasTrabajo)) {
            return { valido: false, mensaje: "No se pudo obtener los días de trabajo del médico" };
        }
        
        const fecha = new Date(fechaCita);
        const nombreDia = obtenerNombreDia(fecha);
        
        if (medico.diasTrabajo.includes(nombreDia)) {
            return { valido: true, mensaje: "Día válido" };
        } else {
            return { 
                valido: false, 
                mensaje: `El médico no trabaja los ${nombreDia}. Días de trabajo: ${medico.diasTrabajo.join(', ')}` 
            };
        }
    }

    // Función auxiliar para convertir hora a minutos
    function convertirHoraAMinutos(hora) {
        const [horas, minutos] = hora.split(':').map(Number);
        return horas * 60 + minutos;
    }

    // Función para formatear hora para display
    function formatearHoraDisplay(hora) {
        if (!hora) return '';
        const [horas, minutos] = hora.split(':');
        const horaNum = parseInt(horas);
        const ampm = horaNum >= 12 ? 'PM' : 'AM';
        const hora12 = horaNum % 12 || 12;
        return `${hora12}:${minutos} ${ampm}`;
    }

    // Función para obtener nombre del día en español
    function obtenerNombreDia(fecha) {
        const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        return dias[fecha.getDay()];
    }

    // ========== SISTEMA DE SELECCIÓN DE MÉDICOS ==========

    // Función para cargar médicos en el select
    function cargarMedicosEnSelect() {
        const medicoSelect = document.getElementById('medicoSelect');
        const hospitalAsignado = document.getElementById('hospitalAsignado');
        const infoMedico = document.getElementById('infoMedico');
        const nombreMedicoTexto = document.getElementById('nombreMedicoTexto');
        const horarioMedicoTexto = document.getElementById('horarioMedicoTexto');
        const diasMedicoTexto = document.getElementById('diasMedicoTexto');
        
        // Limpiar opciones existentes (excepto la primera)
        while (medicoSelect.options.length > 1) {
            medicoSelect.remove(1);
        }
        
        // Obtener datos
        const medicos = JSON.parse(localStorage.getItem("medicos")) || [];
        const hospitales = JSON.parse(localStorage.getItem("hospitales")) || [];
        
        if (medicos.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No hay médicos disponibles';
            medicoSelect.appendChild(option);
            return;
        }
        
        // Agregar médicos al select
        medicos.forEach(medico => {
            const hospitalesDelMedico = hospitales.filter(hospital => 
                hospital.medicos && hospital.medicos.includes(medico.id)
            );
            
            const option = document.createElement('option');
            option.value = medico.id;
            option.textContent = `${medico.nombre} - ${medico.especialidad}`;
            
            // Guardar datos adicionales en el option
            option.dataset.nombre = medico.nombre;
            option.dataset.especialidad = medico.especialidad;
            option.dataset.hospitales = hospitalesDelMedico.map(h => h.nombre).join(', ');
            option.dataset.telefono = medico.telefono || '';
            option.dataset.horarioInicio = medico.horarioInicio || '';
            option.dataset.horarioFin = medico.horarioFin || '';
            option.dataset.diasTrabajo = medico.diasTrabajo ? medico.diasTrabajo.join(',') : '';
            
            medicoSelect.appendChild(option);
        });
        
        // Agregar event listener para mostrar información del médico
        medicoSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const hospitalesTexto = selectedOption.dataset.hospitales || 'No asignado a hospital';
            hospitalAsignado.value = hospitalesTexto;
            
            // Mostrar u ocultar información del médico
            if (selectedOption.value) {
                const horarioInicio = formatearHoraDisplay(selectedOption.dataset.horarioInicio);
                const horarioFin = formatearHoraDisplay(selectedOption.dataset.horarioFin);
                const diasTrabajo = selectedOption.dataset.diasTrabajo ? selectedOption.dataset.diasTrabajo.split(',').join(', ') : 'No definido';
                
                nombreMedicoTexto.textContent = selectedOption.dataset.nombre;
                horarioMedicoTexto.textContent = `${horarioInicio} a ${horarioFin}`;
                diasMedicoTexto.textContent = diasTrabajo;
                infoMedico.style.display = 'block';
            } else {
                infoMedico.style.display = 'none';
            }
            
            // Actualizar título de la cita
            if (selectedOption.value) {
                const tituloInput = document.getElementById('tituloInput');
                if (!tituloInput.value) {
                    tituloInput.value = `Consulta con ${selectedOption.dataset.nombre}`;
                }
            }
            
            // Validar fecha y hora actual si ya hay valores seleccionados
            const fechaInput = document.getElementById('fechaInput');
            const horaInput = document.getElementById('horaInput');
            
            if (fechaInput.value && selectedOption.value) {
                validarYMostrarErrorDia(selectedOption.value, fechaInput.value);
            }
            
            if (horaInput.value && selectedOption.value) {
                validarYMostrarErrorHorario(selectedOption.value, horaInput.value);
            }
        });
        
        // Validar en tiempo real cuando cambia la hora
        const horaInput = document.getElementById('horaInput');
        horaInput.addEventListener('change', function() {
            const medicoSelect = document.getElementById('medicoSelect');
            const selectedOption = medicoSelect.options[medicoSelect.selectedIndex];
            if (selectedOption.value) {
                validarYMostrarErrorHorario(selectedOption.value, this.value);
            }
        });
    }

    // Función para validar y mostrar error de horario
    function validarYMostrarErrorHorario(medicoId, horaCita) {
        const errorHorario = document.getElementById('errorHorario');
        const validacion = validarHorarioCita(medicoId, horaCita);
        
        if (!validacion.valido) {
            errorHorario.textContent = validacion.mensaje;
            errorHorario.style.display = 'block';
            return false;
        } else {
            errorHorario.style.display = 'none';
            return true;
        }
    }

    // Función para validar y mostrar error de día
    function validarYMostrarErrorDia(medicoId, fechaCita) {
        const errorDia = document.getElementById('errorDia');
        const validacion = validarDiaCita(medicoId, fechaCita);
        
        if (!validacion.valido) {
            errorDia.textContent = validacion.mensaje;
            errorDia.style.display = 'block';
            return false;
        } else {
            errorDia.style.display = 'none';
            return true;
        }
    }

    // ========== CALENDARIO Y GESTIÓN DE CITAS ==========

    document.addEventListener('DOMContentLoaded', function() {
      const calendarEl = document.getElementById('calendar');
      const modalEl = document.getElementById('citaModal');
      const modal = new bootstrap.Modal(modalEl);
      const today = new Date().toISOString().split('T')[0];

      // Utilidades de almacenamiento
      function getCitas(){ return JSON.parse(localStorage.getItem("citas")) || []; }
      function setCitas(citas){ localStorage.setItem("citas", JSON.stringify(citas)); }

      function mapearEventos() {
        return getCitas().map((cita, idx) => ({
          id: cita.id || idx.toString(),
          title: cita.titulo || cita.medico,
          start: `${cita.fecha}T${cita.hora}`,
          extendedProps: {
            nombre: cita.nombre,
            correo: cita.correo,
            medico: cita.medico,
            especialidad: cita.especialidad,
            hospital: cita.hospital,
            descripcion: cita.descripcion,
            tipo: cita.tipo
          }
        }));
      }

      // Inicialización del calendario
      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        selectable: true,
        navLinks: true,
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
          today: 'Hoy',
          month: 'Mes',
          week: 'Semana',
          day: 'Día'
        },
        validRange: { start: today },
        dateClick: function(info) {
          const fechaSel = info.dateStr;
          if (fechaSel < today) {
            alert("No puedes agendar en días anteriores al de hoy.");
            return;
          }
          document.getElementById('fechaInput').value = fechaSel;
          const form = document.getElementById('formCita');
          form.reset();
          form.classList.remove('was-validated');
          
          // Resetear campos específicos
          document.getElementById('hospitalAsignado').value = '';
          document.getElementById('infoMedico').style.display = 'none';
          document.getElementById('errorHorario').style.display = 'none';
          document.getElementById('errorDia').style.display = 'none';
          
          // Cargar médicos y validar día si ya hay médico seleccionado
          cargarMedicosEnSelect();
          
          modal.show();
        },
        events: mapearEventos(),
        eventClick: function(info) {
          const evento = info.event;
          const props = evento.extendedProps;
          const rol = localStorage.getItem("rolUsuario");

          const resumen = `
Cita: ${evento.title}
Fecha: ${evento.startStr}
Paciente: ${props.nombre}
Correo: ${props.correo}
Médico: ${props.medico}
Especialidad: ${props.especialidad || 'No especificada'}
Hospital: ${props.hospital || 'No asignado'}
Tipo: ${props.tipo}
Descripción: ${props.descripcion}
          `.trim();

          if (rol === 'admin') {
            if (confirm(resumen + "\n\n¿Deseas eliminar esta cita?")) {
              const citas = getCitas().filter(c => (c.id || '') !== evento.id);
              setCitas(citas);
              calendar.refetchEvents();
              alert("Cita eliminada correctamente.");
            }
          } else {
            alert(resumen);
          }
        }
      });

      calendar.render();

      // Cargar médicos después de que el calendario se inicialice
      setTimeout(() => {
        cargarMedicosEnSelect();
      }, 100);

      // Guardado de la cita con validaciones completas
      const form = document.getElementById('formCita');
      form.addEventListener('submit', function(e){
        if (!form.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
          form.classList.add('was-validated');
          return;
        }
        e.preventDefault();

        const fecha = document.getElementById('fechaInput').value;
        const nombre = document.getElementById('nombreInput').value.trim();
        const medicoSelect = document.getElementById('medicoSelect');
        const selectedOption = medicoSelect.options[medicoSelect.selectedIndex];
        const hora = document.getElementById('horaInput').value;
        const correo = document.getElementById('correoInput').value.trim();
        const titulo = document.getElementById('tituloInput').value.trim();
        const descripcion = document.getElementById('descripcionInput').value.trim();
        const tipo = document.getElementById('tipoSelect').value;

        const hoy = new Date().toISOString().split('T')[0];
        if (!fecha || fecha < hoy) {
          alert("No puedes agendar en días anteriores al de hoy.");
          return;
        }

        // Validar que se seleccionó un médico
        if (!selectedOption.value) {
          alert("Por favor selecciona un médico.");
          medicoSelect.focus();
          return;
        }

        // Validar día de la cita
        if (!validarYMostrarErrorDia(selectedOption.value, fecha)) {
          alert("Por favor selecciona un día en el que el médico trabaje.");
          return;
        }

        // Validar horario de la cita
        if (!validarYMostrarErrorHorario(selectedOption.value, hora)) {
          alert("Por favor selecciona un horario dentro del horario de atención del médico.");
          document.getElementById('horaInput').focus();
          return;
        }

        const nueva = {
          id: 'c_' + Math.random().toString(36).slice(2, 10),
          nombre,
          medico: selectedOption.dataset.nombre,
          medicoId: selectedOption.value,
          especialidad: selectedOption.dataset.especialidad,
          hospital: selectedOption.dataset.hospitales,
          hora,
          correo,
          titulo,
          descripcion,
          tipo,
          fecha
        };

        const citas = getCitas();
        citas.push(nueva);
        setCitas(citas);
        modal.hide();
        calendar.refetchEvents();
        alert("Cita agendada correctamente.");
      });
    });
  </script>
  <script src="./js/navbar.js"></script>
  <!-- Agregar este script en Agenda.php, después de la inicialización del calendario -->

<script>
// Control de acceso y personalización para Agenda.php
document.addEventListener('DOMContentLoaded', function() {
    const rolUsuario = localStorage.getItem('rolUsuario');
    const medicoActual = localStorage.getItem('usuarioActual');
    
    if (rolUsuario === 'medico') {
        // Personalizar el título para médicos
        const titulo = document.querySelector('h1');
        if (titulo) {
            titulo.textContent = 'Mis Citas Agendadas';
        }
        
        // Agregar mensaje informativo
        const mensajeInfo = document.createElement('div');
        mensajeInfo.className = 'alert alert-info mb-4';
        mensajeInfo.innerHTML = `
            <strong>Vista de médico:</strong> Solo puedes ver y gestionar tus propias citas agendadas.
        `;
        
        const calendarEl = document.getElementById('calendar');
        if (calendarEl) {
            calendarEl.parentNode.insertBefore(mensajeInfo, calendarEl);
        }
        
        // Personalizar el modal para médicos
        const modalTitle = document.querySelector('#citaModal .modal-title');
        if (modalTitle) {
            modalTitle.textContent = 'Agendar Mi Cita';
        }
        
        // Filtrar médicos en el select para que solo aparezca el médico actual
        setTimeout(() => {
            const medicoSelect = document.getElementById('medicoSelect');
            if (medicoSelect && medicoActual) {
                // Buscar la opción que coincide con el médico actual
                for (let i = 0; i < medicoSelect.options.length; i++) {
                    const option = medicoSelect.options[i];
                    if (option.textContent.includes(medicoActual)) {
                        medicoSelect.selectedIndex = i;
                        // Disable el select para que no pueda cambiar de médico
                        medicoSelect.disabled = true;
                        break;
                    }
                }
                
                // Si no encontró coincidencia, seleccionar el primero y deshabilitar
                if (!medicoSelect.disabled) {
                    medicoSelect.selectedIndex = 1; // Saltar la opción "Seleccione..."
                    medicoSelect.disabled = true;
                }
                
                // Agregar tooltip explicativo
                medicoSelect.title = 'Usted está asignado como el médico para esta cita';
            }
        }, 1000);
        
        // Personalizar eventos del calendario para mostrar solo citas del médico
        if (typeof calendar !== 'undefined') {
            // Guardar referencia original de events
            const originalEvents = calendar.getOption('events');
            
            // Filtrar eventos para mostrar solo los del médico actual
            calendar.setOption('events', function(fetchInfo, successCallback, failureCallback) {
                if (typeof originalEvents === 'function') {
                    originalEvents(fetchInfo, function(events) {
                        const eventosFiltrados = events.filter(evento => {
                            const props = evento.extendedProps;
                            return props.medico === medicoActual || 
                                   props.medicoId === medicoActual ||
                                   evento.title.includes(medicoActual);
                        });
                        successCallback(eventosFiltrados);
                    }, failureCallback);
                } else {
                    const eventos = originalEvents || [];
                    const eventosFiltrados = eventos.filter(evento => {
                        const props = evento.extendedProps;
                        return props.medico === medicoActual || 
                               props.medicoId === medicoActual ||
                               evento.title.includes(medicoActual);
                    });
                    successCallback(eventosFiltrados);
                }
            });
            
            // Personalizar el eventClick para médicos
            calendar.setOption('eventClick', function(info) {
                const evento = info.event;
                const props = evento.extendedProps;
                
                // Verificar si la cita pertenece al médico actual
                if (props.medico !== medicoActual && !evento.title.includes(medicoActual)) {
                    alert('Esta cita no pertenece a tu agenda. Solo puedes ver tus propias citas.');
                    return;
                }
                
                const resumen = `
Cita: ${evento.title}
Fecha: ${evento.startStr}
Paciente: ${props.nombre}
Correo: ${props.correo}
Médico: ${props.medico}
Especialidad: ${props.especialidad || 'No especificada'}
Hospital: ${props.hospital || 'No asignado'}
Tipo: ${props.tipo}
Descripción: ${props.descripcion}
                `.trim();

                alert(resumen);
            });
        }
    }
    
    // Para administradores, mostrar todo sin restricciones
    else if (rolUsuario === 'admin') {
        const titulo = document.querySelector('h1');
        if (titulo) {
            titulo.textContent = 'Gestión Completa de Citas';
        }
        
        // Asegurar que el select de médicos esté habilitado
        setTimeout(() => {
            const medicoSelect = document.getElementById('medicoSelect');
            if (medicoSelect) {
                medicoSelect.disabled = false;
            }
        }, 1000);
    }
    
    // Para pacientes, personalizar título
    else if (rolUsuario === 'paciente') {
        const titulo = document.querySelector('h1');
        if (titulo) {
            titulo.textContent = 'Mis Citas Médicas';
        }
    }
});
</script>
<script>
// Función para guardar cita en la base de datos
async function guardarCitaEnBD(citaData) {
    try {
        const formData = new FormData();
        formData.append('nombre', citaData.nombre);
        formData.append('medico', citaData.medico);
        formData.append('medicoId', citaData.medicoId);
        formData.append('especialidad', citaData.especialidad);
        formData.append('hospital', citaData.hospital);
        formData.append('hora', citaData.hora);
        formData.append('correo', citaData.correo);
        formData.append('titulo', citaData.titulo);
        formData.append('descripcion', citaData.descripcion);
        formData.append('tipo', citaData.tipo);
        formData.append('fecha', citaData.fecha);

        const response = await fetch('../controlador/guardar_cita.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.success) {
            console.log('✅ Cita guardada en BD:', result.message);
        } else {
            console.error('❌ Error al guardar en BD:', result.message);
        }
    } catch (error) {
        console.error('❌ Error de conexión:', error);
    }
}

// Modificar el evento submit del formulario para guardar en BD
document.addEventListener('DOMContentLoaded', function() {
    const formCita = document.getElementById('formCita');
    
    if (formCita) {
        // Guardar referencia original
        const originalSubmit = formCita.onsubmit;
        
        formCita.onsubmit = function(e) {
            if (!originalSubmit || originalSubmit.call(this, e) !== false) {
                // Obtener datos del formulario
                const fecha = document.getElementById('fechaInput').value;
                const nombre = document.getElementById('nombreInput').value.trim();
                const medicoSelect = document.getElementById('medicoSelect');
                const selectedOption = medicoSelect.options[medicoSelect.selectedIndex];
                const hora = document.getElementById('horaInput').value;
                const correo = document.getElementById('correoInput').value.trim();
                const titulo = document.getElementById('tituloInput').value.trim();
                const descripcion = document.getElementById('descripcionInput').value.trim();
                const tipo = document.getElementById('tipoSelect').value;

                const citaData = {
                    nombre: nombre,
                    medico: selectedOption.dataset.nombre,
                    medicoId: selectedOption.value,
                    especialidad: selectedOption.dataset.especialidad,
                    hospital: selectedOption.dataset.hospitales,
                    hora: hora,
                    correo: correo,
                    titulo: titulo,
                    descripcion: descripcion,
                    tipo: tipo,
                    fecha: fecha
                };

                // Guardar en BD
                guardarCitaEnBD(citaData);
                
                return true;
            }
        };
    }
});
</script>
</body>
</html>