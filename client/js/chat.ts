/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="sock.ts" />

class Chat {
    static Main(addr: string) {
        UserContext.users = {};
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