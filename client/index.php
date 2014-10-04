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
            }
        }

        function what() {
            alert("hi");
        }
    </script>
</head>
<body onload="handleResize();Chat.Main();" onresize="handleResize();">
<div id="connmsg">
    Connecting to chat server ...
</div>
<div id="connerr" style="display: none;">
    Connection interrupted !
</div>
<div id="login" style="display: none;">
    Username: <input type="text" id="name" /> <input type="button" id="loginbtn" value="Join" onclick="Chat.AttemptLogin();" />
</div>
<div id="chat" style="display: none;">
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

            </div>
        </div>
    </div>
    <div id="footer">
        <div>
            <center>
                <br />
                <textarea type="text" cols="2" id="message" style="width: 100%" onkeydown="handleMessage(event);"></textarea>
            </center>
            <input type="button" value="Send MEssage" id="send" onclick='Chat.SendMessage();' />
        </div>
    </div>
</div>
</body>
</html>