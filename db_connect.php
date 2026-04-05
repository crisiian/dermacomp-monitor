<?php
// DB_CONNECT.PHP - Conexión centralizada a la base de datos

$db_host = 'sql213.infinityfree.com'; 
$db_user = 'if0_41360021'; 
$db_pass = 'UFsR1AzTll'; 
$db_name = 'if0_41360021_dermacomp'; 

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
