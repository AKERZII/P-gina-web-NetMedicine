<?php
$mysql = new mysqli("localhost", "root", "", "redmedica");

if ($mysql->connect_errno) {
    echo json_encode(["error" => "Error de conexión"]);
    exit;
}

mysqli_set_charset($mysql, "utf8");