<?php
session_start();
require_once '../modelo/Conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
// Procesar eliminación de médico si se recibe la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_medico'])) {
    if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador') {
        $id_medico = $_POST['id_medico'] ?? '';
        
        if (!empty($id_medico)) {
            try {
                // Usar soft delete (cambiar campo activo a 0) en lugar de eliminar físicamente
                $stmt = $pdo->prepare("UPDATE medico SET activo = 0 WHERE id_medico = ?");
                $stmt->execute([$id_medico]);
                
                $_SESSION['mensaje'] = "Médico eliminado correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
                
                // Redirigir para evitar reenvío del formulario
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
                
            } catch (PDOException $e) {
                $_SESSION['mensaje'] = "Error al eliminar el médico: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }
        }
    } else {
        $_SESSION['mensaje'] = "No tiene permisos para eliminar médicos.";
        $_SESSION['tipo_mensaje'] = "danger";
    }
}

// Obtener médicos de la base de datos con información de hospital
try {
    $query = "
       SELECT
        m.id_medico,
        m.especialidad, 
        m.horario, 
        h.ubicacion as hospital_direccion, 
        u.nombre as usuario_nombre,
        m.activo
        FROM medico m 
        LEFT JOIN hospital h ON m.id_hospital = h.id_hospital 
        LEFT JOIN usuario u ON m.id_usuario = u.id_usuario 
        WHERE m.activo = 1;
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_medicos = "Error al cargar médicos: " . $e->getMessage();
    $medicos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Médicos | Red Médica</title>

    <link rel="stylesheet" href="./css/Principal.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
            animation: fadeInPage 0.8s ease-in-out;
        }
        @keyframes fadeInPage {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .card img {
            height: 220px;
            object-fit: cover;
        }
        .card {
            border-radius: 12px;
            transition: transform .2s, box-shadow .2s;
            height: 100%;
            position: relative;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 14px rgba(0,0,0,0.15);
        }
        .btn-ver {
            background-color: #007bff;
            border: none;
            border-radius: 30px;
            padding: 7px 18px;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        .btn-ver:hover {
            background-color: #0056b3;
            color: white;
        }
        .especialidad-badge {
            background-color: #e3f2fd;
            color: #1976d2;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            margin: 5px 0;
            display: inline-block;
        }
        .horario-info {
            font-size: 0.9em;
            color: #666;
            margin-top: 10px;
        }
        .horario-info i {
            color: #4CAF50;
            margin-right: 5px;
        }
        .hospital-info {
            font-size: 0.9em;
            color: #555;
            margin-top: 5px;
        }
        .hospital-info i {
            color: #f57c00;
            margin-right: 5px;
        }
        .no-image {
            background-color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #757575;
            font-size: 3em;
            height: 220px;
        }
        .search-container {
            max-width: 500px;
            margin: 0 auto 30px;
        }
        .filter-buttons {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .filter-btn {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            padding: 5px 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .filter-btn.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .filter-btn:hover {
            background-color: #e9ecef;
        }
        .medico-count {
            text-align: center;
            margin-bottom: 20px;
            color: #666;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-delete-medico {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
        }
        .btn-delete-medico:hover {
            background: #dc3545;
            transform: scale(1.1);
        }
        .confirm-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .confirm-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
        }
        .admin-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75em;
            z-index: 10;
        }
    </style>
</head>

<body>

<!-- Modal de confirmación para eliminar médico -->
<div id="confirmModalMedico" class="confirm-modal">
    <div class="confirm-content">
        <h4><i class="fas fa-user-md text-danger me-2"></i>Confirmar Eliminación</h4>
        <p id="confirmMessageMedico">¿Está seguro de que desea eliminar este médico?</p>
        <div class="alert alert-warning" id="warningMessageMedico">
            <i class="fas fa-exclamation-triangle"></i>
            Esta acción no se puede deshacer. El médico será dado de baja del sistema.
        </div>
        <form id="deleteMedicoForm" method="POST" style="display: none;">
            <input type="hidden" name="eliminar_medico" value="1">
            <input type="hidden" id="id_medico_input" name="id_medico" value="">
        </form>
        <div class="mt-4 d-flex justify-content-end gap-2">
            <button class="btn btn-secondary" onclick="cancelDeleteMedico()">Cancelar</button>
            <button class="btn btn-danger" onclick="confirmDeleteMedico()">Eliminar Médico</button>
        </div>
    </div>
</div>

<!-- Encabezado -->
<header class="top-header">
    <div class="logo"><img src="./img/Logo.jpg" alt="Logo Red Médica"></div>
    <div class="contacto"><p>Tel: +52 (33) 1234 5678 | ✉ contacto@redmedica.mx</p></div>

    <div class="login" id="loginArea">
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
        <div class="welcome">
            <h1>¡Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h1>
            <p>Has iniciado sesión correctamente como: <?php echo htmlspecialchars($_SESSION['rol']); ?></p>
            <a href="../controlador/logout.php" class="btn-login">Cerrar Sesión</a>
        </div>
        <?php else: ?>
        <div class="not-logged">
            <p>No has iniciado sesión.</p>
            <a href="../controlador/login.php" class="btn-login">Ir al Login</a>
        </div>
        <?php endif; ?>
    </div>
</header>

<!-- NAVBAR -->
<nav class="navbar">
    <ul class="menu">
        <li><a href="./src/Principal.php">Inicio</a></li>
        <li><a href="./Medicos.php">Médicos</a></li>
        <li><a href="./Agenda.php">Agenda</a></li>
        <li><a href="./Consultas.php">Consultas</a></li>

        <li class="dropdown">
            <a href="#">Servicios ▾</a>
            <ul class="submenu">
                <li><a href="./Consultas.php">Consultas</a></li>
                <li><a href="./Hospitalizacion.php">Hospitalización</a></li>
                <li><a href="./Laboratorio.php">Laboratorio Clínico</a></li>
                <li><a href="./Rehabilitacion.php">Rehabilitación</a></li>
                <li><a href="./SaludMental.php">Salud Mental</a></li>
                <li><a href="./Farmacia.php">Farmacia</a></li>
                <li><a href="./Urgencias.php">Urgencias</a></li>
                <li><a href="./Planificacion.php">Planificación Familiar</a></li>
            </ul>
        </li>
    </ul>
</nav>

<!-- Contenedor principal -->
<div class="container mt-5">
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?php echo $_SESSION['tipo_mensaje'] ?? 'info'; ?> alert-dismissible fade show">
            <?php echo $_SESSION['mensaje']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>
    
    <h2 class="text-center mb-4">Nuestros Médicos Especialistas</h2>
    
    <?php if (isset($error_medicos)): ?>
        <div class="alert alert-warning text-center"><?php echo htmlspecialchars($error_medicos); ?></div>
    <?php endif; ?>
    
    <!-- Barra de búsqueda y acciones -->
    <div class="search-container row">
        <div class="col-md-8">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="searchInput" class="form-control" placeholder="Buscar médico por nombre o especialidad...">
                <button class="btn btn-primary" type="button" id="searchBtn">
                    Buscar
                </button>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="action-buttons d-flex gap-2 justify-content-end">
                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
                    <a href="#" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Agregar Médico
                    </a>
                <?php endif; ?>
                <a href="./Hospitales.php" class="btn btn-info">
                    <i class="fas fa-hospital"></i> Ver Hospitales
                </a>
            </div>
        </div>
    </div>
    
    <!-- Filtros por especialidad -->
    <div class="filter-buttons" id="filterButtons">
        <button class="filter-btn active" data-filter="all">Todos</button>
        <?php
        // Obtener especialidades únicas
        $especialidades = [];
        foreach ($medicos as $medico) {
            if (!empty($medico['especialidad'])) {
                $especialidades[] = $medico['especialidad'];
            }
        }
        $especialidades = array_unique($especialidades);
        sort($especialidades);
        
        foreach ($especialidades as $especialidad):
        ?>
            <button class="filter-btn" data-filter="<?php echo htmlspecialchars(strtolower($especialidad)); ?>">
                <?php echo htmlspecialchars($especialidad); ?>
            </button>
        <?php endforeach; ?>
    </div>
    
    <!-- Contador de médicos -->
    <div class="medico-count" id="medicoCount">
        <i class="fas fa-user-md"></i>
        Mostrando <span id="count"><?php echo count($medicos); ?></span> médico(s) activo(s)
    </div>

    <!-- Grid de médicos -->
    <div class="row g-4" id="medicosGrid">
        <?php if (empty($medicos)): ?>
            <div class="col-12 text-center">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay médicos disponibles en este momento.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($medicos as $medico): ?>
                <div class="col-md-4 col-lg-3 medico-card" 
                     data-especialidad="<?php echo htmlspecialchars(strtolower($medico['especialidad'])); ?>"
                     data-nombre="<?php echo htmlspecialchars(strtolower($medico['usuario_nombre'])); ?>">
                    <div class="card">
                        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
                            <div class="admin-badge">Admin</div>
                            <button class="btn-delete-medico" 
                                    onclick="showDeleteMedicoModal(<?php echo $medico['id_medico']; ?>, '<?php echo addslashes($medico['usuario_nombre']); ?>')"
                                    title="Eliminar médico">
                                <i class="fas fa-trash"></i>
                            </button>
                        <?php endif; ?>
                        
                        <!-- Imagen del médico -->
                        <?php if (!empty($medico['foto'])): ?>
                            <img src="<?php echo htmlspecialchars($medico['foto']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($medico['usuario_nombre']); ?>"
                                 onerror="this.src='./img/doctor1.jpg'">
                        <?php else: ?>
                            <div class="card-img-top no-image">
                                <i class="fas fa-user-md"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($medico['usuario_nombre']); ?></h5>
                            
                            <!-- Especialidad -->
                            <div class="especialidad-badge">
                                <i class="fas fa-stethoscope"></i>
                                <?php echo htmlspecialchars($medico['especialidad']); ?>
                            </div>
                            
                            <!-- Hospital -->
                                <div class="hospital-info">
                                    <i class="fas fa-hospital"></i>
                                    <?php if (!empty($medico['hospital_direccion'])): ?>
                                        <br><small><?php echo htmlspecialchars($medico['hospital_direccion']); ?></small>
                                    <?php endif; ?>
                                </div>
                            
                            <!-- Horario -->
                            <?php if (!empty($medico['horario'])): ?>
                                <div class="horario-info">
                                    <i class="far fa-clock"></i>
                                    <?php echo htmlspecialchars($medico['horario']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Botón ver perfil -->
                            <div class="text-center mt-3">
                                <a href="./perfil-medico.php?id=<?php echo $medico['id_medico']; ?>" 
                                   class="btn-ver">
                                    <i class="fas fa-user-circle"></i> Ver Perfil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Variables para eliminar médico
    let medicoToDelete = null;
    let medicoNameToDelete = '';
    
    // Mostrar modal para eliminar médico
    function showDeleteMedicoModal(id, nombre) {
        medicoToDelete = id;
        medicoNameToDelete = nombre;
        
        const modal = document.getElementById('confirmModalMedico');
        const message = document.getElementById('confirmMessageMedico');
        
        message.textContent = `¿Está seguro de que desea eliminar al médico "${nombre}"?`;
        modal.style.display = 'flex';
    }
    
    // Cancelar eliminación de médico
    function cancelDeleteMedico() {
        document.getElementById('confirmModalMedico').style.display = 'none';
        medicoToDelete = null;
        medicoNameToDelete = '';
    }
    
    // Confirmar eliminación de médico
    function confirmDeleteMedico() {
        if (!medicoToDelete) return;
        
        // Setear valores en el formulario oculto
        document.getElementById('id_medico_input').value = medicoToDelete;
        
        // Enviar formulario
        document.getElementById('deleteMedicoForm').submit();
    }
    
    // Funcionalidad de búsqueda y filtrado
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const filterButtons = document.querySelectorAll('.filter-btn');
        const medicoCards = document.querySelectorAll('.medico-card');
        const countElement = document.getElementById('count');
        
        // Función para filtrar médicos
        function filterMedicos() {
            const searchTerm = searchInput.value.toLowerCase();
            const activeFilter = document.querySelector('.filter-btn.active').dataset.filter;
            
            let visibleCount = 0;
            
            medicoCards.forEach(card => {
                const nombre = card.dataset.nombre;
                const especialidad = card.dataset.especialidad;
                const textoCard = card.textContent.toLowerCase();
                
                // Aplicar filtro de especialidad
                const matchesFilter = activeFilter === 'all' || especialidad.includes(activeFilter);
                
                // Aplicar filtro de búsqueda
                const matchesSearch = searchTerm === '' || 
                                     nombre.includes(searchTerm) || 
                                     especialidad.includes(searchTerm) ||
                                     textoCard.includes(searchTerm);
                
                // Mostrar u ocultar tarjeta
                if (matchesFilter && matchesSearch) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Actualizar contador
            countElement.textContent = visibleCount;
        }
        
        // Evento en botón de búsqueda
        searchBtn.addEventListener('click', filterMedicos);
        
        // Evento en input de búsqueda (buscar al escribir)
        searchInput.addEventListener('keyup', filterMedicos);
        
        // Eventos en botones de filtro
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remover clase active de todos los botones
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Agregar clase active al botón clickeado
                this.classList.add('active');
                // Aplicar filtros
                filterMedicos();
            });
        });
        
        // Inicializar filtros
        filterMedicos();
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const medicoModal = document.getElementById('confirmModalMedico');
            if (event.target == medicoModal) {
                cancelDeleteMedico();
            }
        }
    });
</script>
</body>
</html>