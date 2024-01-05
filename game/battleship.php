<?php

$GAME_STATUS = [
    'initialized' => 0,
    'started' => 1,
    'finished' => 2,
];

$SHIP_TYPE = [
    'carrier' => 0,
    'battleship' => 1,
    'cruiser' => 2,
    'submarine' => 3,
    'destroyer' => 4,
];


$SHIP_SIZE = [
    'carrier' => 5,
    'battleship' => 4,
    'cruiser' => 3,
    'submarine' => 3,
    'destroyer' => 2,
];

$SHIP_ORIENTATION = [
    'horizontal' => 0,
    'vertical' => 1,
];

function getActiveGame($db, $user_id) {
    global $GAME_STATUS;
    $stmt = $db->prepare("SELECT * FROM game_session where ( player_1=? or player_2=? )and game_phase !=" . $GAME_STATUS['finished']);
    $stmt->bind_param('ss', $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $game_session = $result->fetch_assoc();
        return $game_session;
    } else {
        return null;
    }
}

function createNewGame($db, $user_id, $second_player) {

    // check if the current player is available
    global $GAME_STATUS;
    $player_1_games = $db->query("SELECT player_1 FROM game_session WHERE game_phase != " . $GAME_STATUS['finished']);
    $player_1_active_games = $player_1_games->fetch_all();

    if (sizeof($player_1_active_games) > 0) {
        return null;
    }

    // check if the second player is available for the game
    $available_players = listAvailablePlayers($db, $user_id);
    $player_2_is_available = false;
    foreach ($available_players as $available_player) {
        if ($available_player['id'] == $second_player) {
            $player_2_is_available = true;
        }
    }

    if (!$player_2_is_available) {
        return null;
    }

    $stmt = $db->prepare("INSERT INTO game_session (player_1, player_2, date_started) VALUES (?, ?, CURRENT_TIMESTAMP)");
    $stmt->bind_param('ss', $user_id, $second_player);
    $stmt->execute();

    $new_game_id = $stmt->insert_id;
    if ($stmt->affected_rows > 0) {
        $game_stmt = $db->prepare("SELECT * FROM game_session WHERE id = ?");
        $game_stmt->bind_param('s', $new_game_id);
        $game_stmt->execute();
        $result = $game_stmt->get_result();
        $game_session = $result->fetch_assoc();
        return $game_session;
    } else {
        return null;
    }
}

function listAvailablePlayers($db, $user_id) {
    global $GAME_STATUS;
    $games_query = $db->query("SELECT player_1, player_2 FROM game_session WHERE game_phase != " . $GAME_STATUS['finished']);
    $active_games = $games_query->fetch_all();
    $busy_users = [];

    foreach ($active_games as $users) {
        $busy_users[] = $users[0];
        if (array_key_exists('1', $users) && $users[1] != null) {
            $busy_users[] = $users[1];
        }
    }

    $concatenated_numbers = implode(', ', $busy_users);

    if (sizeof($busy_users) > 0) {
      $sql_query = "SELECT id, username FROM user WHERE id != ? and id not in ($concatenated_numbers)";
    } else {
        $sql_query = "SELECT id, username FROM user WHERE id != ?";
    }


    $stmt = $db->prepare($sql_query);
    $stmt->bind_param('s', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $available_players = [];

    $current_player = $res->fetch_assoc();
    while($current_player) {
        $available_players []= $current_player;
        $current_player = $res->fetch_assoc();
    }

    return $available_players;
}

function getShipPlacements($db, $user_id) {
    $active_game = getActiveGame($db, $user_id);

    if ($active_game == null) {
        return null;
    }

    $game_stmt = $db->prepare("SELECT * FROM ship_placement WHERE session = ?");
    $game_stmt->bind_param('s', $active_game['id']);
    $game_stmt->execute();
    $res = $game_stmt->get_result();

    $ship_placements = [];

    $current_placement = $res->fetch_assoc();
    while($current_placement) {
        $ship_placements []= $current_placement;
        $current_placement = $res->fetch_assoc();
    }

    return $ship_placements;
}

function placeShip($db, $user_id, $ship_type, $x, $y, $orientation) {
    global $GAME_STATUS;
    global $SHIP_TYPE;
    global $SHIP_ORIENTATION;
    global $SHIP_SIZE;

    $active_game = getActiveGame($db, $user_id);

    if (!$active_game) {
        return null;
    }

    if (!$active_game || $active_game['game_phase'] != $GAME_STATUS['initialized']) {
        return null;
    }

    // get current board
    $current_placements = getShipPlacements($db, $user_id);
    $player_placements = [];
    foreach ($current_placements as $current_placement) {
        if ($current_placement['player'] == $user_id) {
            $player_placements []= $current_placement;
        }
    }

    // validate placement
    $is_valid = true;
    $validation_error = null;
    foreach ($player_placements as $player_placement) {
        if ($player_placement['ship_type'] == $SHIP_TYPE[$ship_type]) {
            $is_valid = false;
            $validation_error = "Ship '$ship_type' has already been placed";
            break;
        }

        if ($orientation == 'horizontal') {
            if ($x + $SHIP_SIZE[$ship_type] > 10) {
                $is_valid = false;
                $validation_error = "Ship '$ship_type' out of range";
                break;
            }

            if ($y == $player_placement['y'] && $x >= $player_placement['x'] && $x <= $player_placement['x'] + $SHIP_SIZE[$ship_type]) {
                $is_valid = false;
                $validation_error = "Ship '$ship_type' conflicts on x = $x with other ship";
                break;
            }
        } else {
            if ($y + $SHIP_SIZE[$ship_type] > 10) {
                $is_valid = false;
                $validation_error = "Ship '$ship_type' out of range";
                break;
            }

            if ($x == $player_placement['x'] && $y >= $player_placement['y'] && $y <= $player_placement['y'] + $SHIP_SIZE[$ship_type]) {
                $is_valid = false;
                $validation_error = "Ship '$ship_type' conflicts on y = $y with other ship";
                break;
            }
        }
    }

    if (!$is_valid) {
        return [
            'is_valid' => $is_valid,
            'validation_error' => $validation_error,
        ];
    }

    // place ship
    $stmt = $db->prepare("INSERT INTO ship_placement (session, player, date, ship_type, x, y, orientation) VALUES (?, ?, CURRENT_DATE, ?, ?, ?, ?)");
    $stmt->bind_param('ssssss', $active_game['id'], $user_id, $SHIP_TYPE[$ship_type], $x, $y, $SHIP_ORIENTATION[$orientation]);
    $stmt->execute();
}

function getBoardStatus($db, $user_id) {
    $active_game = getActiveGame($db, $user_id);

    if (!$active_game) {
        return null;
    }




}

?>