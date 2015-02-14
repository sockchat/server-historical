/// <reference path="user.ts" />
/// <reference path="utils.ts" />
/// <reference path="lang.ts" />
/// <reference path="cookies.ts" />
/// <reference path="sound.ts" />
/// <reference path="sock.ts" />
/// <reference path="notify.ts" />
/// <reference path="chat.ts" />

class Title {
    static username = "";
    static num = 0;

    static startNum = 6;
    static enableStrobing = true;

    static started = false;
    static on = false;

    static strobeCallback() {
        if(!Title.enableStrobing) Title.num = 0;
        if(Title.num > 0) {
            document.title = (Title.on?"[@ ]":"[ @]") +" "+ Title.username +" - "+ UI.chatTitle;
            Title.num--;
            Title.on = !Title.on;
        } else Title.Normalize();
    }

    static Strobe(name: string) {
        Title.num = Title.startNum;
        Title.username = name;

        if(!Title.started) {
            Title.started = true;
            setInterval("Title.strobeCallback();", 500);
        }
    }

    static Normalize() {
        document.title = UI.chatTitle;
    }
}

class UI {
    static chatTitle = "";
    static rowEven = [true, false];
    static currentView = 0;
    static maxMsgLen = 2000;
    static ChatBot = new User(-1, "ChatBot", "inherit", "");
    static autoscroll = true;

    static enableBBCode = true;
    static enableEmotes = true;
    static enableLinks = true;

    static bbcode = Array();
    static emotes = Array();
    static icons = Array();

    static spacks = Array();
    static currentPack = 0;

    static langs: Language[];
    static currentLang = 0;

    static styles = Array();
    static currentStyle = 0;

    static IsMobileView() {
        return window.innerWidth <= 800;
    }

    static InsertChatText(before: string = "", after: string = "") {
        var element = (<HTMLInputElement>document.getElementById("message"));

        if (document.selection) {
            element.focus();
            var sel = document.selection.createRange();
            sel.text = before + sel.text + after;
            element.focus();
        } else if (element.selectionStart || element.selectionStart === 0) {
            var startPos = element.selectionStart;
            var endPos = element.selectionEnd;
            var scrollTop = element.scrollTop;
            element.value = element.value.substring(0, startPos) + before + element.value.substring(startPos, endPos) + after + element.value.substring(endPos, element.value.length);
            element.focus();
            element.selectionStart = startPos + before.length;
            element.selectionEnd = endPos + before.length;
            element.scrollTop = scrollTop;
        } else {
            element.value += before + after;
            element.focus();
        }
    }

    static RenderButtons() {
        document.getElementById("bbCodeContainer").innerHTML = "";
        UI.bbcode.forEach(function(elem, i, arr) {
            if(elem["button"] != undefined) {
                var btn = document.createElement("input");
                btn.setAttribute("type", "button");
                if(elem["bstyle"] != undefined) btn.setAttribute("style", elem["bstyle"]);
                btn.value = typeof elem["button"] == "boolean" ? UI.langs[UI.currentLang].bbCodeText[elem["tag"]] : elem["button"];
                btn.setAttribute("name", typeof elem["button"] == "boolean" ? elem["tag"] : ";;" );
                if(!elem["arg"])
                    btn.onclick = function(e) { UI.InsertChatText("["+ elem['tag'] +"]","[/"+ elem['tag'] +"]") };
                else {
                    if(elem["bhandle"] != undefined)
                        btn.onclick = function(e) { eval(elem["bhandle"]); };
                    else {
                        btn.onclick = function (e) {
                            var val = prompt(elem["bprompt"] != undefined ? (UI.langs[UI.currentLang].menuText[elem["bprompt"]] != undefined ? UI.langs[UI.currentLang].menuText[elem["bprompt"]] : UI.langs[UI.currentLang].menuText["bbprompt"]) : UI.langs[UI.currentLang].menuText["bbprompt"], "");
                            if(val != null && val != undefined)
                                UI.InsertChatText("[" + elem['tag'] + "="+ val +"]", "[/" + elem['tag'] + "]")
                        };
                    }
                }
                document.getElementById("bbCodeContainer").appendChild(btn);
            }
        });
    }

    static RenderIcons() {
        document.getElementById("optionsContainer").innerHTML = "";
        UI.icons.forEach(function(elem, i, arr) {
            var icon = document.createElement("img");
            icon.src = "img/pixel.png";
            icon.alt = elem[0];
            icon.style.background = "url(img/"+ elem[0] +") no-repeat scroll transparent";
            icon.onclick = function(e) { eval(elem[1]); };
            if(elem[2] != undefined) icon.onload = function(e) { eval(elem[2]); };
            document.getElementById("optionsContainer").appendChild(icon);
        });
    }

