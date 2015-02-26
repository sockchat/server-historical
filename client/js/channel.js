/// <reference path="user.ts" />
var Channel = (function () {
    function Channel() {
    }
    Channel.prototype.Join = function (u) {
        u = typeof u != "number" ? u.id : u;
        this.users[u] = u;
    };
    Channel.prototype.Leave = function (u) {
        u = typeof u != "number" ? u.id : u;
        if (this.users[u] != undefined)
            delete this.users[u];
    };
    Channel.prototype.GetUsers = function () {
        var ret = [];
        for (var user in this.users) {
            if (UserContext.users[user] != undefined)
                ret[user] = UserContext.users[user];
        }
        return ret;
    };
    return Channel;
})();
var ChannelContext = (function () {
    function ChannelContext() {
    }
    return ChannelContext;
})();
//# sourceMappingURL=channel.js.map