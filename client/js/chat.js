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
        Chat.SendMessageWrapper(msg);
        document.getElementById("message").value = "";
        document.getElementById("message").focus();
    };
    Chat.SendMessageWrapper = function (msg) {
        if (msg.trim() != "")
            Socket.Send(Message.Pack(2, "" + UserContext.self.id, msg));
    };
    Chat.ChangeChannel = function () {
        var dd = document.getElementById("channeldd");
        if (dd.options[dd.selectedIndex].text[0] == "*" && !UserContext.self.canModerate()) {
            document.getElementById("chname").innerHTML = dd.value;
            document.getElementById("chpwd").value = "";
            document.getElementById("pwdPrompt").style.display = "table-cell";
        }
        else
            Chat.SendMessageWrapper("/join " + dd.value);
        dd.value = UserContext.self.channel;
    };
    Chat.ChangeChannelWithPassword = function () {
        document.getElementById("pwdPrompt").style.display = "none";
        Chat.SendMessageWrapper("/join " + document.getElementById("chname").innerHTML + " " + document.getElementById("chpwd").value);
        document.getElementById("channeldd").value = UserContext.self.channel;
    };
    return Chat;
})();
//# sourceMappingURL=chat.js.map