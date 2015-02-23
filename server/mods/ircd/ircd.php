<?php
namespace sockchat\mods\ircd;
use \sockchat\mods\GenericMod;
use \sockchat\Context;
use \sockchat\User;
use \sockchat\Utils;
use \sockchat\Message;
use \sockchat\Channel;

class FakeSocket {
    public $user;
    public $remoteAddress;

    public function __construct($ip) {
        $this->remoteAddress = $ip;
    }

    public function send($data) {
        Main::OnSendData($data, $this->user);
    }
}

class Main extends GenericMod {
    public static function OnPacketReceive($conn, &$pid, &$data) {
        if($pid == "IRCD") {
            switch($data[0]) {
                case 1:
                    $chans = [];
                    foreach(Context::$channelList as $name => $channel) {
                        array_push($chans, "#". (Context::IsLobby($name) ? "@default" : $name));
                    }
                    $conn->send(Utils::PackMessage(1, $chans));
                    break;
            }
            return false;
        }
    }

    public static function OnSendData($data, $user) {

    }
}