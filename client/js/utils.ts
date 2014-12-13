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

    static UnixNow(): number {
        return Math.round((new Date()).getTime()/1000);
    }

    static StripCharacters(str: string, chars: string) {
        if(chars != "") {
            for(var i = 0; i < chars.length; i++) str = Utils.replaceAll(str, chars[i], "");
        }
        return str;
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
}