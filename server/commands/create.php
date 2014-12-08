<?php
namespace sockchat\cmds;
use \sockchat\cmds\GenericCommand;
use \sockchat\Channel;
use sockchat\Context;
use \sockchat\Message;

class create implements GenericCommand {
    public static function doCommand($user, $args) {
        if($user->channelCreationPermission() != "0") {
            if(isset($args[0]) && $args[0] != "") {
                $channel = null;
                if(is_numeric($args[0]) && isset($args[1]) && $args[1] != "") {
                    $args[0] = ($args[0] > $user->getRank()) ? $user->getRank() : $args[0];
                    $channel = new Channel(implode("_", array_slice($args, 1)), "", $args[0], $user);
                } else {
                    $channel = new Channel(implode("_", $args));
                    $channel->channelOwner = $user;
                }

                $channel->channelType = ($user->channelCreationPermission() == 1) ? CHANNEL_TEMP : CHANNEL_PERM;

                if(($ret = Context::CreateChannel($channel)) == "OK") {
                    if($channel->channelType == CHANNEL_TEMP) Context::SwitchChannel($user, $channel->name, $channel->password);
                    Message::PrivateBotMessage(MSG_NORMAL, "crchan", [$channel->name], $user);
                } else
                    Message::PrivateUserMessage(Message::$bot, $user, $ret);
            } else Message::PrivateBotMessage(MSG_ERROR, "cmderr", [], $user);
        } else Message::PrivateBotMessage(MSG_ERROR, "cmdna", ["/create"], $user);
    }
}