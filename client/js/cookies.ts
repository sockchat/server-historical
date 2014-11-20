class Cookies {
    public static soundpack = 0;
    public static lang = 1;
    public static style = 2;
    public static opts = 3;

    public static cookieList = ["soundpack","lang","style","opts"];

    public static Set(cookie: number, value: string) {
        var expire = new Date(Date.now() + 31536000000);
        document.cookie = Cookies.cookieList[cookie] +"="+ value +"; expires="+ expire.toUTCString();
    }

    public static Get(cookie: number): string {
        var c = document.cookie.split(";");

        for(var i = 0; i < c.length; i++) {
            var entry = c[i].trim().split("=");
            if(entry[0] == Cookies.cookieList[cookie])
                return entry[1];
        }

        return "";
    }
}