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
            $channel->log->Log($user, $msg);
    }

    protected static function SendToChannel($msg, $channel) {
        foreach($channel->users as $user)
            $user->sock->send($msg);
    }

    protected static function LogToChannel($user, $msg, $channel) {
        $channel->log->Log($user, $msg);
    }

    // NOTE: DOES NOT SANITIZE INPUT MESSAGE !! DO THIS ELSEWHERE
    public static function BroadcastUserMessage($user, $msg, $channel = LOCAL_CHANNEL) {
        $out = Utils::PackMessage(2, array(gmdate("U"), $user->id, $msg, Message::$msgId));

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
        $out = Utils::PackMessage(2, array(gmdate("U"), "-1", $msg, Message::$msgId));

        if($channel == ALL_CHANNELS) {
            Message::SendToAll($out);
            Message::LogToAll(Message::$bot, $msg);
            Message::$msgId++;
        } else {
            $channel = ($channel == LOCAL_CHANNEL) ? Utils::$chat["DEFAULT_CHANNEL"] : $channel;

            if(Context::ChannelExists($channel)) {
                Message::SendToChannel($out, $channel);
            }
        }
    }
}