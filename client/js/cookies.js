var Cookie;
(function (Cookie) {
    Cookie[Cookie["Soundpack"] = 0] = "Soundpack";
    Cookie[Cookie["Language"] = 1] = "Language";
    Cookie[Cookie["Style"] = 2] = "Style";
    Cookie[Cookie["Options"] = 3] = "Options";
})(Cookie || (Cookie = {}));
var Cookies = (function () {
    function Cookies() {
    }
    Cookies.Set = function (cookie, value) {
        Cookies.SetRaw(Cookies.cookieList[cookie], value);
    };
    Cookies.SetRaw = function (cookie, value) {
        var expire = new Date(Date.now() + 31536000000);
        document.cookie = cookie + "=" + value + "; expires=" + expire.toUTCString();
    };
    Cookies.Get = function (cookie) {
        return Cookies.GetRaw(Cookies.cookieList[cookie]);
    };
    Cookies.GetRaw = function (cookie) {
        var c = document.cookie.split(";");
        for (var i = 0; i < c.length; i++) {
            var entry = c[i].trim().split("=");
            if (entry[0] == cookie)
                return entry[1];
        }
        return undefined;
    };
    Cookies.cookieList = ["soundpack", "lang", "style", "opts"];
    Cookies.defaultVals = [];
    return Cookies;
})();
//# sourceMappingURL=cookies.js.map