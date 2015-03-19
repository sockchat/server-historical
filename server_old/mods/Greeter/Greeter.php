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
use sockchat\User;
use \sockchat\Utils;
use \sockchat\Message;
use \sockchat\Database;

// the main class must be called Main and must extend \sockchat\mods\GenericMod.
class Main extends GenericMod {
    protected static $bot;

    public static function Init() {
        self::$bot = Context::AddInvisibleUser("negrophobe", "#FF69B4");
    }

    // we want to do things when a user joins
    public static function OnUserJoin($user) {
        // send the bot message to the user that has just entered the chat
        Message::PrivateSilentBotMessage(MSG_NORMAL, "say", ["Welcome to the chat, ". $user->username ."!"], $user, "welcome");
        Message::PrivateSilentBotMessage(MSG_NORMAL, "say", ["If you need help, ask a [color=blue][b]moderator[/b][/color]."], $user, "welcome");
    }

    /*
    public static function AfterChannelCreate($channel) {
        Message::BroadcastUserMessage(self::$bot, "channel ". $channel->name ." created", ALL_CHANNELS);
    }

    public static function OnCommandReceive($user, &$cmd, &$args) {
        //Message::BroadcastUserMessage(self::$bot, $user->username ." sent command ". $cmd ." with args ". implode(" ", $args), $user->channel);
    }

    public static function AfterMessageReceived($user, $msg) {
        if(strstr($msg, "nigger")) Message::BroadcastUserMessage(self::$bot, "the only nigger here is [i]you[/i]", $user->channel);
    }

    public static function OnUserLeave($user) {
        Message::BroadcastUserMessage(self::$bot, "this ". $user->username ." guy is a faggot", $user->channel);
    }
    */
}