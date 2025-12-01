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
        }
        .btn-ver:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

<!-- Encabezado -->
<header class="top-header">
    <div class="logo"><img src="./img/Logo.jpg" alt="Logo Red Médica"></div>
    <div class="contacto"><p>Tel: +52 (33) 1234 5678 | ✉ contacto@redmedica.mx</p></div>

    <div class="login" id="loginArea">
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
        <div class="welcome">
            <h1>¡Bienvenido, <?php echo $_SESSION['nombre']; ?>!</h1>
            <p>Has iniciado sesión correctamente como: <?php echo $_SESSION['rol']?></p>
            <a href="../controlador/login.php"  class="btn-login">Cerrar Sesión</a>
        </div>
    <?php else: ?>
        <div class="not-logged">
            <p>No has iniciado sesión.</p>
            <a href="../controlador/login.php"  class="btn-login">Ir al Login</a>
        </div>
    <?php endif; ?>
    </div>
</header>

<script>
    const usuarioActual = localStorage.getItem("usuarioActual");
    if (usuarioActual) {
        document.getElementById("loginArea").innerHTML = `
            <p> Bienvenido, <strong>${usuarioActual}</strong></p>
            <button id="logoutBtn" class="btn-login">Cerrar Sesión</button>
        `;
        document.getElementById("logoutBtn").addEventListener("click", () => {
            localStorage.removeItem("usuarioActual");
            window.location.reload();
        });
    }
</script>

<!-- NAVBAR -->
<nav class="navbar">
    <ul class="menu">
        <li><a href="./Principal.php">Inicio</a></li>
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
    <h2 class="text-center mb-4">Nuestros Médicos</h2>

    <div class="row g-4">

        <!-- Doctor 1 -->
        <div class="col-md-4">
            <div class="card">
                <img src="./img/doctor1.jpg" class="card-img-top" alt="">
                <div class="card-body text-center">
                    <h5 class="card-title">Dr. Juan García</h5>
                    <p class="card-text">Cardiología <br> Clínica Central Guadalajara</p>
                    <a href="./doctor1.html" class="btn-ver">Ver Perfil</a>
                </div>
            </div>
        </div>

        <!-- Doctor 2 -->
        <div class="col-md-4">
            <div class="card">
                <img src="./img/doctor2.jpg" class="card-img-top" alt="">
                <div class="card-body text-center">
                    <h5 class="card-title">Dr. Luis Fernández</h5>
                    <p class="card-text">Neurología <br> Hospital Real del Valle</p>
                    <a href="./doctor2.html" class="btn-ver">Ver Perfil</a>
                </div>
            </div>
        </div>

        <!-- Doctor 3 -->
        <div class="col-md-4">
            <div class="card">
                <img src="./img/doctor3.jpg" class="card-img-top" alt="">
                <div class="card-body text-center">
                    <h5 class="card-title">Dr. Roberto Martínez</h5>
                    <p class="card-text">Pediatría <br> Hospital San Ángel</p>
                    <a href="./doctor3.html" class="btn-ver">Ver Perfil</a>
                </div>
            </div>
        </div>

        <!-- Doctora 1 -->
        <div class="col-md-4">
            <div class="card">
                <img src="./img/doctora1.jpg" class="card-img-top" alt="">
                <div class="card-body text-center">
                    <h5 class="card-title">Dra. Ana Torres</h5>
                    <p class="card-text">Ginecología <br> Clínica Nueva Esperanza</p>
                    <a href="./doctora1.html" class="btn-ver">Ver Perfil</a>
                </div>
            </div>
        </div>

        <!-- Doctora 2 -->
        <div class="col-md-4">
            <div class="card">
                <img src="./img/doctora2.jpg" class="card-img-top" alt="">
                <div class="card-body text-center">
                    <h5 class="card-title">Dra. Sofía Ramírez</h5>
                    <p class="card-text">Dermatología <br> Centro Médico Arboledas</p>
                    <a href="./doctora2.html" class="btn-ver">Ver Perfil</a>
                </div>
            </div>
        </div>

        <!-- Doctora 3 -->
        <div class="col-md-4">
            <div class="card">
                <img src="./img/doctora3.jpg" class="card-img-top" alt="">
                <div class="card-body text-center">
                    <h5 class="card-title">Dra. Mariela Suárez</h5>
                    <p class="card-text">Medicina Interna <br> Hospital Las Américas</p>
                    <a href="./doctora3.html" class="btn-ver">Ver Perfil</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
