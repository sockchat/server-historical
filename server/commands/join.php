<?php
namespace sockchat\cmds;
use sockchat\cmds\GenericCommand;
use sockchat\Context;
use \sockchat\Message;

class join implements GenericCommand {
    public static function doCommand($user, $args) {
        if(isset($args[0])) {
            $pwd = isset($args[1]) ? $args[1] : "";
            Context::SwitchChannel($user, $args[0], $pwd);
        }
    }
}