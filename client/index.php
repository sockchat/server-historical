<?php
include("config.php");
$inthref[0] = $chat["CINT_FILE"];
include("auth/". $inthref[$chat['INTEGRATION']]);
?>
<html>
<head>
    <title>SockChat</title>
    <link href="style.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="js/user.js"></script>
    <script type="text/javascript" src="js/msg.js"></script>
    <script type="text/javascript" src="js/ui.js"></script>
    <script type="text/javascript" src="js/sock.js"></script>
    <script type="text/javascript" src="js/chat.js"></script>
    <script type="text/javascript">
        var divSizes = Array(75, 125);
        // header , footer

        Socket.args = new Array(<?php for($i = 0; $i < count($out["ARGS"]); $i++) { echo ($i==0?"":",") ."'". $out["ARGS"][$i] ."'"; } ?>);
        UI.timezone = <?php echo $out["TIMEZONE"]; ?>;
        UI.dst = <?php echo $out["DST"]; ?>;

        function handleResize() {
            document.getElementById("header").style.height = divSizes[0];
            document.getElementById("center").style.height = window.innerHeight - (divSizes[0] + divSizes[1]);
            document.getElementById("footer").style.height = divSizes[1];
            document.getElementById("userList").style.marginTop = -(window.innerHeight - (divSizes[0] + divSizes[1]));
        }

        function handleMessage(e) {
            var key = ('which' in e) ? e.which : e.keyCode;

            if(key == 13) {
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
            <div id="chatTitle"><?php echo $chat["CHAT_TITLE"]; ?></div>
        </div>
    </div>
    <div id="center">
        <div id="messageDiv">
            <div id="chatList">

            </div>
        </div>
        <div id="userDiv">
            <div id="userList">

            </div>
        </div>
    </div>
    <div id="footer">
        <div>
            <center>
                <br />
                <textarea type="text" cols="2" id="message" style="width: 100%" onkeypress="handleMessage(event);"></textarea>
            </center>
            <input type="button" value="Send MEssage" id="send" onclick='Chat.SendMessage();' />
        </div>
    </div>
</div>
</body>
</html>