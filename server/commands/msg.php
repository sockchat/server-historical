<?php
namespace sockchat\cmds;
use sockchat\cmds\GenericCommand;
use \sockchat\Message;

class msg implements GenericCommand {
    public static function doCommand($user, $args) {
        if($user->canModerate()) Message::BroadcastBotMessage(MSG_NORMAL, "say", [implode(" ", $args)]);
        else Message::PrivateBotMessage(MSG_ERROR, "cmdna", ["/say"], $user);
    }
}