<?php
/*
 * BEGIN CONFIGURATION
 */

$phpbb = array();

$phpbb["PHPBB_DIR"] =  $_SERVER["DOCUMENT_ROOT"] ."/phpBB31/";

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

/*if(!file_exists($phpbb_root_path ."/ext")) { // pre-ext 3.1 integration
    if($db->sql_fetchrow($db->sql_query("SELECT COUNT(*) FROM `". ACL_OPTIONS_TABLE ."` WHERE `auth_option`='m_chat_mod'"))["COUNT(*)"] == 0)
        $db->sql_query("INSERT INTO `". ACL_OPTIONS_TABLE ."` (auth_option, is_global, is_local, founder_only) VALUES ('m_chat_mod', 1, 0, 0)");

    if($db->sql_fetchrow($db->sql_query("SELECT COUNT(*) FROM `". ACL_OPTIONS_TABLE ."` WHERE `auth_option`='m_chat_logs'"))["COUNT(*)"] == 0)
        $db->sql_query("INSERT INTO `". ACL_OPTIONS_TABLE ."` (auth_option, is_global, is_local, founder_only) VALUES ('m_chat_logs', 1, 0, 0)");

    @mkdir($phpbb_root_path ."language/en/mods");

    if(!file_exists($phpbb_root_path ."language/en/mods/permissions_sockchat.php")) {
        file_put_contents($phpbb_root_path ."language/en/mods/permissions_sockchat.php", base64_decode("PD9waHANCmlmKCFkZWZpbmVkKCdJTl9QSFBCQicpKSBleGl0Ow0KDQppZihlbXB0eSgkbGFuZykgfHwgIWlzX2FycmF5KCRsYW5nKSkgJGxhbmcgPSBhcnJheSgpOw0KDQokbGFuZ1sncGVybWlzc2lvbl9jYXQnXVsnY2hhdCddID0gJ0NoYXQgTW9kZXJhdGlvbic7DQoNCiRsYW5nID0gYXJyYXlfbWVyZ2UoJGxhbmcsIGFycmF5KA0KICAgICdhY2xfbV9jaGF0X21vZCcgICAgPT4gYXJyYXkoJ2xhbmcnID0+ICdDYW4gbW9kZXJhdGUgdGhlIGNoYXQuJywgJ2NhdCcgPT4gJ2NoYXQnKSwNCiAgICAnYWNsX21fY2hhdF9sb2dzJyAgICA9PiBhcnJheSgnbGFuZycgPT4gJ0NhbiB2aWV3IHRoZSBjaGF0IGxvZ3MuJywgJ2NhdCcgPT4gJ2NoYXQnKSwNCikpOw0KPz4="));
        $cache->purge();
    }
} else { // 3.1 ext integration
    if(!file_exists($phpbb_root_path ."/ext/aroltd/sockchat")) {
        @mkdir($phpbb_root_path ."/ext/aroltd/sockchat/language/en", 0777, true);
        @mkdir($phpbb_root_path ."/ext/aroltd/sockchat/migrations", 0777, true);
        file_put_contents($phpbb_root_path ."/ext/aroltd/sockchat/language/en/permissions_sockchat.php", base64_decode("PD9waHANCmlmKCFkZWZpbmVkKCdJTl9QSFBCQicpKSBleGl0Ow0KDQppZihlbXB0eSgkbGFuZykgfHwgIWlzX2FycmF5KCRsYW5nKSkgJGxhbmcgPSBhcnJheSgpOw0KDQokbGFuZ1sncGVybWlzc2lvbl9jYXQnXVsnY2hhdCddID0gJ0NoYXQgTW9kZXJhdGlvbic7DQoNCiRsYW5nID0gYXJyYXlfbWVyZ2UoJGxhbmcsIGFycmF5KA0KICAgICdhY2xfbV9jaGF0X21vZCcgICAgPT4gYXJyYXkoJ2xhbmcnID0+ICdDYW4gbW9kZXJhdGUgdGhlIGNoYXQuJywgJ2NhdCcgPT4gJ2NoYXQnKSwNCiAgICAnYWNsX21fY2hhdF9sb2dzJyAgICA9PiBhcnJheSgnbGFuZycgPT4gJ0NhbiB2aWV3IHRoZSBjaGF0IGxvZ3MuJywgJ2NhdCcgPT4gJ2NoYXQnKSwNCikpOw0KPz4="));
        file_put_contents($phpbb_root_path ."/ext/aroltd/sockchat/migrations/release_6_7_7_0.php", base64_decode(""));
        //file_put_contents($phpbb_root_path ."/ext/aroltd/sockchat/acp/main_info.php", base64_decode("PD9waHANCm5hbWVzcGFjZSBhcm9sdGRcc29ja2NoYXRcYWNwOw0KDQpjbGFzcyBtYWluX2luZm8NCnsNCglmdW5jdGlvbiBtb2R1bGUoKQ0KCXsNCgkJcmV0dXJuIGFycmF5KA0KCQkJJ2ZpbGVuYW1lJwk9PiAnXGFyb2x0ZFxzb2NrY2hhdFxhY3BcbWFpbl9tb2R1bGUnLA0KCQkJJ3RpdGxlJwkJPT4gJ0FDUF9TT0NLX0NIQVRfUEVSTVMnLA0KCQkJJ3ZlcnNpb24nCT0+ICc2LjcuNy4wJywNCgkJCSdtb2RlcycJCT0+IGFycmF5KA0KCQkJCSdzZXR0aW5ncycJPT4gYXJyYXkoDQoJCQkJCSd0aXRsZScgPT4gJ0FDUF9TT0NLX0NIQVQnLA0KCQkJCQknYXV0aCcgPT4gJ2V4dF9hcm9sdGQvc29ja2NoYXQgJiYgYWNsX2FfYm9hcmQnLA0KCQkJCQknY2F0JyA9PiBhcnJheSgnQUNQX1NPQ0tfQ0hBVF9QRVJNUycpDQoJCQkJKSwNCgkJCSksDQoJCSk7DQoJfQ0KfQ0K"));
        //file_put_contents($phpbb_root_path ."/ext/aroltd/sockchat/acp/main_module.php", base64_decode("PD9waHANCm5hbWVzcGFjZSBhcm9sdGRcc29ja2NoYXRcYWNwOw0KDQpjbGFzcyBtYWluX21vZHVsZQ0Kew0KCXZhciAkdV9hY3Rpb247DQoNCglmdW5jdGlvbiBtYWluKCRpZCwgJG1vZGUpDQoJew0KCQkkdGhpcy0+dXNlci0+YWRkX2xhbmdfZXh0KCdhcm9sdGQvc29ja2NoYXQnLCAnY29tbW9uJyk7DQoJCWRpZSgidHJpZ2dlcmVkIik7DQoJfQ0KfQ0K"));
        file_put_contents($phpbb_root_path ."/ext/aroltd/sockchat/ext.php", base64_decode("PD9waHANCm5hbWVzcGFjZSBhcm9sdGRcc29ja2NoYXQ7DQoNCmNsYXNzIGV4dCBleHRlbmRzIFxwaHBiYlxleHRlbnNpb25cYmFzZSB7IH0="));
        file_put_contents();
        file_put_contents($phpbb_root_path ."/ext/aroltd/sockchat/composer.json", base64_decode("ew0KCSJuYW1lIjogImFyb2x0ZC9zb2NrY2hhdCIsDQoJInR5cGUiOiAicGhwYmItZXh0ZW5zaW9uIiwNCgkiZGVzY3JpcHRpb24iOiAiU29jayBDaGF0IFBlcm1pc3Npb25zIGZvciBwaHBCQiIsDQoJImhvbWVwYWdlIjogImh0dHBzOi8vZ2l0aHViLmNvbS9mbGFzaGlpL3NvY2tjaGF0IiwNCgkidmVyc2lvbiI6ICI2LjcuNy4wIiwNCgkidGltZSI6ICIyMDAxLTA5LTExIiwNCgkibGljZW5zZSI6ICJHUEwtMi4wIiwNCgkiYXV0aG9ycyI6IFt7DQoJCQkibmFtZSI6ICJNaWtlIERhd3NvbiIsDQoJCQkiZW1haWwiOiAibWFsbG9jbnVsbEB5ZWFoLm5ldCIsDQoJCQkiaG9tZXBhZ2UiOiAiaHR0cDovL2Fyb2x0ZC5jb20iLA0KCQkJInJvbGUiOiAiT2ZmaWNpYWwgTnVjbGVhciBCaXJkIExhZHkiDQoJCX1dLA0KCSJyZXF1aXJlIjogew0KCQkicGhwIjogIj49NS4zLjMiDQoJfSwNCgkicmVxdWlyZS1kZXYiOiB7DQoJCSJwaHBiYi9lcHYiOiAiZGV2LW1hc3RlciINCgl9LA0KCSJleHRyYSI6IHsNCgkJImRpc3BsYXktbmFtZSI6ICJTb2NrIENoYXQgUGVybWlzc2lvbnMiLA0KCQkic29mdC1yZXF1aXJlIjogew0KCQkJInBocGJiL3BocGJiIjogIj49My4xLjAtUkMyLDwzLjIuKkBkZXYiDQoJCX0NCgl9DQp9DQo="));
        $cache->purge();
    }
}*/

