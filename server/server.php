<?php
namespace sockchat;
require __DIR__ ."/vendor/autoload.php";
use \Ratchet\MessageComponentInterface;
use \Ratchet\ConnectionInterface;
use \Ratchet\Server\IoServer;
use \Ratchet\Http\HttpServer;
use \Ratchet\WebSocket\WsServer;
include("config.php");

require "commands/generic_cmd.php";
foreach(glob("commands/*.php") as $fn) {
    if($fn != "commands/generic_cmd.php")
        include($fn);
}

class User {
    public $id;
    public $username;
    public $color;
    public $permissions;
    public $sock;
    public $ping;

    public function __construct($id, $username, $color, $permissions, $sock) {
        $this->id = $id;
        $this->username = $username;
        $this->color = $color;
        $this->permissions = $permissions;
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

class Chat implements MessageComponentInterface {
    public $connectedUsers = array();
    public $chatbot;
    protected $separator = "\t";
    protected $chat;

    public function __construct() {
        $GLOBALS["auth_method"][0] = $GLOBALS["chat"]["CAUTH_FILE"];
        $this->chat = $GLOBALS["chat"];
        $this->chatbot = new User(-1, "", "", "", null);
        echo "Server started.\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->checkPings();
    }

    protected function checkPings() {
        foreach($this->connectedUsers as $user) {
            if(gmdate("U") - $user->ping > $this->chat["MAX_IDLE_TIME"]) {
                $user->sock->close();
                $this->Broadcast($this->PackMessage(3, array($user->id, $user->username, gmdate("U"))));
                unset($this->connectedUsers[$user->id]);
            }
        }
    }

    protected function getHeader($sock, $name) {
        try {
            return (string)$sock->WebSocket->request->getHeader($name, true);
        } catch(\Exception $e) {
            return "";
        }
    }

    protected function allowUser($username, $sock) {
        foreach($this->connectedUsers as $user) {
            if($user->username == $username || $sock == $user->sock)
                return false;
        }
        return true;
    }

    public function PackMessage($id, $params) {
        return $id . $this->separator . join($this->separator, $params);
    }

    public function Broadcast($msg) {
        foreach($this->connectedUsers as $user) {
            $user->sock->send($msg);
        }
    }

    public function BroadcastMessage($user, $msg) {
        Chat::Broadcast(Chat::PackMessage(2, array(gmdate("U"), $user->id, $msg)));
    }

    protected function Sanitize($str) {
        return str_replace("\n", "<br/>", str_replace("\\","&#92;",htmlspecialchars($str, ENT_QUOTES)));
    }

    protected function GetFileContents($fname) {
        $fp = fopen($fname, "rb");
        $retval = stream_get_contents($fp);
        fclose($fp);
        return $retval;
    }

    public function onMessage(ConnectionInterface $conn, $msg) {
        $this->checkPings();
        if(substr($this->getHeader($conn, "Origin"), -strlen($this->chat["HOST"])) == $this->chat["HOST"]) {
            $this->PackMessage(1, array());

            $parts = explode($this->separator, $msg);
            $id = $parts[0];
            $parts = array_slice($parts, 1);

            switch($id) {
                case 0:
                    if(array_key_exists($parts[0], $this->connectedUsers))
                        $this->connectedUsers[$parts[0]]->ping = gmdate("U");
                    $conn->send($this->PackMessage(0, array("pong")));
                    break;
                case 1:
                    $arglist = "";
                    for($i = 0; $i < count($parts); $i++)
                        $arglist .= ($i==0?"?":"&") ."arg". ($i+1) ."=". urlencode($parts[$i]);
                    $aparts = $this->GetFileContents($this->chat['CHATROOT'] ."auth/". $GLOBALS['auth_method'][$this->chat['AUTH_TYPE']] . $arglist);

                    if($aparts != "reject" && trim($aparts) != "") {
                        $aparts = explode("\n", $aparts);
                        if($this->allowUser($aparts[1], $conn)) {
                            $id = 0;
                            if($this->chat["AUTOID"]) {
                                for($i = 1;; $i++) {
                                    if(!array_key_exists("".$i, $this->connectedUsers)) {
                                        $id = "".$i;
                                        break;
                                    }
                                }
                            } else $id = $aparts[0];

                            $userstr = "". count($this->connectedUsers);
                            foreach($this->connectedUsers as $user)
                                $userstr .= $this->separator . join($this->separator, array($user->id, $user->username, $user->color));

                            $this->Broadcast($this->PackMessage(1, array(gmdate("U"), $id, $this->Sanitize($aparts[1]), $aparts[2])));
                            $conn->send($this->PackMessage(1, array("y", gmdate("U"), $id, $aparts[1], $aparts[2], $userstr)));

                            $this->connectedUsers[$id] = new User($id, $this->Sanitize($aparts[1]), $aparts[2], $aparts[3], $conn);
                        } else {
                            $conn->send(1, array("n","Username in use!"));
                        }
                    }
                    break;
                case 2:
                    if(array_key_exists($parts[0], $this->connectedUsers)) {
                        if($this->connectedUsers[$parts[0]]->sock == $conn) {
                            if(trim($parts[1]) != "") {
                                if(trim($parts[1])[0] != "/") {
                                    $this->BroadcastMessage($this->connectedUsers[$parts[0]], $this->Sanitize($parts[1]));
                                } else {
                                    $parts[1] = substr(trim($parts[1]), 1);
                                    $cmdparts = explode(" ", $parts[1]);
                                    $cmd = str_replace(".","",$cmdparts[0]);
                                    $cmdparts = array_slice($cmdparts, 1);
                                    $user = $this->connectedUsers[$parts[0]];
                                    for($i = 0; $i < count($cmdparts); $i++)
                                        $cmdparts[$i] = $this->Sanitize($cmdparts[$i]);

                                    if(strtolower($cmd) != "generic_cmd" && file_exists("commands/". strtolower($cmd) .".php"))
                                        eval("\\sockchat\\cmds\\". strtolower($cmd) ."::doCommand(\$this, \$user, \$cmdparts);");
                                    else
                                        $conn->send($this->PackMessage(2, array(gmdate("U"), -1, "<i><span style='color: red;'>Error: Command not found!</span></i>")));
                                }
                            }
                        }
                    }
                    break;
            }
        } else
            $conn->close();
    }

    public function onClose(ConnectionInterface $conn) {
        echo $conn->remoteAddress ." has disconnected\n";
        foreach($this->connectedUsers as $user) {
            if($user->sock == $conn) {
                echo "found user ". $user->username .", dropped\n";
                $this->Broadcast($this->PackMessage(3, array($user->id, $user->username, gmdate("U"))));
                unset($this->connectedUsers[$user->id]);
            }
        }
        $this->checkPings();
    }

    public function onError(ConnectionInterface $conn, \Exception $err) {
        $this->checkPings();
        echo "Error on ". $conn->remoteAddress .": ". $err ."\n";
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    $GLOBALS["chat"]["PORT"]
);

$server->run();