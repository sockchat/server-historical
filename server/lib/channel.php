<?php
namespace sockchat;
use \sockchat\User;

class Backlog {
    public $loglen = 10;
    public $logs = array();

    public function Log($user, $msg, $msgid) {
        array_push($this->logs, [gmdate("U"), clone $user, $msg, $msgid]);
        if(count($this->logs) > $this->loglen)
            $this->logs = array_slice($this->logs, 1);
    }

    public function GetAllLogStrings() {
        $retval = array();
        foreach($this->logs as $msg)
            array_push($retval, join(Utils::$separator, array($msg[0], $msg[1]->id, $msg[1]->username, $msg[1]->color, $msg[1]->permissions, $msg[2], $msg[3])));
        return $retval;
    }
}

class Channel {
    public $name;
    public $permissionLevel = 0;

    public $password = "";
    public $users = [];
    public $channelMods = []; // id list

    public $channelOwner = "";
    public $channelType = CHANNEL_PERM;

    public $log;

    public function __construct($name, $permissionLevel = 0, $password = "", $channelOwner = "", $channelType = CHANNEL_PERM) {
        $this->name = $name;
        $this->permissionLevel = $permissionLevel;
        $this->password = $password;
        $this->channelOwner = $channelOwner;
        $this->channelType = $channelType;
        $this->log = new Backlog();
    }

    public function GetAllUsers() {
        return join(Utils::$separator, $this->users);
    }
}