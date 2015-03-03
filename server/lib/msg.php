<?php
namespace sockchat;

class Message {
    public static $msgId = 0;
    public static $bot;

    protected static function SendToAll($msg) {
        foreach(Context::$onlineUsers as $user)
            $user->sock->send($msg);
    }

    protected static function LogToAll($user, $msg, $flags) {
        if(!Modules::ExecuteRoutine("OnMessageLog", [$user, &$msg, "@all", &$flags])) return;
        foreach(Context::$channelList as $channel)
            $channel->log->Log($user, $msg, Message::$msgId, null, $flags);
        Database::Log(gmdate("U"), $user, $msg, "@all", $flags);
        Modules::ExecuteRoutine("OnMessageLog", [$user, $msg, "@all", $flags]);
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

    protected static function LogToChannel($user, $msg, $channel, $flags) {
        if(is_string($channel)) {
            if($channel[0] != "@") {
                if (Context::ChannelExists($channel)) {
                    $channel = Context::GetChannel($channel);
                } else return;
            }
        }

        Modules::ExecuteRoutine("OnMessageLog", [$user, &$msg, $channel, &$flags]);
        if(!is_string($channel)) {
            Database::Log(gmdate("U"), $user, $msg, $channel->name, $flags);
            $channel->log->Log($user, $msg, Message::$msgId, null, $flags);
        } else Database::Log(gmdate("U"), $user, $msg, $channel, $flags);
        Modules::ExecuteRoutine("AfterMessageLog", [$user, $msg, $channel, $flags]);
    }

    public static function BroadcastSilentMessage($user, $msg, $channel = ALL_CHANNELS, $msgid = null, $time = null, $alert = false, $flags = "1001") {
        if(!is_string($channel)) $channel = $channel->name;
        $msgid = $msgid == null ? Message::$msgId : $msgid;
        $flags = substr($flags, 0, 4) ."0";
        if($channel == ALL_CHANNELS)
            Message::SendToAll(Utils::PackMessage(P_CTX_DATA, ["1", $time == null ? gmdate("U") : $time, $user, $msg, $msgid, $alert == true ? "1": "0", $flags]));
        else
            Message::SendToChannel(Utils::PackMessage(P_CTX_DATA, ["1", $time == null ? gmdate("U") : $time, $user, $msg, $msgid, $alert == true ? "1": "0", $flags]), $channel);
    }

    public static function BroadcastSilentBotMessage($type, $langid, $params, $channel = ALL_CHANNELS, $msgid = null, $time = null, $alert = false) {
        Message::BroadcastSilentMessage(Message::$bot, Utils::FormatBotMessage($type, $langid, $params), $channel, $msgid, $time, $alert);
    }

    public static function PrivateSilentMessage($user, $msg, $to, $msgid = null, $time = null, $alert = false, $flags = "1001", $pm = true) {
        $msgid = $msgid == null ? Message::$msgId : $msgid;
        $flags = substr($flags, 0, 4) . ($pm ? "1" : "0");
        $to->sock->send(Utils::PackMessage(P_CTX_DATA, ["1", $time == null ? gmdate("U") : $time, $user, $msg, $msgid, $alert == true ? "1": "0", $flags]));
    }

    public static function PrivateSilentBotMessage($type, $langid, $params, $to, $msgid = null, $time = null, $alert = false) {
        Message::PrivateSilentMessage(Message::$bot, Utils::FormatBotMessage($type, $langid, $params), $to, $msgid, $time, $alert, "1001", false);
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
    public static function BroadcastUserMessage($user, $msg, $channel = LOCAL_CHANNEL, $flags = "1001") {
        if(!is_string($channel)) $channel = $channel->name;
        $flags = substr($flags, 0, 4) ."0";
        $out = Utils::PackMessage(P_SEND_MESSAGE, array(gmdate("U"), $user->id, $msg, Message::$msgId, $flags, "0", ($channel == LOCAL_CHANNEL) ? $user->channel : $channel));

        if($channel == ALL_CHANNELS) {
            Message::SendToAll($out);
            Message::LogToAll($user, $msg, $flags);
            Message::$msgId++;
        } else {
            $channel = ($channel == LOCAL_CHANNEL) ? $user->channel : $channel;

            if(Context::ChannelExists($channel)) {
                Message::SendToChannel($out, Context::GetChannel($channel));
                Message::LogToChannel($user, $msg, $channel, $flags);
                Message::$msgId++;
            }
        }
    }

    public static function BroadcastBotMessage($type, $langid, $params, $channel = ALL_CHANNELS) {
        $msg = Utils::FormatBotMessage($type, $langid, $params);
        $channel = ($channel == LOCAL_CHANNEL) ? Utils::$chat["DEFAULT_CHANNEL"] : $channel;
        Message::BroadcastUserMessage(Message::$bot, $msg, $channel);
    }

    public static function PrivateUserMessage($user, $to, $msg, $flags = "1001", $pm = true) {
        $flags = substr($flags, 0, 4) . ($pm ? "1" : "0");
        $out = Utils::PackMessage(P_SEND_MESSAGE, array(gmdate("U"), $user->id, $msg, Message::$msgId, $flags, "1"));
        $to->sock->send($out);
        if($user->id != $to->id)
            Message::LogToChannel($user, "(@". $to->username .") ". $msg, "@priv", $flags);
        Message::$msgId++;
    }

    public static function PrivateBotMessage($type, $langid, $params, $to) {
        $msg = Utils::FormatBotMessage($type, $langid, $params);
        Message::PrivateUserMessage(Message::$bot, $to, $msg, "1001", false);
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
            $user->sock->send(Utils::PackMessage(P_BAKA, ["kick"]));
        else
            $user->sock->send(Utils::PackMessage(P_BAKA, ["ban", date("U") + $length]));
    }

    public static function HandleUserModification($user) {
        Message::SendToChannel(Utils::PackMessage(P_USER_CHANGE, [$user]), $user->channel);
    }

    public static function HandleJoin($user) {
        Message::SendToChannel(Utils::PackMessage(P_USER_JOIN, array(gmdate("U"), $user, Message::$msgId)), Utils::$chat["DEFAULT_CHANNEL"]);

        $user->sock->send(Utils::PackMessage(P_USER_JOIN, array("y", $user, Utils::$chat["DEFAULT_CHANNEL"])));
        Message::LogToChannel(Message::$bot, Utils::FormatBotMessage(MSG_NORMAL, "join", array($user->username)), Utils::$chat["DEFAULT_CHANNEL"], "10010");

        Message::SendAllChannelsToUser($user);
        $user->sock->send(Utils::PackMessage(P_CTX_DATA, array("0", Context::GetChannel(Utils::$chat["DEFAULT_CHANNEL"])->GetAllUsers())));
        $msgs = Context::GetChannel(Utils::$chat["DEFAULT_CHANNEL"])->log->GetAllLogStrings();
        foreach($msgs as $msg)
            $user->sock->send(Utils::PackMessage(P_CTX_DATA, array("1", $msg)));

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
        Database::ModifyChannel($oldname == "" ? $channel->name : $oldname, $channel->name, $channel->password, $channel->permissionLevel);
        foreach(Context::$onlineUsers as $user) {
            if($user->getRank() >= $channel->permissionLevel) {
                $user->sock->send(Utils::PackMessage(4, ["1", $oldname == "" ? $channel->name : $oldname, $channel]));
                if($user->channel == $oldname && $oldname != "") {
                    $user->sock->send(Utils::PackMessage(5, ["2", $channel->name]));
                    $user->channel = $channel->name;
                }
            }
        }
    }

    public static function HandleChannelSwitch($user, $to, $from) {
        Message::SendToChannel(Utils::PackMessage(P_CHANGE_CHANNEL, array("1", $user->id, Message::$msgId)), $from);
        Message::LogToChannel(Message::$bot, Utils::FormatBotMessage(MSG_NORMAL, "lchan", array($user->username)), $from, "10010");
        Message::SendToChannel(Utils::PackMessage(P_CHANGE_CHANNEL, array("0", $user, Message::$msgId)), $to);
        Message::LogToChannel(Message::$bot, Utils::FormatBotMessage(MSG_NORMAL, "jchan", array($user->username)), $to, "10010");
        $user->sock->send(Utils::PackMessage(P_CTX_CLR, array(CLEAR_MSGNUSERS)));
        $user->sock->send(Utils::PackMessage(P_CTX_DATA, array("0", Context::GetChannel($to)->GetAllUsers(), Message::$msgId)));

        $msgs = Context::GetChannel($to)->log->GetAllLogStrings();
        foreach($msgs as $msg)
            $user->sock->send(Utils::PackMessage(P_CTX_DATA, array("1", $msg)));

        $user->sock->send(Utils::PackMessage(P_CHANGE_CHANNEL, array("2", $to)));

        Message::$msgId++;
    }

    public static function HandleLeave($user, $method = LEAVE_NORMAL) {
        Message::SendToChannel(Utils::PackMessage(P_USER_LEAVE, array($user->id, $user->username, $method, gmdate("U"), Message::$msgId)), $user->channel);
        Message::LogToChannel(Message::$bot, Utils::FormatBotMessage(MSG_NORMAL, $method, array($user->username)), $user->channel, "10010");
        Message::$msgId++;
    }

    public static function DeleteMessage($id) {
        Message::SendToAll(Utils::PackMessage(P_MSG_DEL, [$id]));
    }
}