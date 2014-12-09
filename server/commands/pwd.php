<?php
namespace sockchat\cmds;
use sockchat\cmds\GenericCommand;
use sockchat\Context;
use \sockchat\Message;

class pwd implements GenericCommand {
    public static function doCommand($user, $args) {
        if($user->canModerate() || Context::GetChannel($user->channel)->GetOwner()->id == $user->id) {
            Context::ChangeChannelPassword($user->channel, isset($args[0]) ? implode(" ", $args) : "");
            Message::PrivateBotMessage(MSG_NORMAL, "cpwdchan", [], $user);
        } else Message::PrivateBotMessage(MSG_ERROR, "cmdna", ["/pwd"], $user);
    }
}