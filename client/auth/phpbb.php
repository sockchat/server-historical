<?php
/*
 * BEGIN CONFIGURATION
 */

$phpbb = array();

$phpbb["PHPBB_DIR"] =  $_SERVER["DOCUMENT_ROOT"] ."/phpBB3/";

/*
 * END CONFIGURATION
 *
 * ~~~ !! DO NOT EDIT ANYTHING BELOW THIS !! ~~~
 */

error_reporting(E_ALL);

define('IN_PHPBB', true);
$phpbb_root_path = $phpbb["PHPBB_DIR"];
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

if($db->sql_fetchrow($db->sql_query("SELECT COUNT(*) FROM `". ACL_OPTIONS_TABLE ."` WHERE `auth_option`='m_chat_mod'"))["COUNT(*)"] == 0)
    $db->sql_query("INSERT INTO `". ACL_OPTIONS_TABLE ."` (auth_option, is_global, is_local, founder_only) VALUES ('m_chat_mod', 1, 0, 0)");

if($db->sql_fetchrow($db->sql_query("SELECT COUNT(*) FROM `". ACL_OPTIONS_TABLE ."` WHERE `auth_option`='m_chat_logs'"))["COUNT(*)"] == 0)
    $db->sql_query("INSERT INTO `". ACL_OPTIONS_TABLE ."` (auth_option, is_global, is_local, founder_only) VALUES ('m_chat_logs', 1, 0, 0)");

if(!file_exists($phpbb_root_path ."language/en/mods/permissions_sockchat.php")) {
    $fp = fopen($phpbb_root_path ."language/en/mods/permissions_sockchat.php", "w");
    fwrite($fp, base64_decode("PD9waHANCmlmKCFkZWZpbmVkKCdJTl9QSFBCQicpKSBleGl0Ow0KDQppZihlbXB0eSgkbGFuZykgfHwgIWlzX2FycmF5KCRsYW5nKSkgJGxhbmcgPSBhcnJheSgpOw0KDQokbGFuZ1sncGVybWlzc2lvbl9jYXQnXVsnY2hhdCddID0gJ0NoYXQgTW9kZXJhdGlvbic7DQoNCiRsYW5nID0gYXJyYXlfbWVyZ2UoJGxhbmcsIGFycmF5KA0KICAgICdhY2xfbV9jaGF0X21vZCcgICAgPT4gYXJyYXkoJ2xhbmcnID0+ICdDYW4gbW9kZXJhdGUgdGhlIGNoYXQuJywgJ2NhdCcgPT4gJ2NoYXQnKSwNCiAgICAnYWNsX21fY2hhdF9sb2dzJyAgICA9PiBhcnJheSgnbGFuZycgPT4gJ0NhbiB2aWV3IHRoZSBjaGF0IGxvZ3MuJywgJ2NhdCcgPT4gJ2NoYXQnKSwNCikpOw0KPz4="));
    fclose($fp);
    $cache->destroy('acl_options');
}

$out = array();

if(!$_GET["arg1"]) {
    $user->session_begin();
    $auth->acl($user->data);
    $user->setup();

    if($user->data["user_id"] == ANONYMOUS)
        header("Location: ". $chat["REDIRECT_ADDR"]);

    $out["ARGS"] = array($user->data["user_id"], $user->data["user_password"]);

    $out["TIMEZONE"] = $user->data["user_timezone"];
    $out["DST"] = ($user->data["user_dst"] == 1)?"true":"false";
} else {
    if(is_numeric($_GET["arg1"])) {
        $pwd = $db->sql_escape($_GET["arg2"]);
        if($db->sql_fetchrow($db->sql_query("SELECT COUNT(*) FROM `". USERS_TABLE ."` WHERE `user_id`=". $_GET['arg1'] ." AND `user_password`='". $pwd ."'"))["COUNT(*)"] > 0) {
            $udata = $db->sql_fetchrow($db->sql_query("SELECT * FROM `". USERS_TABLE ."` WHERE `user_id`=". $_GET['arg1'] ." AND `user_password`='". $pwd ."'"));

            $user->session_begin();
            $auth->acl($user->data);
            $auth->_fill_acl($udata["user_permissions"]);
            $user->setup();

            $ulevel = $auth->acl_get("a_")?2:($auth->acl_get("m_")?1:0);
            echo $udata['user_id'] ."\n". $udata['username'] ."\n". ($udata['user_colour']?"#".$udata['user_colour']:"inherit") ."\n". $ulevel . ($auth->acl_get("m_chat_mod")?"1":"0") . ($auth->acl_get("m_chat_logs")?"1":"0");

            $user->session_kill();
        } else
            echo "reject";
    }
}
