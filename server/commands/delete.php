<?php
namespace sockchat\cmds;
use sockchat\cmds\GenericCommand;
use sockchat\Context;
use \sockchat\Message;

class delete implements GenericCommand {
    public static function doCommand($user, $args) {
        if(isset($args[0]) && $args[0] != "") {
            $name = implode($args, "_");
            if(($channel = Context::GetChannel($name)) != null) {
                if($user->canModerate() || $channel->GetOwner()->id == $user->id) {
                    Context::DeleteChannel($channel);
                    Message::PrivateBotMessage(MSG_NORMAL, "delchan", [$name], $user);
                } else Message::PrivateBotMessage(MSG_ERROR, "ndchan", [$name], $user);
            } else Message::PrivateBotMessage(MSG_ERROR, "nochan", [$name], $user);
        } else Message::PrivateBotMessage(MSG_ERROR, "cmderr", [], $user);
    }
}