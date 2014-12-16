/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="sock.ts" />
/// <reference path="cookies.ts" />
/// <reference path="sound.ts" />
/// <reference path="lang.ts" />
/// <reference path="utils.ts" />
var Chat = (function () {
    function Chat() {
    }
    Chat.Main = function (addr) {
        if (Socket.args[0] == "yes") {
            Chat.LoadJSONFiles();
            Cookies.Prepare();

            document.getElementById("styledd").value = Cookies.Get(2 /* Style */);
            UI.ChangeStyle();

            UI.RedrawDropDowns();
            document.getElementById("langdd").value = Cookies.Get(1 /* Language */);
            UI.RenderLanguage();

            Sounds.ChangePack(Cookies.Get(0 /* Soundpack */));

            UI.RenderEmotes();

            UI.ChangeDisplay(false, 11);

            UserContext.users = {};
            Socket.args = Socket.args.slice(1);
            Socket.Init(addr);
        } else
            window.location.href = Socket.redirectUrl;
    };

    Chat.HandleMessage = function (e) {
        var key = ('which' in e) ? e.which : e.keyCode;

        if (key == 13 && !e.shiftKey) {
            Chat.SendMessage();
            e.preventDefault();
            return false;
        } else
            return true;
    };

    Chat.LoadJSONFiles = function () {
        var tmp = JSON.parse(Utils.FetchPage("bbcode.json"));
        tmp.bbcode.forEach(function (elt, i, arr) {
            UI.bbcode.push(elt);
        });

        tmp = JSON.parse(Utils.FetchPage("emotes.json"));
        tmp.emotes.forEach(function (elt, i, arr) {
            UI.emotes.push(Array(elt["img"], elt["syn"]));
        });

        tmp = UI.langs;
        UI.langs = [];
        tmp.forEach(function (elt, i, arr) {
            UI.langs.push(new Language(elt));
        });
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
        } else
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
