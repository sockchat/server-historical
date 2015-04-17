var Notify = (function () {
    function Notify() {
    }
    Notify.Init = function (force) {
        if (force === void 0) { force = false; }
        if ("Notification" in window) {
            if (Notification.permission === "granted")
                this.enabled = true;
            else if (Notification.permission !== "denied" || force) {
                Notification.requestPermission(function (perm) {
                    if (perm === "granted")
                        Notify.enabled = true;
                });
            }
        }
    };
    Notify.Show = function (title, body, icon) {
        if (Notify.enabled) {
            try {
                var test = new Notification(title, { "body": body, "icon": icon });
            }
            catch (e) {
            }
        }
    };
    Notify.enabled = false;
    return Notify;
})();
//# sourceMappingURL=notify.js.map