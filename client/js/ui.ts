class UI {
    static displayDivs = ["connmsg","login","chat","conerr"];
    static rowEven = false;

    static ChangeDisplay(id: number) {
        for(var i = 0; i < this.displayDivs.length; i++)
            document.getElementById(this.displayDivs[i]).style.display = "none";
        document.getElementById(this.displayDivs[id]).style.display = "block";
    }

    static AddMessage(date: string, name: string, msg: string) {
        var msgDiv = document.createElement("div");
        msgDiv.className = (this.rowEven)?"rowEven":"rowOdd";
        // TODO add date in somewhere
        msgDiv.innerHTML = "&nbsp;&nbsp;&nbsp;<b>"+ name +":</b> "+ msg;
        document.getElementById("chatList").appendChild(msgDiv);
        this.rowEven = !this.rowEven;
    }
}