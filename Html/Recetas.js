// recetas.js - Sistema completo de recetas con generación de PDF

// Cargar opciones de medicamentos desde localStorage
(function cargarDatalist(){
    const meds = JSON.parse(localStorage.getItem("medicamentos")) || [];
    const dl = document.getElementById("lista-medicamentos");
    if (!dl) return;
    dl.innerHTML = meds.map(m => `<option value="${m.nombre}">${m.presentacion}</option>`).join('');
})();

// Cargar médicos disponibles en el selector
function cargarMedicosDisponibles() {
    const medicos = JSON.parse(localStorage.getItem("medicos")) || [];
    const selectorMedico = document.getElementById("medicoPrescribe");
    
    if (!selectorMedico) return;
    
    // Limpiar opciones existentes (excepto la primera)
    while (selectorMedico.options.length > 1) {
        selectorMedico.remove(1);
    }
    
    // Agregar médicos al selector
    medicos.forEach(medico => {
        const option = document.createElement("option");
        option.value = medico.nombre;
        option.textContent = medico.nombre;
        option.setAttribute('data-cedula', medico.cedula || '');
        selectorMedico.appendChild(option);
    });
    
    // Agregar event listener para actualizar cédula automáticamente
    selectorMedico.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const cedula = selectedOption.getAttribute('data-cedula') || '';
        document.getElementById('cedulaProfesional').value = cedula;
    });
}

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

// GENERAR PDF - Función principal
function generarPDF() {
    const datos = obtenerDatosReceta();
    if (!datos) return;
    
    // Usar jsPDF
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Configuración
    const margin = 20;
    let yPos = margin;
    const pageWidth = doc.internal.pageSize.width;
    const pageHeight = doc.internal.pageSize.height;
    
    // Logo y encabezado
    doc.setFontSize(16);
    doc.setFont('helvetica', 'bold');
    doc.text('Red Médica', pageWidth / 2, yPos, { align: 'center' });
    
    yPos += 8;
    doc.setFontSize(14);
    doc.text('Receta Médica', pageWidth / 2, yPos, { align: 'center' });
    
    yPos += 15;
    
    // Información del médico y fecha
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.text(`Médico: ${datos.medico}`, margin, yPos);
    doc.text(`Cédula: ${datos.cedula}`, margin, yPos + 5);
    
    doc.text(`Fecha: ${formatearFecha(datos.fecha)}`, pageWidth - margin, yPos, { align: 'right' });
    doc.text(`Lugar: ${datos.lugar}`, pageWidth - margin, yPos + 5, { align: 'right' });
    
    yPos += 15;
    
    // Información del paciente
    doc.setFont('helvetica', 'bold');
    doc.text('Datos del Paciente:', margin, yPos);
    doc.setFont('helvetica', 'normal');
    doc.text(`Nombre: ${datos.paciente}`, margin + 5, yPos + 5);
    doc.text(`Edad: ${datos.edad} años`, margin + 5, yPos + 10);
    doc.text(`Correo: ${datos.correo}`, margin + 5, yPos + 15);
    
    yPos += 25;
    
    // Medicamentos prescritos
    doc.setFont('helvetica', 'bold');
    doc.text('Medicamentos Prescritos:', margin, yPos);
    yPos += 8;
    
    datos.medicamentos.forEach((med, index) => {
        // Verificar si necesitamos nueva página
        if (yPos > pageHeight - 50) {
            doc.addPage();
            yPos = margin;
        }
        
        doc.setFont('helvetica', 'bold');
        doc.text(`${index + 1}. ${med.nombre}`, margin, yPos);
        doc.setFont('helvetica', 'normal');
        
        yPos += 5;
        doc.text(`   Cantidad: ${med.cantidad}`, margin + 5, yPos);
        yPos += 4;
        doc.text(`   Frecuencia: ${med.frecuencia}`, margin + 5, yPos);
        yPos += 4;
        doc.text(`   Duración: ${med.duracion}`, margin + 5, yPos);
        
        if (med.instrucciones) {
            yPos += 4;
            doc.text(`   Instrucciones: ${med.instrucciones}`, margin + 5, yPos);
        }
        
        yPos += 8;
    });
    
    // Instrucciones adicionales
    if (datos.instrucciones) {
        yPos += 5;
        if (yPos > pageHeight - 50) {
            doc.addPage();
            yPos = margin;
        }
        
        doc.setFont('helvetica', 'bold');
        doc.text('Instrucciones Adicionales:', margin, yPos);
        doc.setFont('helvetica', 'normal');
        
        // Dividir texto largo en múltiples líneas
        const instruccionesLines = doc.splitTextToSize(datos.instrucciones, pageWidth - 2 * margin);
        yPos += 5;
        instruccionesLines.forEach(line => {
            if (yPos > pageHeight - 30) {
                doc.addPage();
                yPos = margin;
            }
            doc.text(line, margin, yPos);
            yPos += 5;
        });
    }
    
    // Firma
    yPos += 10;
    if (yPos > pageHeight - 50) {
        doc.addPage();
        yPos = margin;
    }
    
    doc.setFont('helvetica', 'bold');
    doc.text('Firma del Médico', pageWidth / 2, yPos, { align: 'center' });
    yPos += 5;
    doc.setFont('helvetica', 'normal');
    doc.text(datos.medico, pageWidth / 2, yPos, { align: 'center' });
    yPos += 4;
    doc.text(`Cédula Profesional: ${datos.cedula}`, pageWidth / 2, yPos, { align: 'center' });
    
    // Pie de página
    yPos += 10;
    doc.setFontSize(8);
    doc.setTextColor(128, 128, 128);
    doc.text('Receta generada electrónicamente - Red Médica', pageWidth / 2, pageHeight - 10, { align: 'center' });
    
    // Guardar PDF
    const nombreArchivo = `Receta_${datos.paciente.replace(/\s+/g, '_')}_${datos.fecha}.pdf`;
    doc.save(nombreArchivo);
    
    mostrarMensaje(`PDF generado y descargado: ${nombreArchivo}`, 'success');
    
    // Guardar también en localStorage
    guardarRecetaEnSistema(datos);
}

// Guardar receta en el sistema
function guardarRecetaEnSistema(datos) {
    const receta = {
        id: 'rec-' + Math.random().toString(36).slice(2,10),
        fecha: new Date().toISOString(),
        ...datos
    };

    const recetas = JSON.parse(localStorage.getItem("recetas")) || [];
    recetas.push(receta);
    localStorage.setItem("recetas", JSON.stringify(recetas));
}

// Manejar envío del formulario
document.getElementById("receta-form")?.addEventListener("submit", function(e){
    e.preventDefault();
    
    const datos = obtenerDatosReceta();
    if (!datos) return;
    
    guardarRecetaEnSistema(datos);
    
    mostrarMensaje(
        "Receta guardada correctamente. El paciente debe adquirir el medicamento en su farmacia de preferencia.",
        'success'
    );
    
    // Mostrar botón de PDF
    document.getElementById('btnGenerarPDF').style.display = 'inline-block';
});

// Función para mostrar mensajes
function mostrarMensaje(texto, tipo) {
    const mensaje = document.getElementById('mensaje');
    mensaje.textContent = texto;
    mensaje.style.color = tipo === 'error' ? '#dc3545' : '#28a745';
    mensaje.className = tipo === 'error' ? 'error' : 'success';
    
    // Auto-ocultar después de 5 segundos
    setTimeout(() => {
        mensaje.textContent = '';
    }, 5000);
}