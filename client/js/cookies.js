var Cookie;
(function (Cookie) {
    Cookie[Cookie["Language"] = 0] = "Language";
    Cookie[Cookie["Style"] = 1] = "Style";
    Cookie[Cookie["Options"] = 2] = "Options";
    Cookie[Cookie["Persist"] = 3] = "Persist";
    Cookie[Cookie["BBEnable"] = 4] = "BBEnable";
})(Cookie || (Cookie = {}));
var Cookies = (function () {
    function Cookies() {
    }
    Cookies.Set = function (cookie, value) {
        Cookies.SetRaw(Cookies.cookieList[cookie], value);
    };
    Cookies.SetRaw = function (cookie, value) {
        var expire = new Date(Date.now() + 31536000000);
        document.cookie = Cookies.prefix + cookie + "=" + encodeURIComponent(value) + "; expires=" + expire.toUTCString();
    };
    Cookies.Get = function (cookie) {
        return Cookies.GetRaw(Cookies.cookieList[cookie]);
    };
    Cookies.GetRaw = function (cookie) {
        var c = document.cookie.split(";");
        for (var i = 0; i < c.length; i++) {
            var entry = c[i].trim().split("=");
            if (entry[0] == Cookies.prefix + cookie)
                return decodeURIComponent(entry[1]);
        }
        return undefined;
    };
    Cookies.prefix = "sockchat_";
    Cookies.cookieList = ["lang", "style", "opts", "persist", "bbenable"];
    Cookies.defaultVals = [];
    return Cookies;
})();
//# sourceMappingURL=cookies.js.map