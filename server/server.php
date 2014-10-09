<?php
require __DIR__ ."/vendor/autoload.php";
use \Ratchet\MessageComponentInterface;
use \Ratchet\ConnectionInterface;
use \Ratchet\Server\IoServer;
use \Ratchet\Http\HttpServer;
use \Ratchet\WebSocket\WsServer;
use \Ratchet\Http\HttpRequestParser;
include("config.php");

class User {
    public $id;
    public $username;
    public $color;
    public $permissions;
    public $sock;

    public function __construct($id, $username, $color, $permissions, $sock) {
        $this->id = $id;
        $this->username = $username;
        $this->color = $color;
        $this->permissions = $permissions;
        $this->sock = $sock;
    }
}

class Chat implements MessageComponentInterface {
    protected $connectedUsers = array();
    protected $separator;
    protected $reqParser;
    protected $chat;

    public function __construct() {
        $this->separator = "\t";
        $this->reqParser = new HttpRequestParser;
        $GLOBALS["auth_method"][0] = $GLOBALS["chat"]["CAUTH_FILE"];
        $this->chat = $GLOBALS["chat"];
        echo "Server started.\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        // not used at the moment
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

    protected function PackMessage($id, $params) {
        return $id . $this->separator . join($this->separator, $params);
    }

    protected function Broadcast($msg) {
        foreach($this->connectedUsers as $user) {
            $user->sock->send($msg);
        }
    }

    protected function BroadcastMessage($user, $msg) {
        $this->Broadcast($this->PackMessage(2, array(date("U"), $user->id, $msg)));
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
        if(substr($this->getHeader($conn, "Origin"), -strlen($this->chat["HOST"])) == $this->chat["HOST"]) {
            $this->PackMessage(1, array());

            $parts = explode($this->separator, $msg);
            $id = $parts[0];
            $parts = array_slice($parts, 1);

            switch($id) {
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

                            $this->Broadcast($this->PackMessage(1, array(date("U"), $id, $this->Sanitize($aparts[1]), $aparts[2])));
                            $conn->send($this->PackMessage(1, array("y", date("U"), $id, $aparts[1], $aparts[2], $userstr)));

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
                                    // handle commands
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
                $this->Broadcast($this->PackMessage(3, array($user->id, $user->username, date("U"))));
                unset($this->connectedUsers[$user->id]);
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $err) {

    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    $chat["PORT"]
);

$server->run();