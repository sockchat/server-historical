<?php
namespace sockchat;
use \sockchat\User;
use \sockchat\Channel;

class Ban {
    public $id = null;
    public $ip = null;
    public $username = null;
    public $expire;

    public function __construct($ip, $id, $username, $expire) {
        $this->id = Utils::$chat["AUTOID"] ? null : $id;
        $this->ip = $ip;
        $this->username = $username;
        $this->expire = $expire;
    }

    public function Check($id, $ip, $username) {
        return (($this->id == null ? false : $id == $this->id) ||
                ($this->ip == null ? false : Utils::CheckIPAddresses($ip, $this->ip)) ||
                ($this->username == null ? false : $username == $this->username)) &&
               ($this->expire > time() || $this->expire == -1);
    }
}

class Context {
    public static $onlineUsers = [];
    public static $channelList = [];
    public static $bannedUsers = [];
    public static $invisibleUsers = [];

    public static function ForceChannelSwitch($user, $to) {
        if(Context::ChannelExists($to)) {
            $oldchan = $user->channel;

            if(!Modules::ExecuteRoutine("OnChannelDelete", [$user, Context::GetChannel($to), Context::GetChannel($oldchan)])) return;
            Message::HandleChannelSwitch($user, $to, $user->channel);
            unset(Context::GetChannel($user->channel)->users[$user->id]);
            Context::GetChannel($to)->users[$user->id] = Context::$onlineUsers[$user->id];
            Context::$onlineUsers[$user->id]->channel = $to;

            if(Context::GetChannel($oldchan)->channelType == CHANNEL_TEMP && Context::GetChannel($oldchan)->GetOwner()->id == $user->id)
                Context::DeleteChannel($oldchan);

            Modules::ExecuteRoutine("AfterChannelSwitch", [$user, Context::GetChannel($to), Context::GetChannel($oldchan)]);
        }
    }

    public static function SwitchChannel($user, $to, $pwd = "") {
        if($user->channel != $to) {
            if(Context::ChannelExists($to)) {
                if($pwd == Context::GetChannel($to)->password || $user->canModerate() || Context::GetChannel($to)->GetOwner()->id == $user->id) {
                    if(Context::GetChannel($to)->permissionLevel <= $user->getRank()) {
                        Context::ForceChannelSwitch($user, $to);
                        return;
                    } else Message::PrivateBotMessage(MSG_ERROR, "ipchan", array($to), $user);
                } else Message::PrivateBotMessage(MSG_ERROR, "ipwchan", array($to), $user);
            } else Message::PrivateBotMessage(MSG_ERROR, "nochan", array($to), $user);
        } // else Message::PrivateBotMessage(MSG_ERROR, "samechan", array($to), $user); // kind of extraneous
        $user->sock->send(Utils::PackMessage(5, ["2", $user->channel]));
    }

    public static function IsLobby($channel) {
        if(is_string($channel)) $channel = Context::GetChannel($channel);
        return $channel->name == Context::GetChannel(Utils::$chat["DEFAULT_CHANNEL"])->name;
    }

    public static function AddInvisibleUser($name, $color) {
        for($id = -2;;$id--) {
            if(!array_key_exists($id, Context::$onlineUsers) && !array_key_exists($id, Context::$invisibleUsers)) break;
        }
        Context::$invisibleUsers[$id] = new User($id, "", $name, $color, "6770\f1\f1\f1\f1\f1", null, false);
        foreach(Context::$onlineUsers as $user) {
            $user->sock->send(Utils::PackMessage(7, ["1", $id, $name, $color, "6770\f1\f1\f1\f1\f1", ]));
        }
        return Context::$invisibleUsers[$id];
    }

    public static function GetUserByID($id) {
        if(array_key_exists($id, Context::$onlineUsers)) return Context::$onlineUsers[$id];
        else return null;
    }

    public static function GetUserByName($name) {
        foreach(Context::$onlineUsers as $user) {
            if ($user->username == $name) return $user;
        }

        return null;
    }

    public static function GetUserBySock($sock) {
        foreach(Context::$onlineUsers as $user) {
            if($user->sock == $sock) return $user;
        }

        return null;
    }

    public static function GetAllChannels() {
        return join(Utils::$separator, Context::$channelList);
    }

    public static function GetChannel($name) {
        if(array_key_exists($name, Context::$channelList)) return Context::$channelList[$name];
        else return Context::$channelList[Utils::$chat["DEFAULT_CHANNEL"]];
    }

    public static function ChannelExists($name) {
        return array_key_exists($name, Context::$channelList);
    }

    public static function CreateChannel($channel) {
        if(is_string($channel)) $channel = new Channel($channel);

        if(!Context::ChannelExists($channel->name)) {
            if($channel->name[0] != "@" && $channel->name[0] != "*") {
                if(!Modules::ExecuteRoutine("OnChannelCreate", [$channel])) return Utils::FormatBotMessage(MSG_ERROR, "generr", []);
                Context::$channelList[$channel->name] = $channel;
                Message::HandleChannelCreation($channel);
                Database::CreateChannel($channel->name, $channel->password, $channel->permissionLevel);
                Modules::ExecuteRoutine("AfterChannelCreate", [$channel]);
                return "OK";
            } else return Utils::FormatBotMessage(MSG_ERROR, "inchan", []);
        } else return Utils::FormatBotMessage(MSG_ERROR, "nischan", [$channel->name]);
    }

