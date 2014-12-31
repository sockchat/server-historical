<?php
namespace sockchat;

class Modules {
    protected static $mods = [];
    protected static $cmds = [];

    public static function Load() {
        require_once("./mods/generic_mod.php");
        $mods = glob("./mods/*", GLOB_ONLYDIR);
        foreach($mods as $mod) {
            $name = substr($mod, strrpos($mod, "/")+1);
            if(file_exists("{$mod}/{$name}.php")) {
                include("{$mod}/{$name}.php");
                if(class_exists("\\sockchat\\mods\\{$name}\\Main")) {
                    Modules::$mods[$name] = $name;

                    $cmds = call_user_func_array("\\sockchat\\mods\\{$name}\\Main::Init", []);
                    $cmds = call_user_func_array("\\sockchat\\mods\\{$name}\\Main::GetCommands", []);
                    foreach($cmds as $cmd) {
                        if(array_key_exists($cmd, self::$cmds))
                            echo "Error loading module $name: Command $cmd has already been defined by module ". self::$cmds[$cmd] ."!\n";
                        else self::$cmds[$cmd] = $name;
                    }
                }
            }
        }

        //var_dump(self::$cmds);
    }

    public static function DoesModExist($mod) {
        return array_key_exists($mod, self::$mods);
    }

    public static function DoesCommandExist($cmd) {
        return array_key_exists($cmd, self::$cmds);
    }

    public static function ExecuteRoutine($routine, $args, $exitAfterFirstReturn = false) {
        $ret = true;
        foreach(Modules::$mods as $mod) {
            $ret &= call_user_func_array("\\sockchat\\mods\\{$mod}\\Main::{$routine}", $args) === null;
            if(!$ret && $exitAfterFirstReturn) break;
        }
        return $ret;
    }

    public static function ExecuteCommand($cmd, $user, $args) {
        if(!array_key_exists($cmd, self::$cmds)) return false;
        else {
            call_user_func_array("\\sockchat\\mods\\". self::$cmds[$cmd] ."\\Main::ExecuteCommand", [$cmd, $args, $user, "\\sockchat\\mods\\". self::$cmds[$cmd] ."\\Main"]);
            return true;
        }
    }
}