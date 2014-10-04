var Message = (function () {
    function Message() {
    }
    Message.Pack = function (id) {
        var params = [];
        for (var _i = 0; _i < (arguments.length - 1); _i++) {
            params[_i] = arguments[_i + 1];
        }
        return String.fromCharCode(id) + params.join(this.Seperator);
    };
    Message.Seperator = String.fromCharCode(255);
    return Message;
})();
//# sourceMappingURL=msg.js.map
