<?php

namespace sockchat;

include("lib/template.php");
include("lib/auth.php");
include("lib/constants.php");
include("config.php");
include("lib/system.php");

$chat = $GLOBALS["chat"];
$tpl = new Template();

$langs = LanguagePackHandler::getAllLanguagePacks();
$spacks = SoundPackHandler::getAllSoundPacks();
$styles = StyleSheetHandler::getAllStyles();

validateDefaults($spacks, $langs, $styles);

$tpl->SetVariables($chat);

$tpl->SetVariables([
    "LANGS"             => LanguagePackHandler::getLanguagePackString($langs),
    "SOUND_PACK_HTML"   => SoundPackHandler::getSoundPackMarkup($spacks),
    "SOUND_PACKS"       => SoundPackHandler::getSoundPackString($spacks),
    "STYLES"            => StyleSheetHandler::getStyleMarkup($styles)
]);

switch(isset($_GET["view"]) ? $_GET["view"] : "chat") {
    default:
    case "chat":
        $url = "tpl/chat.html";
        break;
    case "logs":
        $url = "tpl/logs.html";
        break;
    case "auth":
        $inthref[0] = $chat["CINT_FILE"];
        include("auth/". $inthref[$chat['INTEGRATION']]);
        exit;
}

$tpl->Serve($url);