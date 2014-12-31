<?php
namespace sockchat\mods;

// TODO finish generic mod interface

abstract class GenericMod {
    public static function Init() {} // called on server startup, do initialization work here

    public static function OnUserJoin($user) {} // called when a user attempts to join the chat
    public static function AfterUserJoin($user) {} // called after a user successfully joins the chat

    public static function OnUserLeave($user) {} // called when a user leaves the chat
    public static function OnUserModify($user) {} // called when a user is modified

    public static function OnUserKick($user, $by, &$duration, &$banip) {} // called when a user is about to be kicked or banned (delimited in type)
    public static function AfterUserKick($user, $by, $duration, $banip) {} // called when a user

    public static function OnBanIP(&$ip, &$duration, $by) {} // called when an ip is banned
    public static function AfterBanIP($ip, $duration, $by) {} // called after an ip is successfully banned

    public static function OnChannelSwitch($user, $to, $from) {} // called when a user switches channels
    public static function AfterChannelSwitch($user, $to, $from) {} // called after a successful channel switch

    public static function OnMessageReceive($user, &$msg) {} // called when a message is received by the server
    public static function AfterMessageReceived($user, $msg) {} // called after a message is successfully processed by the server

    public static function OnCommandReceived($user, $cmd, $args) {} // called when a command is received by the server
    // !!! DO NOT !!! USE OnCommandReceived OR AfterCommandReceived TO HANDLE
    // PARSING COMMANDS IN YOUR MOD. INSTEAD USE THE AddCommandHook FUNCTION;
    // THESE FUNCTIONS SHOULD ONLY BE USED IN CIRCUMSTANCES WHERE YOU NEED TO
    // OVERRIDE THE DEFAULT BEHAVIOUR OF THE COMMAND PARSING ENGINE.
    // For more info on AddCommandHook refer to TODO wiki link
    public static function AfterCommandReceived($user, $cmd, $args) {} // called after a command is successfully executed

    public static function OnPacketReceive($conn, &$pid, &$data) {} // called when a packet is received by the server
    public static function AfterPacketReceived($conn, $pid, $data) {} // called after a packet is successfully processed by the server

    public static function OnChannelCreate($channel) {} // called when a channel is created
    public static function AfterChannelCreate($channel) {} // called after a channel is successfully create

    public static function OnChannelModify($old, $new) {} // called when a channel is modified
    public static function AfterChannelModify($old, $new) {} // called after a channel is successfully modified

    public static function OnChannelDelete($channel) {} // called when a channel is deleted
    public static function AfterChannelDelete($channel) {} // called after a channel is successfully deleted

    protected static $cmdHooks = [];

    public static function GetModFolder($namespace) {
        return "./mods/". substr($namespace, strrpos($namespace, "\\")+1);
    }

    protected static function AddCommandHook($cmd, $functionName) {
        if(!is_array($cmd)) $cmd = [$cmd];
        foreach($cmd as $str)
            self::$cmdHooks[$str] = $functionName;
    }

    public static function ExecuteCommand($cmd, $args, $user, $namespace) {
        if(array_key_exists($cmd, self::$cmdHooks)) {
            call_user_func_array("{$namespace}::". self::$cmdHooks[$cmd], [$cmd, $user, $args]);
            return true;
        } else return false;
    }

    public static function GetCommands() {
        $retval = [];

        foreach(self::$cmdHooks as $cmd => $func)
            array_push($retval, $cmd);

        return $retval;
    }
}