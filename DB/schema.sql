
DROP TABLE IF EXISTS round_action;
DROP TABLE IF EXISTS ship_placement;
DROP TABLE IF EXISTS game_session;
DROP TABLE IF EXISTS user;

CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username char(20) NOT NULL UNIQUE,
    password char(20) NOT NULL
);

CREATE TABLE game_session (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_1 int NOT NULL,
    player_2 INT,
    game_phase INT DEFAULT(0) NOT NULL,
    winner INT,
    round INT NOT NULL DEFAULT(0),
    date_started DATETIME NOT NULL,
    player_1_ready INT NOT NULL DEFAULT (0),
    player_2_ready INT NOT NULL DEFAULT (0),
    FOREIGN KEY (player_1) REFERENCES user(id),
    FOREIGN KEY (player_2) REFERENCES user(id)
);

CREATE TABLE ship_placement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session INT NOT NULL,
    player INT NOT NULL,
    date DATETIME NOT NULL,
    ship_type INT NOT NULL,
    orientation INT NOT NULL,
    x INT NOT NULL,
    y INT NOT NULL,
    FOREIGN KEY (session) REFERENCES game_session(id),
    FOREIGN KEY (player) REFERENCES user(id)
);

CREATE TABLE round_action (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session INT NOT NULL,
    round INT NOT NULL,
    player INT NOT NULL,
    hit INT NOT NULL DEFAULT(0),
    date DATETIME NOT NULL,
    x INT NOT NULL,
    y INT NOT NULL,
    FOREIGN KEY (session) REFERENCES game_session(id),
    FOREIGN KEY (player) REFERENCES user(id)
);
