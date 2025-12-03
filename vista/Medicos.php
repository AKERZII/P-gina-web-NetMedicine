<?php
session_start();
require_once '../modelo/Conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Obtener médicos de la base de datos con información de hospital
try {
    $query = "
        SELECT 
            m.id_medico,
            m.especialidad,
            m.horario,
            u.nombre as usuario_nombre,
            u.correo as usuario_correo
        FROM medico m
        LEFT JOIN usuario u ON m.id_usuario = u.id_usuario
        WHERE m.activo=1
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
    </style>
</head>

<body>

<!-- Encabezado -->
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

<!-- Contenedor principal -->
<div class="container mt-5">
    <h2 class="text-center mb-4">Nuestros Médicos Especialistas</h2>
    
    <?php if (isset($error_medicos)): ?>
        <div class="alert alert-warning text-center"><?php echo htmlspecialchars($error_medicos); ?></div>
    <?php endif; ?>
    
     <!-- Barra de búsqueda mejorada -->
        <div class="search-container">
            <h2>Buscar Médicos</h2>
            <div class="input-group">
                <input type="text" 
                       id="searchInput" 
                       class="form-control search-input" 
                       placeholder="Buscar médico por nombre, especialidad o área...">
                <button class="btn search-btn" type="button" id="searchBtn">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
            
            <div class="buttons-group mt-3">
                   <a href="./FormularioMedicos.php" class="btn-login">
                     <i class="fas fa-user-plus"></i> Agregar médico
                   </a>
                <a href="./Hospitales.php" class="btn-login">
                     <i class="fas fa-hospital"></i> Ver hospitales
                </a>
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
        Mostrando <span id="count"><?php echo count($medicos); ?></span> médico(s)
    </div>

    <!-- Grid de médicos -->
    <div class="row g-4" id="medicosGrid">
        <?php if (empty($medicos)): ?>
            <div class="col-12 text-center">
                <div class="alert alert-info">
                    No hay médicos disponibles en este momento.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($medicos as $medico): ?>
                <div class="col-md-4 col-lg-3 medico-card" 
                     data-especialidad="<?php echo htmlspecialchars(strtolower($medico['especialidad'])); ?>"
                     data-nombre="<?php echo htmlspecialchars(strtolower($medico['usuario_nombre'])); ?>">
                    <div class="card">
                            <div class="card-img-top no-image">
                                <i class="fas fa-user-md"><img src="./img/medico.jpg" alt=""></i>
                            </div>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($medico['usuario_correo']); ?></h5>
                            
                            <!-- Especialidad -->
                            <div class="especialidad-badge">
                                <i class="fas fa-stethoscope"></i>
                                <?php echo htmlspecialchars($medico['especialidad']); ?>
                            </div>
                            
                            
                            <!-- Horario -->
                            <?php if (!empty($medico['horario'])): ?>
                                <div class="horario-info">
                                    <i class="far fa-clock"></i>
                                    <?php echo htmlspecialchars($medico['horario']); ?>
                                </div>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
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
        
        // Si el usuario está logueado desde localStorage (para compatibilidad)
        const usuarioActual = localStorage.getItem("usuarioActual");
        if (usuarioActual && !document.querySelector('.welcome')) {
            document.getElementById("loginArea").innerHTML = `
                <div class="welcome">
                    <p> Bienvenido, <strong>${usuarioActual}</strong></p>
                    <button id="logoutBtn" class="btn-login">Cerrar Sesión</button>
                </div>
            `;
            document.getElementById("logoutBtn").addEventListener("click", () => {
                localStorage.removeItem("usuarioActual");
                window.location.reload();
            });
        }
    });
</script>
</body>
</html>