<?php

require_once "../config_local.php";

$user=$DB_USER;
$pass=$DB_PASS;
$host=$DB_HOST;
$db=$DB_NAME;


if(gethostname()=='users.iee.ihu.gr') {
    $mysqli = new mysqli($host, $user, $pass, $db,null,'/home/student/it/2015/it154522/mysql/run/mysql.sock');
} else {
    $mysqli = new mysqli($host, $user, $pass, $db);
}


if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" .
        $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

?>
