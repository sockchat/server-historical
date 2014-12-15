<?php
namespace sockchat;

use React\Stream\Util;

class Message {
    public static $msgId = 0;
    public static $bot;

    protected static function SendToAll($msg) {
        foreach(Context::$onlineUsers as $user)
            $user->sock->send($msg);
    }

    protected static function LogToAll($user, $msg) {
        foreach(Context::$channelList as $channel)
            $channel->log->Log($user, $msg, Message::$msgId);
    }

    protected static function SendToChannel($msg, $channel) {
        if(is_string($channel)) {
            if(Context::ChannelExists($channel)) {
                $channel = Context::GetChannel($channel);
            } else return;
        }

        foreach($channel->users as $user)
            $user->sock->send($msg);
    }

    protected static function LogToChannel($user, $msg, $channel) {
        if(is_string($channel)) {
            if(Context::ChannelExists($channel)) {
                $channel = Context::GetChannel($channel);
            } else return;
        }

        $channel->log->Log($user, $msg, Message::$msgId);
    }

    public static function BroadcastSilentMessage($time, $user, $msg, $msgid = null, $channel = ALL_CHANNELS) {
        $msgid = $msgid == null ? Message::$msgId : $msgid;
        if($channel == ALL_CHANNELS)
            Message::SendToAll(Utils::PackMessage(7, [$time, $user, $msg, $msgid]));
        else
            Message::SendToChannel(Utils::PackMessage(7, [$time, $user, $msg, $msgid]), $channel);
    }

    public static function PrivateSilentMessage($time, $user, $msg, $to, $msgid = null) {
        $msgid = $msgid == null ? Message::$msgId : $msgid;
        $to->sock->send(Utils::PackMessage(7, [$time, $user, $msg, $msgid]));
    }

    public static function ClearUserContext($user, $type = CLEAR_ALL) {
        $user->sock->send(Utils::PackMessage(P_CTX_CLR, array($type)));
    }

    public static function ClearUserContexts($channel = ALL_CHANNELS, $type = CLEAR_ALL) {
        $out = Utils::PackMessage(P_CTX_CLR, array($type));

        if($channel == ALL_CHANNELS) Message::SendToAll($out);
        else Message::SendToChannel($out, ($channel == LOCAL_CHANNEL) ? Utils::$chat["DEFAULT_CHANNEL"] : $channel);
    }

    // NOTE: DOES NOT SANITIZE INPUT MESSAGE !! DO THIS ELSEWHERE
    public static function BroadcastUserMessage($user, $msg, $channel = LOCAL_CHANNEL) {
        $out = Utils::PackMessage(P_SEND_MESSAGE, array(gmdate("U"), $user->id, $msg, Message::$msgId));

        if($channel == ALL_CHANNELS) {
            Message::SendToAll($out);
            Message::LogToAll($user, $msg);
            Message::$msgId++;
        } else {
            $channel = ($channel == LOCAL_CHANNEL) ? $user->channel : $channel;

            if(Context::ChannelExists($channel)) {
                Message::SendToChannel($out, Context::GetChannel($channel));
                Message::LogToChannel($user, $msg, $channel);
                Message::$msgId++;
            }
        }
    }

    public static function BroadcastBotMessage($type, $langid, $params, $channel = ALL_CHANNELS) {
        $msg = Utils::FormatBotMessage($type, $langid, $params);
        $channel = ($channel == LOCAL_CHANNEL) ? Utils::$chat["DEFAULT_CHANNEL"] : $channel;
        Message::BroadcastUserMessage(Message::$bot, $msg, $channel);
    }

    public static function PrivateUserMessage($user, $to, $msg) {
        $out = Utils::PackMessage(P_SEND_MESSAGE, array(gmdate("U"), $user->id, $msg, Message::$msgId));
        $to->sock->send($out);
        Message::$msgId++;
    }

    public static function PrivateBotMessage($type, $langid, $params, $to) {
        $msg = Utils::FormatBotMessage($type, $langid, $params);
        Message::PrivateUserMessage(Message::$bot, $to, $msg);
        Message::$msgId++;
    }

    public static function SendChannelToUser($user, $channel) {
        if(is_string($channel)) $channel = Context::GetChannel($channel);
        if($user->getRank() >= $channel->permissionLevel) $user->sock->send(Utils::PackMessage(P_CHANNEL_INFO, ["0", $channel]));
    }

    public static function SendAllChannelsToUser($user) {
        $arr = [];
        foreach(Context::$channelList as $channel) {
            if($user->getRank() >= $channel->permissionLevel) array_push($arr, $channel);
        }
        $user->sock->send(Utils::PackMessage(P_CTX_DATA, ["2", count($arr), join(Utils::$separator, $arr)]));
    }

