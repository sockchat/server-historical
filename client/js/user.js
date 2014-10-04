var User = (function () {
    function User(id, u) {
        this.username = u;
        this.id = id;
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
