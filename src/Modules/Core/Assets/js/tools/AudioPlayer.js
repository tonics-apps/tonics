
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

export class AudioPlayer {

    audioPlayerSettings = new Map();
    playlist = null;
    currentGroupID = '';
    globalCurrentTrackTime = null;
    globalTotalTrackTime = null;
    previousTotalTrackDuration = null;
    playlistIndex = null;
    currentHowl = null;
    tonicsAudioPlayerGroups = null;
    groupKeyToMapKey = new Map();
    repeatSong = false;
    repeatMarkerSong = null;
    originalTracksInQueueBeforeShuffle = null;

    /**
     * Would Determine if the player should continue in the next page
     * @param $oneTimePlayer
     */
    constructor($oneTimePlayer = true) {
        if ($oneTimePlayer) {
            document.body.dataset.audio_player_onetime = 'true'
        } else {
            document.body.dataset.audio_player_onetime = 'false'
        }
        this.playlistIndex = 0;
        this.currentHowl = null;
        this.tonicsAudioPlayerGroups = document.querySelectorAll('[data-tonics-audioplayer-group]');
        this.resetAudioPlayerSettings();

        this.progressContainer = document.querySelector('.progress-container');
        this.songSlider = null;
        if (this.progressContainer) {
            this.songSlider = this.progressContainer.querySelector('.song-slider');
        }
        this.userIsSeekingSongSlider = false;
        if (document.querySelector('.audio-player-queue')) {
            this.originalTracksInQueueBeforeShuffle = document.querySelector('.audio-player-queue').innerHTML;
        }


        // Chrome Navigator
        navigator.mediaSession.setActionHandler('play', () => {
            this.play();
        });
        navigator.mediaSession.setActionHandler('pause', () => {
            this.pause();
        });
        navigator.mediaSession.setActionHandler('previoustrack', () => {
            this.prev();
        });
        navigator.mediaSession.setActionHandler('nexttrack', () => {
            this.next();
        });

        this.mutationObserver();
    }

    mutationHandlerFunc(audioTrack) {
        let self = this;
        if (audioTrack && !audioTrack.dataset.hasOwnProperty('trackloaded')) {
            audioTrack.dataset.trackloaded = 'false';
            self.resetAudioPlayerSettings();
            self.originalTracksInQueueBeforeShuffle = document.querySelector('.audio-player-queue').innerHTML;
            self.resetQueue();
        }
    }

    mutationObserver() {
        const audioPlayerObserver = new MutationObserver(((mutationsList, observer) => {
            for (const mutation of mutationsList) {
                let foundNode = false;
                for (let i = 0; i < mutation.addedNodes.length; i++) {
                    // added nodes.
                    let addedNode = mutation.addedNodes[i];
                    if (addedNode.nodeType === Node.ELEMENT_NODE) {
                        let audioTrack = addedNode.querySelector('[data-tonics-audioplayer-track]');
                        if (audioTrack) {
                            // Found the node we are looking for, so break out of the loop
                            this.mutationHandlerFunc(audioTrack);
                            foundNode = true;
                            break;
                        }
                    }
                }

                if (foundNode) {
                    return;
                }

                // for attribute
                if (mutation.attributeName === "data-tonics-audioplayer-track") {
                    let audioTrack = mutation.target;
                    this.mutationHandlerFunc(audioTrack);
                }
            }
        }));
        // Start observing the target node for configured mutations
        audioPlayerObserver.observe(document, {attributes: true, childList: true, subtree: true});
    }

