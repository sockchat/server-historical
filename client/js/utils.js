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
    return Utils;
})();
//# sourceMappingURL=utils.js.map