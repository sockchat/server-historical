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
    Sounds.ChangeVolume = function (vol) {
        try {
            if (vol > 1 || vol < 0)
                alert("WHAT THE FUCK ARE YOU DOING");
            else {
                var audioFiles = document.getElementsByTagName("audio");
                for (var file in audioFiles) {
                    audioFiles[file].volume = vol;
                }
            }
        }
        catch (e) {
        }
    };
    Sounds.ChangePack = function (pack) {
        if (document.getElementById(pack + "." + Sounds.SoundList[0]) != null)
            Sounds.currentSoundPack = pack;
        else
            alert("Sound pack " + pack + " does not exist !");
    };
    Sounds.SoundList = ["chatbot", "error", "join", "leave", "receive", "send"];
    Sounds.currentSoundPack = "";
    return Sounds;
})();
//# sourceMappingURL=sound.js.map