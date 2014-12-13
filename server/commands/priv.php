<?php
namespace sockchat\cmds;
use sockchat\cmds\GenericCommand;
use sockchat\Context;
use \sockchat\Message;

class priv implements GenericCommand {
    public static function doCommand($user, $args) {
        if($user->canModerate() || Context::GetChannel($user->channel)->GetOwner()->id == $user->id) {
            if(!isset($args[0]) || $args[0] <= $user->getRank()) {
                Context::ChangeChannelPermission($user->channel, isset($args[0]) ? $args[0] : 0);
                Message::PrivateBotMessage(MSG_NORMAL, "cprivchan", [], $user);
            } else Message::PrivateBotMessage(MSG_ERROR, "rankerr", [], $user);
        } else Message::PrivateBotMessage(MSG_ERROR, "cmdna", ["/priv"], $user);
    }
}