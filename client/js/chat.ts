/// <reference path="ui.ts" />
/// <reference path="msg.ts" />
/// <reference path="user.ts" />
/// <reference path="sock.ts" />
/// <reference path="cookies.ts" />
/// <reference path="sound.ts" />
/// <reference path="lang.ts" />
/// <reference path="utils.ts" />
/// <reference path="notify.ts" />

class Chat {
    static Settings: any = {
        "sound": true,
        "volume": 0.5
    };

    static Main(addr: string) {
        if(Socket.args[0] == "yes") {
            Chat.LoadJSONFiles();

            (<HTMLSelectElement>document.getElementById("styledd")).value = Cookies.Get(Cookie.Style);
            UI.ChangeStyle();

            UI.RedrawDropDowns();
            (<HTMLSelectElement>document.getElementById("langdd")).value = Cookies.Get(Cookie.Language);

            Sounds.ChangePack(Cookies.Get(Cookie.Soundpack));

            Chat.HideSidebars();
            if(!UI.IsMobileView()) document.getElementById("userList").style.display = "block";

            var tmp = JSON.parse(Utils.FetchPage("conf/settings.json?a="+ Utils.Random(1000000000,9999999999)));
            var table = <HTMLTableElement>document.getElementById("settingsList").getElementsByTagName("table")[0];
            tmp.settings.forEach(function(elt, i, arr) {
                Chat.Settings[elt["id"]] = elt["default"];
                var row = <HTMLTableRowElement>table.insertRow(i);
                row.className = i % 2 == 0 ? "rowOdd" : "rowEven";
                row.setAttribute("name", elt["id"]);
                var cell = row.insertCell(0);
                cell.innerHTML = elt["id"];
                cell = row.insertCell(1);
                cell.className = "setting";
                switch(elt["type"]) {
                    case "select":
                        var select = document.createElement("select");
                        select.onchange = function(e) { var value = this.value; Chat.Settings[elt["id"]] = value; eval(elt["change"]); };
                        if(elt["options"] != undefined) {
                            for(var val in elt["options"]) {
                                var option = document.createElement("option");
                                option.value = val;
                                option.innerHTML = elt["options"][val];
                                select.appendChild(option);
                            }
                        }
                        cell.appendChild(select);
                        break;
                    case "checkbox":
                        var input = document.createElement("input");
                        input.setAttribute("type", "checkbox");
                        input.onchange = function(e) { var value = this.checked; Chat.Settings[elt["id"]] = value; eval(elt["change"]); };
                        cell.appendChild(input);
                        break;
                    default:
                        var input = document.createElement("input");
                        input.setAttribute("type", elt["type"]);
                        input.onchange = function(e) { var value = this.value; Chat.Settings[elt["id"]] = value; eval(elt["change"]); };
                        cell.appendChild(input);
                }
            });
            try {
                var opts = JSON.parse(Cookies.Get(Cookie.Options));
            } catch(e) {
                opts = {};
            }
            for(var opt in Chat.Settings) {
                if(opts[opt] != undefined) Chat.Settings[opt] = opts[opt];
            }
            Chat.BindSettings();


            Sounds.ChangeVolume(Chat.Settings["volume"]);
            UI.RenderLanguage();
            UI.RenderEmotes();
            UI.RenderIcons();
            UI.RenderButtons();
            Notify.Init();

            UI.ChangeDisplay(false, "conn");

            UserContext.users = {};
            Socket.args = Socket.args.slice(1);
            Socket.Init(addr);
        } else window.location.href = Socket.redirectUrl;
    }

    static BindSettings() {
        Cookies.Set(Cookie.Options, JSON.stringify(Chat.Settings));
    }

    static HandleMessage(e) : boolean {
        var key = ('which' in e) ? e.which : e.keyCode;

        if(key == 13 && !e.shiftKey) {
            Chat.SendMessage();
            e.preventDefault();
            return false;
        } else return true;
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

        Chat.SendMessageWrapper(msg);

        (<HTMLInputElement>document.getElementById("message")).value = "";
        (<HTMLInputElement>document.getElementById("message")).focus();
    }

    static SendMessageWrapper(msg: string) {
        if(msg.trim() != "") Socket.Send(Message.Pack(2, ""+ UserContext.self.id, msg));
    }

    static ChangeChannel() {
        var dd = <HTMLSelectElement>document.getElementById("channeldd");
        Chat.SendMessageWrapper("/join "+ dd.value + (dd.options[dd.selectedIndex].text[0] == "*" && !UserContext.self.canModerate() ? " "+ prompt("Enter password for "+ dd.value, "") : ""));
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
        if(!Chat.Settings["sound"]) Sounds.ChangeVolume(0);
        icon.style.backgroundPosition = Chat.Settings["sound"] ? "0px 0px" : "0px -22px";
    }

    static ToggleSound(icon: HTMLElement) {
        icon.style.backgroundPosition = Chat.Settings["sound"] ? "0px -22px" : "0px 0px";
        Sounds.ChangeVolume(Chat.Settings["sound"] ? 0 : Chat.Settings["volume"]);
        Chat.Settings["sound"] = !Chat.Settings["sound"];
        Chat.BindSettings();
    }
}