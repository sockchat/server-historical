<?php
namespace sockchat;
require __DIR__ ."/vendor/autoload.php";
use \Ratchet\MessageComponentInterface;
use \Ratchet\ConnectionInterface;
use \Ratchet\Server\IoServer;
use \Ratchet\Http\HttpServer;
use \Ratchet\WebSocket\WsServer;
use React\Stream\Util;

mb_internal_encoding("UTF-8");
error_reporting(E_ERROR);

require_once("lib/constants.php");
require_once("config.php");
// TODO REMOVE THIS ON RELEASE
require_once("dbinfo.php");
// TODO REMOVE THIS ON RELEASE
require_once("lib/utils.php");
require_once("lib/db.php");
require_once("lib/user.php");
require_once("lib/context.php");
require_once("lib/channel.php");
require_once("lib/msg.php");
require_once("lib/mods.php");

Modules::Load();

class Chat implements MessageComponentInterface {
    public function __construct() {
        Utils::$chat = $GLOBALS["chat"];
        Database::Init();
        Message::$bot = new User("-1", "", "ChatBot", "inherit", "", null);

        Database::TruncateUserList();
        Context::$channelList = array_merge([Utils::$chat["DEFAULT_CHANNEL"] => new Channel(Utils::SanitizeName(Utils::$chat["DEFAULT_CHANNEL"]), "", 0, null, CHANNEL_PERM, Database::FetchBacklog(DEFAULT_CHANNEL))], Database::GetAllChannels());
        Context::$bannedUsers = Database::GetAllBans();

        echo "Server started.\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        Modules::ExecuteRoutine("OnConnectionOpen", [$conn]);
        Context::CheckPings();
    }

    public function onMessage(ConnectionInterface $conn, $msg) {
        Context::CheckPings();
        if(true) {
            $parts = explode(Utils::$separator, $msg);
            $id = $parts[0];
            $parts = array_slice($parts, 1);

            if(!Modules::ExecuteRoutine("OnPacketReceive", [$conn, &$id, &$parts])) return;

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
                            $arglist .= "&arg". ($i+1) ."=". urlencode($parts[$i]);
                        $aparts = file_get_contents(Utils::$chat['CHATROOT'] ."/?view=auth". $arglist);

                        if(substr($aparts, 0, 3) == "yes") {
                            $aparts = explode("\n", mb_substr($aparts, 3));
                            if(($reason = Context::AllowUser($aparts[1], $conn)) === 0) {
                                if(($length = Context::CheckBan(Utils::$chat["AUTOID"] ? null : $aparts[0], $conn->remoteAddress, Utils::SanitizeName($aparts[1]))) === false) {
                                    $id = 0;
                                    if(Utils::$chat["AUTOID"]) {
                                        for($i = 1;; $i++) {
                                            if(Context::GetUserByID($i) == null) {
                                                $id = "".$i;
                                                break;
                                            }
                                        }
                                    } else $id = $aparts[0];

                                    Context::Join(new User($id, Utils::$chat["DEFAULT_CHANNEL"], Utils::SanitizeName($aparts[1]), $aparts[2], $aparts[3], $conn));
                                } else $conn->send(Utils::PackMessage(1, array("n", "joinfail", $length)));
                            } else $conn->send(Utils::PackMessage(1, array("n", $reason)));
                        } else $conn->send(Utils::PackMessage(1, array("n", "authfail")));
                    }
                    break;
                case 2:
                    if(($user = Context::GetUserByID($parts[0])) != null) {
                        if($user->sock == $conn) {
                            if(trim($parts[1]) != "") {
                                $parts[1] = mb_substr(trim($parts[1]), 0, Utils::$chat["MAX_MSG_LEN"]);
                                if(trim($parts[1])[0] != "/") {
                                    $out = Utils::Sanitize($parts[1]);
                                    if(!Modules::ExecuteRoutine("OnMessageReceive", [$user, &$out])) return;
                                    Message::BroadcastUserMessage($user, $out);
                                    Modules::ExecuteRoutine("AfterMessageReceived", [$user, $out]);
                                } else {
                                    //Database::Log(gmdate("U"), $user, Utils::Sanitize(trim($parts[1])));

                                    $parts[1] = mb_substr(trim($parts[1]), 1);
                                    $cmdparts = explode(" ", $parts[1]);
                                    $cmd = strtolower(str_replace(".","",$cmdparts[0]));
                                    $cmdparts = array_slice($cmdparts, 1);
                                    for($i = 0; $i < count($cmdparts); $i++)
                                        $cmdparts[$i] = Utils::Sanitize(trim($cmdparts[$i]));

                                    if(!Modules::ExecuteRoutine("OnCommandReceive", [$user, &$cmd, &$cmdparts])) return;
                                    if(Modules::ExecuteCommand($cmd, $user, $cmdparts))
                                        Modules::ExecuteRoutine("AfterCommandReceived", [$user, $cmd, $cmdparts]);
                                    else
                                        Message::PrivateBotMessage(MSG_ERROR, "nocmd", [strtolower($cmd)], $user);
                                }
                            }
                        }
                    }
                    break;
            }

            Modules::ExecuteRoutine("AfterPacketReceived", [$conn, $id, $parts]);
        } else
            $conn->close();
    }

    public function onClose(ConnectionInterface $conn) {
        echo $conn->remoteAddress ." has disconnected\n";
        Modules::ExecuteRoutine("OnConnectionClose", [$conn]);
        foreach(Context::$onlineUsers as $user) {
            if($user->sock == $conn) {
                echo "found user ". $user->username .", dropped\n";
                Context::Leave($user);
                Modules::ExecuteRoutine("OnUserLeave", [$user]);
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