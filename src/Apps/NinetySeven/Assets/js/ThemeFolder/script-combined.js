var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });

// src/Util/Element/Abstract/ElementAbstract.ts
var ElementAbstract = class {
  constructor($Element) {
    if ($Element) {
      return this.query($Element);
    }
    return this;
  }
  query($classOrID) {
    let $temp = document.querySelector(`${$classOrID}`);
    if ($temp) {
      this.setQueryResult($temp);
      return this;
    }
    console.log(`Invalid class or id name - ${$classOrID}`);
  }
  setQueryResult($result) {
    this.$queryResult = $result;
    return this;
  }
  getQueryResult() {
    return this.$queryResult;
  }
};
__name(ElementAbstract, "ElementAbstract");

// src/Util/Others/Draggables.ts
var Draggables = class extends ElementAbstract {
  constructor($draggableContainer) {
    super($draggableContainer);
    this.dragging = null;
    this.droppedTarget = null;
    this._draggingOriginalRect = null;
    this.xPosition = 0;
    this.yPosition = -1;
    this.mouseActive = false;
    this._constrainedQuad = false;
    this.$draggableElementDetails = {};
  }
  get draggingOriginalRect() {
    return this._draggingOriginalRect;
  }
  set draggingOriginalRect(value) {
    this._draggingOriginalRect = value;
  }
  get constrainedQuad() {
    return this._constrainedQuad;
  }
  set constrainedQuad(value) {
    this._constrainedQuad = value;
  }
  settings($draggableElement, $elementsToIgnore, $constrainedQuad = false) {
    this.constrainedQuad = $constrainedQuad;
    this.getDraggableElementDetails().draggable = {
      constrainedQuad: $constrainedQuad,
      draggableElement: $draggableElement,
      ignoreElements: $elementsToIgnore,
      callbacks: {
        onDragging: null,
        onDragDrop: null,
        onDragRight: null,
        onDragLeft: null,
        onDragBottom: null,
        onDragTop: null
      }
    };
    return this;
  }
  getDraggableElementDetails() {
    return this.$draggableElementDetails;
  }
  checkIfSettingsIsSet() {
    return this.getDraggableElementDetails().draggable;
  }
  onDragDrop($onDragDrop) {
    if (this.checkIfSettingsIsSet()) {
      this.getDraggableElementDetails().draggable.callbacks.onDragDrop = $onDragDrop;
      return this;
    }
  }
  onDragRight($onDragRight) {
    if (this.checkIfSettingsIsSet()) {
      this.getDraggableElementDetails().draggable.callbacks.onDragRight = $onDragRight;
      return this;
    }
  }
  onDragLeft($onDragLeft) {
    if (this.checkIfSettingsIsSet()) {
      this.getDraggableElementDetails().draggable.callbacks.onDragLeft = $onDragLeft;
      return this;
    }
  }
  onDragBottom($onDragBottom) {
    if (this.checkIfSettingsIsSet()) {
      this.getDraggableElementDetails().draggable.callbacks.onDragBottom = $onDragBottom;
      return this;
    }
  }
  onDragTop($onDragTop) {
    if (this.checkIfSettingsIsSet()) {
      this.getDraggableElementDetails().draggable.callbacks.onDragTop = $onDragTop;
      return this;
    }
  }
  run() {
    let $draggableContainer = this.getQueryResult();
    let self = this;
    let shiftX;
    let shiftY;
    if ($draggableContainer) {
      $draggableContainer.addEventListener("pointerdown", function(e) {
        self.setMouseActive(true);
        let el = e.target;
        let startDrag = true;
        self.getDraggableElementDetails().draggable.ignoreElements.forEach((value, index) => {
          if (el.closest(value)) {
            startDrag = false;
          }
        });
        let draggableSelector = self.getDraggableElementDetails().draggable.draggableElement;
        if (el.closest(draggableSelector) && startDrag) {
          self == null ? void 0 : self.setDragging(el.closest(draggableSelector));
          let draggable = self.getDragging();
          shiftX = e.clientX;
          shiftY = e.clientY;
          draggable.classList.add("draggable-start");
          draggable.classList.add("touch-action:none");
          draggable.classList.remove("draggable-animation");
          self._draggingOriginalRect = draggable.getBoundingClientRect();
        }
      });
    }
    $draggableContainer.addEventListener("pointerup", function(e) {
      let el = e.target;
      if (self.isMouseActive()) {
        self.setMouseActive(false);
        let startDrag = true;
        self.getDraggableElementDetails().draggable.ignoreElements.forEach((value, index) => {
          if (el.closest(value)) {
            startDrag = false;
          }
        });
        self.setXPosition(0);
        self.setYPosition(-1);
        let draggable = self.getDragging();
        if (draggable && startDrag) {
          draggable.style["transform"] = "";
          draggable.classList.remove("draggable-start");
          draggable.classList.remove("touch-action:none");
          draggable.classList.add("draggable-animation");
        } else {
          return false;
        }
        let onDragDrop = self.getDraggableElementDetails().draggable.callbacks.onDragDrop;
        if (onDragDrop !== null && typeof onDragDrop == "function") {
          onDragDrop(el, self);
        }
      }
    });
    $draggableContainer.addEventListener("pointermove", function(e) {
      if (self.isMouseActive()) {
        let el = e.target, startDrag = true;
        self.getDraggableElementDetails().draggable.ignoreElements.forEach((value, index) => {
          if (el.closest(value)) {
            startDrag = false;
          }
        });
        let draggable = self.getDragging();
        let draggableSelector = self.getDraggableElementDetails().draggable.draggableElement;
        if (el.closest(draggableSelector) && startDrag && draggable) {
          draggable.classList.add("pointer-events:none");
          let elemBelow = document.elementFromPoint(e.clientX, e.clientY);
          self.setDroppedTarget(elemBelow.closest(draggableSelector));
          draggable.classList.remove("pointer-events:none");
          e.preventDefault();
          let tx = e.clientX - shiftX;
          let ty = e.clientY - shiftY;
          if (!self.constrainedQuad) {
            draggable.style.transform = "translate3d(" + tx + "px," + ty + "px, 0px)";
          }
          if (e.movementX >= 1 && e.movementY === 0) {
            if (self.constrainedQuad) {
              draggable.style.transform = "translate3d(" + tx + "px," + 0 + "px, 0px)";
            }
            let onDragRight = self.getDraggableElementDetails().draggable.callbacks.onDragRight;
            if (onDragRight !== null && typeof onDragRight == "function") {
              onDragRight(draggable);
            }
          }
          if (e.movementX < 0 && e.movementY === 0) {
            if (self.constrainedQuad) {
              draggable.style.transform = "translate3d(" + tx + "px," + 0 + "px, 0px)";
            }
            let onDragLeft = self.getDraggableElementDetails().draggable.callbacks.onDragLeft;
            if (onDragLeft !== null && typeof onDragLeft == "function") {
              onDragLeft(draggable, self);
            }
          }
          if (e.movementX === 0 && e.movementY > 0) {
            if (self.constrainedQuad) {
              draggable.style.transform = "translate3d(" + 0 + "px," + ty + "px, 0px)";
            }
            let onDragBottom = self.getDraggableElementDetails().draggable.callbacks.onDragBottom;
            if (onDragBottom !== null && typeof onDragBottom == "function") {
              onDragBottom(draggable, self);
            }
          } else if (e.movementX === 0 && e.movementY < 0) {
            if (self.constrainedQuad) {
              draggable.style.transform = "translate3d(" + 0 + "px," + ty + "px, 0px)";
            }
            let onDragTop = self.getDraggableElementDetails().draggable.callbacks.onDragTop;
            if (onDragTop !== null && typeof onDragTop == "function") {
              onDragTop(draggable, self);
            }
          }
        }
      }
    });
  }
  getXPosition() {
    return this.xPosition;
  }
  setXPosition(xPosition) {
    this.xPosition = xPosition;
  }
  getYPosition() {
    return this.yPosition;
  }
  setYPosition(yPosition) {
    this.yPosition = yPosition;
  }
  incrementXPosition() {
    return ++this.xPosition;
  }
  decrementXPosition() {
    return this.xPosition = this.xPosition - 1;
  }
  incrementYPosition() {
    return ++this.yPosition;
  }
  decrementYPosition() {
    return this.yPosition = this.xPosition - 1;
  }
  getDragging() {
    return this.dragging;
  }
  setDragging(draggedData) {
    this.dragging = draggedData;
  }
  getDroppedTarget() {
    return this.droppedTarget;
  }
  setDroppedTarget(el) {
    this.droppedTarget = el;
  }
  isMouseActive() {
    return this.mouseActive;
  }
  setMouseActive(result) {
    this.mouseActive = result;
  }
};
__name(Draggables, "Draggables");
if (!window.hasOwnProperty("TonicsScript")) {
  window.TonicsScript = {};
}
window.TonicsScript.Draggables = ($draggableContainer) => new Draggables($draggableContainer);
export {
  Draggables
};
var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });

// src/Util/Http/XHRApi.ts
var XHRApi = class {
  constructor(headers = {}) {
    this.$callbacks = {};
    this.http = new XMLHttpRequest();
    this.headers = headers;
    this.settings();
  }
  getCallbacks() {
    return this.$callbacks;
  }
  settings() {
    this.getCallbacks().callbacks = {
      onProgress: null
    };
  }
  checkIfCallbackIsSet() {
    if (!this.getCallbacks().callbacks) {
      throw new DOMException("No Callbacks exist");
    }
    return true;
  }
  onProgress($onProgress) {
    if (this.checkIfCallbackIsSet()) {
      this.getCallbacks().callbacks.onProgress = $onProgress;
      return this;
    }
  }
  Get(url, callBack) {
    this.getHttp().open("GET", url, true);
    this.setHeaders();
    this.getHttp().send();
    let self = this;
    this.getHttp().onreadystatechange = function() {
      try {
        if (self.http.readyState === XMLHttpRequest.DONE) {
          if (self.http.status === 200) {
            callBack(null, self.http.response);
          } else {
            callBack(self.http.response);
          }
        }
      } catch (e) {
        callBack("Something Went Wrong: " + e.description);
      }
    };
  }
  Post(url, data, callBack) {
    this.getHttp().open("POST", url, true);
    this.setHeaders();
    this.getHttp().send(data);
    let self = this;
    let onProgress = self.getCallbacks().callbacks.onProgress;
    if (onProgress !== null && typeof onProgress == "function") {
      this.getHttp().upload.addEventListener("progress", function(e) {
        onProgress(e);
      });
    }
    this.getHttp().onreadystatechange = function() {
      try {
        self.http.onload = function() {
          callBack(null, self.http.responseText);
        };
      } catch (e) {
        callBack("Something Went Wrong: " + e.description);
      }
    };
  }
  Put(url, data, callBack) {
    this.getHttp().open("PUT", url, true);
    this.setHeaders();
    this.getHttp().send(data);
    let self = this;
    let onProgress = self.getCallbacks().callbacks.onProgress;
    if (onProgress !== null && typeof onProgress == "function") {
      this.getHttp().upload.addEventListener("progress", function(e) {
        onProgress(e);
      });
    }
    try {
      this.http.onload = function() {
        if (self.http.status === 200) {
          callBack(null, self.http.response);
        } else {
          callBack(self.http.response);
        }
      };
    } catch (e) {
      callBack("Something Went Wrong: " + e.description);
    }
  }
  Delete(url, data = null, callBack) {
    this.http.open("DELETE", url, true);
    this.setHeaders();
    if (data) {
      this.http.send(data);
    } else {
      this.http.send();
    }
    let self = this;
    try {
      this.http.onload = function() {
        if (self.http.status === 200) {
          callBack(null, self.http.response);
        } else {
          callBack(self.http.response);
        }
      };
    } catch (e) {
      callBack("Something Went Wrong: " + e.description);
    }
  }
  getHeaders() {
    return this.headers;
  }
  setHeaders() {
    if (this.getHeaders()) {
      for (let key in this.getHeaders()) {
        this.getHttp().setRequestHeader(key, this.getHeaders()[key]);
      }
    }
  }
  getHttp() {
    return this.http;
  }
};
__name(XHRApi, "XHRApi");
if (!window.hasOwnProperty("TonicsScript")) {
  window["TonicsScript"] = {};
}
window["TonicsScript"].XHRApi = (headers = {}) => new XHRApi(headers);
export {
  XHRApi
};

