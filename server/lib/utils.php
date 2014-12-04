<?php
namespace sockchat;

class Utils {
    public static $separator = "\t";
    public static $chat;

    public static function PackMessage($id, $params) {
        return $id . Utils::$separator . join(Utils::$separator, $params);
    }

    public static function FormatBotMessage($type, $id, $params) {
        return $type ."\f". $id ."\f". implode("\f", $params);
    }

    public static function Sanitize($str) {
        return str_replace("\n", " <br/>", str_replace("\\","&#92;",htmlspecialchars($str, ENT_QUOTES)));
    }

    public static function GetHeader($sock, $name) {
        try {
            return (string)$sock->WebSocket->request->getHeader($name, true);
        } catch(\Exception $e) {
            return "";
        }
    }
}