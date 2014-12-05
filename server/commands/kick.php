<?php
namespace sockchat\cmds;
use sockchat\cmds\GenericCommand;
use sockchat\Context;
use sockchat\Message;

class kick implements GenericCommand {
    public static function doCommand($user, $args) {
        if($user->canModerate()) {
            if(($target = Context::GetUserByName($args[0])) != null) {
                if($target->getRank() <= $user->getRank()) {
                    $length = (!isset($args[1]) || !is_numeric($args[1])) ? 0 : $args[1];
                    Context::KickUser($target, $length);
                } else Message::PrivateBotMessage(MSG_ERROR, "kickna", [$args[0]], $user);
            } else Message::PrivateBotMessage(MSG_ERROR, "usernf", [$args[0]], $user);
        } else Message::PrivateBotMessage(MSG_ERROR, "cmdna", ["/kick"], $user);
    }
}