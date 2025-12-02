// Variables globales
        let currentDate = new Date();
        let selectedDate = null;
        let selectedTime = null;
        
        
        // Inicializar la aplicación
        document.addEventListener('DOMContentLoaded', function() {
            renderCalendar(currentDate);
            setupEventListeners();
        });
        
        // Configurar event listeners
        function setupEventListeners() {
            // Navegación del calendario
            document.getElementById('prev-month').addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar(currentDate);
            });
            
            document.getElementById('next-month').addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar(currentDate);
            });
            
            // Cambiar fecha desde el formulario
            document.getElementById('change-date').addEventListener('click', () => {
                document.querySelector('.form-container').style.display = 'none';
                document.querySelector('.calendar-container').style.display = 'block';
            });
            
            // Cancelar formulario
            document.getElementById('cancel-btn').addEventListener('click', () => {
                document.querySelector('.form-container').style.display = 'none';
                document.querySelector('.calendar-container').style.display = 'block';
                resetForm();
            });
            
            // Enviar formulario
            document.getElementById('appointment-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Aquí normalmente enviaríamos los datos al servidor
                const formData = {
                    fecha: selectedDate.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
                    paciente: document.getElementById('patient-name').value,
                    email: document.getElementById('email').value,
                    titulo: document.getElementById('appointment-title').value
                };
                
                // Mostrar confirmación
                alert(`Cita agendada exitosamente:\n\nFecha: ${formData.fecha}\nPaciente: ${formData.paciente}\n\nSe ha enviado un correo de confirmación a ${formData.email}`);
                
                // Resetear formulario y volver al calendario
                resetForm();
                document.querySelector('.form-container').style.display = 'none';
                document.querySelector('.calendar-container').style.display = 'block';
            });
        }
        
        // Renderizar calendario
        function renderCalendar(date) {
            const monthYear = document.getElementById('month-year');
            const calendarDays = document.getElementById('calendar-days');
            
            // Establecer mes y año actual
            const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
            monthYear.textContent = `${monthNames[date.getMonth()]} ${date.getFullYear()}`;
            
            // Limpiar calendario
            calendarDays.innerHTML = '';
            
            // Primer día del mes
            const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
            // Último día del mes
            const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
            // Día de la semana del primer día (0 = domingo, 1 = lunes, etc.)
            const firstDayIndex = firstDay.getDay();
            // Día de la semana del último día
            const lastDayIndex = lastDay.getDay();
            // Número de días del mes anterior
            const prevLastDay = new Date(date.getFullYear(), date.getMonth(), 0).getDate();
            
            // Días del mes anterior
            for (let i = firstDayIndex; i > 0; i--) {
                const day = document.createElement('div');
                day.classList.add('empty');
                day.textContent = prevLastDay - i + 1;
                calendarDays.appendChild(day);
            }
            
            // Días del mes actual
            const today = new Date();
            for (let i = 1; i <= lastDay.getDate(); i++) {
                const day = document.createElement('div');
                day.textContent = i;
                
                // Crear fecha para comparación
                const currentDateObj = new Date(date.getFullYear(), date.getMonth(), i);
                
                // Verificar si es hoy
                if (currentDateObj.toDateString() === today.toDateString()) {
                    day.classList.add('today');
                }
                
                // Verificar si es la fecha seleccionada
                if (selectedDate && currentDateObj.toDateString() === selectedDate.toDateString()) {
                    day.classList.add('selected');
                }
                
                // Deshabilitar días pasados
                if (currentDateObj < today) {
                    day.classList.add('past-day');
                } else {
                    // Agregar event listener para días futuros
                    day.addEventListener('click', () => selectDate(currentDateObj));
                }
                
                calendarDays.appendChild(day);
            }
            
            // Días del siguiente mes
            for (let i = lastDayIndex + 1, j = 1; i < 7; i++, j++) {
                const day = document.createElement('div');
                day.classList.add('empty');
                day.textContent = j;
                calendarDays.appendChild(day);
            }
        }
        
        // Seleccionar fecha
        function selectDate(date) {
            selectedDate = date;
            
            // Mostrar formulario y ocultar calendario
            document.querySelector('.calendar-container').style.display = 'none';
            document.querySelector('.form-container').style.display = 'block';
            
            // Actualizar fecha seleccionada en el formulario
            const formattedDate = date.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('selected-date-display').textContent = formattedDate;
            
            // Resetear selección de hora
            selectedTime = null;
            document.querySelectorAll('.time-slot').forEach(s => {
                s.classList.remove('selected');
            });
            document.getElementById('selected-time').value = '';
            
            // Renderizar calendario con la nueva selección
            renderCalendar(currentDate);
        }
        
        // Resetear formulario
        function resetForm() {
            document.getElementById('appointment-form').reset();
            document.getElementById('hospital').value = '';
            selectedTime = null;
            document.querySelectorAll('.time-slot').forEach(s => {
                s.classList.remove('selected');
            });
        }