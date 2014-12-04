<?php
define("AUTH_CUSTOM", 0);
define("AUTH_PHPBB", 1);

$auth_method = array("", "phpbb.php");

define("MSG_NORMAL", 0);
define("MSG_ERROR", 1);

define("CHANNEL_TEMP", 0);
define("CHANNEL_PERM", 1);

define("ALL_CHANNELS", "@all");
define("LOCAL_CHANNEL", "@local");

define("CLEAR_MSGS", "0");
define("CLEAR_USERS", "1");
define("CLEAR_CHANNELS", "2");
define("CLEAR_MSGNUSERS", "3");
define("CLEAR_ALL", "4");

define("CTX_USER", "0");
define("CTX_MSG", "1");
define("CTX_CHANNEL", "2");

define("P_USER_JOIN", 1);
define("P_SEND_MESSAGE", 2);
define("P_USER_LEAVE", 3);
define("P_CHANNEL_INFO", 4);
define("P_CHANGE_CHANNEL", 5);
define("P_MSG_DEL", 6);
define("P_CTX_DATA", 7);
define("P_CTX_CLR", 8);
define("P_BAKA", 9);
define("P_USER_CHANGE", 10);