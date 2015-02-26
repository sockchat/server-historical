<?php

namespace sockchat;

use sockchat\User;
use sockchat\Utils;

class Auth {
    private static function Request($get, &$buf) {
        $uri = Utils::$chat['CHATROOT'] ."/?view=auth";
        foreach($get as $key => $val)
            $uri .= "&{$key}={$val}";
        $page = file_get_contents($uri);
        if(substr($page, 0, 2) == "no") return false;
        else {
            if(substr($page, 0, 3) == "yes") {
                $buf = substr($page, 3);
                return true;
            } else return false;
        }
    }

    private static function GenerateUser($data) {
        $data = explode("\n", $data);
        return new User($data[0], $GLOBALS["chat"]["DEFAULT_CHANNEL"], Utils::SanitizeName($data[1]), $data[2], $data[3], null, $data[1]);
    }

    public static function Confirm($args) {
        $get = [];
        for($i = 0; $i < count($args); $i++)
            $get["arg". ($i+1)] = $args[$i];
        if(self::Request($get, $buf))
            return self::GenerateUser($buf);
        else return null;
    }

    public static function Validate($id, $username) {
        $get = ["validate" => "true"];
        if($id != null) $get["uid"] = $id;
        if($username != null) $get["username"] = $username;
        if(self::Request($get, $buf))
            return self::GenerateUser($buf);
        else return null;
    }

    public static function Reserved($id, $username) {
        $get = ["reserve" => "true"];
        if($id != null) $get["uid"] = $id;
        if($username != null) $get["username"] = $username;
        return self::Request($get, $buf);
    }
}