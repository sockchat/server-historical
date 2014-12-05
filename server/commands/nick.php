<?php
namespace sockchat\cmds;
use sockchat\cmds\GenericCommand;
use sockchat\Context;
use \sockchat\Message;
use sockchat\Utils;

class nick implements GenericCommand {
    public static function doCommand($user, $args) {
        if($user->canChangeNick()) {
            $name = "~". trim(Utils::SanitizeName(substr(join("_", $args), 0, Utils::$chat["MAX_NAME_LEN"]-1)));
            if(!isset($args[0])) $name = $user->GetOriginalUsername();
            if(Context::GetUserByName($name) == null) {
                $user->username = $name;
                Message::BroadcastBotMessage(MSG_NORMAL, "nick", [$user->username, $name]);
                Context::ModifyUser($user);
            } else Message::PrivateBotMessage(MSG_ERROR, "nameinuse", [$name], $user);
        } else Message::PrivateBotMessage(MSG_ERROR, "cmdna", ["/nick"], $user);
    }
}