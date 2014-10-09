var Message = (function () {
    function Message() {
    }
    Message.Pack = function (id) {
        var params = [];
        for (var _i = 0; _i < (arguments.length - 1); _i++) {
            params[_i] = arguments[_i + 1];
        }
        return id + this.Separator + params.join(this.Separator);
    };

    Message.PackArray = function (arr) {
        return arr.join(this.Separator);
    };
    Message.Separator = "\t";
    return Message;
})();
//# sourceMappingURL=msg.js.map