    run() {
        let self = this;
        let audioPlayerGlobalContainer = self.getAudioPlayerGlobalContainer();
        if (audioPlayerGlobalContainer) {
            this.onPageReload();

            let tonics_audio_seeking = false, tonics_audio_holdTimeout;
            document.addEventListener('mousedown', (e) => {
                let el = e.target, self = this;
                // forward seeking
                if (el.dataset.hasOwnProperty('audioplayer_next')) {
                    tonics_audio_holdTimeout = setTimeout(() => {
                        tonics_audio_seeking = true;
                        seekForward();
                    }, 600); // Start seeking after the button has been held down for 0.6 seconds
                }

                // backward seeking
                if (el.dataset.hasOwnProperty('audioplayer_prev')) {
                    tonics_audio_holdTimeout = setTimeout(() => {
                        tonics_audio_seeking = true;
                        seekBackward();
                    }, 600);  // Start seeking after the button has been held down for 0.6 seconds
                }
            });

            function seekForward() {
                if (tonics_audio_seeking) {
                    self.currentHowl.seek(self.currentHowl.seek() + 1);  // Seek forward 1 second
                    setTimeout(seekForward, 100);  // Call this function again in 100 milliseconds
                }
            }

            function seekBackward() {
                if (tonics_audio_seeking) {
                    const currentSeek = self.currentHowl.seek();  // Get the current seek position
                    const newSeek = currentSeek - 1;  // Calculate the new seek position
                    if (newSeek >= 0) {  // Only seek if the new seek position is greater than or equal to 0
                        self.currentHowl.seek(newSeek);  // Seek backward 1 second
                    }
                    setTimeout(seekBackward, 100);  // Call this function again in 100 milliseconds
                }
            }

            function removeSeeking() {
                tonics_audio_seeking = false;
                clearTimeout(tonics_audio_holdTimeout);
            }

            document.addEventListener('click', (e) => {
                let el = e.target;
                // toggle play
                if (el.dataset.hasOwnProperty('audioplayer_play')) {
                    // play;
                    if (el.dataset.audioplayer_play === 'false') {
                        el.dataset.audioplayer_play = 'true'
                        // if it contains a url
                        if (el.dataset.hasOwnProperty('audioplayer_songurl')) {
                            let songURL = el.dataset.audioplayer_songurl;
                            if (el.dataset.hasOwnProperty('audioplayer_groupid')) {
                                audioPlayerGlobalContainer.dataset.audioplayer_groupid = el.dataset.audioplayer_groupid;
                            }
                            self.loadPlaylist();
                            let groupSongs = null;
                            if (self.audioPlayerSettings.has(self.currentGroupID)) {
                                groupSongs = self.audioPlayerSettings.get(self.currentGroupID);
                                if (groupSongs.has(songURL)) {
                                    self.playlistIndex = groupSongs.get(songURL).songID;
                                    self.play();
                                }
                            }
                        } else {
                            if (this.loadPlaylist()) {
                                this.play();
                            }
                        }
                        // pause
                    } else {
                        el.dataset.audioplayer_play = 'false'
                        this.audioPaused = true;
                        self.pause();
                    }
                }

                // next
                if (el.dataset.hasOwnProperty('audioplayer_next')) {
                    if (tonics_audio_seeking === false && el.dataset.audioplayer_next === 'true') {
                        this.next();
                    }
                }

                // prev
                if (el.dataset.hasOwnProperty('audioplayer_prev')) {
                    if (tonics_audio_seeking === false && el.dataset.audioplayer_prev === 'true') {
                        this.prev();
                    }
                }

                // Remove any possible seeking
                removeSeeking();

                // repeat
                if (el.dataset.hasOwnProperty('audioplayer_repeat')) {
                    if (el.dataset.audioplayer_repeat === 'true') {
                        self.repeatSong = false;
                        el.dataset.audioplayer_repeat = 'false';
                    } else {
                        self.repeatSong = true;
                        el.dataset.audioplayer_repeat = 'true';
                    }
                }

                // marker_repeat
                if (el.dataset.hasOwnProperty('audioplayer_marker_repeat')){
                    if (el.dataset.audioplayer_marker_repeat === 'true') {
                        self.repeatMarkerSong = null;
                        el.dataset.audioplayer_marker_repeat = 'false';
                    } else {
                        // remove all existing audio_marker_repeat
                        const allMarkerRepeat = document.querySelectorAll('[data-audioplayer_marker_repeat]');
                        allMarkerRepeat.forEach((mark) => {
                           mark.dataset.audioplayer_marker_repeat = 'false';
                        });
                        self.repeatMarkerSong = {
                            'start': el.dataset.audioplayer_marker_start,
                            'start_percentage': el.dataset.audioplayer_marker_start_percentage,
                            'end': el.dataset.audioplayer_marker_end,
                        };
                        el.dataset.audioplayer_marker_repeat = 'true';
                    }
                }

                // marker jump
                if (el.dataset.hasOwnProperty('audioplayer_marker_play_jump')){
                    const seekToPosition = el.dataset.audioplayer_marker_play_jump; // get the percentage
                    this.seek(seekToPosition); // and jump
                }

                // shuffle
                if (el.dataset.hasOwnProperty('audioplayer_shuffle')) {
                    if (el.dataset.audioplayer_shuffle === 'true') {
                        el.dataset.audioplayer_shuffle = 'false';
                        if (document.querySelector('.audio-player-queue') && this.originalTracksInQueueBeforeShuffle) {
                            document.querySelector('.audio-player-queue').innerHTML = this.originalTracksInQueueBeforeShuffle;
                            if (this.currentHowl !== null) {
                                let src = self.currentHowl._src;
                                self.resetQueue();
                                // self.resetAudioPlayerSettings();
                                self.setSongUrlPlayAttribute(src[0], 'true', 'Pause');
                            }
                        }
                    } else {
                        el.dataset.audioplayer_shuffle = 'true';
                        let tracksInQueue = document.querySelectorAll('.track-in-queue');
                        if (tracksInQueue) {
                            for (let i = tracksInQueue.length - 1; i > 0; i--) {
                                const j = Math.floor(Math.random() * (i + 1));
                                swapNodes(
                                    tracksInQueue[j],
                                    tracksInQueue[i],
                                    tracksInQueue[j].getBoundingClientRect(), () => {
                                        self.resetQueue();
                                        // self.setCorrectPlaylistIndex();
                                        // self.resetAudioPlayerSettings();
                                    }
                                );
                            }
                        }
                    }
                }

                // Fire The ClickEvent For Tonics Audio
                let OnAudioClick = new OnAudioPlayerClickEvent(self.getSongData(), el);
                self.getEventDispatcher().dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnAudioClick, OnAudioPlayerClickEvent);
            });

            document.addEventListener('pointerdown', self.sliderThumbMouseDown.bind(self));
            document.addEventListener('pointerup', self.sliderThumbMouseUp.bind(self));

            // volume
            document.addEventListener('input', self.volume.bind(self));
        }
    }

    onPageReload() {
        let self = this;
        const storedVolume = localStorage.getItem('HowlerJSVolume');
        if (storedVolume) {
            Howler.volume(parseFloat(storedVolume));
            const volumeSlider = document.querySelector('.volume-slider');
            if (volumeSlider) {
                volumeSlider.value = storedVolume;
            }
        }

        // Get the current main browser URL
        const currentURL = window.location.href;
        // Retrieve the stored position from localStorage
        let storedData = localStorage.getItem(currentURL);
        if (storedData) {
            storedData = JSON.parse(storedData);
            self.loadPlaylist();
            let groupSongs = null;
            if (self.audioPlayerSettings.has(storedData.currentGroupID)) {
                groupSongs = self.audioPlayerSettings.get(storedData.currentGroupID);
                if (groupSongs.has(storedData.songKey)) {
                    self.playlistIndex = groupSongs.get(storedData.songKey).songID;
                    // Load Howl
                     self.play();

                    // Seek to the stored position once the file is loaded
                    self.currentHowl.once('load', () => {
                        let progress = storedData.currentPos / self.currentHowl.duration() * 100;
                        if(this.songSlider){
                            this.songSlider.value = progress;
                            self.seek(progress);
                        }
                    });
                }
            }

        }
    }

    bootPlaylistAndSongs(fromQueue = false) {

        let self = this,
            tonicsAudioPlayerTracks = document.querySelectorAll('[data-tonics-audioplayer-track]');

        if (fromQueue) {
            tonicsAudioPlayerTracks = document.querySelector('.audio-player-queue-list').querySelectorAll('[data-tonics-audioplayer-track]');
        }

        // FOR GROUP
        if (this.tonicsAudioPlayerGroups.length > 0) {
            this.tonicsAudioPlayerGroups.forEach(value => {
                let el = value;
                // The ID can be a name or Whatever
                if (el.dataset.hasOwnProperty('audioplayer_groupid')) {
                    self.audioPlayerSettings.set(el.dataset.audioplayer_groupid, new Map());
                }
            });
        }

        // FOR TRACK
        let groupKeyToMapKeyArray = [];
        if (tonicsAudioPlayerTracks.length > 0) {
            // we can rely on the i var as a key because some track song_url might not exist
            // so, we manually use tonicsTrackKey and increment the counter ourselves
            let tonicsTrackKey = 0;
            for (let i = 0; i < tonicsAudioPlayerTracks.length; i++) {
                const trackElButton = tonicsAudioPlayerTracks[i];
                let key = tonicsTrackKey,
                    groupKey,
                    groupMap;

                trackElButton.dataset.trackloaded = 'true';
                // first get the track groupID, if not set, we set it to global group
                if (trackElButton.dataset.hasOwnProperty('audioplayer_groupid')) {
                    groupKey = trackElButton.dataset.audioplayer_groupid;
                } else {
                    groupKey = 'GLOBAL_GROUP';
                }

                // The song elements needs at-least the songurl to get added to a playlist
                if (trackElButton.dataset.hasOwnProperty('audioplayer_songurl') && trackElButton.dataset.audioplayer_songurl) {
                    groupMap = self.audioPlayerSettings.get(groupKey);
                    let songurl = trackElButton.dataset.audioplayer_songurl;
                    const songData = {
                        'songID': key,
                        'songtitle': trackElButton.dataset.audioplayer_title,
                        'songimage': trackElButton.dataset.audioplayer_image,
                        'songurl': songurl,
                        'url_page': trackElButton.dataset.url_page,
                        'howl': null,
                        'format': (trackElButton.dataset.hasOwnProperty('audioplayer_format')) ? trackElButton.dataset.audioplayer_format : null,
                        'license': (trackElButton.dataset.hasOwnProperty('licenses')) ? JSON.parse(trackElButton.dataset.licenses) : null,
                        '_dataset': trackElButton.dataset,
                    }
                    groupMap.set(songurl, songData);
                    groupKeyToMapKeyArray.push(songurl);
                    self.groupKeyToMapKey.set(groupKey, groupKeyToMapKeyArray);
                    self.audioPlayerSettings.set(groupKey, groupMap);
                    ++tonicsTrackKey;
                }
            }
        }
    }

    resetAudioPlayerSettings() {
        let self = this
        this.audioPlayerSettings = new Map();
        this.audioPlayerSettings.set('GLOBAL_GROUP', new Map());
        this.groupKeyToMapKey = new Map();
        this.bootPlaylistAndSongs();
        this.loadPlaylist();
        this.loadToQueue(this.audioPlayerSettings.get(this.currentGroupID));
        this.setCorrectPlaylistIndex();

        if (this.groupKeyToMapKey.size > 0) {
            let audioPlayerEl = document.querySelector('.audio-player');
            if (audioPlayerEl && audioPlayerEl.classList.contains('d:none')) {
                audioPlayerEl.classList.remove('d:none');
            }
        }
    }

    resetQueue() {
        this.audioPlayerSettings = new Map();
        this.audioPlayerSettings.set('GLOBAL_GROUP', new Map());
        this.groupKeyToMapKey = new Map();
        this.bootPlaylistAndSongs(true);
        this.loadPlaylist();
        this.setCorrectPlaylistIndex();
    }

    loadToQueue(tracks) {
        let queueContainer = document.querySelector('.audio-player-queue-list');
        if (queueContainer) {
            queueContainer.innerHTML = "";
            tracks.forEach(value => {

                let playing;
                if (this.currentHowl !== null && this.currentHowl._src[0] === value.songurl) {
                    playing = 'true'
                } else {
                    playing = "false"
                }

                queueContainer.insertAdjacentHTML('beforeend', `
<li tabindex="0" class="color:black cursor:move draggable track-in-queue bg:white-one border-width:default border:black position:relative">
                    <div class="queue-song-info d:flex align-items:center flex-gap:small">
                        <a href="${value.url_page}" data-tonics_navigate data-url_page="${value.url_page}"  
                        title="${value.songtitle}" class="cursor:pointer color:black text:no-wrap width:80px text-overflow:ellipsis">${value.songtitle}</a>
                    </div>
                    
<button type="button" title="Play" data-tonics-audioplayer-track="" 
data-trackloaded
data-audioplayer_songurl="${value.songurl}" 
data-audioplayer_title="${value.songtitle}" 
data-audioplayer_image="${value.songimage}" 
data-audioplayer_format="${value.format}" 
data-url_page="${value.url_page}" 
data-audioplayer_play="${playing}" class="audioplayer-track border:none act-like-button icon:audio bg:transparent cursor:pointer color:black">
    <svg class="audio-play icon:audio tonics-widget pointer-events:none">
        <use class="svgUse" xlink:href="#tonics-audio-play"></use>
    </svg>
    <svg class="audio-pause icon:audio tonics-widget pointer-events:none">
        <use class="svgUse" xlink:href="#tonics-audio-pause"></use>
    </svg>
</button>
                </li>
`)
            })
        }
    }

    setCorrectPlaylistIndex() {
        let currentPlayingInQueue = document.querySelector('.audio-player-queue [data-audioplayer_play="true"]');
        if (currentPlayingInQueue) {
            let songUrl = currentPlayingInQueue.dataset.audioplayer_songurl;
            let groupKey = 'GLOBAL_GROUP';
            if (currentPlayingInQueue.dataset.hasOwnProperty('audioplayer_groupid')) {
                groupKey = currentPlayingInQueue.dataset.audioplayer_groupid;
            }
            if (this.groupKeyToMapKey.has(groupKey)) {
                let songs = this.groupKeyToMapKey.get(groupKey);
                let newPlaylistIndex = songs.indexOf(songUrl);
                if (newPlaylistIndex !== -1) {
                    this.playlistIndex = newPlaylistIndex;
                }
            }
        }
    }

    setSongUrlPlayAttribute(url, attrVal, title = null) {
        let currentSongWithURL = document.querySelectorAll(`[data-audioplayer_songurl="${url}"]`),
            globalPlayBTN = document.querySelector('.global-play');

        if (currentSongWithURL.length > 0) {
            currentSongWithURL.forEach(value => {
                if (value.dataset.hasOwnProperty('audioplayer_play') && value !== globalPlayBTN) {
                    value.dataset.audioplayer_play = attrVal
                    if (title) {
                        value.title = title;
                    }
                }
            });
        }
    }

    getAudioPlayerGlobalContainer() {
        return document.querySelector('.audio-player-global-container');
    }

    loadPlaylist() {
        let self = this;
        let audioPlayerGlobalContainer = self.getAudioPlayerGlobalContainer();
        if (audioPlayerGlobalContainer && audioPlayerGlobalContainer.dataset.hasOwnProperty('audioplayer_groupid')) {
            let audioPlayerGroupID = audioPlayerGlobalContainer.dataset.audioplayer_groupid;
            if (self.audioPlayerSettings === null) {
                this.bootPlaylistAndSongs();
            }
            if (self.audioPlayerSettings.has(audioPlayerGroupID)) {
                this.playlist = self.groupKeyToMapKey.get(audioPlayerGroupID);
                this.currentGroupID = audioPlayerGroupID;
                return true;
            }
        }
        return false;
    }

    getSongData() {
        if (this.playlist){
            let songKey = this.playlist[this.playlistIndex],
                groupSongs = this.audioPlayerSettings.get(this.currentGroupID);

            if (groupSongs.has(songKey)) {
                const Data = groupSongs.get(songKey);
                Data._self = this;
                return Data;
            }
        }

        return false;
    }

    volume(e) {
        let el = e.target;
        // volume slider
        if (el.classList.contains('volume-slider')) {
            Howler.volume(el.value);
            localStorage.setItem('HowlerJSVolume', el.value);
        }
    }

    sliderThumbMouseDown(e) {
        let el = e.target;
        let self = this;
        if (el.classList.contains('song-slider')) {
            self.userIsSeekingSongSlider = true;
        }
    }

    sliderThumbMouseUp(e) {
        let el = e.target;
        let self = this;
        if (el.classList.contains('song-slider')) {
            self.userIsSeekingSongSlider = false;
            self.seek(el.value);
        }
    }

    pause() {
        let self = this,
            songData = self.currentHowl,
            globalPlayBTN = document.querySelector('.global-play');

        if (globalPlayBTN && globalPlayBTN.dataset.hasOwnProperty('audioplayer_play')) {
            globalPlayBTN.dataset.audioplayer_play = 'false';
            globalPlayBTN.title = 'Play';
        }

        if (songData !== null) {
            songData.pause();
            this.setSongUrlPlayAttribute(this.getSongData().songurl, 'false', 'Play')
        }
    }

    handlePlayElementSettings() {
        let songData = this.getSongData(),
            globalPlayBTN = document.querySelector('.global-play'),
            playings = document.querySelectorAll(`[data-audioplayer_play="true"]`);

        // pause current howl, or should we destroy it?
        if (this.currentHowl) {
            this.currentHowl.pause();
        }

        // reset existing play
        if (playings && playings.length > 0) {
            playings.forEach(value => {
                value.dataset.audioplayer_play = 'false'
            });
        }

        if (globalPlayBTN && globalPlayBTN.dataset.hasOwnProperty('audioplayer_play')) {
            globalPlayBTN.dataset.audioplayer_play = 'true';
            globalPlayBTN.title = 'Pause';
        }

        this.setSongUrlPlayAttribute(songData.songurl, 'true', 'Pause');
    }

    play() {
        let self = this,
            songData = self.getSongData().howl;

        Howler.volume(document.querySelector('.volume-slider').value);
        self.handlePlayElementSettings();

        if (songData === null) {
            self.getSongData().howl = self.newHowlPlay();
            songData = self.getSongData().howl;
        }

        try {
            songData.play();
        } catch (e) {
            self.getSongData().howl = self.newHowlPlay();
            songData = self.getSongData().howl;
            songData.play();
        }

        self.currentHowl = songData;
    }

    newHowlPlay(onload = null) {
        let self = this,
            songData = self.getSongData();
        const TonicsHowl = new Howl({
            preload: false, // this is the only way that dropBox worked
            src: [songData.songurl],
            html5: true,
            // this causes the player not to play, a bug in HOWLER JS?
            // format: [songData.format],
            onplay: () => {
                // we only update marker if it isn't already set
                if (!self.repeatMarkerSong){
                    self.handleMarkerUpdating();
                }
                // Start updating the progress of the track.
                requestAnimationFrame(self.step.bind(self));
            },
            onseek: () => {
                // Start updating the progress of the track.
                requestAnimationFrame(self.step.bind(self));
            },
            onend: () => {
                if (self.repeatSong) {
                    self.pause();
                    self.play();
                } else {
                    self.next();
                }

                self.removeMarker()
            }
        });

        // sometimes the pause event can trigger twice, this put a stop to it
        // note: if a song has not been paused, and you played a new one, pause event would fire and then play event would also fire, meaning they would both be fired
        let isPaused = false;

        TonicsHowl.on('play', function() {
            self.updateGlobalSongProp(songData.songtitle, songData.songimage)
            isPaused = false;
            let OnAudioPlay = new OnAudioPlayerPlayEvent(self.getSongData());
            self.getEventDispatcher().dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnAudioPlay, OnAudioPlayerPlayEvent);
        });

        TonicsHowl.on('pause', function() {
            if (!isPaused) {
                isPaused = true;
                // Fire The PauseEvent For Tonics
                let OnAudioPause = new OnAudioPlayerPauseEvent(self.getSongData());
                self.getEventDispatcher().dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnAudioPause, OnAudioPlayerPauseEvent);
            }
        });

        return TonicsHowl;
    }

    getMarkerPercentageAndSeconds(time, duration) {
        if (!time || !/^\d{1,2}:\d{1,2}(:\d{1,2})?$/.test(time)) {
            console.error(`Invalid time format: ${time}. Should be in format "00:00" or "00:00:00"`);
            return;
        }
        let timeParts = time.split(':');
        let hours = timeParts.length > 2 ? parseInt(timeParts[0], 10) : 0;
        let minutes = parseInt(timeParts[timeParts.length-2], 10);
        let seconds = timeParts.length > 2 ? parseInt(timeParts[timeParts.length-1], 10) : parseInt(timeParts[timeParts.length-1], 10);

        let totalSeconds = (hours * 3600) + (minutes * 60) + seconds;
        if(!duration || duration <= 0) {
            console.error(`audioTrackLength is not defined or is <= 0`);
            return;
        }
        let totalPercentage = (totalSeconds / duration) * 100;
        return {
            percentage: totalPercentage,
            seconds: totalSeconds
        };
    }

    updateMarker(elementClassOrId, markerData) {
        let markerStartInfo = markerData._track_marker_start_info;
        let markerEndInfo = markerData._track_marker_end_info;

        let markerTemplate = document.querySelector('.tonics-audio-marker');
        let markerHTML = markerTemplate.innerHTML;
        markerHTML = markerHTML.replace(/Marker_Percentage/g, markerStartInfo.percentage);
        markerHTML = markerHTML.replace(/Marker_Text/g, markerStartInfo.text);
        markerHTML = markerHTML.replace(/MARKER_START/g, markerStartInfo.seconds);
        markerHTML = markerHTML.replace(/MARKER_END/g, markerEndInfo.seconds);

        let targetElement = document.querySelector(elementClassOrId);
        if (targetElement){
            targetElement.insertAdjacentHTML('afterend', markerHTML);
        }

    }

    handleMarkerUpdating() {
        const songData = this.getSongData();
        if (songData?.markers?.length > 0){
            // Remove Existing Markers if there is any.
            let markers = document.querySelectorAll('div[data-audioplayer_marker]');
            markers.forEach(marker => marker.remove());

            songData.markers.forEach((marker) => {
                if (marker._track_marker_start_info){
                    this.updateMarker('.song-slider', marker);
                }
            });
        }
    }

    storeSongPosition() {
        // Get the Howl we want to manipulate.
        let songData = this.getCurrentHowl();
        let storeKey = window.location.href;
        // Get the current position of the song in seconds
        const currentPosition = songData.seek();
        // Store the current URL and position in localStorage
        localStorage.setItem(storeKey, JSON.stringify({
            'currentPos': currentPosition,
            'songKey': this.playlist[this.playlistIndex],
            'currentGroupID': this.currentGroupID,
        }));
    }

    prev() {
        let self = this;
        self.removeMarker()
        if (self.playlist === null) {
            self.loadPlaylist();
        }
        let index = self.playlistIndex - 1;
        if (index < 0) {
            index = 0;
        }
        this.skipTo(index);
    }

    next() {
        let self = this;
        self.removeMarker()
        if (self.playlist === null) {
            self.loadPlaylist();
        }
        let index = self.playlistIndex + 1;
        if (index >= self.playlist.length) {
            index = 0;
        }
        this.skipTo(index);
    }

    skipTo(index) {
        let self = this;

        // Stop the current track.
        if (self.getCurrentHowl()) {
            self.getCurrentHowl().stop();
        }
        // Play the new track.
        self.playlistIndex = index;
        self.play();
    }

    seek(percentage) {
        let self = this;
        // Get the Howl we want to manipulate.
        let songData = self.getCurrentHowl();

        // calculate the duration to seek to
        let skipToDuration = songData.duration() * percentage / 100;
        if (songData) {
            songData.seek(skipToDuration);
            this.moveSlider();
        }
    }

    moveSlider() {
        let self = this;
        let howl = self.getCurrentHowl();
        // Determine our current seek position.
        let seek = howl.seek() || 0;
        let progress = seek / howl.duration() * 100 || 0;
        progress = Math.round(progress);
        if (self.userIsSeekingSongSlider === false) {
            self.songSlider.value = progress;
        }
    }

    step() {
        let self = this;
        let howl = self.getCurrentHowl();
        if (howl.playing()) {
            if (self.repeatMarkerSong){
                let roundedSeek = Math.round(howl.seek());
                let start = parseInt(self.repeatMarkerSong.start), end = parseInt(self.repeatMarkerSong.end);
                if (roundedSeek >= end) {
                    howl.seek(start);
                }
            }
            self.moveSlider();
            self.storeSongPosition();
            self.updateGlobalTime();
            requestAnimationFrame(this.step.bind(self));
        }
    }

    updateGlobalTime(){
        let songData = this.getCurrentHowl();
        // Get the current position of the song in seconds
        const currentPosition = songData.seek();
        if (!this.globalCurrentTrackTime){
            this.globalCurrentTrackTime = document.querySelector("[data-current_track_time]");
        }
        if (!this.globalTotalTrackTime){
            this.globalTotalTrackTime = document.querySelector("[data-total_track_time]");
        }

        if (this.globalCurrentTrackTime){
            // Set the innertext of the data-current_track_time element to the formatted current track time
            this.globalCurrentTrackTime.innerText = this.formatTimeToHourMinSec(currentPosition);
        }

        if (this.globalTotalTrackTime){
            // Get the total track duration from howlerJS
            const totalTrackDuration = songData.duration();
            // Only set the total track duration if it is different from the previous one
            if ( this.previousTotalTrackDuration !== totalTrackDuration) {
                // Set the innertext of the data-total_track_time element to the formatted total track duration
                this.globalTotalTrackTime.innerText = this.formatTimeToHourMinSec(totalTrackDuration);
                // Update the previous total track duration
                this.previousTotalTrackDuration = totalTrackDuration;
            }
        }
    }

    formatTimeToHourMinSec(time) {
        // Check if the time is a valid number
        if (isNaN(time) || time < 0) {
            return "-";
        }

        const hours = Math.floor(time / 3600);
        const minutes = Math.floor((time % 3600) / 60);
        const seconds = Math.floor(time % 60);
        let formattedTime = "";

        if (hours > 0) {
            formattedTime += ("0" + hours).slice(-2) + ":";
        }

        formattedTime += ("0" + minutes).slice(-2) + ":";
        formattedTime += ("0" + seconds).slice(-2);

        return formattedTime;
    }

    removeMarker(){
        // at this point, we gotta remove the marker
        this.repeatMarkerSong = null;
    }

    updateGlobalSongProp(title = '', image = '') {
        let songTitle = document.querySelector('[data-audioplayer_globaltitle]'),
            songImage = document.querySelector('.main-album-art[data-audioplayer_globalart]');

        if (songTitle) {
            songTitle.innerText = title;
            songTitle.title = title;
        }

        if (songImage) {
            songImage.src = image;
        }

        if ('mediaSession' in navigator) {
            navigator.mediaSession.metadata = new MediaMetadata({
                title: title,
                artwork: [
                    {src: this.convertRelativeToAbsoluteURL(image), sizes: '200x200', type: 'image/png'},
                ]
            });
        }

    }

    convertRelativeToAbsoluteURL(url) {
        // Check if the URL is a relative URL
        if (!url.startsWith('http')) {
            // Convert the relative URL to an absolute URL using the new URL constructor
            url = new URL(url, window.location.href).href;
        }

        return url;
    }

    getCurrentHowl() {
        return this.currentHowl;
    }

    getEventDispatcher() {
        return window.TonicsEvent.EventDispatcher;
    }
}

