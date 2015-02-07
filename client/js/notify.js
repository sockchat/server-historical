var Notify = (function () {
    function Notify() {
    }
    Notify.Init = function () {
        if ("Notification" in window) {
            if (Notification.permission === "granted")
                this.enabled = true;
            else if (Notification.permission !== "denied") {
                Notification.requestPermission(function (perm) {
                    if (perm === "granted")
                        Notify.enabled = true;
                });
            }
        }
    };
    Notify.Show = function (title, body, icon) {
        if (Notify.enabled) {
            var test = new Notification(title, { "body": body, "icon": icon });
        }
    };
    Notify.enabled = false;
    return Notify;
})();
//# sourceMappingURL=notify.js.map