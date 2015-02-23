<?php
namespace sockchat\IRCd;

class SockChat {
    public static $sock;
    public static $host;
    public static $ip;
    public static $port;

    public static function Init($ip, $port) {
        self::$sock = socket_create(AF_INET, SOCK_STREAM, 0);
        self::$ip = gethostbyname($ip);
        self::$host = $ip;
        self::$port = $port;
    }

    private static function GenerateKey() {
        $str = "uvplgbhbcujylrcl";
        for($i = 0; $i < 16; $i++)
            $str[$i] = rand(0, 255);
        return base64_encode($str);
    }

    public static function Connect($origin) {
        socket_set_block(self::$sock);
        if(@socket_connect(self::$sock, self::$ip, self::$port) === false) return false;
        $to = self::$host . (self::$port != 80 ? ":". self::$port : "");
        $headers = "GET / HTTP/1.1\r\n".
                   "Host: {$to}\r\n".
                   "Connection: Upgrade\r\n".
                   "Upgrade: websocket\r\n".
                   "Origin: {$origin}\r\n".
                   "Sec-Websocket-Version: 13\r\n".
                   "Sec-Websocket-Key: ". self::GenerateKey() ."\r\n\r\n";
        socket_send(self::$sock, $headers, strlen($headers), 0);
        @socket_recv(self::$sock, $buf, 2048, 0);
        socket_set_nonblock(self::$sock);
        return true;
    }

    public static function Mask($data, $key, $offset = 0) {
        $ret = "";
        for ($i = 0; $i < strlen($data); $i++) {
            $j = ($i + $offset) % 4;
            $ret .= chr(ord($data[$i]) ^ ord($key[$j]));
        }
        return $ret;
    }

    public static function EncodeFrame($data) {
        $ret = chr(0x81);
        $len = strlen($data);
        if($len < 126)
            $ret .= chr(0x80 + $len);
        else if($len < 65536)
            $ret .= chr(254) . pack("n", $len);
        else
            $ret .= chr(255) . pack("N", 0) . pack("N", $len);

        $mask = pack("N", rand(0, pow(2,28)));
        $ret .= $mask;
        return $ret . self::Mask($data, $mask);
    }

    public static function Recv() {
        if(@socket_recv(self::$sock, $buf, 2048, 0) !== false)
            return substr($buf, $buf < 126 ? 2 : ($buf == 126 ? 4 : 10 ));
        else return null;
    }

    public static function InterpretMessage($msg, &$id) {
        $args = explode($msg, "\t");
        $id = $args[0];
        return array_slice($args, 1);
    }

    public static function Send($data) {
        socket_write(self::$sock, self::EncodeFrame("IRCD\t{$data}"));
    }
}

class User {
    public $sock;
    public $channels = [];
    public $username = null;
    public $hostname = "localhost";
    public $nick = null;
    public $desc = null;

    public function __construct($conn) {
        $this->sock = $conn;
    }

    public function Send($data) {
        $data = str_replace(["\r", "\n"], ["", ""], $data) ."\r\n";
        socket_write($this->sock, $data, strlen($data));
    }

    public function Notice($msg) {
        $this->Send(":". IRC::$server ." NOTICE ". $this->nick ." :". $msg);
    }

    public function SendGlobal($data) {
        $this->Send(":". IRC::$server ." ". $data);
    }

    public function Join($channel) {
        if(IRC::Join($this, $channel)) array_push($this->channels, $channel);
    }

    public function GetRepresentation() {
        return $this->nick ."!". $this->username ."@". $this->hostname;
    }
}

class IRC {
    public static $sock;
    public static $server;
    public static $users;

