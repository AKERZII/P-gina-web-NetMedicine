<?php
// controlador/logout.php
session_start();
session_destroy();
header('Location: ../vista/login.php');
exit;
?>