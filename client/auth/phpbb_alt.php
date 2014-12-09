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

define('IN_PHPBB', true);
$phpbb_root_path = $phpbb["PHPBB_DIR"];
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

require_once("../lib/auth.php");
use sockchat\Auth;
$sauth = new Auth();

if($sauth->GetPageType() == AUTH_FETCH) {
    $user->session_begin();
    $auth->acl($user->data);
    $user->setup();

    if($user->data["user_id"] != ANONYMOUS) {
        $sauth->AppendArguments([$user->data["user_id"], $user->data["user_password"]]);
        $sauth->Accept();
    } else
        $sauth->Deny();
} else {
    $qdata = array(
        "user_id" => request_var("arg1", -1),
        "user_password" => request_var("arg2", "e")
    );

    if($db->sql_fetchrow($db->sql_query("SELECT COUNT(*) FROM `". USERS_TABLE ."` WHERE ". $db->sql_build_array('SELECT', $qdata)))["COUNT(*)"] > 0) {
        $udata = $db->sql_fetchrow($db->sql_query("SELECT * FROM `". USERS_TABLE ."` WHERE ". $db->sql_build_array('SELECT', $qdata)));

        $user->session_begin();
        $auth->acl($user->data);
        $auth->_fill_acl($udata["user_permissions"]);
        $user->setup();

        $sauth->SetUserData(
            $udata['user_id'],
            $udata['username'],
            $udata['user_colour'] ? "#".$udata['user_colour'] : "inherit"
        );

        $sauth->SetCommonPermissions(
            $auth->acl_get("a_") ? 2 : ($auth->acl_get("m_") ? 1 : 0),
            $auth->acl_get("m_") || $auth->acl_get("a_") ? "1" : "0",
            $auth->acl_get("m_") || $auth->acl_get("a_") ? "1" : "0",
            $auth->acl_get("m_") || $auth->acl_get("a_") ? "1" : "1",
            $auth->acl_get("m_") || $auth->acl_get("a_") ? "2" : "1"
        );

        $sauth->Accept();

        $user->session_kill();
    } else $sauth->Deny();
}

$sauth->Serve();