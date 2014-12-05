class Utils {
    static replaceAll(haystack: string, needle: string, replace: string, ignore = false): string {
        return haystack.replace(new RegExp(needle.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g,"\\$&"),(ignore?"gi":"g")),(typeof(replace)=="string")?replace.replace(/\$/g,"$$$$"):replace);
    }

    static Sanitize(str: string): string {
        return  Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(str,
            "&", "&amp;"),
            ">", "&gt;"),
            "<", "&lt;"),
            "'", "&apos;"),
            "\"", "&quot;"),
            "\\", "&#92;"),
            "\n", "<br />");
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

    static UnixNow(): number {
        return Math.round((new Date()).getTime()/1000);
    }
}