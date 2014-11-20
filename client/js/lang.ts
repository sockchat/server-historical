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
}