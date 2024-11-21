<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

require_once 'pokemonAPI.php';

$dbHost = 'db';
$dbName = 'pokemon_db';
$dbUser = 'user';
$dbPass = 'pass';

$api = new PokemonAPI($dbHost, $dbName, $dbUser, $dbPass);
$api->handleRequest();
?>