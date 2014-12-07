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
        return str_replace("\n", " <br/>", str_replace("\\","&#92;",htmlspecialchars($str, ENT_QUOTES)));
    }

    public static function SanitizeName($name) {
        return str_replace(" ", "_", str_replace("\\","&#92;",htmlspecialchars($name, ENT_QUOTES)));
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
}