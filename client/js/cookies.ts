enum Cookie {Language, Style, Options, Persist, BBEnable}

class Cookies {
    public static prefix = "sockchat_";
    public static cookieList = ["lang","style","opts","persist","bbenable"];
    public static defaultVals = [];

    public static Set(cookie: Cookie, value: string) {
        Cookies.SetRaw(Cookies.cookieList[cookie], value);
    }

    public static SetRaw(cookie: string, value: string) {
        var expire = new Date(Date.now() + 31536000000);
        document.cookie = Cookies.prefix + cookie +"="+ encodeURIComponent(value) +"; expires="+ expire.toUTCString();
    }

    public static Get(cookie: Cookie): string {
        return Cookies.GetRaw(Cookies.cookieList[cookie]);
    }

    public static GetRaw(cookie: string): string {
        var c = document.cookie.split(";");

        for(var i = 0; i < c.length; i++) {
            var entry = c[i].trim().split("=");
            if(entry[0] == Cookies.prefix + cookie)
                return decodeURIComponent(entry[1]);
        }

        return undefined;
    }

    /*public static Prepare() {
        for(var i = 0; i < Cookies.cookieList.length; i++) {
            if(Cookies.Get(i) == undefined)
                Cookies.Set(i, Cookies.defaultVals[i]);
        }
    }*/
}