// Abstract Class
class AudioPlayerEventAbstract {

    constructor(event) {
        this._songData = event;
    }

    get songData() {
        return this._songData;
    }

    set songData(value) {
        this._songData = value;
    }
}

// Event Classes
class OnAudioPlayerPlayEvent extends AudioPlayerEventAbstract {


}

class OnAudioPlayerPauseEvent extends AudioPlayerEventAbstract {
}

class OnAudioPlayerClickEvent extends AudioPlayerEventAbstract {

    constructor(event, eventEl) {
        super(event);
        this._eventEl = eventEl;
    }

    get eventEl() {
        return this._eventEl;
    }
}

if (document.querySelector('.audio-player')) {
    let audioPlayer = new AudioPlayer();
    audioPlayer.run();
    let parent = '.audio-player-queue-list',
        widgetChild = `.track-in-queue`,
        top = false, bottom = false,
        sensitivity = 0, sensitivityMax = 5;
    if (window?.TonicsScript.hasOwnProperty('Draggables')) {
        window.TonicsScript.Draggables(parent)
            .settings(widgetChild, ['.track-license'], false) // draggable element
            .onDragDrop(function (element, self) {
                let elementDropped = self.getDroppedTarget().closest(widgetChild);
                let elementDragged = self.getDragging().closest(widgetChild);
                if (elementDropped !== elementDragged && top || bottom) {
                    // swap element
                    swapNodes(elementDragged, elementDropped, self.draggingOriginalRect, () => {
                        audioPlayer.resetQueue();
                    });
                    sensitivity = 0;
                    top = false;
                    bottom = false;
                }
            }).onDragTop((element) => {
            if (sensitivity++ >= sensitivityMax) {
                let dragToTheTop = element.previousElementSibling;
                if (dragToTheTop && dragToTheTop.classList.contains('track-in-queue')) {
                    top = true;
                }
            }
        }).onDragBottom((element) => {
            if (sensitivity++ >= sensitivityMax) {
                let dragToTheBottom = element.nextElementSibling;
                if (dragToTheBottom && dragToTheBottom.classList.contains('track-in-queue')) {
                    bottom = true;
                }
            }
        }).run();

    }

    if (window?.TonicsScript.hasOwnProperty('MenuToggle') && window?.TonicsScript.hasOwnProperty('Query')) {
        window.TonicsScript.MenuToggle('.audio-player', window.TonicsScript.Query())
            .settings('.audio-player-global-container', '.dropdown-toggle', '.audio-player-queue')
            .buttonIcon('#tonics-arrow-down', '#tonics-arrow-up')
            .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
            .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
            .stopPropagation(false)
            .closeOnClickOutSide(false)
            .run();

        window.TonicsScript.MenuToggle('.time-progress', window.TonicsScript.Query())
            .settings('.time-progress-marker', '.marker-dropdown-toggle', '.audio-player-marker-data')
            .buttonIcon('#tonics-arrow-down', '#tonics-arrow-up')
            .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
            .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
            .stopPropagation(false)
            .closeOnClickOutSide(false)
            .run();

        window.TonicsScript.MenuToggle('.audio-player-queue', window.TonicsScript.Query())
            .settings('.track-in-queue', '.dropdown-toggle', '.track-license')
            .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
            .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
            .stopPropagation(false)
            .closeOnClickOutSide(false)
            .run();
    }

}