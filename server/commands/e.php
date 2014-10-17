<?php
namespace sockchat\cmds;
use sockchat\cmds\GenericCommand;

class e implements GenericCommand {
    public static function doCommand($chat, $user, $arr) {
        $chat->BroadcastMessage($chat->chatbot, "<a href='http://aroltd.com'>hello</a>");
    }
}