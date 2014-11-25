/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="sock.ts" />
/// <reference path="cookies.ts" />
var Chat = (function () {
    function Chat() {
    }
    Chat.Main = function (addr) {
        UserContext.users = {};
        Cookies.Prepare();

        document.getElementById("styledd").value = Cookies.Get(Cookies.style);
        UI.ChangeStyle();

        UI.RedrawDropDowns();
        document.getElementById("langdd").value = Cookies.Get(Cookies.lang);
        UI.RenderLanguage();

        UI.RenderEmotes();
        Socket.Init(addr);
    };

    Chat.SendMessage = function () {
        var msg = document.getElementById("message").value;
        msg = msg.replace(/\t/g, " ");

        if (msg.trim() != "") {
            Socket.Send(Message.Pack(2, "" + UserContext.self.id, msg));
        }

        document.getElementById("message").value = "";
        document.getElementById("message").focus();
    };
    return Chat;
})();
//# sourceMappingURL=chat.js.map