    public static function HandleKick($user, $length = 0) {
        if($length == 0)
            $user->sock->send(Utils::PackMessage(P_BAKA, ["0"]));
        else
            $user->sock->send(Utils::PackMessage(P_BAKA, ["1", $length]));
    }

    public static function HandleUserModification($user) {
        Message::SendToChannel(Utils::PackMessage(P_USER_CHANGE, [$user]), $user->channel);
    }

    public static function HandleJoin($user) {
        Message::SendToChannel(Utils::PackMessage(P_USER_JOIN, array(gmdate("U"), $user, Message::$msgId)), Utils::$chat["DEFAULT_CHANNEL"]);

        $user->sock->send(Utils::PackMessage(P_USER_JOIN, array("y", $user, Utils::$chat["DEFAULT_CHANNEL"])));
        Message::LogToChannel(Message::$bot, Utils::FormatBotMessage(MSG_NORMAL, "join", array($user->username)), Utils::$chat["DEFAULT_CHANNEL"]);
        $user->sock->send(Utils::PackMessage(P_CTX_DATA, array("0", count(Context::GetChannel(Utils::$chat["DEFAULT_CHANNEL"])->users), Context::GetChannel(Utils::$chat["DEFAULT_CHANNEL"])->GetAllUsers())));

        $msgs = Context::GetChannel(Utils::$chat["DEFAULT_CHANNEL"])->log->GetAllLogStrings();
        foreach($msgs as $msg)
            $user->sock->send(Utils::PackMessage(P_CTX_DATA, array("1", $msg)));

        Message::SendAllChannelsToUser($user);

        Message::$msgId++;
    }

    public static function HandleChannelCreation($channel) {
        foreach(Context::$onlineUsers as $user)
            Message::SendChannelToUser($user, $channel);
    }

    public static function HandleChannelDeletion($channel) {
        if(is_string($channel)) $channel = Context::GetChannel($channel);

        foreach(Context::$onlineUsers as $user) {
            if($user->getRank() >= $channel->permissionLevel) $user->sock->send(Utils::PackMessage(4, ["2", $channel->name]));
        }
    }

    public static function HandleChannelModification($channel, $oldname = "") {
        if(is_string($channel)) $channel = Context::GetChannel($channel);
        foreach(Context::$onlineUsers as $user) {
            if($user->getRank() >= $channel->permissionLevel) {
                $user->sock->send(Utils::PackMessage(4, ["2", $oldname == "" ? $channel->name : $oldname, $channel]));
                if($user->channel == $oldname && $oldname != "") {
                    $user->sock->send(Utils::PackMessage(5, ["2", $channel->name]));
                    $user->channel = $channel->name;
                }
            }
        }
    }

    public static function HandleChannelSwitch($user, $to, $from) {
        Message::SendToChannel(Utils::PackMessage(P_CHANGE_CHANNEL, array("1", $user->id, Message::$msgId)), $from);
        Message::LogToChannel(Message::$bot, Utils::FormatBotMessage(MSG_NORMAL, "lchan", array($user->username)), $from);
        Message::SendToChannel(Utils::PackMessage(P_CHANGE_CHANNEL, array("0", $user, Message::$msgId)), $to);
        Message::LogToChannel(Message::$bot, Utils::FormatBotMessage(MSG_NORMAL, "jchan", array($user->username)), $to);
        $user->sock->send(Utils::PackMessage(P_CTX_CLR, array(CLEAR_MSGNUSERS)));
        $user->sock->send(Utils::PackMessage(P_CTX_DATA, array("0", count(Context::GetChannel($to)->users), Context::GetChannel($to)->GetAllUsers(), Message::$msgId)));

        $msgs = Context::GetChannel($to)->log->GetAllLogStrings();
        foreach($msgs as $msg)
            $user->sock->send(Utils::PackMessage(P_CTX_DATA, array("1", $msg)));

        $user->sock->send(Utils::PackMessage(P_CHANGE_CHANNEL, array("2", $to)));

        Message::$msgId++;
    }

    public static function HandleLeave($user, $method = LEAVE_NORMAL) {
        Message::SendToChannel(Utils::PackMessage(P_USER_LEAVE, array($user->id, $user->username, $method, gmdate("U"), Message::$msgId)), $user->channel);
        Message::LogToChannel(Message::$bot, Utils::FormatBotMessage(MSG_NORMAL, $method == LEAVE_NORMAL ? "leave" : "kick", array($user->username)), $user->channel);
        Message::$msgId++;
    }
}