    static RenderEmotes() {
        document.getElementById("emotes").innerHTML = "";
        UI.emotes.forEach(function(elem, i, arr) {
            var egami = document.createElement("img");
            egami.src = "img/emotes/"+ elem[0];
            egami.alt = egami.title = elem[1][0];
            egami.onclick = function(e) { UI.InsertChatText(egami.alt); };
            document.getElementById("emotes").appendChild(egami);
        });
        (<HTMLInputElement>document.getElementById("message")).value = "";
    }

    static RedrawDropDowns() {
        document.getElementById("langdd").innerHTML = "";
        UI.langs.forEach(function(elem, i, arr) {
            var e = document.createElement("option");
            e.value = elem.code;
            e.innerHTML = elem.name;
            document.getElementById("langdd").appendChild(e);
        });
    }

    static ChangeStyle() {
        var selected = (<HTMLSelectElement>document.getElementById("styledd")).value;
        Cookies.Set(Cookie.Style, selected);

        var oldlink = document.getElementsByTagName("link").item(0);

        var newlink = document.createElement("link");
        newlink.setAttribute("rel", "stylesheet");
        newlink.setAttribute("type", "text/css");
        newlink.setAttribute("href", "./styles/"+ selected +".css");

        document.getElementsByTagName("head").item(0).replaceChild(newlink, oldlink);
    }

    static ChangeDisplay(chat: boolean, msgid: string = "chan", indicator: boolean = true, err: string = "", link: boolean = false) {
        if(chat) {
            document.getElementById("connmsg").style.display = "none";
            document.getElementById("chat").style.display = "block";
        } else {
            document.getElementById("chat").style.display = "none";
            document.getElementById("connmsg").style.display = "block";
            document.getElementById("conntxt").innerHTML = UI.langs[UI.currentLang].menuText[msgid] + err + (link ? "<br/><br/><a href='"+ Socket.redirectUrl +"'>"+ UI.langs[UI.currentLang].menuText["back"] +"</a>" : "");
            document.getElementById("indicator").style.display = indicator ? "block" : "none";
        }
    }

    static RenderLanguage() {
        var id = (<HTMLSelectElement>document.getElementById("langdd")).selectedIndex;
        this.currentLang = id;

        Cookies.Set(Cookie.Language, UI.langs[id].code);

        document.getElementById("tchan").innerHTML = UI.langs[id].menuText["chan"];
        document.getElementById("tstyle").innerHTML = UI.langs[id].menuText["style"];
        document.getElementById("tlang").innerHTML = UI.langs[id].menuText["lang"];

        (<HTMLElement>document.getElementsByClassName("top")[0]).innerHTML = UI.langs[id].menuText["online"];
        (<HTMLElement>document.getElementsByClassName("top")[1]).innerHTML = UI.langs[id].menuText["sets"];
        (<HTMLElement>document.getElementsByClassName("top")[2]).innerHTML = UI.langs[id].menuText["help"];
        (<HTMLInputElement>document.getElementById("sendmsg")).value = UI.langs[id].menuText["submit"];

        try {
            document.getElementById("namelabel").innerHTML = UI.langs[id].menuText["username"];
        } catch(e) {}
        try {
            document.getElementById("msglabel").innerHTML = UI.langs[id].menuText["message"];
        } catch(e) {}

        var rows = document.getElementById("settingsList").getElementsByTagName("table")[0].getElementsByTagName("tr");
        console.log(rows);
        for(var i = 0; i < rows.length; i++) {
            if(rows[i].getAttribute("name").substr(0,2) == ";;") {
                // persistence
            } else if(rows[i].getAttribute("name").substr(0,2) == "||") {
                var code = rows[i].getAttribute("name").substr(2);
                (<HTMLTableCellElement>(<HTMLTableRowElement>rows[i]).cells[0]).innerHTML = UI.langs[UI.currentLang].menuText["enable"].replace("{0}", UI.langs[UI.currentLang].bbCodeText[code] != undefined ? UI.langs[UI.currentLang].bbCodeText[code] : code);
            } else {
                if(UI.langs[UI.currentLang].settingsText[rows[i].getAttribute("name")] != undefined)
                    (<HTMLTableCellElement>(<HTMLTableRowElement>rows[i]).cells[0]).innerHTML = UI.langs[UI.currentLang].settingsText[rows[i].getAttribute("name")] +":";
            }
        }

        var btns = document.getElementById("bbCodeContainer").getElementsByTagName("input");
        for(var i = 0; i < btns.length; i++) {
            if(btns[i].getAttribute("name") != ";;")
                btns[i].value = UI.langs[UI.currentLang].bbCodeText[btns[i].getAttribute("name")] != undefined ? UI.langs[UI.currentLang].bbCodeText[btns[i].getAttribute("name")] : btns[i].getAttribute("name");
        }
    }

