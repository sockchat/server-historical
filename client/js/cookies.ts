enum Cookie {Soundpack, Language, Style, Options}

class Cookies {
    public static cookieList = ["soundpack","lang","style","opts"];
    public static defaultVals = [];

    public static Set(cookie: Cookie, value: string) {
        Cookies.SetRaw(Cookies.cookieList[cookie], value);
    }

    public static SetRaw(cookie: string, value: string) {
        var expire = new Date(Date.now() + 31536000000);
        document.cookie = cookie +"="+ value +"; expires="+ expire.toUTCString();
    }

    public static Get(cookie: Cookie): string {
        return Cookies.GetRaw(Cookies.cookieList[cookie]);
    }

    public static GetRaw(cookie: string): string {
        var c = document.cookie.split(";");

        for(var i = 0; i < c.length; i++) {
            var entry = c[i].trim().split("=");
            if(entry[0] == cookie)
                return entry[1];
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