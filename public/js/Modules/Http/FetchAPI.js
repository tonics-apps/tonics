var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
var __async = (__this, __arguments, generator) => {
  return new Promise((resolve, reject) => {
    var fulfilled = (value) => {
      try {
        step(generator.next(value));
      } catch (e) {
        reject(e);
      }
    };
    var rejected = (value) => {
      try {
        step(generator.throw(value));
      } catch (e) {
        reject(e);
      }
    };
    var step = (x) => x.done ? resolve(x.value) : Promise.resolve(x.value).then(fulfilled, rejected);
    step((generator = generator.apply(__this, __arguments)).next());
  });
};

// src/Util/Http/FetchAPI.ts
var FetchAPI = class {
  constructor($request) {
    this.$request = $request;
    return this;
  }
  run() {
    return __async(this, null, function* () {
      return yield fetch(this.getRequest());
    });
  }
  getRequest() {
    return this.$request;
  }
};
__name(FetchAPI, "FetchAPI");
export {
  FetchAPI
};
