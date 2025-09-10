<?php
// api/index.php
/*
Api para la gestion de productos
*/
require __DIR__ . '/db.php';
require __DIR__ . '/validators.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/api/#', '', $uri);

function json_out($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    switch ($path) {
        case 'bodegas':
            $stmt = $pdo->query("SELECT id, nombre FROM bodegas ORDER BY nombre ASC");
            json_out($stmt->fetchAll());
            break;

        case 'sucursales':
            $bodega_id = isset($_GET['bodega_id']) ? (int)$_GET['bodega_id'] : 0;
            $stmt = $pdo->prepare("SELECT id, nombre FROM sucursales WHERE bodega_id = :b ORDER BY nombre ASC");
            $stmt->execute([':b' => $bodega_id]);
            json_out($stmt->fetchAll());
            break;

        case 'monedas':
            $stmt = $pdo->query("SELECT id, codigo, nombre FROM monedas ORDER BY nombre ASC");
            json_out($stmt->fetchAll());
            break;

        case 'materiales':
            $stmt = $pdo->query("SELECT id, nombre FROM materiales ORDER BY id ASC");
            json_out($stmt->fetchAll());
            break;

        case 'check_code':
            $codigo = isset($_GET['codigo']) ? trim($_GET['codigo']) : '';
            $stmt = $pdo->prepare("SELECT 1 FROM productos WHERE codigo = :c LIMIT 1");
            $stmt->execute([':c' => $codigo]);
            json_out(['exists' => (bool)$stmt->fetchColumn()]);
            break;

        case 'save_product':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                json_out(['ok' => false, 'error' => 'Método no permitido'], 405);
            }

            // JSON o x-www-form-urlencoded
            $payload = [];
            $ct = $_SERVER['CONTENT_TYPE'] ?? '';
            if (stripos($ct, 'application/json') !== false) {
                $payload = json_decode(file_get_contents('php://input'), true) ?? [];
            } else {
                $payload = $_POST;
            }

            $codigo      = $payload['codigo']      ?? '';
            $nombre      = $payload['nombre']      ?? '';
            $bodega_id   = (int)($payload['bodega_id']   ?? 0);
            $sucursal_id = (int)($payload['sucursal_id'] ?? 0);
            $moneda_id   = (int)($payload['moneda_id']   ?? 0);
            $precio      = $payload['precio']      ?? '';
            $descripcion = $payload['descripcion'] ?? '';
            $materiales  = $payload['materiales']  ?? [];

            // Validaciones
            if ($msg = validate_codigo($codigo)) {
                json_out(['ok' => false, 'error' => $msg], 400);
            }
            if ($msg = validate_nombre($nombre)) {
                json_out(['ok' => false, 'error' => $msg], 400);
            }
            if ($msg = validate_ids_obligatorios($bodega_id, $sucursal_id, $moneda_id)) {
                json_out(['ok' => false, 'error' => $msg], 400);
            }
            if ($msg = validate_precio($precio)) {
                json_out(['ok' => false, 'error' => $msg], 400);
            }
            if ($msg = validate_materiales($materiales)) {
                json_out(['ok' => false, 'error' => $msg], 400);
            }
            if ($msg = validate_descripcion($descripcion)) {
                json_out(['ok' => false, 'error' => $msg], 400);
            }

            // Verificar FK y relación sucursal-bodega
            $query = $pdo->prepare("SELECT 1 FROM bodegas WHERE id = :id");
            $query->execute([':id' => $bodega_id]);
            if (!$query->fetchColumn()) {
                json_out(['ok' => false, 'error' => 'Debe seleccionar una bodega.'], 400);
            }
            // Verificar sucursal pertenece a bodega
            $query = $pdo->prepare("SELECT 1 FROM sucursales WHERE id = :id AND bodega_id = :b");
            $query->execute([':id' => $sucursal_id, ':b' => $bodega_id]);
            if (!$query->fetchColumn()) {
                json_out(['ok' => false, 'error' => 'Debe seleccionar una sucursal para la bodega seleccionada.'], 400);
            }

            // Verificar moneda existe
            $query = $pdo->prepare("SELECT 1 FROM monedas WHERE id = :id");
            $query->execute([':id' => $moneda_id]);
            if (!$query->fetchColumn()) {
                json_out(['ok' => false, 'error' => 'Debe seleccionar una moneda para el producto.'], 400);
            }

            // Validar materiales existen
            $materiales = array_values(array_unique(array_map('intval', (array)$materiales)));
            if (count($materiales) < 2) {
                json_out(['ok' => false, 'error' => 'Debe seleccionar al menos dos materiales para el producto.'], 400);
            }
            $place = implode(',', array_fill(0, count($materiales), '?'));
            $query = $pdo->prepare("SELECT COUNT(*) FROM materiales WHERE id IN ($place)");
            $query->execute($materiales);
            if ((int)$query->fetchColumn() !== count($materiales)) {
                json_out(['ok' => false, 'error' => 'Materiales inválidos.'], 400);
            }

            // Unicidad de código
            $query = $pdo->prepare("SELECT 1 FROM productos WHERE codigo = :c");
            $query->execute([':c' => $codigo]);
            if ($query->fetchColumn()) {
                json_out(['ok' => false, 'error' => 'El código del producto ya está registrado.'], 400);
            }

            // Insert transaccional
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("
                    INSERT INTO productos (codigo, nombre, bodega_id, sucursal_id, moneda_id, precio, descripcion)
                    VALUES (:codigo, :nombre, :bodega_id, :sucursal_id, :moneda_id, :precio, :descripcion)
                    RETURNING id
                ");
                $stmt->execute([
                    ':codigo' => $codigo,
                    ':nombre' => $nombre,
                    ':bodega_id' => $bodega_id,
                    ':sucursal_id' => $sucursal_id,
                    ':moneda_id' => $moneda_id,
                    ':precio' => $precio,
                    ':descripcion' => $descripcion
                ]);
                $product_id = (int)$stmt->fetchColumn();

                $ins = $pdo->prepare("INSERT INTO producto_material (producto_id, material_id) VALUES (:p, :m)");
                foreach ($materiales as $m) {
                    $ins->execute([':p' => $product_id, ':m' => $m]);
                }

                $pdo->commit();
                json_out(['ok' => true, 'id' => $product_id]);
            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                json_out(['ok' => false, 'error' => 'No se pudo guardar el producto.'], 500);
            }
            break;

        default:
            json_out(['ok' => false, 'error' => 'Endpoint no encontrado'], 404);
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    json_out(['ok' => false, 'error' => 'Error: ' . $e->getMessage()], 500);
}
