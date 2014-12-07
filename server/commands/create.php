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

                /*
                $channel = new Channel(str_replace(" ", "_", $args[0]));
                if($user->channelCreationPermission() == 1) {
                    $channel->channelType = CHANNEL_TEMP;
                    $channel->channelOwner = $user;
                }

                if(isset($args[1]) && $args[1] != "") $channel->password = $args[1];
                if(($ret = Context::CreateChannel($channel)) == "OK") {
                    if($channel->channelType == CHANNEL_TEMP) Context::SwitchChannel($user, $channel->name, $channel->password);
                    Message::PrivateBotMessage(MSG_NORMAL, "crchan", [$channel->name], $user);
                } else
                    Message::PrivateUserMessage(Message::$bot, $user, $ret);
                */
            } else Message::PrivateBotMessage(MSG_ERROR, "cmderr", [], $user);
        } else Message::PrivateBotMessage(MSG_ERROR, "cmdna", ["/create"], $user);
    }
}