// Establecer fecha actual por defecto y configurar validación
function configurarFechaPrescripcion() {
    const fechaInput = document.getElementById('fechaPrescripcion');
    if (!fechaInput) return;
    
    // Establecer fecha actual
    const hoy = new Date().toISOString().split('T')[0];
    fechaInput.value = hoy;
    
    // Establecer fecha mínima como hoy
    fechaInput.min = hoy;
    
    // Agregar validación en tiempo real
    fechaInput.addEventListener('change', function() {
        validarFechaPrescripcion(this.value);
    });
    
    // Validar también al cargar la página
    validarFechaPrescripcion(hoy);
}

// Validar que la fecha no sea anterior al día actual
function validarFechaPrescripcion(fechaSeleccionada) {
    const fechaInput = document.getElementById('fechaPrescripcion');
    const errorFecha = document.getElementById('errorFecha');
    const hoy = new Date().toISOString().split('T')[0];
    
    if (fechaSeleccionada < hoy) {
        // Fecha inválida
        fechaInput.classList.add('is-invalid');
        errorFecha.style.display = 'block';
        return false;
    } else {
        // Fecha válida
        fechaInput.classList.remove('is-invalid');
        fechaInput.classList.add('is-valid');
        errorFecha.style.display = 'none';
        return true;
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Cargar médicos disponibles
    cargarMedicosDisponibles();
    
    // Configurar fecha de prescripción
    configurarFechaPrescripcion();
});

// Contador para medicamentos
let contadorMedicamentos = 1;

