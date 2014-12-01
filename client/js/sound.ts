enum Sound {ChatBot, Error, Join, Leave, Receive, Send}

class Sounds {
    static SoundList = ["chatbot","error","join","leave","receive","send"];
    static currentSoundPack = "";

    static Play(id: Sound) {
        var sound = <HTMLAudioElement>document.getElementById(Sounds.currentSoundPack +"."+ Sounds.SoundList[id]);
        sound.pause();
        sound.currentTime = 0;
        sound.play();
    }

    static ChangeVolume(vol: number) {
        if(vol > 1 || vol < 0) alert("WHAT THE FUCK ARE YOU DOING");
        else {
            var audioFiles = document.getElementsByTagName("audio");

            for(var file in audioFiles) {
                (<HTMLAudioElement>audioFiles[file]).volume = vol;
            }
        }
    }

    static ChangePack(pack: string) {
        if(document.getElementById(pack +"."+ Sounds.SoundList[0]) != null)
            Sounds.currentSoundPack = pack;
        else alert("Sound pack "+ pack +" does not exist !");
    }
}