/// <reference path="user.ts" />
/// <reference path="utils.ts" />
/// <reference path="lang.ts" />
/// <reference path="cookies.ts" />
/// <reference path="sound.ts" />
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
        Cookies.Set(2 /* Style */, selected);

        var oldlink = document.getElementsByTagName("link").item(0);

        var newlink = document.createElement("link");
        newlink.setAttribute("rel", "stylesheet");
        newlink.setAttribute("type", "text/css");
        newlink.setAttribute("href", "./styles/" + selected + ".css");

        document.getElementsByTagName("head").item(0).replaceChild(newlink, oldlink);
    };

    UI.ChangeDisplay = function (chat, msgid, indicator, err, link) {
        if (typeof msgid === "undefined") { msgid = 0; }
        if (typeof indicator === "undefined") { indicator = true; }
        if (typeof err === "undefined") { err = ""; }
        if (typeof link === "undefined") { link = false; }
        if (chat) {
            document.getElementById("connmsg").style.display = "none";
            document.getElementById("chat").style.display = "block";
        } else {
            document.getElementById("chat").style.display = "none";
            document.getElementById("connmsg").style.display = "block";
            document.getElementById("conntxt").innerHTML = UI.langs[UI.currentLang].menuText[msgid] + err + (link ? "<br/><br/><a href='" + Socket.redirectUrl + "'>" + UI.langs[UI.currentLang].menuText[14] + "</a>" : "");
            document.getElementById("indicator").style.display = indicator ? "block" : "none";
        }
    };

    UI.RenderLanguage = function () {
        var id = document.getElementById("langdd").selectedIndex;
        this.currentLang = id;

        Cookies.Set(1 /* Language */, UI.langs[id].code);

        document.getElementById("tchan").innerHTML = UI.langs[id].menuText[0];
        document.getElementById("tstyle").innerHTML = UI.langs[id].menuText[1];
        document.getElementById("tlang").innerHTML = UI.langs[id].menuText[2];

        document.getElementById("top").innerHTML = UI.langs[id].menuText[3];
        document.getElementById("sendmsg").value = UI.langs[id].menuText[4];
        // TODO message reparsing
    };

    UI.AddMessage = function (msgid, date, u, msg, strobe, playsound) {
        if (typeof strobe === "undefined") { strobe = true; }
        if (typeof playsound === "undefined") { playsound = true; }
        var msgDiv = document.createElement("div");
        msgDiv.id = "sock_msg_" + msgid;
        msgDiv.className = (this.rowEven[0]) ? "rowEven" : "rowOdd";

        /*var timecorrection = (new Date()).getTimezoneOffset()*60000;
        var dateval = new Date((date + ((((UI.dst)?0:1)+UI.timezone)*3600))*1000 + timecorrection);*/
        var dateval = new Date(date * 1000);
        var datestr = (((dateval.getHours() > 9) ? "" : "0") + dateval.getHours()) + ":" + (((dateval.getMinutes() > 9) ? "" : "0") + dateval.getMinutes()) + ":" + (((dateval.getSeconds() > 9) ? "" : "0") + dateval.getSeconds());
        var outmsg = msg;

        if (u.id == -1)
            outmsg = UI.langs[UI.currentLang].interpretBotString(msg);

        if (playsound) {
            if (u.id == -1)
                Sounds.Play(UI.langs[UI.currentLang].isBotMessageError(msg) ? 1 /* Error */ : 0 /* ChatBot */);
            else if (u.id == UserContext.self.id)
                Sounds.Play(5 /* Send */);
            else
                Sounds.Play(4 /* Receive */);
        }

        UI.emotes.forEach(function (elem, i, arr) {
            elem[1].forEach(function (elt, j, akbar) {
                outmsg = Utils.replaceAll(outmsg, Utils.Sanitize(elt), "<img src='img/emotes/" + elem[0] + "' class='chatEmote' />");
            });
        });

        for (var i = 0; i < UI.bbcode.length; i++) {
            if (!UI.bbcode[i]["arg"]) {
                var at = 0;
                while ((at = outmsg.indexOf("[" + UI.bbcode[i]['tag'] + "]", at)) != -1) {
                    var end;
                    if ((end = outmsg.indexOf("[/" + UI.bbcode[i]['tag'] + "]", at)) != -1) {
                        var inner = Utils.StripCharacters(outmsg.substring(at + ("[" + UI.bbcode[i]['tag'] + "]").length, end), UI.bbcode[i]["rmin"] == undefined ? "" : UI.bbcode[i]["rmin"]);
                        outmsg = outmsg.substring(0, at) + Utils.replaceAll(UI.bbcode[i]['swap'], "{0}", inner) + outmsg.substring(end + ("[/" + UI.bbcode[i]['tag'] + "]").length);
                    } else
                        break;
                }
            } else {
                var at = 0;
                while ((at = outmsg.indexOf("[" + UI.bbcode[i]['tag'] + "=", at)) != -1) {
                    var start, end;
                    if ((start = outmsg.indexOf("]", at)) != -1) {
                        if ((end = outmsg.indexOf("[/" + UI.bbcode[i]['tag'] + "]", start)) != -1) {
                            var arg = Utils.StripCharacters(outmsg.substring(at + ("[" + UI.bbcode[i]['tag'] + "=").length, start), "[]" + (UI.bbcode[i]["rmarg"] == undefined ? "" : UI.bbcode[i]["rmarg"]));
                            var inner = Utils.StripCharacters(outmsg.substring(start + 1, end), UI.bbcode[i]["rmin"] == undefined ? "" : UI.bbcode[i]["rmin"]);
                            outmsg = outmsg.substring(0, at) + Utils.replaceAll(Utils.replaceAll(UI.bbcode[i]['swap'], "{1}", inner), "{0}", arg) + outmsg.substring(end + ("[/" + UI.bbcode[i]['tag'] + "]").length);
                        } else
                            break;
                    } else
                        break;
                }
            }
        }

        var tmp = outmsg.split(' ');
        for (var i = 0; i < tmp.length; i++) {
            if (tmp[i].substr(0, 7) == "http://" || tmp[i].substr(0, 8) == "https://" || tmp[i].substr(0, 6) == "ftp://")
                tmp[i] = "<a href='" + tmp[i] + "' onclick='window.open(this.href);return false;'>" + tmp[i] + "</a>";
        }
        outmsg = tmp.join(" ");

        var name = (u.id == -1) ? "<span class='botName'>" + u.username + "</span>" : u.username;
        msgDiv.innerHTML = "<span class='date'>(" + datestr + ")</span> <span style='font-weight:bold;color:" + u.color + ";'>" + name + "</span>: " + outmsg + "";
        document.getElementById("chatList").appendChild(msgDiv);
        this.rowEven[0] = !this.rowEven[0];
        document.getElementById("chatList").scrollTop = document.getElementById("chatList").scrollHeight;

        if (strobe && u.id != UserContext.self.id)
            Title.Strobe(u.username);
    };

    UI.AddUser = function (u, addToContext) {
        if (typeof addToContext === "undefined") { addToContext = true; }
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEven[1]) ? "rowEven" : "rowOdd";
        msgDiv.id = "sock_user_" + u.id;
        msgDiv.innerHTML = "<span style='color:" + u.color + ";'>" + u.username + "</span>";
        document.getElementById("userList").appendChild(msgDiv);
        this.rowEven[1] = !this.rowEven[1];

        if (addToContext) {
            UserContext.users["" + u.id] = u;
        }
    };

    UI.ModifyUser = function (u) {
        document.getElementById("sock_user_" + u.id).innerHTML = "<span style='color:" + u.color + ";'>" + u.username + "</span>";
    };

    UI.AddChannel = function (name, ispwd, istemp) {
        var opt = document.createElement("option");
        opt.text = (ispwd ? "*" : "") + (istemp ? "[" : "") + name + (istemp ? "]" : "");
        opt.value = name;
        document.getElementById("channeldd").add(opt);
    };

    UI.ModifyChannel = function (oldname, newname, ispwd, istemp) {
        var opt = Utils.GetOptionByValue(document.getElementById("channeldd"), oldname);
        opt.value = newname;
        opt.text = (ispwd ? "*" : "") + (istemp ? "[" : "") + newname + (istemp ? "]" : "");
    };

    UI.RemoveChannel = function (name) {
        var cdd = document.getElementById("channeldd");
        cdd.remove(Utils.GetOptionIndexByValue(cdd, name));
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
    UI.rowEven = [true, false];
    UI.currentView = 0;
    UI.maxMsgLen = 2000;
    UI.ChatBot = new User(-1, "ChatBot", "inherit", "");

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
