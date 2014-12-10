<?php

namespace sockchat;

define("AUTH_FETCH", 1);
define("AUTH_CONFIRM", 2);

define("USER_NORMAL", "0");
define("USER_MODERATOR", "1");

define("LOGS_DISABLED", "0");
define("LOGS_ENABLED", "1");

define("NICK_DISABLED", "0");
define("NICK_ENABLED", "1");

define("CHANNEL_CREATE_DISABLED", "0");
define("CHANNEL_CREATE_TEMP", "1");
define("CHANNEL_CREATE_PERM", "2");

class Auth {
    protected $args = [];
    protected $user = [];
    protected $perms = [[],[]];
    protected $accept = true;

    public function GetPageType() {
        return isset($_GET["arg1"]) ? AUTH_CONFIRM : AUTH_FETCH;
    }

    public function AppendArguments($in) {
        if(!is_array($in)) $in = [$in];
        $this->args = array_merge($this->args, $in);
    }

    public function SetUserData($id, $username, $color) {
        $this->user = [$id, $username, $color];
    }

    public function SetCommonPermissions($rank, $usertype, $viewlogs, $changenick, $createchannel) {
        $this->perms[0] = [$rank, $usertype, $viewlogs, $changenick, $createchannel];
    }

    public function SetCustomPermissions($permarr) {
        $this->perms[1] = is_array($permarr) ? $permarr : [$permarr];
    }

    public function Accept() {
        $this->accept = true;
    }

    public function Deny() {
        $this->accept = false;
    }

    public function Serve() {
        header("Access-Control-Allow-Origin: localhost");
        if($this->GetPageType() == AUTH_FETCH)
            echo $this->accept ? "yes\f". implode("\f", $this->args) : "no";
        else {
            echo $this->accept ? "yes" . implode("\n", $this->user) . "\n" . implode("\t", $this->perms[0]) . ($this->perms[1] == [] ? "" : "\t". implode("\t", $this->perms[1])) : "no";
        }
    }
}