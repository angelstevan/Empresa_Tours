<?php
require_once 'db.php';
require_once 'models/cliente.php';

header("Content-Type: application/json");

// CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    http_response_code(200);
    exit();
}

$cliente = new Cliente($pdo);

// Obtener método y URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

// Suponiendo estructura tipo: /api/xyz/cliente/5
$resource = $uri[2] ?? null; // "cliente"
$id = $uri[3] ?? null;

// Ruteo manual simple
switch ($resource) {
    case 'cliente':
        require 'controllers/clienteController.php';
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Recurso no encontrado']);
        break;
};

?>