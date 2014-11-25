enum Sounds {ChatBot, Error, Join, Leave, Receive, Send}

class Sound {
    static SoundList = ["chatbot","error","join","leave","receive","send"];

    static Play(id: Sounds) {
        (<HTMLAudioElement>document.getElementById(Sound.SoundList[id])).play();
    }

    static ChangeVolume(vol: number) {
        if(vol > 1 || vol < 0) alert("WHAT THE FUCK ARE YOU DOING");
        else {
            for(var sound in Sound.SoundList) {
                (<HTMLAudioElement>document.getElementById(Sound.SoundList[sound])).volume = vol;
            }
        }
    }

    static ChangePack(pack: string) {
        for(var sound in Sound.SoundList) {
            (<HTMLAudioElement>document.getElementById(Sound.SoundList[sound])).volume = vol;
        }
    }
}