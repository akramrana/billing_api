<?php

header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, access-control-allow-origin, secret");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD');
header('Content-Type: application/json; charset=utf-8');

require_once '../function.php';
require_once '../db.php';
require_once '../controllers/DropdownController.php';

$_route = !empty($_GET['_route']) ? filter_input(INPUT_GET, '_route', FILTER_DEFAULT) : 'index';

$controller = new DropdownController();

if (isset($_route)) {
    if ($_route == 'business-list') {
        $results = $controller->businessList();
        echo json_encode($results);
    }
    if ($_route == 'colour-list') {
        $results = $controller->colourList();
        echo json_encode($results);
    }
    if ($_route == 'size-list') {
        $results = $controller->sizeList();
        echo json_encode($results);
    }
    if ($_route == 'status-list') {
        $results = $controller->statusList();
        echo json_encode($results);
    }
    if ($_route == 'paymode-list') {
        $results = $controller->paymodeList();
        echo json_encode($results);
    }
    if ($_route == 'order-list') {
        $results = $controller->orderList();
        echo json_encode($results);
    }
}