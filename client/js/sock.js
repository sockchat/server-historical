/// <reference path="ui.ts" />
var Socket = (function () {
    function Socket() {
    }
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
    };

    Socket.onConnError = function (e) {
        UI.ChangeDisplay(3);
    };

    Socket.onConnClose = function (e) {
    };
    return Socket;
})();
//# sourceMappingURL=sock.js.map
