/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="sock.ts" />
/// <reference path="cookies.ts" />
/// <reference path="sound.ts" />

class Chat {
    static Main(addr: string) {
        UserContext.users = {};
        Cookies.Prepare();

        (<HTMLSelectElement>document.getElementById("styledd")).value = Cookies.Get(Cookie.Style);
        UI.ChangeStyle();

        UI.RedrawDropDowns();
        (<HTMLSelectElement>document.getElementById("langdd")).value = Cookies.Get(Cookie.Language);
        UI.RenderLanguage();

        Sounds.ChangePack(Cookies.Get(Cookie.Soundpack));

        UI.RenderEmotes();
        Socket.Init(addr);
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
}