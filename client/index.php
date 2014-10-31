<?php
include("config.php");
$inthref[0] = $chat["CINT_FILE"];
include("auth/". $inthref[$chat['INTEGRATION']]);
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="charset=UTF-8" />
    <title><?php echo $chat["CHAT_TITLE"]; ?></title>
    <link href="style.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="js/user.js"></script>
    <script type="text/javascript" src="js/msg.js"></script>
    <script type="text/javascript" src="js/ui.js"></script>
    <script type="text/javascript" src="js/sock.js"></script>
    <script type="text/javascript" src="js/chat.js"></script>
    <script type="text/javascript">
        var divSizes = Array(57, 122, 200);
        // header , footer , sidebar

        Socket.args = new Array(<?php for($i = 0; $i < count($out["ARGS"]); $i++) { echo ($i==0?"":",") ."'". $out["ARGS"][$i] ."'"; } ?>);
        Socket.pingTime = <?php echo $chat["PING_PERIOD"]; ?>;
        Socket.redirectUrl = "<?php echo $chat["REDIRECT_ADDR"]; ?>";
        //UI.timezone = <?php echo $out["TIMEZONE"]; ?>;
        //UI.dst = <?php echo $out["DST"]; ?>;

        function loadBBCode() {
            var tmp = "<?php echo str_replace('"', '\\"', trim(preg_replace('/\s+/', ' ', preg_replace('!/\*.*?\*/!s', '', file_get_contents("bbcode.json"))))); ?>";
            tmp = JSON.parse(tmp);

            console.log(tmp);

            tmp.bbcode.forEach(function(elt, i, arr) {
                if(elt.arg)
                    UI.bbcode.push(Array(new RegExp("\\["+ elt.tag +"=([^\\s\\]]+)\\s*\\](.*(?=\\[\\/"+ elt.tag +"\\]))\\[\\/"+ elt.tag +"\\]"), elt.swap));
                else
                    UI.bbcode.push(Array(new RegExp("\\["+ elt.tag +"\\](.*(?=\\[\\/"+ elt.tag +"\\]))\\[\\/"+ elt.tag +"\\]"), elt.swap));
            });
        }
        loadBBCode();

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

            footer.style.bottom = "16px";
            footer.style.left = "20px";
            footer.style.width = (window.innerWidth-40) +"px";
            footer.style.height = divSizes[1] +"px";

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
            <div class="alignLeft">
                <div id="chatTitle"><?php echo $chat["CHAT_TITLE"]; ?></div>
                <div id="userData">
                    Channel:&nbsp;
                    <select>
                        <option>Public</option>
                    </select>
                    &nbsp;Style:&nbsp;
                    <select>
                        <option>black</option>
                    </select>
                    &nbsp;Language:&nbsp;
                    <select>
                        <option>English</option>
                    </select>
                </div>
            </div>
            <div class="alignRight">
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
        <textarea type="text" cols="2" id="message" style="width: 100%" onkeypress="handleMessage(event);"></textarea>
        <div class="alignLeft">

        </div>
        <div class="alignRight">
            <input type="button" value="Submit" id="send" onclick='Chat.SendMessage();' />
        </div>
    </div>
</div>
</body>
</html>