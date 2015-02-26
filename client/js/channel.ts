/// <reference path="user.ts" />

class Channel {
    public name: string;
    public users: number[];

    public Join(u: number);
    public Join(u: User);
    public Join(u: any) {
        u = typeof u != "number" ? u.id : u;
        this.users[u] = u;
    }

    public Leave(u: number);
    public Leave(u: User);
    public Leave(u: any) {
        u = typeof u != "number" ? u.id : u;
        if(this.users[u] != undefined)
            delete this.users[u];
    }

    public GetUsers(): User[] {
        var ret = [];
        for(var user in this.users) {
            if(UserContext.users[user] != undefined)
                ret[user] = UserContext.users[user];
        }
        return ret;
    }
}

class ChannelContext {
    public channels: Channel[];
    public activeChannel: string;


}