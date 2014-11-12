<?php
function getFileContents($fname) {
    return str_replace('"', '\\"', trim(preg_replace('/\s+/', ' ', preg_replace('!/\*.*?\*/!s', '', file_get_contents($fname)))));
}

class SoundPackHandler {
    private static $expectedFiles = ["chatbot","error","join","leave","receive","send"];
    private static $soundExtension = "mp3";
    private static $soundMime = "audio/mpeg";

    public static function getAllSoundPacks() {
        $packs = [];

        foreach(glob("./sound/*", GLOB_ONLYDIR) as $dir) {
            $valid = true;
            foreach(SoundPackHandler::$expectedFiles as $file) {
                if(!file_exists($dir ."/". $file .".". SoundPackHandler::$soundExtension)) {
                    $valid = false;
                    break;
                }
            }
            if($valid) array_push($packs, substr($dir, strrpos($dir,"/")+1));
        }

        return $packs;
    }

    public static function findDefaultPack($packarr) {
        global $chat;
        $ret = 0;

        for($i = 0; $i < count($packarr); $i++) {
            if($packarr[$i] == $chat["DEFAULT_SPACK"]) {
                $ret = $i;
                break;
            }
        }

        return $ret;
    }

    public static function printSoundPack($pack) {
        foreach(SoundPackHandler::$expectedFiles as $file) {
            echo "<audio id='". $file ."'><source id='". $file ."src' src='./sound/". $pack ."/". $file .".". SoundPackHandler::$soundExtension ."' type='". SoundPackHandler::$soundMime ."'></audio>";
        }
    }
}