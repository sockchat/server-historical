/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />

class Socket {
    static sock: WebSocket;
    static username: string;
    static color: string;
    static mod: boolean;

    static Send(msg: string) {
        this.sock.send(msg);
    }

    static Init(addr: string) {
        this.sock = new WebSocket(addr);
        this.sock.onopen = this.onConnOpen;
        this.sock.onmessage = this.onMessageRecv;
        this.sock.onerror = this.onConnError;
        this.sock.onclose = this.onConnClose;
    }

    static onConnOpen(e) {
        if(UI.useDefaultAuth)
            UI.ChangeDisplay(1);
        else {
            UI.ChangeDisplay(4);
            Socket.Send(Message.Pack(1, Socket.username, Socket.color));
            UserContext.self = new User(0, Socket.username, Socket.color);
        }
    }

    static onMessageRecv(e) {
        var msgid = (<string>e.data).charCodeAt(0);
        var parts = (<string>e.data).substr(1).split(Message.Seperator);

        switch (msgid) {
            case 1:
                if(UI.currentView == 2) {
                    UI.AddUser(+parts[1], parts[2], parts[3]);
                    UI.AddMessage(+parts[0], "<i>ChatBot</i>", "#C0C0C0", "<i>"+ parts[2] +" has joined the chat.</i>");
                } else {
                    if(parts[0] == "y") {
                        UserContext.self.id = +parts[1];
                        UI.ChangeDisplay(2);
                        UI.AddMessage(+parts[3], "<i>ChatBot</i>", "#C0C0C0", "<i>"+ UserContext.self.username +" has joined the chat.</i>");
                        UI.AddUser(0, UserContext.self.username, UserContext.self.color, false);

                        if(+parts[2] != 0) {
                            for(var i = 0; i < +parts[2]; i++) {
                                UI.AddUser(+parts[3+3*i], parts[4+3*i], parts[5+3*i]);
                            }
                        }
                    } else {
                        alert("Username is in use!");
                        if(UI.useDefaultAuth) {
                            document.getElementById("name").disabled = false;
                            document.getElementById("loginbtn").disabled = false;
                        } else {
                            // do something?
                        }
                    }
                }
                break;
            case 2:
                UI.AddMessage(+parts[0], parts[1], parts[2], parts[3]);
                break;
            case 3:
                UI.AddMessage(+parts[2], "<i>ChatBot</i>", "#C0C0C0", "<i>"+ parts[1] +" has disconnected.</i>");
                UI.RemoveUser(+parts[0]);
                break;
        }
    }

    static onConnError(e) {
        UI.ChangeDisplay(3);
    }

    static onConnClose(e) {

    }
}