/// <reference path="ui.ts" />

class Socket {
    static sock: WebSocket;

    static Init(addr: string) {
        this.sock = new WebSocket(addr);
        this.sock.onopen = this.onConnOpen;
        this.sock.onmessage = this.onMessageRecv;
        this.sock.onerror = this.onConnError;
        this.sock.onclose = this.onConnClose;
    }

    static onConnOpen(e) {
        UI.ChangeDisplay(1);
    }

    static onMessageRecv(e) {

    }

    static onConnError(e) {
        UI.ChangeDisplay(3);
    }

    static onConnClose(e) {

    }
}