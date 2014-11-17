var Message = (function () {
    function Message() {
    }
    Message.Pack = function (id) {
        var params = [];
        for (var _i = 1; _i < arguments.length; _i++) {
            params[_i - 1] = arguments[_i];
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