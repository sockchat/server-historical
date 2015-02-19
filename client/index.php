<?php

namespace sockchat;
use \sockchat\Auth;

include("lib/template.php");
include("lib/auth.php");
include("lib/constants.php");
include("config.php");
// todo remove on release
include("lib/dbinfo.php");
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

$tpl->SetVariables(array_slice($chat, 0, count($chat)-4));

$tpl->SetVariables([
    "LANGS"             => LanguagePackHandler::getLanguagePackString($langs),
    "SOUND_PACK_HTML"   => SoundPackHandler::getSoundPackMarkup($spacks),
    "SOUND_PACKS"       => SoundPackHandler::getSoundPackString($spacks),
    "STYLES"            => StyleSheetHandler::getStyleMarkup($styles),
    "ALT_STYLES"        => StyleSheetHandler::getAlternateStyleMarkup($styles),
    "AUTH"              => Auth::$out
]);

$date = ["YEARS"    => "<option value=''>----</option>",
         "MONTHS"   => "<option value=''>--</option>",
         "DAYS"     => "<option value=''>--</option>",
         "TIME"    => "<option value=''>-----</option>"];
for($i = gmdate("Y"); $i >= 2014; $i--)
    $date["YEARS"] .= "<option value='{$i}'>{$i}</option>";
for($i = 1; $i <= 12; $i++)
    $date["MONTHS"] .= "<option value='". ($i-1) ."'>{$i}</option>";
for($i = 1; $i <= 31; $i++)
    $date["DAYS"] .= "<option value='{$i}'>{$i}</option>";
for($i = 0; $i <= 23; $i++)
    $date["TIME"] .= "<option value='{$i}'>". ($i<10?'0'.$i:$i) .":00</option>";
$tpl->SetVariables($date);

switch(isset($_GET["view"]) ? $_GET["view"] : "chat") {
    default:
    case "chat":
        $url = "tpl/chat.html";
        break;
    case "logs":
        $url = "tpl/logs.html";
        break;
    case "rawlogs":
        include("lib/logs.php");
        exit;
    case "auth":
        header("Access-Control-Allow-Origin: localhost");
        echo Auth::$out;
        exit;
}

$tpl->Serve($url);