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

class Chat {
    static Settings: any = {
        "sound": true,
        "volume": 0.5,
        "spack": Cookies.defaultVals[0]
    };

    static Persist: any = {};
    static bbEnable: any = {};

    static Main(addr: string, logs: boolean = false) {
        if(Socket.args[0] == "yes") {
            Chat.LoadJSONFiles();

            (<HTMLSelectElement>document.getElementById("styledd")).value = Cookies.Get(Cookie.Style);
            UI.ChangeStyle();

            UI.RedrawDropDowns();
            (<HTMLSelectElement>document.getElementById("langdd")).value = Cookies.Get(Cookie.Language);

            Chat.HideSidebars();
            if(!UI.IsMobileView() && !logs) document.getElementById("userList").style.display = "block";
            if(!UI.IsMobileView() && logs) document.getElementById("settingsList").style.display = "block";

            var tmp = JSON.parse(Utils.FetchPage("conf/settings.json?a="+ Utils.Random(1000000000,9999999999)));
            var table = <HTMLTableElement>document.getElementById("settingsList").getElementsByTagName("table")[0];
            var cnt = 0;
            tmp.settings.forEach(function(elt, i, arr) {
                Chat.Settings[elt["id"]] = elt["default"] != undefined ? elt["default"] : null;
                var row = <HTMLTableRowElement>table.insertRow(i);
                row.className = cnt % 2 == 0 ? "rowOdd" : "rowEven";
                row.setAttribute("name", elt["id"]);
                var cell = row.insertCell(0);
                cell.innerHTML = elt["id"];
                cell = row.insertCell(1);
                cell.className = "setting";
                var input: any = null;
                switch(elt["type"]) {
                    case "select":
                        input = document.createElement("select");
                        input.onchange = function(e) { var value = this.value; Chat.Settings[elt["id"]] = value; Chat.BindSettings(); eval(elt["change"]); };
                        if(elt["options"] != undefined) {
                            for(var val in elt["options"]) {
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
                        input.onchange = function(e) { var value = this.checked; Chat.Settings[elt["id"]] = value; Chat.BindSettings(); eval(elt["change"]); };
                        cell.appendChild(input);
                        break;
                    default:
                        input = document.createElement("input");
                        input.setAttribute("type", elt["type"]);
                        input.onchange = function(e) { var value = this.value; Chat.Settings[elt["id"]] = value; Chat.BindSettings(); eval(elt["change"]); };
                        cell.appendChild(input);
                }
                if(elt["load"] != undefined) eval(elt["load"]);
                cnt++;
            });
            try {
                var opts = JSON.parse(Cookies.Get(Cookie.Options));
            } catch(e) {
                opts = {};
            }
            for(var opt in Chat.Settings) {
                if(opts[opt] != undefined) Chat.Settings[opt] = opts[opt];
                try {
                    var elems = document.getElementById("settingsList").getElementsByTagName("tr");
                    var elem = null;
                    for(var i = 0; i < elems.length; i++) {
                        elem = elems[i];
                        if(elem.getAttribute("name") == opt)
                            break;
                        else elem = null;
                    }
                    if(elem == null) continue;
                    var input = elem.getElementsByTagName("input");
                    if(input.length > 0) {
                        input = input[0];
                        if (input.getAttribute("type") == "checkbox")
                            input.checked = Chat.Settings[opt];
                        else input.value = Chat.Settings[opt];
                    } else {
                        input = elem.getElementsByTagName("select")[0];
                        input.value = Chat.Settings[opt];
                    }
                    input.onchange(input);
                } catch(e) {}
            }
            Chat.BindSettings();
            try {
                opts = JSON.parse(Cookies.Get(Cookie.BBEnable));
            } catch(e) {
                opts = {};
            }
            try {
                var persist = JSON.parse(Cookies.Get(Cookie.Persist));
            } catch(e) {
                persist = {};
            }
            UI.bbcode.forEach(function(elt, i, arr) {
                if(elt["toggle"] === true) {
                    if(Chat.bbEnable[elt["tag"]] === undefined) {
                        Chat.bbEnable[elt["tag"]] = opts[elt["tag"]] != undefined ? opts[elt["tag"]] : (elt["tdef"] != undefined ? elt["tdef"] : true);
                        var row = <HTMLTableRowElement>table.insertRow(cnt);
                        row.className = cnt % 2 == 0 ? "rowOdd" : "rowEven";
                        row.setAttribute("name", "||" + elt["tag"]);
                        var cell = row.insertCell(0);
                        cell.innerHTML = "enable " + elt["tag"];
                        cell = row.insertCell(1);
                        cell.className = "setting";
                        input = document.createElement("input");
                        input.setAttribute("type", "checkbox");
                        input.checked = Chat.bbEnable[elt["tag"]];
                        input.onchange = function(e) { Chat.bbEnable[elt['tag']] = this.checked; Chat.BindBBEnable(); };
                        cell.appendChild(input);
                        cnt++;
                    }
                }
                if(elt["persist"] === true && !logs) {
                    if(Chat.Persist[elt["tag"]] === undefined) {
                        Chat.Persist[elt["tag"]] = {
                            "style": elt["pstyle"],
                            "enable": persist[elt["tag"]] != undefined ? persist[elt["tag"]]["enable"] : (elt["pdef"] != undefined ? elt["pdef"] : false),
                            "value": persist[elt["tag"]] != undefined ? persist[elt["tag"]]["value"] : false
                        };
                        var row = <HTMLTableRowElement>table.insertRow(cnt);
                        row.className = cnt % 2 == 0 ? "rowOdd" : "rowEven";
                        row.setAttribute("name", ";;" + elt["tag"]);
                        var cell = row.insertCell(0);
                        cell.innerHTML = "persist " + elt["tag"];
                        cell = row.insertCell(1);
                        cell.className = "setting";
                        input = document.createElement("input");
                        input.setAttribute("type", "checkbox");
                        input.checked = Chat.Persist[elt["tag"]]["enable"];
                        input.onchange = function(e) { Chat.Persist[elt['tag']]["enable"] = this.checked; Chat.Persist[elt['tag']]["value"] = false; Chat.BindPersist(); };
                        cell.appendChild(input);
                        cnt++;
                    }
                }
            });
            Chat.BindBBEnable();
            if(!logs) Chat.BindPersist();

            Socket.args = Socket.args.slice(1);

            if(!logs) {
                Sounds.ChangeVolume(Chat.Settings["volume"]);
                UI.RenderLanguage();
                UI.RenderEmotes();
                UI.RenderIcons();
                UI.RenderButtons();
                Notify.Init();

                UI.ChangeDisplay(false, "conn");

                UserContext.users = {};
                Socket.Init(addr);
            } else {
                Sounds.Toggle(false);
                UI.RenderLanguage();
                UserContext.self = UI.ChatBot;
                Logs.Main();
            }
        } else window.location.href = Socket.redirectUrl;
    }

    static BindSettings() {
        Cookies.Set(Cookie.Options, JSON.stringify(Chat.Settings));
    }

    static BindBBEnable() {
        Cookies.Set(Cookie.BBEnable, JSON.stringify(Chat.bbEnable));
    }

    static BindPersist() {
        Cookies.Set(Cookie.Persist, JSON.stringify(Chat.Persist));

        var style = "";
        for(var i in Chat.Persist) {
            if(Chat.Persist[i]["enable"] && Chat.Persist[i]["value"] != false) {
                style += Utils.replaceAll(Chat.Persist[i]["style"], "{0}", Chat.Persist[i]["value"]);
            }
        }
        document.getElementById("message").setAttribute("style", style);
    }

    static HandleMessage(e) : boolean {
        var key = ('which' in e) ? e.which : e.keyCode;

        if(key == 13 && !e.shiftKey) {
            Chat.SendMessage();
            e.preventDefault();
            return false;
        } else return true;
    }

    static LoadContextMenus() {
        UI.contextMenus = { "self": [], "others": [] };
        var tmp = JSON.parse(Utils.FetchPage("conf/context.json?a="+ Utils.Random(1000000000,9999999999)));
        tmp.contextFields.forEach(function(elt, i, arr) {

        });
    }

    static LoadJSONFiles() {
        var tmp = JSON.parse(Utils.FetchPage("conf/bbcode.json?a="+ Utils.Random(1000000000,9999999999)));
        tmp.bbcode.forEach(function(elt, i, arr) {
            UI.bbcode.push(elt);
        });

        tmp = JSON.parse(Utils.FetchPage("conf/emotes.json?a="+ Utils.Random(1000000000,9999999999)));
        tmp.emotes.forEach(function(elt, i, arr) {
            UI.emotes.push(Array(elt["img"], elt["syn"]));
        });

        tmp = JSON.parse(Utils.FetchPage("conf/icons.json?a="+ Utils.Random(1000000000,9999999999)));
        tmp.icons.forEach(function(elt, i, arr) {
            UI.icons.push(Array(elt["img"], elt["action"], elt["load"]));
        });

        tmp = UI.langs;
        UI.langs = [];
        tmp.forEach(function(elt, i, arr) {
            UI.langs.push(new Language(<string[]>elt));
        });
    }

    static SendMessage() {
        var msg = (<HTMLInputElement>document.getElementById("message")).value;
        msg = msg.replace(/\t/g, "    ");

        var ignore = false;
        if(msg.trim() == "") ignore = true;

        if(msg.trim().charAt(0) != "/") {
            for (var i in Chat.Persist) {
                if (Chat.Persist[i]["enable"] && Chat.Persist[i]["value"] != false) {
                    if (Chat.Persist[i]["value"] != true)
                        msg = "["+ i +"="+ Chat.Persist[i]['value'] +"]"+ msg +"[/"+ i +"]";
                    else
                        msg = "["+ i +"]"+ msg +"[/"+ i +"]";
                }
            }
        }

        if(!ignore) Chat.SendMessageWrapper(msg);

        (<HTMLInputElement>document.getElementById("message")).value = "";
        (<HTMLInputElement>document.getElementById("message")).focus();
    }

    static SendMessageWrapper(msg: string) {
        if(msg.trim() != "") Socket.Send(Message.Pack(2, ""+ UserContext.self.id, msg));
    }

    static ChangeChannel() {
        var dd = <HTMLSelectElement>document.getElementById("channeldd");
        Chat.SendMessageWrapper("/join "+ dd.value + (dd.options[dd.selectedIndex].text[0] == "*" && !UserContext.self.canModerate() ? " "+ prompt(UI.langs[UI.currentLang].menuText["chanpwd"].replace("{0}", dd.value)) : ""));
    }

    static HideSidebars() {
        var sidebars = document.getElementsByClassName("sidebar");
        for(var i = 0; i < sidebars.length; i++)
            (<HTMLElement>sidebars[i]).style.display = "none";

        var sidebars = document.getElementsByClassName("widebar");
        for(var i = 0; i < sidebars.length; i++)
            (<HTMLElement>sidebars[i]).style.display = "none";
    }

    static ToggleSidebar(id: string, wide: boolean = true) {
        var open = document.getElementById(id).style.display != "none";
        Chat.HideSidebars();
        if(!open) {
            document.getElementById(id).style.display = "block";
            document.getElementById("chatList").className = wide ? "wideSideVisible" : "userListVisible";
        } else
            document.getElementById("chatList").className = "fullWidth";
    }

    static Clear() {
        document.getElementById("chatList").innerHTML = "";
        UI.rowEven[0] = true;
    }

    static ToggleScrolling(icon: HTMLElement) {
        icon.style.backgroundPosition = UI.autoscroll ? "0px -22px" : "0px 0px";
        UI.autoscroll = !UI.autoscroll;
    }

    static PrepareSound(icon: HTMLElement) {
        Sounds.Toggle(Chat.Settings["sound"]);
        icon.style.backgroundPosition = Chat.Settings["sound"] ? "0px 0px" : "0px -22px";
    }

    static ToggleSound(icon: HTMLElement) {
        icon.style.backgroundPosition = Chat.Settings["sound"] ? "0px -22px" : "0px 0px";
        Chat.Settings["sound"] = !Chat.Settings["sound"];
        Sounds.Toggle(Chat.Settings["sound"]);
        Chat.BindSettings();
    }

    static InsertBBCode(tag: string, arg: string = null) {
        if(Chat.Persist[tag] != undefined && Chat.Persist[tag]["enable"]) {
            Chat.Persist[tag]["value"] = arg == null ? !Chat.Persist[tag]["value"] : arg;
            Chat.BindPersist();
            document.getElementById("message").focus();
        } else {
            if (arg == null)
                UI.InsertChatText("[" + tag + "]", "[/" + tag + "]");
            else
                UI.InsertChatText("[" + tag + "=" + arg + "]", "[/" + tag + "]");
        }
    }
}