var UI = (function () {
    function UI() {
    }
    UI.ChangeDisplay = function (id) {
        for (var i = 0; i < this.displayDivs.length; i++)
            document.getElementById(this.displayDivs[i]).style.display = "none";
        document.getElementById(this.displayDivs[id]).style.display = "block";
    };

    UI.AddMessage = function (date, name, msg) {
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEven) ? "rowEven" : "rowOdd";

        // TODO add date in somewhere
        msgDiv.innerHTML = "&nbsp;&nbsp;&nbsp;<b>" + name + ":</b> " + msg;
        document.getElementById("chatList").appendChild(msgDiv);
        this.rowEven = !this.rowEven;
    };
    UI.displayDivs = ["connmsg", "login", "chat", "conerr"];
    UI.rowEven = false;
    return UI;
})();
//# sourceMappingURL=ui.js.map
