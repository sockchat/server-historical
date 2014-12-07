var Utils = (function () {
    function Utils() {
    }
    Utils.replaceAll = function (haystack, needle, replace, ignore) {
        if (ignore === void 0) { ignore = false; }
        return haystack.replace(new RegExp(needle.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g, "\\$&"), (ignore ? "gi" : "g")), (typeof (replace) == "string") ? replace.replace(/\$/g, "$$$$") : replace);
    };
    Utils.Sanitize = function (str) {
        return Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(str, "&", "&amp;"), ">", "&gt;"), "<", "&lt;"), "'", "&apos;"), "\"", "&quot;"), "\\", "&#92;"), "\n", "<br />");
    };
    Utils.formatBotMessage = function (type, id, params) {
        if (params === void 0) { params = []; }
        return type + "\f" + id + "\f" + params.join("\f");
    };
    Utils.GetOptionByValue = function (select, value) {
        for (var option in select)
            if (select[option].value == value)
                return select[option];
        return null;
    };
    Utils.GetOptionIndexByValue = function (select, value) {
        for (var option in select)
            if (select[option].value == value)
                return option;
        return -1;
    };
    Utils.FetchPage = function (url) {
        var req = new XMLHttpRequest();
        req.open("GET", url, false);
        req.send(null);
        if (req.status === 200)
            return req.responseText;
        else
            return "";
    };
    Utils.UnixNow = function () {
        return Math.round((new Date()).getTime() / 1000);
    };
    return Utils;
})();
//# sourceMappingURL=utils.js.map