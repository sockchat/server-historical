var User = (function () {
    function User(id, u, c) {
        this.username = u;
        this.id = id;
        this.color = c;
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
