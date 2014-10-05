/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
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

    Socket.onConnOpen = function (e) {
        if (UI.useDefaultAuth)
            UI.ChangeDisplay(1);
        else {
            UI.ChangeDisplay(4);
            Socket.Send(Message.Pack(1, Socket.username, Socket.color));
            UserContext.self = new User(0, Socket.username, Socket.color);
        }
    };

    Socket.onMessageRecv = function (e) {
        var msgid = e.data.charCodeAt(0);
        var parts = e.data.substr(1).split(Message.Seperator);

        switch (msgid) {
            case 1:
                if (UI.currentView == 2) {
                    UI.AddUser(+parts[1], parts[2], parts[3]);
                    UI.AddMessage(+parts[0], "<i>ChatBot</i>", "#C0C0C0", "<i>" + parts[2] + " has joined the chat.</i>");
                } else {
                    if (parts[0] == "y") {
                        UserContext.self.id = +parts[1];
                        UI.ChangeDisplay(2);
                        UI.AddMessage(+parts[3], "<i>ChatBot</i>", "#C0C0C0", "<i>" + UserContext.self.username + " has joined the chat.</i>");
                        UI.AddUser(0, UserContext.self.username, UserContext.self.color, false);

                        if (+parts[2] != 0) {
                            for (var i = 0; i < +parts[2]; i++) {
                                UI.AddUser(+parts[3 + 3 * i], parts[4 + 3 * i], parts[5 + 3 * i]);
                            }
                        }
                    } else {
                        alert("Username is in use!");
                        if (UI.useDefaultAuth) {
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
                UI.AddMessage(+parts[2], "<i>ChatBot</i>", "#C0C0C0", "<i>" + parts[1] + " has disconnected.</i>");
                UI.RemoveUser(+parts[0]);
                break;
        }
    };

    Socket.onConnError = function (e) {
        UI.ChangeDisplay(3);
    };

    Socket.onConnClose = function (e) {
    };
    return Socket;
})();
//# sourceMappingURL=sock.js.map
