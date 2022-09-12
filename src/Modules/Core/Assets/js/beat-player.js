/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */


songs = []; // init songs as array collection
let trackinfo = document.querySelectorAll('.track-info'); // query all .track.info
let tracks = {}; // init track as object
for (let i = 0, len = trackinfo.length; i < len; i++) { // loop through them and assign them to the tracks object using spread operator
    tracks = {
        ...tracks,
        index: trackinfo[i].dataset.song_index,
        name: trackinfo[i].dataset.track_name,
        artist: trackinfo[i].dataset.artist_name,
        url: trackinfo[i].dataset.track_audio_url,
        cover_art_url: trackinfo[i].dataset.cover_art_url,
        slug_id: trackinfo[i].dataset.track_slug_id,
        play_hash: trackinfo[i].dataset.track_play_hash
    }
    songs.push(tracks); // push of all 'em one after the order to songs
}
Amplitude.init({
    "songs": songs, // we then pass ever damn song to the amplitude songs
    "volume": 80,
    'callbacks': {
        // here, it gets interesting:
        'song_change': function () {
            // if repeat is enabled, force the repeated song to play (instead of the next song)
            if (repeat) {
                Amplitude.playNow(repeatedSong)
            }
        },
        playing: function () {
            let slugID = Amplitude.getActiveSongMetadata().slug_id,
                playHash = Amplitude.getActiveSongMetadata().play_hash;
            // console.log(Amplitude.getActiveSongMetadata().play_hash)
            cartUI.storePlayListening(slugID, playHash)
        }
    }
});
// Amplitude.pause();

var repeat = false;
var repeatedSong = null;
document.querySelector('[data-repeat]').addEventListener('click', function (e) {
    let dataRepeat = document.querySelector('[data-repeat]'); // we first got the value that is in the data-repeat attribute
    /*
     * We then say, if the value of data-repeat is off when clicked, we change it to on and set the repeat state to true,
     * otherwise if the value of data-repeat is on when clicked, we change it to off and repeat to false... A TOGGLE...
     */
    dataRepeat.getAttribute('data-repeat') === 'off' ?
        (dataRepeat.setAttribute('data-repeat', 'on'), repeat = true, repeatedSong = Amplitude.getActiveSongMetadata()) :
        (dataRepeat.setAttribute('data-repeat', 'off'), repeat = false);

    console.log(repeat);
    console.log(repeatedSong);
})

// Toggle Functionality for Play-pause button
MainPlayButtons = document.querySelectorAll('.tonics-play-outline');
for (let i = 0, len = MainPlayButtons.length; i < len; i++) {
    MainPlayButtons[i].addEventListener('click', function (e) {
        MainPlayButtons[i].querySelector('use').getAttribute("xlink:href") === '#tonics-play-outline' ?
            (MainPlayButtons[i].querySelector('use').setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', '#tonics-pause-outline'),
                MainPlayButtons[i].nextElementSibling.innerHTML = "Pause") :

            (MainPlayButtons[i].querySelector('use').setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', '#tonics-play-outline'),
                MainPlayButtons[i].nextElementSibling.innerHTML = "Play");

    });
}