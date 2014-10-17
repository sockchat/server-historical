<?php
include("constants.php");

$chat = array();

$chat["CHATROOT"] = "http://www.aroltd.com/chat/";
$chat["HOST"] = "aroltd.com";
$chat["PORT"] = 12120;

$chat["AUTH_TYPE"] = AUTH_PHPBB;
$chat["CAUTH_FILE"] = "authfile.php";
$chat["AUTOID"] = false;

$chat["MAX_IDLE_TIME"] = 90;

$GLOBALS["chat"] = $chat;

/*$chat["DB_TYPE"] = NO_DB;
$chat["DB_NAME"] = "sockchat";
$chat["DB_HOST"] = "localhost";
$chat["DB_USER"] = "username";
$chat["DB_PASS"] = "password";*/