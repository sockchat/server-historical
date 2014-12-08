<?php
namespace sockchat\cmds;
use sockchat\cmds\GenericCommand;
use sockchat\Context;
use \sockchat\Message;
use sockchat\Utils;

class join implements GenericCommand {
    public static function doCommand($user, $args) {
        if(isset($args[0]) && $args[0] != "") {
            $pwd = isset($args[1]) ? Utils::Hash(implode(" ", array_slice($args, 1))) : "";
            Context::SwitchChannel($user, $args[0], $pwd);
        }
    }
}