export function swapNodes(el1, el2, el1InitialRect, onSwapDone = null) {
    let x1, y1, x2, y2;

    x1 = el1InitialRect.left - el2.getBoundingClientRect().left;
    y1 = el1InitialRect.top - el2.getBoundingClientRect().top;

    x2 = el2.getBoundingClientRect().left - el1InitialRect.left;
    y2 = el2.getBoundingClientRect().top - el1InitialRect.top;

    el1.classList.add('draggable-transition');
    el2.classList.add('draggable-transition');

    el2.style.transform = "translate(" + x1 + "px," + y1 + "px)";
    el1.style.transform = "translate(" + x2 + "px," + y2 + "px)";

    function swap(){
        el1.classList.remove('draggable-transition');
        el2.classList.remove('draggable-transition');

        el1.removeAttribute('style');
        el2.removeAttribute('style');

        let tempEl = document.createElement("div");
        el1.parentNode.insertBefore(tempEl, el1); el2.parentNode.insertBefore(el1, el2);
        tempEl.parentNode.insertBefore(el2, tempEl); tempEl.parentNode.removeChild(tempEl);

/*
        // THIS ONE KEEP LOSING SELECT DATA BUT THE TEMP VERSION ABOVE WORKS SUPERB
        let copyEl1 = el1.cloneNode(true);
        let copyEl2 = el2.cloneNode(true);
        el1.replaceWith(copyEl2);
        el2.replaceWith(copyEl1);*/
    }

    el2.addEventListener("transitionend", () => {
        swap();
        if (onSwapDone){
            onSwapDone();
        }
    }, { once: true });
}

