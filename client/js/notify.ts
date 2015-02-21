interface Notification {
    new(title: string, options?: any);
    requestPermission(func: any);
    permission: string;
}
declare var Notification : Notification;

class Notify {
    public static enabled: boolean = false;

    public static Init(force: boolean = false) {
        if("Notification" in window) {
            if(Notification.permission === "granted") this.enabled = true;
            else if(Notification.permission !== "denied" || force) {
                Notification.requestPermission(function(perm) {
                    if(perm === "granted") Notify.enabled = true;
                });
            }
        }
    }

    public static Show(title: string, body: string, icon: string) {
        if(Notify.enabled) {
            try {
                var test = new Notification(title, {"body": body, "icon": icon});
            } catch(e) {}
        }
    }
}