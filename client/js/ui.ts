/// <reference path="user.ts" />

class UI {
    static displayDivs = ["connmsg","login","chat","connerr","attemptlogin"];
    static rowEven = false;
    static rowEvenUsers = false;
    static currentView = 0;
    static useDefaultAuth = true;

    static timezone = 0.00;
    static dst = false;

    static ChangeDisplay(id: number) {
        for(var i = 0; i < this.displayDivs.length; i++)
            document.getElementById(this.displayDivs[i]).style.display = "none";
        document.getElementById(this.displayDivs[id]).style.display = "block";
        this.currentView = id;
    }

    static AddMessage(date: number, name: string, color: string, msg: string) {
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEven)?"rowEven":"rowOdd";
        // TODO add date in somewhere

        var dateval = /*new Date((date + ((((UI.dst)?1:0)+UI.timezone)*3600))*1000);*/ new Date();
        var datestr = (((dateval.getHours() > 9)?"":"0") + dateval.getHours()) +":"+ (((dateval.getMinutes() > 9)?"":"0") + dateval.getMinutes()) +":"+ (((dateval.getSeconds() > 9)?"":"0") + dateval.getSeconds());
        msgDiv.innerHTML = "&nbsp;&nbsp;&nbsp;<span style='font-size: 0.8em;'>("+ datestr +")</span> <span style='font-weight:bold;color:"+color+";'>"+ name +"</span>: "+ msg;
        document.getElementById("chatList").appendChild(msgDiv);
        this.rowEven = !this.rowEven;
        document.getElementById("chatList").scrollTop = document.getElementById("chatList").scrollHeight;
    }

    static AddUser(id: number, name: string, color: string, addToContext = true) {
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEvenUsers)?"rowEven":"rowOdd";
        msgDiv.innerHTML = "&nbsp;<span style='font-weight:bold;color:"+color+";'>"+ name +"</span>";
        document.getElementById("userList").appendChild(msgDiv);
        this.rowEvenUsers = !this.rowEvenUsers;

        if(addToContext) {
            UserContext.users[""+id] = new User(id, name, color);
        }
    }

    static RemoveUser(id: number) {
        delete UserContext.users[""+ id];
        this.RedrawUserList();
    }

    static RedrawUserList() {
        document.getElementById("userList").innerHTML = "";
        this.AddUser(UserContext.self.id, UserContext.self.username, UserContext.self.color, false);
        for(var key in UserContext.users) {
            this.AddUser(UserContext.users[key].id, UserContext.users[key].username, UserContext.users[key].color, false);
        }
    }
}