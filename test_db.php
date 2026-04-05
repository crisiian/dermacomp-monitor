<?php
// ARCHIVO DE PRUEBA DE CONEXIÓN
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Dermacomp - Diagnóstico de Conexión</h2>";

require_once 'db_connect.php';

if ($conn->connect_error) {
    echo "<p style='color:red'>❌ Error de conexión: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color:green'>✅ ¡Conexión exitosa al servidor MySQL!</p>";
    
    // Probar si las tablas existen
    $check_table = $conn->query("SHOW TABLES LIKE 'productos'");
    if ($check_table->num_rows > 0) {
        echo "<p style='color:green'>✅ La tabla 'productos' existe correctamente.</p>";
    } else {
        echo "<p style='color:orange'>⚠️ La conexión funciona, pero parece que las tablas no están creadas aún.</p>";
    }
}
?>
