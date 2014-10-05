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

    UI.AddMessage = function (date, name, color, msg) {
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEven) ? "rowEven" : "rowOdd";

        // TODO add date in somewhere
        var dateval = /*new Date((date + ((((UI.dst)?1:0)+UI.timezone)*3600))*1000);*/ new Date();
        var datestr = (((dateval.getHours() > 9) ? "" : "0") + dateval.getHours()) + ":" + (((dateval.getMinutes() > 9) ? "" : "0") + dateval.getMinutes()) + ":" + (((dateval.getSeconds() > 9) ? "" : "0") + dateval.getSeconds());
        msgDiv.innerHTML = "&nbsp;&nbsp;&nbsp;<span style='font-size: 0.8em;'>(" + datestr + ")</span> <span style='font-weight:bold;color:" + color + ";'>" + name + "</span>: " + msg;
        document.getElementById("chatList").appendChild(msgDiv);
        this.rowEven = !this.rowEven;
        document.getElementById("chatList").scrollTop = document.getElementById("chatList").scrollHeight;
    };

    UI.AddUser = function (id, name, color, addToContext) {
        if (typeof addToContext === "undefined") { addToContext = true; }
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEvenUsers) ? "rowEven" : "rowOdd";
        msgDiv.innerHTML = "&nbsp;<span style='font-weight:bold;color:" + color + ";'>" + name + "</span>";
        document.getElementById("userList").appendChild(msgDiv);
        this.rowEvenUsers = !this.rowEvenUsers;

        if (addToContext) {
            UserContext.users["" + id] = new User(id, name, color);
        }
    };

    UI.RemoveUser = function (id) {
        delete UserContext.users["" + id];
        this.RedrawUserList();
    };

    UI.RedrawUserList = function () {
        document.getElementById("userList").innerHTML = "";
        this.AddUser(UserContext.self.id, UserContext.self.username, UserContext.self.color, false);
        for (var key in UserContext.users) {
            this.AddUser(UserContext.users[key].id, UserContext.users[key].username, UserContext.users[key].color, false);
        }
    };
    UI.displayDivs = ["connmsg", "login", "chat", "connerr", "attemptlogin"];
    UI.rowEven = false;
    UI.rowEvenUsers = false;
    UI.currentView = 0;
    UI.useDefaultAuth = true;

    UI.timezone = 0.00;
    UI.dst = false;
    return UI;
})();
//# sourceMappingURL=ui.js.map
