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
        $args = explode("\t", $msg);
        $id = $args[0];
        return array_slice($args, 1);
    }

    public static function Send($data) {
        socket_write(self::$sock, self::EncodeFrame("IRCD\t{$data}"));
    }

    public static function GetResponse($data) {
        socket_set_block(self::$sock);
        self::Send($data);
        $get = self::Recv();
        socket_set_nonblock(self::$sock);
        return $get;
    }
}

class User {
    public $sock;
    public $channels = [];
    public $id;
    public $username = null;
    public $hostname = "localhost";
    public $nick = null;
    public $verified = false;

    public function __construct($conn, $id = null) {
        $this->sock = $conn;
        $this->id = $id;
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

    public function Register() {
        socket_getpeername($this->sock, $ip);
        $data = explode("\t", SockChat::GetResponse(PackMsg([4, $this->username, $ip])));
        if($data[1] == "yes") {
            $this->id = $data[2];
            $this->username = $this->nick = $data[3];
            self::Join("#@default");
        } else socket_close($this);
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
            for($i = -2;;$i--) {
                if(self::GetUserById($i) == null) break;
            }
            array_push(self::$users, new User($conn, $i));
            return true;
        } else return false;
    }

    public static function Recv() {
        $ret = [];
        foreach(self::$users as $user) {
            if(@socket_recv($user->sock, $data, 2048, 0) !== false) {
                array_push($ret, [$user, $data]);
            } else {
                $err = socket_last_error($user->sock);
                if($err != 0 && $err != 11 && $err != 115)
                    self::HandleLeave($user);
            }
        }
        return $ret;
    }

    public static function Broadcast($msg, $channel, $global = false) {
        foreach(self::$users as $user) {
            if(in_array($channel, $user->channels)) {
                if(!$global)
                    $user->Send($msg);
                else
                    $user->SendGlobal($msg);
            }
        }
    }

    public static function HandleLeave($user) {
        SockChat::Send("5\t". $user->id);
        foreach(self::$users as $k => $u) {
            if($u->id == $user->id) {
                socket_close($u->sock);
                unset(self::$users[$k]);
                break;
            }
        }
    }

    public static function GetUserById($id) {
        foreach(self::$users as $user) {
            if($user->id == $id) return $user;
        }
        return null;
    }

    public static function GetUsersInChannel($channel) {
        $ret = [];
        foreach(self::$users as $user) {
            if(in_array($channel, $user->channels))
                array_push($ret, $user);
        }
        return $ret;
    }

    public static function Join(User $user, $channel) {
        $created = false;
        if(in_array($channel, $user->channels)) {
            $user->Notice("You are already in {$channel}!");
            return false;
        }

        if(explode("\t", SockChat::GetResponse(PackMsg([2, $user->id, $user->username, $channel])))[1] == "no") {
            $user->Notice("Could not join channel.");
            return false;
        }

        $user->Send(":". $user->GetRepresentation() ." JOIN {$channel}", $channel);
        if($created)
            $user->SendGlobal("MODE {$channel} +nt");
        $user->SendGlobal("332 ". $user->nick ." {$channel} :Discuss stuff.");

        echo SockChat::GetResponse(PackMsg([3, $channel]));
        $users = array_slice(explode("\t", SockChat::GetResponse(PackMsg([3, $channel]))), 1);
        foreach($users as $u) {
            if(trim($u) != "") {
                $data = explode("\n", $u);
                $user->SendGlobal("353 " . $user->nick . " = {$channel} :" . $data[0]);
            }
        }

        $user->SendGlobal("366 ". $user->nick ." {$channel} :End of /NAMES list");
        return true;
    }
}

function PackMsg($arr) {
    return implode("\t", $arr);
}

SockChat::Init("127.0.0.1", 12120);
while(!SockChat::Connect("127.0.0.1"));
SockChat::Send("1");

IRC::Init("SircChat", 6667);

