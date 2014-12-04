var Utils = (function () {
    function Utils() {
    }
    Utils.replaceAll = function (haystack, needle, replace, ignore) {
        if (typeof ignore === "undefined") { ignore = false; }
        return haystack.replace(new RegExp(needle.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g, "\\$&"), (ignore ? "gi" : "g")), (typeof (replace) == "string") ? replace.replace(/\$/g, "$$$$") : replace);
    };

    Utils.Sanitize = function (str) {
        return Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(str, "&", "&amp;"), ">", "&gt;"), "<", "&lt;"), "'", "&apos;"), "\"", "&quot;"), "\\", "&#92;"), "\n", "<br />");
    };

    Utils.formatBotMessage = function (type, id, params) {
        if (typeof params === "undefined") { params = []; }
        return type + "\f" + id + "\f" + params.join("\f");
    };

    Utils.GetOptionByValue = function (select, value) {
        for (var option in select)
            if (select[option].value == value)
                return select[option];

        return null;
    };
    return Utils;
})();
//# sourceMappingURL=utils.js.map
