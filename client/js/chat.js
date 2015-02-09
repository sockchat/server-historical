/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="sock.ts" />
/// <reference path="cookies.ts" />
/// <reference path="sound.ts" />
/// <reference path="lang.ts" />
/// <reference path="utils.ts" />
/// <reference path="notify.ts" />
var Chat = (function () {
    function Chat() {
    }
    Chat.Main = function (addr) {
        if (Socket.args[0] == "yes") {
            Chat.LoadJSONFiles();
            document.getElementById("styledd").value = Cookies.Get(2 /* Style */);
            UI.ChangeStyle();
            UI.RedrawDropDowns();
            document.getElementById("langdd").value = Cookies.Get(1 /* Language */);
            UI.RenderLanguage();
            Sounds.ChangePack(Cookies.Get(0 /* Soundpack */));
            Chat.HideSidebars();
            if (!UI.IsMobileView())
                document.getElementById("userList").style.display = "block";
            UI.RenderEmotes();
            UI.RenderIcons();
            UI.RenderButtons();
            Notify.Init();
            UI.ChangeDisplay(false, "conn");
            UserContext.users = {};
            Socket.args = Socket.args.slice(1);
            Socket.Init(addr);
        }
        else
            window.location.href = Socket.redirectUrl;
    };
    Chat.HandleMessage = function (e) {
        var key = ('which' in e) ? e.which : e.keyCode;
        if (key == 13 && !e.shiftKey) {
            Chat.SendMessage();
            e.preventDefault();
            return false;
        }
        else
            return true;
    };
    Chat.LoadJSONFiles = function () {
        var tmp = JSON.parse(Utils.FetchPage("conf/bbcode.json?a=" + Utils.Random(1000000000, 9999999999)));
        tmp.bbcode.forEach(function (elt, i, arr) {
            UI.bbcode.push(elt);
        });
        tmp = JSON.parse(Utils.FetchPage("conf/emotes.json?a=" + Utils.Random(1000000000, 9999999999)));
        tmp.emotes.forEach(function (elt, i, arr) {
            UI.emotes.push(Array(elt["img"], elt["syn"]));
        });
        tmp = JSON.parse(Utils.FetchPage("conf/icons.json?a=" + Utils.Random(1000000000, 9999999999)));
        tmp.icons.forEach(function (elt, i, arr) {
            UI.icons.push(Array(elt["img"], elt["action"]));
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
        Chat.SendMessageWrapper("/join " + dd.value + (dd.options[dd.selectedIndex].text[0] == "*" && !UserContext.self.canModerate() ? " " + prompt("Enter password for " + dd.value, "") : ""));
    };
    Chat.HideSidebars = function () {
        var sidebars = document.getElementsByClassName("sidebar");
        for (var i = 0; i < sidebars.length; i++)
            sidebars[i].style.display = "none";
        var sidebars = document.getElementsByClassName("widebar");
        for (var i = 0; i < sidebars.length; i++)
            sidebars[i].style.display = "none";
    };
    Chat.ToggleSidebar = function (id, wide) {
        if (wide === void 0) { wide = true; }
        var open = document.getElementById(id).style.display != "none";
        Chat.HideSidebars();
        if (!open) {
            document.getElementById(id).style.display = "block";
            document.getElementById("chatList").className = wide ? "wideSideVisible" : "userListVisible";
        }
        else
            document.getElementById("chatList").className = "fullWidth";
    };
    return Chat;
})();
//# sourceMappingURL=chat.js.map