while(true) {
    if(($data = SockChat::Recv()) != null) {
        echo $data;
        $args = SockChat::InterpretMessage($data, $id);
        switch($id) {
            case 2:
                var_dump($args);
                if(($to = IRC::GetUserById($args[1])) != null) {
                    $to->Send(":". $args[0] ." PRIVMSG ". $args[2] ." :". $args[3]);
                }
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
        case "nickserv":
            $args = array_slice($args, 1);
        case "pass":
        case "auth":
            if(!CheckArgs($user, 1, 1, $args)) break;
            if(!$user->verified) {
                $data = explode("\t", SockChat::GetResponse(PackMsg([1, $user->username, $args[0]])));
                if ($data[1] == "yes") {
                    $user->id = $data[2];
                    $user->verified = true;
                    $user->Notice("Welcome to the chat, " . $data[3] . "!");
                    $user->Register();
                    //$user->SendGlobal("221 ". $user->nick ." ". $user->nick);
                } else $user->Notice("Your authentication was rejected.");
            } else $user->Notice("You are already authenticated!");
            break;
        case "ping":
            if(!CheckArgs($user, 1, 1, $args)) break;
            $user->SendGlobal("PONG ". IRC::$server ." :". $args[0]);
            break;
        case "nick":
            if(!CheckArgs($user, 1, 1, $args)) break;
            if($user->nick == null)
                null;
                //$user->nick = CleanNick($args[0]);
            else {
                // TODO on change after first join
            }
            break;
        case "user":
            if(!CheckArgs($user, 1, 4, $args)) break;
            /*if($user->username != null) {
                $user->Send("NOTICE AUTH :User info cannot be changed while logged in.");
                break;
            }
            $user->username = $args[0];
            $user->desc = count($args) > 3 ? $args[3] : "(no description)";*/

            $user->username = $user->nick = $args[0];

            $user->SendGlobal("001 ". $user->nick ." :Welcome to ". IRC::$server ."!");
            $user->SendGlobal("004 ". $user->nick ." ". IRC::$server ." SircChat");
            $user->SendGlobal("375 ". $user->nick ." :- ". IRC::$server ." Message of the Day -");
            $user->SendGlobal("372 ". $user->nick ." :Welcome to ". IRC::$server ."!");
            /**/
            $user->SendGlobal("376 ". $user->nick ." :End of MOTD.");

            echo SockChat::GetResponse(PackMsg([0, $args[0]]));
            $response = explode("\t", SockChat::GetResponse(PackMsg([0, $args[0]])))[1];
            if($response == "yes") {
                $user->Notice("NOTE: You must authenticate yourself before performing any chat functions.");
                $user->Notice("You must have generated a session key on the web chat client to authenticate.");
                $user->Notice("To authenticate, use '/auth password' where the password is your session key password.");
            } else if($response == "no") {
                $user->verified = true;
                $user->Notice("Welcome to the chat, " . $user->nick . "!");
                $user->Register();
            } else {
                $user->Notice("Username ". $user->username ." is not recognized by this server!");
                socket_close($user->sock);
            }
            break;
        case "join":
            if(!CheckArgs($user, 1, 2, $args)) break;
            if(!CheckValid($user)) break;
            $channels = explode(",", $args[0]);
            foreach($channels as $channel) {
                if($channel[0] == "#")
                    $user->Join($channel);
                else
                    $user->Notice("Channel names must start with #.");
            }
            break;
        case "who":
            /*if(!CheckArgs($user, 0, 2, $args)) break;
            if(count($args) > 1) $user->Notice("Filtering by operator in WHO is not supported. Ignoring...");

            $search = (count($args) > 0) ? $args[0] : "";
            if(array_key_exists($search, IRC::$channels)) {
                foreach(IRC::$channels[$search]->users as $u)
                    $user->SendGlobal("352 ". $user->nick ." {$search} ". $u->username ." ". $u->hostname ." ". IRC::$server ." ". $u->nick ." H :0 ". $u->desc);
            } else $user->Notice("WHO with nonchannel arguments is not supported. Ignoring...");
            $user->Send("315 ". $user->nick ." {$search} :End of /WHO list");*/
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
            //$user->Notice("Command not recognized.");
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

function CheckValid(User $user) {
    if($user->verified) return true;
    else {
        $user->Notice("You must authenticate yourself to do this!");
        return false;
    }
}