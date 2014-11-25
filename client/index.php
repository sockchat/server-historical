<?php
include("config.php");
include("system.php");

$inthref[0] = $chat["CINT_FILE"];
include("auth/". $inthref[$chat['INTEGRATION']]);

$packs = SoundPackHandler::getAllSoundPacks();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="charset=UTF-8" />
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 11 Sep 2001 09:11:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />
    <title><?php echo $chat["CHAT_TITLE"]; ?></title>
    <?php
    echo "<link href='./styles/". $chat["DEFAULT_STYLE"] .".css' rel='stylesheet' type='text/css' />\n";
    ?>
    <script type="text/javascript" src="js/lang.js"></script>
    <script type="text/javascript" src="js/utils.js"></script>
    <script type="text/javascript" src="js/cookies.js"></script>
    <script type="text/javascript" src="js/user.js"></script>
    <script type="text/javascript" src="js/msg.js"></script>
    <script type="text/javascript" src="js/ui.js"></script>
    <script type="text/javascript" src="js/sock.js"></script>
    <script type="text/javascript" src="js/chat.js"></script>
    <script type="text/javascript">
        var divSizes = [57, 122, 200];
        // header , footer , sidebar

        Socket.args = [<?php for($i = 0; $i < count($out["ARGS"]); $i++) { echo ($i==0?"":",") ."'". $out["ARGS"][$i] ."'"; } ?>];
        Socket.pingTime = <?php echo $chat["PING_PERIOD"]; ?>;
        Socket.redirectUrl = "<?php echo $chat["REDIRECT_ADDR"]; ?>";

        UI.chatTitle = "<?php echo addslashes($chat["CHAT_TITLE"]); ?>";
        UI.spacks = [<?php for($i = 0; $i < count($packs); $i++) { echo ($i==0?"":",") ."'". $packs[$i] ."'"; } ?>];
        UI.langs = [<?php
                        $langs = glob("./lang/*", GLOB_ONLYDIR);

                        $firstLang = "";
                        $foundDefault = false;

                        for($i = 0; $i < count($langs); $i++) {
                            if(file_exists($langs[$i] ."/common.json")) {
                                $code = substr($langs[$i], strrpos($langs[$i], "/")+1);

                                $firstLang = ($firstLang == "")?$code:$firstLang;
                                $foundDefault = ($code == $chat["DEFAULT_LANG"])?true:$foundDefault;

                                echo ($i==0?"":",") ."new Language(\"$code\", [";

                                $files = glob($langs[$i] ."/*.json");
                                for($j = 0; $j < count($files); $j++)
                                    echo ($i==0?"":",") ."JSON.parse(\"". getFileContents($files[$j]) ."\")";
                                echo "])";
                            }
                        }

                        if(!$foundDefault) $chat["DEFAULT_LANG"] = $firstLang;
                    ?>];

        Cookies.defaultVals = ["<?php echo SoundPackHandler::findDefaultPack($packs); ?>", "<?php echo $chat["DEFAULT_LANG"]; ?>", "<?php echo $chat["DEFAULT_STYLE"]; ?>", ""];

        function loadChatData() {
            var tmp = "<?php echo getFileContents("bbcode.json"); ?>";
            tmp = JSON.parse(tmp);

            tmp.bbcode.forEach(function(elt, i, arr) {
                if(elt.arg)
                    UI.bbcode.push(Array(new RegExp("\\["+ elt.tag +"=([^\\s\\]]+)\\s*\\](.*(?=\\[\\/"+ elt.tag +"\\]))\\[\\/"+ elt.tag +"\\]"), elt.swap));
                else
                    UI.bbcode.push(Array(new RegExp("\\["+ elt.tag +"\\](.*(?=\\[\\/"+ elt.tag +"\\]))\\[\\/"+ elt.tag +"\\]"), elt.swap));
            });

            tmp = "<?php echo getFileContents("emotes.json"); ?>";
            tmp = JSON.parse(tmp);

            tmp.emotes.forEach(function(elt, i, arr) {
                UI.emotes.push(Array(elt.img, elt.syn));
            });
        }
        loadChatData();

        function handleResize() {
            var header = document.getElementById("header");
            var center = document.getElementById("center");
            var footer = document.getElementById("footer");
            var message = document.getElementById("messageDiv");
            var user = document.getElementById("userDiv");

            header.style.top = "20px";
            header.style.left = "20px";
            header.style.width = (window.innerWidth-40) +"px";
            header.style.height = divSizes[0] +"px";

            center.style.top = (20+divSizes[0]+8) +"px";
            center.style.left = "20px";
            center.style.width = (window.innerWidth-42) +"px";
            var csize = window.innerHeight-divSizes[0]-divSizes[1]-40-16;
            center.style.height =  csize +"px";

            footer.style.bottom = "20px";
            footer.style.left = "20px";
            footer.style.width = (window.innerWidth-44) +"px";
            footer.style.height = divSizes[1] +"px";
            document.getElementById("message").style.width = footer.style.width;

            message.style.width = (window.innerWidth-42-divSizes[2]-10) +"px";
            user.style.width = divSizes[2] +"px";
        }

        function handleMessage(e) {
            var key = ('which' in e) ? e.which : e.keyCode;

            if(key == 13 && !e.shiftKey) {
                Chat.SendMessage();
                e.preventDefault();
                return false;
            }
        }
    </script>
