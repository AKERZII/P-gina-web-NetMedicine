<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios Registrados - Red Médica</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/usuarios.css">
    <link rel="stylesheet" href="./css/Principal.css">
</head>
<body>
<!-- Encabezado -->
<header class="top-header">
    <div class="logo"><img src="./img/Logo.jpg" alt="Logo Red Médica"></div>
    <div class="contacto"><p>Tel: +52 (33) 1234 5678 | ✉ contacto@redmedica.mx</p></div>

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
      <li><a href="./Usuarios.php">Usuarios Registrados</a></li>
    </ul>
  </nav>
    <div class="container">
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-value" id="total-users">0</div>
                <div class="stat-label">Total Usuarios</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="admin-count">0</div>
                <div class="stat-label">Administradores</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="medico-count">0</div>
                <div class="stat-label">Médicos</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="paciente-count">0</div>
                <div class="stat-label">Pacientes</div>
            </div>
        </div>

        <div class="controls">
            <div class="filter-controls">
                <div>
                    <label for="role-filter">Filtrar por rol:</label>
                    <select id="role-filter">
                        <option value="all">Todos los roles</option>
                        <option value="administrador">Administradores</option>
                        <option value="medico">Médicos</option>
                        <option value="paciente">Pacientes</option>
                    </select>
                </div>
                
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-input" placeholder="Buscar por nombre o correo...">
                </div>
            </div>
            
            <button class="refresh-btn" id="refresh-btn">
                <i class="fas fa-sync-alt"></i> Actualizar Datos
            </button>
        </div>
        
        <div class="role-filter">
            <button class="role-filter-btn active" data-role="all">Todos</button>
            <button class="role-filter-btn" data-role="administrador">Administradores</button>
            <button class="role-filter-btn" data-role="medico">Médicos</button>
            <button class="role-filter-btn" data-role="paciente">Pacientes</button>
        </div>

        <div class="users-container">
            <div class="loading" id="loading-indicator">
                <i class="fas fa-spinner"></i> Cargando datos de usuarios...
            </div>
            
            <div id="users-table-container" style="display: none;">
                <table class="users-table" id="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Información Adicional</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                        <!-- Los datos de usuarios se insertarán aquí -->
                    </tbody>
                </table>
            </div>
            
            <div class="no-data" id="no-data-message" style="display: none;">
                <i class="fas fa-user-slash fa-2x"></i>
                <p>No se encontraron usuarios que coincidan con los criterios de búsqueda.</p>
            </div>
        </div>

        <footer>
            <p>Sistema Red Médica &copy; 2025 | Base de datos: redmedica | Tabla: usuario</p>
        </footer>
    </div>

    <script>
        // Datos de ejemplo que simulan la información de la base de datos
        // En un entorno real, estos datos vendrían de una conexión PHP a MySQL
        const usuarios = [
            { id: 1, nombre: "milca celeste", correo: "milcaceleste@gmail.com", telefono: "3321708076", rol: "administrador", detalles: "Usuario con acceso completo al sistema" },
            { id: 3, nombre: "Hibiki Carlo Moreno", correo: "hibiki@gmail.com", telefono: "3321708970", rol: "medico", detalles: "Especialidad: Urología, Hospital: Puebla" },
            { id: 4, nombre: "Carlos López", correo: "carlos@gmail.com", telefono: "555-1111", rol: "medico", detalles: "Especialidad: Cardiología, Hospital: Ciudad de México" },
            { id: 5, nombre: "Ana García", correo: "ana@gmail.com", telefono: "555-2222", rol: "medico", detalles: "Médico general" },
            { id: 6, nombre: "María Rodríguez", correo: "maria@gmail.com", telefono: "555-3333", rol: "paciente", detalles: "Género: Femenino, Altura: 1.65m, Peso: 60.5kg" },
            { id: 7, nombre: "Juan Pérez", correo: "juan@gmail.com", telefono: "555-4444", rol: "paciente", detalles: "Género: Masculino, Altura: 1.75m, Peso: 78.2kg" }
        ];

        // Estado de la aplicación
        let filteredUsers = [...usuarios];
        let currentRoleFilter = 'all';
        let currentSearchTerm = '';

        // Elementos del DOM
        const usersTableBody = document.getElementById('users-table-body');
        const loadingIndicator = document.getElementById('loading-indicator');
        const usersTableContainer = document.getElementById('users-table-container');
        const noDataMessage = document.getElementById('no-data-message');
        const roleFilter = document.getElementById('role-filter');
        const searchInput = document.getElementById('search-input');
        const refreshBtn = document.getElementById('refresh-btn');
        const roleFilterBtns = document.querySelectorAll('.role-filter-btn');
        
        // Elementos de estadísticas
        const totalUsersElement = document.getElementById('total-users');
        const adminCountElement = document.getElementById('admin-count');
        const medicoCountElement = document.getElementById('medico-count');
        const pacienteCountElement = document.getElementById('paciente-count');

        // Función para simular la carga de datos (en un caso real, aquí harías una petición AJAX)
        function loadUsers() {
            // Simular tiempo de carga
            setTimeout(() => {
                updateStatistics();
                renderUsersTable();
                loadingIndicator.style.display = 'none';
                usersTableContainer.style.display = 'block';
            }, 800);
        }

        // Función para actualizar las estadísticas
        function updateStatistics() {
            const totalUsers = usuarios.length;
            const adminCount = usuarios.filter(u => u.rol === 'administrador').length;
            const medicoCount = usuarios.filter(u => u.rol === 'medico').length;
            const pacienteCount = usuarios.filter(u => u.rol === 'paciente').length;
            
            totalUsersElement.textContent = totalUsers;
            adminCountElement.textContent = adminCount;
            medicoCountElement.textContent = medicoCount;
            pacienteCountElement.textContent = pacienteCount;
        }

        // Función para aplicar filtros
        function applyFilters() {
            filteredUsers = usuarios.filter(user => {
                // Filtrar por rol
                if (currentRoleFilter !== 'all' && user.rol !== currentRoleFilter) {
                    return false;
                }
                
                // Filtrar por término de búsqueda
                if (currentSearchTerm && currentSearchTerm.trim() !== '') {
                    const searchTerm = currentSearchTerm.toLowerCase();
                    const userName = user.nombre.toLowerCase();
                    const userEmail = user.correo.toLowerCase();
                    
                    if (!userName.includes(searchTerm) && !userEmail.includes(searchTerm)) {
                        return false;
                    }
                }
                
                return true;
            });
            
            renderUsersTable();
        }

        // Función para renderizar la tabla de usuarios
        function renderUsersTable() {
            usersTableBody.innerHTML = '';
            
            if (filteredUsers.length === 0) {
                usersTableContainer.style.display = 'none';
                noDataMessage.style.display = 'block';
                return;
            }
            
            noDataMessage.style.display = 'none';
            usersTableContainer.style.display = 'block';
            
            filteredUsers.forEach(user => {
                const row = document.createElement('tr');
                
                // Determinar la clase CSS para el rol
                let roleClass = '';
                let roleDisplay = '';
                
                switch(user.rol) {
                    case 'administrador':
                        roleClass = 'role-admin';
                        roleDisplay = 'Administrador';
                        break;
                    case 'medico':
                        roleClass = 'role-medico';
                        roleDisplay = 'Médico';
                        break;
                    case 'paciente':
                        roleClass = 'role-paciente';
                        roleDisplay = 'Paciente';
                        break;
                    default:
                        roleClass = '';
                        roleDisplay = user.rol;
                }
                
                row.innerHTML = `
                    <td>${user.id}</td>
                    <td>
                        <strong>${user.nombre}</strong>
                    </td>
                    <td>${user.correo}</td>
                    <td>${user.telefono}</td>
                    <td><span class="user-role ${roleClass}">${roleDisplay}</span></td>
                    <td>
                        <div class="user-details">${user.detalles}</div>
                    </td>
                `;
                
                usersTableBody.appendChild(row);
            });
        }

        // Función para simular la recarga de datos desde el servidor
        function refreshData() {
            loadingIndicator.style.display = 'block';
            usersTableContainer.style.display = 'none';
            noDataMessage.style.display = 'none';
            
            // Simular una llamada al servidor para obtener datos actualizados
            setTimeout(() => {
                // En un caso real, aquí obtendrías los datos actualizados del servidor
                updateStatistics();
                applyFilters();
                loadingIndicator.style.display = 'none';
                
                // Mostrar notificación de actualización
                const originalText = refreshBtn.innerHTML;
                refreshBtn.innerHTML = '<i class="fas fa-check"></i> Datos Actualizados';
                refreshBtn.style.backgroundColor = '#2e7d32';
                
                setTimeout(() => {
                    refreshBtn.innerHTML = originalText;
                    refreshBtn.style.backgroundColor = '#0d4d8a';
                }, 2000);
            }, 1000);
        }

        // Inicialización de eventos
        function initEventListeners() {
            // Filtro por rol (select)
            roleFilter.addEventListener('change', function() {
                currentRoleFilter = this.value;
                
                // Actualizar botones de filtro de rol
                roleFilterBtns.forEach(btn => {
                    if (btn.dataset.role === currentRoleFilter) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
                
                applyFilters();
            });
            
            // Botones de filtro por rol
            roleFilterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const role = this.dataset.role;
                    currentRoleFilter = role;
                    
                    // Actualizar select
                    roleFilter.value = role;
                    
                    // Actualizar estado de botones
                    roleFilterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    applyFilters();
                });
            });
            
            // Búsqueda por nombre o correo
            searchInput.addEventListener('input', function() {
                currentSearchTerm = this.value;
                applyFilters();
            });
            
            // Botón de actualizar
            refreshBtn.addEventListener('click', refreshData);
            
            // Simular carga inicial de datos
            loadUsers();
        }

        // Inicializar la aplicación cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', initEventListeners);
    </script>
</body>
</html>