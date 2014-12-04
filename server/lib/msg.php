<?php
namespace sockchat;
use \sockchat\Context;
use \sockchat\Utils;

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
        foreach($channel->users as $user)
            $user->sock->send($msg);
    }

    protected static function LogToChannel($user, $msg, $channel) {
        $channel->log->Log($user, $msg, Message::$msgId);
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

    public static function HandleJoin($user) {
        Message::SendToChannel(Utils::PackMessage(P_USER_JOIN, array(gmdate("U"), $user->id, $user->username, $user->color, $user->permissions, Message::$msgId)), Utils::$chat["DEFAULT_CHANNEL"]);

        $user->sock->send(Utils::PackMessage(P_USER_JOIN, array("y", $user->id, $user->username, $user->color, $user->permissions, Utils::$chat["DEFAULT_CHANNEL"])));
        Message::LogToChannel(Message::$bot, Utils::FormatBotMessage(MSG_NORMAL, "join", array($user->username)), Utils::$chat["DEFAULT_CHANNEL"]);
        $user->sock->send(Utils::PackMessage(P_CTX_DATA, array("0", count(Context::GetChannel(Utils::$chat["DEFAULT_CHANNEL"])->users), join(Utils::$separator, Context::GetChannel(Utils::$chat["DEFAULT_CHANNEL"]).GetAllUsers()))));

        $msgs = Context::GetChannel(Utils::$chat["DEFAULT_CHANNEL"])->log->GetAllLogStrings();
        foreach($msgs as $msg)
            $user->sock->send(Utils::PackMessage(P_CTX_DATA, array("1", $msg)));

        Message::$msgId++;
    }

    public static function HandleChannelSwitch($user, $to, $from) {
        Message::SendToChannel(Utils::PackMessage(P_CHANGE_CHANNEL, array("1", $user->id, Message::$msgId)), $from);
        Message::LogToChannel(Message::$bot, Utils::PackMessage(MSG_NORMAL, "lchan", array($user->username)), $from);
        Message::SendToChannel(Utils::PackMessage(P_CHANGE_CHANNEL, array("0", $user->id, $user->username, $user->color, $user->permissions, Message::$msgId)), $to);
        Message::LogToChannel(Message::$bot, Utils::PackMessage(MSG_NORMAL, "jchan", array($user->username)), $to);
        $user->sock->send(Utils::PackMessage(P_CTX_CLR, array(CLEAR_MSGNUSERS)));
        $user->sock->send(Utils::PackMessage(P_CTX_DATA, array("0", count(Context::GetChannel($to)->users), join(Utils::$separator, Context::GetChannel($to)->GetAllUsers()), Message::$msgId)));

        $msgs = Context::GetChannel($to)->log->GetAllLogStrings();
        foreach($msgs as $msg)
            $user->sock->send(Utils::PackMessage(P_CTX_DATA, array("1", $msg)));

        Message::$msgId++;
    }

    public static function HandleLeave($user) {
        Message::SendToChannel(Utils::PackMessage(P_USER_LEAVE, array($user->id, $user->username, gmdate("U"), Message::$msgId)), $user->channel);
        Message::LogToChannel(Message::$bot, Utils::FormatBotMessage(MSG_NORMAL, "leave", array($user->username)), $user->channel);
        Message::$msgId++;
    }
}