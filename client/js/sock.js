/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="utils.ts" />
/// <reference path="sound.ts" />
/// <reference path="lang.ts" />
var Socket = (function () {
    function Socket() {
    }
    Socket.Send = function (msg) {
        this.sock.send(msg);
    };
    Socket.Init = function (addr) {
        this.addr = addr;
        this.sock = new WebSocket(addr);
        this.sock.binaryType = "arraybuffer";
        this.sock.onopen = this.onConnOpen;
        this.sock.onmessage = this.onMessageRecv;
        this.sock.onerror = this.onConnError;
        this.sock.onclose = this.onConnClose;
    };
    Socket.ping = function () {
        this.sock.send(Message.Pack(0, ["" + UserContext.self.id]));
    };
    Socket.onConnOpen = function (e) {
        if (document.getElementById("chat").style.display == "none")
            UI.ChangeDisplay(false, "auth");
        if (!Socket.pinging) {
            setInterval("Socket.ping();", Socket.pingTime * 1000);
            Socket.pinging = true;
        }
        UserContext.users = {};
        UI.rowEven[0] = true;
        var chats = document.getElementsByName("chatList");
        for (var i in chats) {
            try {
                chats[i].parentElement.removeChild(chats[i]);
            }
            catch (e) {
            }
        }
        //document.getElementById("channeldd").innerHTML = "";
        Socket.Send(Message.Pack(1, Socket.args));
    };
    Socket.onMessageRecv = function (e) {
        var msgobj = Message.Unpack(new Uint8Array(e.data));
        console.log(msgobj);
        if (!msgobj.valid)
            return;
        var parts = msgobj.parts;
        switch (msgobj.id) {
            case 1:
                if (parts[0] != "y" && parts[0] != "n") {
                    UI.AddUser(new User(+parts[1], parts[2], parts[3], parts[4]));
                    UI.AddMessage(parts[5], +parts[0], UI.ChatBot, Utils.formatBotMessage("0", "join", [parts[2]]), true, false);
                    Sounds.Play(2 /* Join */);
                }
                else {
                    if (parts[0] == "y") {
                        UserContext.self = new User(+parts[1], parts[2], parts[3], parts[4]);
                        UserContext.self.channel = parts[5];
                        UI.maxMsgLen = +parts[6];
                        UI.ChangeDisplay(true);
                        ChannelContext.Create(parts[5]);
                        ChannelContext.Join(parts[5]);
                        UI.AddUser(UserContext.self, false);
                        UI.RedrawUserList();
                    }
                    else {
                        UI.ChangeDisplay(false, parts[1], false, parts[1] == "joinfail" ? "<br/>" + Utils.GetDateTimeString(new Date(+parts[2] * 1000)) : "", true);
                    }
                }
                break;
            case 2:
                if (+parts[1] != UserContext.self.id) {
                    if (+parts[1] != -1)
                        UI.AddMessage(parts[3], +parts[0], UserContext.users[+parts[1]], parts[2], true, true, parts[4]);
                    else
                        UI.AddMessage(parts[3], +parts[0], UI.ChatBot, parts[2], true, true, parts[4]);
                }
                else
                    UI.AddMessage(parts[3], +parts[0], UserContext.self, parts[2], true, true, parts[4]);
                break;
            case 3:
                UI.AddMessage(parts[4], +parts[3], UI.ChatBot, Utils.formatBotMessage("0", parts[2], [parts[1]]), true, false);
                Sounds.Play(3 /* Leave */);
                UI.RemoveUser(+parts[0]);
                break;
            case 4:
                switch (+parts[0]) {
                    case 0:
                        UI.AddChannel(parts[1], parts[2] == "1", parts[3] == "1");
                        break;
                    case 1:
                        UI.ModifyChannel(parts[1], parts[2], parts[3] == "1", parts[4] == "1");
                        break;
                    case 2:
                        UI.RemoveChannel(parts[1]);
                        break;
                }
                break;
            case 5:
                switch (+parts[0]) {
                    case 0:
                        if (+parts[1] != UserContext.self.id) {
                            UI.AddUser(new User(+parts[1], parts[2], parts[3], parts[4]));
                            UI.AddMessage(parts[5], Utils.UnixNow(), UI.ChatBot, Utils.formatBotMessage("0", "jchan", [parts[2]]), true, false);
                            Sounds.Play(2 /* Join */);
                        }
                        break;
                    case 1:
                        if (+parts[1] != UserContext.self.id) {
                            UI.AddMessage(parts[2], Utils.UnixNow(), UI.ChatBot, Utils.formatBotMessage("0", "lchan", [UserContext.users[+parts[1]].username]), true, false);
                            UI.RemoveUser(+parts[1]);
                            Sounds.Play(3 /* Leave */);
                        }
                        break;
                    case 2:
                        break;
                }
                break;
            case 6:
                try {
                    var msg = document.getElementById("sock_msg_" + parts[0]);
                    msg.parentElement.removeChild(msg);
                }
                catch (e) {
                }
                break;
            case 7:
                switch (+parts[0]) {
                    case 0:
                        for (var i = 0; i < +parts[1]; i++) {
                            if (+parts[2 + 5 * i] != UserContext.self.id)
                                UI.AddUser(new User(+parts[2 + 5 * i], parts[3 + 5 * i], parts[4 + 5 * i], parts[5 + 5 * i], parts[6 + 5 * i] == "1"));
                        }
                        break;
                    case 1:
                        console.log(parts);
                        UI.AddMessage(parts[7], +parts[1], (+parts[2] != -1) ? new User(+parts[2], parts[3], parts[4], parts[5]) : UI.ChatBot, parts[6], parts[8] == "1", parts[8] == "1", parts[9]);
                        break;
                    case 2:
                        for (var i = 0; i < +parts[1]; i++)
                            ChannelContext.Create(parts[2 + 3 * i], parts[3 + 3 * i] == "1", parts[4 + 3 * i] == "1");
                        break;
                }
                break;
            case 8:
                if (+parts[0] == 0 || +parts[0] == 3 || +parts[0] == 4) {
                    document.getElementById("chatList").innerHTML = "";
                    UI.rowEven[0] = true;
                }
                if (+parts[0] == 1 || +parts[0] == 3 || +parts[0] == 4) {
                    for (var u in UserContext.users)
                        delete UserContext.users[u];
                    UI.RedrawUserList();
                }
                if (+parts[0] == 2 || +parts[0] == 4) {
                }
                break;
            case 9:
                Socket.kicked = true;
                UI.ChangeDisplay(false, parts[0], false, parts[0] == "kick" ? "" : "<br/>" + Utils.GetDateTimeString(new Date(+parts[1] * 1000)), true);
                break;
            case 10:
                if (+parts[0] == UserContext.self.id) {
                    UserContext.self.username = parts[1];
                    UserContext.self.color = parts[2];
                    UserContext.self.permstr = parts[3];
                    UserContext.self.EvaluatePermString();
                    UI.ModifyUser(UserContext.self);
                }
                else {
                    UserContext.users[parts[0]].username = parts[1];
                    UserContext.users[parts[0]].color = parts[2];
                    UserContext.users[parts[0]].permstr = parts[3];
                    UserContext.users[parts[0]].EvaluatePermString();
                    UI.ModifyUser(UserContext.users[parts[0]]);
                }
                break;
        }
    };
    Socket.onConnError = function (e) {
        //alert("errored! error is "+ e.get);
    };
    Socket.onConnClose = function (e) {
        if (!Socket.kicked) {
            if (document.getElementById("chat").style.display != "none") {
                UI.AddMessage("rc", Utils.UnixNow(), UI.ChatBot, Utils.formatBotMessage("1", "reconnect", []), false, false);
                Socket.Init(Socket.addr);
            }
            else
                UI.ChangeDisplay(false, "term", false, "<br /><br />Exit code " + e.code);
        }
    };
    Socket.kicked = false;
    Socket.pinging = false;
    return Socket;
})();
//# sourceMappingURL=sock.js.map