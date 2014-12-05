<?php
$chat = array();

$chat["DEFAULT_CHANNEL"] = "Lobby";

$chat["CHATROOT"] = "http://www.aroltd.com/chat/";
$chat["HOST"] = "aroltd.com";
$chat["PORT"] = 12120;

$chat["MAX_NAME_LEN"] = 25;
$chat["MAX_MSG_LEN"] = 2000;

$chat["AUTH_TYPE"] = AUTH_PHPBB;
$chat["CAUTH_FILE"] = "authfile.php";
$chat["AUTOID"] = false;

$chat["MAX_IDLE_TIME"] = 180;

$chat["BACKLOG_LENGTH"] = 9;

$GLOBALS["chat"] = $chat;

/*$chat["DB_TYPE"] = NO_DB;
$chat["DB_NAME"] = "sockchat";
$chat["DB_HOST"] = "localhost";
$chat["DB_USER"] = "username";
$chat["DB_PASS"] = "password";*/