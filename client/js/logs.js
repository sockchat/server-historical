/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="sock.ts" />
/// <reference path="cookies.ts" />
/// <reference path="sound.ts" />
/// <reference path="lang.ts" />
/// <reference path="utils.ts" />
/// <reference path="notify.ts" />
/// <reference path="chat.ts" />
var Logs = (function () {
    function Logs() {
    }
    Logs.HandleDataChange = function (e) {
        var key = ('which' in e) ? e.which : e.keyCode;
        if (key == 13) {
            Logs.BindParameters();
            e.preventDefault();
            return false;
        }
        else
            return true;
    };
    Logs.FetchUpdate = function (id, off) {
        if (off === void 0) { off = 0; }
        var params = "";
        for (var p in Logs.params) {
            if (Logs.params[p] != null)
                params += "&" + p + "=" + Logs.params[p];
        }
        if (off != 0)
            params += "&offset=" + off;
        if (Logs.fetchId != id)
            return;
        var data = Utils.FetchPage("./?view=rawlogs" + Logs.authString + params);
        if (Logs.fetchId != id)
            return;
        var more;
        switch (data[0]) {
            case "x":
                UI.AddMessage("log", Utils.UnixNow(), UI.ChatBot, Utils.formatBotMessage("1", "logimper", []), false, false, "10010", true);
                return;
            case "u":
                UI.AddMessage("log", Utils.UnixNow(), UI.ChatBot, Utils.formatBotMessage("1", "logerr", []), false, false, "10010", true);
                return;
            case "y":
                more = true;
                break;
            case "n":
                more = false;
                break;
            default:
                UI.AddMessage("log", Utils.UnixNow(), UI.ChatBot, Utils.formatBotMessage("1", "generr", []), false, false, "10010", true);
                return;
        }
        var msgs = data.split("\r\t\n\f").slice(1);
        for (var i = more ? 1 : 0; i < msgs.length; i++) {
            if (msgs[i].trim() != "") {
                var msg = msgs[i].split("\t");
                var user = (msg[2] == "-1") ? UI.ChatBot : new User(+msg[2], msg[3], msg[4], "");
                UI.AddMessage(msg[0], +msg[1], user, msg[7], false, false, msg[8], true);
            }
        }
        if (more)
            this.thread = setTimeout("Logs.FetchUpdate(" + Logs.fetchId + ", " + (+msgs[0] + 1) + ");", 1000);
    };
    Logs.BindParameters = function (force) {
        if (force === void 0) { force = false; }
        if (!Logs.ready)
            return;
        try {
            clearTimeout(Logs.thread);
        }
        catch (e) {
        }
        document.getElementById("chatList").innerHTML = "";
        Logs.fetchId++;
        if (!force) {
            var data = ["channeldd", "username", "msg", "year", "month", "day", "time"];
            for (var i in data)
                data[i] = document.getElementById(data[i]).value;
            Logs.params["channel"] = data[0] == "" ? null : data[0];
            Logs.params["name"] = data[1].trim() == "" ? null : data[1].trim();
            Logs.params["msg"] = data[2].trim() == "" ? null : data[2].trim();
            if (data[3] != "") {
                var start = new Date(), end = new Date();
                if (data[4] != "") {
                    if (data[5] != "") {
                        if (data[6] != "") {
                            start.setFullYear(+data[3], +data[4], +data[5]);
                            start.setHours(+data[6], 0, 0, 0);
                            end.setFullYear(+data[3], +data[4], +data[5]);
                            end.setHours(+data[6] + 1, 0, 0, 0);
                        }
                        else {
                            start.setFullYear(+data[3], +data[4], +data[5]);
                            start.setHours(0, 0, 0, 0);
                            end.setFullYear(+data[3], +data[4], +data[5] + 1);
                            end.setHours(0, 0, 0, 0);
                        }
                    }
                    else {
                        start.setFullYear(+data[3], +data[4], 0);
                        start.setHours(0, 0, 0, 0);
                        end.setFullYear(+data[3], +data[4] + 1, 0);
                        end.setHours(0, 0, 0, 0);
                    }
                }
                else {
                    start.setFullYear(+data[3], 0, 0);
                    start.setHours(0, 0, 0, 0);
                    end.setFullYear(+data[3] + 1, 0, 0);
                    end.setHours(0, 0, 0, 0);
                }
                Logs.params["high"] = Utils.UnixTime(end) - 1;
                Logs.params["low"] = Utils.UnixTime(start);
            }
            else {
                Logs.params["high"] = null;
                Logs.params["low"] = null;
            }
        }
        this.thread = setTimeout("Logs.FetchUpdate(" + Logs.fetchId + ");", 0);
    };
    Logs.Main = function () {
        var elements = ["channeldd", "username", "msg", "year", "month", "day", "time"];
        for (var ifdg in elements) {
            document.getElementById(elements[ifdg]).value = "";
        }
        Logs.authString = "&";
        for (var i = 0; i < Socket.args.length; i++) {
            Logs.authString += (i != 0 ? "&" : "") + "arg" + (i + 1) + "=" + Socket.args[i];
        }
        var channels = Utils.FetchPage("./?view=rawlogs" + Logs.authString + "&channels=list");
        switch (channels[0]) {
            case "x":
                UI.AddMessage("log", Utils.UnixNow(), UI.ChatBot, Utils.formatBotMessage("1", "logimper", []), false, false, "10010", true);
                break;
            case "u":
                UI.AddMessage("log", Utils.UnixNow(), UI.ChatBot, Utils.formatBotMessage("1", "logerr", []), false, false, "10010", true);
                break;
            case "l":
                var chans = channels.split("\n").slice(1);
                for (var chan in chans) {
                    if (chans[chan] == "@dead") {
                        var opt = document.createElement("option");
                        opt.text = " ";
                        opt.disabled = true;
                        document.getElementById("channeldd").add(opt);
                    }
                    else {
                        UI.AddChannel(chans[chan], false, false);
                    }
                }
                break;
            default:
                UI.AddMessage("log", Utils.UnixNow(), UI.ChatBot, Utils.formatBotMessage("1", "generr", []), false, false, "10010", true);
        }
        Logs.ready = true;
        Logs.params["high"] = Utils.UnixNow();
        Logs.params["low"] = Utils.UnixNow() - 1800;
        Logs.BindParameters(true);
    };
    Logs.authString = "";
    Logs.params = {
        "channel": null,
        "high": null,
        "low": null,
        "name": null,
        "msg": null
    };
    Logs.thread = 0;
    Logs.fetchId = 0;
    Logs.ready = false;
    return Logs;
})();
//# sourceMappingURL=logs.js.map