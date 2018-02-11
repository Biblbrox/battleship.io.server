<?php

error_reporting(E_ALL);

require_once __DIR__ .  "/vendor/autoload.php";

use \Battleship\App\BattleshipServer;

$server = new BattleshipServer();
$server->run();
