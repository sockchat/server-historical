<?php

namespace sockchat;
use \sockchat\Auth;

include("lib/template.php");
include("lib/auth.php");
include("lib/constants.php");
include("config.php");
include("lib/system.php");

$chat = $GLOBALS["chat"];
$inthref[0] = $chat["CINT_FILE"];
include("auth/". $inthref[$chat['INTEGRATION']]);

$tpl = new Template();

$langs = LanguagePackHandler::getAllLanguagePacks();
$spacks = SoundPackHandler::getAllSoundPacks();
$styles = StyleSheetHandler::getAllStyles();

validateDefaults($spacks, $langs, $styles);
validateCookies($spacks, $langs, $styles);
insertIcons($tpl);

$tpl->SetVariables($chat);

$tpl->SetVariables([
    "LANGS"             => LanguagePackHandler::getLanguagePackString($langs),
    "SOUND_PACK_HTML"   => SoundPackHandler::getSoundPackMarkup($spacks),
    "SOUND_PACKS"       => SoundPackHandler::getSoundPackString($spacks),
    "STYLES"            => StyleSheetHandler::getStyleMarkup($styles),
    "ALT_STYLES"        => StyleSheetHandler::getAlternateStyleMarkup($styles),
    "AUTH"              => Auth::$out
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
        header("Access-Control-Allow-Origin: localhost");
        echo Auth::$out;
        exit;
}

$tpl->Serve($url);