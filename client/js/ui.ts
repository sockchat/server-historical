/// <reference path="user.ts" />

class UI {
    static displayDivs = ["connmsg","login","chat","connerr"];
    static rowEven = false;
    static rowEvenUsers = false;
    static currentView = 0;

    static ChangeDisplay(id: number) {
        for(var i = 0; i < this.displayDivs.length; i++)
            document.getElementById(this.displayDivs[i]).style.display = "none";
        document.getElementById(this.displayDivs[id]).style.display = "block";
        this.currentView = id;
    }

    static AddMessage(date: string, name: string, msg: string) {
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEven)?"rowEven":"rowOdd";
        // TODO add date in somewhere
        msgDiv.innerHTML = "&nbsp;&nbsp;&nbsp;<b>"+ name +":</b> "+ msg;
        document.getElementById("chatList").appendChild(msgDiv);
        this.rowEven = !this.rowEven;
        document.getElementById("chatList").scrollTop = document.getElementById("chatList").scrollHeight;
    }

    static AddUser(id: number, name: string, addToContext = true) {
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEvenUsers)?"rowEven":"rowOdd";
        msgDiv.innerHTML = "&nbsp;<b>"+ name +"</b>";
        document.getElementById("userList").appendChild(msgDiv);
        this.rowEvenUsers = !this.rowEvenUsers;

        if(addToContext) {
            UserContext.users[""+id] = new User(id, name);
        }
    }

    static RemoveUser(id: number) {
        delete UserContext.users[""+ id];
        this.RedrawUserList();
    }

    static RedrawUserList() {
        document.getElementById("userList").innerHTML = "";
        this.AddUser(UserContext.self.id, UserContext.self.username, false);
        for(var key in UserContext.users) {
            this.AddUser(UserContext.users[key].id, UserContext.users[key].username, false);
        }
    }
}