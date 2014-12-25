<?php
namespace sockchat;
use \PDO;

class FFDB {
    protected static function CreateFile($dir) {
        try {
            while(file_exists($fname = "$dir/". md5(microtime())));
        } catch(\Exception $e) {
            while(file_exists($fname = "$dir/". md5(time() + rand(0, 100))));
        }
        return $fname;
    }

    public static function Init() {
        if(!file_exists("./ffdb")) mkdir("./ffdb");
        if(!file_exists("./ffdb/chans")) mkdir("./ffdb/chans");
        if(!file_exists("./ffdb/bans")) mkdir("./ffdb/bans");
        if(!file_exists("./ffdb/logs.txt")) file_put_contents("./ffdb/logs.txt", "");
    }

    public static function Log($str) {
        file_put_contents("./ffdb/logs.txt", "{$str}\n", FILE_APPEND);
    }

    public static function Ban($ip, $id, $username, $expire) {
        $fname = FFDB::CreateFile("./ffdb/bans");
        file_put_contents($fname, implode("\f", [$ip, $id, $username, $expire]));
        echo $fname ."\n";
    }

    public static function Unban($ip, $id, $username) {
        $files = glob("./ffdb/bans/*");
        foreach($files as $file) {
            $data = explode("\f", file_get_contents($file));
            if(($data[0] == $ip && $data[0] != null) || ($data[1] == $id && $data[1] != null) || ($data[2] == $username && $data[2] != null))
                unlink($file);
        }
    }

    public static function GetAllBans() {
        $bans = [];

        $files = glob("./ffdb/bans/*");
        foreach($files as $file) {
            $data = explode("\f", file_get_contents($file));
            if($data[3] <= time() && $data[3] != "-1") {
                unlink($file);
                continue;
            }

            array_push($bans, new Ban($data[0], $data[1], $data[2], $data[3]));
        }

        return $bans;
    }

    public static function CreateChannel($name, $pwd, $priv) {
        $fname = FFDB::CreateFile("./ffdb/chans");
        echo $fname ."\n";
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
            $chans[$data[0]] = new Channel($data[0], $data[1], $data[2]);
        }

        return $chans;
    }
}

class Database {
    protected static $conn = null;
    protected static $statements;
    protected static $useFlatFile;

    protected static function Execute($stmt, $fetch = false) {
        $tmp = Database::$conn->prepare(Database::$statements[$stmt]["query"]);
        foreach(Database::$statements[$stmt] as $param => $value) {
            if($param != "query") $tmp->bindValue(":{$param}", $value);
        }
        $tmp->execute();
        if ($fetch) return $tmp->fetchAll(PDO::FETCH_BOTH);
        else return [];
    }

    public static function Init($persist = true) {
        $chat = $GLOBALS["chat"];
        Database::$useFlatFile = !$chat["DB_ENABLE"];
        if($chat["DB_ENABLE"]) {
            try {
                Database::$conn = new PDO($chat["DB_DSN"], $chat["DB_USER"], $chat["DB_PASS"], $persist ? [PDO::ATTR_PERSISTENT => true] : []);
                $pre = $chat["DB_TABLE_PREFIX"];

                Database::$statements = [
                    "logstore" => [
                        "query" => "INSERT INTO {$pre}_logs (epoch, userid, username, color, channel, chrank, message) VALUES (:epoch, :uid, :uname, :color, :chan, :chrank, :msg)",
                        "epoch" => "","uid" => "", "uname" => "", "color" => "", "chan" => "", "chrank" => "", "msg" => ""
                    ],
                    "fetchbacklog" => [
                        "query" => "SELECT * FROM {$pre}_logs WHERE channel = :chan OR channel = '@all' ORDER BY epoch DESC LIMIT 0, ". Backlog::$loglen,
                        "chan" => ""
                    ],
                    "login" => [
                        "query" => "INSERT INTO {$pre}_online_users (userid, username, color, perms) VALUES (:uid, :uname, :col, :perms)",
                        "uid" => "", "uname" => "", "col" => "", "perms" => ""
                    ],
                    "logout" => [
                        "query" => "DELETE FROM {$pre}_online_users WHERE userid = :uid",
                        "uid" => ""
                    ],
                    "clrusers" => [
                        "query" => "TRUNCATE TABLE {$pre}_online_users"
                    ],
                    "crchan" => [
                        "query" => "INSERT INTO {$pre}_channels (chname, pwd, priv) VALUES (:chn, :pwd, :priv)",
                        "chn" => "", "pwd" => "", "priv" => ""
                    ],
                    "modchan" => [
                        "query" => "UPDATE {$pre}_channels SET chname = :chn, pwd = :pwd, priv = :priv WHERE chname = :chon",
                        "chn" => "", "pwd" => "", "priv" => "", "chon" => ""
                    ],
                    "delchan" => [
                        "query" => "DELETE FROM {$pre}_channels WHERE chname = :chn",
                        "chn" => ""
                    ],
                    "fetchchan" => [
                        "query" => "SELECT * FROM {$pre}_channels"
                    ],
                    "banuser" => [
                        "query" => "INSERT INTO {$pre}_banned_users (ip, uid, username, expiration) VALUES (:ip, :id, :uname, :exp)",
                        "ip" => "", "id" => "", "uname" => "", "exp" => ""
                    ],
                    "unban" => [
                        "query" => "DELETE FROM {$pre}_banned_users WHERE (ip IS NOT NULL AND ip LIKE :ip) OR (id IS NOT NULL AND id = :id) OR (username IS NOT NULL AND username = :uname)",
                        "ip" => "", "id" => "", "uname" => ""
                    ],
                    "fetchbans" => [
                        "query" => "SELECT * FROM {$pre}_banned_users"
                    ],
                    "updatebans" => [
                        "query" => "DELETE FROM {$pre}_banned_users WHERE expiration <= :epoch AND expiration != -1",
                        "epoch" => ""
                    ]
                ];
            } catch(\Exception $err) {
                echo "Could not connect to the database! Details: ". $err->getMessage() ."\n";
                return;
            }
        } else FFDB::Init();
    }

