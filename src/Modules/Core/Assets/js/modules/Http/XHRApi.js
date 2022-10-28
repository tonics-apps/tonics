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
