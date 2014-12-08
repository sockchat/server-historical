<?php
/*
 * GREETER: A SAMPLE SERVER MOD
 *
 * This will send a chatbot message to a user first thing as they join the server, greeting them. This is meant as a
 * simple example of how to write mods for the Sock Chat server engine.
 *
 * For a reference guide to the Sock Chat engine interface, refer to the following page:
 * TODO put link to engine reference here
 */

// the filename containing the main class must have the same name as the folder

namespace sockchat\mods\Greeter; // the third portion of the namespace must be the same as the folder name
use \sockchat\mods\GenericMod; // necessary for the implementation (detailed below)

// some common static classes you may deal with
use \sockchat\Context;
use \sockchat\Utils;
use \sockchat\Message;
use \sockchat\Database;

// the main class must be called Main and must implement \sockchat\mods\GenericMod.
class Main implements GenericMod {
    // you technically need to implement all of the handles, but you don't actually need to do anything with them.
    public static function Init() {}
    public static function OnUserLeave($user) {}
    public static function OnChannelSwitch($user, $to, $from) {}
    public static function OnMessageReceive($user, $msg) {}
    public static function OnPacketReceive($user, $packet) {}

    // we want to actually implement this function, however
    public static function OnUserJoin($user) {
        // send the bot message to the user that has just entered the chat
        Message::PrivateBotMessage(MSG_NORMAL, "say", "Welcome to the chat, ". $user->username ."!", $user);
        Message::PrivateBotMessage(MSG_NORMAL, "say", "If you need help, ask a [color=blue][b]moderator[/b][/color].", $user);
    }
}