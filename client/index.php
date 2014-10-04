<html>
<head>
    <title>SockChat</title>
    <link href="style.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="js/ui.js"></script>
    <script type="text/javascript" src="js/sock.js"></script>
    <script type="text/javascript" src="js/chat.js"></script>
    <script type="text/javascript">
        var divSizes = Array(75, 125);
        // header , footer

        function handleResize() {
            document.getElementById("header").style.height = divSizes[0];
            document.getElementById("center").style.height = window.innerHeight - (divSizes[0] + divSizes[1]);
            document.getElementById("footer").style.height = divSizes[1];
            document.getElementById("userList").style.marginTop = -(window.innerHeight - (divSizes[0] + divSizes[1]));
        }
    </script>
</head>
<body onload="handleResize();" onresize="handleResize();">
<div id="connmsg" style="display: none;">
    Connecting to chat server ...
</div>
<div id="connerr" style="display: none;">
    Connection interrupted !
</div>
<div id="login" style="display: none;">
    Username: <input type="text" id="name" /> <input type="button" id="login" value="Join" />
</div>
<div id="chat">
    <div id="header">
        <div>
            <div id="chatTitle">hi mom</div>
        </div>
    </div>
    <div id="center">
        <div id="messageDiv">
            <div id="chatList">

            </div>
        </div>
        <div id="userDiv">
            <div id="userList">
                <div class="rowEven"><b>malloc</b></div>
            </div>
        </div>
    </div>
    <div id="footer">
        <div>
            <center>
                <br />
                <input type="text" id="message" style="width: 97%" />
            </center>
            <input type="button" value="Send MEssage" id="send" onclick='UI.AddMessage("","malloc","test test test");' />
        </div>
    </div>
</div>
</body>
</html>