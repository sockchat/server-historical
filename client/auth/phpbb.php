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
        echo $ulevel ."\t". ($auth->acl_get("m_")||$auth->acl_get("a_")?"1":"0") ."\t". ($auth->acl_get("m_")||$auth->acl_get("a_")?"1":"0") ."\t". ($auth->acl_get("m_")||$auth->acl_get("a_")?"1":"0");

        $user->session_kill();
    } else
        echo "reject";
}
