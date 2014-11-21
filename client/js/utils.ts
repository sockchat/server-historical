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
}