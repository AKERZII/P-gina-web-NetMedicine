<?php
require_once '../modelo/Conexion.php';

try {
    // Obtener todos los hospitales
    $stmt = $pdo->prepare("SELECT * FROM hospital");
    $stmt->execute();
    $hospitales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener especialidades POR HOSPITAL
    $especialidades_por_hospital = [];
    $medicos_por_hospital = [];
    
    foreach ($hospitales as $hospital) {
        // Especialidades
        $stmt = $pdo->prepare("SELECT DISTINCT especialidad FROM medico WHERE id_hospital = ?");
        $stmt->execute([$hospital['id_hospital']]);
        $especialidades_por_hospital[$hospital['id_hospital']] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Contar médicos
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM medico WHERE id_hospital = ?");
        $stmt->execute([$hospital['id_hospital']]);
        $medicos_por_hospital[$hospital['id_hospital']] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
} catch (PDOException $e) {
    $error_hospitales = "Error al cargar hospitales: " . $e->getMessage();
    $hospitales = [];
    $especialidades_por_hospital = [];
    $medicos_por_hospital = [];
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
        .delete-btn {
            transition: all 0.3s;
        }
        .delete-btn:hover {
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
            z-index: 1000;
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
        
        .search-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .search-title {
            color: #2c3e50;
            font-weight: 600;
            margin: 0;
        }
        
        .search-controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .search-type-toggle {
            display: flex;
            background: white;
            border-radius: 8px;
            padding: 2px;
            border: 1px solid #dee2e6;
            margin-bottom: 15px;
        }
        
        .search-type-btn {
            padding: 8px 20px;
            border: none;
            background: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .search-type-btn.active {
            background: #4c7ea6;
            color: white;
        }
        
        .search-input-group {
            position: relative;
            margin-bottom: 15px;
        }
        
        .search-input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .search-input {
            padding-left: 45px;
            border-radius: 10px;
            border: 2px solid #dee2e6;
            transition: all 0.3s;
            height: 48px;
        }
        
        .search-input:focus {
            border-color: #4c7ea6;;
        }
        
        .filter-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .filter-select {
            flex: 1;
            min-width: 200px;
            border-radius: 10px;
            border: 2px solid #dee2e6;
            height: 48px;
            padding: 0 15px;
        }
        
        .search-btn {
            background: #4c7ea6;;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 30px;
            font-weight: 600;
            transition: all 0.3s;
            height: 48px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .action-btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .action-btn.add {
            background: #4c7ea6;
            color: white;
            border: none;
        }
        
        .action-btn.view {
            background: #0dcaf0;
            color: #000;
            border: none;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            text-decoration: none;
        }
        
        .action-btn.add:hover {
            background: #416b8dff;
            color: white;
            box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3);
        }
        
        .action-btn.view:hover {
            background: #4c7ea6;
            color: #000;
            box-shadow: 0 5px 15px rgba(13, 202, 240, 0.3);
        }
        
        .results-info {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #4c7ea6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .hospital-card {
            transition: all 0.3s;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            height: 100%;
        }
        
        .hospital-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .card-img-top.no-image {
            background: #4c7ea6;
            color: white;
            padding: 25px 0;
        }
        
        .especialidad-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .search-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-controls {
                width: 100%;
            }
            
            .filter-group {
                flex-direction: column;
            }
            
            .filter-select {
                min-width: 100%;
            }
            
            .action-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<!-- Modal de confirmación -->
<div id="confirmModal" class="confirm-modal">
    <div class="confirm-content">
        <h4>Confirmar Eliminación</h4>
        <p id="confirmMessage">¿Está seguro de que desea eliminar este hospital?</p>
        <div class="alert alert-warning" id="warningMessage" style="display:none;">
            <i class="fas fa-exclamation-triangle"></i>
            <span id="warningText"></span>
        </div>
        <div class="mt-4">
            <button class="btn btn-danger" onclick="confirmDelete()">Eliminar</button>
            <button class="btn btn-secondary" onclick="cancelDelete()">Cancelar</button>
        </div>
    </div>
</div>

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

            
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="action-buttons">
                    <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['administrador', 'medico'])): ?>
                        <a href="FormularioMedicos.php" class="action-btn add">
                            <i class="fas fa-user-plus"></i> Agregar Médico
                        </a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
                        <a href="FormularioHospital.php" class="action-btn add" style="background: #4c7ea6;">
                            <i class="fas fa-hospital-alt"></i> Agregar Hospital
                        </a>
                    <?php endif; ?>
                </div>
                
            </div>
        </form>
    </div>

<!-- Contenido principal -->
<div class="container mt-4">
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?php echo $_SESSION['tipo_mensaje'] ?? 'info'; ?> alert-dismissible fade show">
            <?php echo $_SESSION['mensaje']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>
    
    <div class="row">
        <?php if (!empty($hospitales)): ?>
            <?php foreach ($hospitales as $hospital): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100">
                        <div class="card-img-top no-image text-center py-4 bg-light">
                            <i class="fas fa-hospital fa-3x text-primary"></i>
                        </div>
                        
                        <div class="card-body">
                            <h5 class="card-title">
                                Hospital <?php echo htmlspecialchars($hospital['id_hospital']); ?>
                                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
                                    <button class="btn btn-sm btn-outline-danger delete-btn float-end" 
                                            onclick="showDeleteModal(<?php echo $hospital['id_hospital']; ?>, '<?php echo addslashes($hospital['ubicacion']); ?>', <?php echo $medicos_por_hospital[$hospital['id_hospital']] ?? 0; ?>)"
                                            title="Eliminar hospital">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </h5>
                            
                            <div class="especialidad-badge mb-2">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($hospital['ubicacion'] ?? 'No disponible'); ?>
                            </div>
                            
                            <div class="especialidad-badge mb-3">
                                <i class="fas fa-phone"></i>
                                <?php echo htmlspecialchars($hospital['telefono'] ?? 'No disponible'); ?>
                            </div>
                            
                            <div class="especialidad-badge mb-2">
                                <i class="fas fa-user-md"></i>
                                Médicos: <?php echo $medicos_por_hospital[$hospital['id_hospital']] ?? 0; ?>
                            </div>
                            
                            <h6>Especialidades:</h6>
                            <?php 
                            $especialidades = $especialidades_por_hospital[$hospital['id_hospital']] ?? [];
                            if (!empty($especialidades)): 
                            ?>
                                <?php foreach ($especialidades as $especialidad): ?>
                                    <span class="badge bg-secondary mb-1">
                                        <i class="fas fa-stethoscope"></i>
                                        <?php echo htmlspecialchars($especialidad); ?>
                                    </span><br>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">No hay especialidades</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    No hay hospitales registrados en el sistema.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
let hospitalToDelete = null;
let medicosCount = 0;

function showDeleteModal(id, ubicacion, medicos) {
    hospitalToDelete = id;
    medicosCount = medicos;

    // Función para cambiar el tipo de búsqueda
function setSearchType(type) {
    document.getElementById('searchType').value = type;
    const buttons = document.querySelectorAll('.search-type-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    if (type === 'hospital') {
        buttons[0].classList.add('active');
        document.getElementById('searchQuery').placeholder = 'Buscar por nombre, especialidad, médico...';
    } else {
        buttons[1].classList.add('active');
        document.getElementById('searchQuery').placeholder = 'Buscar por nombre de médico, especialidad...';
    }
}

// Función para limpiar filtros
function resetFilters() {
    window.location.href = window.location.pathname;
}
    
    const modal = document.getElementById('confirmModal');
    const message = document.getElementById('confirmMessage');
    const warning = document.getElementById('warningMessage');
    const warningText = document.getElementById('warningText');
    
    message.textContent = `¿Está seguro de que desea eliminar el Hospital ${id} (${ubicacion})?`;
    
    if (medicos > 0) {
        warning.style.display = 'block';
        warningText.textContent = `ADVERTENCIA: Este hospital tiene ${medicos} médico(s) asignado(s). Al eliminar el hospital, también se eliminarán estos médicos.`;
    } else {
        warning.style.display = 'none';
    }
    
    modal.style.display = 'flex';
}

function cancelDelete() {
    document.getElementById('confirmModal').style.display = 'none';
    hospitalToDelete = null;
    medicosCount = 0;
}

// Búsqueda en tiempo real
document.getElementById('searchQuery').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const hospitalCards = document.querySelectorAll('.hospital-card');
    let visibleCount = 0;
    
    hospitalCards.forEach(card => {
        const cardText = card.textContent.toLowerCase();
        const parentDiv = card.closest('.col-md-4, .col-lg-3');
        
        if (cardText.includes(searchTerm) || searchTerm === '') {
            parentDiv.style.display = 'block';
            visibleCount++;
        } else {
            parentDiv.style.display = 'none';
        }
    });
    

function confirmDelete() {
    if (!hospitalToDelete) return;
    
    // Crear formulario para enviar la solicitud
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../controlador/eliminar_hospital.php';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'id_hospital';
    input.value = hospitalToDelete;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>