<?php

$database = 'SISTEMAGEST';
$host = '192.168.1.31';
$username = 'cycwebcob';
$password = "k4&{'Ba7Np1";

$mysqli = new mysqli($host, $username, $password, $database);

if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

$mysqli->set_charset("utf8");