    public static function FetchBacklog($chan) {
        if(!Database::$useFlatFile) {
            $ret = new Backlog();

            Database::$statements["fetchbacklog"]["chan"] = $chan;
            $logs = Database::Execute("fetchbacklog", true);
            foreach($logs as $log)
                $ret->Log(new User($log["userid"], "", $log["username"], $log["color"], "", null), $log["message"], "rlbl", $log["epoch"]);
            $ret->logs = array_reverse($ret->logs);

            return $ret;
        } else return new Backlog();
    }

    public static function TruncateUserList() {
        if(!Database::$useFlatFile) Database::Execute("clrusers");
    }

    public static function Login($user) {
        if(!Database::$useFlatFile) {
            Database::$statements["login"]["uid"] = $user->id;
            Database::$statements["login"]["uname"] = $user->username;
            Database::$statements["login"]["col"] = $user->color;
            Database::$statements["login"]["perms"] = $user->permstr;
            Database::Execute("login");
        }
    }

    public static function Logout($user) {
        if(!Database::$useFlatFile) {
            Database::$statements["logout"]["uid"] = $user->id;
            Database::Execute("logout");
        }
    }

    public static function Log($time, $user, $msg, $chan = null) {
        if(Database::$useFlatFile)
            FFDB::Log("(". date("m/d/Y H:i:s") . ") ". $user->username ." to ". ($chan == null ? $user->channel : $chan) .": ". $msg);
        else {
            Database::$statements["logstore"]["epoch"] = $time;
            Database::$statements["logstore"]["uid"] = $user->id;
            Database::$statements["logstore"]["uname"] = $user->username;
            Database::$statements["logstore"]["color"] = $user->color;
            Database::$statements["logstore"]["chan"] = $chan == null ? $user->channel : $chan;
            Database::$statements["logstore"]["chrank"] = Context::GetChannel($user->channel)->permissionLevel;
            Database::$statements["logstore"]["msg"] = $msg;
            Database::Execute("logstore");
        }
    }

    public static function Ban($ip, $id, $username, $expire) {
        if(Database::$useFlatFile)
            FFDB::Ban($ip, $id, $username, $expire);
        else {
            Database::$statements["banuser"]["ip"] = $ip;
            Database::$statements["banuser"]["id"] = $id;
            Database::$statements["banuser"]["uname"] = $username;
            Database::$statements["banuser"]["exp"] = $expire;
            Database::Execute("banuser");
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
            Database::Execute("updatebans");

            $blist = [];
            $bans = Database::Execute("fetchbans", true);
            foreach($bans as $ban) {
                if($ban["expiration"] > $time || $ban["expiration"] == "-1") array_push($blist, new Ban($ban["ip"], $ban["uid"], $ban["username"], $ban["expiration"]));
            }
            return $blist;
        }
    }

    // you want some
    // i'll give it ya
    public static function Unban($ip, $id, $username) {
        if(Database::$useFlatFile)
            FFDB::Unban($ip, $id, $username);
        else {
            Database::$statements["unbanuser"]["ip"] = $ip;
            Database::$statements["unbanuser"]["id"] = $id;
            Database::$statements["unbanuser"]["uname"] = $username;
            Database::Execute("unbanuser");
        }
    }

    public static function CreateChannel($name, $pwd, $priv = 0) {
        if(Database::$useFlatFile)
            FFDB::CreateChannel($name, $pwd, $priv);
        else {
            Database::$statements["crchan"]["chn"] = $name;
            Database::$statements["crchan"]["pwd"] = $pwd;
            Database::$statements["crchan"]["priv"] = $priv;
            Database::Execute("crchan");
        }
    }

    public static function RemoveChannel($name) {
        if(Database::$useFlatFile)
            FFDB::RemoveChannel($name);
        else {
            Database::$statements["delchan"]["chn"] = $name;
            Database::Execute("delchan");
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
            Database::Execute("modchan");
        }
    }

    public static function GetAllChannels() {
        if(Database::$useFlatFile)
            return FFDB::GetAllChannels();
        else {
            $clist = [];
            $chans = Database::Execute("fetchchan", true);
            foreach($chans as $chan)
                $clist[$chan["chname"]] = new Channel($chan["chname"], $chan["pwd"], $chan["priv"], null, CHANNEL_PERM, Database::FetchBacklog($chan["chname"]));
            return $clist;
        }
    }
}