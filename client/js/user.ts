class User {
    public username: string;
    public id: number;
    public color: string;

    public constructor(id: number, u: string, c: string) {
        this.username = u;
        this.id = id;
        this.color = c;
    }
}

class UserContext {
    static users = {};
    static self: User;
}