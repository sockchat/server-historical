<?php
namespace sockchat;
use \sockchat\User;

class Backlog {
    public $loglen = 10;
    public $logs = array();

    public function Log($user, $msg) {
        array_push(Backlog::$logs, [clone $user, $msg]);
        if(count(Backlog::$logs) > Backlog::$loglen)
            Backlog::$logs = array_slice(Backlog::$logs, 1);
    }
}

class Channel {
    public $name;
    public $permissionLevel = 0;

    public $password = "";
    public $users = [];

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
}