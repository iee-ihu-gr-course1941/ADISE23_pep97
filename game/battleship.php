<?php

function getActiveGame($db, $user_id) {
    $stmt = $db->prepare("SELECT id, player_1 FROM game_session where player_1=? and finished=0");
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

function createNewGame($db, $user_id) {
    $stmt = $db->prepare("INSERT INTO game_session (player_1, date_started) VALUES (?, CURRENT_TIMESTAMP)");
    $stmt->bind_param('s', $user_id);
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
    $games_query = $db->query("SELECT player_1, player_2 FROM game_session WHERE finished = 0");
    $active_games = $games_query->fetch_all();
    $busy_users = [];

    foreach ($active_games as $users) {
        $busy_users[] = $users[0];
        if (array_key_exists('1', $users) && $users[1] != null) {
            $busy_users[] = $users[1];
        }
    }

    $concatenated_numbers = implode(', ', $busy_users);


    $stmt = $db->prepare("SELECT id, username FROM user WHERE id != ? and id not in ($concatenated_numbers)");
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