// Función para agregar más medicamentos
function agregarMedicamento() {
    contadorMedicamentos++;
    const container = document.getElementById('medicamentos-container');
    
    const nuevoMedicamento = document.createElement('div');
    nuevoMedicamento.className = 'medicamento-item border p-3 mb-3 rounded';
    nuevoMedicamento.innerHTML = `
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
                    Eliminar
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(nuevoMedicamento);
}

// Función para eliminar medicamento
function eliminarMedicamento(boton) {
    if (contadorMedicamentos > 1) {
        const item = boton.closest('.medicamento-item');
        item.remove();
        contadorMedicamentos--;
    } else {
        alert('Debe haber al menos un medicamento en la receta.');
    }
}

// Función para recolectar datos de medicamentos
function obtenerDatosMedicamentos() {
    const medicamentos = [];
    const items = document.querySelectorAll('.medicamento-item');
    
    items.forEach(item => {
        const medicamento = {
            nombre: item.querySelector('.medicamento-nombre').value,
            cantidad: item.querySelector('.medicamento-cantidad').value,
            frecuencia: item.querySelector('.medicamento-frecuencia').value,
            duracion: item.querySelector('.medicamento-duracion').value,
            instrucciones: item.querySelector('.medicamento-instrucciones').value
        };
        medicamentos.push(medicamento);
    });
    
    return medicamentos;
}

// Previsualizar receta
function previsualizarReceta() {
    const datos = obtenerDatosReceta();
    if (!datos) return;
    
    const preview = document.getElementById('preview-content');
    preview.innerHTML = generarHTMLReceta(datos);
    
    document.getElementById('receta-preview').style.display = 'block';
    document.getElementById('btnGenerarPDF').style.display = 'inline-block';
    
    // Scroll a la previsualización
    document.getElementById('receta-preview').scrollIntoView({ behavior: 'smooth' });
}

// Obtener todos los datos de la receta
function obtenerDatosReceta() {
    const medicoSelect = document.getElementById('medicoPrescribe');
    const medico = medicoSelect.value;
    const cedula = document.getElementById('cedulaProfesional').value.trim();
    const paciente = document.getElementById('nombrePaciente').value.trim();
    const edad = document.getElementById('edadPaciente').value.trim();
    const correo = document.getElementById('correoPaciente').value.trim();
    const instrucciones = document.getElementById('instruccionesAdicionales').value.trim();
    const fecha = document.getElementById('fechaPrescripcion').value;
    const lugar = document.getElementById('lugarExpedicion').value.trim();
    
    // Validar fecha primero
    if (!validarFechaPrescripcion(fecha)) {
        mostrarMensaje('La fecha de prescripción no puede ser anterior al día actual.', 'error');
        return null;
    }
    
    // Validaciones básicas
    if (!medico) {
        mostrarMensaje('Seleccione un médico que prescribe.', 'error');
        return null;
    }
    
    if (!cedula) {
        mostrarMensaje('La cédula profesional es obligatoria.', 'error');
        return null;
    }
    
    if (!paciente || !edad || !correo || !fecha) {
        mostrarMensaje('Completa todos los campos obligatorios.', 'error');
        return null;
    }
    
    const medicamentos = obtenerDatosMedicamentos();
    if (medicamentos.length === 0) {
        mostrarMensaje('Agrega al menos un medicamento.', 'error');
        return null;
    }
    
    return {
        medico,
        cedula,
        paciente,
        edad,
        correo,
        instrucciones,
        fecha,
        lugar,
        medicamentos
    };
}

// Generar HTML para previsualización
function generarHTMLReceta(datos) {
    let medicamentosHTML = '';
    datos.medicamentos.forEach((med, index) => {
        medicamentosHTML += `
            <div class="medicamento-preview mb-3 p-2 border rounded">
                <strong>${index + 1}. ${med.nombre}</strong><br>
                <small><strong>Cantidad:</strong> ${med.cantidad}</small><br>
                <small><strong>Frecuencia:</strong> ${med.frecuencia}</small><br>
                <small><strong>Duración:</strong> ${med.duracion}</small>
                ${med.instrucciones ? `<br><small><strong>Instrucciones:</strong> ${med.instrucciones}</small>` : ''}
            </div>
        `;
    });
    
    return `
        <div class="receta-html">
            <div class="text-center mb-4">
                <h4>Red Médica</h4>
                <h5>Receta Médica</h5>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Médico:</strong> ${datos.medico}<br>
                    <strong>Cédula:</strong> ${datos.cedula}
                </div>
                <div class="col-md-6 text-end">
                    <strong>Fecha:</strong> ${formatearFecha(datos.fecha)}<br>
                    <strong>Lugar:</strong> ${datos.lugar}
                </div>
            </div>
            
            <div class="mb-3">
                <strong>Paciente:</strong> ${datos.paciente}<br>
                <strong>Edad:</strong> ${datos.edad} años<br>
                <strong>Correo:</strong> ${datos.correo}
            </div>
            
            <div class="mb-3">
                <h6>Medicamentos Prescritos:</h6>
                ${medicamentosHTML}
            </div>
            
            ${datos.instrucciones ? `
            <div class="mb-3">
                <h6>Instrucciones Adicionales:</h6>
                <p>${datos.instrucciones}</p>
            </div>
            ` : ''}
            
            <div class="mt-4 pt-3 border-top">
                <div class="text-center">
                    <p><strong>Firma del Médico</strong></p>
                    <p>${datos.medico}</p>
                    <p>Cédula Profesional: ${datos.cedula}</p>
                </div>
            </div>
            
            <div class="mt-3 text-center text-muted">
                <small>Receta generada electrónicamente - Red Médica</small>
            </div>
        </div>
    `;
}

// Formatear fecha
function formatearFecha(fechaISO) {
    const fecha = new Date(fechaISO);
    return fecha.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}


// Función para agregar más medicamentos
function agregarMedicamento() {
    contadorMedicamentos++;
    const container = document.getElementById('medicamentos-container');
    
    const nuevoMedicamento = document.createElement('div');
    nuevoMedicamento.className = 'medicamento-item border p-3 mb-3 rounded';
    nuevoMedicamento.innerHTML = `
        <input type="hidden" name="medicamento_index[]" value="${contadorMedicamentos}">
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Medicamento</label>
                <input type="text" name="medicamento_nombre_${contadorMedicamentos}" class="form-control medicamento-nombre" list="lista-medicamentos" placeholder="Paracetamol" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cantidad</label>
                <input type="text" name="medicamento_cantidad_${contadorMedicamentos}" class="form-control medicamento-cantidad" placeholder="1 tableta" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Frecuencia</label>
                <input type="text" name="medicamento_frecuencia_${contadorMedicamentos}" class="form-control medicamento-frecuencia" placeholder="Cada 8 horas" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Duración</label>
                <input type="text" name="medicamento_duracion_${contadorMedicamentos}" class="form-control medicamento-duracion" placeholder="7 días" required>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <label class="form-label">Instrucciones especiales</label>
                <input type="text" name="medicamento_instrucciones_${contadorMedicamentos}" class="form-control medicamento-instrucciones" placeholder="Tomar después de los alimentos">
                <button type="button" class="btn btn-outline-danger btn-sm mt-2" onclick="eliminarMedicamento(this)">
                    Eliminar
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(nuevoMedicamento);
}

// Configurar fecha actual al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const fechaInput = document.getElementById('fechaPrescripcion');
    if (fechaInput) {
        const hoy = new Date().toISOString().split('T')[0];
        fechaInput.value = hoy;
        fechaInput.min = hoy;
    }
});

// Función para eliminar medicamento
function eliminarMedicamento(boton) {
    if (contadorMedicamentos > 1) {
        const item = boton.closest('.medicamento-item');
        item.remove();
        contadorMedicamentos--;
    } else {
        alert('Debe haber al menos un medicamento en la receta.');
    }
}