$out = array();

$qdata = array(
    "user_id" => request_var("arg1", -1),
    "user_password" => request_var("arg2", "e")
);

if($qdata["user_id"] == -1) {
    $user->session_begin();
    $auth->acl($user->data);
    $user->setup();

    if($user->data["user_id"] == ANONYMOUS)
        header("Location: ". $chat["REDIRECT_ADDR"]);

    $out["ARGS"] = array($user->data["user_id"], $user->data["user_password"]);

    $out["TIMEZONE"] = $user->data["user_timezone"];
    $out["DST"] = ($user->data["user_dst"] == 1)?"true":"false";
} else {
    if($db->sql_fetchrow($db->sql_query("SELECT COUNT(*) FROM `". USERS_TABLE ."` WHERE ". $db->sql_build_array('SELECT', $qdata)))["COUNT(*)"] > 0) {
        $udata = $db->sql_fetchrow($db->sql_query("SELECT * FROM `". USERS_TABLE ."` WHERE ". $db->sql_build_array('SELECT', $qdata)));

        $user->session_begin();
        $auth->acl($user->data);
        $auth->_fill_acl($udata["user_permissions"]);
        $user->setup();

        $ulevel = $auth->acl_get("a_")?2:($auth->acl_get("m_")?1:0);

        // must print the user's id or -1 for chat server auto id numbering (NOT RECOMMENDED !!)
        echo $udata['user_id'] ."\n";

        // must print the user's proper name as seen in the chat
        echo $udata['username'] ."\n";

        // must print the user's name color (use inherit for the default text color)
        echo ($udata['user_colour']?"#".$udata['user_colour']:"inherit") ."\n";

        // permissions string TODO write about this
        echo $ulevel . ($auth->acl_get("m_")||$auth->acl_get("a_")?"1":"0") . ($auth->acl_get("m_")||$auth->acl_get("a_")?"1":"0");

        $user->session_kill();
    } else
        echo "reject";
}
