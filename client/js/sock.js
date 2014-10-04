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
        UI.ChangeDisplay(1);
    };

    Socket.onMessageRecv = function (e) {
        var msgid = e.data.charCodeAt(0);
        var parts = e.data.substr(1).split(Message.Seperator);

        switch (msgid) {
            case 1:
                if (UI.currentView == 2) {
                    UI.AddUser(+parts[1], parts[2]);
                    UI.AddMessage("", "ChatBot", parts[2] + " has joined the chat.");
                } else {
                    if (parts[0] == "y") {
                        UserContext.self.id = +parts[1];
                        UI.ChangeDisplay(2);
                        UI.AddMessage("", "ChatBot", UserContext.self.username + " has joined the chat.");
                        UI.AddUser(0, UserContext.self.username, false);

                        if (+parts[2] != 0) {
                            for (var i = 0; i < +parts[2]; i++) {
                                UI.AddUser(+parts[3 + 2 * i], parts[4 + 2 * i]);
                            }
                        }
                    } else {
                        document.getElementById("name").disabled = false;
                        document.getElementById("loginbtn").disabled = false;
                        alert("Username is in use!");
                    }
                }
                break;
            case 2:
                UI.AddMessage("", parts[1], parts[2]);
                break;
            case 3:
                UI.AddMessage("", "ChatBot", parts[1] + " has disconnected.");
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
