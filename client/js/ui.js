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

    UI.AddMessage = function (date, name, msg) {
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEven) ? "rowEven" : "rowOdd";

        // TODO add date in somewhere
        msgDiv.innerHTML = "&nbsp;&nbsp;&nbsp;<b>" + name + ":</b> " + msg;
        document.getElementById("chatList").appendChild(msgDiv);
        this.rowEven = !this.rowEven;
        document.getElementById("chatList").scrollTop = document.getElementById("chatList").scrollHeight;
    };

    UI.AddUser = function (id, name, addToContext) {
        if (typeof addToContext === "undefined") { addToContext = true; }
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEvenUsers) ? "rowEven" : "rowOdd";
        msgDiv.innerHTML = "&nbsp;<b>" + name + "</b>";
        document.getElementById("userList").appendChild(msgDiv);
        this.rowEvenUsers = !this.rowEvenUsers;

        if (addToContext) {
            UserContext.users["" + id] = new User(id, name);
        }
    };

    UI.RemoveUser = function (id) {
        delete UserContext.users["" + id];
        this.RedrawUserList();
    };

    UI.RedrawUserList = function () {
        document.getElementById("userList").innerHTML = "";
        this.AddUser(UserContext.self.id, UserContext.self.username, false);
        for (var key in UserContext.users) {
            this.AddUser(UserContext.users[key].id, UserContext.users[key].username, false);
        }
    };
    UI.displayDivs = ["connmsg", "login", "chat", "connerr"];
    UI.rowEven = false;
    UI.rowEvenUsers = false;
    UI.currentView = 0;
    return UI;
})();
//# sourceMappingURL=ui.js.map
