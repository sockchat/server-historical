/// <reference path="user.ts" />
/// <reference path="ui.ts" />

class Channel {
    public name: string;
    public ispwd: boolean;
    public istmp: boolean;
    public users: number[];

    public constructor(name: string, ispwd: boolean = false, istmp: boolean = false) {
        this.Set(name, ispwd, istmp);
    }

    public Set(name: string, ispwd: boolean = false, istmp: boolean = false) {
        this.name = name;
        this.ispwd = ispwd;
        this.istmp = istmp;
    }

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
    public static channels: Channel[] = [];
    public static openChannels: string[] = [];
    public static activeChannel: string;

    public static Create(name: string, ispwd: boolean = false, istmp: boolean = false) {
        ChannelContext.channels[name] = new Channel(name, ispwd, istmp);
        UI.RedrawChannelList();
    }

    public static Modify(oldname: string, newname: string, ispwd: boolean, istmp: boolean) {
        if(oldname != newname) {
            ChannelContext.channels[newname] = ChannelContext.channels[oldname];
            delete ChannelContext.channels[oldname];
            if(ChannelContext.openChannels[oldname] != undefined) {
                ChannelContext.openChannels[newname] = newname;
                delete ChannelContext.openChannels[oldname];
            }
        }
        ChannelContext.channels[newname].ispwd = ispwd;
        ChannelContext.channels[newname].istmp = ispwd;
        UI.RedrawChannelList();
    }

    public static Delete(name: string) {
        if(ChannelContext.channels[name] != undefined)
            delete ChannelContext.channels[name];
        if(ChannelContext.openChannels[name] != undefined)
            ChannelContext.Leave(name);
        else
            UI.RedrawChannelList();
    }

    public static Join(name: string) {
        console.log(name);
        if(ChannelContext.channels[name] == undefined) return;
        ChannelContext.openChannels[name] = name;
        if(document.getElementById("chat."+ name) == undefined)
            UI.SpawnChatList(name);
        UI.ChangeActiveChat(name);
        ChannelContext.activeChannel = name;
        UI.RedrawChannelList();
    }

    public static Leave(name: string) {
        console.log(name);
        if(ChannelContext.openChannels[name] == undefined) return;
        delete ChannelContext.openChannels[name];
        UI.DeleteChatList(name);
        UI.RedrawChannelList();
    }
}