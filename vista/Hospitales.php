<?php
require_once '../modelo/Conexion.php';
session_start();

try {
    $pdo = Conexion::conectar();
    
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