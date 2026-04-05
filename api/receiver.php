<?php
/**
 * RECEPTOR DE DATOS - DERMACOMP API
 * Este archivo recibe el JSON del Bot y lo guarda en la base de datos de InfinityFree.
 */

// 1. Configuración de Seguridad y Errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// --- CAMBIA ESTO CON TUS DATOS DE INFINITYFREE ---
$db_host = 'sql213.infinityfree.com'; // Por ejemplo, sql202.infinityfree.com
$db_user = 'if0_41360021'; // Tu usuario de InfinityFree
$db_pass = 'UFsR1AzTll'; // Tu contraseña de MySQL
$db_name = 'if0_41360021_dermacomp'; // El nombre de tu base de datos
$api_key = 'MI_CLAVE_SECRETA_123'; // Inventa una clave secreta aquí
// -------------------------------------------------

// 2. Conexión a la Base de Datos
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Error de conexión: ' . $conn->connect_error]));
}

// 3. Validar Clave de API
$received_key = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($received_key !== $api_key) {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => 'Clave de API inválida']));
}

// 4. Procesar Datos recibidos (JSON)
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['productos'])) {
    die(json_encode(['status' => 'error', 'message' => 'Datos inválidos o faltantes']));
}

$productos_procesados = 0;

foreach ($data['productos'] as $prod) {
    $nombre = $conn->real_escape_string($prod['nombre']);
    $marca = $conn->real_escape_string($prod['marca']);
    $sku = $conn->real_escape_string($prod['sku']);
    $imagen = $conn->real_escape_string($prod['imagen']);
    $tienda = $conn->real_escape_string($prod['tienda']);
    $precio = (float)$prod['precio'];
    $precio_oferta = isset($prod['precio_oferta']) ? (float)$prod['precio_oferta'] : "NULL";
    $url = $conn->real_escape_string($prod['url']);

    // A. Insertar o Actualizar el producto
    $sql_prod = "INSERT INTO productos (nombre, marca, sku, imagen) 
                 VALUES ('$nombre', '$marca', '$sku', '$imagen')
                 ON DUPLICATE KEY UPDATE nombre='$nombre', marca='$marca', imagen='$imagen'";
    
    if ($conn->query($sql_prod)) {
        // Obtener el ID del producto (ya sea nuevo o existente)
        $id_query = $conn->query("SELECT id FROM productos WHERE sku='$sku'");
        $id_res = $id_query->fetch_assoc();
        $id_producto = $id_res['id'];

        // B. Insertar el nuevo precio en el historial
        $sql_precio = "INSERT INTO precios (id_producto, tienda, precio, precio_oferta, url) 
                       VALUES ($id_producto, '$tienda', $precio, $precio_oferta, '$url')";
        
        if ($conn->query($sql_precio)) {
            $productos_procesados++;
        }
    }
}

// 5. Responder al Bot
echo json_encode([
    'status' => 'success',
    'message' => "Se procesaron $productos_procesados productos correctamente.",
    'timestamp' => date('Y-m-d H:i:s')
]);

$conn->close();
?>
