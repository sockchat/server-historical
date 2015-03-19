<?php
namespace sockchat\mods\ircd;
use \sockchat\mods\GenericMod;
use \sockchat\Context;
use \sockchat\User;
use \sockchat\Utils;
use \sockchat\Message;
use \sockchat\Channel;
use \sockchat\Auth;
use \sockchat\Modules;

class FakeSocket {
    public $user;
    public $remoteAddress;
    public $isFake = true;

    public function __construct($ip, $user) {
        $this->remoteAddress = $ip;
        $this->user = $user;
    }

    public function send($data) {
        Main::OnSendData($data, $this->user);
    }

    public function close() {
        Main::OnConnClose($this->user);
    }
}

class Main extends GenericMod {
    public static $sock;

    public static function Init() {
        Context::$channelList["@IRCD"] = new Channel("@IRCD", "", 999999);
    }

    public static function OnPacketReceive($conn, &$pid, &$data) {
        if($pid == "IRCD" && $conn->remoteAddress == "127.0.0.1") {
            self::$sock = $conn;
            switch($data[0]) {
                case 0:
                    if(Auth::Validate(null, $data[1]) != null) {
                        if (Auth::Reserved(null, $data[1]))
                            $out = "yes";
                        else $out = "no";
                    } else $out = "exit";
                    $conn->send(Utils::PackMessage(1, [$out]));
                    break;
                case 1:
                    $user = \sockchat\mods\session\Main::CheckSession($data[1], $data[2]);
                    if($user != null)
                        $conn->send(Utils::PackMessage(1, ["yes", $user->id, $user->username]));
                    else
                        $conn->send(Utils::PackMessage(1, ["no"]));
                    break;
                case 2:
                    $chan = substr($data[3], 1);
                    $chan = $chan == "@default" ? $GLOBALS["chat"]["DEFAULT_CHANNEL"] : $chan;
                    if(Context::ChannelExists($chan))
                        $conn->send(Utils::PackMessage(2, ["yes"]));
                    else
                        $conn->send(Utils::PackMessage(2, ["no"]));
                    break;
                case 3:
                    $chan = substr($data[1], 1);
                    $chan = $chan == "@default" ? $GLOBALS["chat"]["DEFAULT_CHANNEL"] : $chan;
                    if(($chan = Context::GetChannel($chan)) != null) {
                        $out = ["ChatBot"];
                        foreach($chan->users as $user)
                            array_push($out, $user->GetOriginalUsername() ."\n". $user->username);
                        foreach(Context::$invisibleUsers as $user)
                            array_push($out, $user->GetOriginalUsername() ."\n". $user->username);
                        $conn->send(Utils::PackMessage(3, $out));
                    } else $conn->send(Utils::PackMessage(3, [""]));
                    break;
                case 4:
                    if(($u = Auth::Validate(null, $data[1])) != null) {
                        if($GLOBALS["chat"]["AUTOID"]) {
                            for($i = 1 ;; $i++) {
                                if(Context::GetUserByID($i) == null) {
                                    $u->id = $i;
                                    break;
                                }
                            }
                        }
                        $u->ping = PHP_INT_MAX;
                        $u->sock = new FakeSocket($data[2], $u);
                        //if(!Modules::ExecuteRoutine("OnUserJoin", [$u])) return;
                        Context::Join($u);
                        $conn->send(Utils::PackMessage(4, ["yes", $u->id, $u->username]));
                    } else $conn->send(Utils::PackMessage(4, ["no"]));
                    break;
            }
            return false;
        }
    }

    public static function OnSendData($data, $user) {
        $parts = explode(Utils::$separator, $data);
        switch($parts[0]) {
            case "2":
                if($parts[2] == $user->id) break;

                if($parts[2] == -1)
                    $from = Message::$bot;
                else if($parts[2] < 0)
                    $from = Context::$invisibleUsers[$parts[2]];
                else
                    $from = Context::GetUserByID($parts[2]);

                $msg = $parts[3];
                $msg = preg_replace("/(<([^>]+)>)/i", "", $msg);
                $msg = preg_replace("/(\\[([^\\]]+)\\])/i", "", $msg);

                if($parts[6] == "0")
                    $to = "#". ($parts[7] == $GLOBALS["chat"]["DEFAULT_CHANNEL"] ? "@default" : $parts[7]);
                else {
                    if($parts[5][5] == "0")
                        $to = "#@all";
                    else
                        $to = "you";
                }

                self::$sock->send(Utils::PackMessage(2, [$from->username ."!". $from->GetOriginalUsername() ."@localhost", $user->id, $to, $msg]));
                break;
        }
    }

    public static function OnConnClose($user) {

    }
}