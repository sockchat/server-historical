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
//declare function ColorPicker(id: any, fuck: any, func: any);
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
            //Chat.HideSidebars();
            UI.ChangeSidebar(null);
            UI.ToggleChannelMenu(false);
            if (!UI.IsMobileView() && !logs)
                UI.ChangeSidebar("userList");
            if (!UI.IsMobileView() && logs)
                UI.ChangeSidebar("settingsList");
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
            try {
                var persist = JSON.parse(Cookies.Get(3 /* Persist */));
            }
            catch (e) {
                persist = {};
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
                if (elt["persist"] === true && !logs) {
                    if (Chat.Persist[elt["tag"]] === undefined) {
                        Chat.Persist[elt["tag"]] = {
                            "style": elt["pstyle"],
                            "enable": persist[elt["tag"]] != undefined ? persist[elt["tag"]]["enable"] : (elt["pdef"] != undefined ? elt["pdef"] : false),
                            "value": persist[elt["tag"]] != undefined ? persist[elt["tag"]]["value"] : false
                        };
                        var row = table.insertRow(cnt);
                        row.className = cnt % 2 == 0 ? "rowOdd" : "rowEven";
                        row.setAttribute("name", ";;" + elt["tag"]);
                        var cell = row.insertCell(0);
                        cell.innerHTML = "persist " + elt["tag"];
                        cell = row.insertCell(1);
                        cell.className = "setting";
                        input = document.createElement("input");
                        input.setAttribute("type", "checkbox");
                        input.checked = Chat.Persist[elt["tag"]]["enable"];
                        input.onchange = function (e) {
                            Chat.Persist[elt['tag']]["enable"] = this.checked;
                            Chat.Persist[elt['tag']]["value"] = false;
                            Chat.BindPersist();
                        };
                        cell.appendChild(input);
                        cnt++;
                    }
                }
            });
            Chat.BindBBEnable();
            if (!logs)
                Chat.BindPersist();
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
        var style = "";
        for (var i in Chat.Persist) {
            if (Chat.Persist[i]["enable"] && Chat.Persist[i]["value"] != false) {
                style += Utils.replaceAll(Chat.Persist[i]["style"], "{0}", Chat.Persist[i]["value"]);
            }
        }
        document.getElementById("message").setAttribute("style", style);
    };
    Chat.HandleMessage = function (e) {
        var key = ('which' in e) ? e.which : e.keyCode;
        if (key == 13 && !e.shiftKey) {
            Chat.SendMessage();
            if (e.preventDefault)
                e.preventDefault();
            return false;
        }
        else if (key == 9) {
            var box = document.getElementById("message");
            var pos = UI.GetCursorPosition();
            if (pos > 0) {
                for (var i = pos - 1; i >= 0; i--) {
                    if (box.value.charAt(i) == " " || box.value.charAt(i) == "\n") {
                        i++;
                        break;
                    }
                }
                var search = box.value.substring(i, pos);
                if (search != "") {
                    var matches = [];
                    for (var user in UserContext.users) {
                        var val = UserContext.users[user].username;
                        if (val.substr(0, search.length).toLowerCase() == search.toLowerCase())
                            matches.push(val);
                    }
                    val = UserContext.self.username;
                    if (val.substr(0, search.length).toLowerCase() == search.toLowerCase())
                        matches.push(val);
                    var ret = search;
                    if (matches.length == 1) {
                        ret = matches[0];
                    }
                    else if (matches.length > 1) {
                        var closest = "";
                        for (var v in matches) {
                            for (var m in matches) {
                                var index = matches[v].length > matches[m].length ? matches[m].length : matches[v].length;
                                for (; index >= 0; index--) {
                                    if (matches[v].substr(0, index).toLowerCase() == matches[m].substr(0, index).toLowerCase()) {
                                        if (closest == "" || closest.length > index)
                                            closest = matches[v].substr(0, index).toLowerCase();
                                        break;
                                    }
                                }
                                if (closest.toLowerCase() == search.toLowerCase())
                                    break;
                            }
                            if (closest.toLowerCase() == search.toLowerCase())
                                break;
                        }
                        ret = closest;
                    }
                    UI.InsertChatText(ret.substr(search.length));
                }
            }
            if (e.preventDefault)
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
        tmp = JSON.parse(Utils.FetchPage("conf/context.json?a=" + Utils.Random(1000000000, 9999999999)));
        tmp.contextFields.forEach(function (elt, i, arr) {
            UI.contextMenuFields.push(elt);
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
        var ignore = false;
        if (msg.trim() == "")
            ignore = true;
        if (msg.trim().charAt(0) != "/") {
            for (var i in Chat.Persist) {
                if (Chat.Persist[i]["enable"] && Chat.Persist[i]["value"] != false) {
                    if (Chat.Persist[i]["value"] != true)
                        msg = "[" + i + "=" + Chat.Persist[i]['value'] + "]" + msg + "[/" + i + "]";
                    else
                        msg = "[" + i + "]" + msg + "[/" + i + "]";
                }
            }
        }
        if (!ignore)
            Chat.SendMessageWrapper(msg);
        document.getElementById("message").value = "";
        document.getElementById("message").focus();
    };
    Chat.SendMessageWrapper = function (msg) {
        if (msg.trim() != "")
            Socket.Send(Message.Pack(2, [ChannelContext.activeChannel, msg]));
    };
    Chat.ChangeChannel = function () {
        //var dd = <HTMLSelectElement>document.getElementById("channeldd");
        //
    };
    Chat.JoinChannel = function (name, pwd) {
        if (pwd === void 0) { pwd = false; }
        Chat.SendMessageWrapper("/join " + name + (pwd && !UserContext.self.canModerate() ? " " + prompt(UI.langs[UI.currentLang].menuText["chanpwd"].replace("{0}", name)) : ""));
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
    Chat.InsertBBCode = function (tag, arg) {
        if (arg === void 0) { arg = null; }
        if (Chat.Persist[tag] != undefined && Chat.Persist[tag]["enable"]) {
            Chat.Persist[tag]["value"] = arg == null ? !Chat.Persist[tag]["value"] : arg;
            Chat.BindPersist();
            document.getElementById("message").focus();
        }
        else {
            if (arg == null)
                UI.InsertChatText("[" + tag + "]", "[/" + tag + "]");
            else
                UI.InsertChatText("[" + tag + "=" + arg + "]", "[/" + tag + "]");
        }
    };
    Chat.ShowColorPicker = function (hide) {
        if (hide === void 0) { hide = false; }
        if (!Chat.pickerSpawned) {
            ColorPicker.fixIndicators(document.getElementById("pslideri"), document.getElementById("ppickeri"));
            ColorPicker(document.getElementById("ppicker"), document.getElementById("pslider"), function (hex, hsv, rgb, p, s) {
                if (p != undefined)
                    p.x += 40;
                ColorPicker.positionIndicators(document.getElementById("pslideri"), document.getElementById("ppickeri"), s, p);
                Chat.color = hex;
                document.getElementById("pok").style.color = hex;
            });
            Chat.pickerSpawned = true;
        }
        if (hide) {
            Chat.InsertBBCode("color", Chat.color);
        }
        document.getElementById("picker").style.display = hide ? "none" : "block";
    };
    Chat.Settings = {
        "sound": true,
        "volume": 0.5,
        "spack": Cookies.defaultVals[0]
    };
    Chat.Persist = {};
    Chat.bbEnable = {};
    Chat.pickerSpawned = false;
    Chat.color = "#000";
    return Chat;
})();
//# sourceMappingURL=chat.js.map