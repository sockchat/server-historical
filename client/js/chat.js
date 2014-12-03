/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="sock.ts" />
/// <reference path="cookies.ts" />
/// <reference path="sound.ts" />
var Chat = (function () {
    function Chat() {
    }
    Chat.Main = function (addr) {
        UserContext.users = {};
        Cookies.Prepare();

        document.getElementById("styledd").value = Cookies.Get(2 /* Style */);
        UI.ChangeStyle();

        UI.RedrawDropDowns();
        document.getElementById("langdd").value = Cookies.Get(1 /* Language */);
        UI.RenderLanguage();

        Sounds.ChangePack(Cookies.Get(0 /* Soundpack */));

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
