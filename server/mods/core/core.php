<?php

namespace sockchat\mods\core;
use \sockchat\mods\GenericMod;

use \sockchat\Context;
use \sockchat\User;
use \sockchat\Utils;
use \sockchat\Message;
use \sockchat\Database;

class Main extends GenericMod {
    protected static $silencedUsers;
    protected static $maxAfkTagLength = 5;
    protected static $allowedCmds = ["join", "whois", "afk"];

    public static function IsSilencedFromList($user) {
        $key = Utils::$chat["AUTOID"] ? $user->GetOriginalUsername() : $user->id;
        if(array_key_exists($key, self::$silencedUsers)) {
            if(self::$silencedUsers[$key] > gmdate("U") || self::$silencedUsers[$key] == "-1") return self::$silencedUsers[$key];
            else unset(self::$silencedUsers[$key]);
        }

        return 0;
    }

    public static function IsSilenced($user) {
        $tmp = $user->GetParameter("silence");
        if($tmp == null) return false;
        else {
            if(gmdate("U") > $tmp || $tmp == "-1") return true;
            else {
                $user->SetParameter("silence", null);
                return false;
            }
        }
    }

    public static function Init() {
        $dir = self::GetModFolder();
        if(!file_exists("$dir/ffdb/silences")) mkdir("$dir/ffdb/silences", 0777, true);
        self::$silencedUsers = [];

        $files = glob("$dir/ffdb/silences/*");
        foreach($files as $file) {
            $parts = explode("\f", file_get_contents($file));

            if(gmdate("U") >= $parts[0])
                unlink($file);
            else
                self::$silencedUsers[Utils::$chat["AUTOID"] ? $parts[2] : $parts[1]] = $parts[0];
        }
    }

    public static function OnUserJoin($user) {
        if(($time = self::IsSilencedFromList($user)) != 0) $user->SetParameter("silence", $time);
    }

    public static function OnMessageReceive($user, &$msg) {
        if($user->GetParameter("afk") != null) {
            $user->SetParameter("afk", null);
            $user->username = subst
        }
        if(!self::IsSilenced($user)) return false;
    }

    public static function OnCommandReceive($user, &$cmd, &$args) {
        if(!self::IsSilenced($user) || (self::IsSilenced($user) && in_array($cmd, self::$allowedCmds))) {
            if($cmd == "afk")  {
                if($user->GetParameter("afk") == null && trim($args[0]) != "") {
                    $val = mb_substr($args[0], 0, self::$maxAfkTagLength);
                    $user->SetParameter("afk", $val);
                    $user->username = "<$val>_". $user->username;
                    Context::ModifyUser($user);
                }
                return false;
            } else if($cmd == "silence") {
                if($user->canModerate()) {
                    if(($target = Context::GetUserByName($args[0])) != null) {
                        if(self::IsSilenced())
                    } else Message::PrivateBotMessage(MSG_ERROR, "usernf", [$args[0]], $user);
                } else Message::PrivateBotMessage(MSG_ERROR, "cmdna", ["/silence"], $user);
                return false;
            } else if($cmd == "unsilence") {

                return false;
            }
        } else return false;
    }
}