</head>
<body onload="handleResize();Chat.Main('ws://<?php echo $chat["SERVER_ADDR"]; ?>');" onresize="handleResize();">
<div id="connmsg">
    Connecting to chat server ...
</div>
<div id="attemptlogin" style="display: none;">
    Logging into chat ...
</div>
<div id="connerr" style="display: none;">
    Connection interrupted !
</div>
<div id="connclose" style="display: none;">
    Connection closed !
</div>
<div id="chat" style="display: none;">
    <div id="header">
        <div>
            <div class="topleft" id="chatTitle"><?php echo $chat["CHAT_TITLE"]; ?></div>
            <div class="botleft" id="userData">
                <span id="tchan">Channel</span>:&nbsp;
                <select id="channeldd">
                    <option>Public</option>
                </select>
                &nbsp;<span id="tstyle">Style</span>:&nbsp;
                <select id="styledd" onchange="UI.ChangeStyle();">
                    <?php
                    $styles = glob("./styles/*.css");

                    foreach($styles as $style) {
                        $name = substr($style, strrpos($style, "/")+1);
                        $name = substr($name, 0, strrpos($name, "."));

                        if($name[0] != "_")
                            echo "<option value='$name' ". ($name == $chat["DEFAULT_STYLE"] ? " selected='selected'" : "") .">$name</option>\n";
                    }
                    ?>
                </select>
                &nbsp;<span id="tlang">Language</span>:&nbsp;
                <select id="langdd" onchange="UI.RenderLanguage();">
                </select>
            </div>
            <div class="topright">
                <!--
                    Remove this if you want, I don't really care.
                    Just remember that the top right corner will
                    look really boring without it.
                -->
                <div id="therearefourfundamentalfreedomseveryuserofsoftwaremusthave"><a href="https://github.com/flashii/sockchat">Sock Chat</a> &copy; <a href="http://aroltd.com">aroltd.com</a></div>
            </div>
        </div>
    </div>
    <div id="center">
        <div id="messageDiv">
            <div id="chatList">

            </div>
        </div>
        <div id="userDiv">
            <div id="userList">
                <div id="top" class="rowEven">
                    Online users
                </div>
            </div>
        </div>
    </div>
    <div id="footer">
        <textarea type="text" cols="2" id="message" style="width: 100%;" onkeypress="handleMessage(event);"></textarea>
        <div class="botleft" style="padding: 3px;">
            <span id="emotes"></span>
            <div style="margin-top: 8px;">
                <input type="button" value="Test" class="btn" />
            </div>
        </div>
        <div class="alignRight" style="margin-top: 6px;">
            <input type="button" value="Submit" id="sendmsg" class="btn" onclick='Chat.SendMessage();' />
        </div>
        <div class="botright" id="options" style="padding: 3px;">
            <?php
            $btns = ["help", "settings", "users", "audio", "autoscroll"];
            foreach($btns as $btn)
                echo '<img src="img/pixel.png" style="background: url(img/'. $btn .'.png) no-repeat scroll transparent;" />';
            ?>
        </div>
    </div>
    <div id="hidden">
        <?php SoundPackHandler::printSoundPacks($packs); ?>
    </div>
</div>
</body>
</html>