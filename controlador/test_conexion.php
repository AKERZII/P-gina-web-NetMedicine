<?php
$conexion = mysqli_connect("localhost", "root", "", "redmedica");

if ($conexion) {
    echo "Conexión exitosa a la base de datos<br>";
    
    // Probar consulta
    $result = mysqli_query($conexion, "SELECT COUNT(*) as total FROM usuario");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "✅ Total de usuarios en la base de datos: " . $row['total'];
    } else {
        echo "❌ Error en consulta: " . mysqli_error($conexion);
    }
    
    mysqli_close($conexion);
} else {
    echo "❌ Error de conexión: " . mysqli_connect_error();
}
?>