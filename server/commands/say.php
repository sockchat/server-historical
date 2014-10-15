<?php
namespace sockchat\cmds;

include("generic_cmd.php");

class say implements GenericCommand {
    public static function doCommand($chat, $user, $arr) {
        if($user->canModerate())
            $chat->BroadcastMessage($chat->chatbot, "<i>". join(" ", $arr) ."</i>");
    }
}