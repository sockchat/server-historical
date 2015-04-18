/// <reference path="ui.ts" />
/// <reference path="utf8.d.ts" />

class Utils {
    static replaceAll(haystack: string, needle: string, replace: string, ignore = false): string {
        return haystack.replace(new RegExp(needle.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g,"\\$&"),(ignore?"gi":"g")),(typeof(replace)=="string")?replace.replace(/\$/g,"$$$$"):replace);
    }

    static Sanitize(str: string): string {
        return  Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(str,
            ">", "&gt;"),
            "<", "&lt;"),
            "\n", " <br /> ");
    }

    static formatBotMessage(type: string, id: string, params: string[] = []): string {
        return type +"\f"+ id +"\f"+ params.join("\f");
    }

    static GetOptionByValue(select: HTMLSelectElement, value: string) {
        for(var option in select)
            if(select[option].value == value) return select[option];

        return null;
    }

    static GetOptionIndexByValue(select: HTMLSelectElement, value: string) {
        for(var option in select)
            if(select[option].value == value) return option;

        return -1;
    }

    static FetchPage(url: string) {
        var req = new XMLHttpRequest();
        req.open("GET", url, false);
        req.send(null);

        if(req.status === 200) return req.responseText;
        else return "";
    }

    static UnixTime(t: Date) {
        return Math.round(t.getTime()/1000);
    }

    static UnixNow(): number {
        return Math.round((new Date()).getTime()/1000);
    }

    static StripCharacters(str: string, chars: string) {
        if(chars != "") {
            for(var i = 0; i < chars.length; i++) str = Utils.replaceAll(str, chars[i], "");
        }
        return str;
    }

    static AddZero(i: number, mag: number = 1): string {
        var ret = ""+i;
        if(i < Math.pow(10, mag)) ret = "0" + ret;
        return ret;
    }

    static GetDateTimeString(dt: Date): string {
        return (dt.getTime() < 0) ? UI.langs[UI.currentLang].menuText["eot"] :
            dt.toDateString() +" @ "+ Utils.AddZero(dt.getHours()) +":"+ Utils.AddZero(dt.getMinutes()) +":"+ Utils.AddZero(dt.getSeconds());
    }

    static EmbedVideo(link: HTMLElement) {
        var id = link.parentElement.title;
        var holder = link.parentElement.getElementsByTagName("span")[0];

        holder.innerHTML = holder.title == "link" ? "<iframe width='560' height='315' src='//www.youtube.com/embed/"+ id +"' frameborder='0' allowfullscreen></iframe>" : "<a href='https://www.youtube.com/watch?v="+ id +"' onclick='window.open(this.href);return false;'>https://www.youtube.com/watch?v="+ id +"</a>";
        link.innerHTML = holder.title == "link" ? "Remove" : "Embed";
        holder.title = holder.title == "link" ? "video" : "link";
    }

    static EmbedImage(link: HTMLElement) {
        var id = link.parentElement.title;
        var holder = link.parentElement.getElementsByTagName("span")[0];
        var imglink = holder.getElementsByTagName("a")[0];

        imglink.innerHTML = holder.title == "link" ? "<img src='"+ id +"' alt='userimg' class='insertImage' />" : id;
        link.innerHTML = holder.title == "link" ? "Remove" : "Embed";
        holder.title = holder.title == "link" ? "image" : "link";
    }

    static Random(min: number, max: number): number {
        return Math.round(Math.random() * (max - min)) + min;
    }

    static ContainsSpecialChar(input: string): boolean {
        for(var i = 0; i < input.length; i++) {
            if(input.charCodeAt(i) > 127) return true;
        }
        return false;
    }

    static SanitizeRegex(input: string): string {
        var out = "";
        for(var i = 0; i < input.length; i++) {
            var cc = input.charCodeAt(i);
            if(!((cc>47 && cc<58) || (cc>64 && cc<91) || (cc>96 && cc<123)))
                out += "\\";
            out += input.charAt(i);
        }
        return out;
    }

    static PackBytes(num: number, bytes: number = 4): Uint8Array {
        var ret = new Uint8Array(bytes);
        for(var i = 0; i < bytes; i++)
            ret[i] = (num & (0xFF << 8 * (bytes - 1 - i))) >>> 8 * (bytes - 1 - i);
        return ret;
    }

    static UnpackBytes(bytes: Uint8Array): number {
        var ret = 0;
        for(var i = 0; i < bytes.length; i++)
            ret = ret | ((bytes[i] & 0xFF) << 8*(bytes.length - 1 - i));
        return ret;
    }

    static ByteLength(str: string): number {
        return utf8.encode(str).length;
    }

    static StringToByteArray(str: string): Uint8Array {
        str = utf8.encode(str);
        var ret = new Uint8Array(str.length);
        for(var i = 0; i < str.length; i++)
            ret[i] = str.charCodeAt(i);

        return ret;
    }

    static ByteArrayToString(bytes: Uint8Array): string {
        var chunkSize = 10000;
        var raw = "";
        for(var i = 0;; i++) {
            if(bytes.length < chunkSize*i) break;
            raw += String.fromCharCode.apply(null, bytes.subarray(chunkSize*i, chunkSize*i + chunkSize));
        }
        return utf8.decode(raw);
    }
}