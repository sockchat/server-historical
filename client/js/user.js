var User = (function () {
    function User(id, u, c, p, v) {
        if (v === void 0) { v = true; }
        this.username = u;
        this.id = id;
        this.color = c;
        this.permstr = p;
        this.perms = p.split("\f");
        this.visible = v;
    }
    User.prototype.EvaluatePermString = function () {
        this.perms = this.permstr.split("\f");
    };
    User.prototype.getRank = function () {
        return +this.perms[0];
    };
    User.prototype.canModerate = function () {
        return this.perms[1] == "1";
    };
    return User;
})();
var UserContext = (function () {
    function UserContext() {
    }
    UserContext.users = {};
    return UserContext;
})();
//# sourceMappingURL=user.js.map