ADISE NAVMAXIA 2023


## Configure

1. create the file "config_local.php" at the project root
2. Declare the variables `$DB_USER` and `$DB_PASS`
3. Create the database `adise_battleship`
4. Run the sql script found at DB/schema.sql

## Play

1. Create  a user
2. Create a game
3. Place ships
4. Mark player as ready
5. Start the game

## API

### Users

#### Create

```shell
curl --location 'http://localhost/auth/register.php' \
--header 'Content-Type: application/json' \
--data '{
    "username": "user01",
    "password": "password"
}'
```

#### Login

```shell
curl --location 'http://localhost/auth/login.php' \
--header 'Content-Type: application/json' \
--data '{
    "username": "user01",
    "password": "password"
}'
```

#### Logout

```shell
curl --location --request POST 'http://localhost/auth/logout.php' \
--header 'Cookie: PHPSESSID=....'
```



### Game

#### Get available players

```shell
curl --location 'http://localhost/game/available-players.php' \
--header 'Cookie: PHPSESSID=...'
```

#### Create a game

player_2: The id of the other player

```shell
curl --location 'http://localhost/game/initialize.php' \
--header 'Content-Type: application/json' \
--header 'Cookie: PHPSESSID=...' \
--data '{
    "player_2": player_id
}
'
```

#### Mark player as ready

You should have placed all the ships, before marking the player as ready

```shell
curl --location --request POST 'http://localhost/game/player-ready.php' \
--header 'Cookie: PHPSESSID=...'
```

#### Place ship

x: 1 -10

y: 1 - 10

orientation: 'horizontal' or 'vertical'

ship_type: 'carrier', 'battleship', 'cruiser', 'submarine', 'destroyer'

```shell
curl --location 'http://localhost/game/place-ship.php' \
--header 'Content-Type: application/json' \
--header 'Cookie: PHPSESSID=...' \
--data '{
    "ship_type": "carrier",
    "orientation": "horizontal",
    "x": 1,
    "y": 1
}
'
```

#### Play a round

Player 1 plays on even turn numbers and player 2 on odd 

x: 1 -10

y: 1 - 10

```shell
curl --location 'http://localhost/game/execute-turn.php' \
--header 'Content-Type: application/json' \
--header 'Cookie: PHPSESSID=...' \
--data '{
    "x": 1,
    "y": 4
}
'
```

#### Read board status
```shell
curl --location 'http://localhost/game/board-status.php?active=false' \
--header 'Cookie: PHPSESSID=...'
```
