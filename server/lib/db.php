<?php
namespace sockchat;
use \PDO;

class FFDB {
    protected static function CreateFile($dir) {

    }

    public static function Init() {
        if(!file_exists("./ffdb")) mkdir("./ffdb");
        if(!file_exists("./ffdb/chans")) mkdir("./ffdb/chans");
        if(!file_exists("./ffdb/bans")) mkdir("./ffdb/bans");
    }

    public static function Log($str) {
        file_put_contents("./ffdb/logs.txt", "{$str}\n", FILE_APPEND);
    }

    public static function Ban($type, $value, $expire) {
        while(file_exists($fname = "./ffdb/bans/". md5(time() + rand(0, 100))));
        file_put_contents($fname, implode("\f", [$type, $value, $expire]));
    }

    public static function Unban($type, $value) {
        $files = glob("./ffdb/bans/*");
        foreach($files as $file) {
            $data = explode("\f", file_get_contents($file));
            if($data[0] == $type && $data[1] == $value) {
                unlink($file);
                break;
            }
        }
    }

    public static function GetAllBans() {
        $bans = [];

        $files = glob("./ffdb/bans/*");
        foreach($files as $file) {
            $data = explode("\f", file_get_contents($file));
            if($data[2] <= time() && $data[2] != "-1") {
                unlink($file);
                continue;
            }

            array_push($bans, new Ban($data[0], $data[1], $data[2]));
        }

        return $bans;
    }

    public static function CreateChannel($name, $pwd, $priv) {
        while(file_exists($fname = "./ffdb/chans/". md5(time() + rand(0, 100))));
        file_put_contents($fname, implode("\f", [$name, $pwd, $priv]));
    }

    public static function ModifyChannel($oldname, $newname, $pwd, $priv) {
        $files = glob("./ffdb/chans/*");
        foreach($files as $file) {
            $data = explode("\f", file_get_contents($file));
            if($data[0] == $oldname) {
                file_put_contents($file, implode("\f", [$newname, $pwd, $priv]));
                break;
            }
        }
    }

    public static function RemoveChannel($name) {
        $files = glob("./ffdb/chans/*");
        foreach($files as $file) {
            $data = explode("\f", file_get_contents($file));
            if($data[0] == $name) {
                unlink($file);
                break;
            }
        }
    }

    public static function GetAllChannels() {
        $chans = [];

        $files = glob("./ffdb/chans/*");
        foreach($files as $file) {
            $data = explode("\f", file_get_contents($file));
            array_push($chans, new Channel($data[0], $data[1], $data[2]));
        }

        return $chans;
    }
}

class Database {
    protected static $conn = null;
    protected static $statements;
    protected static $useFlatFile;

    public static function Init($persist = true) {
        $chat = $GLOBALS["chat"];
        Database::$useFlatFile = $chat["DB_ENABLE"];
        if($chat["DB_ENABLE"]) {
            try {
                Database::$conn = new PDO($chat["DB_DSN"], $chat["DB_USER"], $chat["DB_PASS"], $persist ? [PDO::ATTR_PERSISTENT => true] : []);
                $pre = $chat["DB_TABLE_PREFIX"];

                Database::$statements = [
                    "logstore" => [
                        "query" => Database::$conn->prepare("INSERT INTO {$pre}_logs (epoch, userid, username, color, channel, chrank, message) VALUES (:epoch, :uid, :uname, :color, :chan, :chrank, :msg)"),
                        "epoch" => "","uid" => "", "uname" => "", "color" => "", "chan" => "", "chrank" => "", "msg" => ""
                    ],
                    "logfetch" => [
                        "query" => Database::$conn->prepare("SELECT * FROM {$pre}_logs WHERE channel = :chan AND datetime >= :lb AND datetime <= :ub AND chrank <= :uperm"),
                        "chan" => "", "lb" => "", "ub" => "", "uperm" => ""
                    ],
                    "logfetchall" => [
                        "query" => Database::$conn->prepare("SELECT * FROM {$pre}_logs WHERE datetime >= :lb AND datetime <= :ub AND chrank <= :uperm"),
                        "lb" => "", "ub" => "", "uperm" => ""
                    ],
                    "login" => [
                        "query" => Database::$conn->prepare("INSERT INTO {$pre}_online_users (userid, username, color, perms) VALUES (:uid, :uname, :col, :perms)"),
                        "uid" => "", "uname" => "", "col" => "", "perms" => ""
                    ],
                    "logout" => [
                        "query" => Database::$conn->prepare("DELETE FROM {$pre}_online_users WHERE userid = :uid"),
                        "uid" => ""
                    ],
                    "clrusers" => [
                        "query" => Database::$conn->prepare("TRUNCATE TABLE {$pre}_online_users")
                    ],
                    "crchan" => [
                        "query" => Database::$conn->prepare("INSERT INTO {$pre}_channels (chname, pwd, priv) VALUES (:chn, :pwd, :priv)"),
                        "chn" => "", "pwd" => "", "priv" => ""
                    ],
                    "modchan" => [
                        "query" => Database::$conn->prepare("UPDATE {$pre}_channels SET chname = :chn, pwd = :pwd, priv = :priv WHERE chname = :chon"),
                        "chn" => "", "pwd" => "", "priv" => "", "chon" => ""
                    ],
                    "delchan" => [
                        "query" => Database::$conn->prepare("DELETE FROM {$pre}_channels WHERE chname = :chn"),
                        "chn" => ""
                    ],
                    "fetchchan" => [
                        "query" => Database::$conn->prepare("SELECT * FROM {$pre}_channels")
                    ],
                    "banuser" => [
                        "query" => Database::$conn->prepare("INSERT INTO {$pre}_banned_users (bantype, banvalue, expiration) VALUES (:type, :val, :exp)"),
                        "type" => "", "val" => "", "exp" => ""
                    ],
                    "unbanuser" => [
                        "query" => Database::$conn->prepare("DELETE FROM {$pre}_banned_users WHERE bantype = :type AND banvalue = :val"),
                        "type" => "", "val" => ""
                    ],
                    "fetchbans" => [
                        "query" => Database::$conn->prepare("SELECT * FROM {$pre}_banned_users")
                    ],
                    "updatebans" => [
                        "query" => Database::$conn->prepare("DELETE FROM {$pre}_banned_users WHERE expiration <= :epoch AND expiration != -1"),
                        "epoch" => ""
                    ]
                ];

                foreach(Database::$statements as $stmt) {
                    foreach($stmt as $param => $value) {
                        if($param != "query") $stmt["query"]->bindParam(":{$param}", $stmt[$param]);
                    }
                }
            } catch(\Exception $err) {
                echo "Could not connect to the database! Details: ". $err->getMessage() ."\n";
                return;
            }
        } else FFDB::Init();
    }

