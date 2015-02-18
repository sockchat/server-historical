<?php
/* RETURN HEADER KEY
 *
 * x: not allowed to view logs
 * u: unsupported
 *
 * l: channel list (delimited by \n)
 * y: more data in queue -> (delimited by \r\t\n\f)
 * n: queue empty -------|
 */

mb_internal_encoding("UTF-8");

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
$priv = $perms[1] == 1 ? "true" : "false";

if(isset($_GET["channel"])) {
    if($_GET["channel"] == "@priv" && $priv == "false")
        die("n");
}

$queries = [
    "SELECT DISTINCT channel FROM {$pre}_logs WHERE (channel) IN (SELECT chname FROM {$pre}_channels)",
    "SELECT DISTINCT channel FROM {$pre}_logs WHERE (channel) NOT IN (SELECT chname FROM {$pre}_channels)",

    "SELECT * FROM {$pre}_logs WHERE chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE message LIKE :msg AND userid <> -1 AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND userid <> -1 AND chrank <= :rank LIMIT :lim OFFSET :off",

    "SELECT COUNT(*) FROM {$pre}_logs WHERE chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE message LIKE :msg AND userid <> -1 AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND userid <> -1 AND chrank <= :rank",

    "SELECT * FROM {$pre}_logs WHERE (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE message LIKE :msg AND (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND userid <> -1 AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND userid <> -1 AND chrank <= :rank LIMIT :lim OFFSET :off",

    "SELECT COUNT(*) FROM {$pre}_logs WHERE (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE message LIKE :msg AND (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND userid <> -1 AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND userid <> -1 AND chrank <= :rank",

    "SELECT * FROM {$pre}_logs WHERE epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE message LIKE :msg AND userid <> -1 AND epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND userid <> -1 AND epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",

    "SELECT COUNT(*) FROM {$pre}_logs WHERE epoch >= :low AND epoch <= :high AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND epoch >= :low AND epoch <= :high AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE message LIKE :msg AND userid <> -1 AND epoch >= :low AND epoch <= :high AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND userid <> -1 AND epoch >= :low AND epoch <= :high AND chrank <= :rank",

    "SELECT * FROM {$pre}_logs WHERE (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE message LIKE :msg AND userid <> -1 AND (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",
    "SELECT * FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND userid <> -1 AND (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND epoch >= :low AND epoch <= :high AND chrank <= :rank LIMIT :lim OFFSET :off",

    "SELECT COUNT(*) FROM {$pre}_logs WHERE (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND epoch >= :low AND epoch <= :high AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND epoch >= :low AND epoch <= :high AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE message LIKE :msg AND userid <> -1 AND (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND epoch >= :low AND epoch <= :high AND chrank <= :rank",
    "SELECT COUNT(*) FROM {$pre}_logs WHERE username = :uname AND message LIKE :msg AND userid <> -1 AND (channel = :chan OR channel = '@all' OR (channel = '@priv' AND {$priv})) AND epoch >= :low AND epoch <= :high AND chrank <= :rank"
];

//die(var_dump($queries));

function escapeWildcard($str) {
    return $str;//str_replace(["[","_","%"], ["[[]","[_]","[%]"], $str);
}

if(isset($_GET["channels"])) {
    $live = [];
    foreach($conn->query($queries[0]) as $channel) {
        if($channel[0][0] != "@") array_push($live, $channel[0]);
    }
    $dead = [];
    foreach($conn->query($queries[1]) as $channel) {
        if($channel[0][0] != "@") array_push($dead, $channel[0]);
    }
    echo "l\n" . implode("\n", $live) . (count($dead) > 0 ? ("\n@dead\n" . implode("\n", $dead)) : "");
} else {
    if((isset($_GET["high"]) && !isset($_GET["low"])) || (!isset($_GET["high"]) && isset($_GET["low"]))) die("n");
    $base = 2 + 8*(isset($_GET["high"])?1:0) + 8*(isset($_GET["low"])?1:0) + 8*(isset($_GET["channel"])?1:0);
    $base += isset($_GET["name"]) ? (isset($_GET["msg"]) ? 3 : 1) : (isset($_GET["msg"]) ? 2 : 0);
    $off = isset($_GET["offset"]) ? $size * $_GET["offset"] : 0;
    for($i = 0; $i <= 1; $i++) {
        $query = $conn->prepare($queries[$i == 0 ? $base + 4 : $base]);
        if($i == 1) {
            $query->bindValue(":lim", $size, PDO::PARAM_INT);
            $query->bindValue(":off", $off , PDO::PARAM_INT);
        }
        $query->bindValue(":rank", $rank, PDO::PARAM_INT);
        if(isset($_GET["channel"])) {
            $query->bindValue(":chan", $_GET["channel"], PDO::PARAM_STR);
            //$query->bindValue(":priv", /*$perms[1] == 1*/ false, PDO::PARAM_BOOL);
        }
        if(isset($_GET["high"])) $query->bindValue(":high", $_GET["high"], PDO::PARAM_INT);
        if(isset($_GET["low"]))  $query->bindValue(":low", $_GET["low"], PDO::PARAM_INT);
        if(isset($_GET["name"])) $query->bindValue(":uname", $_GET["name"], PDO::PARAM_STR);
        if(isset($_GET["msg"])) $query->bindValue(":msg", "%". escapeWildcard($_GET["msg"]) ."%", PDO::PARAM_STR);
        $query->execute();
        $ret = $query->fetchAll(PDO::FETCH_NUM);
        if($i == 0) echo $off + $size < $ret[0][0] ? "y\r\t\n\f". ($off/$size) : "n";
        else {
            foreach($ret as $msg) {
                if(!isset($_GET["channel"]) && $msg[2] != -1) $msg[7] = "<i>(to channel ". $msg[5] .")</i> ". $msg[7];
                echo "\r\t\n\f". implode("\t", $msg);
            }
        }
    }
}

//echo "y";