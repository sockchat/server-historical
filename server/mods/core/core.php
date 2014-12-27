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
    protected static $folder;

    public static function Silence($user, $expires) {
        self::$silencedUsers[Utils::$chat["AUTOID"] ? $user->GetOriginalUsername() : $user->id] = $expires;
        $user->SetParameter("silence", $expires);
        $fn = Utils::CreateUniqueFile(self::$folder ."/ffdb/silences");
        file_put_contents($fn, implode("\f", [$expires, $user->id, $user->GetOriginalUsername()]));
    }

    public static function RemoveSilence($user) {
        $search = Utils::$chat["AUTOID"] ? [$user->GetOriginalUsername(), 2] : [$user->id, 1];
        if(array_key_exists($search[0], self::$silencedUsers)) unset(self::$silencedUsers[$search[0]]);
        $user->SetParameter("silence", null);

        $files = glob(self::$folder ."/ffdb/silences/*");
        foreach($files as $file) {
            $parts = explode("\f", file_get_contents($file));
            if($parts[$search[1]] == $search[0]) {
                unlink($file);
                break;
            }
        }
    }

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
            if($tmp > gmdate("U") || $tmp == "-1") return true;
            else {
                self::RemoveSilence($user);
                return false;
            }
        }
    }

    public static function Init() {
        $dir = self::$folder = self::GetModFolder(__NAMESPACE__);

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
        if(($val = $user->GetParameter("afk")) != null) {
            $user->username = mb_substr($user->username, strlen("&lt;$val&gt;_"));
            $user->SetParameter("afk", null);
            Context::ModifyUser($user);
        }
        if(self::IsSilenced($user)) return false;
    }

    public static function OnCommandReceive($user, &$cmd, &$args) {
        if(!self::IsSilenced($user) || (self::IsSilenced($user) && in_array($cmd, self::$allowedCmds))) {
            if($cmd == "afk")  {
                $val = isset($args[0]) ? strtoupper(mb_substr($args[0], 0, self::$maxAfkTagLength)) : "AFK";
                if($user->GetParameter("afk") == null && $val != "") {
                    $user->SetParameter("afk", $val);
                    $user->username = "&lt;$val&gt;_". $user->username;
                    Context::ModifyUser($user);
                }
                return false;
            } else if($cmd == "silence") {
                if($user->canModerate()) {
                    if(($target = Context::GetUserByName($args[0])) != null) {
                        if($target->id != $user->id) {
                            if($target->getRank() < $user->getRank()) {
                                if (!self::IsSilenced($target)) {
                                    $exp = isset($args[1]) ? (int)gmdate("U") + abs($args[1]) : -1;
                                    self::Silence($target, $exp);
                                    Message::PrivateBotMessage(MSG_NORMAL, "silence", [], $target);
                                    Message::PrivateBotMessage(MSG_NORMAL, "silok", [$target->username], $user);
                                } else Message::PrivateBotMessage(MSG_ERROR, "silerr", [], $user);
                            } else Message::PrivateBotMessage(MSG_ERROR, "silperr", [], $user);
                        } else Message::PrivateBotMessage(MSG_ERROR, "silself", [], $user);
                    } else Message::PrivateBotMessage(MSG_ERROR, "usernf", [$args[0]], $user);
                } else Message::PrivateBotMessage(MSG_ERROR, "cmdna", ["/silence"], $user);
                return false;
            } else if($cmd == "unsilence") {
                if($user->canModerate()) {
                    if(($target = Context::GetUserByName($args[0])) != null) {
                        if($target->getRank() < $user->getRank()) {
                            if (self::IsSilenced($target)) {
                                self::RemoveSilence($target);
                                Message::PrivateBotMessage(MSG_NORMAL, "unsil", [], $target);
                                Message::PrivateBotMessage(MSG_NORMAL, "usilok", [$target->username], $user);
                            } else Message::PrivateBotMessage(MSG_ERROR, "usilerr", [], $user);
                        } else Message::PrivateBotMessage(MSG_ERROR, "usilperr", [], $user);
                    } else Message::PrivateBotMessage(MSG_ERROR, "usernf", [$args[0]], $user);
                } else Message::PrivateBotMessage(MSG_ERROR, "cmdna", ["/unsilence"], $user);
                return false;
            }
        } else return false;
    }
}