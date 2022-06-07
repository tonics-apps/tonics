export class AudioPlayer {
    audioPlayerSettings = new Map();
    playlist = null;
    currentGroupID = '';
    playlistIndex = null;
    currentHowl = null;
    tonicsAudioPlayerGroups = null;
    groupKeyToMapKey = new Map();
    repeatSong = false;
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
        this.userIsSeeking = false;
        if (document.querySelector('.audio-player-queue')){
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

    mutationObserver(){
        const audioPlayerObserver = new MutationObserver(((mutationsList, observer) => {
            for (const mutation of mutationsList) {
                // added nodes.
                let addedNode = mutation.addedNodes[0];
                if (mutation.addedNodes.length > 0 && addedNode.nodeType === Node.ELEMENT_NODE) {
                    let audioTrack = addedNode.querySelector('[data-tonics-audioplayer-track]');
                    if (audioTrack && !audioTrack.dataset.hasOwnProperty('trackloaded')) {
                        audioTrack.dataset.trackloaded = 'false';
                        this.resetAudioPlayerSettings();
                        this.originalTracksInQueueBeforeShuffle = document.querySelector('.audio-player-queue').innerHTML;
                        this.resetQueue();
                        return;
                    }
                }

                // for attribute
                if (mutation.attributeName === "data-tonics-audioplayer-track"){
                    let audioTrack = mutation.target;
                    if (audioTrack && !audioTrack.dataset.hasOwnProperty('trackloaded')) {
                        audioTrack.dataset.trackloaded = 'false';
                        this.resetAudioPlayerSettings();
                        this.originalTracksInQueueBeforeShuffle = document.querySelector('.audio-player-queue').innerHTML;
                        this.resetQueue();
                    }
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
            document.addEventListener('click', (e) => {
                let el = e.target;
                // toggle play
                if (el.dataset.hasOwnProperty('audioplayer_play')) {

                    // play;
                    if(el.dataset.audioplayer_play === 'false') {
                        el.dataset.audioplayer_play = 'true'
                        // if it contains a url
                        if (el.dataset.hasOwnProperty('audioplayer_songurl')){
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
                    if (el.dataset.audioplayer_next === 'true') {
                        this.next();
                    }
                }

                // prev
                if (el.dataset.hasOwnProperty('audioplayer_prev')) {
                    if (el.dataset.audioplayer_prev === 'true') {
                        this.prev();
                    }
                }

                // repeat
                if (el.dataset.hasOwnProperty('audioplayer_repeat')){
                    if (el.dataset.audioplayer_repeat === 'true'){
                        self.repeatSong = false;
                        el.dataset.audioplayer_repeat = 'false';
                    } else {
                        self.repeatSong = true;
                        el.dataset.audioplayer_repeat = 'true';
                    }
                }

                // shuffle
                if (el.dataset.hasOwnProperty('audioplayer_shuffle')){
                    if (el.dataset.audioplayer_shuffle === 'true'){
                        el.dataset.audioplayer_shuffle = 'false';
                        if (document.querySelector('.audio-player-queue') && this.originalTracksInQueueBeforeShuffle){
                            document.querySelector('.audio-player-queue').innerHTML = this.originalTracksInQueueBeforeShuffle;
                            if (this.currentHowl !== null){
                                let src = self.currentHowl._src;
                                self.resetQueue();
                                // self.resetAudioPlayerSettings();
                                self.setSongUrlPlayAttribute(src[0], 'true', 'Pause');
                            }
                        }
                    } else {
                        el.dataset.audioplayer_shuffle = 'true';
                        let tracksInQueue = document.querySelectorAll('.track-in-queue');
                        if (tracksInQueue){
                            for (let i = tracksInQueue.length - 1; i > 0; i--) {
                                const j = Math.floor(Math.random() * (i + 1));
                                swapNodes(
                                    tracksInQueue[j],
                                    tracksInQueue[i],
                                    tracksInQueue[j].getBoundingClientRect(),  () => {
                                        self.resetQueue();
                                        // self.setCorrectPlaylistIndex();
                                        // self.resetAudioPlayerSettings();
                                    }
                                );
                            }
                        }
                    }
                }
            });

            document.addEventListener('pointerdown', self.sliderThumbMouseDown.bind(self));
            document.addEventListener('pointerup', self.sliderThumbMouseUp.bind(self));

            // volume
            document.addEventListener('input', self.volume.bind(self));
        }
    }

    bootPlaylistAndSongs(fromQueue = false) {

        let self = this,
            tonicsAudioPlayerTracks = document.querySelectorAll('[data-tonics-audioplayer-track]');

        if (fromQueue){
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
            for (let i = 0; i < tonicsAudioPlayerTracks.length; i++) {
                let el = tonicsAudioPlayerTracks[i],
                    key = i,
                    groupKey,
                    groupMap;

                el.dataset.trackloaded = 'true';
                // first get the track groupID, if not set, we set it to global group
                if (el.dataset.hasOwnProperty('audioplayer_groupid')) {
                    groupKey = el.dataset.audioplayer_groupid;
                } else {
                    groupKey = 'GLOBAL_GROUP';
                }

                // The song elements needs at-least the songurl to get added to a playlist
                if (el.dataset.hasOwnProperty('audioplayer_songurl')) {
                    groupMap = self.audioPlayerSettings.get(groupKey);
                    let songurl = el.dataset.audioplayer_songurl;
                    groupMap.set(songurl, {
                        'songID': key,
                        'songtitle': el.dataset.audioplayer_title,
                        'songimage': el.dataset.audioplayer_image,
                        'songurl': songurl,
                        'howl': null,
                        'format': (el.dataset.hasOwnProperty('audioplayer_format')) ? el.dataset.audioplayer_format : null,
                        'license': (el.dataset.hasOwnProperty('licenses')) ? JSON.parse(el.dataset.licenses) : null

                    });
                    groupKeyToMapKeyArray.push(songurl);
                    self.groupKeyToMapKey.set(groupKey, groupKeyToMapKeyArray);
                    self.audioPlayerSettings.set(groupKey, groupMap);
                }
            }
        }
    }

    resetAudioPlayerSettings(){
        let self = this
        this.audioPlayerSettings = new Map();
        this.audioPlayerSettings.set('GLOBAL_GROUP', new Map());
        this.groupKeyToMapKey  = new Map();
        this.bootPlaylistAndSongs();
        this.loadPlaylist();
        this.loadToQueue(this.audioPlayerSettings.get(this.currentGroupID));
        this.setCorrectPlaylistIndex();

        if (this.groupKeyToMapKey.size > 0){
            let audioPlayerEl = document.querySelector('.audio-player');
            if (audioPlayerEl && audioPlayerEl.classList.contains('d:none')){
                audioPlayerEl.classList.remove('d:none');
            }
        }
    }

    resetQueue(){
        this.audioPlayerSettings = new Map();
        this.audioPlayerSettings.set('GLOBAL_GROUP', new Map());
        this.groupKeyToMapKey  = new Map();
        this.bootPlaylistAndSongs(true);
        this.loadPlaylist();
        this.setCorrectPlaylistIndex();
    }

    loadToQueue(tracks){
        let queueContainer = document.querySelector('.audio-player-queue-list');
        if (queueContainer){
            queueContainer.innerHTML = "";
            tracks.forEach(value => {

                let licenses = [];
                licenses['icon'] = '';
                licenses['data'] = '';

                if (value.license !== null){
                    licenses['icon'] = `
                            <button class="dropdown-toggle bg:transparent border:none" aria-expanded="false" aria-label="Expand child menu" data-menutoggle_click_outside="true">
                                <svg class="icon:audio color:black tonics-widget cursor:pointer act-like-button">
                                    <use class="svgUse" xlink:href="#tonics-shopping-cart"></use>
                                </svg>
                            </button>`;

                    if (licenses.length > 0){
                        licenses.forEach((el => {
                            licenses['data'] += `
<li class="d:flex flex-d:column align-items:center">
        <span class="license-name">${el.name}</span>
        <span class="license-price">$${el.price}</span>
</li>`
                        }))
                    }
                }

                let playing;
                if (this.currentHowl !== null && this.currentHowl._src[0] === value.songurl){
                    playing = 'true'
                } else {
                    playing = "false"
                }

                queueContainer.insertAdjacentHTML('beforeend', `
<li tabindex="0" class="color:black cursor:move draggable track-in-queue bg:white-one border-width:default border:black position:relative">
                    <div class="queue-song-info d:flex align-items:center flex-gap:small">
                        <div title="${value.songtitle}" class="cursor:text text:no-wrap width:80px text-overflow:ellipsis">${value.songtitle}</div>
                        ${licenses['icon']}
                    </div>
                    
<button type="button" title="Play" data-tonics-audioplayer-track="" 
data-trackloaded
data-audioplayer_songurl="${value.songurl}" 
data-audioplayer_title="${value.songtitle}" 
data-audioplayer_image="${value.songimage}" 
data-audioplayer_format="${value.format}" 
data-audioplayer_play="${playing}" class="audioplayer-track border:none act-like-button icon:audio bg:transparent cursor:pointer color:black">
    <svg class="audio-play icon:audio tonics-widget pointer-events:none">
        <use class="svgUse" xlink:href="#tonics-audio-play"></use>
    </svg>
    <svg class="audio-pause icon:audio tonics-widget pointer-events:none">
        <use class="svgUse" xlink:href="#tonics-audio-pause"></use>
    </svg>
</button>

<ul class="cursor:pointer track-license d:none z-index:audio-sticky-footer:license-in-queue flex-d:column width:100% position:absolute flex-gap left:0 top:46px color:black bg:white-one border-width:default border:black">
    ${licenses['data']}
</ul>
<span class="width:100% height:100% z-index:hidden-over-draggable draggable-hidden-over"></span>
                </li>
`)
            })
        }
    }

    setCorrectPlaylistIndex(){
        let currentPlayingInQueue = document.querySelector('.audio-player-queue [data-audioplayer_play="true"]');
        if (currentPlayingInQueue){
            let songUrl = currentPlayingInQueue.dataset.audioplayer_songurl;
            let groupKey = 'GLOBAL_GROUP';
            if (currentPlayingInQueue.dataset.hasOwnProperty('audioplayer_groupid')){
                groupKey = currentPlayingInQueue.dataset.audioplayer_groupid;
            }
            if (this.groupKeyToMapKey.has(groupKey)){
                let songs = this.groupKeyToMapKey.get(groupKey);
                let newPlaylistIndex = songs.indexOf(songUrl);
                if (newPlaylistIndex !== -1){
                    this.playlistIndex = newPlaylistIndex;
                }
            }
        }
    }

    setSongUrlPlayAttribute(url, attrVal, title = null){
        let currentSongWithURL = document.querySelectorAll(`[data-audioplayer_songurl="${url}"]`),
            globalPlayBTN = document.querySelector('.global-play');

        if (currentSongWithURL.length > 0) {
            currentSongWithURL.forEach(value => {
                if (value.dataset.hasOwnProperty('audioplayer_play') && value !== globalPlayBTN) {
                    value.dataset.audioplayer_play = attrVal
                    if (title){
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
        if (audioPlayerGlobalContainer && audioPlayerGlobalContainer.dataset.hasOwnProperty('audioplayer_groupid')){
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
        let songKey = this.playlist[this.playlistIndex],
            groupSongs = this.audioPlayerSettings.get(this.currentGroupID);

        if (groupSongs.has(songKey)) {
            return groupSongs.get(songKey);
        }

        return false;
    }

    volume(e) {
        let el = e.target;
        // volume slider
        if (el.classList.contains('volume-slider')) {
            Howler.volume(el.value);
        }
    }

    sliderThumbMouseDown(e) {
        let el = e.target;
        let self = this;
        if (el.classList.contains('song-slider')) {
            self.userIsSeeking = true;
        }
    }

    sliderThumbMouseUp(e) {
        let el = e.target;
        let self = this;
        if (el.classList.contains('song-slider')) {
            self.userIsSeeking = false;
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
        if (this.currentHowl){
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
        this.updateGlobalSongProp(self.getSongData().songtitle, self.getSongData().songimage)
    }

    newHowlPlay() {
        let self = this,
            songData = self.getSongData();
        return new Howl({
            src: [songData.songurl],
            html5: true,
            // this causes the player not to play, a bug in HOWLER JS?
            // format: [songData.format],
            onplay: () => {
                // Start updating the progress of the track.
                requestAnimationFrame(self.step.bind(self));
            },
            onseek: () => {
                // Start updating the progress of the track.
                requestAnimationFrame(self.step.bind(self));
            },
            onend: () => {
                if (self.repeatSong){
                    self.pause();
                    self.play();
                } else {
                    self.next();
                }
            }
        });
    }

    prev() {
        let self = this;
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
        if (songData.playing()) {
            songData.seek(skipToDuration);
        }
    }

    step() {
        let self = this;
        let howl = self.getCurrentHowl();
        if (howl.playing()) {
            // Determine our current seek position.
            let seek = howl.seek() || 0;
            let progress = seek / howl.duration() * 100 || 0;
            progress = Math.round(progress);
            if (self.userIsSeeking === false) {
                self.songSlider.value = progress;
            }
            requestAnimationFrame(this.step.bind(self));
        }
    }

    updateGlobalSongProp(title = '', image = ''){
        let songTitle = document.querySelector('[data-audioplayer_globaltitle]'),
            songImage = document.querySelector('[data-audioplayer_globalart]');

        if (songTitle){
            songTitle.innerText = title;
            songTitle.title = title;
        }

        if (songImage){
            songImage.src = image;
        }

        if ('mediaSession' in navigator) {
            navigator.mediaSession.metadata = new MediaMetadata({
                title: title,
                artwork: [
                    {src: image, sizes: '100x100', type: 'image/png'},
                ]
            });
        }

    }

    getCurrentHowl() {
        return this.currentHowl;
    }
}