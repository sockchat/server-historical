<?php
namespace sockchat;

class User {
    public $id;
    public $channel;
    public $originalData = [];
    public $dirtyname;
    public $username;
    public $color;
    public $permissions;
    public $permstr;
    public $sock;
    public $ping;
    protected $customParams = [];

    public function __construct($id, $channel, $username, $color, $permissions, $sock, $dirty = null) {
        $this->id = $id;
        $this->channel = $channel;
        $this->originalData = [$username, $color, $permissions];
        $this->username = $username;
        $this->color = $color;
        $this->permstr = $permissions;
        $this->permissions = explode("\f", $permissions);
        $this->sock = $sock;
        $this->ping = gmdate("U");
        $this->dirtyname = ($dirty == null) ? $this->username : $dirty;
    }

    public function SetParameter($key, $value) {
        $this->customParams[$key] = $value;
    }

    public function GetParameter($key) {
        if(array_key_exists($key, $this->customParams)) return $this->customParams[$key];
        else return null;
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

    public function GetRank() {
        return $this->permissions[0];
    }

    public function CanModerate() {
        return $this->permissions[1] == "1";
    }

    public function CanViewLogs() {
        return $this->permissions[2] == "1";
    }

    public function CanChangeNick() {
        return $this->permissions[3] == "1";
    }

    public function ChannelCreationPermission() {
        return $this->permissions[4];
    }

    public function __toString() {
        return join(Utils::$separator, array($this->id, $this->username, $this->color, $this->permstr));
    }
}