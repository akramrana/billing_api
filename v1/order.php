<?php

header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, access-control-allow-origin, secret");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD');
header('Content-Type: application/json; charset=utf-8');

require_once '../function.php';
require_once '../db.php';
require_once '../controllers/OrderController.php';

$_route = !empty($_GET['_route']) ? filter_input(INPUT_GET, '_route', FILTER_DEFAULT) : 'index';
$_page = !empty($_GET['_page']) ? filter_input(INPUT_GET, '_page', FILTER_DEFAULT) : 1;
$_limit = !empty($_GET['_limit']) ? filter_input(INPUT_GET, '_limit', FILTER_DEFAULT) : 20;
$_id = !empty($_GET['id']) ? filter_input(INPUT_GET, 'id', FILTER_DEFAULT) : null;

$likes = [
    'order_number' => filter_input(INPUT_GET, 'order_number_like', FILTER_DEFAULT),
];

$controller = new OrderController();

if (isset($_route)) {
    if ($_route == 'next-order-number') {
        $results = $controller->nextOrderNumber();
        echo json_encode($results);
    }
    if ($_route == 'create') {
        $json = file_get_contents('php://input');
        $postData = json_decode($json, true);
        $results = $controller->create($postData);
        echo json_encode($results);
    }
    if ($_route == 'index') {
        $results = $controller->list($_page, $_limit, $likes);
        echo json_encode([
            'data' => !empty($results['data']) ? $results['data'] : [],
            'total' => !empty($results['total']) ? $results['total'] : 0,
        ]);
    }
}