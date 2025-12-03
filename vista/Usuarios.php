<?php
session_start();
require_once '../modelo/Conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$userRole = $_SESSION['rol'] ?? 'paciente';

// Permitir solo médicos y administradores
if ($userRole !== 'medico' && $userRole !== 'administrador') {
    header('Location: ./soloAdmin.php');
    exit;
}

// Obtener datos de usuarios de la base de datos
try {
    // Consulta para obtener todos los usuarios con información detallada según su rol
    $sql = "SELECT u.*, 
                   p.genero, p.altura, p.peso,
                   m.especialidad, m.horario, m.id_hospital, m.activo as medico_activo,
                   h.ubicacion as hospital_ubicacion,
                   a.telefono as admin_telefono,
                   COUNT(DISTINCT ag.id_agenda) as total_citas,
                   COUNT(DISTINCT r.id_receta) as total_recetas
            FROM usuario u
            LEFT JOIN paciente p ON u.id_usuario = p.id_usuario
            LEFT JOIN medico m ON u.id_usuario = m.id_usuario
            LEFT JOIN administrador a ON u.id_usuario = a.id_usuario
            LEFT JOIN hospital h ON m.id_hospital = h.id_hospital
            LEFT JOIN agenda ag ON u.id_usuario = ag.id_usuario
            LEFT JOIN receta r ON m.id_medico = r.id_medico OR p.id_paciente = r.id_paciente
            GROUP BY u.id_usuario
            ORDER BY u.id_usuario DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener estadísticas
    $sql_estadisticas = "SELECT 
                        COUNT(*) as total_usuarios,
                        SUM(CASE WHEN rol = 'paciente' THEN 1 ELSE 0 END) as total_pacientes,
                        SUM(CASE WHEN rol = 'medico' THEN 1 ELSE 0 END) as total_medicos,
                        SUM(CASE WHEN rol = 'administrador' THEN 1 ELSE 0 END) as total_administradores,
                        SUM(CASE WHEN rol IS NULL OR rol = '' THEN 1 ELSE 0 END) as total_sin_rol
                        FROM usuario";
    
    $stmt_est = $pdo->prepare($sql_estadisticas);
    $stmt_est->execute();
    $estadisticas = $stmt_est->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error al cargar usuarios: " . $e->getMessage();
    $usuarios = [];
    $estadisticas = [
        'total_usuarios' => 0,
        'total_pacientes' => 0,
        'total_medicos' => 0,
        'total_administradores' => 0,
        'total_sin_rol' => 0
    ];
}

// Procesar búsqueda si existe
$busqueda = '';
if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
    $busqueda = $_GET['busqueda'];
    try {
        $sql_busqueda = "SELECT u.*, 
                                p.genero, p.altura, p.peso,
                                m.especialidad, m.horario, m.id_hospital, m.activo as medico_activo,
                                h.ubicacion as hospital_ubicacion,
                                a.telefono as admin_telefono,
                                COUNT(DISTINCT ag.id_agenda) as total_citas,
                                COUNT(DISTINCT r.id_receta) as total_recetas
                         FROM usuario u
                         LEFT JOIN paciente p ON u.id_usuario = p.id_usuario
                         LEFT JOIN medico m ON u.id_usuario = m.id_usuario
                         LEFT JOIN administrador a ON u.id_usuario = a.id_usuario
                         LEFT JOIN hospital h ON m.id_hospital = h.id_hospital
                         LEFT JOIN agenda ag ON u.id_usuario = ag.id_usuario
                         LEFT JOIN receta r ON m.id_medico = r.id_medico OR p.id_paciente = r.id_paciente
                         WHERE u.nombre LIKE :busqueda 
                            OR u.correo LIKE :busqueda 
                            OR u.telefono LIKE :busqueda 
                            OR u.rol LIKE :busqueda
                            OR m.especialidad LIKE :busqueda
                            OR h.ubicacion LIKE :busqueda
                         GROUP BY u.id_usuario
                         ORDER BY u.id_usuario DESC";
        
        $stmt_busqueda = $pdo->prepare($sql_busqueda);
        $like_busqueda = "%$busqueda%";
        $stmt_busqueda->bindParam(':busqueda', $like_busqueda, PDO::PARAM_STR);
        $stmt_busqueda->execute();
        $usuarios = $stmt_busqueda->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $error = "Error en la búsqueda: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios Registrados - Red Médica</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/Principal.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 0 0 20px 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .search-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .search-input {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .search-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .users-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .table th {
            border: none;
            padding: 15px;
            font-weight: 600;
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
            border-color: #f1f3f4;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .role-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .role-paciente {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .role-medico {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .role-administrador {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        
        .status-active {
            color: #2e7d32;
            font-weight: 600;
        }
        
        .status-inactive {
            color: #d32f2f;
            font-weight: 600;
        }
        
        .action-buttons .btn {
            padding: 5px 12px;
            margin: 0 2px;
        }
        
        .export-buttons {
            margin-top: 20px;
        }
        
        .btn-export {
            margin: 0 5px;
        }
        
        .no-results {
            padding: 50px 20px;
            text-align: center;
            color: #6c757d;
        }
        
        .no-results i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        
        .user-details-modal .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .user-details-modal .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
        }
        
        .info-item {
            padding: 10px 0;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .info-label {
            font-weight: 600;
            color: #667eea;
        }
        
        .info-value {
            color: #495057;
        }
        
        @media (max-width: 768px) {
            .dashboard-header h1 {
                font-size: 1.8rem;
            }
            
            .table-responsive {
                border-radius: 10px;
            }
            
            .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }
            
            .action-buttons .btn {
                width: 100%;
                margin: 2px 0;
            }
        }
        
        .filters-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .filter-group {
            margin-bottom: 15px;
        }
        
        .filter-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 8px 15px;
            width: 100%;
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

