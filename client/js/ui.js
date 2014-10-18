/// <reference path="user.ts" />
var UI = (function () {
    function UI() {
    }
    UI.ChangeDisplay = function (id) {
        for (var i = 0; i < this.displayDivs.length; i++)
            document.getElementById(this.displayDivs[i]).style.display = "none";
        document.getElementById(this.displayDivs[id]).style.display = "block";
        this.currentView = id;
    };

    UI.AddMessage = function (date, u, msg) {
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEven[0]) ? "rowEven" : "rowOdd";

        /*var timecorrection = (new Date()).getTimezoneOffset()*60000;
        var dateval = new Date((date + ((((UI.dst)?0:1)+UI.timezone)*3600))*1000 + timecorrection);*/
        var dateval = new Date(date * 1000);
        var datestr = (((dateval.getHours() > 9) ? "" : "0") + dateval.getHours()) + ":" + (((dateval.getMinutes() > 9) ? "" : "0") + dateval.getMinutes()) + ":" + (((dateval.getSeconds() > 9) ? "" : "0") + dateval.getSeconds());
        var outmsg = msg;
        for (var i = 0; i < UI.bbcode.length; i++)
            outmsg = outmsg.replace(UI.bbcode[i][0], UI.bbcode[i][1]);

        var tmp = outmsg.split(' ');
        for (var i = 0; i < tmp.length; i++) {
            if (tmp[i].substr(0, 7) == "http://" || tmp[i].substr(0, 8) == "https://" || tmp[i].substr(0, 6) == "ftp://")
                tmp[i] = "<a href='" + tmp[i] + "' onclick='window.open(this.href);return false;'>" + tmp[i] + "</a>";
        }
        outmsg = tmp.join(" ");

        msgDiv.innerHTML = "<span class='date'>(" + datestr + ")</span> <span style='font-weight:bold;color:" + u.color + ";'>" + u.username + "</span>: " + outmsg + "";
        document.getElementById("chatList").appendChild(msgDiv);
        this.rowEven[0] = !this.rowEven[0];
        document.getElementById("chatList").scrollTop = document.getElementById("chatList").scrollHeight;
    };

    UI.AddUser = function (u, addToContext) {
        if (typeof addToContext === "undefined") { addToContext = true; }
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEven[1]) ? "rowEven" : "rowOdd";
        msgDiv.innerHTML = "<span style='font-weight:bold;color:" + u.color + ";'>" + u.username + "</span>";
        document.getElementById("userList").appendChild(msgDiv);
        this.rowEven[1] = !this.rowEven[1];

        if (addToContext) {
            UserContext.users["" + u.id] = u;
        }
    };

    UI.RemoveUser = function (id) {
        delete UserContext.users["" + id];
        this.RedrawUserList();
    };

    UI.RedrawUserList = function () {
        document.getElementById("userList").innerHTML = "";
        this.AddUser(UserContext.self, false);
        for (var key in UserContext.users) {
            this.AddUser(UserContext.users[key], false);
        }
    };
    UI.displayDivs = ["connmsg", "connclose", "chat", "connerr", "attemptlogin"];
    UI.rowEven = [false, false];
    UI.currentView = 0;
    UI.ChatBot = new User(0, "<i>ChatBot</i>", "#C0C0C0");
    UI.bbcode = Array();

    UI.timezone = 0.00;
    UI.dst = false;
    return UI;
})();
//# sourceMappingURL=ui.js.map
