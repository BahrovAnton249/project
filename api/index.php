<?php
require_once 'config.php';
require_once 'UserController.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$controller = new UserController($pdo);

switch ($method . ':' . $action) {
    case 'POST:register':
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($controller->register($data));
        return; 

    case 'POST:login':
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($controller->login($data));
        return; 

    case 'GET:users':
        echo json_encode($controller->getAll());
        return;

    case 'GET:user':
        $id = $_GET['id'] ?? 0;
        echo json_encode($controller->getById($id));
        return;

    case 'PUT:update':
    case 'PATCH:update':
        $id = $_GET['id'] ?? 0;
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($controller->updatePassword($id, $data));
        return;

    case 'DELETE:delete':
        $id = $_GET['id'] ?? 0;
        echo json_encode($controller->delete($id));
        return;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Маршрут не найден']);
        return;
}
?>
