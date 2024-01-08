<?php

require_once "lib/db_connect.php";

header('Content-type: application/json');
$response = [
    'status' => 'error',
    'data' => '',
];

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $response['status'] = 'error';
    $response['data'] = 'Not supported method';
}

$request_data = json_decode(file_get_contents('php://input'), true);

/* Prepared statement, stage 1: prepare */
$stmt = $mysqli->prepare("SELECT id, username FROM user where username=?");
$stmt->bind_param('s', $request_data['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $response['status'] = 'error';
    $response['data'] = 'User already exist';
} else {
    $stmt = $mysqli->prepare("INSERT INTO user (username, password) VALUES (?,?)");
    $stmt->bind_param('ss', $request_data['username'], $request_data['password']);
    $stmt->execute();
    $result = $stmt->get_result();

    $response['status'] = 'ok';
    $response['data'] = [
        'id' => $stmt->insert_id,
        'username' => $request_data['username'],
    ];
}

$mysqli->close();
print(json_encode($response, JSON_PRETTY_PRINT));
?>
