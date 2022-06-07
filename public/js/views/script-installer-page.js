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
    try {
      this.http.onload = function() {
        callBack(null, self.http.responseText);
      };
    } catch (e) {
      callBack("Something Went Wrong: " + e.description);
    }
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
export {
  XHRApi
};
let showPassContainer = document.querySelectorAll('.password-with-show');
if (showPassContainer){
    for (let i = 0, len = showPassContainer.length; i < len; i++) {
        showPassContainer[i].addEventListener('click', function (e) {
            let el = e.target;
            if (el.classList.contains('show-password')){
                let inputPass = showPassContainer[i].querySelector('input');
                if (el.getAttribute('aria-pressed') && el.getAttribute('aria-pressed') === 'false'){
                    el.setAttribute('aria-pressed', true);
                    el.innerText = 'Hide';
                    inputPass.type = 'text'
                    return;
                }

                if (el.getAttribute('aria-pressed') && el.getAttribute('aria-pressed') === 'true'){
                    el.setAttribute('aria-pressed', false);
                    inputPass.type = 'password';
                    el.innerText = 'Show';
                }
            }
        });
    }
}let preElement = document.querySelector('.installation-pre'),
    form = document.querySelector('form');


function submitForm(event) {
    if (document.forms.hasOwnProperty('InstallerForm')){
        event.preventDefault();
        let formEl = document.forms.InstallerForm;
        let formData = new FormData(formEl);
        let formEntries = {};
        // Arrange The Form Data
        formData.forEach(function (value, key, parent) {
            formEntries[key] = value;
        })
        let preInstallerAPI = `/api/pre-installer`
        let XHR = new XHRApi();
        XHR.Post(preInstallerAPI, JSON.stringify(formEntries), function (err, data) {
            if (err) {
                err = JSON.parse(err);
                console.log(err);
            }
            if (data) {
                data = JSON.parse(data);
                if (data.hasOwnProperty('status')) {
                    if (data.status === 200) {
                        let preElement = document.querySelector('.installation-pre');
                        let eventSource = new EventSource('/api/installer');

                        // would have used error instead issue but there is already a built-in type named error
                        eventSource.addEventListener('issue', function(e) {
                            try{
                                const data = JSON.parse(e.data);
                                preCodeMessage(`<code class='color:red'>${data}</code>`);
                            }catch(e){
                                preCodeMessage(`<code class='color:red'>> BAD JSON DATA: ${e}</code>`)
                            }
                        }, false);

                        eventSource.addEventListener('message', function(e) {
                            try{
                                const data = JSON.parse(e.data);
                                console.log(data);
                                preCodeMessage(`<code>${data}</code>`);
                            }catch(e){
                                preCodeMessage(`<code class='color:red'>> BAD JSON DATA: ${e}</code>`)
                            }
                        }, false);

                        eventSource.addEventListener('close', function(e) {
                            preCodeMessage(`<code>Closed</code>`);
                            eventSource.close();
                            eventSource = null;
                        }, false);

                        eventSource.addEventListener('open', function(e) {
                            // Connection was opened.
                            console.log("Opening new connection");
                        }, false);

                        eventSource.addEventListener('redirect', function(e) {
                            try{
                                let url = JSON.parse(JSON.parse(e.data));
                                window.location.href = window.location.origin + url.page;
                            }catch(e){
                                preCodeMessage(`<code class='color:red'>> BAD JSON DATA: ${e}</code>`);
                            }
                        }, false);
                    }
                }
            }
        });
    }
}

function preCodeMessage(message = '') {
    if (preElement){
        preElement.insertAdjacentHTML('beforeend', message)
        if (preElement.lastElementChild){
            preElement.scrollTop = preElement.scrollHeight;
        }
    }
}

form.addEventListener('submit', submitForm);