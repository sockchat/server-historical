/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="utils.ts" />
/// <reference path="sound.ts" />
/// <reference path="lang.ts" />

class Socket {
    static sock: WebSocket;
    static args: string[];
    static pingTime: number;
    static redirectUrl: string;
    static addr: string;
    static kicked: boolean = false;
    static pinging: boolean = false;

    static Send(msg: string) {
        this.sock.send(msg);
    }

    static Init(addr: string) {
        this.addr = addr;
        this.sock = new WebSocket(addr);
        this.sock.onopen = this.onConnOpen;
        this.sock.onmessage = this.onMessageRecv;
        this.sock.onerror = this.onConnError;
        this.sock.onclose = this.onConnClose;
    }

    static ping() {
        this.sock.send(Message.Pack(0, ""+UserContext.self.id));
    }

    static onConnOpen(e) {
        if(document.getElementById("chat").style.display == "none") UI.ChangeDisplay(false, "auth");
        if(!Socket.pinging) {
            setInterval("Socket.ping();", Socket.pingTime * 1000);
            Socket.pinging = true;
        }
        UserContext.users = {};
        UI.rowEven[0] = true;
        document.getElementById("chatList").innerHTML = "";
        document.getElementById("channeldd").innerHTML = "";
        Socket.Send(Message.Pack(1, Message.PackArray(Socket.args)));
    }

