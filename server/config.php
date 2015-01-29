<?php
$chat = array();

$chat["DEFAULT_CHANNEL"]        = "Lobby";

$chat["CHATROOT"]               = "http://www.aroltd.com/chat/";
$chat["HOST"]                   = "aroltd.com";
$chat["PORT"]                   = 12120;

$chat["MAX_CHANNEL_NAME_LEN"]   = 30;
$chat["MAX_USERNAME_LEN"]       = 30;
$chat["MAX_MSG_LEN"]            = 2000;

$chat["AUTOID"]                 = false;
$chat["MAX_IDLE_TIME"]          = 180;
$chat["BACKLOG_LENGTH"]         = 9;

$chat["DB_ENABLE"]              = true;
$chat["DB_DSN"]                 = "mysql:host=localhost;dbname=sockchat";
$chat["DB_TABLE_PREFIX"]        = "sock";
$chat["DB_USER"]                = "username";
$chat["DB_PASS"]                = "password";

$GLOBALS["chat"]                = $chat;