<?php
namespace sockchat;
use \PDO;

class Database {
    protected $conn = null;
    protected $statements;

    public function Init($persist = true) {
        $chat = $GLOBALS["chat"];
        if($chat["DB_ENABLE"]) {
            try {
                $this->conn = new PDO($chat["DB_DSN"], $chat["DB_USER"], $chat["DB_PASS"], $persist ? [PDO::ATTR_PERSISTENT => true] : []);
                $pre = $chat["DB_TABLE_PREFIX"];

                $this->statements = [
                    "logstore" => [
                        "query" => $this->conn->prepare("INSERT INTO {$pre}_logs (userid, username, color, channel, chrank, message) VALUES (:uid, :uname, :color, :chan, :chrank, :msg)"),
                        "uid" => "", "uname" => "", "color" => "", "chan" => "", "chrank" => "", "msg" => ""
                    ],
                    "logfetch" => [
                        "query" => $this->conn->prepare("SELECT * FROM {$pre}_logs WHERE channel = :chan, datetime >= :lb, datetime <= :ub, chrank <= :uperm"),
                        "chan" => "", "lb" => "", "ub" => "", "uperm" => ""
                    ],
                    "logfetchall" => [
                        "query" => $this->conn->prepare("SELECT * FROM {$pre}_logs WHERE datetime >= :lb, datetime <= :ub, chrank <= :uperm"),
                        "lb" => "", "ub" => "", "uperm" => ""
                    ],
                    "login" => [
                        "query" => $this->conn->prepare("INSERT INTO {$pre}_online_users (userid, username, color, perms) VALUES (:uid, :uname, :col, :perms)"),
                        "uid" => "", "uname" => "", "col" => "", "perms" => ""
                    ],
                    "logout" => [
                        "query" => $this->conn->prepare("DELETE FROM {$pre}_online_users WHERE userid = :uid"),
                        "uid" => ""
                    ],
                    "crchan" => [
                        "query" => $this->conn->prepare("INSERT INTO {$pre}_channels (chname, pwd) VALUES (:chn, :pwd)"),
                        "chn" => "", "pwd" => ""
                    ],
                    "delchan" => [
                        "query" => $this->conn->prepare("DELETE FROM {$pre}_channels WHERE chname = :chn"),
                        "chn" => ""
                    ]
                ];

                foreach($this->statements as $stmt) {
                    foreach($stmt as $param => $value) {
                        if($param != "query") $stmt["query"]->bindParam(":{$param}", $stmt[$param]);
                    }
                }
            } catch(\Exception $err) {
                echo "Could not connect to the database! Details: ". $err->getMessage() ."\n";
                return;
            }
        }
    }
}