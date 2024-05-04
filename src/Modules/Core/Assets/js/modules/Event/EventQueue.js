
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