    static AddMessage(msgid: string, date: number, u: User, msg: string, strobe = true, playsound = true, flags = "10010", fulldate = false) {
        var msgDiv = document.createElement("div");
        msgDiv.id = "sock_msg_"+ msgid;
        msgDiv.className = (this.rowEven[0])?"rowEven":"rowOdd";

        /*var timecorrection = (new Date()).getTimezoneOffset()*60000;
        var dateval = new Date((date + ((((UI.dst)?0:1)+UI.timezone)*3600))*1000 + timecorrection);*/
        var dateval = new Date(date*1000);
        var datestr = Utils.AddZero(dateval.getHours()) +":"+ Utils.AddZero(dateval.getMinutes()) +":"+ Utils.AddZero(dateval.getSeconds());
        if(fulldate) datestr = dateval.getFullYear() +"-"+ Utils.AddZero(dateval.getMonth()+1) +"-"+ Utils.AddZero(dateval.getDate()) +" "+ datestr;
        var outmsg = msg;

        if(u.id == -1) outmsg = UI.langs[UI.currentLang].interpretBotString(msg);

        if(playsound) {
            if (u.id == -1)
                Sounds.Play(UI.langs[UI.currentLang].isBotMessageError(msg) ? Sound.Error : Sound.ChatBot);
            else if (u.id == UserContext.self.id)
                Sounds.Play(Sound.Send);
            else
                Sounds.Play(Sound.Receive);
        }

        var mention = false;
        try {
            if (!Utils.ContainsSpecialChar(UserContext.self.username))
                mention = (new RegExp("\\b" + Utils.SanitizeRegex(UserContext.self.username) + "\\b", "i")).test(outmsg);
            else
                mention = (outmsg.toLowerCase()).indexOf(UserContext.self.username.toLowerCase()) != -1;
        } catch(e) {}

        if(strobe && mention && !document.hasFocus()) {
            var strip = outmsg.replace(new RegExp("\\[.*?\\]", "g"), "").replace(new RegExp("\\<.*?\\>", "g"), "");
            Notify.Show(u.username, strip, "img/alert.png");
        }

        if(UI.enableBBCode) {
            for (var i = 0; i < UI.bbcode.length; i++) {
                if (!UI.bbcode[i]["arg"]) {
                    var at = 0;
                    while ((at = outmsg.indexOf("[" + UI.bbcode[i]['tag'] + "]", at)) != -1) {
                        var end;
                        if ((end = outmsg.indexOf("[/" + UI.bbcode[i]['tag'] + "]", at)) != -1) {
                            var inner = Utils.StripCharacters(outmsg.substring(at + ("[" + UI.bbcode[i]['tag'] + "]").length, end), UI.bbcode[i]["rmin"] == undefined ? "" : UI.bbcode[i]["rmin"]);
                            var replace = Utils.replaceAll(UI.bbcode[i]['swap'], "{0}", inner);
                            if(UI.bbcode[i]['toggle'] && !Chat.bbEnable[UI.bbcode[i]['tag']]) replace = inner;
                            outmsg = outmsg.substring(0, at) + replace + outmsg.substring(end + ("[/" + UI.bbcode[i]['tag'] + "]").length);
                            at += replace.length;
                        } else break;
                    }
                } else {
                    var at = 0;
                    while ((at = outmsg.indexOf("[" + UI.bbcode[i]['tag'] + "=", at)) != -1) {
                        var start, end;
                        if ((start = outmsg.indexOf("]", at)) != -1) {
                            if ((end = outmsg.indexOf("[/" + UI.bbcode[i]['tag'] + "]", start)) != -1) {
                                var arg = Utils.StripCharacters(outmsg.substring(at + ("[" + UI.bbcode[i]['tag'] + "=").length, start), "[]" + (UI.bbcode[i]["rmarg"] == undefined ? "" : UI.bbcode[i]["rmarg"]));
                                var inner = Utils.StripCharacters(outmsg.substring(start + 1, end), UI.bbcode[i]["rmin"] == undefined ? "" : UI.bbcode[i]["rmin"]);
                                var replace = Utils.replaceAll(Utils.replaceAll(UI.bbcode[i]['swap'], "{1}", inner), "{0}", arg);
                                if(UI.bbcode[i]['toggle'] && !Chat.bbEnable[UI.bbcode[i]['tag']]) replace = inner;
                                outmsg = outmsg.substring(0, at) + replace + outmsg.substring(end + ("[/" + UI.bbcode[i]['tag'] + "]").length);
                                at += replace.length;
                            } else break;
                        } else break;
                    }
                }
            }
        }

        if(UI.enableLinks) {
            var tmp = outmsg.split(' ');
            for (var i = 0; i < tmp.length; i++) {
                if (tmp[i].substr(0, 7) == "http://" ||
                    tmp[i].substr(0, 8) == "https://" ||
                    tmp[i].substr(0, 6) == "ftp://")
                    tmp[i] = "<a href='" + tmp[i] + "' onclick='window.open(this.href);return false;'>" + tmp[i] + "</a>";
            }
            outmsg = tmp.join(" ");
        }

        if(UI.enableEmotes) {
            UI.emotes.forEach(function (elem, i, arr) {
                var args:string[] = [];
                elem[1].forEach(function (elt:string, j, akbar) {
                    args.push(Utils.SanitizeRegex(Utils.Sanitize(elt)));
                });

                outmsg = outmsg.replace(new RegExp("(" + args.join("|") + ")(?![^\\<]*\\>)", "g"), "<img src='img/emotes/" + elem[0] + "' class='chatEmote' />");
            });
        }

        var name = (u.id == -1)?"<span class='botName'>"+ u.username +"</span>": u.username;
        msgDiv.innerHTML = "<span class='date'>("+ datestr +")</span> <span onclick='UI.InsertChatText(this.innerHTML.replace(/<[^>]*>/g, \"\"));' style='font-weight:bold;color:"+ u.color +";'>"+ name +"</span><span class='msgColon'>: </span><span class='msgBreak'><br /></span>"+ outmsg +"";
        document.getElementById("chatList").appendChild(msgDiv);
        this.rowEven[0] = !this.rowEven[0];
        if(UI.autoscroll)
            document.getElementById("chatList").scrollTop = document.getElementById("chatList").scrollHeight;

        if(strobe && u.id != UserContext.self.id) Title.Strobe(u.username);
    }

