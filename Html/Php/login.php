<?php
$conexion = mysqli_connect("localhost", "root", "", "redmedica");
  
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';

$sql = "SELECT * FROM administradores WHERE Usuario = '$usuario' AND Contraseña = '$password'";
$resultado = mysqli_query($conexion, $sql);

if (mysqli_num_rows($resultado) > 0) {
    echo "<h1>Bienvenido, $usuario</h1>";
  
   header("Location: ../principal.html");
exit;

} else {
    echo "<h1>Usuario o contraseña incorrectos</h1>";
}

mysqli_close($conexion);
?>
