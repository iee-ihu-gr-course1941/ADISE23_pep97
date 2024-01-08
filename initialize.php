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
    $currentGame = getActiveGame($mysqli, $_SESSION['user']['id']);

    if (!$request_data['player_2']) {
        $response['status'] = 'error';
        $response['data'] = 'Please add the player_2 field';
    } else {
        if (!$currentGame) {
            $new_game = createNewGame($mysqli, $_SESSION['user']['id'], $request_data['player_2']);
            if ($new_game != null) {
                $response['status'] = 'ok';
                $response['data'] = $new_game;
            } else {
                $response['status'] = 'error';
                $response['data'] = 'The game could not be initialized';
            }

        } else {
            $response['status'] = 'error';
            $response['data'] = 'A game is already in progress';
        }
    }
}

$mysqli->close();
print(json_encode($response, JSON_PRETTY_PRINT));
?>
