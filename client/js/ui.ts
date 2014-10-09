/// <reference path="user.ts" />

class UI {
    static displayDivs = ["connmsg","connclose","chat","connerr","attemptlogin"];
    static rowEven = [false, false];
    static currentView = 0;
    static ChatBot = new User(0, "ChatBot", "#C0C0C0");

    static timezone = 0.00;
    static dst = false;

    static ChangeDisplay(id: number) {
        for(var i = 0; i < this.displayDivs.length; i++)
            document.getElementById(this.displayDivs[i]).style.display = "none";
        document.getElementById(this.displayDivs[id]).style.display = "block";
        this.currentView = id;
    }

    static AddMessage(date: number, u: User, msg: string) {
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEven[0])?"rowEven":"rowOdd";

        // TODO fix date timezone correction algorithm
        var dateval = /*new Date((date + ((((UI.dst)?1:0)+UI.timezone)*3600))*1000);*/ new Date();
        var datestr = (((dateval.getHours() > 9)?"":"0") + dateval.getHours()) +":"+ (((dateval.getMinutes() > 9)?"":"0") + dateval.getMinutes()) +":"+ (((dateval.getSeconds() > 9)?"":"0") + dateval.getSeconds());
        msgDiv.innerHTML = "&nbsp;&nbsp;&nbsp;<span style='font-size: 0.8em;'>("+ datestr +")</span> <span style='font-weight:bold;color:"+ u.color +";'>"+ u.username +"</span>: "+ msg;
        document.getElementById("chatList").appendChild(msgDiv);
        this.rowEven[0] = !this.rowEven[0];
        document.getElementById("chatList").scrollTop = document.getElementById("chatList").scrollHeight;
    }

    static AddUser(u: User, addToContext = true) {
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEven[1])?"rowEven":"rowOdd";
        msgDiv.innerHTML = "&nbsp;<span style='font-weight:bold;color:"+ u.color +";'>"+ u.username +"</span>";
        document.getElementById("userList").appendChild(msgDiv);
        this.rowEven[1] = !this.rowEven[1];

        if(addToContext) {
            UserContext.users[""+ u.id] = u;
        }
    }

    static RemoveUser(id: number) {
        delete UserContext.users[""+ id];
        this.RedrawUserList();
    }

    static RedrawUserList() {
        document.getElementById("userList").innerHTML = "";
        this.AddUser(UserContext.self, false);
        for(var key in UserContext.users) {
            this.AddUser(<User>UserContext.users[key], false);
        }
    }
}