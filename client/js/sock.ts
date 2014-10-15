/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />

class Socket {
    static sock: WebSocket;
    static args: string[];

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

    static ping() {
        this.sock.send(Message.Pack(0, "ping"));
    }

    static onConnOpen(e) {
        UI.ChangeDisplay(4);
        setInterval("Socket.ping();", 60000);
        Socket.Send(Message.Pack(1, Message.PackArray(Socket.args)));
    }

    static onMessageRecv(e) {
        var parts = (<string>e.data).split(Message.Separator);
        var msgid = +parts[0];
        parts = parts.slice(1);

        switch (msgid) {
            case 1:
                if(UI.currentView == 2) {
                    UI.AddUser(new User(+parts[1], parts[2], parts[3]));
                    UI.AddMessage(+parts[0], UI.ChatBot, "<i>"+ parts[2] +" has joined the chat.</i>");
                } else {
                    if(parts[0] == "y") {
                        UserContext.self = new User(+parts[2], parts[3], parts[4]);
                        UI.ChangeDisplay(2);
                        UI.AddMessage(+parts[1], UI.ChatBot, "<i>"+ UserContext.self.username +" has joined the chat.</i>");
                        UI.AddUser(UserContext.self, false);

                        if(+parts[5] != 0) {
                            for(var i = 0; i < +parts[5]; i++) {
                                UI.AddUser(new User(+parts[6+3*i], parts[7+3*i], parts[8+3*i]));
                            }
                        }
                    } else {
                        alert("Username is in use!");
                    }
                }
                break;
            case 2:
                if(+parts[1] != UserContext.self.id) {
                    if(+parts[1] != -1)
                        UI.AddMessage(+parts[0], UserContext.users[+parts[1]], parts[2]);
                    else
                        UI.AddMessage(+parts[0], UI.ChatBot, parts[2]);
                } else
                    UI.AddMessage(+parts[0], UserContext.self, parts[2]);
                break;
            case 3:
                UI.AddMessage(+parts[2], UI.ChatBot, "<i>"+ parts[1] +" has disconnected.</i>");
                UI.RemoveUser(+parts[0]);
                break;
        }
    }

    static onConnError(e) {
        //alert("errored! error is "+ e.get);
        UI.ChangeDisplay(3);
    }

    static onConnClose(e) {
        //alert("closed because"+ e.reason);
        UI.ChangeDisplay(1);
    }
}