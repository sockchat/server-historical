<?php
/*
 * BEGIN CONFIGURATION
 */

$phpbb = array();

$phpbb["ALLOW_ANONS"] = false;
$phpbb["PHPBB_DIR"] = "../phpBB3/";

/*
 * END CONFIGURATION
 *
 * ~~~ !! DO NOT EDIT ANYTHING BELOW THIS !! ~~~
 */

define('IN_PHPBB', true);
$phpbb_root_path = $phpbb["PHPBB_DIR"];
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$user->session_begin();
$auth->acl($user->data);
$user->setup();

//die(var_dump($user->data));

$out = array();

$out["USERNAME"] = $user->data["username"];
if($user->data["user_id"] == ANONYMOUS) {
    if($phpbb["ALLOW_ANONS"]) {
        $out["USERNAME"] = "ANON_". rand(10000,99999);
    } else {
        header("Location: ". $phpbb_root_path);
    }
}
$out["COLOR"] = "#". $user->data["user_colour"];
$out["TIMEZONE"] = $user->data["user_timezone"];
$out["DST"] = ($user->data["user_dst"] == 1)?"true":"false";
$out["MOD"] = ($auth->acl_get("m_") || $auth->acl_get("a_"))?"true":"false";