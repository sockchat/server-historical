<?php
namespace sockchat;

class Utils {
    public static $separator = "\t";
    public static $chat;

    public static function PackMessage($id, $params) {
        $ret = $id . Utils::$separator . join(Utils::$separator, $params);
        return $ret;
    }

    public static function FormatBotMessage($type, $id, $params) {
        return $type ."\f". $id ."\f". implode("\f", $params);
    }

    public static function Sanitize($str) {
        return str_replace(["<", ">", "\n"], ["&lt;", "&gt;", " <br/> "], $str);
    }

    public static function SanitizeName($name) {
        return str_replace([" ","\n","\t","\f",":","!","@","#"], ["_","","","","","","",""], htmlspecialchars($name, ENT_QUOTES));
    }

    public static function GetHeader($sock, $name) {
        try {
            return (string)$sock->WebSocket->request->getHeader($name, true);
        } catch(\Exception $e) {
            return "";
        }
    }

    public static function DoesModExist($name) {
        return file_exists("./mods/". $name);
    }

    public static function DoesCommandExist($name) {
        return file_exists("./commands/". $name .".php");
    }

    public static function Hash($in) {
        return hash("sha256", $in);
    }

    public static function IsValidIPAddress($addr) {
        $addr = explode(".", $addr);
        if(count($addr) != 4) return false;
        foreach($addr as $subaddr) {
            if(!is_numeric($subaddr) && $subaddr != "*") return false;
            if(($subaddr > 255 || $subaddr < 0) && $subaddr != "*") return false;
        }
        return true;
    }

    public static function CheckIPAddresses($addr1, $addr2) {
        $addr1 = explode(".", $addr1);
        $addr2 = explode(".", $addr2);

        for($i = 0; $i < 4; $i++) {
            if($addr1[$i] != $addr2[$i] && $addr1[$i] != "*" && $addr2[$i] != "*") return false;
        }

        return true;
    }

    public static function CreateUniqueFile($dir) {
        try {
            while(file_exists($fname = "$dir/". md5(microtime())));
        } catch(\Exception $e) {
            while(file_exists($fname = "$dir/". md5(time() + rand(0, 100))));
        }
        return $fname;
    }
}