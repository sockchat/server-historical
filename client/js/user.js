var User = (function () {
    function User(id, u, c, p) {
        this.username = u;
        this.id = id;
        this.color = c;
        this.perms = p;
    }
    return User;
})();

var UserContext = (function () {
    function UserContext() {
    }
    UserContext.users = {};
    return UserContext;
})();
//# sourceMappingURL=user.js.map
