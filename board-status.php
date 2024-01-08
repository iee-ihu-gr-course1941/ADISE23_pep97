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
    if (array_key_exists('active', $_GET) && $_GET['active'] == 'true') {
        $game = getActiveGame($mysqli, $_SESSION['user']['id']);
        if (!$game) {
            $response['status'] = 'error';
            $response['data'] = 'There is not an active game';
        } else {
            $response['status'] = 'ok';
            $placements = getShipPlacements($mysqli, $_SESSION['user']['id']);
            $own_placements = [];

            foreach ($placements as $placement) {
                if ($placement['player'] == $_SESSION['user']['id']) {
                    $own_placements [] = $placement;
                }
            }

            $response['data'] = [
                'ship_placements' => $own_placements,
                'round_actions' => getRoundActions($mysqli, $_SESSION['user']['id']),
                'game' => getActiveGame($mysqli, $_SESSION['user']['id']),
            ];
        }
    } else {
        $response['status'] = 'ok';
        $response['data'] = getAllPlayerGames($mysqli, $_SESSION['user']['id']);
    }
}

$mysqli->close();
print(json_encode($response, JSON_PRETTY_PRINT));
?>