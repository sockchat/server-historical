var Sounds;
(function (Sounds) {
    Sounds[Sounds["ChatBot"] = 0] = "ChatBot";
    Sounds[Sounds["Error"] = 1] = "Error";
    Sounds[Sounds["Join"] = 2] = "Join";
    Sounds[Sounds["Leave"] = 3] = "Leave";
    Sounds[Sounds["Receive"] = 4] = "Receive";
    Sounds[Sounds["Send"] = 5] = "Send";
})(Sounds || (Sounds = {}));

var Sound = (function () {
    function Sound() {
    }
    Sound.Play = function (id) {
        document.getElementById(Sound.SoundList[id]).play();
    };

    Sound.ChangeVolume = function (vol) {
        if (vol > 1 || vol < 0)
            alert("WHAT THE FUCK ARE YOU DOING");
        else {
            for (var sound in Sound.SoundList) {
                document.getElementById(Sound.SoundList[sound]).volume = vol;
            }
        }
    };

    Sound.ChangePack = function (pack) {
        for (var sound in Sound.SoundList) {
            document.getElementById(Sound.SoundList[sound]).volume = vol;
        }
    };
    Sound.SoundList = ["chatbot", "error", "join", "leave", "receive", "send"];
    return Sound;
})();
//# sourceMappingURL=sound.js.map
