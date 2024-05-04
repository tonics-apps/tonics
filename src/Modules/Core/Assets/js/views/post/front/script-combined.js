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

// src/Event/EventHelper.ts
function attachEventAndHandlersToHandlerProvider($eventConfig, $eventName) {
  let $listenerProvider = new EventQueue();
  let eventName = $eventName.name;
  if ($eventConfig.hasOwnProperty(eventName)) {
    let $listeners = $eventConfig[eventName];
    if ($listeners.length > 0) {
      $listeners == null ? void 0 : $listeners.forEach((value, index) => {
        $listenerProvider.attachHandlerToEvent($eventName, value);
      });
    }
    return $listenerProvider;
  }
  throw new DOMException(`Can't attach ${$eventName} to listeners because it doesn't exist`);
}
__name(attachEventAndHandlersToHandlerProvider, "attachEventAndHandlersToHandlerProvider");
if (!window.hasOwnProperty("TonicsEvent")) {
  window.TonicsEvent = {};
}
window.TonicsEvent.attachEventAndHandlersToHandlerProvider = ($eventConfig, $eventName) => attachEventAndHandlersToHandlerProvider($eventConfig, $eventName);

// src/Event/EventDispatcher.ts
var EventDispatcher = class {
  constructor($handleProvider) {
    if ($handleProvider) {
      this.$handleProvider = $handleProvider;
      return this;
    }
  }
  dispatch($event) {
    let $eventName = $event.constructor;
    const eventHandlers = this.getHandler().getEventHandlers($eventName);
    for (let i = 0; i < eventHandlers.length; i++) {
      if (!Object.getOwnPropertyNames(eventHandlers[i]).includes("arguments")) {
        new eventHandlers[i]($event);
      } else {
        eventHandlers[i]($event);
      }
    }
    return $event;
  }
  setHandler($handler) {
    this.$handleProvider = $handler;
    return this;
  }
  getHandler() {
    return this.$handleProvider;
  }
  dispatchEventToHandlers($eventConfig, $eventObject, $eventClass) {
    let eventHandlers = attachEventAndHandlersToHandlerProvider($eventConfig, $eventClass);
    this.setHandler(eventHandlers).dispatch($eventObject);
  }
};
__name(EventDispatcher, "EventDispatcher");
if (!window.hasOwnProperty("TonicsEvent")) {
  window.TonicsEvent = {};
}
window.TonicsEvent.EventDispatcher = new EventDispatcher();
export {
  EventDispatcher
};
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

/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

const EventsConfig = {

    OnBeforeTonicsFieldPreviewEvent: [],
    OnBeforeTonicsFieldSubmitEvent: [],

    //  OtherEvent: [],
    // DataTables Event
    OnBeforeScrollBottomEvent: [],
    OnScrollBottomEvent: [],
    OnDoubleClickEvent: [],
    OnClickEvent: [],
    OnShiftClickEvent: [],
    OnRowMarkForDeletionEvent: [],

    OnSubmitFieldEditorsFormEvent: [],

    // Event For Audio Player
    OnAudioPlayerPlayEvent: [],
    OnAudioPlayerPauseEvent: [],
    OnAudioPlayerPreviousEvent: [],
    OnAudioPlayerNextEvent: [],
    OnAudioPlayerClickEvent: [],

    // Event For Payment Gateway
    OnPaymentGatewayCollatorEvent: [],
};

window.TonicsEvent.EventConfig = EventsConfig;


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

