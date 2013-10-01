<?php

define("CURL_OPTION_SSL_VERIFYPEER", false);

error_reporting(-1);

require 'connector.php';
require 'eahashor.php';

$answer   = "";
$username = "";
$password = "";
$machine  = "";

$hashor     = new EAHashor();
$hash       = $hashor->eaEncode($answer);
$connector  = new Connector($username, $password, $answer, $machine);
$connection = $connector->connect();
