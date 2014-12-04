<?php
namespace sockchat\cmds;
use sockchat\cmds\GenericCommand;
use \sockchat\Message;

class say implements GenericCommand {
    public static function doCommand($user, $arr) {
        if($user->canModerate())
            Message::BroadcastBotMessage(MSG_NORMAL, "say", [implode(" ", $arr)]);
    }
}