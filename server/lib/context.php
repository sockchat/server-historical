<?php
namespace sockchat;
use \sockchat\User;
use \sockchat\Channel;

class Ban {
    public $id;
    public $ip;
    public $username;
}

class Context {
    public static $onlineUsers = [];
    public static $channelList = [];
    public static $bannedUsers = [];

    public static function SwitchChannel($user, $to) {
        if($user->channel != $to) {
            if(Context::ChannelExists($to)) {
                if(Context::GetChannel($to)->permissionLevel <= $user->getRank()) {
                    Message::HandleChannelSwitch($user, $to, $user->channel);
                    unset(Context::GetChannel($user->channel)->users[$user->id]);
                    Context::GetChannel($to)->users[$user->id] = Context::$onlineUsers[$user->id];
                    Context::$onlineUsers[$user->id]->channel = $to;
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
        if(!Context::ChannelExists($channel->name) && $channel[0] != "@" && $channel[0] != "*") {
            Context::$channelList[$channel->name] = $channel;
            return true;
        } else return false;
    }

    public static function ModifyChannel() {

    }

    public static function DeleteChannel($name) {

    }

    public static function Join($user) {
        Message::HandleJoin($user);
        Context::$onlineUsers[$user->id] = $user;
        Context::$channelList[Utils::$chat["DEFAULT_CHANNEL"]]->users[$user->id] = Context::$onlineUsers[$user->id];
    }

    public static function AllowUser($username, $sock) {
        foreach(Context::$onlineUsers as $user) {
            if($user->username == $username || $sock == $user->sock)
                return false;
        }
        return 0;
    }

    public static function CheckPings() {
        foreach(Context::$onlineUsers as $user) {
            if(gmdate("U") - $user->ping > Utils::$chat["MAX_IDLE_TIME"]) {
                $user->sock->close();
                Context::Leave($user);
            }
        }
    }

    public static function DoesSockExist($sock) {
        foreach(Context::$onlineUsers as $u) {
            if($u->sock == $sock) return true;
        }

        return false;
    }

    public static function Leave($user) {
        Message::HandleLeave($user);
        unset(Context::GetChannel($user->channel)->users[$user->id]);
        unset(Context::$onlineUsers[$user->id]);
    }
}