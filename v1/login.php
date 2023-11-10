<?php

header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, access-control-allow-origin, secret, token");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD');
header('Content-Type: application/json; charset=utf-8');

require_once '../function.php';
require_once '../db.php';
require_once '../controllers/LoginController.php';

$header = getallheaders();
checkToken($header,false);

$_route = !empty($_GET['_route']) ? filter_input(INPUT_GET, '_route', FILTER_DEFAULT) : 'index';
$controller = new LoginController();

if (isset($_route)) {
    if ($_route == 'login') {
        $json = file_get_contents('php://input');
        $postData = json_decode($json, true);
        $results = $controller->login($postData);
        echo json_encode($results);
    }
}
