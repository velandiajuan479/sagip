<?php
define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));

header_remove();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('X-Content-Type-Options: nosniff');

ini_set('display_errors', 0);
ini_set('html_errors', 0);

require_once ROOT_PATH . '/models/conexion.php';
require_once ROOT_PATH . '/models/msersop.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Método no permitido', 405);
    }

    $term = filter_input(INPUT_GET, 'term', FILTER_SANITIZE_STRING);
    
    if (empty($term)) {
        echo json_encode([]);
        exit;
    }

    $msersop = new Msersop();
    $results = $msersop->buscarUsuarios($term);

    echo json_encode([
        'status' => 'success',
        'data' => is_array($results) ? $results : [],
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}

exit;