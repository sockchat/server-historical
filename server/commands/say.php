<?php
namespace sockchat\cmds;
use sockchat\cmds\GenericCommand;

class say implements GenericCommand {
    public static function doCommand($chat, $user, $arr) {
        if($user->canModerate())
            $chat->BroadcastMessage($chat->chatbot, "<i>". join(" ", $arr) ."</i>");
    }
}