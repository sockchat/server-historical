/// <reference path="user.ts" />
/// <reference path="utils.ts" />
/// <reference path="lang.ts" />
/// <reference path="cookies.ts" />
/// <reference path="sound.ts" />
/// <reference path="sock.ts" />
/// <reference path="notify.ts" />
/// <reference path="chat.ts" />
/// <reference path="channel.ts" />
var Title = (function () {
    function Title() {
    }
    Title.strobeCallback = function () {
        if (!Title.enableStrobing)
            Title.num = 0;
        if (Title.num > 0) {
            document.title = (Title.on ? "[@ ]" : "[ @]") + " " + Title.username + " - " + UI.chatTitle;
            Title.num--;
            Title.on = !Title.on;
        }
        else
            Title.Normalize();
    };
    Title.Strobe = function (name) {
        Title.num = Title.startNum;
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
    Title.startNum = 6;
    Title.enableStrobing = true;
    Title.started = false;
    Title.on = false;
    return Title;
})();
var UI = (function () {
    function UI() {
    }
    UI.GetChatListClass = function () {
        var sidebar = UI.chatFlags[1] == null ? "fullWidth" : (document.getElementById(UI.chatFlags[1]).className == "sidebar" ? "userListVisible" : "wideSideVisible");
        return "chatList " + (UI.chatFlags[0] ? "channelListVisible" : "channelListHidden") + " " + sidebar;
    };
    UI.UpdateChatLists = function () {
        var chats = document.getElementsByName("chatList");
        for (var i in chats) {
            try {
                chats[i].className = UI.GetChatListClass();
            }
            catch (e) {
            }
        }
    };
    UI.ChangeSidebar = function (id) {
        if (UI.chatFlags[1] != null)
            document.getElementById(UI.chatFlags[1]).style.display = "none";
        if (id == UI.chatFlags[1] || id == null)
            UI.chatFlags[1] = null;
        else {
            document.getElementById(id).style.display = "block";
            UI.chatFlags[1] = id;
        }
        UI.UpdateChatLists();
    };
    UI.ToggleChannelMenu = function (val) {
        if (val === void 0) { val = null; }
        if (val == null)
            UI.chatFlags[0] = !UI.chatFlags[0];
        else
            UI.chatFlags[0] = val;
        document.getElementById("channelList").style.display = UI.chatFlags[0] ? "block" : "none";
        UI.UpdateChatLists();
    };
    UI.SpawnChatList = function (channel) {
        var div = document.createElement("div");
        div.id = "chat." + channel;
        div.className = UI.GetChatListClass();
        div.setAttribute("name", "chatList");
        document.getElementById("chat").appendChild(div);
    };
    UI.DeleteChatList = function (channel) {
        var self = document.getElementById("chat." + channel);
        self.parentElement.removeChild(self);
    };
    UI.IsMobileView = function () {
        return window.innerWidth <= 800;
    };
    UI.InsertChatText = function (before, after) {
        if (before === void 0) { before = ""; }
        if (after === void 0) { after = ""; }
        var element = document.getElementById("message");
        if (document.selection) {
            element.focus();
            var sel = document.selection.createRange();
            sel.text = before + sel.text + after;
            element.focus();
        }
        else if (element.selectionStart || element.selectionStart === 0) {
            var startPos = element.selectionStart;
            var endPos = element.selectionEnd;
            var scrollTop = element.scrollTop;
            element.value = element.value.substring(0, startPos) + before + element.value.substring(startPos, endPos) + after + element.value.substring(endPos, element.value.length);
            element.focus();
            element.selectionStart = startPos + before.length;
            element.selectionEnd = endPos + before.length;
            element.scrollTop = scrollTop;
        }
        else {
            element.value += before + after;
            element.focus();
        }
    };
    UI.ChangeActiveChat = function (name) {
        if (name === void 0) { name = null; }
        var chats = document.getElementsByName("chatList");
        for (var i in chats) {
            try {
                chats[i].style.display = "none";
            }
            catch (e) {
            }
        }
        if (name != null)
            document.getElementById("chat." + name).style.display = "block";
    };
    UI.GetCursorPosition = function () {
        var element = document.getElementById("message");
        if (document.selection) {
            var c = "\001", sel = document.selection.createRange(), dul = sel.duplicate(), len = 0;
            dul.moveToElementText(element);
            sel.text = c;
            len = dul.text.indexOf(c);
            sel.moveStart('character', -1);
            sel.text = "";
            return len;
        }
        else if (element.selectionStart || element.selectionStart === 0) {
            return element.selectionStart;
        }
        else
            return 0;
    };
    UI.RenderButtons = function () {
        document.getElementById("bbCodeContainer").innerHTML = "";
        UI.bbcode.forEach(function (elem, i, arr) {
            if (elem["button"] != undefined) {
                var btn = document.createElement("input");
                btn.setAttribute("type", "button");
                if (elem["bstyle"] != undefined)
                    btn.setAttribute("style", elem["bstyle"]);
                btn.value = typeof elem["button"] == "boolean" ? UI.langs[UI.currentLang].bbCodeText[elem["tag"]] : elem["button"];
                btn.setAttribute("name", typeof elem["button"] == "boolean" ? elem["tag"] : ";;");
                if (!elem["arg"])
                    btn.onclick = function (e) {
                        Chat.InsertBBCode(elem["tag"]);
                    };
                else {
                    if (elem["bhandle"] != undefined)
                        btn.onclick = function (e) {
                            eval(elem["bhandle"]);
                        };
                    else {
                        btn.onclick = function (e) {
                            var val = prompt(elem["bprompt"] != undefined ? (UI.langs[UI.currentLang].menuText[elem["bprompt"]] != undefined ? UI.langs[UI.currentLang].menuText[elem["bprompt"]] : UI.langs[UI.currentLang].menuText["bbprompt"]) : UI.langs[UI.currentLang].menuText["bbprompt"], "");
                            if (val != null && val != undefined)
                                Chat.InsertBBCode(elem["tag"], val);
                        };
                    }
                }
                document.getElementById("bbCodeContainer").appendChild(btn);
            }
        });
    };
    UI.RenderIcons = function () {
        document.getElementById("optionsContainer").innerHTML = "";
        UI.icons.forEach(function (elem, i, arr) {
            var icon = document.createElement("img");
            icon.src = "img/pixel.png";
            icon.alt = elem[0];
            icon.style.background = "url(img/" + elem[0] + ") no-repeat scroll transparent";
            icon.onclick = function (e) {
                eval(elem[1]);
            };
            if (elem[2] != undefined)
                icon.onload = function (e) {
                    eval(elem[2]);
                };
            document.getElementById("optionsContainer").appendChild(icon);
        });
    };
    UI.RenderEmotes = function () {
        document.getElementById("emotes").innerHTML = "";
        UI.emotes.forEach(function (elem, i, arr) {
            var egami = document.createElement("img");
            egami.src = "img/emotes/" + elem[0];
            egami.alt = egami.title = elem[1][0];
            egami.onclick = function (e) {
                UI.InsertChatText(egami.alt);
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
        Cookies.Set(1 /* Style */, selected);
        var oldlink = document.getElementsByTagName("link").item(0);
        var newlink = document.createElement("link");
        newlink.setAttribute("rel", "stylesheet");
        newlink.setAttribute("type", "text/css");
        newlink.setAttribute("href", "./styles/" + selected + ".css");
        document.getElementsByTagName("head").item(0).replaceChild(newlink, oldlink);
    };
    UI.ChangeDisplay = function (chat, msgid, indicator, err, link) {
        if (msgid === void 0) { msgid = "chan"; }
        if (indicator === void 0) { indicator = true; }
        if (err === void 0) { err = ""; }
        if (link === void 0) { link = false; }
        if (chat) {
            document.getElementById("connmsg").style.display = "none";
            document.getElementById("chat").style.display = "block";
        }
        else {
            document.getElementById("chat").style.display = "none";
            document.getElementById("connmsg").style.display = "block";
            document.getElementById("conntxt").innerHTML = UI.langs[UI.currentLang].menuText[msgid] + err + (link ? "<br/><br/><a href='" + Socket.redirectUrl + "'>" + UI.langs[UI.currentLang].menuText["back"] + "</a>" : "");
            document.getElementById("indicator").style.display = indicator ? "block" : "none";
        }
    };
    UI.RenderLanguage = function () {
        var id = document.getElementById("langdd").selectedIndex;
        this.currentLang = id;
        Cookies.Set(0 /* Language */, UI.langs[id].code);
        document.getElementById("chanbtn").value = UI.langs[id].menuText["chan"];
        document.getElementById("tstyle").innerHTML = UI.langs[id].menuText["style"];
        document.getElementById("tlang").innerHTML = UI.langs[id].menuText["lang"];
        document.getElementsByClassName("top")[0].innerHTML = UI.langs[id].menuText["channels"];
        document.getElementsByClassName("top")[1].innerHTML = UI.langs[id].menuText["online"];
        document.getElementsByClassName("top")[2].innerHTML = UI.langs[id].menuText["sets"];
        UI.RedrawHelpList();
        document.getElementById("sendmsg").value = UI.langs[id].menuText["submit"];
        try {
            document.getElementById("namelabel").innerHTML = UI.langs[id].menuText["username"];
        }
        catch (e) {
        }
        try {
            document.getElementById("msglabel").innerHTML = UI.langs[id].menuText["message"];
        }
        catch (e) {
        }
        var rows = document.getElementById("settingsList").getElementsByTagName("table")[0].getElementsByTagName("tr");
        console.log(rows);
        for (var i = 0; i < rows.length; i++) {
            if (rows[i].getAttribute("name").substr(0, 2) == ";;") {
                var code = rows[i].getAttribute("name").substr(2);
                rows[i].cells[0].innerHTML = UI.langs[UI.currentLang].menuText["persist"].replace("{0}", UI.langs[UI.currentLang].bbCodeText[code] != undefined ? UI.langs[UI.currentLang].bbCodeText[code] : code);
            }
            else if (rows[i].getAttribute("name").substr(0, 2) == "||") {
                var code = rows[i].getAttribute("name").substr(2);
                rows[i].cells[0].innerHTML = UI.langs[UI.currentLang].menuText["enable"].replace("{0}", UI.langs[UI.currentLang].bbCodeText[code] != undefined ? UI.langs[UI.currentLang].bbCodeText[code] : code);
            }
            else {
                if (UI.langs[UI.currentLang].settingsText[rows[i].getAttribute("name")] != undefined)
                    rows[i].cells[0].innerHTML = UI.langs[UI.currentLang].settingsText[rows[i].getAttribute("name")] + ":";
            }
        }
        var btns = document.getElementById("bbCodeContainer").getElementsByTagName("input");
        for (var i = 0; i < btns.length; i++) {
            if (btns[i].getAttribute("name") != ";;")
                btns[i].value = UI.langs[UI.currentLang].bbCodeText[btns[i].getAttribute("name")] != undefined ? UI.langs[UI.currentLang].bbCodeText[btns[i].getAttribute("name")] : btns[i].getAttribute("name");
        }
        if (UserContext.self != undefined)
            UI.RedrawUserList();
    };
    UI.GenerateContextMenu = function (u) {
        var ret = document.createElement("ul");
        ret.id = "sock_menu_" + u.id;
        ret.className = "userMenu";
        if (u.id != UserContext.self.id)
            ret.style.display = "none";
        UI.contextMenuFields.forEach(function (elt, i, arr) {
            if ((u.id == UserContext.self.id && elt["self"]) || (elt["others"] && u.id != UserContext.self.id)) {
                if ((UserContext.self.canModerate() && elt["modonly"]) || !elt["modonly"]) {
                    if (UI.langs[UI.currentLang].menuText[elt["langid"]] != undefined) {
                        var li = document.createElement("li");
                        var link = document.createElement("a");
                        link.href = "javascript:void(0);";
                        link.innerHTML = UI.langs[UI.currentLang].menuText[elt["langid"]];
                        switch (elt["action"]) {
                            default:
                            case 0:
                                var msg = Utils.replaceAll(Utils.replaceAll(elt["value"], "{0}", "{ {0} }"), "{1}", "{ {1} }");
                                link.onclick = function (e) {
                                    UI.InsertChatText(Utils.replaceAll(Utils.replaceAll(msg, "{ {0} }", UserContext.self.username), "{ {1} }", u.username) + " ");
                                };
                                break;
                            case 1:
                                var msg = Utils.replaceAll(Utils.replaceAll(elt["value"], "{0}", "{ {0} }"), "{1}", "{ {1} }");
                                link.onclick = function (e) {
                                    Chat.SendMessageWrapper(Utils.replaceAll(Utils.replaceAll(msg, "{ {0} }", UserContext.self.username), "{ {1} }", u.username));
                                };
                                break;
                            case 2:
                                link.onclick = function (e) {
                                    var user = u;
                                    eval(elt["value"]);
                                };
                                break;
                        }
                        li.appendChild(link);
                        ret.appendChild(li);
                    }
                }
            }
        });
        return ret;
    };
    UI.AddMessage = function (msgid, date, u, msg, strobe, playsound, flags, fulldate) {
        if (strobe === void 0) { strobe = true; }
        if (playsound === void 0) { playsound = true; }
        if (flags === void 0) { flags = "10010"; }
        if (fulldate === void 0) { fulldate = false; }
        var msgDiv = document.createElement("div");
        msgDiv.id = "sock_msg_" + msgid;
        msgDiv.className = (this.rowEven[0]) ? "rowEven" : "rowOdd";
        /*var timecorrection = (new Date()).getTimezoneOffset()*60000;
        var dateval = new Date((date + ((((UI.dst)?0:1)+UI.timezone)*3600))*1000 + timecorrection);*/
        var dateval = new Date(date * 1000);
        var datestr = Utils.AddZero(dateval.getHours()) + ":" + Utils.AddZero(dateval.getMinutes()) + ":" + Utils.AddZero(dateval.getSeconds());
        if (fulldate)
            datestr = dateval.getFullYear() + "-" + Utils.AddZero(dateval.getMonth() + 1) + "-" + Utils.AddZero(dateval.getDate()) + " " + datestr;
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
        var mention = false;
        try {
            if (!Utils.ContainsSpecialChar(UserContext.self.username))
                mention = (new RegExp("\\b" + Utils.SanitizeRegex(UserContext.self.username) + "\\b", "i")).test(outmsg);
            else
                mention = (outmsg.toLowerCase()).indexOf(UserContext.self.username.toLowerCase()) != -1;
        }
        catch (e) {
        }
        if (flags.charAt(4) == "1") {
            if (u.id == UserContext.self.id) {
                var p = outmsg.split(" ");
                outmsg = "<i>" + UI.langs[UI.currentLang].menuText['whisperto'].replace('{0}', p[0]) + "</i> " + p.slice(1).join(" ");
            }
            else
                outmsg = "<i>" + UI.langs[UI.currentLang].menuText['whisper'] + "</i> " + outmsg;
        }
        if (strobe && !document.hasFocus() && (flags.charAt(4) == "1" || mention)) {
            var strip = outmsg.replace(new RegExp("\\[.*?\\]", "g"), "").replace(new RegExp("\\<.*?\\>", "g"), "");
            Notify.Show(u.username, strip, "img/alert.png");
        }
        if (UI.enableBBCode) {
            for (var i = 0; i < UI.bbcode.length; i++) {
                if (!UI.bbcode[i]["arg"]) {
                    var at = 0;
                    while ((at = outmsg.indexOf("[" + UI.bbcode[i]['tag'] + "]", at)) != -1) {
                        var end;
                        if ((end = outmsg.indexOf("[/" + UI.bbcode[i]['tag'] + "]", at)) != -1) {
                            var inner = Utils.StripCharacters(outmsg.substring(at + ("[" + UI.bbcode[i]['tag'] + "]").length, end), UI.bbcode[i]["rmin"] == undefined ? "" : UI.bbcode[i]["rmin"]);
                            var replace = Utils.replaceAll(UI.bbcode[i]['swap'], "{0}", inner);
                            if (UI.bbcode[i]['toggle'] && !Chat.bbEnable[UI.bbcode[i]['tag']])
                                replace = inner;
                            outmsg = outmsg.substring(0, at) + replace + outmsg.substring(end + ("[/" + UI.bbcode[i]['tag'] + "]").length);
                            at += replace.length;
                        }
                        else
                            break;
                    }
                }
                else {
                    var at = 0;
                    while ((at = outmsg.indexOf("[" + UI.bbcode[i]['tag'] + "=", at)) != -1) {
                        var start, end;
                        if ((start = outmsg.indexOf("]", at)) != -1) {
                            if ((end = outmsg.indexOf("[/" + UI.bbcode[i]['tag'] + "]", start)) != -1) {
                                var arg = Utils.StripCharacters(outmsg.substring(at + ("[" + UI.bbcode[i]['tag'] + "=").length, start), "[]" + (UI.bbcode[i]["rmarg"] == undefined ? "" : UI.bbcode[i]["rmarg"]));
                                var inner = Utils.StripCharacters(outmsg.substring(start + 1, end), UI.bbcode[i]["rmin"] == undefined ? "" : UI.bbcode[i]["rmin"]);
                                var replace = Utils.replaceAll(Utils.replaceAll(UI.bbcode[i]['swap'], "{1}", inner), "{0}", arg);
                                if (UI.bbcode[i]['toggle'] && !Chat.bbEnable[UI.bbcode[i]['tag']])
                                    replace = inner;
                                outmsg = outmsg.substring(0, at) + replace + outmsg.substring(end + ("[/" + UI.bbcode[i]['tag'] + "]").length);
                                at += replace.length;
                            }
                            else
                                break;
                        }
                        else
                            break;
                    }
                }
            }
        }
        if (UI.enableLinks) {
            var tmp = outmsg.split(' ');
            for (var i = 0; i < tmp.length; i++) {
                if (tmp[i].indexOf("<") != -1 && tmp[i].indexOf(">") == -1) {
                    tmp[i + 1] = tmp[i] + " " + tmp[i + 1];
                    tmp[i] = "";
                    continue;
                }
                var text = tmp[i].replace(/(<([^>]+)>)/ig, "");
                if (text.substr(0, 7) == "http://" || text.substr(0, 8) == "https://" || text.substr(0, 6) == "ftp://")
                    tmp[i] = "<a href='" + text + "' onclick='window.open(this.href);return false;'>" + text + "</a>";
            }
            outmsg = tmp.join(" ");
        }
        if (UI.enableEmotes) {
            UI.emotes.forEach(function (elem, i, arr) {
                var args = [];
                elem[1].forEach(function (elt, j, akbar) {
                    args.push(Utils.SanitizeRegex(Utils.Sanitize(elt)));
                });
                outmsg = outmsg.replace(new RegExp("(" + args.join("|") + ")(?![^\\<]*\\>)", "g"), "<img src='img/emotes/" + elem[0] + "' class='chatEmote' />");
            });
        }
        var namestyle = (flags.charAt(0) == "1" ? "font-weight: bold;" : "") + (flags.charAt(1) == "1" ? "font-style: italic;" : "") + (flags.charAt(2) == "1" ? "text-decoration: underline;" : "");
        var colon = flags.charAt(3) == "1" ? ":" : "";
        var name = (u.id == -1) ? "<span class='botName'>" + u.username + "</span>" : u.username;
        msgDiv.innerHTML = "<span class='date'>(" + datestr + ")</span> <span onclick='UI.InsertChatText(this.innerHTML.replace(/<[^>]*>/g, \"\"));' style='" + namestyle + "color:" + u.color + ";'>" + name + "</span><span class='msgColon'>" + colon + " </span><span class='msgBreak'><br /></span>" + outmsg + "";
        if (UserContext.self != undefined && !isNaN(+msgid)) {
            if (UserContext.self.canModerate() && u.id != -1 && u.getRank() <= UserContext.self.getRank()) {
                var del = document.createElement("img");
                del.src = "img/delete.png";
                del.alt = "delete";
                del.setAttribute("class", "fakeLink");
                del.style.setProperty("float", "right");
                del.style.setProperty("margin", "2px 0px 0px 0px");
                del.onclick = function (e) {
                    if (confirm(UI.langs[UI.currentLang].menuText["delmsg"]))
                        Chat.SendMessageWrapper("/delete " + msgid);
                };
                msgDiv.insertBefore(del, msgDiv.childNodes[0]);
            }
        }
        document.getElementById("chatList").appendChild(msgDiv);
        this.rowEven[0] = !this.rowEven[0];
        if (UI.autoscroll)
            document.getElementById("chatList").scrollTop = document.getElementById("chatList").scrollHeight;
        if (strobe && u.id != UserContext.self.id)
            Title.Strobe(u.username);
    };
    UI.ToggleUserMenu = function (id) {
        var menu = document.getElementById("sock_menu_" + id);
        menu.style.display = menu.style.display == "none" ? "block" : "none";
    };
    UI.AddUser = function (u, addToContext) {
        if (addToContext === void 0) { addToContext = true; }
        if (u.visible) {
            var msgDiv = document.createElement("div");
            msgDiv.className = (this.rowEven[1]) ? "rowEven" : "rowOdd";
            msgDiv.id = "sock_user_" + u.id;
            msgDiv.innerHTML = "<a style='color:" + u.color + "; display: block;' href='javascript:UI.ToggleUserMenu(" + u.id + ");'>" + u.username + "</a>";
            msgDiv.appendChild(UI.GenerateContextMenu(u));
            document.getElementById("userList").appendChild(msgDiv);
            this.rowEven[1] = !this.rowEven[1];
        }
        if (addToContext) {
            UserContext.users["" + u.id] = u;
        }
    };
    UI.ModifyUser = function (u) {
        var tmp = document.getElementById("sock_user_" + u.id).getElementsByTagName("a")[0];
        tmp.style.color = u.color;
        tmp.innerHTML = u.username;
    };
    UI.AddChannel = function (name, ispwd, istemp) {
        var opt = document.createElement("option");
        opt.text = (ispwd ? "*" : "") + (istemp ? "[" : "") + name + (istemp ? "]" : "");
        opt.value = name;
        //(<HTMLSelectElement>document.getElementById("channeldd")).add(opt);
    };
    UI.ModifyChannel = function (oldname, newname, ispwd, istemp) {
        /*var opt = Utils.GetOptionByValue(<HTMLSelectElement>document.getElementById("channeldd"), oldname);
        opt.value = newname;
        opt.text = (ispwd ? "*" : "") + (istemp ? "[" : "") + newname + (istemp ? "]" : "");*/
    };
    UI.RemoveChannel = function (name) {
        /*var cdd = <HTMLSelectElement>document.getElementById("channeldd");
        cdd.remove(Utils.GetOptionIndexByValue(cdd, name));*/
    };
    UI.RemoveUser = function (id) {
        delete UserContext.users["" + id];
        this.RedrawUserList();
    };
    UI.RedrawUserList = function () {
        document.getElementById("userList").innerHTML = '<div class="top">' + UI.langs[UI.currentLang].menuText["online"] + '</div>';
        this.rowEven[1] = false;
        this.AddUser(UserContext.self, false);
        for (var key in UserContext.users) {
            if (UserContext.users[key].visible)
                this.AddUser(UserContext.users[key], false);
        }
    };
    UI.RedrawHelpList = function () {
        document.getElementById("helpList").innerHTML = '<div class="top">' + UI.langs[UI.currentLang].menuText["help"] + '</div>';
        var table = document.createElement("table");
        var rowEven = false;
        for (var desc in UI.langs[UI.currentLang].helpText) {
            console.log(desc);
            var row = table.insertRow(-1);
            row.className = rowEven ? "rowEven" : "rowOdd";
            var cell = row.insertCell(0);
            cell.style.width = "50%";
            cell.innerHTML = desc + ":";
            cell = row.insertCell(1);
            cell.innerHTML = "<i>" + UI.langs[UI.currentLang].helpText[desc] + "</i>";
            rowEven = !rowEven;
        }
        document.getElementById("helpList").appendChild(table);
    };
    UI.GenerateChannelDiv = function (c, row, open) {
        if (open === void 0) { open = false; }
        var ret = document.createElement("div");
        ret.className = row ? 'rowEven' : 'rowOdd';
        if (open) {
            var l = document.createElement("a");
            l.href = 'javascript: ChannelContext.Leave("' + Utils.replaceAll(c.name, '"', '\\"') + '");';
            var img = document.createElement("img");
            img.src = "img/delete.png";
            img.style.setProperty("float", "right");
            img.style.padding = "2px 0 0 0";
            l.appendChild(img);
            ret.appendChild(l);
        }
        var link = document.createElement("a");
        var name = (c.istmp ? "[" : "") + Utils.replaceAll(c.name, '"', '\\"') + (c.istmp ? "]" : "") + (c.ispwd ? " *" : "");
        link.href = 'javascript: ChannelContext.Join("' + name + '");';
        link.innerHTML = (c.istmp ? "[" : "") + c.name + (c.istmp ? "]" : "") + (c.ispwd ? " *" : "");
        ret.appendChild(link);
        return ret;
    };
    UI.RedrawChannelList = function () {
        var list = document.getElementById("channelList");
        list.innerHTML = '<div class="top">' + UI.langs[UI.currentLang].menuText["channels"] + '</div>';
        var rowEven = false;
        for (var name in ChannelContext.openChannels) {
            list.appendChild(UI.GenerateChannelDiv(ChannelContext.channels[name], rowEven, true));
            rowEven = !rowEven;
        }
        list.innerHTML += "<div class='" + (rowEven ? 'rowEven' : 'rowOdd') + "'>&nbsp;</div>";
        rowEven = !rowEven;
        for (var name in ChannelContext.channels) {
            if (ChannelContext.openChannels[name] == undefined) {
                list.appendChild(UI.GenerateChannelDiv(ChannelContext.channels[name], rowEven));
                rowEven = !rowEven;
            }
        }
    };
    UI.chatTitle = "";
    UI.rowEven = [true, false];
    UI.currentView = 0;
    UI.maxMsgLen = 2000;
    UI.ChatBot = new User(-1, "ChatBot", "inherit", "");
    UI.autoscroll = true;
    UI.enableBBCode = true;
    UI.enableEmotes = true;
    UI.enableLinks = true;
    UI.contextMenuFields = Array();
    UI.bbcode = Array();
    UI.emotes = Array();
    UI.icons = Array();
    UI.spacks = Array();
    UI.currentPack = 0;
    UI.currentLang = 0;
    UI.styles = Array();
    UI.currentStyle = 0;
    UI.chatFlags = [false, "userList"];
    return UI;
})();
//# sourceMappingURL=ui.js.map