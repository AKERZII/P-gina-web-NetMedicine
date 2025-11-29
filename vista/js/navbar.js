document.addEventListener('DOMContentLoaded', function() {
    // Obtener la página actual
    const currentPage = window.location.pathname.split('/').pop() || 'Principal.php';
    
    // Buscar y marcar como activo el enlace correspondiente
    const menuLinks = document.querySelectorAll('.navbar .menu a, .navbar .submenu a');
    
    menuLinks.forEach(link => {
        const linkHref = link.getAttribute('href');
        
        // Verificar si este enlace corresponde a la página actual
        if (linkHref === currentPage) {
            link.classList.add('active');
            
            // Si está en un submenú, también marcar el padre del dropdown
            const dropdownParent = link.closest('.submenu')?.parentElement;
            if (dropdownParent) {
                const dropdownToggle = dropdownParent.querySelector('a:first-child');
                if (dropdownToggle) {
                    dropdownToggle.classList.add('active');
                }
            }
        } else {
            link.classList.remove('active');
        }
    });

    // ========== CONTROL DE ACCESO POR ROLES ==========
    const usuarioActual = localStorage.getItem("usuarioActual");
    const rolUsuario = localStorage.getItem("rolUsuario");
    const loginArea = document.getElementById("loginArea");
    
    // Actualizar área de login
    if (usuarioActual && loginArea) {
        loginArea.innerHTML = `
            <p>Bienvenido, <strong>${usuarioActual}</strong> (${rolUsuario})</p>
            <button id="logoutBtn" class="btn-login">Cerrar Sesión</button>
        `;
        
        document.getElementById("logoutBtn").addEventListener("click", () => {
            localStorage.removeItem("usuarioActual");
            localStorage.removeItem("rolUsuario");
            window.location.href = 'Principal.php';
        });

        // Aplicar restricciones según el rol
        aplicarRestriccionesPorRol(rolUsuario);
    }

    // Función para aplicar restricciones según el rol
    function aplicarRestriccionesPorRol(rol) {
        console.log(`Aplicando restricciones para rol: ${rol}`);
        
        if (rol === 'medico') {
            // Ocultar elementos que el médico no puede ver
            const elementosRestringidos = [
                'Medicos.php', // Gestión de médicos
                'Usuarios.php' // Gestión de usuarios
            ];
            
            // Ocultar enlaces del menú
            const menuLinks = document.querySelectorAll('.navbar .menu a, .navbar .submenu a');
            menuLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (elementosRestringidos.includes(href)) {
                    link.style.display = 'none';
                    // También ocultar el elemento li padre si es necesario
                    const liParent = link.closest('li');
                    if (liParent) {
                        liParent.style.display = 'none';
                    }
                }
            });

            // Ocultar botones de administración
            document.querySelectorAll('.btn-admin').forEach(btn => {
                btn.style.display = 'none';
            });

            // Ocultar pestañas de administración en Médicos.php
            const hospitalesTab = document.getElementById('hospitales-tab');
            if (hospitalesTab) {
                hospitalesTab.style.display = 'none';
            }

            // Mostrar solo la pestaña de médicos
            const medicosTab = document.getElementById('medicos-tab');
            if (medicosTab) {
                medicosTab.click(); // Activar pestaña de médicos
            }

        } else if (rol === 'admin') {
            // Admin puede ver todo - mostrar todos los elementos
            document.querySelectorAll('.btn-admin').forEach(btn => {
                btn.style.display = 'inline-block';
            });
        }
        
        // Para pacientes, no se muestran botones de admin
        else if (rol === 'paciente') {
            document.querySelectorAll('.btn-admin').forEach(btn => {
                btn.style.display = 'none';
            });
        }
    }

    // Verificar acceso a páginas restringidas
    verificarAccesoPagina(currentPage, rolUsuario);
});

// Función para verificar acceso a páginas específicas
function verificarAccesoPagina(pagina, rol) {
    const paginasRestringidas = {
        //'Medicos.php': ['paciente'],
        'Usuarios.php': ['admin'],
        'Reportes.php': ['admin']
    };

    if (paginasRestringidas[pagina] && !paginasRestringidas[pagina].includes(rol)) {
        alert('No tienes permisos para acceder a esta página.');
        window.location.href = 'Principal.php';
        return;
    }
}