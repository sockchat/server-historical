/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="sock.ts" />
/// <reference path="cookies.ts" />
/// <reference path="sound.ts" />
/// <reference path="lang.ts" />
/// <reference path="utils.ts" />

class Chat {
    static Main(addr: string) {
        if(Socket.args[0] == "yes") {
            Chat.LoadJSONFiles();
            Cookies.Prepare();

            (<HTMLSelectElement>document.getElementById("styledd")).value = Cookies.Get(Cookie.Style);
            UI.ChangeStyle();

            UI.RedrawDropDowns();
            (<HTMLSelectElement>document.getElementById("langdd")).value = Cookies.Get(Cookie.Language);
            UI.RenderLanguage();

            Sounds.ChangePack(Cookies.Get(Cookie.Soundpack));

            UI.RenderEmotes();

            UI.ChangeDisplay(false, 11);

            UserContext.users = {};
            Socket.args = Socket.args.slice(1);
            Socket.Init(addr);
        } else window.location.href = Socket.redirectUrl;
    }

    static HandleMessage(e) : boolean {
        var key = ('which' in e) ? e.which : e.keyCode;

        if(key == 13 && !e.shiftKey) {
            Chat.SendMessage();
            e.preventDefault();
            return false;
        } else return true;
    }

    static LoadJSONFiles() {
        var tmp = JSON.parse(Utils.FetchPage("bbcode.json"));
        tmp.bbcode.forEach(function(elt, i, arr) {
            UI.bbcode.push(elt);
        });

        tmp = JSON.parse(Utils.FetchPage("emotes.json"));
        tmp.emotes.forEach(function(elt, i, arr) {
            UI.emotes.push(Array(elt["img"], elt["syn"]));
        });

        tmp = UI.langs;
        UI.langs = [];
        tmp.forEach(function(elt, i, arr) {
            UI.langs.push(new Language(<string[]>elt));
        });
    }

    static SendMessage() {
        var msg = (<HTMLInputElement>document.getElementById("message")).value;
        msg = msg.replace(/\t/g, " ");

        Chat.SendMessageWrapper(msg);

        (<HTMLInputElement>document.getElementById("message")).value = "";
        (<HTMLInputElement>document.getElementById("message")).focus();
    }

    static SendMessageWrapper(msg: string) {
        if(msg.trim() != "") Socket.Send(Message.Pack(2, ""+ UserContext.self.id, msg));
    }

    static ChangeChannel() {
        var dd = <HTMLSelectElement>document.getElementById("channeldd");
        Chat.SendMessageWrapper("/join "+ dd.value + (dd.options[dd.selectedIndex].text[0] == "*" && !UserContext.self.canModerate() ? " "+ prompt("Enter password for "+ dd.value, "") : ""));
    }
}