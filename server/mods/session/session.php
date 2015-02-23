<?php
namespace sockchat\mods\session;
use \sockchat\mods\GenericMod;
use \sockchat\Context;
use \sockchat\User;
use \sockchat\Utils;
use \sockchat\Message;
use \sockchat\Channel;

class Main extends GenericMod {
    public $dir;

    public static function Init() {
        $dir = self::GetModFolder(__NAMESPACE__) . "/sessions/";
        @mkdir($dir);
        self::AddCommandHook("session", "OnSessionCommand");
    }

    public static function GenerateSessionKey() {

    }

    public static function OnSessionCommand($cmd, $user, $args) {
        if(count($args) > 0) {

        } else Message::PrivateBotMessage(MSG_ERROR, "sesscmderr", [], $user);
    }
}