// src/Util/Element/MenuToggle.ts
var MenuToggle = class extends ElementAbstract {
  constructor($parentElement, $queryAdapter) {
    super($parentElement);
    this.$menuDetails = {};
    this.queryAdapter = $queryAdapter;
  }
  getQueryAdapter() {
    return this.queryAdapter;
  }
  settings($menuItemElement, $buttonElement, $subMenuElement) {
    this.getMenuDetails().menu = {
      parent: {
        element: this.getQueryResult(),
        event: ""
      },
      propagate: true,
      propagateElements: [],
      menuClass: $menuItemElement,
      buttonClass: $buttonElement,
      subMenuClass: $subMenuElement,
      on: {
        button: { icon: { add: "", remove: "" } },
        subMenu: { class: { add: "", remove: "", animation: { start: "", end: "" } } }
      },
      off: {
        button: { icon: { add: "", remove: "" } },
        subMenu: { class: { add: "", remove: "", animation: { start: "", end: "" } } }
      }
    };
    return this;
  }
  stopPropagation($bool = true) {
    if (this.getMenuDetails().hasOwnProperty("menu")) {
      this.getMenuDetails().menu.propagate = $bool;
    }
    return this;
  }
  propagateElements($elementsToPropagate = []) {
    if (this.getMenuDetails().hasOwnProperty("menu")) {
      this.getMenuDetails().menu.propagateElements = $elementsToPropagate;
    }
    return this;
  }
  buttonIcon($add, $remove) {
    if (this.getMenuDetails().hasOwnProperty("menu")) {
      this.getMenuDetails().menu.on.button.icon.add = $add;
      this.getMenuDetails().menu.off.button.icon.add = $remove;
      return this;
    }
    throw new DOMException("No Menu Element Added");
  }
  menuIsOn($addClass, $removeClass) {
    if (this.getMenuDetails().hasOwnProperty("menu")) {
      this.getMenuDetails().menu.on.subMenu.class.add = $addClass;
      this.getMenuDetails().menu.on.subMenu.class.remove = $removeClass;
      return this;
    }
    throw new DOMException("No Menu Element Added");
  }
  menuIsOff($addClass, $removeClass) {
    if (this.getMenuDetails().hasOwnProperty("menu")) {
      this.getMenuDetails().menu.off.subMenu.class.add = $addClass;
      this.getMenuDetails().menu.off.subMenu.class.remove = $removeClass;
      return this;
    }
    throw new DOMException("No Menu Element Added");
  }
  run() {
    let $parent = this.getMenuDetails().menu.parent.element;
    if ($parent) {
      $parent.addEventListener("click", (e) => {
        var _a, _b;
        let el = e.target;
        if (this.getMenuDetails().menu.propagate) {
          let matchesStopPropagationEl = false;
          this.getMenuDetails().menu.propagateElements.forEach((propElString) => {
            if (el.closest(propElString)) {
              matchesStopPropagationEl = true;
            }
          });
          if (!matchesStopPropagationEl) {
            e.stopPropagation();
          }
        }
        if (el.closest(this.getMenuDetails().menu.buttonClass)) {
          e.preventDefault();
          let $button = el.closest(this.getMenuDetails().menu.buttonClass);
          $button.classList.toggle("toggle-on");
          $button.ariaLabel = "Expand child menu";
          $button.ariaExpanded = "false";
          if ($button.classList.contains("toggle-on")) {
            if ($button.closest("[data-menu-depth]")) {
              let menuDepth = $button.closest("[data-menu-depth]");
              let allMenuOnSameDepth = document.querySelectorAll(`[data-menu-depth="${menuDepth.dataset.menuDepth}"]`);
              if (allMenuOnSameDepth.length > 0) {
                allMenuOnSameDepth.forEach((el2) => {
                  let allMenuOnSameDepthMenu = el2.querySelector(this.getMenuDetails().menu.buttonClass);
                  if (allMenuOnSameDepthMenu === $button) {
                    return;
                  }
                  if (allMenuOnSameDepthMenu) {
                    if (allMenuOnSameDepthMenu.classList.contains("toggle-on")) {
                      allMenuOnSameDepthMenu.click();
                    }
                  }
                });
              }
            }
            $button.ariaLabel = "Collapse child menu";
            $button.ariaExpanded = "true";
          }
          let $menuItem = $button.closest(this.getMenuDetails().menu.menuClass);
          let $subMenu = (_b = (_a = this.getQueryAdapter().addNodeElement($menuItem).in()) == null ? void 0 : _a.forward(this.getMenuDetails().menu.subMenuClass)) == null ? void 0 : _b.getQueryResult();
          if (!$subMenu) {
            $subMenu = this.getQueryAdapter().addNodeElement($menuItem).queryChildren(this.getMenuDetails().menu.subMenuClass).getQueryResult();
          }
          if ($subMenu.classList.contains(this.getMenuDetails().menu.on.subMenu.class.add[0])) {
            if (this.hasAnimation($subMenu)) {
              const flexString = this.getMenuDetails().menu.off.subMenu.class.remove.find((a) => a.includes("flex") || a.includes("display-block"));
              let toRemove = this.getMenuDetails().menu.off.subMenu.class.remove.filter((e2) => e2 !== flexString);
              const noneString = this.getMenuDetails().menu.off.subMenu.class.add.find((a) => a.includes("none"));
              let toAdd = this.getMenuDetails().menu.off.subMenu.class.add.filter((e2) => e2 !== noneString);
              $subMenu.classList.remove(toRemove);
              $subMenu.classList.add(toAdd);
              $subMenu.addEventListener("animationend", () => {
                $subMenu.classList.remove(flexString);
                $subMenu.classList.add(noneString);
              }, { once: true });
            } else {
              $subMenu.classList.remove(...this.getMenuDetails().menu.off.subMenu.class.remove);
              $subMenu.classList.add(...this.getMenuDetails().menu.off.subMenu.class.add);
            }
          } else {
            $subMenu.classList.add(...this.getMenuDetails().menu.on.subMenu.class.add);
            $subMenu.classList.remove(...this.getMenuDetails().menu.on.subMenu.class.remove);
          }
          if (this.getMenuDetails().menu.off.button.icon.add) {
            let $svgUse = $button.querySelector(".svgUse");
            if ($svgUse) {
              let $svgUseAttribute = $svgUse.getAttribute("xlink:href");
              if ($svgUseAttribute === this.getMenuDetails().menu.off.button.icon.add) {
                this.getQueryAdapter().addNodeElement($svgUse).setSVGUseAttribute(this.getMenuDetails().menu.on.button.icon.add);
              } else {
                this.getQueryAdapter().addNodeElement($svgUse).setSVGUseAttribute(this.getMenuDetails().menu.off.button.icon.add);
              }
            } else {
              throw new DOMException("Add class `svgUse` to svg use element");
            }
          }
        }
      });
    }
  }
  getMenuDetails() {
    return this.$menuDetails;
  }
  closeMenuToggle($parent = null) {
    let self = this;
    if ($parent === null) {
      $parent = document;
    }
    $parent.querySelectorAll(self.getMenuDetails().menu.buttonClass).forEach((button) => {
      if (button.classList.contains("toggle-on") && button.dataset.hasOwnProperty("menutoggle_click_outside") && button.dataset.menutoggle_click_outside === "true") {
        button.click();
      }
    });
  }
  closeOnClickOutSide($bool) {
    if ($bool) {
      let $parent = this.getMenuDetails().menu.parent.element;
      $parent.querySelectorAll(this.getMenuDetails().menu.buttonClass).forEach((button) => {
        button.setAttribute("data-menutoggle_click_outside", "true");
      });
    }
    let self = this;
    document.addEventListener("click", function(e) {
      self.closeMenuToggle();
    });
    document.addEventListener("keyup", function(e) {
      if (e.key === "Escape") {
        self.closeMenuToggle();
      }
    });
    return this;
  }
  hasAnimation($el) {
    let styles = window.getComputedStyle($el, null);
    const animDuration = parseFloat(styles.getPropertyValue("animation-duration") || "0");
    const transDuration = parseFloat(styles.getPropertyValue("transition-duration") || "0");
    return animDuration > 0 || transDuration > 0;
  }
};

