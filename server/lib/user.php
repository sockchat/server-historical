<?php
namespace sockchat;

class User {
    public $id;
    public $channel;
    public $originalData = [];
    public $username;
    public $color;
    public $permissions;
    public $permstr;
    public $sock;
    public $ping;

    public function __construct($id, $channel, $username, $color, $permissions, $sock) {
        $this->id = $id;
        $this->channel = $channel;
        $this->originalData = [$username, $color, $permissions];
        $this->username = $username;
        $this->color = $color;
        $this->permstr = $permissions;
        $this->permissions = explode("\f", $permissions);
        $this->sock = $sock;
        $this->ping = gmdate("U");
    }

    public function GetOriginalUsername() {
        return $this->originalData[0];
    }

    public function GetOriginalColor() {
        return $this->originalData[1];
    }

    public function GetOriginalPermissionString() {
        return $this->originalData[2];
    }

    public function Copy($user) {
        $this->username = $user->username;
        $this->color = $user->color;
        $this->permstr = $user->permstr;
        $this->permissions = $user->permissions;
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

    public function canChangeNick() {
        return $this->permissions[3] == "1";
    }

    public function channelCreationPermission() {
        return $this->permissions[4];
    }

    public function __toString() {
        return join(Utils::$separator, array($this->id, $this->username, $this->color, $this->permstr));
    }
}