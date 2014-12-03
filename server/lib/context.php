<?php
namespace sockchat;
use \sockchat\User;
use \sockchat\Channel;

class Context {
    public static $onlineUsers = [];
    public static $channelList = [];
    public static $chatbot;

    public static function SwitchChannel($user, $to) {
        if($user->channel != $to) {
            if(Context::ChannelExists($to)) {
                if(Context::GetChannel($to)->permissionLevel <= $user->getRank()) {

                } else Message::PrivateBotMessage(MSG_ERROR, "ipchan", array($to), $user);
            } else Message::PrivateBotMessage(MSG_ERROR, "nochan", array($to), $user);
        } else Message::PrivateBotMessage(MSG_ERROR, "samechan", array($to), $user);
    }

    public static function GetUserByID($id) {
        if(array_key_exists(Context::$onlineUsers, $id)) return Context::$onlineUsers[$id];
        else return null;
    }

    public static function GetUserByName($name) {
        foreach(Context::$onlineUsers as $user) {
            if ($user->username == $name) return $user;
        }

        return null;
    }

    public static function GetChannel($name) {
        if(array_key_exists(Context::$channelList, $name)) return Context::$channelList[$name];
        else return Context::$channelList[Utils::$chat["DEFAULT_CHANNEL"]];
    }

    public static function ChannelExists($name) {
        return array_key_exists($name, Context::$channelList);
    }

    public static function CreateChannel($channel) {
        if(!Context::ChannelExists($channel->name)) {
            Context::$channelList[$channel->name] = $channel;
            return true;
        } else return false;
    }

    public static function DeleteChannel($name) {

    }

    public static function Join($user) {
        Context::$onlineUsers[$user->id] = $user;
        Context::$channelList[Utils::$chat["DEFAULT_CHANNEL"]]->users[$user->id] = $user;

    }

    public static function Leave($user) {

    }
}