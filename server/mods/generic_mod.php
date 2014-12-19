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

    public static function OnCommandReceive($user, &$cmd, &$args) {} // called when a command is received by the server
    public static function AfterCommandReceived($user, $cmd, $args) {} // called after a command is successfully executed

    public static function OnPacketReceive($conn, &$pid, &$data) {} // called when a packet is received by the server
    public static function AfterPacketReceived($conn, $pid, $data) {} // called after a packet is successfully processed by the server

    public static function OnChannelCreate($channel) {} // called when a channel is created
    public static function AfterChannelCreate($channel) {} // called after a channel is successfully create

    public static function OnChannelModify($old, $new) {} // called when a channel is modified
    public static function AfterChannelModify($old, $new) {} // called after a channel is successfully modified

    public static function OnChannelDelete($channel) {} // called when a channel is deleted
    public static function AfterChannelDelete($channel) {} // called after a channel is successfully deleted
}