<?php
namespace sockchat\cmds;

interface GenericCommand {
    public static function doCommand($chat, $user, $arr);
}