__name(MenuToggle, "MenuToggle");
if (!window.hasOwnProperty("TonicsScript")) {
  window.TonicsScript = {};
}
window.TonicsScript.MenuToggle = ($parentElement, $queryAdapter) => new MenuToggle($parentElement, $queryAdapter);
export {
  MenuToggle
};
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

// src/Util/Element/Query.ts
var Query = class extends ElementAbstract {
  addNodeElement($element) {
    this.setQueryResult($element);
    return this;
  }
  forward($classOrID) {
    let $nextElement = this.getQueryResult().nextElementSibling;
    while ($nextElement) {
      if ($nextElement.matches($classOrID)) {
        this.setQueryResult($nextElement);
        return this;
      }
      $nextElement = $nextElement.nextElementSibling;
    }
    return null;
  }
  backward($classOrID) {
    let $prevElement = this.getQueryResult().previousElementSibling;
    while ($prevElement) {
      if ($prevElement.matches($classOrID)) {
        this.setQueryResult($prevElement);
        return this;
      }
      $prevElement = $prevElement.previousElementSibling;
    }
    return null;
  }
  in() {
    let $in = this.getQueryResult().firstElementChild;
    if ($in) {
      this.setQueryResult($in);
      return this;
    }
    return null;
  }
  out() {
    let $out = this.getQueryResult().parentElement;
    if ($out) {
      this.setQueryResult($out);
      return this;
    }
    return null;
  }
  queryChildren($classOrID, setQueryResult = true) {
    let $childElement = this.getQueryResult().querySelector($classOrID);
    if ($childElement) {
      if (setQueryResult) {
        this.setQueryResult($childElement);
        return this;
      }
      return $childElement;
    }
    return null;
  }
  setSVGUseAttribute($attributeName) {
    let $svgUseAttribute = this.getQueryResult();
    if ($svgUseAttribute.tagName == "use") {
      $svgUseAttribute.removeAttribute("xlink:href");
      $svgUseAttribute.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", $attributeName);
    } else {
      throw new DOMException("Not a valid svg use element");
    }
  }
};

