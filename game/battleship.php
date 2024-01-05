<?php

$GAME_STATUS = [
    'initialized' => 0,
    'started' => 1,
    'finished' => 2,
];

function getActiveGame($db, $user_id) {
    global $GAME_STATUS;
    $stmt = $db->prepare("SELECT id, player_1 FROM game_session where player_1=? and game_phase=" . $GAME_STATUS['finished']);
    $stmt->bind_param('s', $user_id);
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



?>