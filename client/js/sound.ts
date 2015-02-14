enum Sound {ChatBot, Error, Join, Leave, Receive, Send}

class Sounds {
    static SoundList = ["chatbot","error","join","leave","receive","send"];
    static currentSoundPack = "";
    static enabled = true;
    static volume = 0.5;

    static Play(id: Sound) {
        try {
            var sound = <HTMLAudioElement>document.getElementById(Sounds.currentSoundPack +"."+ Sounds.SoundList[id]);
            sound.pause();
            sound.currentTime = 0;
            sound.play();
        } catch(e) {}
    }

    static ChangeVolume(vol: number, f = false) {
        try {
            if(vol <= 1 && vol >= 0) {
                if(!f) Sounds.volume = vol;

                if(Sounds.enabled || f) {
                    var audioFiles = document.getElementsByTagName("audio");

                    for (var file in audioFiles) {
                        (<HTMLAudioElement>audioFiles[file]).volume = vol;
                    }
                }
            }
        } catch(e) {}
    }

    static Toggle(s: boolean) {
        Sounds.ChangeVolume(s ? Sounds.volume : 0, true);
        this.enabled = s;
    }

    static ChangePack(pack: string) {
        if(document.getElementById(pack +"."+ Sounds.SoundList[0]) != null)
            Sounds.currentSoundPack = pack;
        //else alert("Sound pack "+ pack +" does not exist !");
    }
}