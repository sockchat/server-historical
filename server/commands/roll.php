<?php
namespace sockchat\cmds;
use sockchat\cmds\GenericCommand;

class roll implements GenericCommand {
    public static function doCommand($chat, $user, $arr) {
        $chat->BroadcastMessage($chat->chatbot, "<i><strong>" . $user . "</strong> rolled the dice and got " . mt_rand(1,10) . ".</i>");
        // will add 1d1 etc
    }
}
