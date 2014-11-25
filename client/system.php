<?php
function getFileContents($fname) {
    return str_replace('"', '\\"', trim(preg_replace('/\s+/', ' ', preg_replace('!/\*.*?\*/!s', '', file_get_contents($fname)))));
}

class SoundPackHandler {
    private static $expectedFiles = ["chatbot","error","join","leave","receive","send"];

    public static function getAllSoundPacks() {
        $packs = [];

        foreach(glob("./sound/*", GLOB_ONLYDIR) as $dir) {
            $valid = true;
            foreach(SoundPackHandler::$expectedFiles as $file) {
                $test = glob($dir ."/". $file .".*");
                if(empty($test)) {
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

    public static function printSoundPacks($packs) {
        foreach($packs as $pack) {
            foreach(SoundPackHandler::$expectedFiles as $file) {
                echo "<audio id='". $pack .".". $file ."'>";
                $fdata = glob("./sound/$pack/$file.*");
                foreach($fdata as $dfatas)
                    echo "<source src='$dfatas' type='". finfo_file(finfo_open(FILEINFO_MIME_TYPE), $dfatas) ."' />";
                echo "</audio>";
            }
        }
    }
}