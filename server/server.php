<?php
namespace sockchat;
require __DIR__ ."/vendor/autoload.php";
use \Ratchet\MessageComponentInterface;
use \Ratchet\ConnectionInterface;
use \Ratchet\Server\IoServer;
use \Ratchet\Http\HttpServer;
use \Ratchet\WebSocket\WsServer;
use \SQLite3;

include("lib/constants.php");
include("config.php");
include("lib/utils.php");
include("lib/user.php");
include("lib/context.php");
include("lib/channel.php");
include("lib/msg.php");

require "commands/generic_cmd.php";
foreach(glob("commands/*.php") as $fn) {
    if($fn != "commands/generic_cmd.php")
        include($fn);
}

class Database {
    static protected $conn;

    static public function init() {
        if(file_exists("chat.db"))
            Database::$conn = new SQLite3("chat.db");
        else {
            Database::$conn = new SQLite3("chat.db");
            Database::$conn->query("CREATE TABLE `logs` (`id` INTEGER PRIMARY KEY AUTOINCREMENT,`timestamp` INTEGER,`username` TEXT,`message` TEXT);CREATE TABLE `bans` (`uid` INTEGER,`username` TEXT,`ip` TEXT,`expiration` INTEGER);");
        }
    }

    static public function query($str) {
        return Database::$conn->query($str);
    }

    static public function logMessage($time, $username, $msg) {
        Database::query("INSERT INTO `logs` (`timestamp`, `username`, `message`) VALUES (". $time .", '". $username ."','". $msg ."')");
    }

    //static public function
}

class Chat implements MessageComponentInterface {
    public $connectedUsers = array();
    public $chatbot;
    protected $separator = "\t";
    protected $chat;
    protected $msgid = 1;

    public function __construct() {
        $GLOBALS["auth_method"][0] = $GLOBALS["chat"]["CAUTH_FILE"];
        Utils::$chat = $GLOBALS["chat"];
        Message::$bot = new User("-1", "", "bot", "inherit", "", null);
        Context::CreateChannel(new Channel(Utils::$chat["DEFAULT_CHANNEL"]));

        echo "Server started.\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        Context::CheckPings();
    }

    public function onMessage(ConnectionInterface $conn, $msg) {
        Context::CheckPings();
        if(substr(Utils::GetHeader($conn, "Origin"), -strlen($this->chat["HOST"])) == $this->chat["HOST"]) {
            $parts = explode($this->separator, $msg);
            $id = $parts[0];
            $parts = array_slice($parts, 1);

            switch($id) {
                case 0:
                    if(($u = Context::GetUserByID($parts[0])) != null) {
                        $u->ping = gmdate("U");
                        $conn->send(Utils::PackMessage(0, array("pong")));
                    }
                    break;
                case 1:
                    if(!Context::DoesSockExist($conn)) {
                        $arglist = "";
                        for($i = 0; $i < count($parts); $i++)
                            $arglist .= ($i==0?"?":"&") ."arg". ($i+1) ."=". urlencode($parts[$i]);
                        $aparts = file_get_contents($this->chat['CHATROOT'] ."/auth/". $GLOBALS['auth_method'][$this->chat['AUTH_TYPE']] . $arglist);

                        if(substr($aparts, 0, 3) == "yes") {
                            $aparts = explode("\n", substr($aparts, 3));
                            if(Context::AllowUser($aparts[1], $conn) == 0) {
                                $id = 0;
                                if($this->chat["AUTOID"]) {
                                    for($i = 1;; $i++) {
                                        if(!array_key_exists("".$i, $this->connectedUsers)) {
                                            $id = "".$i;
                                            break;
                                        }
                                    }
                                } else $id = $aparts[0];

                                Context::Join(new User($id, Utils::$chat["DEFAULT_CHANNEL"], $this->Sanitize($aparts[1]), $aparts[2], $aparts[3], $conn));
                            } else {
                                $conn->send(1, array("n","Username in use!"));
                            }
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

                                    if(strtolower($cmd) != "generic_cmd" && file_exists("commands/". strtolower($cmd) .".php")) {
                                        try {
                                            call_user_func_array("\\sockchat\\cmds\\". strtolower($cmd) ."::doCommand", [$this, $user, $cmdparts]);
                                        } catch(\Exception $err) {
                                            $this->BroadcastMessage($this->chatbot, $this->FormatBotMessage(MSG_ERROR, "cmderr", [strtolower($cmd), $err->getMessage()]));
                                        }
                                    } else
                                        $this->SendMessage($this->chatbot, $this->connectedUsers[$parts[0]], $this->FormatBotMessage(MSG_ERROR, "nocmd", [strtolower($cmd)]));
                                        //echo "got here";
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
                $this->Broadcast($this->PackMessage(3, array($user->id, $user->username, gmdate("U"), $this->msgid)));
                $this->msgid++;
                unset($this->connectedUsers[$user->id]);
            }
        }
        Context::CheckPings();
    }

    public function onError(ConnectionInterface $conn, \Exception $err) {
        Context::CheckPings();
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