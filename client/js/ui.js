/// <reference path="user.ts" />
/// <reference path="utils.ts" />
/// <reference path="lang.ts" />
/// <reference path="cookies.ts" />
var Title = (function () {
    function Title() {
    }
    Title.strobeCallback = function () {
        if (Title.num > 0) {
            document.title = (Title.on ? "[@ ]" : "[ @]") + " " + Title.username + " - " + UI.chatTitle;
            Title.num--;
            Title.on = !Title.on;
        } else
            Title.Normalize();
    };

    Title.Strobe = function (name) {
        Title.num = 6;
        Title.username = name;

        if (!Title.started) {
            Title.started = true;
            setInterval("Title.strobeCallback();", 500);
        }
    };

    Title.Normalize = function () {
        document.title = UI.chatTitle;
    };
    Title.username = "";
    Title.num = 0;

    Title.started = false;
    Title.on = false;
    return Title;
})();

var Options = (function () {
    function Options() {
    }
    Options.getValue = function () {
        return 2;
    };
    return Options;
})();

var UI = (function () {
    function UI() {
    }
    UI.AppendChatText = function (sin) {
        document.getElementById("message").value += sin;
        document.getElementById("message").focus();
    };

    UI.RenderEmotes = function () {
        document.getElementById("emotes").innerHTML = "";
        UI.emotes.forEach(function (elem, i, arr) {
            var egami = document.createElement("img");
            egami.src = "img/emotes/" + elem[0];
            egami.alt = egami.title = elem[1][0];
            egami.onclick = function (e) {
                UI.AppendChatText(egami.alt);
            };
            document.getElementById("emotes").appendChild(egami);
        });
        document.getElementById("message").value = "";
    };

    UI.RedrawDropDowns = function () {
        document.getElementById("langdd").innerHTML = "";
        UI.langs.forEach(function (elem, i, arr) {
            var e = document.createElement("option");
            e.value = elem.code;
            e.innerHTML = elem.name;
            document.getElementById("langdd").appendChild(e);
        });
    };

    UI.ChangeStyle = function () {
        var selected = document.getElementById("styledd").value;
        Cookies.Set(Cookies.style, selected);

        var oldlink = document.getElementsByTagName("link").item(0);

        var newlink = document.createElement("link");
        newlink.setAttribute("rel", "stylesheet");
        newlink.setAttribute("type", "text/css");
        newlink.setAttribute("href", "./styles/" + selected + ".css");

        document.getElementsByTagName("head").item(0).replaceChild(newlink, oldlink);
    };

    UI.ChangeDisplay = function (id) {
        for (var i = 0; i < this.displayDivs.length; i++)
            document.getElementById(this.displayDivs[i]).style.display = "none";
        document.getElementById(this.displayDivs[id]).style.display = "block";
        this.currentView = id;
    };

    UI.RenderLanguage = function () {
        var id = document.getElementById("langdd").selectedIndex;
        this.currentLang = id;

        Cookies.Set(Cookies.lang, UI.langs[id].code);

        document.getElementById("tchan").innerHTML = UI.langs[id].menuText[0];
        document.getElementById("tstyle").innerHTML = UI.langs[id].menuText[1];
        document.getElementById("tlang").innerHTML = UI.langs[id].menuText[2];

        document.getElementById("top").innerHTML = UI.langs[id].menuText[3];
        document.getElementById("sendmsg").value = UI.langs[id].menuText[4];
        // TODO message reparsing
    };

    UI.AddMessage = function (date, u, msg, strobe) {
        if (typeof strobe === "undefined") { strobe = true; }
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEven[0]) ? "rowEven" : "rowOdd";

        /*var timecorrection = (new Date()).getTimezoneOffset()*60000;
        var dateval = new Date((date + ((((UI.dst)?0:1)+UI.timezone)*3600))*1000 + timecorrection);*/
        var dateval = new Date(date * 1000);
        var datestr = (((dateval.getHours() > 9) ? "" : "0") + dateval.getHours()) + ":" + (((dateval.getMinutes() > 9) ? "" : "0") + dateval.getMinutes()) + ":" + (((dateval.getSeconds() > 9) ? "" : "0") + dateval.getSeconds());
        var outmsg = msg;

        if (u.id == -1)
            outmsg = UI.langs[UI.currentLang].interpretBotString(msg);

        UI.emotes.forEach(function (elem, i, arr) {
            elem[1].forEach(function (elt, j, akbar) {
                outmsg = Utils.replaceAll(outmsg, Utils.Sanitize(elt), "<img src='img/emotes/" + elem[0] + "' class='chatEmote' />");
            });
        });

        for (var i = 0; i < UI.bbcode.length; i++)
            outmsg = outmsg.replace(UI.bbcode[i][0], UI.bbcode[i][1]);

        var tmp = outmsg.split(' ');
        for (var i = 0; i < tmp.length; i++) {
            if (tmp[i].substr(0, 7) == "http://" || tmp[i].substr(0, 8) == "https://" || tmp[i].substr(0, 6) == "ftp://")
                tmp[i] = "<a href='" + tmp[i] + "' onclick='window.open(this.href);return false;'>" + tmp[i] + "</a>";
        }
        outmsg = tmp.join(" ");

        var name = (u.id == -1) ? "<i>" + u.username + "</i>" : u.username;
        msgDiv.innerHTML = "<span class='date'>(" + datestr + ")</span> <span style='font-weight:bold;color:" + u.color + ";'>" + name + "</span>: " + outmsg + "";
        document.getElementById("chatList").appendChild(msgDiv);
        this.rowEven[0] = !this.rowEven[0];
        document.getElementById("chatList").scrollTop = document.getElementById("chatList").scrollHeight;

        if (strobe)
            Title.Strobe(u.username);
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
        document.getElementById("userList").innerHTML = '<div id="top" class="rowEven">' + UI.langs[UI.currentLang].menuText[3] + '</div>';
        this.rowEven[1] = false;
        this.AddUser(UserContext.self, false);
        for (var key in UserContext.users) {
            this.AddUser(UserContext.users[key], false);
        }
    };
    UI.chatTitle = "";
    UI.displayDivs = ["connmsg", "connclose", "chat", "connerr", "attemptlogin"];
    UI.rowEven = [true, false];
    UI.currentView = 0;
    UI.ChatBot = new User(-1, "ChatBot", "#C0C0C0");

    UI.bbcode = Array();
    UI.emotes = Array();

    UI.spacks = Array();
    UI.currentPack = 0;

    UI.currentLang = 0;

    UI.styles = Array();
    UI.currentStyle = 0;
    return UI;
})();
//# sourceMappingURL=ui.js.map
