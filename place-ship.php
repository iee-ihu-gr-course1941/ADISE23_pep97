<?php

require_once "lib/db_connect.php";
require_once "battleship.php";

session_start();
header('Content-type: application/json');
$response = [
    'status' => 'error',
    'data' => '',
];

if (!array_key_exists('user', $_SESSION)) {
    $response['data'] = 'Not authenticated';
} else if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $response['status'] = 'error';
    $response['data'] = 'Not supported method';
} else {
    $request_data = json_decode(file_get_contents('php://input'), true);

    if (!array_key_exists('ship_type', $request_data)) {
        $response['status'] = 'error';
        $response['data'] = "Param 'ship_type' not found";
    } else if (!array_key_exists('x', $request_data)) {
        $response['status'] = 'error';
        $response['data'] = "Param 'x' not found";
    } else if (!array_key_exists('y', $request_data)) {
        $response['status'] = 'error';
        $response['data'] = "Param 'y' not found";
    } else if (!array_key_exists('orientation', $request_data)) {
        $response['status'] = 'error';
        $response['data'] = "Param 'orientation' not found";
    } else if ($request_data['orientation'] != 'horizontal' && $request_data['orientation'] != 'vertical') {
        $response['status'] = 'error';
        $response['data'] = "Param 'orientation' can be only 'horizontal' or 'vertical'";
    } else if ($request_data['x'] < 0  || $request_data['x'] > 10) {
        $response['status'] = 'error';
        $response['data'] = "Param 'x' is out of range 0-10";
    } else if ($request_data['y'] < 0  || $request_data['y'] > 10) {
        $response['status'] = 'error';
        $response['data'] = "Param 'y' is out of range 0-10";
    } else if ($request_data['ship_type'] != 'carrier' && $request_data['ship_type'] != 'battleship' && $request_data['ship_type'] != 'cruiser' && $request_data['ship_type'] != 'submarine' && $request_data['ship_type'] != 'destroyer') {
        $response['status'] = 'error';
        $response['data'] = "Param 'ship_type' is " . $request_data['ship_type'] . "but it can only by 'carrier', 'battleship', 'cruiser', 'submarine' or 'destroyer'";
    } else {
        $result = placeShip($mysqli, $_SESSION['user']['id'], $request_data['ship_type'], $request_data['x'], $request_data['y'], $request_data['orientation']);

        if (!$result['is_valid']) {
            $response['status'] = 'error';
            $response['data'] = $result['validation_error'];
        } else {
            $response['status'] = 'ok';
            $response['data'] = $result;
        }
    }
}

$mysqli->close();
print(json_encode($response, JSON_PRETTY_PRINT));
?>