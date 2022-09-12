var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });

// src/Event/EventQueue.ts
var EventQueue = class {
  constructor() {
    this.$eventHandlers = new Map();
  }
  attachHandlerToEvent($eventType, $callback) {
    var _a;
    if (this.getHandlers().has($eventType)) {
      (_a = this.getHandlers().get($eventType)) == null ? void 0 : _a.push($callback);
      return this;
    }
    this.getHandlers().set($eventType, [$callback]);
    return this;
  }
  getHandlers() {
    return this.$eventHandlers;
  }
  detachHandlerFromEvent($eventType) {
    if (this.getHandlers().has($eventType)) {
      this.getHandlers().delete($eventType);
      return this;
    }
  }
  getEventHandlers($event) {
    var _a;
    if (!this.getHandlers().has($event)) {
      return [];
    }
    return (_a = this.getHandlers().get($event)) != null ? _a : [];
  }
};
__name(EventQueue, "EventQueue");
if (!window.hasOwnProperty("TonicsEvent")) {
  window.TonicsEvent = {};
}
window.TonicsEvent.EventQueue = () => new EventQueue();
export {
  EventQueue
};
