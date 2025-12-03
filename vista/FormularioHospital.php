<?php
session_start();
require_once '../modelo/Conexion.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$userRole = $_SESSION['rol'] ?? 'paciente';

// Permitir solo administradores
if ($userRole !== 'administrador') {
    header('Location: ./soloAdmin.php');
    exit;
}

// Función para obtener el próximo ID correcto
function obtenerProximoIdHospital($pdo) {
    try {
        // Obtener el máximo ID actual
        $stmt = $pdo->query("SELECT MAX(id_hospital) as max_id FROM hospital");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['max_id']) {
            return intval($result['max_id']) + 1;
        } else {
            return 1; // Si no hay registros
        }
    } catch (PDOException $e) {
        return 1; // En caso de error, empezar desde 1
    }
}

// Obtener el próximo ID
$proximo_id = obtenerProximoIdHospital($pdo);

// Procesar el formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    
    $errores = [];
    
    // Validaciones
    if (empty($ubicacion)) {
        $errores[] = "La ubicación es obligatoria";
    }
    
    if (empty($telefono)) {
        $errores[] = "El teléfono es obligatorio";
    }
    
    // Si no hay errores, insertar en la base de datos
    if (empty($errores)) {
        try {
            // Insertar con ID específico (sobreescribiendo AUTO_INCREMENT)
            $stmt = $pdo->prepare("INSERT INTO hospital (id_hospital, ubicacion, telefono) VALUES (?, ?, ?)");
            $stmt->execute([$proximo_id, $ubicacion, $telefono]);
            
            // Corregir el AUTO_INCREMENT para el próximo registro
            $next_id = $proximo_id + 1;
            $pdo->exec("ALTER TABLE hospital AUTO_INCREMENT = $next_id");
            
            $_SESSION['mensaje'] = "Hospital registrado exitosamente con ID: " . $proximo_id;
            $_SESSION['tipo_mensaje'] = "success";
            header('Location: Hospitales.php');
            exit;
        } catch (PDOException $e) {
            $errores[] = "Error al registrar el hospital: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Hospital | Red Médica</title>
    <link rel="stylesheet" href="./css/Principal.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- ESTILOS ESPECÍFICOS PARA FORMULARIO DE HOSPITAL --- */
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            animation: fadeInPage 0.8s ease-in-out;
            margin: 0;
            padding: 0;
        }

        @keyframes fadeInPage {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hospital-container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding: 30px;
        }

        .form-title {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 25px;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
        }

        .form-icon {
            color: #3498db;
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .id-info {
            background-color: #e8f4fc;
            border-left: 4px solid #3498db;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .id-info i {
            color: #3498db;
            font-size: 1.2rem;
        }

        .id-label {
            font-weight: 600;
            color: #2c3e50;
        }

        .id-value {
            font-weight: bold;
            color: #e74c3c;
            font-size: 1.2rem;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }

        .form-label i {
            margin-right: 8px;
            color: #4c7ea6;
            width: 20px;
            text-align: center;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #f9fbfd;
        }

        .form-control:focus {
            border-color: #4c7ea6;
            box-shadow: 0 0 0 0.2rem rgba(76, 126, 166, 0.25);
            outline: none;
            background-color: #fff;
        }

        .form-control::placeholder {
            color: #95a5a6;
            font-style: italic;
        }

        .btn-hospital {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            text-align: center;
        }

        .btn-registrar {
            background-color: #3498db;
            color: white;
        }

        .btn-registrar:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }

        .btn-volver {
            background-color: #95a5a6;
            color: white;
        }

        .btn-volver:hover {
            background-color: #7f8c8d;
            transform: translateY(-2px);
        }

        .button-group {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .alert-hospital {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: none;
        }

        .alert-danger-hospital {
            background-color: #fdeaea;
            color: #c62828;
            border-left: 4px solid #c62828;
        }

        .alert-danger-hospital h5 {
            margin-top: 0;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger-hospital ul {
            margin-bottom: 0;
            padding-left: 20px;
        }

        .alert-danger-hospital li {
            margin-bottom: 5px;
        }

        .alert-danger-hospital li:last-child {
            margin-bottom: 0;
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.875em;
            margin-top: 5px;
            min-height: 20px;
        }

        .is-invalid {
            border-color: #e74c3c !important;
            background-color: #fdeaea !important;
        }

        .is-valid {
            border-color: #27ae60 !important;
            background-color: #eaf7ea !important;
        }

        @media (max-width: 768px) {
            .hospital-container {
                margin: 20px;
                padding: 20px;
            }
            
            .button-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-hospital {
                width: 100%;
            }
            
            .form-title {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .hospital-container {
                margin: 10px;
                padding: 15px;
            }
            
            .form-control {
                padding: 10px 12px;
                font-size: 14px;
            }
            
            .btn-hospital {
                padding: 10px 20px;
                font-size: 14px;
            }
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

    <!-- Navegación -->
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

    <!-- Formulario de registro de hospital -->
    <div class="hospital-container">
        <h2 class="form-title">
            <i class="fas fa-hospital form-icon"></i> Registrar Nuevo Hospital
        </h2>
        
        <?php if (!empty($errores)): ?>
            <div class="alert-hospital alert-danger-hospital">
                <h5><i class="fas fa-exclamation-triangle"></i> Errores:</h5>
                <ul class="mb-0">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Información del ID automático -->
        <div class="id-info">
            <i class="fas fa-info-circle"></i>
            <div>
                <span class="id-label">Próximo ID que se asignará:</span>
                <span class="id-value">Hospital <?php echo htmlspecialchars($proximo_id); ?></span>
                <p style="margin: 5px 0 0 0; font-size: 0.9em; color: #666;">
                    El ID se asignará automáticamente siguiendo la secuencia numérica.
                </p>
            </div>
        </div>
        
        <form method="POST" action="" id="formHospital" novalidate>
            <!-- Ubicación -->
            <div class="form-group">
                <label for="ubicacion" class="form-label">
                    <i class="fas fa-map-marker-alt"></i> Ubicación *
                </label>
                <input type="text" 
                       class="form-control" 
                       id="ubicacion" 
                       name="ubicacion"
                       value="<?php echo htmlspecialchars($_POST['ubicacion'] ?? ''); ?>"
                       placeholder="Ej: Av. Principal #123, Ciudad, Estado"
                       required
                       maxlength="200">
                <div class="error-message" id="errorUbicacion"></div>
            </div>
            
            <!-- Teléfono -->
            <div class="form-group">
                <label for="telefono" class="form-label">
                    <i class="fas fa-phone"></i> Teléfono *
                </label>
                <input type="tel" 
                       class="form-control" 
                       id="telefono" 
                       name="telefono"
                       value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>"
                       placeholder="Ej: +52 (33) 1234 5678"
                       required
                       maxlength="20">
                <div class="error-message" id="errorTelefono"></div>
            </div>
            
            <!-- Botones -->
            <div class="button-group">
                <a href="Hospitales.php" class="btn-hospital btn-volver">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button type="submit" class="btn-hospital btn-registrar">
                    <i class="fas fa-save"></i> Registrar Hospital
                </button>
            </div>
        </form>
    </div>

    <!-- Script de validación -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formHospital');
            const ubicacion = document.getElementById('ubicacion');
            const telefono = document.getElementById('telefono');
            
            // Validación en tiempo real
            ubicacion.addEventListener('blur', function() {
                const errorUbicacion = document.getElementById('errorUbicacion');
                if (this.value.trim() === '') {
                    errorUbicacion.textContent = 'La ubicación es obligatoria';
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else {
                    errorUbicacion.textContent = '';
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
            
            telefono.addEventListener('blur', function() {
                const errorTelefono = document.getElementById('errorTelefono');
                const telefonoPattern = /^[\+\d\s\(\)\-]+$/;
                
                if (this.value.trim() === '') {
                    errorTelefono.textContent = 'El teléfono es obligatorio';
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else if (!telefonoPattern.test(this.value)) {
                    errorTelefono.textContent = 'Formato de teléfono inválido';
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else {
                    errorTelefono.textContent = '';
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
            
            // Validación al enviar el formulario
            form.addEventListener('submit', function(e) {
                let valid = true;
                
                // Validar ubicación
                if (ubicacion.value.trim() === '') {
                    document.getElementById('errorUbicacion').textContent = 'La ubicación es obligatoria';
                    ubicacion.classList.add('is-invalid');
                    ubicacion.classList.remove('is-valid');
                    valid = false;
                }
                
                // Validar teléfono
                const telefonoPattern = /^[\+\d\s\(\)\-]+$/;
                if (telefono.value.trim() === '') {
                    document.getElementById('errorTelefono').textContent = 'El teléfono es obligatorio';
                    telefono.classList.add('is-invalid');
                    telefono.classList.remove('is-valid');
                    valid = false;
                } else if (!telefonoPattern.test(telefono.value)) {
                    document.getElementById('errorTelefono').textContent = 'Formato de teléfono inválido';
                    telefono.classList.add('is-invalid');
                    telefono.classList.remove('is-valid');
                    valid = false;
                }
                
                if (!valid) {
                    e.preventDefault();
                    // Desplazarse al primer error
                    const firstError = document.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            });
            
            // Limpiar validación al escribir
            const inputs = [ubicacion, telefono];
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid')) {
                        const errorId = this.id === 'ubicacion' ? 'errorUbicacion' : 'errorTelefono';
                        document.getElementById(errorId).textContent = '';
                        this.classList.remove('is-invalid');
                    }
                });
            });
        });
    </script>
</body>
</html>