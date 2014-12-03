/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="utils.ts" />
/// <reference path="sound.ts" />
var Socket = (function () {
    function Socket() {
    }
    Socket.Send = function (msg) {
        this.sock.send(msg);
    };

    Socket.Init = function (addr) {
        this.sock = new WebSocket(addr);
        this.sock.onopen = this.onConnOpen;
        this.sock.onmessage = this.onMessageRecv;
        this.sock.onerror = this.onConnError;
        this.sock.onclose = this.onConnClose;
    };

    Socket.ping = function () {
        this.sock.send(Message.Pack(0, "" + UserContext.self.id));
    };

    Socket.onConnOpen = function (e) {
        UI.ChangeDisplay(4);
        setInterval("Socket.ping();", Socket.pingTime * 1000);
        Socket.Send(Message.Pack(1, Message.PackArray(Socket.args)));
    };

    Socket.onMessageRecv = function (e) {
        var parts = e.data.split(Message.Separator);
        var msgid = +parts[0];
        parts = parts.slice(1);

        switch (msgid) {
            case 1:
                if (UI.currentView == 2) {
                    UI.AddUser(new User(+parts[1], parts[2], parts[3], parts[4]));
                    UI.AddMessage(+parts[0], UI.ChatBot, Utils.formatBotMessage("0", "join", [parts[2]]), true, false);
                    Sounds.Play(2 /* Join */);
                } else {
                    if (parts[0] == "y") {
                        UserContext.self = new User(+parts[2], parts[3], parts[4], parts[5]);
                        UI.ChangeDisplay(2);
                        UI.AddMessage(+parts[1], UI.ChatBot, Utils.formatBotMessage("0", "join", [UserContext.self.username]), false, false);
                        UI.AddUser(UserContext.self, false);

                        if (+parts[5] != 0) {
                            for (var i = 0; i < +parts[5]; i++) {
                                UI.AddUser(new User(+parts[6 + 4 * i], parts[7 + 4 * i], parts[8 + 4 * i], parts[9 + 4 * i]));
                            }
                        }
                    } else {
                        alert("Username is in use!");
                    }
                }
                break;
            case 2:
                if (+parts[1] != UserContext.self.id) {
                    if (+parts[1] != -1)
                        UI.AddMessage(+parts[0], UserContext.users[+parts[1]], parts[2]);
                    else
                        UI.AddMessage(+parts[0], UI.ChatBot, parts[2]);
                } else
                    UI.AddMessage(+parts[0], UserContext.self, parts[2]);
                break;
            case 3:
                UI.AddMessage(+parts[2], UI.ChatBot, Utils.formatBotMessage("0", "leave", [parts[1]]), true, false);
                Sounds.Play(3 /* Leave */);
                UI.RemoveUser(+parts[0]);
                break;
        }
    };

    Socket.onConnError = function (e) {
        //alert("errored! error is "+ e.get);
    };

    Socket.onConnClose = function (e) {
        //alert("closed because"+ e.reason);
        UI.ChangeDisplay(3);
        //window.location.href = Socket.redirectUrl;
    };
    return Socket;
})();
//# sourceMappingURL=sock.js.map
