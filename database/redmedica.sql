-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-11-2025 a las 15:21:29
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `redmedica`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `idAdmin` int(11) NOT NULL,
  `Usuario` text NOT NULL,
  `Contraseña` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`idAdmin`, `Usuario`, `Contraseña`) VALUES
(1, 'kev', '12345678');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agenda`
--

CREATE TABLE `agenda` (
  `IdAgenda` int(11) NOT NULL,
  `Correo` text NOT NULL,
  `Titulo` text NOT NULL,
  `Descripcion` text NOT NULL,
  `Fecha` date NOT NULL,
  `Tipo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `agenda`
--

INSERT INTO `agenda` (`IdAgenda`, `Correo`, `Titulo`, `Descripcion`, `Fecha`, `Tipo`) VALUES
(1, 'kevin@gmail.com', 'Cita de sangre', 'Tiene cita para sacar estudios', '2025-11-14', 'EXAMEN'),
(2, '', '', '', '0000-00-00', ''),
(3, 'kevin@gmail.com', 'Cita de sangre', 'Tiene cita para sacar estudios', '2025-11-14', 'EXAMEN'),
(4, '', '', '', '0000-00-00', ''),
(6, 'kevin@gmail.com', 'Cita con el doctor', 'Rutina medica', '2025-11-16', 'CONSULTA'),
(7, 'kevin@gmail.com', 'Cita con papoi', 'Albetocore', '2025-11-17', 'Urgencia'),
(8, 'kevin@gmail.com', 'Examen de glucosa', 'Examinar la glucosa del paciente', '2025-11-17', 'Examen'),
(9, 'kevin@gmail.com', 'cita psicologo', 'Seguimiento de paciente', '2025-11-17', 'Consulta'),
(10, 'kevin@gmail.com', 'cita psicologo', 'Seguimiento a kevin', '2025-11-21', 'Consulta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hospitales`
--

CREATE TABLE `hospitales` (
  `idHospital` int(11) NOT NULL,
  `idMedico` int(11) NOT NULL,
  `ubicacion` varchar(100) NOT NULL,
  `telefono` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicos`
--

CREATE TABLE `medicos` (
  `IdMedico` int(11) NOT NULL,
  `Nombre` text NOT NULL,
  `Especialidad` text NOT NULL,
  `idHospital` text NOT NULL,
  `Contacto` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `idPaciente` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(50) NOT NULL,
  `edad` int(3) NOT NULL,
  `tipoSangre` varchar(10) NOT NULL,
  `alergias` varchar(100) NOT NULL,
  `discapacidad` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recetas`
--

CREATE TABLE `recetas` (
  `IdReceta` int(11) NOT NULL,
  `correo` text NOT NULL,
  `medicamento` text NOT NULL,
  `cantidad` text NOT NULL,
  `admi` text NOT NULL,
  `periodo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recetas`
--

INSERT INTO `recetas` (`IdReceta`, `correo`, `medicamento`, `cantidad`, `admi`, `periodo`) VALUES
(1, 'kevin@gmail.com', 'Paracetamol', '10', 'Via oral', '15 dias'),
(2, 'kevin@gmail.com', 'Ibuprofeno', '8', 'via oral', 'cada 5 horas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `idServicio` int(11) NOT NULL,
  `idDepartamento` int(11) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `atencion` varchar(50) NOT NULL,
  `nivel` varchar(20) NOT NULL,
  `activo` varchar(30) NOT NULL,
  `costo` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idUsuario` int(11) NOT NULL,
  `Nombre` text NOT NULL,
  `Apellido` text NOT NULL,
  `Telefono` int(11) NOT NULL,
  `Correo` text NOT NULL,
  `Contraseña` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idUsuario`, `Nombre`, `Apellido`, `Telefono`, `Correo`, `Contraseña`) VALUES
(1, 'Kev', 'Escobedo', 2147483647, 'kevinz@gmail.com', '12345678'),
(2, '', '', 0, '', ''),
(3, '', '', 0, '', ''),
(4, 'kevin', 'Escobedo', 33298574, '', ''),
(5, 'kevi', 'escobedo', 33298574, 'kevin@gmail.com', '12345'),
(6, 'Alejandro ', 'Hernández ', 2147483647, 'kevinz331976@gmail.com', '123456789'),
(7, 'Pipza', 'Fideos ', 2147483647, 'fideo@gmail.com', '12345\n'),
(8, 'kevs', 'escobedo', 3326985, 'kevin@gmail.com', '12345'),
(9, '', '', 0, '', ''),
(10, '', '', 0, '', ''),
(11, '', '', 0, '', ''),
(12, '', '', 0, '', ''),
(13, '', '', 0, '', ''),
(14, '', '', 0, '', ''),
(15, '', '', 0, '', ''),
(16, 'luis', 'hernandez', 996585324, 'luis@gmail.com', '12345'),
(17, 'oscar', 'ernan', 111254568, 'oscar@gmail.com', '12345'),
(18, 'kevin2', 'escobedo', 33289685, 'kevinz339@gmail.com', '12345'),
(19, 'chiwis', 'camacho', 332564978, 'chiwis@gmail', '12345'),
(20, '', '', 0, '', ''),
(21, 'juab', 'Ernandez', 332698574, 'juan@gmail.com', '12345'),
(22, 'juab', 'Ernandez', 332698574, 'juan@gmail.com', '12345'),
(23, 'juab', 'Ernandez', 332698574, 'juan@gmail.com', '12345'),
(24, 'juab', 'Ernandez', 332698574, 'juan@gmail.com', '12345'),
(25, 'juan', 'espanta', 33269874, 'juan@gmail.com', '12345'),
(26, '', '', 0, '', ''),
(27, 'juanin', 'porfirio', 33269878, 'juanin@gmail.com', '12345'),
(28, 'juanin', 'porfirio', 33269878, 'juanin@gmail.com', '12345'),
(29, 'juanin', 'porfirio', 33269878, 'juanin@gmail.com', '12345'),
(30, 'Ramon', 'Escobedo', 33298788, 'ramon@gmail.com', '12345'),
(31, 'ian', 'perez', 33258747, 'ian@gmail.com', '12345'),
(32, 'pablo', 'artiaga', 2147483647, 'pablo@gmail.com', '12345'),
(33, 'alejandro', 'escoria', 332564987, 'ale@gmail.com', '123456789');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`idAdmin`);

--
-- Indices de la tabla `agenda`
--
ALTER TABLE `agenda`
  ADD PRIMARY KEY (`IdAgenda`);

--
-- Indices de la tabla `hospitales`
--
ALTER TABLE `hospitales`
  ADD PRIMARY KEY (`idHospital`);

--
-- Indices de la tabla `medicos`
--
ALTER TABLE `medicos`
  ADD PRIMARY KEY (`IdMedico`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`idPaciente`);

--
-- Indices de la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD PRIMARY KEY (`IdReceta`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`idServicio`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idUsuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `idAdmin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `agenda`
--
ALTER TABLE `agenda`
  MODIFY `IdAgenda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `medicos`
--
ALTER TABLE `medicos`
  MODIFY `IdMedico` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recetas`
--
ALTER TABLE `recetas`
  MODIFY `IdReceta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `hospitales`
--
ALTER TABLE `hospitales`
  ADD CONSTRAINT `fk_hospital_medico` FOREIGN KEY (`idMedico`) REFERENCES `medicos` (`idMedico`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
-- Actualizar tabla agenda con nuevos campos
ALTER TABLE agenda 
ADD COLUMN nombre_paciente TEXT AFTER IdAgenda,
ADD COLUMN medico TEXT AFTER nombre_paciente,
ADD COLUMN hora TIME AFTER medico;

-- Actualizar tabla para recetas más completas
ALTER TABLE recetas 
ADD COLUMN paciente_nombre TEXT AFTER IdReceta,
ADD COLUMN medico_nombre TEXT AFTER paciente_nombre,
ADD COLUMN fecha_prescripcion DATE AFTER medico_nombre,
ADD COLUMN instrucciones TEXT AFTER periodo;

-- Agregar tabla de médicos si no existe con estructura completa
CREATE TABLE IF NOT EXISTS medicos_detallados (
    id_medico INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    especialidad VARCHAR(100) NOT NULL,
    telefono VARCHAR(15),
    horario_inicio TIME,
    horario_fin TIME,
    dias_trabajo VARCHAR(100),
    cedula_profesional VARCHAR(20),
    activo BOOLEAN DEFAULT true
);

-- Insertar médicos de ejemplo
INSERT INTO medicos_detallados (nombre, especialidad, telefono, horario_inicio, horario_fin, dias_trabajo, cedula_profesional) VALUES
('Dr. Juan García', 'Cardiología', '+52 (33) 1234 5678', '09:00:00', '14:00:00', 'Lunes,Martes,Miércoles,Jueves,Viernes', 'CARD123456'),
('Dra. María López', 'Pediatría', '+52 (33) 1234 5679', '10:00:00', '16:00:00', 'Lunes,Martes,Miércoles,Jueves,Viernes', 'PEDI789012');