    static AddUser(u: User, addToContext = true) {
        if(u.visible) {
            var msgDiv = document.createElement("div");
            msgDiv.className = (this.rowEven[1])?"rowEven":"rowOdd";
            msgDiv.id = "sock_user_"+ u.id;
            msgDiv.innerHTML = "<span style='color:"+ u.color +";'>"+ u.username +"</span>";
            document.getElementById("userList").appendChild(msgDiv);
            this.rowEven[1] = !this.rowEven[1];
        }

        if(addToContext) {
            UserContext.users[""+ u.id] = u;
        }
    }

    static ModifyUser(u: User) {
        document.getElementById("sock_user_"+ u.id).innerHTML = "<span style='color:"+ u.color +";'>"+ u.username +"</span>";
    }

    static AddChannel(name: string, ispwd: boolean, istemp: boolean) {
        var opt = document.createElement("option");
        opt.text = (ispwd ? "*" : "") + (istemp ? "[" : "") + name + (istemp ? "]" : "");
        opt.value = name;
        (<HTMLSelectElement>document.getElementById("channeldd")).add(opt);
    }

    static ModifyChannel(oldname: string, newname: string, ispwd: boolean, istemp: boolean) {
        console.log("here");
        var opt = Utils.GetOptionByValue(<HTMLSelectElement>document.getElementById("channeldd"), oldname);
        opt.value = newname;
        opt.text = (ispwd ? "*" : "") + (istemp ? "[" : "") + newname + (istemp ? "]" : "");
    }

    static RemoveChannel(name: string) {
        var cdd = <HTMLSelectElement>document.getElementById("channeldd");
        cdd.remove(Utils.GetOptionIndexByValue(cdd, name));
    }

    static RemoveUser(id: number) {
        delete UserContext.users[""+ id];
        this.RedrawUserList();
    }

    static RedrawUserList() {
        document.getElementById("userList").innerHTML = '<div class="top">'+ UI.langs[UI.currentLang].menuText["online"] +'</div>';
        this.rowEven[1] = false;
        this.AddUser(UserContext.self, false);
        for(var key in UserContext.users) {
            if(<User>UserContext.users[key].visible)
                this.AddUser(<User>UserContext.users[key], false);
        }
    }
}