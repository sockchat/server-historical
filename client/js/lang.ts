class Language {
    public name: string;
    public code: string;
    public dir: string;

    public menuText = [];
    public botText = [];
    public botErrText = [];

    public constructor(code: string, json: any[]) {
        this.code = code;

        for(var file in json) {
            console.log(json[file]);

            if(json[file].name != undefined) {
                this.name = json[file].name;
                this.dir = json[file].dir;

                for(var key in json[file].menuText) {
                    this.menuText.push(json[file].menuText[key]);
                }
            }

            if(json[file].botText != undefined) {
                for(var key in json[file].botText) {
                    this.botText[key] = json[file].botText[key];
                }
            }

            if(json[file].botErrText != undefined) {
                for(var key in json[file].botErrText) {
                    this.botErrText[key] = json[file].botErrText[key];
                }
            }
        }
    }

    public interpretBotString(str: string): string {
        var parts = str.split("\f");

        var retval = (parts[0] == "0")?this.botText[parts[1]]:this.botErrText[parts[1]];
        if(retval == undefined) {
            retval = Utils.replaceAll(Utils.replaceAll(this.botErrText["nolang"], "{0}", parts[1]), "{1}", (parts[0]=="0")?"message":"error");
            parts[0] = "1";
        } else {
            if(parts[2] != undefined) {
                for(var i = 2; i < parts.length; i++) {
                    retval = Utils.replaceAll(retval, "{"+ (i-2) +"}", parts[i]);
                }
            }
        }

        if(parts[0] == "1") retval = "<span class='botError'>"+ retval +"</span>";
        return retval;
    }
}