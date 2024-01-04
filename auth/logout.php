<?php
session_start();

header('Content-type: application/json');
$response = [
    'status' => 'error',
    'data' => '',
];

if (array_key_exists('user', $_SESSION)) {
    session_destroy();
    $response['status'] = 'ok';
    $response['data'] = 'user logged out';
} else {
    $response['status'] = 'error';
    $response['data'] = 'User is not logged in';
}

print(json_encode($response, JSON_PRETTY_PRINT));
?>