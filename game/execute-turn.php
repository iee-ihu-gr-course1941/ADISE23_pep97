<?php

require_once "../lib/db_connect.php";
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

    if (!array_key_exists('x', $request_data)) {
        $response['status'] = 'error';
        $response['data'] = "Param 'x' not found";
    } else if (!array_key_exists('y', $request_data)) {
        $response['status'] = 'error';
        $response['data'] = "Param 'y' not found";
    } else if ($request_data['x'] < 1  || $request_data['x'] > 10) {
        $response['status'] = 'error';
        $response['data'] = "Param 'x' is out of range 0-10";
    } else if ($request_data['y'] < 1  || $request_data['y'] > 10) {
        $response['status'] = 'error';
        $response['data'] = "Param 'y' is out of range 0-10";
    } else {
        $response['status'] = 'ok';
        $turnResult = playTurn($mysqli, $_SESSION['user']['id'], $request_data['x'], $request_data['y']);

        if ($turnResult == null) {
            $response['status'] = 'error';
            $response['data'] = "Could not execute turn";
        } else {
            $response['data'] = [
                'hit' => $turnResult,
            ];
        }
    }
}

$mysqli->close();
print(json_encode($response, JSON_PRETTY_PRINT));
?>