<!-- Dashboard de Usuarios -->
<div class="container-fluid">
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-users me-3"></i>Usuarios Registrados</h1>
                    <p class="lead">Gestión completa de usuarios del sistema</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="registro.php" class="btn btn-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo $estadisticas['total_usuarios']; ?></div>
                    <div class="stat-label">Usuarios Totales</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon text-success">
                        <i class="fas fa-user-injured"></i>
                    </div>
                    <div class="stat-number"><?php echo $estadisticas['total_pacientes']; ?></div>
                    <div class="stat-label">Pacientes</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon text-warning">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="stat-number"><?php echo $estadisticas['total_medicos']; ?></div>
                    <div class="stat-label">Médicos</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon text-danger">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-number"><?php echo $estadisticas['total_administradores']; ?></div>
                    <div class="stat-label">Administradores</div>
                </div>
            </div>
        </div>

        <!-- Barra de búsqueda y filtros -->
        <div class="search-container">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control search-input" 
                                   name="busqueda" 
                                   placeholder="Buscar por nombre, correo, teléfono o especialidad..."
                                   value="<?php echo htmlspecialchars($busqueda); ?>">
                            <button class="btn search-btn" type="submit">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="filters-container">
                            <div class="filter-group">
                                <select class="form-control filter-select" name="rol" onchange="this.form.submit()">
                                    <option value="">Todos los roles</option>
                                    <option value="paciente" <?php echo (isset($_GET['rol']) && $_GET['rol'] == 'paciente') ? 'selected' : ''; ?>>Pacientes</option>
                                    <option value="medico" <?php echo (isset($_GET['rol']) && $_GET['rol'] == 'medico') ? 'selected' : ''; ?>>Médicos</option>
                                    <option value="administrador" <?php echo (isset($_GET['rol']) && $_GET['rol'] == 'administrador') ? 'selected' : ''; ?>>Administradores</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            
            <?php if (!empty($busqueda)): ?>
                <div class="mt-3">
                    <span class="badge bg-info">
                        Resultados para: "<?php echo htmlspecialchars($busqueda); ?>"
                    </span>
                    <a href="Usuarios.php" class="btn btn-sm btn-outline-secondary ms-2">
                        <i class="fas fa-times"></i> Limpiar búsqueda
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tabla de usuarios -->
        <div class="users-table">
            <?php if (!empty($usuarios)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Información de Contacto</th>
                                <th>Rol</th>
                                <th>Información Adicional</th>
                                <th>Actividad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): 
                                $iniciales = substr($usuario['nombre'] ?? 'U', 0, 2);
                            ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($usuario['id_usuario']); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-3">
                                                <?php echo strtoupper($iniciales); ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($usuario['nombre'] ?? 'Sin nombre'); ?></strong><br>
                                                <small class="text-muted">Registrado</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><i class="fas fa-envelope me-2 text-primary"></i><?php echo htmlspecialchars($usuario['correo']); ?></div>
                                        <div><i class="fas fa-phone me-2 text-success"></i><?php echo htmlspecialchars($usuario['telefono'] ?? 'No disponible'); ?></div>
                                    </td>
                                    <td>
                                        <?php 
                                        $rol = $usuario['rol'] ?? 'sin rol';
                                        $badge_class = '';
                                        switch($rol) {
                                            case 'paciente': $badge_class = 'role-paciente'; break;
                                            case 'medico': $badge_class = 'role-medico'; break;
                                            case 'administrador': $badge_class = 'role-administrador'; break;
                                            default: $badge_class = 'bg-secondary'; break;
                                        }
                                        ?>
                                        <span class="role-badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst($rol); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($usuario['rol'] == 'paciente'): ?>
                                            <div><small>Género: <?php echo htmlspecialchars($usuario['genero'] ?? 'No especificado'); ?></small></div>
                                            <div><small>Peso: <?php echo $usuario['peso'] ?? '0'; ?> kg</small></div>
                                            <div><small>Altura: <?php echo $usuario['altura'] ?? '0'; ?> m</small></div>
                                        <?php elseif ($usuario['rol'] == 'medico'): ?>
                                            <div><small>Especialidad: <?php echo htmlspecialchars($usuario['especialidad'] ?? 'No especificada'); ?></small></div>
                                            <div><small>Hospital: <?php echo htmlspecialchars($usuario['hospital_ubicacion'] ?? 'No asignado'); ?></small></div>
                                            <div><small>Horario: <?php echo htmlspecialchars($usuario['horario'] ?? 'No especificado'); ?></small></div>
                                        <?php elseif ($usuario['rol'] == 'administrador'): ?>
                                            <div><small>Teléfono admin: <?php echo htmlspecialchars($usuario['admin_telefono'] ?? 'No disponible'); ?></small></div>
                                        <?php else: ?>
                                            <small class="text-muted">Sin información adicional</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><small>Citas: <?php echo $usuario['total_citas'] ?? 0; ?></small></div>
                                        <div><small>Recetas: <?php echo $usuario['total_recetas'] ?? 0; ?></small></div>
                                        <div>
                                            <?php if ($usuario['rol'] == 'medico'): ?>
                                                <span class="<?php echo ($usuario['medico_activo'] == 1) ? 'status-active' : 'status-inactive'; ?>">
                                                    <i class="fas fa-circle me-1"></i>
                                                    <?php echo ($usuario['medico_activo'] == 1) ? 'Activo' : 'Inactivo'; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#userModal<?php echo $usuario['id_usuario']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="editar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($_SESSION['rol'] == 'administrador' && $usuario['id_usuario'] != $_SESSION['id_usuario']): ?>
                                                <button class="btn btn-sm btn-danger" 
                                                        onclick="confirmDelete(<?php echo $usuario['id_usuario']; ?>, '<?php echo addslashes($usuario['nombre']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Modal de detalles del usuario -->
                                <div class="modal fade user-details-modal" id="userModal<?php echo $usuario['id_usuario']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-user me-2"></i>
                                                    Detalles del Usuario: <?php echo htmlspecialchars($usuario['nombre']); ?>
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="info-item">
                                                            <span class="info-label">ID:</span>
                                                            <span class="info-value">#<?php echo $usuario['id_usuario']; ?></span>
                                                        </div>
                                                        <div class="info-item">
                                                            <span class="info-label">Nombre:</span>
                                                            <span class="info-value"><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                                                        </div>
                                                        <div class="info-item">
                                                            <span class="info-label">Correo:</span>
                                                            <span class="info-value"><?php echo htmlspecialchars($usuario['correo']); ?></span>
                                                        </div>
                                                        <div class="info-item">
                                                            <span class="info-label">Teléfono:</span>
                                                            <span class="info-value"><?php echo htmlspecialchars($usuario['telefono'] ?? 'No disponible'); ?></span>
                                                        </div>
                                                        <div class="info-item">
                                                            <span class="info-label">Rol:</span>
                                                            <span class="role-badge <?php echo $badge_class; ?>">
                                                                <?php echo ucfirst($usuario['rol']); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <?php if ($usuario['rol'] == 'paciente'): ?>
                                                            <h6>Información del Paciente</h6>
                                                            <div class="info-item">
                                                                <span class="info-label">Género:</span>
                                                                <span class="info-value"><?php echo htmlspecialchars($usuario['genero'] ?? 'No especificado'); ?></span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Altura:</span>
                                                                <span class="info-value"><?php echo $usuario['altura'] ?? '0'; ?> m</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Peso:</span>
                                                                <span class="info-value"><?php echo $usuario['peso'] ?? '0'; ?> kg</span>
                                                            </div>
                                                        <?php elseif ($usuario['rol'] == 'medico'): ?>
                                                            <h6>Información del Médico</h6>
                                                            <div class="info-item">
                                                                <span class="info-label">Especialidad:</span>
                                                                <span class="info-value"><?php echo htmlspecialchars($usuario['especialidad'] ?? 'No especificada'); ?></span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Hospital:</span>
                                                                <span class="info-value"><?php echo htmlspecialchars($usuario['hospital_ubicacion'] ?? 'No asignado'); ?></span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Horario:</span>
                                                                <span class="info-value"><?php echo htmlspecialchars($usuario['horario'] ?? 'No especificado'); ?></span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Estado:</span>
                                                                <span class="<?php echo ($usuario['medico_activo'] == 1) ? 'status-active' : 'status-inactive'; ?>">
                                                                    <?php echo ($usuario['medico_activo'] == 1) ? 'Activo' : 'Inactivo'; ?>
                                                                </span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col-12">
                                                        <h6>Actividad</h6>
                                                        <div class="info-item">
                                                            <span class="info-label">Total de Citas:</span>
                                                            <span class="info-value"><?php echo $usuario['total_citas'] ?? 0; ?></span>
                                                        </div>
                                                        <div class="info-item">
                                                            <span class="info-label">Total de Recetas:</span>
                                                            <span class="info-value"><?php echo $usuario['total_recetas'] ?? 0; ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                <a href="editar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-primary">
                                                    <i class="fas fa-edit me-2"></i>Editar Usuario
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Botones de exportación -->
                <div class="export-buttons p-3 border-top">
                    <div class="text-end">
                        <span class="text-muted me-3">
                            Mostrando <?php echo count($usuarios); ?> de <?php echo $estadisticas['total_usuarios']; ?> usuarios
                        </span>
                        <button class="btn btn-outline-primary btn-export">
                            <i class="fas fa-file-excel me-2"></i>Exportar a Excel
                        </button>
                        <button class="btn btn-outline-success btn-export">
                            <i class="fas fa-file-pdf me-2"></i>Exportar a PDF
                        </button>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-user-slash"></i>
                    <h4>No se encontraron usuarios</h4>
                    <p><?php echo !empty($busqueda) ? 'Prueba con otros términos de búsqueda.' : 'No hay usuarios registrados en el sistema.'; ?></p>
                    <a href="Usuarios.php" class="btn btn-primary mt-3">
                        <i class="fas fa-redo me-2"></i>Ver todos los usuarios
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger mt-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage">¿Está seguro de que desea eliminar este usuario?</p>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Esta acción eliminará toda la información relacionada con el usuario y no se puede deshacer.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let usuarioToDelete = null;

function confirmDelete(id, nombre) {
    usuarioToDelete = id;
    document.getElementById('deleteMessage').innerHTML = 
        `¿Está seguro de que desea eliminar al usuario <strong>${nombre}</strong> (ID: ${id})?`;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (usuarioToDelete) {
        window.location.href = `../controlador/eliminar_usuario.php?id=${usuarioToDelete}`;
    }
});

// Filtrar por rol
document.addEventListener('DOMContentLoaded', function() {
    const rolFilter = document.querySelector('select[name="rol"]');
    if (rolFilter) {
        rolFilter.addEventListener('change', function() {
            this.form.submit();
        });
    }
});

// Función para exportar datos
function exportData(format) {
    alert(`Exportando datos en formato ${format}...`);
    // Aquí iría la lógica real de exportación
}
</script>
</body>
</html>