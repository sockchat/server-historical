var Cookies = (function () {
    function Cookies() {
    }
    Cookies.Set = function (cookie, value) {
        var expire = new Date(Date.now() + 31536000000);
        document.cookie = Cookies.cookieList[cookie] + "=" + value + "; expires=" + expire.toUTCString();
    };
    Cookies.Get = function (cookie) {
        var c = document.cookie.split(";");
        for (var i = 0; i < c.length; i++) {
            var entry = c[i].trim().split("=");
            if (entry[0] == Cookies.cookieList[cookie])
                return entry[1];
        }
        return undefined;
    };
    Cookies.Prepare = function () {
        for (var i = 0; i < Cookies.cookieList.length; i++) {
            if (Cookies.Get(i) == undefined)
                Cookies.Set(i, Cookies.defaultVals[i]);
        }
    };
    Cookies.soundpack = 0;
    Cookies.lang = 1;
    Cookies.style = 2;
    Cookies.opts = 3;
    Cookies.cookieList = ["soundpack", "lang", "style", "opts"];
    Cookies.defaultVals = [];
    return Cookies;
})();
//# sourceMappingURL=cookies.js.map