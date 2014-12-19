<?php
namespace sockchat;

class Commands {
    protected static $cmds = [];

    public static function Load() {
        require_once("./commands/generic_cmd.php");
        $cmds = glob("./commands/*.php");
        foreach($cmds as $cmd) {
            $name = substr($cmd, strrpos($cmd, "/")+1, -4);
            if($name != "generic_cmd") {
                include($cmd);
                if(method_exists("\\sockchat\\cmds\\" . $name, "doCommand"))
                    Commands::$cmds[$name] = $name;
            }
        }
    }

    public static function DoesCommandExist($cmd) {
        return array_key_exists($cmd, Commands::$cmds);
    }

    public static function ExecuteCommand($cmd, $user, $args) {
        if(Commands::DoesCommandExist($cmd)) {
            call_user_func_array("\\sockchat\\cmds\\". strtolower($cmd) ."::doCommand", [$user, $args]);
            return true;
        } else return false;
    }
}

class Modules {
    protected static $mods = [];

    public static function Load() {
        require_once("./mods/generic_mod.php");
        $mods = glob("./mods/*", GLOB_ONLYDIR);
        foreach($mods as $mod) {
            $name = substr($mod, strrpos($mod, "/")+1);
            if(file_exists("{$mod}/{$name}.php")) {
                include("{$mod}/{$name}.php");
                if(class_exists("\\sockchat\\mods\\{$name}\\Main"))
                    Modules::$mods[$name] = $name;
            }
        }
    }

    public static function DoesModExist($mod) {
        return array_key_exists($mod, Modules::$mods);
    }

    public static function ExecuteRoutine($routine, $args) {
        $ret = true;
        foreach(Modules::$mods as $mod)
            $ret &= call_user_func_array("\\sockchat\\mods\\{$mod}\\Main::{$routine}", $args) === null;
        return $ret;
    }
}