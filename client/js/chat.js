/// <reference path="sock.ts" />
var Chat = (function () {
    function Chat() {
    }
    Chat.Main = function () {
        Socket.Init("ws://aroltd.com:1212");
    };
    return Chat;
})();
//# sourceMappingURL=chat.js.map
