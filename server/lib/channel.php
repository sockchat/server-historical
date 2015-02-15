<?php
namespace sockchat;
use \sockchat\User;

class Backlog {
    public static $loglen = 10;
    public $logs = array();

    public function Log($user, $msg, $msgid, $time = null, $flags = "10010") {
        array_push($this->logs, [$time == null ? gmdate("U") : $time, clone $user, $msg, $msgid, $flags]);
        if(count($this->logs) > Backlog::$loglen)
            $this->logs = array_slice($this->logs, 1);
    }

    public function GetAllLogStrings() {
        $retval = array();
        foreach($this->logs as $msg)
            array_push($retval, join(Utils::$separator, array($msg[0], $msg[1], $msg[2], $msg[3], "0", $msg[4])));
        //$retval = array_reverse($retval);
        return $retval;
    }
}

class Channel {
    public $name;
    public $permissionLevel = 0;

    public $password = "";
    public $users = [];
    public $channelMods = []; // id list

    public $channelOwner = null;
    public $channelType = CHANNEL_PERM;

    public $log;

    public function __construct($name, $password = "", $permissionLevel = 0, $channelOwner = null, $channelType = CHANNEL_PERM, $backlog = null) {
        $this->name = $name;
        $this->permissionLevel = $permissionLevel;
        $this->password = $password;
        $this->channelOwner = $channelOwner;
        $this->channelType = $channelType;
        $this->log = $backlog == null ? new Backlog() : $backlog;
    }

    public function GetOwner() {
        if($this->channelOwner != null) return $this->channelOwner;
        else return new User(-1, "", "", "", "", null);
    }

    public function GetAllUsers() {
        $arr = [];

        foreach(Context::$invisibleUsers as $user)
            array_push($arr, $user . Utils::$separator . "0");
        foreach($this->users as $user)
            array_push($arr, $user . Utils::$separator . "1");

        return (count($this->users) + count(Context::$invisibleUsers)) . Utils::$separator . join(Utils::$separator, $arr);
    }

    public function __toString() {
        return join(Utils::$separator, [$this->name, $this->password != "" ? "1" : "0", $this->channelType == CHANNEL_TEMP ? "1" : "0"]);
    }
}