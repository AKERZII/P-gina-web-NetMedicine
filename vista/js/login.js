// login.js - Sistema completo de login y registro

document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const loginSection = document.getElementById('login-section');
    const registerSection = document.getElementById('register-section');
    const showRegisterBtn = document.getElementById('show-register');
    const showLoginBtn = document.getElementById('show-login');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const mensaje = document.getElementById('mensaje');

    // Alternar entre login y registro
    showRegisterBtn.addEventListener('click', function() {
        loginSection.classList.remove('active');
        registerSection.classList.add('active');
        mensaje.textContent = '';
    });

    showLoginBtn.addEventListener('click', function() {
        registerSection.classList.remove('active');
        loginSection.classList.add('active');
        mensaje.textContent = '';
    });

    // ========== SISTEMA DE LOGIN ==========
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const usuario = document.getElementById('login-user').value.trim();
        const password = document.getElementById('login-pass').value.trim();

        // Validación básica
        if (!usuario || !password) {
            mostrarMensaje('Usuario y contraseña son obligatorios', 'error');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('usuario', usuario);
            formData.append('password', password);

            const res = await fetch('../controlador/login.php', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                mostrarMensaje(data.message, 'success');
                
                // Guardar en localStorage
                localStorage.setItem('usuarioActual', data.usuario);
                localStorage.setItem('rolUsuario', data.rol);

                // Redirigir después de breve delay
                setTimeout(() => {
                    window.location.href = 'principal.html';
                }, 1000);

            } else {
                mostrarMensaje(data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    // ========== SISTEMA DE REGISTRO ==========
   // En la sección de registro del login.js - verificar que esté así:
registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Obtener datos del formulario
    const nombre = document.getElementById('reg-nombre').value.trim();
    const apellido = document.getElementById('reg-apellido').value.trim();
    const correo = document.getElementById('reg-correo').value.trim();
    const telefono = document.getElementById('reg-telefono').value.trim();
    const usuario = document.getElementById('reg-usuario').value.trim();
    const password = document.getElementById('reg-password').value;
    const confirmPassword = document.getElementById('reg-confirm-password').value;
    const rol = document.getElementById('reg-rol').value;

    console.log('Datos a enviar:', { nombre, apellido, correo, telefono, usuario, password, rol });

    // Validaciones
        if (!nombre || !apellido || !correo || !telefono || !usuario || !password || !confirmPassword || !rol) {
            mostrarMensaje('Todos los campos son obligatorios', 'error');
            return;
        }

        if (password.length < 6) {
            mostrarMensaje('La contraseña debe tener al menos 6 caracteres', 'error');
            return;
        }

        if (password !== confirmPassword) {
            mostrarMensaje('Las contraseñas no coinciden', 'error');
            return;
        }

        if (!validarEmail(correo)) {
            mostrarMensaje('Ingresa un correo electrónico válido', 'error');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('apellido', apellido);
            formData.append('correo', correo);
            formData.append('telefono', telefono);
            formData.append('usuario', usuario);
            formData.append('password', password);
            formData.append('rol', rol);
            formData.append('action', 'register');

            const res = await fetch('../controlador/Registro.php', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                mostrarMensaje(data.message, 'success');
                
                // Limpiar formulario
                registerForm.reset();
                
                // Cambiar a login después de 2 segundos
                setTimeout(() => {
                    registerSection.classList.remove('active');
                    loginSection.classList.add('active');
                }, 2000);

            } else {
                mostrarMensaje(data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarMensaje('Error de conexión con el servidor', 'error');
        }
    });

    // Función para validar email
    function validarEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    // Función para mostrar mensajes
    function mostrarMensaje(texto, tipo) {
        mensaje.textContent = texto;
        mensaje.style.color = tipo === 'error' ? '#dc3545' : '#28a745';
        
        // Auto-ocultar después de 5 segundos
        setTimeout(() => {
            if (mensaje.textContent === texto) {
                mensaje.textContent = '';
            }
        }, 5000);
    }

    // Verificar si ya está logueado
    const usuarioActual = localStorage.getItem('usuarioActual');
    if (usuarioActual && window.location.pathname.includes('login.html')) {
        window.location.href = 'principal.html';
    }
});