    public static function Init($name, $port) {
        self::$server = $name;
        self::$users = [];
        self::$sock = socket_create(AF_INET, SOCK_STREAM, 0);
        socket_set_option(self::$sock, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind(self::$sock, "0.0.0.0", $port);
        socket_listen(self::$sock);
        socket_set_nonblock(self::$sock);
    }

    public static function Accept() {
        if(($conn = @socket_accept(self::$sock)) !== false) {
            socket_set_nonblock($conn);
            array_push(self::$users, new User($conn));
            return true;
        } else return false;
    }

    public static function Recv() {
        $ret = [];
        foreach(self::$users as $user) {
            if(@socket_recv($user->sock, $data, 2048, 0) !== false) {
                array_push($ret, [$user, $data]);
            }
        }
        return $ret;
    }

    public static function Join(User $user, $channel) {
        $created = false;
        if(!array_key_exists($channel, IRC::$channels)) {
            // TODO add creating channels
            $user->Notice("Channel {$channel} does not exist!");
            return false;
        }
        if(in_array($channel, $user->channels)) {
            $user->Notice("You are already in {$channel}!");
            return false;
        }
        $chan = IRC::$channels[$channel];
        $chan->Add($user);
        $chan->Broadcast(":". $user->GetRepresentation() ." JOIN {$channel}");
        if($created)
            $user->SendGlobal("MODE {$channel} +nt");
        $user->SendGlobal(($chan->topic == null ? "331" : "332") ." ". $user->nick ." {$channel} :". ($chan->topic == null ? "No topic is set." : $chan->topic));
        foreach($chan->users as $u)
            $user->SendGlobal("353 ". $user->nick ." = {$channel} :". $u->nick);
        $user->SendGlobal("366 ". $user->nick ." {$channel} :End of /NAMES list");
        return true;
    }
}

SockChat::Init("127.0.0.1", 12120);
while(!SockChat::Connect("127.0.0.1"));
SockChat::Send("1");

IRC::Init("SircChat", 6667);

while(true) {
    if(($data = SockChat::Recv()) != null) {
        $args = SockChat::InterpretMessage($data, $id);
        switch($id) {
            case 1:

                break;
        }
    }

    IRC::Accept();
    foreach(IRC::Recv() as $recv) {
        $msgs = explode("\r\n", $recv[1]);
        foreach($msgs as $msg) {
            $msg = trim($msg);
            $tmp = $msg;

            if($msg != "") {
                $prefix = "";

                if ($msg[0] == ":") {
                    $parts = explode(" ", $msg, 2);
                    $prefix = $parts[0];
                    $msg = count($parts) > 1 ? $parts[1] : "";
                }

                $parts = explode(" ", $msg, 2);
                $cmd = $parts[0];
                $msg = count($parts) > 1 ? $parts[1] : "";

                $parts = explode(" :", $msg, 2);
                $msg = $parts[0];
                $trailing = count($parts) > 1 ? $parts[1] : null;

                $args = [];
                if ($msg != "") $args = array_merge(explode(" ", $msg));
                if ($trailing != null) array_push($args, $trailing);

                if (preg_match("/[0-9][0-9][0-9]/", $cmd) === 1) $cmd = "n" . $cmd;

                //echo $tmp ."\n";
                ParseLine($recv[0], $prefix, strtolower($cmd), $args);
            }
        }
    }

    usleep(100);
}

function CleanNick($nick) {
    return str_replace([":", " ", "!", "@", "#"], ["", "", "", "", ""], $nick);
}

function ParseLine(User $user, $prefix, $cmd, $args) {
    switch($cmd) {
        case "ping":
            if(!CheckArgs($user, 1, 1, $args)) break;
            $user->SendGlobal("PONG ". IRC::$server ." :". $args[0]);
            break;
        case "nick":
            if(!CheckArgs($user, 1, 1, $args)) break;
            if($user->nick == null)
                $user->nick = CleanNick($args[0]);
            else {
                // TODO on change after first join
            }
            break;
        case "user":
            if(!CheckArgs($user, 1, 4, $args)) break;
            if($user->username != null) {
                $user->Send("NOTICE AUTH :User info cannot be changed while logged in.");
                break;
            }
            $user->username = $args[0];
            $user->desc = count($args) > 3 ? $args[3] : "(no description)";

            $user->SendGlobal("001 ". $user->nick ." :Welcome to ". IRC::$server ."!");
            $user->SendGlobal("004 ". $user->nick ." ". IRC::$server ." SircChat");
            $user->SendGlobal("375 ". $user->nick ." :- ". IRC::$server ." Message of the Day -");
            $user->SendGlobal("372 ". $user->nick ." :Welcome to ". IRC::$server ."!");
            $user->SendGlobal("376 ". $user->nick ." :End of MOTD.");
            break;
        case "join":
            if(!CheckArgs($user, 1, 2, $args)) break;
            if(count($args) == 2)
                $user->Notice("This server does not support channel keys. Ignoring...");
            $channels = explode(",", $args[0]);
            foreach($channels as $channel) {
                if($channel[0] == "#")
                    $user->Join($channel);
                else
                    $user->Notice("Channel names must start with #.");
            }
            break;
        case "who":
            if(!CheckArgs($user, 0, 2, $args)) break;
            if(count($args) > 1) $user->Notice("Filtering by operator in WHO is not supported. Ignoring...");

            $search = (count($args) > 0) ? $args[0] : "";
            if(array_key_exists($search, IRC::$channels)) {
                foreach(IRC::$channels[$search]->users as $u)
                    $user->SendGlobal("352 ". $user->nick ." {$search} ". $u->username ." ". $u->hostname ." ". IRC::$server ." ". $u->nick ." H :0 ". $u->desc);
            } else $user->Notice("WHO with nonchannel arguments is not supported. Ignoring...");
            $user->Send("315 ". $user->nick ." {$search} :End of /WHO list");
            break;
        case "mode":
            if(!CheckArgs($user, 0, 2, $args)) break;
            switch(count($args)) {
                case 1:
                    if($args[0][0] == "#")
                        $user->SendGlobal("324 ". $user->nick ." ". $args[0] ." +nt");
                    else
                        $user->Notice("User mode querying not supported.");
                    break;
                case 2:
                    switch($args[1]) {
                        case "+b":
                            if($args[0][0] == "#") {
                                $user->SendGlobal("368 ". $user->nick ." ". $args[0] ." :End of channel ban list");
                            } else $user->Notice("+b for users not supported.");
                            break;
                        case "+e":
                            if($args[0][0] == "#") {
                                $user->SendGlobal("349 ". $user->nick ." ". $args[0] ." :End of channel exception list");
                            } else $user->Notice("+e for users not supported.");
                            break;
                        default:
                            $user->Notice("Mode ". $args[1] ." not supported.");
                            break;
                    }
                    break;
                default:
                    $user->Notice("Specific modes not supported.");
                    break;
            }
            break;
        case "privmsg":
            if(!CheckArgs($user, 2, 2, $args)) break;
            foreach(explode(",", $args[0]) as $to) {
                if($to[0] == "#") {
                    if(!array_key_exists($to, IRC::$channels))
                        $user->Notice("Channel does not exist!");
                    else if(!in_array($to, $user->channels)) {
                        var_dump($user->channels);
                        $user->Notice("You are not in {$to}!");
                    } else
                        IRC::$channels[$to]->Broadcast(":". $user->GetRepresentation() ." PRIVMSG {$to} :". $args[1], $user);
                } else {
                    // TODO sock chat passthrough
                }
            }
            break;
        default:
            // TODO sock chat passthrough
            $user->Notice("Command not recognized.");
            break;
    }
}

function CheckArgs($user, $min, $max, $args) {
    if (count($args) >= $min && count($args) <= $max) return true;
    else {
        $user->Notice("You fucked up.");
        return false;
    }
}