    public static function RenameChannel($oldname, $newname) {
        if(Context::ChannelExists($oldname) && !Context::ChannelExists($newname)) {
            Context::$channelList[$newname] = clone Context::GetChannel($oldname);
            Context::$channelList[$newname]->name = $newname;
            if(!Modules::ExecuteRoutine("OnChannelModify", [Context::$channelList[$oldname], Context::$channelList[$newname]])) {
                unset(Context::$channelList[$newname]);
                return false;
            }
            Message::HandleChannelModification($newname, $oldname);
            Modules::ExecuteRoutine("AfterChannelModify", [Context::$channelList[$oldname], Context::$channelList[$newname]]);
            unset(Context::$channelList[$oldname]);
            return true;
        } else return false;
    }

    public static function ChangeChannelPassword($channel, $pwd) {
        if(is_string($channel)) $channel = Context::GetChannel($channel);
        $tmp = [clone $channel, clone $channel];
        $tmp[0]->password = trim($pwd) == "" ? "" : Utils::Hash(trim($pwd));
        if(!Modules::ExecuteRoutine("OnChannelModify", [$channel, $tmp[0]])) return;
        Context::$channelList[$channel->name] = $tmp[0];
        Message::HandleChannelModification(Context::$channelList[$channel->name]);
        Modules::ExecuteRoutine("AfterChannelModify", [$tmp[1], $channel]);
    }

    public static function ChangeChannelPermission($channel, $perm) {
        if(is_string($channel)) $channel = Context::GetChannel($channel);
        $tmp = [clone $channel, clone $channel];
        $tmp[0]->permissionLevel = $perm;
        if(!Modules::ExecuteRoutine("OnChannelModify", [$channel, $tmp[0]])) return;
        Context::$channelList[$channel->name] = $tmp[0];
        Message::HandleChannelModification(Context::$channelList[$channel->name]);
        Modules::ExecuteRoutine("AfterChannelModify", [$tmp[1], $channel]);
    }

    public static function DeleteChannel($channel) {
        if(is_string($channel)) $channel = Context::GetChannel($channel);
        if(!Modules::ExecuteRoutine("OnChannelDelete", [$channel]));
        foreach($channel->users as $user) Context::SwitchChannel($user, Utils::$chat["DEFAULT_CHANNEL"]);
        Message::HandleChannelDeletion($channel);
        Database::RemoveChannel($channel->name);
        unset(Context::$channelList[$channel->name]);
        Modules::ExecuteRoutine("AfterChannelDelete", [$channel]);
    }

    public static function Join($user) {
        if(!Modules::ExecuteRoutine("OnUserJoin", [$user])) return;
        Message::HandleJoin($user);
        Context::$onlineUsers[$user->id] = $user;
        Context::$channelList[Utils::$chat["DEFAULT_CHANNEL"]]->users[$user->id] = Context::$onlineUsers[$user->id];
        Database::Login($user);
        Modules::ExecuteRoutine("AfterUserJoin", [$user]);
    }

    public static function AllowUser($username, $sock) {
        foreach(Context::$onlineUsers as $user) {
            if($user->GetOriginalUsername() != $username) {
                if($sock == $user->sock) {
                    return "sockfail";
                }
            } else return "userfail";
        }
        return 0;
    }

    public static function CheckBan($id, $ip, $name) {
        foreach(Context::$bannedUsers as $ban) {
            if($ban->Check($id, $ip, $name)) return $ban->expire;
        }

        return false;
    }

    public static function ModifyUser($newuser) {
        $u = Context::GetUserByID($newuser->id);
        $u->Copy($newuser);
        if(!Modules::ExecuteRoutine("OnUserModify", [$u])) return;
        Message::HandleUserModification($u);
    }

    public static function KickUser($user, $by = null, $time = 0, $banip = false, $type = LEAVE_KICK) {
        if(!Modules::ExecuteRoutine("OnUserKick", [$user, $by == null ? Message::$bot : $by, &$time, &$banip])) return;
        Message::HandleKick($user, $time);
        if($time != 0) {
            $exp = $time < 0 ? -1 : (int)gmdate("U") + $time;
            Database::Ban($banip ? $user->sock->remoteAddress : null , $user->id, $user->GetOriginalUsername(), $exp);
            array_push(Context::$bannedUsers, new Ban($banip ? $user->sock->remoteAddress : null , $user->id, $user->GetOriginalUsername(), $exp));
        }
        $ip = $user->sock->remoteAddress;
        $user->sock->close();
        Context::Leave($user, $type);
        if($banip) Context::BanIP($ip, $time, $by, true);
        Modules::ExecuteRoutine("AfterUserKick", [$user, $by == null ? Message::$bot : $by, $time, $banip]);
    }

    public static function BanIP($ip, $time = -1, $by = null, $alreadybanned = false) {
        if(!$alreadybanned) {
            if(!Modules::ExecuteRoutine("OnBanIP", [&$ip, &$time, $by == null ? Message::$bot : $by])) return;
            $exp = $time < 0 ? -1 : (int)gmdate("U") + $time;
            array_push(Context::$bannedUsers, new Ban($ip, null, null, $exp));
            Database::Ban($ip, null, null, $exp);
        }

        foreach(Context::$onlineUsers as $user) {
            if(Utils::CheckIPAddresses($user->sock->remoteAddress, $ip)) {
                Message::HandleKick($user, $time);
                $user->sock->close();
                Context::Leave($user, LEAVE_KICK);
            }
        }

        if(!$alreadybanned)
            Modules::ExecuteRoutine("AfterBanIP", [$ip, $time, $by == null ? Message::$bot : $by]);
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

    public static function Leave($user, $type = LEAVE_NORMAL) {
        if(Context::GetChannel($user->channel)->channelType == CHANNEL_TEMP && Context::GetChannel($user->channel)->GetOwner()->id == $user->id)
            Context::DeleteChannel($user->channel);

        Database::Logout($user);
        Message::HandleLeave($user, $type);
        unset(Context::GetChannel($user->channel)->users[$user->id]);
        unset(Context::$onlineUsers[$user->id]);
    }
}