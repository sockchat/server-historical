/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="sock.ts" />

class Chat {
    static Main() {
        UserContext.users = {};
        Socket.Init("ws://aroltd.com:1212");
    }

    static AttemptLogin() {
        var name = (<HTMLInputElement>document.getElementById("name")).value;
        document.getElementById("name").disabled = true;
        document.getElementById("loginbtn").disabled = true;
        Socket.Send(Message.Pack(1, name, "#fff"));
        UserContext.self = new User(0, name, "#fff");
    }

    static SendMessage() {
        var msg = (<HTMLInputElement>document.getElementById("message")).value;

        if(msg.trim() != "") {
            msg = msg.replace("<","&lt;").replace(">","&gt;");
            Socket.Send(Message.Pack(2, ""+ UserContext.self.id, msg));
        }

        (<HTMLInputElement>document.getElementById("message")).value = "";
        (<HTMLInputElement>document.getElementById("message")).focus();
    }
}