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
} else if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    $response['status'] = 'error';
    $response['data'] = 'Not supported method';
} else {
    $response['status'] = 'ok';
    $response['data'] = listAvailablePlayers($mysqli, $_SESSION['user']['id']);
}

$mysqli->close();
print(json_encode($response, JSON_PRETTY_PRINT));
?>
