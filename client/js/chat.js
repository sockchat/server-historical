/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="sock.ts" />
var Chat = (function () {
    function Chat() {
    }
    Chat.Main = function () {
        UserContext.users = {};
        Socket.Init("ws://aroltd.com:1212");
    };

    Chat.AttemptLogin = function () {
        var name = document.getElementById("name").value;
        document.getElementById("name").disabled = true;
        document.getElementById("loginbtn").disabled = true;
        Socket.Send(Message.Pack(1, name, "#fff"));
        UserContext.self = new User(0, name, "#fff");
    };

    Chat.SendMessage = function () {
        var msg = document.getElementById("message").value;

        if (msg.trim() != "") {
            msg = msg.replace("<", "&lt;").replace(">", "&gt;");
            Socket.Send(Message.Pack(2, "" + UserContext.self.id, msg));
        }

        document.getElementById("message").value = "";
        document.getElementById("message").focus();
    };
    return Chat;
})();
//# sourceMappingURL=chat.js.map
