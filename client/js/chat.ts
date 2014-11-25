/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="sock.ts" />
/// <reference path="cookies.ts" />

class Chat {
    static Main(addr: string) {
        UserContext.users = {};
        Cookies.Prepare();

        (<HTMLSelectElement>document.getElementById("styledd")).value = Cookies.Get(Cookies.style);
        UI.ChangeStyle();

        UI.RedrawDropDowns();
        (<HTMLSelectElement>document.getElementById("langdd")).value = Cookies.Get(Cookies.lang);
        UI.RenderLanguage();

        UI.RenderEmotes();
        Socket.Init(addr);
    }

    static SendMessage() {
        var msg = (<HTMLInputElement>document.getElementById("message")).value;
        msg = msg.replace(/\t/g, " ");

        if(msg.trim() != "") {
            Socket.Send(Message.Pack(2, ""+ UserContext.self.id, msg));
        }

        (<HTMLInputElement>document.getElementById("message")).value = "";
        (<HTMLInputElement>document.getElementById("message")).focus();
    }
}