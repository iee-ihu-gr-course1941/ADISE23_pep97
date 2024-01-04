<?php

require_once "../lib/db_connect.php";

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
$stmt = $mysqli->prepare("SELECT id, username FROM user where username=? and password=?");
$stmt->bind_param('ss', $request_data['username'], $request_data['password']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    $response['status'] = 'ok';
    $response['data'] = [
        'id' => $user['id'],
        'username' => $user['username'],
    ];

    // Start the session
    session_start();
    $_SESSION["user"] = [
        'id' => $user['id'],
        'username' => $user['username'],
    ];
} else {
    $response['data'] = 'Login failed';
}

$mysqli->close();
print(json_encode($response, JSON_PRETTY_PRINT));
?>