__name(Query, "Query");
if (!window.hasOwnProperty("TonicsScript")) {
  window.TonicsScript = {};
}
window.TonicsScript.Query = () => new Query();
export {
  Query
};
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

// src/Util/Others/HelpersUtil.ts
function str_replace($search, $replace, $subject) {
  let i, regex = [], map = {};
  for (i = 0; i < $search.length; i++) {
    regex.push($search[i].replace(/([-[\]{}()*+?.\\^$|#,])/g, "\\$1"));
    map[$search[i]] = $replace[i];
  }
  regex = regex.join("|");
  $subject = $subject.replace(new RegExp(regex, "g"), function(matched) {
    return map[matched];
  });
  return $subject;
}
__name(str_replace, "str_replace");
function slug($string, $separator = "-") {
  $string = $string.trim();
  $string = str_replace(["&", "@", "%", "$", "*", "<", ">", "+", "!"], [
    " and",
    " at",
    " percentage",
    " dollar",
    " asterisk ",
    " less than",
    " greater than",
    " plus",
    "exclamation"
  ], $string);
  return $string.toLocaleString().toLowerCase().normalize("NFD").replace(/[^\S]+/g, $separator);
}
__name(slug, "slug");
function isValidTagName(tagName) {
  return document.createElement(tagName).toString() !== "[object HTMLUnknownElement]";
}
__name(isValidTagName, "isValidTagName");

// src/Util/Others/TableOfContent.ts
var TableOfContent = class {
  constructor($tableOfContentContainer = "") {
    this._$tableOfContentDetails = {};
    this._tocTree = [];
    this._breakLoopBackward = false;
    this._tocResult = "";
    this._$tableOfContentDetails = {
      tocContainer: $tableOfContentContainer,
      noOfHeadersFound: 0,
      tocDepth: 4,
      tocNoHeadingToTrigger: 2,
      tocLabel: "Table Of Content",
      tocLabelTag: "h2",
      tocClass: "tonics-toc"
    };
  }
  get breakLoopBackward() {
    return this._breakLoopBackward;
  }
  set breakLoopBackward(value) {
    this._breakLoopBackward = value;
  }
  get tocResult() {
    return this._tocResult;
  }
  set tocResult(value) {
    this._tocResult = value;
  }
  get tocTree() {
    return this._tocTree;
  }
  set tocTree(value) {
    this._tocTree = value;
  }
  get $tableOfContentDetails() {
    return this._$tableOfContentDetails;
  }
  set $tableOfContentDetails(value) {
    this._$tableOfContentDetails = value;
  }
  settings() {
    this._$tableOfContentDetails = {
      tocDepth: 4,
      tocNoHeadingToTrigger: 2,
      tocLabel: "Table Of Content",
      tocLabelTag: "h2",
      tocClass: ".tonics-toc"
    };
  }
  tocContainer(tag) {
    this._$tableOfContentDetails.tocContainer = tag;
    return this;
  }
  tocDepth(int) {
    this._$tableOfContentDetails.tocDepth = int;
    return this;
  }
  tocNoHeadingToTrigger(int) {
    this._$tableOfContentDetails.tocNoHeadingToTrigger = int;
    return this;
  }
  tocLabel(label) {
    this._$tableOfContentDetails.tocLabel = label;
    return this;
  }
  tocLabelTag(labelTag) {
    this._$tableOfContentDetails.tocLabelTag = labelTag;
    return this;
  }
  tocClass(tocClass) {
    this._$tableOfContentDetails.tocClass = tocClass;
    return this;
  }
  run() {
    const toc = this._$tableOfContentDetails;
    if (document.querySelector(toc.tocContainer)) {
      let selector = "h1, h2, h3, h4";
      if (toc.tocDepth <= 6) {
        selector = "";
        for (let i = 1; i <= toc.tocDepth; i++) {
          if (i === toc.tocDepth) {
            selector += `h${i}`;
          } else {
            selector += `h${i}, `;
          }
        }
      }
      let tocHeader = document.querySelector(toc.tocContainer).querySelectorAll(selector);
      if (tocHeader.length < toc.tocNoHeadingToTrigger) {
        return;
      }
      const isTagNameValid = isValidTagName(toc.tocLabelTag);
      if (!isTagNameValid) {
        throw new DOMException(toc.tocLabelTag + " is not a valid tagName ");
      }
      if (tocHeader.length > 0) {
        toc.noOfHeadersFound = tocHeader.length;
        let currentLevel = 0, lastAddedElementToTree = 0, result = `<ul>`, item = {}, childStack = [];
        tocHeader.forEach((header, index) => {
          if (header.textContent.length > 0) {
            let headerIDSlug = slug(header.textContent);
            let headerText = header.textContent;
            header.id = headerIDSlug;
            currentLevel = parseInt(header.tagName[1]);
            item = {
              "level": currentLevel,
              "headerID": headerIDSlug,
              "headerText": headerText
            };
            item.data = `<li data-heading_level="${currentLevel}"><a href="#${headerIDSlug}"><span class="toctext">${headerText}</span></a>`;
            if (this.tocTree.length === 0) {
              this.pushToTree(item);
            } else {
              if (this.tocTree[lastAddedElementToTree].level === currentLevel) {
                this.tocTree[lastAddedElementToTree].data += "</li>" + item.data;
              }
              if (currentLevel > this.tocTree[lastAddedElementToTree].level) {
                this.tocTree[lastAddedElementToTree].data += "<ul>";
                this.pushToTree(item);
                lastAddedElementToTree = this.tocTree.length - 1;
                childStack.push(item);
              }
              if (currentLevel < this.tocTree[lastAddedElementToTree].level) {
                let timesToClose = 0;
                for (const treeData of this.loopTreeBackward(childStack)) {
                  if (treeData.level > currentLevel) {
                    treeData.data += "</li>";
                    childStack.pop();
                    timesToClose++;
                  }
                  if (treeData.level === currentLevel) {
                    this.breakLoopBackward = true;
                  }
                }
                item.data = "</ul></li>".repeat(timesToClose) + item.data;
                this.pushToTree(item);
                lastAddedElementToTree = this.tocTree.length - 1;
              }
              if (index === tocHeader.length - 1) {
                this.tocTree[lastAddedElementToTree].data += "</li>";
              }
            }
          }
        });
        this.tocTree.forEach((item2) => {
          result += item2.data;
        });
        result += "</ul>";
        result = `<div class="${toc.tocClass}"> <${toc.tocLabelTag}> ${toc.tocLabel} </${toc.tocLabelTag}> ${result} </div>`;
        this._tocResult = result;
      }
    }
  }
  pushToTree(item) {
    item.index = this.tocTree.length;
    this.tocTree.push(item);
  }
  *loopTreeBackward(treeToLoop = null) {
    if (treeToLoop === null) {
      treeToLoop = this.tocTree;
    }
    for (let i = treeToLoop.length - 1; i >= 0; i--) {
      if (this.breakLoopBackward) {
        break;
      }
      yield treeToLoop[i];
    }
    this.breakLoopBackward = false;
  }
};
__name(TableOfContent, "TableOfContent");
if (!window.hasOwnProperty("TonicsScript")) {
  window.TonicsScript = {};
}
window.TonicsScript.TableOfContent = ($tableOfContentContainer) => new TableOfContent($tableOfContentContainer);
export {
  TableOfContent
};

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

const tableOfContent = new TableOfContent('.entry-content');
tableOfContent.tocDepth(6).run();
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

try {
    new MenuToggle('.site-nav', new Query())
        .settings('.menu-block', '.dropdown-toggle', '.child-menu')
        .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(true)
        .run();
}catch (e) {
    console.error("An Error Occur Setting MenuToggle: Site-Nav")
}