    static onMessageRecv(e) {
        console.log(<string>e.data);
        var parts = (<string>e.data).split(Message.Separator);
        var msgid = +parts[0];
        parts = parts.slice(1);

        switch (msgid) {
            case 1:
                if(parts[0] != "y" && parts[0] != "n") {
                    UI.AddUser(new User(+parts[1], parts[2], parts[3], parts[4]));
                    UI.AddMessage(parts[5], +parts[0], UI.ChatBot, Utils.formatBotMessage("0","join",[parts[2]]), true, false);
                    Sounds.Play(Sound.Join);
                } else {
                    if(parts[0] == "y") {
                        UserContext.self = new User(+parts[1], parts[2], parts[3], parts[4]);
                        UserContext.self.channel = parts[5];
                        UI.maxMsgLen = +parts[6];
                        UI.ChangeDisplay(true);
                        UI.AddUser(UserContext.self, false);
                        UI.RedrawUserList();
                    } else {
                        UI.ChangeDisplay(false, parts[1], false, parts[1] == "joinfail" ? "<br/>" + Utils.GetDateTimeString(new Date(+parts[2] * 1000)) : "", true);
                    }
                }
                break;
            case 2:
                if(+parts[1] != UserContext.self.id) {
                    if(+parts[1] != -1)
                        UI.AddMessage(parts[3], +parts[0], UserContext.users[+parts[1]], parts[2], true, true, parts[4]);
                    else
                        UI.AddMessage(parts[3], +parts[0], UI.ChatBot, parts[2], true, true, parts[4]);
                } else
                    UI.AddMessage(parts[3], +parts[0], UserContext.self, parts[2], true, true, parts[4]);
                break;
            case 3:
                UI.AddMessage(parts[4], +parts[3], UI.ChatBot, Utils.formatBotMessage("0", parts[2], [parts[1]]), true, false);
                Sounds.Play(Sound.Leave);
                UI.RemoveUser(+parts[0]);
                break;
            case 4:
                switch(+parts[0]) {
                    case 0:
                        UI.AddChannel(parts[1], parts[2] == "1", parts[3] == "1");
                        break;
                    case 1:
                        UI.ModifyChannel(parts[1], parts[2], parts[3] == "1", parts[4] == "1")
                        break;
                    case 2:
                        UI.RemoveChannel(parts[1]);
                        break;
                }
                break;
            case 5:
                switch(+parts[0]) {
                    case 0:
                        if(+parts[1] != UserContext.self.id) {
                            UI.AddUser(new User(+parts[1], parts[2], parts[3], parts[4]));
                            UI.AddMessage(parts[5], Utils.UnixNow(), UI.ChatBot, Utils.formatBotMessage("0", "jchan", [parts[2]]), true, false);
                            Sounds.Play(Sound.Join);
                        }
                        break;
                    case 1:
                        if(+parts[1] != UserContext.self.id) {
                            UI.AddMessage(parts[2], Utils.UnixNow(), UI.ChatBot, Utils.formatBotMessage("0", "lchan", [UserContext.users[+parts[1]].username]), true, false);
                            UI.RemoveUser(+parts[1]);
                            Sounds.Play(Sound.Leave);
                        }
                        break;
                    case 2:
                        (<HTMLSelectElement>document.getElementById("channeldd")).value = parts[1];
                        break;
                }
                break;
            case 6:
                try {
                    var msg = document.getElementById("sock_msg_"+ parts[0]);
                    msg.parentElement.removeChild(msg);
                } catch(e) {}
                break;
            case 7:
                switch(+parts[0]) {
                    case 0:
                        for(var i = 0; i < +parts[1]; i++) {
                            if(+parts[2+5*i] != UserContext.self.id)
                                UI.AddUser(new User(+parts[2+5*i], parts[3+5*i], parts[4+5*i], parts[5+5*i], parts[6+5*i] == "1"));
                        }
                        break;
                    case 1:
                        console.log(parts);
                        UI.AddMessage(parts[7], +parts[1], (+parts[2] != -1) ? new User(+parts[2], parts[3], parts[4], parts[5]) : UI.ChatBot, parts[6], parts[8] == "1", parts[8] == "1", parts[9]);
                        break;
                    case 2:
                        for(var i = 0; i < +parts[1]; i++)
                            UI.AddChannel(parts[2+3*i], parts[3+3*i] == "1", parts[4+3*i] == "1");
                        (<HTMLSelectElement>document.getElementById("channeldd")).value = UserContext.self.channel;
                        break;
                }
                break;
            case 8:
                if(+parts[0] == 0 || +parts[0] == 3 || +parts[0] == 4) {
                    document.getElementById("chatList").innerHTML = "";
                    UI.rowEven[0] = true;
                }
                if(+parts[0] == 1 || +parts[0] == 3 || +parts[0] == 4) {
                    for(var u in UserContext.users)
                        delete UserContext.users[u];
                    UI.RedrawUserList();
                }
                if(+parts[0] == 2 || +parts[0] == 4) {
                    var tmp = <HTMLSelectElement> document.getElementById("channeldd");
                    for(var i = tmp.length-1; i >= 0; i++)
                        tmp.remove(i);
                }
                break;
            case 9:
                Socket.kicked = true;
                UI.ChangeDisplay(false, parts[0], false, parts[0] == "kick" ? "" : "<br/>"+ Utils.GetDateTimeString(new Date(+parts[1]*1000)), true);
                //window.location.href = Socket.redirectUrl;
                break;
            case 10:
                if(+parts[0] == UserContext.self.id) {
                    UserContext.self.username = parts[1];
                    UserContext.self.color = parts[2];
                    UserContext.self.permstr = parts[3];
                    UserContext.self.EvaluatePermString();

                    UI.ModifyUser(UserContext.self);
                } else {
                    UserContext.users[parts[0]].username = parts[1];
                    UserContext.users[parts[0]].color = parts[2];
                    UserContext.users[parts[0]].permstr = parts[3];
                    UserContext.users[parts[0]].EvaluatePermString();

                    UI.ModifyUser(UserContext.users[parts[0]])
                }
                break;
        }
    }

    static onConnError(e) {
        //alert("errored! error is "+ e.get);
    }

    static onConnClose(e) {
        if(!Socket.kicked) {
            if (document.getElementById("chat").style.display != "none") {
                UI.AddMessage("rc", Utils.UnixNow(), UI.ChatBot, Utils.formatBotMessage("1", "reconnect", []), false, false);
                Socket.Init(Socket.addr);
            } else UI.ChangeDisplay(false, "term", false, "<br /><br />Exit code " + e.code);
        }
    }
}