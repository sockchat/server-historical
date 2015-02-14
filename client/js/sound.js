var Sound;
(function (Sound) {
    Sound[Sound["ChatBot"] = 0] = "ChatBot";
    Sound[Sound["Error"] = 1] = "Error";
    Sound[Sound["Join"] = 2] = "Join";
    Sound[Sound["Leave"] = 3] = "Leave";
    Sound[Sound["Receive"] = 4] = "Receive";
    Sound[Sound["Send"] = 5] = "Send";
})(Sound || (Sound = {}));
var Sounds = (function () {
    function Sounds() {
    }
    Sounds.Play = function (id) {
        try {
            var sound = document.getElementById(Sounds.currentSoundPack + "." + Sounds.SoundList[id]);
            sound.pause();
            sound.currentTime = 0;
            sound.play();
        }
        catch (e) {
        }
    };
    Sounds.ChangeVolume = function (vol, f) {
        if (f === void 0) { f = false; }
        try {
            if (vol <= 1 && vol >= 0) {
                if (!f)
                    Sounds.volume = vol;
                if (Sounds.enabled || f) {
                    var audioFiles = document.getElementsByTagName("audio");
                    for (var file in audioFiles) {
                        audioFiles[file].volume = vol;
                    }
                }
            }
        }
        catch (e) {
        }
    };
    Sounds.Toggle = function (s) {
        Sounds.ChangeVolume(s ? Sounds.volume : 0, true);
        this.enabled = s;
    };
    Sounds.ChangePack = function (pack) {
        if (document.getElementById(pack + "." + Sounds.SoundList[0]) != null)
            Sounds.currentSoundPack = pack;
        //else alert("Sound pack "+ pack +" does not exist !");
    };
    Sounds.SoundList = ["chatbot", "error", "join", "leave", "receive", "send"];
    Sounds.currentSoundPack = "";
    Sounds.enabled = true;
    Sounds.volume = 0.5;
    return Sounds;
})();
//# sourceMappingURL=sound.js.map