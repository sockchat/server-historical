/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="sock.ts" />
/// <reference path="cookies.ts" />
/// <reference path="sound.ts" />
/// <reference path="lang.ts" />
/// <reference path="utils.ts" />
/// <reference path="notify.ts" />
/// <reference path="logs.ts" />
var Chat = (function () {
    function Chat() {
    }
    Chat.Main = function (addr, logs) {
        if (logs === void 0) { logs = false; }
        if (Socket.args[0] == "yes") {
            Chat.LoadJSONFiles();
            document.getElementById("styledd").value = Cookies.Get(1 /* Style */);
            UI.ChangeStyle();
            UI.RedrawDropDowns();
            document.getElementById("langdd").value = Cookies.Get(0 /* Language */);
            Chat.HideSidebars();
            if (!UI.IsMobileView() && !logs)
                document.getElementById("userList").style.display = "block";
            if (!UI.IsMobileView() && logs)
                document.getElementById("settingsList").style.display = "block";
            var tmp = JSON.parse(Utils.FetchPage("conf/settings.json?a=" + Utils.Random(1000000000, 9999999999)));
            var table = document.getElementById("settingsList").getElementsByTagName("table")[0];
            var cnt = 0;
            tmp.settings.forEach(function (elt, i, arr) {
                Chat.Settings[elt["id"]] = elt["default"] != undefined ? elt["default"] : null;
                var row = table.insertRow(i);
                row.className = cnt % 2 == 0 ? "rowOdd" : "rowEven";
                row.setAttribute("name", elt["id"]);
                var cell = row.insertCell(0);
                cell.innerHTML = elt["id"];
                cell = row.insertCell(1);
                cell.className = "setting";
                var input = null;
                switch (elt["type"]) {
                    case "select":
                        input = document.createElement("select");
                        input.onchange = function (e) {
                            var value = this.value;
                            Chat.Settings[elt["id"]] = value;
                            Chat.BindSettings();
                            eval(elt["change"]);
                        };
                        if (elt["options"] != undefined) {
                            for (var val in elt["options"]) {
                                var option = document.createElement("option");
                                option.value = val;
                                option.innerHTML = elt["options"][val];
                                input.appendChild(option);
                            }
                        }
                        cell.appendChild(input);
                        break;
                    case "checkbox":
                        input = document.createElement("input");
                        input.setAttribute("type", "checkbox");
                        input.onchange = function (e) {
                            var value = this.checked;
                            Chat.Settings[elt["id"]] = value;
                            Chat.BindSettings();
                            eval(elt["change"]);
                        };
                        cell.appendChild(input);
                        break;
                    default:
                        input = document.createElement("input");
                        input.setAttribute("type", elt["type"]);
                        input.onchange = function (e) {
                            var value = this.value;
                            Chat.Settings[elt["id"]] = value;
                            Chat.BindSettings();
                            eval(elt["change"]);
                        };
                        cell.appendChild(input);
                }
                if (elt["load"] != undefined)
                    eval(elt["load"]);
                cnt++;
            });
            try {
                var opts = JSON.parse(Cookies.Get(2 /* Options */));
            }
            catch (e) {
                opts = {};
            }
            for (var opt in Chat.Settings) {
                if (opts[opt] != undefined)
                    Chat.Settings[opt] = opts[opt];
                try {
                    var elems = document.getElementById("settingsList").getElementsByTagName("tr");
                    var elem = null;
                    for (var i = 0; i < elems.length; i++) {
                        elem = elems[i];
                        if (elem.getAttribute("name") == opt)
                            break;
                        else
                            elem = null;
                    }
                    if (elem == null)
                        continue;
                    var input = elem.getElementsByTagName("input");
                    if (input.length > 0) {
                        input = input[0];
                        if (input.getAttribute("type") == "checkbox")
                            input.checked = Chat.Settings[opt];
                        else
                            input.value = Chat.Settings[opt];
                    }
                    else {
                        input = elem.getElementsByTagName("select")[0];
                        input.value = Chat.Settings[opt];
                    }
                    input.onchange(input);
                }
                catch (e) {
                }
            }
            Chat.BindSettings();
            try {
                opts = JSON.parse(Cookies.Get(4 /* BBEnable */));
            }
            catch (e) {
                opts = {};
            }
            UI.bbcode.forEach(function (elt, i, arr) {
                if (elt["toggle"] === true) {
                    if (Chat.bbEnable[elt["tag"]] === undefined) {
                        Chat.bbEnable[elt["tag"]] = opts[elt["tag"]] != undefined ? opts[elt["tag"]] : (elt["tdef"] != undefined ? elt["tdef"] : true);
                        var row = table.insertRow(cnt);
                        row.className = cnt % 2 == 0 ? "rowOdd" : "rowEven";
                        row.setAttribute("name", "||" + elt["tag"]);
                        var cell = row.insertCell(0);
                        cell.innerHTML = "enable " + elt["tag"];
                        cell = row.insertCell(1);
                        cell.className = "setting";
                        input = document.createElement("input");
                        input.setAttribute("type", "checkbox");
                        input.checked = Chat.bbEnable[elt["tag"]];
                        input.onchange = function (e) {
                            Chat.bbEnable[elt['tag']] = this.checked;
                            Chat.BindBBEnable();
                        };
                        cell.appendChild(input);
                        cnt++;
                    }
                }
            });
            Chat.BindBBEnable();
            Socket.args = Socket.args.slice(1);
            if (!logs) {
                Sounds.ChangeVolume(Chat.Settings["volume"]);
                UI.RenderLanguage();
                UI.RenderEmotes();
                UI.RenderIcons();
                UI.RenderButtons();
                Notify.Init();
                UI.ChangeDisplay(false, "conn");
                UserContext.users = {};
                Socket.Init(addr);
            }
            else {
                Sounds.Toggle(false);
                UI.RenderLanguage();
                UserContext.self = UI.ChatBot;
                Logs.Main();
            }
        }
        else
            window.location.href = Socket.redirectUrl;
    };
    Chat.BindSettings = function () {
        Cookies.Set(2 /* Options */, JSON.stringify(Chat.Settings));
    };
    Chat.BindBBEnable = function () {
        Cookies.Set(4 /* BBEnable */, JSON.stringify(Chat.bbEnable));
    };
    Chat.BindPersist = function () {
        Cookies.Set(3 /* Persist */, JSON.stringify(Chat.Persist));
    };
    Chat.HandleMessage = function (e) {
        var key = ('which' in e) ? e.which : e.keyCode;
        if (key == 13 && !e.shiftKey) {
            Chat.SendMessage();
            e.preventDefault();
            return false;
        }
        else
            return true;
    };
    Chat.LoadJSONFiles = function () {
        var tmp = JSON.parse(Utils.FetchPage("conf/bbcode.json?a=" + Utils.Random(1000000000, 9999999999)));
        tmp.bbcode.forEach(function (elt, i, arr) {
            UI.bbcode.push(elt);
        });
        tmp = JSON.parse(Utils.FetchPage("conf/emotes.json?a=" + Utils.Random(1000000000, 9999999999)));
        tmp.emotes.forEach(function (elt, i, arr) {
            UI.emotes.push(Array(elt["img"], elt["syn"]));
        });
        tmp = JSON.parse(Utils.FetchPage("conf/icons.json?a=" + Utils.Random(1000000000, 9999999999)));
        tmp.icons.forEach(function (elt, i, arr) {
            UI.icons.push(Array(elt["img"], elt["action"], elt["load"]));
        });
        tmp = UI.langs;
        UI.langs = [];
        tmp.forEach(function (elt, i, arr) {
            UI.langs.push(new Language(elt));
        });
    };
    Chat.SendMessage = function () {
        var msg = document.getElementById("message").value;
        msg = msg.replace(/\t/g, "    ");
        Chat.SendMessageWrapper(msg);
        document.getElementById("message").value = "";
        document.getElementById("message").focus();
    };
    Chat.SendMessageWrapper = function (msg) {
        if (msg.trim() != "")
            Socket.Send(Message.Pack(2, "" + UserContext.self.id, msg));
    };
    Chat.ChangeChannel = function () {
        var dd = document.getElementById("channeldd");
        Chat.SendMessageWrapper("/join " + dd.value + (dd.options[dd.selectedIndex].text[0] == "*" && !UserContext.self.canModerate() ? " " + prompt(UI.langs[UI.currentLang].menuText["chanpwd"].replace("{0}", dd.value)) : ""));
    };
    Chat.HideSidebars = function () {
        var sidebars = document.getElementsByClassName("sidebar");
        for (var i = 0; i < sidebars.length; i++)
            sidebars[i].style.display = "none";
        var sidebars = document.getElementsByClassName("widebar");
        for (var i = 0; i < sidebars.length; i++)
            sidebars[i].style.display = "none";
    };
    Chat.ToggleSidebar = function (id, wide) {
        if (wide === void 0) { wide = true; }
        var open = document.getElementById(id).style.display != "none";
        Chat.HideSidebars();
        if (!open) {
            document.getElementById(id).style.display = "block";
            document.getElementById("chatList").className = wide ? "wideSideVisible" : "userListVisible";
        }
        else
            document.getElementById("chatList").className = "fullWidth";
    };
    Chat.Clear = function () {
        document.getElementById("chatList").innerHTML = "";
        UI.rowEven[0] = true;
    };
    Chat.ToggleScrolling = function (icon) {
        icon.style.backgroundPosition = UI.autoscroll ? "0px -22px" : "0px 0px";
        UI.autoscroll = !UI.autoscroll;
    };
    Chat.PrepareSound = function (icon) {
        Sounds.Toggle(Chat.Settings["sound"]);
        icon.style.backgroundPosition = Chat.Settings["sound"] ? "0px 0px" : "0px -22px";
    };
    Chat.ToggleSound = function (icon) {
        icon.style.backgroundPosition = Chat.Settings["sound"] ? "0px -22px" : "0px 0px";
        Chat.Settings["sound"] = !Chat.Settings["sound"];
        Sounds.Toggle(Chat.Settings["sound"]);
        Chat.BindSettings();
    };
    Chat.Settings = {
        "sound": true,
        "volume": 0.5,
        "spack": Cookies.defaultVals[0]
    };
    Chat.Persist = {};
    Chat.bbEnable = {};
    return Chat;
})();
//# sourceMappingURL=chat.js.map