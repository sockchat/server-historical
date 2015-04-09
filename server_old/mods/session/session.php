<?php
namespace sockchat\mods\session;
use \sockchat\mods\GenericMod;
use \sockchat\Context;
use \sockchat\User;
use \sockchat\Utils;
use \sockchat\Message;
use \sockchat\Channel;
use \sockchat\Auth;

class Main extends GenericMod {
    public static $dir;

    public static function Init() {
        self::$dir = self::GetModFolder(__NAMESPACE__) . "/sessions/";
        @mkdir(self::$dir);
        self::AddCommandHook("session", "OnSessionCommand");
    }

    private static function FindSessionKey($user) {
        $files = glob(self::$dir ."*");
        foreach($files as $file) {
            $parts = explode("\n", file_get_contents($file));
            var_dump($parts);
            if($GLOBALS["chat"]["AUTOID"]) {
                if($parts[1] == $user->GetOriginalUsername())
                    return $file;
            } else {
                if($parts[0] == $user->id)
                    return $file;
            }
        }
        return null;
    }

    public static function CheckSession($name, $pwd) {
        if(Auth::Reserved(null, $name)) {
            if(file_exists(self::$dir . Utils::Hash($name . $pwd))) {
                $parts = explode("\n", file_get_contents(self::$dir . Utils::Hash($name . $pwd)));
                return Auth::Validate($GLOBALS["chat"]["AUTOID"] ? null : $parts[0], $parts[1]);
            } else return null;
        } else return Auth::Validate(null, $name);
    }

    private static function GenerateSessionKey($user, $pwd) {
        if(Auth::Reserved($GLOBALS["chat"]["AUTOID"] ? null : $user->id, $user->username)) {
            if(($file = self::FindSessionKey($user)) != null) unlink($file);
            file_put_contents(self::$dir . Utils::Hash($user->GetOriginalUsername() . $pwd), $user->id ."\n". $user->GetOriginalUsername());
            Message::PrivateBotMessage(MSG_NORMAL, "sessgen", [], $user);
        } else Message::PrivateBotMessage(MSG_ERROR, "sessnotres", [], $user);
    }

    public static function DeleteSessionKey($user) {
        if(($file = self::FindSessionKey($user)) != null) {
            unlink($file);
            Message::PrivateBotMessage(MSG_NORMAL, "sessdel", [], $user);
        } else Message::PrivateBotMessage(MSG_ERROR, "sessdelerr", [], $user);
    }

    public static function OnSessionCommand($cmd, $user, $args) {
        if(count($args) > 0) {
            if($args[0] == "set") {
                if(count($args) > 1) {
                    self::GenerateSessionKey($user, implode(" ", array_slice($args, 1)));
                } else Message::PrivateBotMessage(MSG_ERROR, "sesscmderr", [], $user);
            } else if($args[0] == "delete") {
                self::DeleteSessionKey($user);
            } else Message::PrivateBotMessage(MSG_ERROR, "sesscmderr", [], $user);
        } else Message::PrivateBotMessage(MSG_ERROR, "sesscmderr", [], $user);
    }
}