/// <reference path="user.ts" />
/// <reference path="ui.ts" />
var Channel = (function () {
    function Channel(name, ispwd, istmp) {
        if (ispwd === void 0) { ispwd = false; }
        if (istmp === void 0) { istmp = false; }
        this.Set(name, ispwd, istmp);
    }
    Channel.prototype.Set = function (name, ispwd, istmp) {
        if (ispwd === void 0) { ispwd = false; }
        if (istmp === void 0) { istmp = false; }
        this.name = name;
        this.ispwd = ispwd;
        this.istmp = istmp;
    };
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
    ChannelContext.Create = function (name, ispwd, istmp) {
        if (ispwd === void 0) { ispwd = false; }
        if (istmp === void 0) { istmp = false; }
        ChannelContext.channels[name] = new Channel(name, ispwd, istmp);
        UI.RedrawChannelList();
    };
    ChannelContext.Modify = function (oldname, newname, ispwd, istmp) {
        if (oldname != newname) {
            ChannelContext.channels[newname] = ChannelContext.channels[oldname];
            delete ChannelContext.channels[oldname];
            if (ChannelContext.openChannels[oldname] != undefined) {
                ChannelContext.openChannels[newname] = newname;
                delete ChannelContext.openChannels[oldname];
            }
        }
        ChannelContext.channels[newname].ispwd = ispwd;
        ChannelContext.channels[newname].istmp = ispwd;
        UI.RedrawChannelList();
    };
    ChannelContext.Delete = function (name) {
        if (ChannelContext.channels[name] != undefined)
            delete ChannelContext.channels[name];
        if (ChannelContext.openChannels[name] != undefined)
            ChannelContext.Leave(name);
        else
            UI.RedrawChannelList();
    };
    ChannelContext.Join = function (name) {
        console.log(name);
        if (ChannelContext.channels[name] == undefined)
            return;
        ChannelContext.openChannels[name] = name;
        if (document.getElementById("chat." + name) == undefined)
            UI.SpawnChatList(name);
        UI.ChangeActiveChat(name);
        ChannelContext.activeChannel = name;
        UI.RedrawChannelList();
    };
    ChannelContext.Leave = function (name) {
        console.log(name);
        if (ChannelContext.openChannels[name] == undefined)
            return;
        delete ChannelContext.openChannels[name];
        UI.DeleteChatList(name);
        UI.RedrawChannelList();
    };
    ChannelContext.channels = [];
    ChannelContext.openChannels = [];
    return ChannelContext;
})();
//# sourceMappingURL=channel.js.map