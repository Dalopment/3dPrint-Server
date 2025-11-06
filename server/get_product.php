<?php
// Deshabilitar warnings y notices que rompen el JSON
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

// Headers CORS al inicio
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Manejar preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    include_once "conexion.php";
    
    // Verificar que la conexión existe
    if (!isset($conexion)) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    // Consulta para obtener productos con sus variantes
    $sql = "
        SELECT 
            p.id,
            p.nombre,
            p.descripcion,
            p.categoria,
            p.subcategoria,
            p.marca,
            p.is_available,
            p.imagen_url as producto_imagen,
            v.id as variante_id,
            v.nombre_variante as variante_nombre,
            v.color_hex,
            v.precio,
            v.stock,
            v.sku,
            v.imagen_url as variante_imagen,
            v.is_available as variante_disponible
        FROM productos p
        LEFT JOIN variantes_producto v ON p.id = v.producto_id
        WHERE p.is_available = 1
        ORDER BY p.id, v.id
    ";
    
    $resultado = $conexion->query($sql);
    
    if (!$resultado) {
        throw new Exception("Error en la consulta: " . $conexion->error);
    }
    
    // Agrupar productos con sus variantes
    $productos = array();
    
    while ($row = $resultado->fetch_assoc()) {
        $producto_id = $row['id'];
        
        if (!isset($productos[$producto_id])) {
            $productos[$producto_id] = array(
                'id' => (int)$row['id'],
                'nombre' => isset($row['nombre']) ? $row['nombre'] : '',
                'descripcion' => isset($row['descripcion']) ? $row['descripcion'] : '',
                'categoria' => isset($row['categoria']) ? $row['categoria'] : '',
                'subcategoria' => isset($row['subcategoria']) ? $row['subcategoria'] : '',
                'marca' => isset($row['marca']) ? $row['marca'] : '',
                'is_available' => (bool)$row['is_available'],
                'imagen_url' => isset($row['producto_imagen']) ? $row['producto_imagen'] : '',
                'variantes' => array()
            );
        }
        
        if ($row['variante_id']) {
            $productos[$producto_id]['variantes'][] = array(
                'id' => (int)$row['variante_id'],
                'nombre_variante' => isset($row['variante_nombre']) ? $row['variante_nombre'] : '',
                'color_hex' => isset($row['color_hex']) ? $row['color_hex'] : '',
                'precio' => (float)$row['precio'],
                'stock' => (int)$row['stock'],
                'sku' => isset($row['sku']) ? $row['sku'] : '',
                'imagen_url' => isset($row['variante_imagen']) ? $row['variante_imagen'] : '',
                'is_available' => (bool)$row['variante_disponible']
            );
        }
    }
    
    $productos_array = array_values($productos);
    
    http_response_code(200);
    echo json_encode($productos_array);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'error' => true,
        'mensaje' => $e->getMessage()
    ));
}

// Cerrar conexión si existe
if (isset($conexion)) {
    $conexion->close();
}
?>