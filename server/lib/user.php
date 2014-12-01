<?php
namespace sockchat;

class User {
    public $id;
    public $channel;
    public $username;
    public $color;
    public $permissions;
    public $sock;
    public $ping;

    public function __construct($id, $channel, $username, $color, $permissions, $sock) {
        $this->id = $id;
        $this->channel = $channel;
        $this->username = $username;
        $this->color = $color;
        $this->permissions = explode("\t", $permissions);
        $this->sock = $sock;
        $this->ping = gmdate("U");
    }

    public function getRank() {
        return $this->permissions[0];
    }

    public function canModerate() {
        return $this->permissions[1] == "1";
    }

    public function canViewLogs() {
        return $this->permissions[2] == "1";
    }
}