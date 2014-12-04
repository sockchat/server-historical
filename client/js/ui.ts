/// <reference path="user.ts" />
/// <reference path="utils.ts" />
/// <reference path="lang.ts" />
/// <reference path="cookies.ts" />
/// <reference path="sound.ts" />

class Title {
    static username = "";
    static num = 0;

    static started = false;
    static on = false;

    static strobeCallback() {
        if(Title.num > 0) {
            document.title = (Title.on?"[@ ]":"[ @]") +" "+ Title.username +" - "+ UI.chatTitle;
            Title.num--;
            Title.on = !Title.on;
        } else Title.Normalize();
    }

    static Strobe(name: string) {
        Title.num = 6;
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

class Options {
    static getValue(): number {
        return 2;
    }
}

class UI {
    static chatTitle = "";
    static displayDivs = ["connmsg","connclose","chat","connerr","attemptlogin"];
    static rowEven = [true, false];
    static currentView = 0;
    static ChatBot = new User(-1, "ChatBot", "inherit", "");

    static bbcode = Array();
    static emotes = Array();

    static spacks = Array();
    static currentPack = 0;

    static langs: Language[];
    static currentLang = 0;

    static styles = Array();
    static currentStyle = 0;

    static AppendChatText(sin: string) {
        (<HTMLInputElement>document.getElementById("message")).value += sin;
        (<HTMLInputElement>document.getElementById("message")).focus();
    }

    static RenderEmotes() {
        document.getElementById("emotes").innerHTML = "";
        UI.emotes.forEach(function(elem, i, arr) {
            var egami = document.createElement("img");
            egami.src = "img/emotes/"+ elem[0];
            egami.alt = egami.title = elem[1][0];
            egami.onclick = function(e) { UI.AppendChatText(egami.alt); };
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

    static ChangeDisplay(id: number) {
        for(var i = 0; i < this.displayDivs.length; i++)
            document.getElementById(this.displayDivs[i]).style.display = "none";
        document.getElementById(this.displayDivs[id]).style.display = "block";
        this.currentView = id;
    }

    static RenderLanguage() {
        var id = (<HTMLSelectElement>document.getElementById("langdd")).selectedIndex;
        this.currentLang = id;

        Cookies.Set(Cookie.Language, UI.langs[id].code);

        document.getElementById("tchan").innerHTML = UI.langs[id].menuText[0];
        document.getElementById("tstyle").innerHTML = UI.langs[id].menuText[1];
        document.getElementById("tlang").innerHTML = UI.langs[id].menuText[2];

        document.getElementById("top").innerHTML = UI.langs[id].menuText[3];
        (<HTMLInputElement>document.getElementById("sendmsg")).value = UI.langs[id].menuText[4];

        // TODO message reparsing
    }

    static AddMessage(msgid: string, date: number, u: User, msg: string, strobe = true, playsound = true) {
        var msgDiv = document.createElement("div");
        msgDiv.id = "sock_msg_"+ msgid;
        msgDiv.className = (this.rowEven[0])?"rowEven":"rowOdd";

        /*var timecorrection = (new Date()).getTimezoneOffset()*60000;
        var dateval = new Date((date + ((((UI.dst)?0:1)+UI.timezone)*3600))*1000 + timecorrection);*/
        var dateval = new Date(date*1000);
        var datestr = (((dateval.getHours() > 9)?"":"0") + dateval.getHours()) +":"+ (((dateval.getMinutes() > 9)?"":"0") + dateval.getMinutes()) +":"+ (((dateval.getSeconds() > 9)?"":"0") + dateval.getSeconds());
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

        UI.emotes.forEach(function(elem, i, arr) {
            elem[1].forEach(function(elt, j, akbar) {
                outmsg = Utils.replaceAll(outmsg, Utils.Sanitize(elt), "<img src='img/emotes/"+ elem[0] +"' class='chatEmote' />");
            });
        });

        for(var i = 0; i < UI.bbcode.length; i++)
            outmsg = outmsg.replace(UI.bbcode[i][0], UI.bbcode[i][1]);

        var tmp = outmsg.split(' ');
        for(var i = 0; i < tmp.length; i++) {
            if(tmp[i].substr(0, 7) == "http://" ||
               tmp[i].substr(0, 8) == "https://" ||
               tmp[i].substr(0, 6) == "ftp://")
                tmp[i] = "<a href='"+ tmp[i] +"' onclick='window.open(this.href);return false;'>"+ tmp[i] +"</a>";
        }
        outmsg = tmp.join(" ");

        var name = (u.id == -1)?"<span class='botName'>"+ u.username +"</span>": u.username;
        msgDiv.innerHTML = "<span class='date'>("+ datestr +")</span> <span style='font-weight:bold;color:"+ u.color +";'>"+ name +"</span>: "+ outmsg +"";
        document.getElementById("chatList").appendChild(msgDiv);
        this.rowEven[0] = !this.rowEven[0];
        document.getElementById("chatList").scrollTop = document.getElementById("chatList").scrollHeight;

        if(strobe) Title.Strobe(u.username);
    }

    static AddUser(u: User, addToContext = true) {
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEven[1])?"rowEven":"rowOdd";
        msgDiv.id = "sock_user_"+ u.id;
        msgDiv.innerHTML = "<span style='color:"+ u.color +";'>"+ u.username +"</span>";
        document.getElementById("userList").appendChild(msgDiv);
        this.rowEven[1] = !this.rowEven[1];

        if(addToContext) {
            UserContext.users[""+ u.id] = u;
        }
    }

    static ModifyUser(u: User) {
        document.getElementById("sock_user_"+ u.id).innerHTML = "<span style='color:"+ u.color +";'>"+ u.username +"</span>";
    }

    static AddChannel(name: string, istemp: boolean, ispwd: boolean) {

    }

    static ModifyChannel(oldname: string, newname: string, istemp: boolean, ispwd: boolean) {

    }

    static RemoveChannel(name: string) {

    }

    static RemoveUser(id: number) {
        delete UserContext.users[""+ id];
        this.RedrawUserList();
    }

    static RedrawUserList() {
        document.getElementById("userList").innerHTML = '<div id="top" class="rowEven">'+ UI.langs[UI.currentLang].menuText[3] +'</div>';
        this.rowEven[1] = false;
        this.AddUser(UserContext.self, false);
        for(var key in UserContext.users) {
            this.AddUser(<User>UserContext.users[key], false);
        }
    }
}