if (!window.hasOwnProperty('TonicsScript')){ window.TonicsScript = {};}
window.TonicsScript.swapNodes = (el1, el2, el1InitialRect, onSwapDone = null) => swapNodes(el1, el2, el1InitialRect, onSwapDone);/*! howler.js v2.2.3 | (c) 2013-2020, James Simpson of GoldFire Studios | MIT License | howlerjs.com */
!function(){"use strict";var e=function(){this.init()};e.prototype={init:function(){var e=this||n;return e._counter=1e3,e._html5AudioPool=[],e.html5PoolSize=10,e._codecs={},e._howls=[],e._muted=!1,e._volume=1,e._canPlayEvent="canplaythrough",e._navigator="undefined"!=typeof window&&window.navigator?window.navigator:null,e.masterGain=null,e.noAudio=!1,e.usingWebAudio=!0,e.autoSuspend=!0,e.ctx=null,e.autoUnlock=!0,e._setup(),e},volume:function(e){var o=this||n;if(e=parseFloat(e),o.ctx||_(),void 0!==e&&e>=0&&e<=1){if(o._volume=e,o._muted)return o;o.usingWebAudio&&o.masterGain.gain.setValueAtTime(e,n.ctx.currentTime);for(var t=0;t<o._howls.length;t++)if(!o._howls[t]._webAudio)for(var r=o._howls[t]._getSoundIds(),a=0;a<r.length;a++){var u=o._howls[t]._soundById(r[a]);u&&u._node&&(u._node.volume=u._volume*e)}return o}return o._volume},mute:function(e){var o=this||n;o.ctx||_(),o._muted=e,o.usingWebAudio&&o.masterGain.gain.setValueAtTime(e?0:o._volume,n.ctx.currentTime);for(var t=0;t<o._howls.length;t++)if(!o._howls[t]._webAudio)for(var r=o._howls[t]._getSoundIds(),a=0;a<r.length;a++){var u=o._howls[t]._soundById(r[a]);u&&u._node&&(u._node.muted=!!e||u._muted)}return o},stop:function(){for(var e=this||n,o=0;o<e._howls.length;o++)e._howls[o].stop();return e},unload:function(){for(var e=this||n,o=e._howls.length-1;o>=0;o--)e._howls[o].unload();return e.usingWebAudio&&e.ctx&&void 0!==e.ctx.close&&(e.ctx.close(),e.ctx=null,_()),e},codecs:function(e){return(this||n)._codecs[e.replace(/^x-/,"")]},_setup:function(){var e=this||n;if(e.state=e.ctx?e.ctx.state||"suspended":"suspended",e._autoSuspend(),!e.usingWebAudio)if("undefined"!=typeof Audio)try{var o=new Audio;void 0===o.oncanplaythrough&&(e._canPlayEvent="canplay")}catch(n){e.noAudio=!0}else e.noAudio=!0;try{var o=new Audio;o.muted&&(e.noAudio=!0)}catch(e){}return e.noAudio||e._setupCodecs(),e},_setupCodecs:function(){var e=this||n,o=null;try{o="undefined"!=typeof Audio?new Audio:null}catch(n){return e}if(!o||"function"!=typeof o.canPlayType)return e;var t=o.canPlayType("audio/mpeg;").replace(/^no$/,""),r=e._navigator?e._navigator.userAgent:"",a=r.match(/OPR\/([0-6].)/g),u=a&&parseInt(a[0].split("/")[1],10)<33,d=-1!==r.indexOf("Safari")&&-1===r.indexOf("Chrome"),i=r.match(/Version\/(.*?) /),_=d&&i&&parseInt(i[1],10)<15;return e._codecs={mp3:!(u||!t&&!o.canPlayType("audio/mp3;").replace(/^no$/,"")),mpeg:!!t,opus:!!o.canPlayType('audio/ogg; codecs="opus"').replace(/^no$/,""),ogg:!!o.canPlayType('audio/ogg; codecs="vorbis"').replace(/^no$/,""),oga:!!o.canPlayType('audio/ogg; codecs="vorbis"').replace(/^no$/,""),wav:!!(o.canPlayType('audio/wav; codecs="1"')||o.canPlayType("audio/wav")).replace(/^no$/,""),aac:!!o.canPlayType("audio/aac;").replace(/^no$/,""),caf:!!o.canPlayType("audio/x-caf;").replace(/^no$/,""),m4a:!!(o.canPlayType("audio/x-m4a;")||o.canPlayType("audio/m4a;")||o.canPlayType("audio/aac;")).replace(/^no$/,""),m4b:!!(o.canPlayType("audio/x-m4b;")||o.canPlayType("audio/m4b;")||o.canPlayType("audio/aac;")).replace(/^no$/,""),mp4:!!(o.canPlayType("audio/x-mp4;")||o.canPlayType("audio/mp4;")||o.canPlayType("audio/aac;")).replace(/^no$/,""),weba:!(_||!o.canPlayType('audio/webm; codecs="vorbis"').replace(/^no$/,"")),webm:!(_||!o.canPlayType('audio/webm; codecs="vorbis"').replace(/^no$/,"")),dolby:!!o.canPlayType('audio/mp4; codecs="ec-3"').replace(/^no$/,""),flac:!!(o.canPlayType("audio/x-flac;")||o.canPlayType("audio/flac;")).replace(/^no$/,"")},e},_unlockAudio:function(){var e=this||n;if(!e._audioUnlocked&&e.ctx){e._audioUnlocked=!1,e.autoUnlock=!1,e._mobileUnloaded||44100===e.ctx.sampleRate||(e._mobileUnloaded=!0,e.unload()),e._scratchBuffer=e.ctx.createBuffer(1,1,22050);var o=function(n){for(;e._html5AudioPool.length<e.html5PoolSize;)try{var t=new Audio;t._unlocked=!0,e._releaseHtml5Audio(t)}catch(n){e.noAudio=!0;break}for(var r=0;r<e._howls.length;r++)if(!e._howls[r]._webAudio)for(var a=e._howls[r]._getSoundIds(),u=0;u<a.length;u++){var d=e._howls[r]._soundById(a[u]);d&&d._node&&!d._node._unlocked&&(d._node._unlocked=!0,d._node.load())}e._autoResume();var i=e.ctx.createBufferSource();i.buffer=e._scratchBuffer,i.connect(e.ctx.destination),void 0===i.start?i.noteOn(0):i.start(0),"function"==typeof e.ctx.resume&&e.ctx.resume(),i.onended=function(){i.disconnect(0),e._audioUnlocked=!0,document.removeEventListener("touchstart",o,!0),document.removeEventListener("touchend",o,!0),document.removeEventListener("click",o,!0),document.removeEventListener("keydown",o,!0);for(var n=0;n<e._howls.length;n++)e._howls[n]._emit("unlock")}};return document.addEventListener("touchstart",o,!0),document.addEventListener("touchend",o,!0),document.addEventListener("click",o,!0),document.addEventListener("keydown",o,!0),e}},_obtainHtml5Audio:function(){var e=this||n;if(e._html5AudioPool.length)return e._html5AudioPool.pop();var o=(new Audio).play();return o&&"undefined"!=typeof Promise&&(o instanceof Promise||"function"==typeof o.then)&&o.catch(function(){console.warn("HTML5 Audio pool exhausted, returning potentially locked audio object.")}),new Audio},_releaseHtml5Audio:function(e){var o=this||n;return e._unlocked&&o._html5AudioPool.push(e),o},_autoSuspend:function(){var e=this;if(e.autoSuspend&&e.ctx&&void 0!==e.ctx.suspend&&n.usingWebAudio){for(var o=0;o<e._howls.length;o++)if(e._howls[o]._webAudio)for(var t=0;t<e._howls[o]._sounds.length;t++)if(!e._howls[o]._sounds[t]._paused)return e;return e._suspendTimer&&clearTimeout(e._suspendTimer),e._suspendTimer=setTimeout(function(){if(e.autoSuspend){e._suspendTimer=null,e.state="suspending";var n=function(){e.state="suspended",e._resumeAfterSuspend&&(delete e._resumeAfterSuspend,e._autoResume())};e.ctx.suspend().then(n,n)}},3e4),e}},_autoResume:function(){var e=this;if(e.ctx&&void 0!==e.ctx.resume&&n.usingWebAudio)return"running"===e.state&&"interrupted"!==e.ctx.state&&e._suspendTimer?(clearTimeout(e._suspendTimer),e._suspendTimer=null):"suspended"===e.state||"running"===e.state&&"interrupted"===e.ctx.state?(e.ctx.resume().then(function(){e.state="running";for(var n=0;n<e._howls.length;n++)e._howls[n]._emit("resume")}),e._suspendTimer&&(clearTimeout(e._suspendTimer),e._suspendTimer=null)):"suspending"===e.state&&(e._resumeAfterSuspend=!0),e}};var n=new e,o=function(e){var n=this;if(!e.src||0===e.src.length)return void console.error("An array of source files must be passed with any new Howl.");n.init(e)};o.prototype={init:function(e){var o=this;return n.ctx||_(),o._autoplay=e.autoplay||!1,o._format="string"!=typeof e.format?e.format:[e.format],o._html5=e.html5||!1,o._muted=e.mute||!1,o._loop=e.loop||!1,o._pool=e.pool||5,o._preload="boolean"!=typeof e.preload&&"metadata"!==e.preload||e.preload,o._rate=e.rate||1,o._sprite=e.sprite||{},o._src="string"!=typeof e.src?e.src:[e.src],o._volume=void 0!==e.volume?e.volume:1,o._xhr={method:e.xhr&&e.xhr.method?e.xhr.method:"GET",headers:e.xhr&&e.xhr.headers?e.xhr.headers:null,withCredentials:!(!e.xhr||!e.xhr.withCredentials)&&e.xhr.withCredentials},o._duration=0,o._state="unloaded",o._sounds=[],o._endTimers={},o._queue=[],o._playLock=!1,o._onend=e.onend?[{fn:e.onend}]:[],o._onfade=e.onfade?[{fn:e.onfade}]:[],o._onload=e.onload?[{fn:e.onload}]:[],o._onloaderror=e.onloaderror?[{fn:e.onloaderror}]:[],o._onplayerror=e.onplayerror?[{fn:e.onplayerror}]:[],o._onpause=e.onpause?[{fn:e.onpause}]:[],o._onplay=e.onplay?[{fn:e.onplay}]:[],o._onstop=e.onstop?[{fn:e.onstop}]:[],o._onmute=e.onmute?[{fn:e.onmute}]:[],o._onvolume=e.onvolume?[{fn:e.onvolume}]:[],o._onrate=e.onrate?[{fn:e.onrate}]:[],o._onseek=e.onseek?[{fn:e.onseek}]:[],o._onunlock=e.onunlock?[{fn:e.onunlock}]:[],o._onresume=[],o._webAudio=n.usingWebAudio&&!o._html5,void 0!==n.ctx&&n.ctx&&n.autoUnlock&&n._unlockAudio(),n._howls.push(o),o._autoplay&&o._queue.push({event:"play",action:function(){o.play()}}),o._preload&&"none"!==o._preload&&o.load(),o},load:function(){var e=this,o=null;if(n.noAudio)return void e._emit("loaderror",null,"No audio support.");"string"==typeof e._src&&(e._src=[e._src]);for(var r=0;r<e._src.length;r++){var u,d;if(e._format&&e._format[r])u=e._format[r];else{if("string"!=typeof(d=e._src[r])){e._emit("loaderror",null,"Non-string found in selected audio sources - ignoring.");continue}u=/^data:audio\/([^;,]+);/i.exec(d),u||(u=/\.([^.]+)$/.exec(d.split("?",1)[0])),u&&(u=u[1].toLowerCase())}if(u||console.warn('No file extension was found. Consider using the "format" property or specify an extension.'),u&&n.codecs(u)){o=e._src[r];break}}return o?(e._src=o,e._state="loading","https:"===window.location.protocol&&"http:"===o.slice(0,5)&&(e._html5=!0,e._webAudio=!1),new t(e),e._webAudio&&a(e),e):void e._emit("loaderror",null,"No codec support for selected audio sources.")},play:function(e,o){var t=this,r=null;if("number"==typeof e)r=e,e=null;else{if("string"==typeof e&&"loaded"===t._state&&!t._sprite[e])return null;if(void 0===e&&(e="__default",!t._playLock)){for(var a=0,u=0;u<t._sounds.length;u++)t._sounds[u]._paused&&!t._sounds[u]._ended&&(a++,r=t._sounds[u]._id);1===a?e=null:r=null}}var d=r?t._soundById(r):t._inactiveSound();if(!d)return null;if(r&&!e&&(e=d._sprite||"__default"),"loaded"!==t._state){d._sprite=e,d._ended=!1;var i=d._id;return t._queue.push({event:"play",action:function(){t.play(i)}}),i}if(r&&!d._paused)return o||t._loadQueue("play"),d._id;t._webAudio&&n._autoResume();var _=Math.max(0,d._seek>0?d._seek:t._sprite[e][0]/1e3),s=Math.max(0,(t._sprite[e][0]+t._sprite[e][1])/1e3-_),l=1e3*s/Math.abs(d._rate),c=t._sprite[e][0]/1e3,f=(t._sprite[e][0]+t._sprite[e][1])/1e3;d._sprite=e,d._ended=!1;var p=function(){d._paused=!1,d._seek=_,d._start=c,d._stop=f,d._loop=!(!d._loop&&!t._sprite[e][2])};if(_>=f)return void t._ended(d);var m=d._node;if(t._webAudio){var v=function(){t._playLock=!1,p(),t._refreshBuffer(d);var e=d._muted||t._muted?0:d._volume;m.gain.setValueAtTime(e,n.ctx.currentTime),d._playStart=n.ctx.currentTime,void 0===m.bufferSource.start?d._loop?m.bufferSource.noteGrainOn(0,_,86400):m.bufferSource.noteGrainOn(0,_,s):d._loop?m.bufferSource.start(0,_,86400):m.bufferSource.start(0,_,s),l!==1/0&&(t._endTimers[d._id]=setTimeout(t._ended.bind(t,d),l)),o||setTimeout(function(){t._emit("play",d._id),t._loadQueue()},0)};"running"===n.state&&"interrupted"!==n.ctx.state?v():(t._playLock=!0,t.once("resume",v),t._clearTimer(d._id))}else{var h=function(){m.currentTime=_,m.muted=d._muted||t._muted||n._muted||m.muted,m.volume=d._volume*n.volume(),m.playbackRate=d._rate;try{var r=m.play();if(r&&"undefined"!=typeof Promise&&(r instanceof Promise||"function"==typeof r.then)?(t._playLock=!0,p(),r.then(function(){t._playLock=!1,m._unlocked=!0,o?t._loadQueue():t._emit("play",d._id)}).catch(function(){t._playLock=!1,t._emit("playerror",d._id,"Playback was unable to start. This is most commonly an issue on mobile devices and Chrome where playback was not within a user interaction."),d._ended=!0,d._paused=!0})):o||(t._playLock=!1,p(),t._emit("play",d._id)),m.playbackRate=d._rate,m.paused)return void t._emit("playerror",d._id,"Playback was unable to start. This is most commonly an issue on mobile devices and Chrome where playback was not within a user interaction.");"__default"!==e||d._loop?t._endTimers[d._id]=setTimeout(t._ended.bind(t,d),l):(t._endTimers[d._id]=function(){t._ended(d),m.removeEventListener("ended",t._endTimers[d._id],!1)},m.addEventListener("ended",t._endTimers[d._id],!1))}catch(e){t._emit("playerror",d._id,e)}};"data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA"===m.src&&(m.src=t._src,m.load());var y=window&&window.ejecta||!m.readyState&&n._navigator.isCocoonJS;if(m.readyState>=3||y)h();else{t._playLock=!0,t._state="loading";var g=function(){t._state="loaded",h(),m.removeEventListener(n._canPlayEvent,g,!1)};m.addEventListener(n._canPlayEvent,g,!1),t._clearTimer(d._id)}}return d._id},pause:function(e){var n=this;if("loaded"!==n._state||n._playLock)return n._queue.push({event:"pause",action:function(){n.pause(e)}}),n;for(var o=n._getSoundIds(e),t=0;t<o.length;t++){n._clearTimer(o[t]);var r=n._soundById(o[t]);if(r&&!r._paused&&(r._seek=n.seek(o[t]),r._rateSeek=0,r._paused=!0,n._stopFade(o[t]),r._node))if(n._webAudio){if(!r._node.bufferSource)continue;void 0===r._node.bufferSource.stop?r._node.bufferSource.noteOff(0):r._node.bufferSource.stop(0),n._cleanBuffer(r._node)}else isNaN(r._node.duration)&&r._node.duration!==1/0||r._node.pause();arguments[1]||n._emit("pause",r?r._id:null)}return n},stop:function(e,n){var o=this;if("loaded"!==o._state||o._playLock)return o._queue.push({event:"stop",action:function(){o.stop(e)}}),o;for(var t=o._getSoundIds(e),r=0;r<t.length;r++){o._clearTimer(t[r]);var a=o._soundById(t[r]);a&&(a._seek=a._start||0,a._rateSeek=0,a._paused=!0,a._ended=!0,o._stopFade(t[r]),a._node&&(o._webAudio?a._node.bufferSource&&(void 0===a._node.bufferSource.stop?a._node.bufferSource.noteOff(0):a._node.bufferSource.stop(0),o._cleanBuffer(a._node)):isNaN(a._node.duration)&&a._node.duration!==1/0||(a._node.currentTime=a._start||0,a._node.pause(),a._node.duration===1/0&&o._clearSound(a._node))),n||o._emit("stop",a._id))}return o},mute:function(e,o){var t=this;if("loaded"!==t._state||t._playLock)return t._queue.push({event:"mute",action:function(){t.mute(e,o)}}),t;if(void 0===o){if("boolean"!=typeof e)return t._muted;t._muted=e}for(var r=t._getSoundIds(o),a=0;a<r.length;a++){var u=t._soundById(r[a]);u&&(u._muted=e,u._interval&&t._stopFade(u._id),t._webAudio&&u._node?u._node.gain.setValueAtTime(e?0:u._volume,n.ctx.currentTime):u._node&&(u._node.muted=!!n._muted||e),t._emit("mute",u._id))}return t},volume:function(){var e,o,t=this,r=arguments;if(0===r.length)return t._volume;if(1===r.length||2===r.length&&void 0===r[1]){t._getSoundIds().indexOf(r[0])>=0?o=parseInt(r[0],10):e=parseFloat(r[0])}else r.length>=2&&(e=parseFloat(r[0]),o=parseInt(r[1],10));var a;if(!(void 0!==e&&e>=0&&e<=1))return a=o?t._soundById(o):t._sounds[0],a?a._volume:0;if("loaded"!==t._state||t._playLock)return t._queue.push({event:"volume",action:function(){t.volume.apply(t,r)}}),t;void 0===o&&(t._volume=e),o=t._getSoundIds(o);for(var u=0;u<o.length;u++)(a=t._soundById(o[u]))&&(a._volume=e,r[2]||t._stopFade(o[u]),t._webAudio&&a._node&&!a._muted?a._node.gain.setValueAtTime(e,n.ctx.currentTime):a._node&&!a._muted&&(a._node.volume=e*n.volume()),t._emit("volume",a._id));return t},fade:function(e,o,t,r){var a=this;if("loaded"!==a._state||a._playLock)return a._queue.push({event:"fade",action:function(){a.fade(e,o,t,r)}}),a;e=Math.min(Math.max(0,parseFloat(e)),1),o=Math.min(Math.max(0,parseFloat(o)),1),t=parseFloat(t),a.volume(e,r);for(var u=a._getSoundIds(r),d=0;d<u.length;d++){var i=a._soundById(u[d]);if(i){if(r||a._stopFade(u[d]),a._webAudio&&!i._muted){var _=n.ctx.currentTime,s=_+t/1e3;i._volume=e,i._node.gain.setValueAtTime(e,_),i._node.gain.linearRampToValueAtTime(o,s)}a._startFadeInterval(i,e,o,t,u[d],void 0===r)}}return a},_startFadeInterval:function(e,n,o,t,r,a){var u=this,d=n,i=o-n,_=Math.abs(i/.01),s=Math.max(4,_>0?t/_:t),l=Date.now();e._fadeTo=o,e._interval=setInterval(function(){var r=(Date.now()-l)/t;l=Date.now(),d+=i*r,d=Math.round(100*d)/100,d=i<0?Math.max(o,d):Math.min(o,d),u._webAudio?e._volume=d:u.volume(d,e._id,!0),a&&(u._volume=d),(o<n&&d<=o||o>n&&d>=o)&&(clearInterval(e._interval),e._interval=null,e._fadeTo=null,u.volume(o,e._id),u._emit("fade",e._id))},s)},_stopFade:function(e){var o=this,t=o._soundById(e);return t&&t._interval&&(o._webAudio&&t._node.gain.cancelScheduledValues(n.ctx.currentTime),clearInterval(t._interval),t._interval=null,o.volume(t._fadeTo,e),t._fadeTo=null,o._emit("fade",e)),o},loop:function(){var e,n,o,t=this,r=arguments;if(0===r.length)return t._loop;if(1===r.length){if("boolean"!=typeof r[0])return!!(o=t._soundById(parseInt(r[0],10)))&&o._loop;e=r[0],t._loop=e}else 2===r.length&&(e=r[0],n=parseInt(r[1],10));for(var a=t._getSoundIds(n),u=0;u<a.length;u++)(o=t._soundById(a[u]))&&(o._loop=e,t._webAudio&&o._node&&o._node.bufferSource&&(o._node.bufferSource.loop=e,e&&(o._node.bufferSource.loopStart=o._start||0,o._node.bufferSource.loopEnd=o._stop,t.playing(a[u])&&(t.pause(a[u],!0),t.play(a[u],!0)))));return t},rate:function(){var e,o,t=this,r=arguments;if(0===r.length)o=t._sounds[0]._id;else if(1===r.length){var a=t._getSoundIds(),u=a.indexOf(r[0]);u>=0?o=parseInt(r[0],10):e=parseFloat(r[0])}else 2===r.length&&(e=parseFloat(r[0]),o=parseInt(r[1],10));var d;if("number"!=typeof e)return d=t._soundById(o),d?d._rate:t._rate;if("loaded"!==t._state||t._playLock)return t._queue.push({event:"rate",action:function(){t.rate.apply(t,r)}}),t;void 0===o&&(t._rate=e),o=t._getSoundIds(o);for(var i=0;i<o.length;i++)if(d=t._soundById(o[i])){t.playing(o[i])&&(d._rateSeek=t.seek(o[i]),d._playStart=t._webAudio?n.ctx.currentTime:d._playStart),d._rate=e,t._webAudio&&d._node&&d._node.bufferSource?d._node.bufferSource.playbackRate.setValueAtTime(e,n.ctx.currentTime):d._node&&(d._node.playbackRate=e);var _=t.seek(o[i]),s=(t._sprite[d._sprite][0]+t._sprite[d._sprite][1])/1e3-_,l=1e3*s/Math.abs(d._rate);!t._endTimers[o[i]]&&d._paused||(t._clearTimer(o[i]),t._endTimers[o[i]]=setTimeout(t._ended.bind(t,d),l)),t._emit("rate",d._id)}return t},seek:function(){var e,o,t=this,r=arguments;if(0===r.length)t._sounds.length&&(o=t._sounds[0]._id);else if(1===r.length){var a=t._getSoundIds(),u=a.indexOf(r[0]);u>=0?o=parseInt(r[0],10):t._sounds.length&&(o=t._sounds[0]._id,e=parseFloat(r[0]))}else 2===r.length&&(e=parseFloat(r[0]),o=parseInt(r[1],10));if(void 0===o)return 0;if("number"==typeof e&&("loaded"!==t._state||t._playLock))return t._queue.push({event:"seek",action:function(){t.seek.apply(t,r)}}),t;var d=t._soundById(o);if(d){if(!("number"==typeof e&&e>=0)){if(t._webAudio){var i=t.playing(o)?n.ctx.currentTime-d._playStart:0,_=d._rateSeek?d._rateSeek-d._seek:0;return d._seek+(_+i*Math.abs(d._rate))}return d._node.currentTime}var s=t.playing(o);s&&t.pause(o,!0),d._seek=e,d._ended=!1,t._clearTimer(o),t._webAudio||!d._node||isNaN(d._node.duration)||(d._node.currentTime=e);var l=function(){s&&t.play(o,!0),t._emit("seek",o)};if(s&&!t._webAudio){var c=function(){t._playLock?setTimeout(c,0):l()};setTimeout(c,0)}else l()}return t},playing:function(e){var n=this;if("number"==typeof e){var o=n._soundById(e);return!!o&&!o._paused}for(var t=0;t<n._sounds.length;t++)if(!n._sounds[t]._paused)return!0;return!1},duration:function(e){var n=this,o=n._duration,t=n._soundById(e);return t&&(o=n._sprite[t._sprite][1]/1e3),o},state:function(){return this._state},unload:function(){for(var e=this,o=e._sounds,t=0;t<o.length;t++)o[t]._paused||e.stop(o[t]._id),e._webAudio||(e._clearSound(o[t]._node),o[t]._node.removeEventListener("error",o[t]._errorFn,!1),o[t]._node.removeEventListener(n._canPlayEvent,o[t]._loadFn,!1),o[t]._node.removeEventListener("ended",o[t]._endFn,!1),n._releaseHtml5Audio(o[t]._node)),delete o[t]._node,e._clearTimer(o[t]._id);var a=n._howls.indexOf(e);a>=0&&n._howls.splice(a,1);var u=!0;for(t=0;t<n._howls.length;t++)if(n._howls[t]._src===e._src||e._src.indexOf(n._howls[t]._src)>=0){u=!1;break}return r&&u&&delete r[e._src],n.noAudio=!1,e._state="unloaded",e._sounds=[],e=null,null},on:function(e,n,o,t){var r=this,a=r["_on"+e];return"function"==typeof n&&a.push(t?{id:o,fn:n,once:t}:{id:o,fn:n}),r},off:function(e,n,o){var t=this,r=t["_on"+e],a=0;if("number"==typeof n&&(o=n,n=null),n||o)for(a=0;a<r.length;a++){var u=o===r[a].id;if(n===r[a].fn&&u||!n&&u){r.splice(a,1);break}}else if(e)t["_on"+e]=[];else{var d=Object.keys(t);for(a=0;a<d.length;a++)0===d[a].indexOf("_on")&&Array.isArray(t[d[a]])&&(t[d[a]]=[])}return t},once:function(e,n,o){var t=this;return t.on(e,n,o,1),t},_emit:function(e,n,o){for(var t=this,r=t["_on"+e],a=r.length-1;a>=0;a--)r[a].id&&r[a].id!==n&&"load"!==e||(setTimeout(function(e){e.call(this,n,o)}.bind(t,r[a].fn),0),r[a].once&&t.off(e,r[a].fn,r[a].id));return t._loadQueue(e),t},_loadQueue:function(e){var n=this;if(n._queue.length>0){var o=n._queue[0];o.event===e&&(n._queue.shift(),n._loadQueue()),e||o.action()}return n},_ended:function(e){var o=this,t=e._sprite;if(!o._webAudio&&e._node&&!e._node.paused&&!e._node.ended&&e._node.currentTime<e._stop)return setTimeout(o._ended.bind(o,e),100),o;var r=!(!e._loop&&!o._sprite[t][2]);if(o._emit("end",e._id),!o._webAudio&&r&&o.stop(e._id,!0).play(e._id),o._webAudio&&r){o._emit("play",e._id),e._seek=e._start||0,e._rateSeek=0,e._playStart=n.ctx.currentTime;var a=1e3*(e._stop-e._start)/Math.abs(e._rate);o._endTimers[e._id]=setTimeout(o._ended.bind(o,e),a)}return o._webAudio&&!r&&(e._paused=!0,e._ended=!0,e._seek=e._start||0,e._rateSeek=0,o._clearTimer(e._id),o._cleanBuffer(e._node),n._autoSuspend()),o._webAudio||r||o.stop(e._id,!0),o},_clearTimer:function(e){var n=this;if(n._endTimers[e]){if("function"!=typeof n._endTimers[e])clearTimeout(n._endTimers[e]);else{var o=n._soundById(e);o&&o._node&&o._node.removeEventListener("ended",n._endTimers[e],!1)}delete n._endTimers[e]}return n},_soundById:function(e){for(var n=this,o=0;o<n._sounds.length;o++)if(e===n._sounds[o]._id)return n._sounds[o];return null},_inactiveSound:function(){var e=this;e._drain();for(var n=0;n<e._sounds.length;n++)if(e._sounds[n]._ended)return e._sounds[n].resetTrackGroups();return new t(e)},_drain:function(){var e=this,n=e._pool,o=0,t=0;if(!(e._sounds.length<n)){for(t=0; t<e._sounds.length; t++)e._sounds[t]._ended&&o++;for(t=e._sounds.length-1; t>=0; t--){if(o<=n)return;e._sounds[t]._ended&&(e._webAudio&&e._sounds[t]._node&&e._sounds[t]._node.disconnect(0),e._sounds.splice(t,1),o--)}}},_getSoundIds:function(e){var n=this;if(void 0===e){for(var o=[],t=0; t<n._sounds.length; t++)o.push(n._sounds[t]._id);return o}return[e]},_refreshBuffer:function(e){var o=this;return e._node.bufferSource=n.ctx.createBufferSource(),e._node.bufferSource.buffer=r[o._src],e._panner?e._node.bufferSource.connect(e._panner):e._node.bufferSource.connect(e._node),e._node.bufferSource.loop=e._loop,e._loop&&(e._node.bufferSource.loopStart=e._start||0,e._node.bufferSource.loopEnd=e._stop||0),e._node.bufferSource.playbackRate.setValueAtTime(e._rate,n.ctx.currentTime),o},_cleanBuffer:function(e){var o=this,t=n._navigator&&n._navigator.vendor.indexOf("Apple")>=0;if(n._scratchBuffer&&e.bufferSource&&(e.bufferSource.onended=null,e.bufferSource.disconnect(0),t))try{e.bufferSource.buffer=n._scratchBuffer}catch(e){}return e.bufferSource=null,o},_clearSound:function(e){/MSIE |Trident\//.test(n._navigator&&n._navigator.userAgent)||(e.src="data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA")}};var t=function(e){this._parent=e,this.init()};t.prototype={init:function(){var e=this,o=e._parent;return e._muted=o._muted,e._loop=o._loop,e._volume=o._volume,e._rate=o._rate,e._seek=0,e._paused=!0,e._ended=!0,e._sprite="__default",e._id=++n._counter,o._sounds.push(e),e.create(),e},create:function(){var e=this,o=e._parent,t=n._muted||e._muted||e._parent._muted?0:e._volume;return o._webAudio?(e._node=void 0===n.ctx.createGain?n.ctx.createGainNode():n.ctx.createGain(),e._node.gain.setValueAtTime(t,n.ctx.currentTime),e._node.paused=!0,e._node.connect(n.masterGain)):n.noAudio||(e._node=n._obtainHtml5Audio(),e._errorFn=e._errorListener.bind(e),e._node.addEventListener("error",e._errorFn,!1),e._loadFn=e._loadListener.bind(e),e._node.addEventListener(n._canPlayEvent,e._loadFn,!1),e._endFn=e._endListener.bind(e),e._node.addEventListener("ended",e._endFn,!1),e._node.src=o._src,e._node.preload=!0===o._preload?"auto":o._preload,e._node.volume=t*n.volume(),e._node.load()),e},reset:function(){var e=this,o=e._parent;return e._muted=o._muted,e._loop=o._loop,e._volume=o._volume,e._rate=o._rate,e._seek=0,e._rateSeek=0,e._paused=!0,e._ended=!0,e._sprite="__default",e._id=++n._counter,e},_errorListener:function(){var e=this;e._parent._emit("loaderror",e._id,e._node.error?e._node.error.code:0),e._node.removeEventListener("error",e._errorFn,!1)},_loadListener:function(){var e=this,o=e._parent;o._duration=Math.ceil(10*e._node.duration)/10,0===Object.keys(o._sprite).length&&(o._sprite={__default:[0,1e3*o._duration]}),"loaded"!==o._state&&(o._state="loaded",o._emit("load"),o._loadQueue()),e._node.removeEventListener(n._canPlayEvent,e._loadFn,!1)},_endListener:function(){var e=this,n=e._parent;n._duration===1/0&&(n._duration=Math.ceil(10*e._node.duration)/10,n._sprite.__default[1]===1/0&&(n._sprite.__default[1]=1e3*n._duration),n._ended(e)),e._node.removeEventListener("ended",e._endFn,!1)}};var r={},a=function(e){var n=e._src;if(r[n])return e._duration=r[n].duration,void i(e);if(/^data:[^;]+;base64,/.test(n)){for(var o=atob(n.split(",")[1]),t=new Uint8Array(o.length),a=0; a<o.length; ++a)t[a]=o.charCodeAt(a);d(t.buffer,e)}else{var _=new XMLHttpRequest;_.open(e._xhr.method,n,!0),_.withCredentials=e._xhr.withCredentials,_.responseType="arraybuffer",e._xhr.headers&&Object.keys(e._xhr.headers).forEach(function(n){_.setRequestHeader(n,e._xhr.headers[n])}),_.onload=function(){var n=(_.status+"")[0];if("0"!==n&&"2"!==n&&"3"!==n)return void e._emit("loaderror",null,"Failed loading audio file with status: "+_.status+".");d(_.response,e)},_.onerror=function(){e._webAudio&&(e._html5=!0,e._webAudio=!1,e._sounds=[],delete r[n],e.load())},u(_)}},u=function(e){try{e.send()}catch(n){e.onerror()}},d=function(e, o){var t=function(){o._emit("loaderror",null,"Decoding audio data failed.")},a=function(e){e&&o._sounds.length>0?(r[o._src]=e,i(o,e)):t()};"undefined"!=typeof Promise&&1===n.ctx.decodeAudioData.length?n.ctx.decodeAudioData(e).then(a).catch(t):n.ctx.decodeAudioData(e,a,t)},i=function(e, n){n&&!e._duration&&(e._duration=n.duration),0===Object.keys(e._sprite).length&&(e._sprite={__default:[0,1e3*e._duration]}),"loaded"!==e._state&&(e._state="loaded",e._emit("load"),e._loadQueue())},_=function(){if(n.usingWebAudio){try{"undefined"!=typeof AudioContext?n.ctx=new AudioContext:"undefined"!=typeof webkitAudioContext?n.ctx=new webkitAudioContext:n.usingWebAudio=!1}catch(e){n.usingWebAudio=!1}n.ctx||(n.usingWebAudio=!1);var e=/iP(hone|od|ad)/.test(n._navigator&&n._navigator.platform),o=n._navigator&&n._navigator.appVersion.match(/OS (\d+)_(\d+)_?(\d+)?/),t=o?parseInt(o[1],10):null;if(e&&t&&t<9){var r=/safari/.test(n._navigator&&n._navigator.userAgent.toLowerCase());n._navigator&&!r&&(n.usingWebAudio=!1)}n.usingWebAudio&&(n.masterGain=void 0===n.ctx.createGain?n.ctx.createGainNode():n.ctx.createGain(),n.masterGain.gain.setValueAtTime(n._muted?0:n._volume,n.ctx.currentTime),n.masterGain.connect(n.ctx.destination)),n._setup()}};"function"==typeof define&&define.amd&&define([],function(){return{Howler:n,Howl:o}}),"undefined"!=typeof exports&&(exports.Howler=n,exports.Howl=o),"undefined"!=typeof global?(global.HowlerGlobal=e,global.Howler=n,global.Howl=o,global.Sound=t):"undefined"!=typeof window&&(window.HowlerGlobal=e,window.Howler=n,window.Howl=o,window.Sound=t)}();export class AudioPlayer {

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

/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

class SimpleState {

    constructor() {
        this.returnState = "";
        this.currentState = "";
        this.debug = false;
        this.errorCode = 0;
        this.errorMessage = "";
        this.sucessMessage = "";
        this.stateResult = "";
    }

    static DONE = 'DONE';
    static NEXT = 'NEXT';
    static ERROR = 'ERROR';

    runStates(returnErrorPage = true) {
        while (this.stateResult = this.dispatchState(this.currentState)) {
            if (this.stateResult === SimpleState.NEXT) {
                continue;
            }
            if (this.stateResult === SimpleState.DONE) {
                break;
            }
            if (this.stateResult === SimpleState.ERROR) {
                if (returnErrorPage) {
                    this.displayErrorMessage(this.errorCode, this.errorMessage);
                }
                break;
            }
        }
    }

    dispatchState(state) {
        return state();
    }

    displayErrorMessage(errorCode, errorMessage) {
        console.log(`Error: ${errorMessage} with code ${errorCode}`)
    }


    /**
     * Switch to a new state and pass the arguments to setCurrentState
     *
     * @param {function} state - The function representing the new state of the state machine.
     * @param {*} [stateResult = null] - The optional state result to be returned.
     * @param {...*} args - The arguments to be passed to the `currentState` function.
     * @returns {Object | *} - The current object or the state result.
     *
     * @example
     * // passing multiple arguments
     * object.switchState(object.StateName, arg1, arg2);
     *
     * @example
     * // passing an object with the arguments
     * object.switchState(object.StateName,{arg1:value1,arg2:value2});
     */
    switchState(state, stateResult = null, ...args) {
        this.setCurrentState(state, ...args);
        if (this.debug) {
            console.log(`State Switched To ${state}`);
        }

        if (stateResult !== null) {
            return stateResult;
        }
        return this;
    }

    getCurrentState() {
        return this.currentState;
    }

    /**
     * Set the current state of the state machine.
     *
     * @param {function} currentState - The function representing the new state of the state machine.
     * @param {...*} [args] - The arguments to be passed to the `currentState` function.
     * @returns {Object} - The current object, allowing for method chaining.
     *
     * @example
     * // passing multiple arguments
     * object.setCurrentState(object.StateName, arg1, arg2);
     *
     * @example
     * // passing an object with the arguments
     * object.setCurrentState(object.StateName,{arg1:value1,arg2:value2});
     */
    setCurrentState(currentState, ...args) {
        if (args.length === 0) {
            this.currentState = currentState.bind(this);
        } else {
            this.currentState = currentState.bind(this, args);
        }
        return this;
    }

    getReturnState() {
        return this.returnState;
    }

    setReturnState(returnState) {
        this.returnState = returnState;
        return this;
    }

    isDebug() {
        return this.debug;
    }

    setDebug(debug) {
        this.debug = debug;
    }

    getErrorCode() {
        return this.errorCode;
    }

    setErrorCode(errorCode) {
        this.errorCode = errorCode;
        return this;
    }

    getErrorMessage() {
        return this.errorMessage;
    }

    setErrorMessage(errorMessage) {
        this.errorMessage = errorMessage;
        return this;
    }

    getStateResult() {
        return this.stateResult;
    }

    setStateResult(stateResult) {
        this.stateResult = stateResult;
    }

    getSuccessMessage() {
        return this.sucessMessage;
    }

    setSuccessMessage(successMessage) {
        this.sucessMessage = successMessage;
    }
}


export class TrackCart extends SimpleState {

    static cartStorageKey = 'Tonics_Cart_Key_Audio_Store';
    shakeCartButtonAnimation = true;
    cartStorageData = new Map();
    licenseData = null;
    cartItemToRemove = null;

    constructor() {
        super();
    }

    getCartStorageKey()
    {
        return TrackCart.cartStorageKey;
    }

    InitialState() {
        let cart = this.getCart();
        cart.set(this.licenseData.slug_id, this.licenseData);
        this.cartStorageData = cart;
        return this.switchState(this.AddCartToLocalStorageState, SimpleState.NEXT);
    }

    AddCartToLocalStorageState() {
        localStorage.setItem(TrackCart.cartStorageKey, JSON.stringify(Array.from(this.cartStorageData)));
        return this.switchState(this.UpdateCartLicenseInfo, SimpleState.NEXT);
    }

    UpdateCartLicenseInfo() {
        let cartHeader = document.querySelector('.tonics-cart-items-container');
        if (cartHeader){
            const cart = this.getCart();
            // Remove All Cart Items
            let cartItems = document.querySelectorAll(`.cart-item[data-slug_id]`);
            if (cartItems){
                cartItems.forEach((cartItem) => {
                    cartItem.remove();
                });
            }
            for (let [key, value] of cart.entries()) {
                cartHeader.insertAdjacentHTML('beforeend', this.getLicenseFrag(value));
            }
        }

        return this.switchState(this.UpdateCartBasketNumberState, SimpleState.NEXT);
    }

    UpdateCartBasketNumberState() {
        let cartCounter = document.querySelector('.cb-counter-label');
        if (cartCounter){
            cartCounter.innerHTML = `${this.getCart().size}`;
            if (this.shakeCartButtonAnimation){
                this.shakeCartButton();
            }
        }
        return this.switchState(this.TotalItemsPriceInCartState, SimpleState.NEXT);
    }

    RemoveItemFromCartState() {
        if (this.cartItemToRemove){
            let slug_id = this.cartItemToRemove?.dataset?.slug_id;
            let cart = this.getCart();
            if (cart.has(slug_id)){
                this.cartItemToRemove.remove();
                cart.delete(slug_id);
                localStorage.setItem(TrackCart.cartStorageKey, JSON.stringify(Array.from(cart)));
            }
        }

        return this.switchState(this.UpdateCartLicenseInfo, SimpleState.NEXT);
    }

    TotalItemsPriceInCartState() {
        let tonicsCheckoutPrice = document.querySelector('.tonics-checkout-price');

       if (tonicsCheckoutPrice){
           let currency = 'USD', locale = 'en-US';
           let totalPrice = this.getTotalItemPrice();

           // Format it in USD
           // Create our CURRENCY Formatter, thanks to Intl.NumberFormat.
           // Usage is formatter.format(2500); /* $2,500.00 */
           const formatter = new Intl.NumberFormat(locale, {
               style: 'currency',
               currency: currency,
           });
           totalPrice = formatter.format(totalPrice);
           tonicsCheckoutPrice.innerHTML = `${totalPrice}`;
       }

       return SimpleState.DONE;
    }

    ReloadCartFromLocalStorageState() {
        return this.switchState(this.UpdateCartLicenseInfo, SimpleState.NEXT);
    }

    // That is if a cart is added to the cart menu, we change the cart icon to remove icon
    // this way, a user can remove the cart icon
    UpdateCartIconAdditionToTheCartMenuState(args) {
        if(args.length > 0){
            let trackDownloadContainer = args[0];
            let trackSlugID = trackDownloadContainer.closest('[data-slug_id]')?.dataset.slug_id;
            let licenses = trackDownloadContainer.querySelectorAll('[data-unique_id]');
            let cart = this.getCart();
            if(licenses.length > 0){
                licenses.forEach((license) => {
                    // By Default, we remove the remove icon even if we would later add it when the unique_id matches
                     this.removeIconDeleteButton(license);

                    for (let [key, value] of cart.entries()) {

                        if (trackSlugID !== key){
                           continue;
                        }

                        let licenseUniqueID = license.dataset?.unique_id;
                        let cartStorageUniqueID = value?.unique_id;
                        if ((licenseUniqueID && cartStorageUniqueID) && (licenseUniqueID === cartStorageUniqueID)){
                            let buttonTitle = license.title;
                            let svgElement = license.querySelector('svg');
                            let useElement = license.querySelector('use');

                            if (svgElement && useElement){
                                license.dataset.remove_from_cart = 'true';
                                license.title = 'Remove From Cart'
                                svgElement.dataset.prev_button_title = buttonTitle;
                                svgElement.classList.add('color:red')
                                useElement.setAttribute("xlink:href", "#tonics-remove");
                            }
                            break;
                        }
                    }
                });
            }
            return SimpleState.DONE;
        }

    }

    RemoveItemFromCartWithUniqueID(args) {
        if(args.length > 0){
            let licenseButton = args[0];
            let licenseUniqueID = licenseButton.dataset?.unique_id;
            let trackSlugID = licenseButton.closest('[data-slug_id]')?.dataset.slug_id;
            let cart = this.getCart();

            for (let [key, value] of cart.entries()) {

                if (trackSlugID !== key){
                    continue;
                }

                let cartStorageUniqueID = value?.unique_id;
                if ((licenseUniqueID && cartStorageUniqueID) && (licenseUniqueID === cartStorageUniqueID)){
                    this.removeIconDeleteButton(licenseButton);
                    cart.delete(key);
                    localStorage.setItem(TrackCart.cartStorageKey, JSON.stringify(Array.from(cart)));
                    break;
                }
            }
        }

        return this.switchState(this.UpdateCartLicenseInfo, SimpleState.NEXT);
    }

    removeIconDeleteButton(licenseButton){
        let svgElement = licenseButton.querySelector('svg');
        let useElement = licenseButton.querySelector('use')

        if (!licenseButton.dataset.hasOwnProperty('indie_license_type_is_free') && (svgElement && useElement)){
            licenseButton.removeAttribute("data-remove_from_cart");
            licenseButton.title = svgElement?.dataset?.prev_button_title ?? licenseButton.title;
            svgElement.removeAttribute("data-prev_button_title");
            svgElement.classList.remove('color:red')
            useElement.setAttribute("xlink:href", "#tonics-cart");
        }
    }

    getCart() {
        if (localStorage.getItem(TrackCart.cartStorageKey) !== null) {
            let storedMap = localStorage.getItem(TrackCart.cartStorageKey);
            this.cartStorageData = new Map(JSON.parse(storedMap));
        }
        return this.cartStorageData;
    }

    getCheckOutEmail() {
        return document.querySelector('.checkout-email-tonics');
    }

    addCheckoutEmailInvalid() {
        let emailInput = document.querySelector('.checkout-email-tonics');
        let checkoutEmailContainer = document.querySelector('.checkout-email-error-container');
        let checkoutEmailErrorMessageSpanEl = document.querySelector('.checkout-email-error');

        if (checkoutEmailContainer){
            checkoutEmailContainer.classList.remove('d:none');
        }

        if (emailInput){
            emailInput.setAttribute('aria-invalid', 'true');
            emailInput.setAttribute('aria-describedby', checkoutEmailErrorMessageSpanEl.id);
        }

        if (checkoutEmailErrorMessageSpanEl){
            checkoutEmailErrorMessageSpanEl.setAttribute('aria-live', 'assertive');
        }
    }

    removeCheckoutEmailInvalid() {
        let emailInput = document.querySelector('.checkout-email-tonics');
        let checkoutEmailContainer = document.querySelector('.checkout-email-error-container');
        let checkoutEmailErrorMessageSpanEl = document.querySelector('.checkout-email-error');

        if (checkoutEmailContainer){
            checkoutEmailContainer.classList.add('d:none');
        }

        if (emailInput){
            emailInput.setAttribute('aria-invalid', 'false');
            emailInput.removeAttribute('aria-describedby');
        }

        if (checkoutEmailErrorMessageSpanEl){
            checkoutEmailErrorMessageSpanEl.removeAttribute('aria-live');
        }
    }

    getTotalItemPrice() {
        let price = 0;
        for (let [key, value] of this.getCart().entries()) {
            price = price + (parseFloat(value.price));
        }
        return price;
    }

    getLicenseFrag(data) {
        let currency = '$';
        return `            
            <div data-slug_id="${data.slug_id}" class="cart-item d:flex flex-d:row flex-wrap:wrap padding:2rem-1rem align-items:center flex-gap">
                <img data-audioplayer_globalart src="${data.track_image}" class="image:avatar" 
                alt="${data.track_title}">
                <div class="cart-detail">
                    <a data-tonics_navigate data-url_page="${data.url_page}" href="${data.url_page}"><span class="text cart-title color:black">${data.track_title}</span></a> 
                    <span class="text cart-license-price">${data.name}
                <span> → (${currency}${data.price})</span>
            </span>
                    <button data-slug_id="${data.slug_id}" class="tonics-remove-cart-item background:transparent border:none color:black bg:white-one border-width:default border:black padding:small cursor:pointer button:box-shadow-variant-1">
                        <span class="text text:no-wrap">Remove</span>
                    </button>
                </div>
            </div>`;
    }

    shakeCartButton(){
        let cartButton = document.querySelector('.cart-button');
        if (cartButton){
            cartButton.classList.add("jello-diagonal-1"); // Add Animation to cart button
            setTimeout(function () { // Remove Animation After 1 sec
                cartButton.classList.remove("jello-diagonal-1");
            }, 1000);
        }

    }
}



/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

try {
    // For Filter Options
    window.TonicsScript.MenuToggle('.main-tonics-folder-container', window.TonicsScript.Query())
        .settings('.form-and-filter', '.filter-button-toggle', '.filter-container')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(false)
        .stopPropagation(false)
        .run();

    // For More Filter Options
    window.TonicsScript.MenuToggle('.main-tonics-folder-container', window.TonicsScript.Query())
        .settings('.form-and-filter', '.more-filter-button-toggle', '.more-filter-container')
        .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(false)
        .stopPropagation(false)
        .run();

    // Filter For Download or Buy
    window.TonicsScript.MenuToggle('.main-tonics-folder-container', window.TonicsScript.Query())
        .settings('.tonics-file', '.audioplayer-track-download-buy-button', '.track-download-buy-container')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(false)
        .stopPropagation(false)
        .run();

    // For Read More Container
    window.TonicsScript.MenuToggle('.main-tonics-folder-container', window.TonicsScript.Query())
        .settings('.tonics-folder-about-container', '.read-more-button', '.tonics-track-content')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd"])
        .menuIsOn(["swing-in-top-fwd"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(false)
        .stopPropagation(false)
        .run();

    // For Cart Toggle
    window.TonicsScript.MenuToggle('.tonics-cart-container', window.TonicsScript.Query())
        .settings('.cart-button-counter', '.cart-button', '.cart-child-container')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(true)
        .stopPropagation(true)
        .propagateElements(['[data-tonics_navigate]'])
        .run();

} catch (e) {
    console.error("An Error Occur Setting MenuToggle: Track Audio Page")
}


const selectElementsForm = document.querySelector("form");
if (selectElementsForm) {
    selectElementsForm.addEventListener("submit", function (event) {
        const inputElements = this.querySelectorAll("input, select");
        inputElements.forEach(inputElement => {
            if (inputElement.value === "") {
                inputElement.removeAttribute("name");
            }
        });
    });
}

function initRouting(containerSelector, navigateCallback = null) {
    const container = document.querySelector(containerSelector);

    function callCallback(options) {
        if (navigateCallback) {
            navigateCallback(options);
        }
    }

    function navigate(url) {
        callCallback({url, type: 'before'});
        // Push a new history entry with the url
        window.history.pushState({'url': url}, '', url);
        callCallback({url, type: 'after'});
    }

    window.onload = () => {
        // Perform initialization or setup
        // without the below, the popstate won't fire if user uses the back button for the first time

        // Get the query string from the URL
        const UrlPlusQueryString = window.location.pathname + window.location.search;
        // Replace the current state of the browser history with the current URL, including the query string
        window.history.replaceState({url: UrlPlusQueryString}, '', UrlPlusQueryString);
    };

    // Bind a popstate event listener to enable the back button
    window.addEventListener('popstate', (event) => {
        if (event.state) {
            let url = event.state.url;
            callCallback({url, type: 'popstate'});
            // we only navigate in a pop state if the url is not the same, without doing this, the forward button won't work
            // because there won't be anywhere to navigate to

            // Check if the URL is a relative URL
            if (!url.startsWith('http')) {
                // Convert the relative URL to an absolute URL using the new URL constructor
                url = new URL(url, window.location.href).href;
            }

            // Parse the URL using the URL interface
            const parsedUrl = new URL(url);
            // Compare the pathname and search properties of the parsed URL to the window.location object
            if (window.location.pathname !== parsedUrl.pathname || window.location.search !== parsedUrl.search) {
                navigate(url);
            }
        }
    })

    // Bind a click event listener to the container using event delegation
    container.addEventListener('click', e => {
        const el = e.target;
        if (el.closest('[data-tonics_navigate]')) {
            e.preventDefault();
            let element = el.closest('[data-tonics_navigate]');
            let url = element.getAttribute('data-url_page');
            const loading = element.querySelector('.svg-per-file-loading');
            if (loading) {
                loading.classList.remove('d:none');
            }
            navigate(url);
        }

        if (el.closest('.tonics-submit-button') && el.closest('.form-and-filter')) {
            e.preventDefault();
            const form = el.closest('.form-and-filter');
            // Get the form data
            const formData = new FormData(form);
            // Construct the query string using the URLSearchParams interface
            const params = new URLSearchParams();
            for (const [key, value] of formData) {
                // Trim the value before adding it to the query string
                if (value.trim()) {
                    const trimmedValue = value.trim();
                    params.append(key, trimmedValue);
                }
            }
            const queryString = params.toString();
            // if queryString is not empty
            if (queryString) {
                // Append the query string to the URL
                const newUrl = window.location.pathname + '?' + queryString;
                navigate(newUrl);
            }
        }

    });
}

// Initialize the routing for the tonics-file-container element
initRouting('body', ({url, type}) => {
    if (type === 'after' || type === 'popstate') {
        window.TonicsScript.XHRApi({isAPI: true, type: 'isTonicsNavigation'}).Get(url, function (err, data) {
            if (data) {
                data = JSON.parse(data);
                if (data.data?.isFolder) {
                    tonicsAudioNavForFolder(data, url);
                }
                if (data.data?.isTrack) {
                    tonicsAudioNavForTrack(data, url);
                }
            }
        });
    }
});

function tonicsAudioNavForFolder(data, url) {
    let tonicsFolderMain = document.querySelector('.tonics-folder-main'),
        beforeFolderSearchLoading = document.querySelector('.before-folder-search'),
        tonicsFolderAboutContainer = document.querySelector('.tonics-folder-about-container'),
        tonicsFolderSearch = document.querySelector('.tonics-folder-search');

    if (tonicsFolderAboutContainer){
        tonicsFolderAboutContainer.remove();
    }

    if (tonicsFolderMain && data.data?.fragment) {
        tonicsFolderMain.innerHTML = data?.data.fragment;
        document.title = data?.data.title;
    }

    if (tonicsFolderSearch) {
        tonicsFolderSearch.remove();
    }

    if (beforeFolderSearchLoading) {
        beforeFolderSearchLoading.classList.remove('d:none');
        window.TonicsScript.XHRApi({isAPI: true, type: 'isSearch'}).Get(url, function (err, data) {
            data = JSON.parse(data);
            beforeFolderSearchLoading.classList.add('d:none');
            beforeFolderSearchLoading.insertAdjacentHTML('beforebegin', data?.data);
        });
    }
}

function tonicsAudioNavForTrack(data, url) {
    let tonicsFolderMain = document.querySelector('.tonics-folder-main'),
        tonicsFolderAboutContainer = document.querySelector('.tonics-folder-about-container'),
        tonicsFolderSearch = document.querySelector('.tonics-folder-search');
    if (tonicsFolderMain && data.data?.fragment) {
        tonicsFolderMain.innerHTML = data?.data.fragment;
        document.title = data?.data.title;
        if (tonicsFolderSearch) {
            tonicsFolderSearch.remove();
            tonicsFolderAboutContainer.remove();
        }
    }
}

const tonicsCartSectionContainer = document.querySelector('.tonics-cart-container');
if (tonicsCartSectionContainer) {
    tonicsCartSectionContainer.addEventListener('click', (e) => {
        let el = e.target;
        if (el.closest('.tonics-remove-cart-item')) {
            let trackCart = new TrackCart();
            trackCart.cartItemToRemove = el.closest('.cart-item[data-slug_id]');
            trackCart.setCurrentState(trackCart.RemoveItemFromCartState);
            trackCart.runStates();
        }

        const cartButtonCounterEl = el.closest('.cart-button-counter');
        if (cartButtonCounterEl && !cartButtonCounterEl.dataset.hasOwnProperty('tonics_loaded_payment_gateway')) {
            cartButtonCounterEl.dataset.tonics_loaded_payment_gateway = ' true';
            // Fire Payment Gateways
            let OnAudioPlayerPaymentGatewayCollator = new OnAudioPlayerPaymentGatewayCollatorEvent();
            window.TonicsEvent.EventDispatcher.dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnAudioPlayerPaymentGatewayCollator, OnAudioPlayerPaymentGatewayCollatorEvent);
        }

    });
}

// Reload TonicsCart Data From LocalStorage
let trackCart = new TrackCart();
trackCart.setCurrentState(trackCart.ReloadCartFromLocalStorageState);
trackCart.runStates();

//----------------
//--- HANDLERS
//----------------

class TonicsAudioPlayHandler {
    constructor(event) {
        const songData = event._songData;
        const url_page = songData?.url_page;
        const url_page_el = document.querySelector(`button[data-url_page="${url_page}"]`);
        if (url_page_el.closest('[data-tonics-audioplayer-track]') && !songData.hasOwnProperty('markers')) {
            window.TonicsScript.XHRApi({isAPI: true, type: 'getMarker'}).Get(url_page, function (err, data) {
                data = JSON.parse(data);
                if (data?.data?.markers) {
                    songData.markers = data.data.markers;
                    event._songData = songData;
                    if (songData._self && songData?.markers.length > 0) {
                        songData.markers.forEach((marker) => {
                            if (marker.track_marker_start) {
                                const markerPercentageAndSec = songData._self.getMarkerPercentageAndSeconds(marker.track_marker_start, songData.howl.duration());
                                markerPercentageAndSec.text = marker.track_marker_name;
                                marker._track_marker_start_info = markerPercentageAndSec;
                            }

                            if (marker.track_marker_end) {
                                const markerPercentageAndSec = songData._self.getMarkerPercentageAndSeconds(marker.track_marker_end, songData.howl.duration());
                                markerPercentageAndSec.text = marker.track_marker_name;
                                marker._track_marker_end_info = markerPercentageAndSec;
                            }
                        });
                        songData._self.handleMarkerUpdating();
                    }
                }
            });
        }
    }
}

class TonicsAudioPlayerClickHandler {
    constructor(event) {
        let trackCart = new TrackCart();
        const el = event._eventEl;
        let trackDownloadContainer = el.closest('.tonics-file')?.querySelector('.track-download-ul-container');
        let self = this;
        // download_buy_container
        if (el.dataset.hasOwnProperty('download_buy_button') && el.dataset.hasOwnProperty('licenses')) {
            let licenses = el.dataset.licenses;

            if (trackDownloadContainer) {
                if (trackDownloadContainer.dataset.license_loaded === 'false') {
                    trackDownloadContainer.dataset.license_loaded = 'true';
                    licenses = JSON.parse(licenses);
                    licenses.forEach((license) => {
                        trackDownloadContainer.insertAdjacentHTML('beforeend', this.trackDownloadList(license))
                    });
                }

                if (trackDownloadContainer.dataset.license_loaded === 'true') {
                    trackCart.setCurrentState(trackCart.UpdateCartIconAdditionToTheCartMenuState, trackDownloadContainer);
                    trackCart.runStates();
                }
            }
        }

        if (el.dataset.hasOwnProperty('remove_from_cart')) {
            trackCart.setCurrentState(trackCart.RemoveItemFromCartWithUniqueID, el);
            trackCart.runStates();
            return;
        }

        if (el.dataset.hasOwnProperty('indie_license')) {
            if (el.dataset.hasOwnProperty('indie_license_type_is_free')) {
                let trackItem = el.closest('[data-url_page]'),
                    urlPage = trackItem?.dataset?.url_page,
                    slugID = trackItem?.dataset?.slug_id;

                let dataSet = JSON.stringify({urlPage, slugID, dataset: el.dataset.indie_license});

                window.TonicsScript.XHRApi({
                    isAPI: true,
                    type: 'freeTrackDownload',
                    freeTrackData: dataSet
                }).Get(urlPage, function (err, data) {
                    if (data) {
                        data = JSON.parse(data);
                        if (data?.data?.artifact) {
                            // Issue a download link
                            self.openDownloadLink(data.data.artifact);
                        }
                    }
                });
            } else {
                let trackItem = el.closest('[data-slug_id]');
                let trackSlugID = trackItem?.dataset?.slug_id;
                let trackURLPage = trackItem?.dataset?.url_page;
                let trackTitle = trackItem?.dataset?.audioplayer_title;
                let trackImage = trackItem?.dataset?.audioplayer_image;
                let indieLicense = JSON.parse(el.dataset.indie_license);
                if (trackSlugID) {
                    indieLicense.slug_id = trackSlugID;
                    indieLicense.track_title = trackTitle;
                    indieLicense.track_image = trackImage;
                    indieLicense.url_page = trackURLPage;
                    trackCart.licenseData = indieLicense;
                    trackCart.setCurrentState(trackCart.InitialState);
                    trackCart.runStates();

                    // Add Remove Button
                    trackCart.setCurrentState(trackCart.UpdateCartIconAdditionToTheCartMenuState, trackDownloadContainer);
                    trackCart.runStates();
                }
            }
        }
    }

    openDownloadLink(link) {
        window.open(link, "_blank");
    }

    trackDownloadList(data) {
        let price = parseInt(data.price),
            name = data.name,
            currency = '$',
            uniqueID = data.unique_id;
        let encodeData = JSON.stringify(data);

        if (data?.is_enabled === '1') {
            if (price > 0) {
                return `
<li class="download-li">
    <span class="text cart-license-price">${name}<span> (${currency}${price}) → </span></span>
    <button type="button" title="Add (${name} License) To Cart" data-unique_id="${uniqueID}" data-indie_license='${encodeData}' class="audioplayer-track border:none act-like-button icon:audio bg:transparent cursor:pointer color:white">
                <svg class="icon:audio tonics-cart-icon tonics-widget pointer-events:none"><use class="svgUse" xlink:href="#tonics-cart"></use>
     </button>
</li>`;
            } else {
                return `
<li class="download-li">
    <span class="text cart-license-price">${name}<span> (Free) → </span></span>
    <button type="button" title="Download ${name}" data-unique_id="${uniqueID}" data-indie_license_type_is_free="true" 
    data-indie_license='${encodeData}' class="audioplayer-track border:none act-like-button icon:audio bg:transparent cursor:pointer color:white">
                <svg class="icon:audio tonics-cart-icon tonics-widget pointer-events:none"><use class="svgUse" xlink:href="#tonics-download"></use>
     </button>
</li>`;
            }
        }

        return '';
    }

}


//----------------------
//--- PAYMENT EVENTS
//---------------------

class TonicsPaymentEventAbstract {

    getPaymentName() {
    }

    getPaymentButton() {

    }

    bootPayment() {

    }
}

class OnAudioPlayerPaymentGatewayCollatorEvent {

    get_request_flow_address = "/tracks_payment/get_request_flow";
    post_request_flow_address = "/tracks_payment/post_request_flow";

    checkout_button_div_el = document.querySelector('.checkout-payment-gateways-buttons');

    addPaymentButton(string) {
        if (this.checkout_button_div_el) {
            let loadingAnimation = this.checkout_button_div_el.querySelector('.loading-button-payment-gateway');
            if (loadingAnimation && !loadingAnimation.classList.contains('d:none')) {
                loadingAnimation.classList.add('d:none');
            }
            this.checkout_button_div_el.insertAdjacentHTML('beforeend', string)
        }
    }

    generateInvoiceID(PaymentHandlerName, onSuccess = null, onError = null) {
        window.TonicsScript.XHRApi({
            PaymentHandlerName: PaymentHandlerName,
            PaymentQueryType: "GenerateInvoiceID"
        }).Get(this.get_request_flow_address, function (err, data) {
            if (data) {
                data = JSON.parse(data);
                if (onSuccess) {
                    onSuccess(data);
                }
            }

            if (err) {
                onError()
            }
        });
    }

    getClientCredentials(PaymentHandlerName, onSuccess = null, onError = null) {
        window.TonicsScript.XHRApi({
            PaymentHandlerName: PaymentHandlerName,
            PaymentQueryType: "ClientPaymentCredentials"
        }).Get(this.get_request_flow_address, function (err, data) {
            if (data) {
                data = JSON.parse(data);
                if (onSuccess) {
                    onSuccess(data);
                }
            }

            if (err) {
                onError()
            }
        });
    }

    sendBody(PaymentHandlerName, BodyData, onSuccess = null, onError = null) {
        window.TonicsScript.XHRApi({
            PaymentHandlerName: PaymentHandlerName,
            PaymentQueryType: "CapturedPaymentDetails",
            'Tonics-CSRF-Token': `${this.getCSRFFromInput(['tonics_csrf_token', 'csrf_token', 'token'])}`
        }).Post(this.post_request_flow_address, JSON.stringify(BodyData), function (err, data) {
            if (data) {
                data = JSON.parse(data);
                if (onSuccess) {
                    onSuccess(data);
                }
            } else {
                if (onError) {
                    onError();
                }
            }
            if (err) {
                onError()
            }
        });
    }

    /**
     * Load External or Internal Script Asynchronously
     * @param $scriptPath
     * e.g /js/script/tonics.js
     * @param $uniqueIdentifier
     * e.g tonics, this is useful for preventing the script from loading twice
     */
    loadScriptDynamically($scriptPath, $uniqueIdentifier) {
        return new Promise((resolve, reject) => {
            let scriptCheck = document.querySelector(`[data-script_id="${$uniqueIdentifier}"]`);
            // if script has previously been loaded, resolve
            if (scriptCheck) {
                resolve();
                // else...load script
            } else {
                const script = document.createElement('script');
                script.dataset.script_id = $uniqueIdentifier;
                document.body.appendChild(script);
                script.onload = resolve;
                script.onerror = reject;
                script.async = true;
                script.src = $scriptPath;
            }
        });
    }

    getCSRFFromInput(csrfNames) {

        let csrf = null;
        csrfNames.forEach(((value, index) => {
            let inputCSRF = document.querySelector(`input[name=${value}]`)?.value;
            if (!inputCSRF){
                inputCSRF = document.querySelector(`meta[name=${value}]`)?.content;
            }
            if (!csrf && inputCSRF){
                csrf = inputCSRF;
            }
        }))
        return csrf;
    }
}

//----------------------
//--- PAYMENT HANDLERS
//---------------------

class TonicsPayPalGateway extends TonicsPaymentEventAbstract {
    invoice_id = null;

    constructor(event) {
        super();
        this.bootPayment(event);
    }

    getPaymentName() {
        return "AudioTonicsPayPalHandler";
    }

    getPaymentButton() {
        return `
        <div id="smart-button-container">
            <div style="text-align: center;">
                <div id="paypal-button-container"></div>
            </div>
        </div>
        `;
    }

    bootPayment(event = null) {
        let self = this;
        if (event) {
            event.getClientCredentials(self.getPaymentName(), (data) => {
                const clientID = data?.data;
                const currencyName = 'USD';
                event.loadScriptDynamically(`https://www.paypal.com/sdk/js?client-id=${clientID}&enable-funding=venmo&currency=${currencyName}`, 'paypal')
                    .then(() => {
                        event.addPaymentButton(self.getPaymentButton());
                        self.initPayPalButton(event);
                    });
            })

        }
    }


    initPayPalButton(event) {
        let self = this;
        paypal.Buttons({
            style: {
                shape: 'pill',
                color: 'white',
                layout: 'vertical',
                label: 'pay',
            },

            createOrder: (data, actions) => {
                //Make an AJAX request to the server to generate the invoice_id
                return new Promise((resolve, reject) => {
                    const cart = new TrackCart();
                    const currency = 'USD';
                    const totalPrice = cart.getTotalItemPrice();
                    const payeeEmail = cart.getCheckOutEmail();

                    if (payeeEmail && payeeEmail.checkValidity()) {
                        cart.removeCheckoutEmailInvalid();
                    } else {
                        cart.addCheckoutEmailInvalid();
                        reject('Invalid Email Address');
                    }

                    event.generateInvoiceID(self.getPaymentName(), (data) => {
                        self.invoice_id = data?.data;
                        if (self.invoice_id) {
                            resolve(actions.order.create({
                                "purchase_units": [{
                                    "amount": {
                                        "currency_code": currency,
                                        "value": totalPrice,
                                        "breakdown": {
                                            "item_total": {
                                                "currency_code": currency,
                                                "value": totalPrice
                                            }
                                        }
                                    },
                                    "invoice_id": self.invoice_id,
                                    "items": self.getPayPalItems(cart.getCart(), currency)
                                }]
                            }));
                        } else {
                            reject('Invalid Invoice ID');
                        }

                    }, () => {
                        reject('Something Went Wrong Processing Payment');
                    });
                }).catch(function (error) {
                    console.log("Error creating order: ", error);
                });
            },

            onApprove: (data, actions) => {
                return actions.order.capture().then((orderData) => {

                    if (orderData.status === 'COMPLETED') {
                        const cart = new TrackCart();
                        const checkOutEmail = cart.getCheckOutEmail();
                        const body = {
                            invoice_id: self.invoice_id,
                            checkout_email: checkOutEmail.value,
                            orderData: orderData,
                            cartItems:  Array.from(cart.getCart())
                        };

                        event.sendBody(self.getPaymentName(),
                            body,
                            (data) => {
                                console.log(data);
                                // Show a success message within this page, e.g.
                                const element = document.getElementById('paypal-button-container');
                                element.innerHTML = '';
                                element.innerHTML = data?.message;
                                const cart = new TrackCart();
                                localStorage.removeItem(cart.getCartStorageKey())
                                // Reload TonicsCart Data From LocalStorage
                                cart.setCurrentState(cart.ReloadCartFromLocalStorageState);
                                cart.runStates();
                                // Or go to another URL:  actions.redirect('thank_you.html');
                            },
                            (error) => {

                            });

                        // Full available details
                       // console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));

                    } else {

                    }
                });
            },

            onError: function (err) {
                // console.log('An Error Occured Processing Payment')
                // console.log(err);
            }
        }).render('#paypal-button-container');
    }

    getPayPalItems(cart, currency = 'USD') {
        const items = [];
        for (let [key, value] of cart.entries()) {
            items.push({
                "name": value.track_title,
                "description": `You ordered License ${value.name} with slug ${value.slug_id}`,
                "unit_amount": {
                    "currency_code": currency,
                    "value": value?.price
                },
                "quantity": "1"
            },)
        }

        return items;
    }
}

//---------------------------
//--- HANDLER AND EVENT SETUP
//---------------------------
if (window?.TonicsEvent?.EventConfig) {
    window.TonicsEvent.EventConfig.OnAudioPlayerPlayEvent.push(
        ...[
            TonicsAudioPlayHandler
        ]
    );

    window.TonicsEvent.EventConfig.OnAudioPlayerClickEvent.push(
        ...[
            TonicsAudioPlayerClickHandler
        ]
    );

    window.TonicsEvent.EventConfig.OnAudioPlayerPaymentGatewayCollatorEvent.push(
        ...[
            TonicsPayPalGateway
        ]
    );
}

