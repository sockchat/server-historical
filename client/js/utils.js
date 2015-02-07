/// <reference path="ui.ts" />
var Utils = (function () {
    function Utils() {
    }
    Utils.replaceAll = function (haystack, needle, replace, ignore) {
        if (ignore === void 0) { ignore = false; }
        return haystack.replace(new RegExp(needle.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g, "\\$&"), (ignore ? "gi" : "g")), (typeof (replace) == "string") ? replace.replace(/\$/g, "$$$$") : replace);
    };
    Utils.Sanitize = function (str) {
        return Utils.replaceAll(Utils.replaceAll(Utils.replaceAll(str, ">", "&gt;"), "<", "&lt;"), "\n", " <br /> ");
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
    Utils.StripCharacters = function (str, chars) {
        if (chars != "") {
            for (var i = 0; i < chars.length; i++)
                str = Utils.replaceAll(str, chars[i], "");
        }
        return str;
    };
    Utils.AddZero = function (i, mag) {
        if (mag === void 0) { mag = 1; }
        var ret = "" + i;
        if (i < Math.pow(10, mag))
            ret = "0" + ret;
        return ret;
    };
    Utils.GetDateTimeString = function (dt) {
        alert(dt.getTime());
        return (dt.getTime() < 0) ? UI.langs[UI.currentLang].menuText[15] : dt.toDateString() + " @ " + Utils.AddZero(dt.getHours()) + ":" + Utils.AddZero(dt.getMinutes()) + ":" + Utils.AddZero(dt.getSeconds());
    };
    Utils.EmbedVideo = function (link) {
        var id = link.parentElement.title;
        var holder = link.parentElement.getElementsByTagName("span")[0];
        holder.innerHTML = holder.title == "link" ? "<iframe width='560' height='315' src='//www.youtube.com/embed/" + id + "' frameborder='0' allowfullscreen></iframe>" : "<a href='https://www.youtube.com/watch?v=" + id + "' onclick='window.open(this.href);return false;'>https://www.youtube.com/watch?v=" + id + "</a>";
        link.innerHTML = holder.title == "link" ? "Remove" : "Embed";
        holder.title = holder.title == "link" ? "video" : "link";
    };
    Utils.EmbedImage = function (link) {
        var id = link.parentElement.title;
        var holder = link.parentElement.getElementsByTagName("span")[0];
        var imglink = holder.getElementsByTagName("a")[0];
        imglink.innerHTML = holder.title == "link" ? "<img src='" + id + "' alt='userimg' class='insertImage' />" : id;
        link.innerHTML = holder.title == "link" ? "Remove" : "Embed";
        holder.title = holder.title == "link" ? "image" : "link";
    };
    Utils.Random = function (min, max) {
        return Math.round(Math.random() * (max - min)) + min;
    };
    return Utils;
})();
//# sourceMappingURL=utils.js.map