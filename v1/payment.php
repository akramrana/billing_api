<?php

header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, access-control-allow-origin, secret, token");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD');
header('Content-Type: application/json; charset=utf-8');

require_once '../function.php';
require_once '../db.php';
require_once '../controllers/PaymentController.php';

$header = getallheaders();
checkToken($header);

$_page = !empty($_GET['_page']) ? filter_input(INPUT_GET, '_page', FILTER_DEFAULT) : 1;
$_limit = !empty($_GET['_limit']) ? filter_input(INPUT_GET, '_limit', FILTER_DEFAULT) : 20;
$_route = !empty($_GET['_route']) ? filter_input(INPUT_GET, '_route', FILTER_DEFAULT) : 'index';
$_id = !empty($_GET['id']) ? filter_input(INPUT_GET, 'id', FILTER_DEFAULT) : null;

$created_at_like = filter_input(INPUT_GET, 'created_at_like', FILTER_DEFAULT);
$created_at = isset($created_at_like) ? date('Y-m-d', strtotime(trim($created_at_like))) : null;

$likes = [
    'orders.order_number' => filter_input(INPUT_GET, 'order_number_like', FILTER_DEFAULT),
    'businesses.name' => filter_input(INPUT_GET, 'business_name_like', FILTER_DEFAULT),
    'payments.amount' => filter_input(INPUT_GET, 'amount_like', FILTER_DEFAULT),
    'payments.paymode' => filter_input(INPUT_GET, 'paymode_like', FILTER_DEFAULT),
];
$where = [
    'DATE(payments.created_at)' => $created_at,
];

$controller = new PaymentController();

if (isset($_route)) {
    if ($_route == 'index') {
        $results = $controller->list($_page, $_limit, $likes, $where);
        echo json_encode([
            'data' => !empty($results['data']) ? $results['data'] : [],
            'total' => !empty($results['total']) ? $results['total'] : 0,
        ]);
    }
    if ($_route == 'create') {
        $json = file_get_contents('php://input');
        $postData = json_decode($json, true);
        $results = $controller->create($postData);
        echo json_encode($results);
    }
    if ($_route == 'view') {
        $results = $controller->view($_id);
        echo json_encode($results);
    }
    if ($_route == 'update') {
        $json = file_get_contents('php://input');
        $postData = json_decode($json, true);
        $results = $controller->update($postData, $_id);
        echo json_encode($results);
    }
    if ($_route == 'delete') {
        $json = file_get_contents('php://input');
        $results = $controller->delete($_id);
        echo json_encode($results);
    }
}