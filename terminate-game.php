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
    $result = terminateGame($mysqli, $_SESSION['user']['id']);
    if ($result != null) {
        $response['status'] = 'ok';
        $response['data'] = 'Game terminated';
    } else {
        $response['status'] = 'error';
        $response['data'] = 'There is no active game';
    }
}

$mysqli->close();
print(json_encode($response, JSON_PRETTY_PRINT));
?>