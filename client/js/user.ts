class User {
    public username: string;
    public id: number;
    public color: string;
    public perms: string;

    public constructor(id: number, u: string, c: string, p: string) {
        this.username = u;
        this.id = id;
        this.color = c;
        this.perms = p;
    }
}

class UserContext {
    static users = {};
    static self: User;
}