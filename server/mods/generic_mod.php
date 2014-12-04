<?php
namespace sockchat\mods;

// TODO finish generic mod interface

interface GenericMod {

    public static function Init(); // called on server startup, do initialization work here
    public static function OnUserJoin($user); // called when a user joins the chat
    public static function OnUserLeave($user); // called when a user leaves the char
    public static function OnChannelSwitch($user, $to, $from); // called when a user switches channels
    public static function OnMessageReceive($user, $msg); // called when a message is received by the server
    public static function OnPacketReceive($user, $packet); // called when a packet is received by the server

}