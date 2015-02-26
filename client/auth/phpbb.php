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

use sockchat\Auth;

if(isset($request))
    $request->enable_super_globals();

if(Auth::GetPageType() == AUTH_FETCH) {
    $user->session_begin();
    $auth->acl($user->data);
    $user->setup();

    if($user->data["user_id"] != ANONYMOUS) {
        Auth::AppendArguments([$user->data["user_id"], $user->data["user_password"]]);
        Auth::Accept();
    } else
        Auth::Deny();
} else if(Auth::GetPageType() != AUTH_RESERVED) {
    if(Auth::GetPageType() == AUTH_CONFIRM) {
        $qdata = array(
            "user_id" => request_var("arg1", -1),
            "user_password" => request_var("arg2", "e")
        );
    } else {
        if(isset($_GET["uid"])) {
            $qdata = array(
                "user_id" => request_var("uid", -1)
            );
        } else {
            $qdata = array(
                "username" => request_var("username", "e")
            );
        }
    }

    if($db->sql_fetchrow($db->sql_query("SELECT COUNT(*) FROM `". USERS_TABLE ."` WHERE ". $db->sql_build_array('SELECT', $qdata)))["COUNT(*)"] > 0) {
        $udata = $db->sql_fetchrow($db->sql_query("SELECT * FROM `". USERS_TABLE ."` WHERE ". $db->sql_build_array('SELECT', $qdata)));

        $user->session_begin();
        $auth->acl($user->data);
        $auth->_fill_acl($udata["user_permissions"]);
        $user->setup();

        Auth::SetUserData(
            $udata['user_id'],
            $udata['username'],
            $udata['user_colour'] ? "#".$udata['user_colour'] : "inherit"
        );

        Auth::SetCommonPermissions(
            $auth->acl_get("a_") ? 2 : ($auth->acl_get("m_") ? 1 : 0),
            $auth->acl_get("m_") || $auth->acl_get("a_") ? "1" : "0",
            $auth->acl_get("m_") || $auth->acl_get("a_") ? "1" : "0",
            $auth->acl_get("m_") || $auth->acl_get("a_") ? "1" : "1",
            $auth->acl_get("m_") || $auth->acl_get("a_") ? "2" : "1"
        );

        Auth::Accept();

        //$user->session_kill();
    } else Auth::Deny();
} else
    Auth::Reserved();

Auth::Serve();