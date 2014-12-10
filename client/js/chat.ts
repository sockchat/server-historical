/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="sock.ts" />
/// <reference path="cookies.ts" />
/// <reference path="sound.ts" />
/// <reference path="utils.js" />

class Chat {
    static Main(addr: string) {
        UserContext.users = {};
        Cookies.Prepare();
        Chat.LoadJSONFiles();
        Socket.args = Utils.FetchPage("./index.php?view=auth").split("\f");

        (<HTMLSelectElement>document.getElementById("styledd")).value = Cookies.Get(Cookie.Style);
        UI.ChangeStyle();

        UI.RedrawDropDowns();
        (<HTMLSelectElement>document.getElementById("langdd")).value = Cookies.Get(Cookie.Language);
        UI.RenderLanguage();

        Sounds.ChangePack(Cookies.Get(Cookie.Soundpack));

        UI.RenderEmotes();
        Socket.Init(addr);
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

        if(dd.options[dd.selectedIndex].text[0] == "*" && !UserContext.self.canModerate()) {
            document.getElementById("chname").innerHTML = dd.value;
            (<HTMLInputElement>document.getElementById("chpwd")).value = "";
            document.getElementById("pwdPrompt").style.display = "table-cell";
        } else Chat.SendMessageWrapper("/join "+ dd.value);

        dd.value = UserContext.self.channel;
    }

    static ChangeChannelWithPassword() {
        document.getElementById("pwdPrompt").style.display = "none";
        Chat.SendMessageWrapper("/join "+ document.getElementById("chname").innerHTML +" "+ (<HTMLInputElement>document.getElementById("chpwd")).value);
        (<HTMLSelectElement>document.getElementById("channeldd")).value = UserContext.self.channel;
    }
}