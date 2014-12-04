<?php
namespace sockchat\cmds;

interface GenericCommand {
    public static function doCommand($user, $args);
}