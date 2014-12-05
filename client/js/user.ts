class User {
    public username: string;
    public id: number;
    public color: string;
    public permstr: string;
    public perms: string[];
    public channel: string;

    public constructor(id: number, u: string, c: string, p: string) {
        this.username = u;
        this.id = id;
        this.color = c;
        this.permstr = p;
        this.perms = p.split("\f");
    }

    public EvaluatePermString() {
        this.perms = this.permstr.split("\f");
    }

    public getRank(): number {
        return +this.perms[0];
    }

    public canModerate() {
        return this.perms[1] == "1";
    }
}

class UserContext {
    static users = {};
    static self: User;
}