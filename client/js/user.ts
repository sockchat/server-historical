class User {
    public username: string;
    public id: number;

    public constructor(id: number, u: string) {
        this.username = u;
        this.id = id;
    }
}

class UserContext {
    static users = {};
    static self: User;
}