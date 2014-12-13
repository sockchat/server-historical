<?php
namespace sockchat\cmds;
use sockchat\cmds\GenericCommand;
use sockchat\Context;
use \sockchat\Message;

class whois implements GenericCommand {
    public static function doCommand($user, $args) {
        if($user->canModerate()) {
            if(($tgt = Context::GetUserByName($args[0])) != null) {
                Message::PrivateBotMessage(MSG_NORMAL, "ipaddr", [$args[0], $tgt->sock->remoteAddress], $user);
            } else Message::PrivateBotMessage(MSG_ERROR, "usernf", [$args[0]], $user);
        } else Message::PrivateBotMessage(MSG_ERROR, "cmdna", ["/whois"], $user);
    }
}