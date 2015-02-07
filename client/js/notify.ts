interface Notification {
    new(title: string, options?: any);
    requestPermission(func: any);
    permission: string;
}
declare var Notification : Notification;

class Notify {
    public static enabled: boolean = false;

    public static Init() {
        if("Notification" in window) {
            if(Notification.permission === "granted") this.enabled = true;
            else if(Notification.permission !== "denied") {
                Notification.requestPermission(function(perm) {
                    if(perm === "granted") Notify.enabled = true;
                });
            }
        }
    }

    public static Show(title: string, body: string, icon: string) {
        if(Notify.enabled) {
            var test = new Notification(title, {"body": body, "icon": icon});
        }
    }
}