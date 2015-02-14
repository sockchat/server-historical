<?php
/* RETURN HEADER KEY
 *
 * x: not allowed to view logs
 * u: unsupported
 * i: not allowed to view channel
 *
 * l: channel list
 * y: more data in queue
 * n: queue empty
 */

mb_internal_encoding("UTF-8");

include("../config.php");
// TODO remove for release
include("dbinfo.php");

$args = "";
for($i = 1;; $i++) {
    if($_GET["arg{$i}"] === null) break;
    $args .= "&arg{$i}=". $_GET["arg{$i}"];
}

$data = file_get_contents($chat['ROOT'] ."/index.php?view=auth{$args}");
if(mb_substr($data, 0, 2) == "no")
    die("x");

$data = mb_substr($data, 3);
$perms = explode("\f", explode("\n", $data)[3]);
$rank = $perms[0];
if($perms[2] == "0")
    die("x");

if(!$chat["DB_ENABLE"])
    die("u");

$conn = new PDO($chat["DB_DSN"], $chat["DB_USER"], $chat["DB_PASS"], [PDO::ATTR_PERSISTENT => true]);
$pre = $chat["DB_TABLE_PREFIX"];
$size = $chat["LOG_BATCH_SIZE"];

$queries = [
    "SELECT DISTINCT channel FROM {$pre}_logs",

    "SELECT * FROM {$pre}_logs WHERE chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE message LIKE :msg AND userid <> -1 AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND userid <> -1 AND chrank <= :rank LIMIT :lim OFFSET :off",

    "SELECT COUNT(*) FROM {$pre}_logs WHERE chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE message LIKE :msg AND userid <> -1 AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND userid <> -1 AND chrank <= :rank",

    "SELECT * FROM {$pre}_logs WHERE channel = :chan AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND channel = :chan AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE message LIKE :msg AND channel = :chan AND userid <> -1 AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND channel = :chan AND userid <> -1 AND chrank <= :rank LIMIT :lim OFFSET :off",

    "SELECT COUNT(*) FROM {$pre}_logs WHERE channel = :chan AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND channel = :chan AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE message LIKE :msg AND channel = :chan AND userid <> -1 AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND channel = :chan AND userid <> -1 AND chrank <= :rank",

    "SELECT * FROM {$pre}_logs WHERE epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE message LIKE :msg AND userid <> -1 AND epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND userid <> -1 AND epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",

    "SELECT COUNT(*) FROM {$pre}_logs WHERE epoch >= :low AND epoch <= :high AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND epoch >= :low AND epoch <= :high AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE message LIKE :msg AND userid <> -1 AND epoch >= :low AND epoch <= :high AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND userid <> -1 AND epoch >= :low AND epoch <= :high AND chrank <= :rank",

    "SELECT * FROM {$pre}_logs WHERE channel = :chan AND epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND channel = :chan AND epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE message LIKE :msg AND userid <> -1 AND channel = :chan AND epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND userid <> -1 AND channel = :chan AND epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",

    "SELECT COUNT(*) FROM {$pre}_logs WHERE channel = :chan AND epoch >= :low AND epoch <= :high AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND channel = :chan AND epoch >= :low AND epoch <= :high AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE message LIKE :msg AND userid <> -1 AND channel = :chan AND epoch >= :low AND epoch <= :high AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND userid <> -1 AND channel = :chan AND epoch >= :low AND epoch <= :high AND chrank <= :rank"
];

if(isset($_GET["channels"])) {
    $arr = [];
    foreach($conn->query($queries[0]) as $channel) {
        if($channel[0][0] != "@") array_push($arr, $channel[0]);
    }
    echo "l" . implode("\n", $arr);
} else {
    if((isset($_GET["high"]) && !isset($_GET["low"])) || (!isset($_GET["high"]) && isset($_GET["low"]))) die("n");
    $base = 1 + 8*(isset($_GET["high"])?1:0) + 8*(isset($_GET["low"])?1:0) + 8*(isset($_GET["channel"])?1:0);
    $base += isset($_GET["name"]) ? (isset($_GET["msg"]) ? 3 : 1) : (isset($_GET["msg"]) ? 2 : 0);
    $off = isset($_GET["offset"]) ? $size * $_GET["offset"] : 0;
    for($i = 0; $i <= 1; $i++) {
        $query = $conn->prepare($queries[$i == 0 ? $base + 4 : $base]);
        if($i == 1) {
            $query->bindValue(":lim", $size, PDO::PARAM_INT);
            $query->bindValue(":off", $off , PDO::PARAM_INT);
        }
        $query->bindValue(":rank", $rank, PDO::PARAM_INT);
        $query->execute();
        $ret = $query->fetchAll(PDO::FETCH_BOTH);
        if($i == 0) echo $off + $size < $ret[0][0] ? "y" : "n";
        else {

        }
    }
    var_dump($ret);
}

//echo "y";