    public static function TruncateUserList() {
        if(!Database::$useFlatFile) Database::$statements["clrusers"]["query"]->execute();
    }

    public static function Login($user) {
        if(!Database::$useFlatFile) {
            Database::$statements["login"]["uid"] = $user->id;
            Database::$statements["login"]["uname"] = $user->username;
            Database::$statements["login"]["col"] = $user->color;
            Database::$statements["login"]["perms"] = $user->permstr;
            Database::$statements["login"]["query"]->execute();
        }
    }

    public static function Logout($user) {
        if(!Database::$useFlatFile) {
            Database::$statements["logout"]["uid"] = $user->id;
            Database::$statements["logout"]["query"]->execute();
        }
    }

    public static function Log($time, $user, $msg) {
        if(Database::$useFlatFile)
            FFDB::Log("(". date("m/d/Y H:i:s") . ") ". $user->username .": ". $msg);
        else {
            Database::$statements["logstore"]["epoch"] = $time;
            Database::$statements["logstore"]["uid"] = $user->id;
            Database::$statements["logstore"]["uname"] = $user->username;
            Database::$statements["logstore"]["color"] = $user->color;
            Database::$statements["logstore"]["chan"] = $user->channel;
            Database::$statements["logstore"]["chrank"] = Context::GetChannel($user->channel)->permissionLevel;
            Database::$statements["logstore"]["msg"] = $msg;
            Database::$statements["logstore"]["query"]->execute();
        }
    }

    public static function Ban($type, $value, $expire) {
        if(Database::$useFlatFile)
            FFDB::Ban($type, $value, $expire);
        else {
            Database::$statements["banuser"]["type"] = $type;
            Database::$statements["banuser"]["val"] = $value;
            Database::$statements["banuser"]["exp"] = $expire;
            Database::$statements["banuser"]["query"]->execute();
        }
    }

    // you've got no bans
    // you've got no drans
    public static function GetAllBans() {
        if(Database::$useFlatFile)
            return FFDB::GetAllBans();
        else {
            $time = time();
            Database::$statements["updatebans"]["epoch"] = $time;
            Database::$statements["updatebans"]["query"]->execute();

            $blist = [];
            $bans = Database::$statements["fetchbans"]["query"]->execute()->fetchAll(PDO::FETCH_BOTH);
            foreach($bans as $ban) {
                if($ban["expiration"] > $time) array_push($blist, new Ban($ban["bantype"], $ban["banvalue"], $ban["expiration"]));
            }
            return $blist;
        }
    }

    // you want some
    // i'll give it ya
    public static function Unban($type, $value) {
        if(Database::$useFlatFile)
            FFDB::Unban($type, $value);
        else {
            Database::$statements["unbanuser"]["type"] = $type;
            Database::$statements["unbanuser"]["val"] = $value;
            Database::$statements["unbanuser"]["query"]->execute();
        }
    }

    public static function CreateChannel($name, $pwd, $priv = 0) {
        if(Database::$useFlatFile)
            FFDB::CreateChannel($name, $pwd, $priv);
        else {
            Database::$statements["crchan"]["chn"] = $name;
            Database::$statements["crchan"]["pwd"] = $pwd;
            Database::$statements["crchan"]["priv"] = $priv;
            Database::$statements["crchan"]["query"]->execute();
        }
    }

    public static function RemoveChannel($name) {
        if(Database::$useFlatFile)
            FFDB::RemoveChannel($name);
        else {
            Database::$statements["delchan"]["chn"] = $name;
            Database::$statements["delchan"]["query"]->execute();
        }
    }

    public static function ModifyChannel($oldname, $newname, $pwd, $priv) {
        if(Database::$useFlatFile)
            FFDB::ModifyChannel($oldname, $newname, $pwd, $priv);
        else {
            Database::$statements["modchan"]["chon"] = $oldname;
            Database::$statements["modchan"]["chn"] = $newname;
            Database::$statements["modchan"]["pwd"] = $pwd;
            Database::$statements["modchan"]["priv"] = $priv;
            Database::$statements["modchan"]["query"]->execute();
        }
    }

    public static function GetAllChannels() {
        if(Database::$useFlatFile)
            return FFDB::GetAllChannels();
        else {
            $clist = [];
            $chans = Database::$statements["fetchchan"]["query"]->execute()->fetchAll(PDO::FETCH_BOTH);
            foreach($chans as $chan)
                array_push($clist, new Channel($chan["chname"], $chan["pwd"], $chan["priv"]));
            return $clist;
        }
    }
}