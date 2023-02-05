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
var __create = Object.create;
var __defProp = Object.defineProperty;
var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
var __getOwnPropNames = Object.getOwnPropertyNames;
var __getOwnPropSymbols = Object.getOwnPropertySymbols;
var __getProtoOf = Object.getPrototypeOf;
var __hasOwnProp = Object.prototype.hasOwnProperty;
var __propIsEnum = Object.prototype.propertyIsEnumerable;
var __defNormalProp = (obj, key, value) => key in obj ? __defProp(obj, key, { enumerable: true, configurable: true, writable: true, value }) : obj[key] = value;
var __spreadValues = (a, b) => {
  for (var prop in b || (b = {}))
    if (__hasOwnProp.call(b, prop))
      __defNormalProp(a, prop, b[prop]);
  if (__getOwnPropSymbols)
    for (var prop of __getOwnPropSymbols(b)) {
      if (__propIsEnum.call(b, prop))
        __defNormalProp(a, prop, b[prop]);
    }
  return a;
};
var __markAsModule = (target) => __defProp(target, "__esModule", { value: true });
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
var __commonJS = (cb, mod) => function __require() {
  return mod || (0, cb[Object.keys(cb)[0]])((mod = { exports: {} }).exports, mod), mod.exports;
};
var __reExport = (target, module, desc) => {
  if (module && typeof module === "object" || typeof module === "function") {
    for (let key of __getOwnPropNames(module))
      if (!__hasOwnProp.call(target, key) && key !== "default")
        __defProp(target, key, { get: () => module[key], enumerable: !(desc = __getOwnPropDesc(module, key)) || desc.enumerable });
  }
  return target;
};
var __toModule = (module) => {
  return __reExport(__markAsModule(__defProp(module != null ? __create(__getProtoOf(module)) : {}, "default", module && module.__esModule && "default" in module ? { get: () => module.default, enumerable: true } : { value: module, enumerable: true })), module);
};
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

// node_modules/sweetalert2/dist/sweetalert2.all.js
var require_sweetalert2_all = __commonJS({
  "node_modules/sweetalert2/dist/sweetalert2.all.js"(exports, module) {
    (function(global, factory) {
      typeof exports === "object" && typeof module !== "undefined" ? module.exports = factory() : typeof define === "function" && define.amd ? define(factory) : (global = global || self, global.Sweetalert2 = factory());
    })(exports, function() {
      "use strict";
      const DismissReason = Object.freeze({
        cancel: "cancel",
        backdrop: "backdrop",
        close: "close",
        esc: "esc",
        timer: "timer"
      });
      const consolePrefix = "SweetAlert2:";
      const uniqueArray = /* @__PURE__ */ __name((arr) => {
        const result = [];
        for (let i = 0; i < arr.length; i++) {
          if (result.indexOf(arr[i]) === -1) {
            result.push(arr[i]);
          }
        }
        return result;
      }, "uniqueArray");
      const capitalizeFirstLetter = /* @__PURE__ */ __name((str) => str.charAt(0).toUpperCase() + str.slice(1), "capitalizeFirstLetter");
      const toArray = /* @__PURE__ */ __name((nodeList) => Array.prototype.slice.call(nodeList), "toArray");
      const warn = /* @__PURE__ */ __name((message) => {
        console.warn("".concat(consolePrefix, " ").concat(typeof message === "object" ? message.join(" ") : message));
      }, "warn");
      const error = /* @__PURE__ */ __name((message) => {
        console.error("".concat(consolePrefix, " ").concat(message));
      }, "error");
      const previousWarnOnceMessages = [];
      const warnOnce = /* @__PURE__ */ __name((message) => {
        if (!previousWarnOnceMessages.includes(message)) {
          previousWarnOnceMessages.push(message);
          warn(message);
        }
      }, "warnOnce");
      const warnAboutDeprecation = /* @__PURE__ */ __name((deprecatedParam, useInstead) => {
        warnOnce('"'.concat(deprecatedParam, '" is deprecated and will be removed in the next major release. Please use "').concat(useInstead, '" instead.'));
      }, "warnAboutDeprecation");
      const callIfFunction = /* @__PURE__ */ __name((arg) => typeof arg === "function" ? arg() : arg, "callIfFunction");
      const hasToPromiseFn = /* @__PURE__ */ __name((arg) => arg && typeof arg.toPromise === "function", "hasToPromiseFn");
      const asPromise = /* @__PURE__ */ __name((arg) => hasToPromiseFn(arg) ? arg.toPromise() : Promise.resolve(arg), "asPromise");
      const isPromise = /* @__PURE__ */ __name((arg) => arg && Promise.resolve(arg) === arg, "isPromise");
      const isJqueryElement = /* @__PURE__ */ __name((elem) => typeof elem === "object" && elem.jquery, "isJqueryElement");
      const isElement = /* @__PURE__ */ __name((elem) => elem instanceof Element || isJqueryElement(elem), "isElement");
      const argsToParams = /* @__PURE__ */ __name((args) => {
        const params = {};
        if (typeof args[0] === "object" && !isElement(args[0])) {
          Object.assign(params, args[0]);
        } else {
          ["title", "html", "icon"].forEach((name, index) => {
            const arg = args[index];
            if (typeof arg === "string" || isElement(arg)) {
              params[name] = arg;
            } else if (arg !== void 0) {
              error("Unexpected type of ".concat(name, '! Expected "string" or "Element", got ').concat(typeof arg));
            }
          });
        }
        return params;
      }, "argsToParams");
      const swalPrefix = "swal2-";
      const prefix = /* @__PURE__ */ __name((items) => {
        const result = {};
        for (const i in items) {
          result[items[i]] = swalPrefix + items[i];
        }
        return result;
      }, "prefix");
      const swalClasses = prefix(["container", "shown", "height-auto", "iosfix", "popup", "modal", "no-backdrop", "no-transition", "toast", "toast-shown", "show", "hide", "close", "title", "html-container", "actions", "confirm", "deny", "cancel", "default-outline", "footer", "icon", "icon-content", "image", "input", "file", "range", "select", "radio", "checkbox", "label", "textarea", "inputerror", "input-label", "validation-message", "progress-steps", "active-progress-step", "progress-step", "progress-step-line", "loader", "loading", "styled", "top", "top-start", "top-end", "top-left", "top-right", "center", "center-start", "center-end", "center-left", "center-right", "bottom", "bottom-start", "bottom-end", "bottom-left", "bottom-right", "grow-row", "grow-column", "grow-fullscreen", "rtl", "timer-progress-bar", "timer-progress-bar-container", "scrollbar-measure", "icon-success", "icon-warning", "icon-info", "icon-question", "icon-error"]);
      const iconTypes = prefix(["success", "warning", "info", "question", "error"]);
      const getContainer = /* @__PURE__ */ __name(() => document.body.querySelector(".".concat(swalClasses.container)), "getContainer");
      const elementBySelector = /* @__PURE__ */ __name((selectorString) => {
        const container = getContainer();
        return container ? container.querySelector(selectorString) : null;
      }, "elementBySelector");
      const elementByClass = /* @__PURE__ */ __name((className) => {
        return elementBySelector(".".concat(className));
      }, "elementByClass");
      const getPopup = /* @__PURE__ */ __name(() => elementByClass(swalClasses.popup), "getPopup");
      const getIcon = /* @__PURE__ */ __name(() => elementByClass(swalClasses.icon), "getIcon");
      const getTitle = /* @__PURE__ */ __name(() => elementByClass(swalClasses.title), "getTitle");
      const getHtmlContainer = /* @__PURE__ */ __name(() => elementByClass(swalClasses["html-container"]), "getHtmlContainer");
      const getImage = /* @__PURE__ */ __name(() => elementByClass(swalClasses.image), "getImage");
      const getProgressSteps = /* @__PURE__ */ __name(() => elementByClass(swalClasses["progress-steps"]), "getProgressSteps");
      const getValidationMessage = /* @__PURE__ */ __name(() => elementByClass(swalClasses["validation-message"]), "getValidationMessage");
      const getConfirmButton = /* @__PURE__ */ __name(() => elementBySelector(".".concat(swalClasses.actions, " .").concat(swalClasses.confirm)), "getConfirmButton");
      const getDenyButton = /* @__PURE__ */ __name(() => elementBySelector(".".concat(swalClasses.actions, " .").concat(swalClasses.deny)), "getDenyButton");
      const getInputLabel = /* @__PURE__ */ __name(() => elementByClass(swalClasses["input-label"]), "getInputLabel");
      const getLoader = /* @__PURE__ */ __name(() => elementBySelector(".".concat(swalClasses.loader)), "getLoader");
      const getCancelButton = /* @__PURE__ */ __name(() => elementBySelector(".".concat(swalClasses.actions, " .").concat(swalClasses.cancel)), "getCancelButton");
      const getActions = /* @__PURE__ */ __name(() => elementByClass(swalClasses.actions), "getActions");
      const getFooter = /* @__PURE__ */ __name(() => elementByClass(swalClasses.footer), "getFooter");
      const getTimerProgressBar = /* @__PURE__ */ __name(() => elementByClass(swalClasses["timer-progress-bar"]), "getTimerProgressBar");
      const getCloseButton = /* @__PURE__ */ __name(() => elementByClass(swalClasses.close), "getCloseButton");
      const focusable = '\n  a[href],\n  area[href],\n  input:not([disabled]),\n  select:not([disabled]),\n  textarea:not([disabled]),\n  button:not([disabled]),\n  iframe,\n  object,\n  embed,\n  [tabindex="0"],\n  [contenteditable],\n  audio[controls],\n  video[controls],\n  summary\n';
      const getFocusableElements = /* @__PURE__ */ __name(() => {
        const focusableElementsWithTabindex = toArray(getPopup().querySelectorAll('[tabindex]:not([tabindex="-1"]):not([tabindex="0"])')).sort((a, b) => {
          a = parseInt(a.getAttribute("tabindex"));
          b = parseInt(b.getAttribute("tabindex"));
          if (a > b) {
            return 1;
          } else if (a < b) {
            return -1;
          }
          return 0;
        });
        const otherFocusableElements = toArray(getPopup().querySelectorAll(focusable)).filter((el) => el.getAttribute("tabindex") !== "-1");
        return uniqueArray(focusableElementsWithTabindex.concat(otherFocusableElements)).filter((el) => isVisible(el));
      }, "getFocusableElements");
      const isModal = /* @__PURE__ */ __name(() => {
        return !isToast() && !document.body.classList.contains(swalClasses["no-backdrop"]);
      }, "isModal");
      const isToast = /* @__PURE__ */ __name(() => {
        return document.body.classList.contains(swalClasses["toast-shown"]);
      }, "isToast");
      const isLoading = /* @__PURE__ */ __name(() => {
        return getPopup().hasAttribute("data-loading");
      }, "isLoading");
      const states = {
        previousBodyPadding: null
      };
      const setInnerHtml = /* @__PURE__ */ __name((elem, html) => {
        elem.textContent = "";
        if (html) {
          const parser = new DOMParser();
          const parsed = parser.parseFromString(html, "text/html");
          toArray(parsed.querySelector("head").childNodes).forEach((child) => {
            elem.appendChild(child);
          });
          toArray(parsed.querySelector("body").childNodes).forEach((child) => {
            elem.appendChild(child);
          });
        }
      }, "setInnerHtml");
      const hasClass = /* @__PURE__ */ __name((elem, className) => {
        if (!className) {
          return false;
        }
        const classList = className.split(/\s+/);
        for (let i = 0; i < classList.length; i++) {
          if (!elem.classList.contains(classList[i])) {
            return false;
          }
        }
        return true;
      }, "hasClass");
      const removeCustomClasses = /* @__PURE__ */ __name((elem, params) => {
        toArray(elem.classList).forEach((className) => {
          if (!Object.values(swalClasses).includes(className) && !Object.values(iconTypes).includes(className) && !Object.values(params.showClass).includes(className)) {
            elem.classList.remove(className);
          }
        });
      }, "removeCustomClasses");
      const applyCustomClass = /* @__PURE__ */ __name((elem, params, className) => {
        removeCustomClasses(elem, params);
        if (params.customClass && params.customClass[className]) {
          if (typeof params.customClass[className] !== "string" && !params.customClass[className].forEach) {
            return warn("Invalid type of customClass.".concat(className, '! Expected string or iterable object, got "').concat(typeof params.customClass[className], '"'));
          }
          addClass(elem, params.customClass[className]);
        }
      }, "applyCustomClass");
      const getInput = /* @__PURE__ */ __name((popup, inputType) => {
        if (!inputType) {
          return null;
        }
        switch (inputType) {
          case "select":
          case "textarea":
          case "file":
            return getChildByClass(popup, swalClasses[inputType]);
          case "checkbox":
            return popup.querySelector(".".concat(swalClasses.checkbox, " input"));
          case "radio":
            return popup.querySelector(".".concat(swalClasses.radio, " input:checked")) || popup.querySelector(".".concat(swalClasses.radio, " input:first-child"));
          case "range":
            return popup.querySelector(".".concat(swalClasses.range, " input"));
          default:
            return getChildByClass(popup, swalClasses.input);
        }
      }, "getInput");
      const focusInput = /* @__PURE__ */ __name((input) => {
        input.focus();
        if (input.type !== "file") {
          const val = input.value;
          input.value = "";
          input.value = val;
        }
      }, "focusInput");
      const toggleClass = /* @__PURE__ */ __name((target, classList, condition) => {
        if (!target || !classList) {
          return;
        }
        if (typeof classList === "string") {
          classList = classList.split(/\s+/).filter(Boolean);
        }
        classList.forEach((className) => {
          if (target.forEach) {
            target.forEach((elem) => {
              condition ? elem.classList.add(className) : elem.classList.remove(className);
            });
          } else {
            condition ? target.classList.add(className) : target.classList.remove(className);
          }
        });
      }, "toggleClass");
      const addClass = /* @__PURE__ */ __name((target, classList) => {
        toggleClass(target, classList, true);
      }, "addClass");
      const removeClass = /* @__PURE__ */ __name((target, classList) => {
        toggleClass(target, classList, false);
      }, "removeClass");
      const getChildByClass = /* @__PURE__ */ __name((elem, className) => {
        for (let i = 0; i < elem.childNodes.length; i++) {
          if (hasClass(elem.childNodes[i], className)) {
            return elem.childNodes[i];
          }
        }
      }, "getChildByClass");
      const applyNumericalStyle = /* @__PURE__ */ __name((elem, property, value) => {
        if (value === "".concat(parseInt(value))) {
          value = parseInt(value);
        }
        if (value || parseInt(value) === 0) {
          elem.style[property] = typeof value === "number" ? "".concat(value, "px") : value;
        } else {
          elem.style.removeProperty(property);
        }
      }, "applyNumericalStyle");
      const show = /* @__PURE__ */ __name(function(elem) {
        let display = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : "flex";
        elem.style.display = display;
      }, "show");
      const hide = /* @__PURE__ */ __name((elem) => {
        elem.style.display = "none";
      }, "hide");
      const setStyle = /* @__PURE__ */ __name((parent, selector, property, value) => {
        const el = parent.querySelector(selector);
        if (el) {
          el.style[property] = value;
        }
      }, "setStyle");
      const toggle = /* @__PURE__ */ __name((elem, condition, display) => {
        condition ? show(elem, display) : hide(elem);
      }, "toggle");
      const isVisible = /* @__PURE__ */ __name((elem) => !!(elem && (elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length)), "isVisible");
      const allButtonsAreHidden = /* @__PURE__ */ __name(() => !isVisible(getConfirmButton()) && !isVisible(getDenyButton()) && !isVisible(getCancelButton()), "allButtonsAreHidden");
      const isScrollable = /* @__PURE__ */ __name((elem) => !!(elem.scrollHeight > elem.clientHeight), "isScrollable");
      const hasCssAnimation = /* @__PURE__ */ __name((elem) => {
        const style = window.getComputedStyle(elem);
        const animDuration = parseFloat(style.getPropertyValue("animation-duration") || "0");
        const transDuration = parseFloat(style.getPropertyValue("transition-duration") || "0");
        return animDuration > 0 || transDuration > 0;
      }, "hasCssAnimation");
      const animateTimerProgressBar = /* @__PURE__ */ __name(function(timer) {
        let reset = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : false;
        const timerProgressBar = getTimerProgressBar();
        if (isVisible(timerProgressBar)) {
          if (reset) {
            timerProgressBar.style.transition = "none";
            timerProgressBar.style.width = "100%";
          }
          setTimeout(() => {
            timerProgressBar.style.transition = "width ".concat(timer / 1e3, "s linear");
            timerProgressBar.style.width = "0%";
          }, 10);
        }
      }, "animateTimerProgressBar");
      const stopTimerProgressBar = /* @__PURE__ */ __name(() => {
        const timerProgressBar = getTimerProgressBar();
        const timerProgressBarWidth = parseInt(window.getComputedStyle(timerProgressBar).width);
        timerProgressBar.style.removeProperty("transition");
        timerProgressBar.style.width = "100%";
        const timerProgressBarFullWidth = parseInt(window.getComputedStyle(timerProgressBar).width);
        const timerProgressBarPercent = parseInt(timerProgressBarWidth / timerProgressBarFullWidth * 100);
        timerProgressBar.style.removeProperty("transition");
        timerProgressBar.style.width = "".concat(timerProgressBarPercent, "%");
      }, "stopTimerProgressBar");
      const isNodeEnv = /* @__PURE__ */ __name(() => typeof window === "undefined" || typeof document === "undefined", "isNodeEnv");
      const sweetHTML = '\n <div aria-labelledby="'.concat(swalClasses.title, '" aria-describedby="').concat(swalClasses["html-container"], '" class="').concat(swalClasses.popup, '" tabindex="-1">\n   <button type="button" class="').concat(swalClasses.close, '"></button>\n   <ul class="').concat(swalClasses["progress-steps"], '"></ul>\n   <div class="').concat(swalClasses.icon, '"></div>\n   <img class="').concat(swalClasses.image, '" />\n   <h2 class="').concat(swalClasses.title, '" id="').concat(swalClasses.title, '"></h2>\n   <div class="').concat(swalClasses["html-container"], '" id="').concat(swalClasses["html-container"], '"></div>\n   <input class="').concat(swalClasses.input, '" />\n   <input type="file" class="').concat(swalClasses.file, '" />\n   <div class="').concat(swalClasses.range, '">\n     <input type="range" />\n     <output></output>\n   </div>\n   <select class="').concat(swalClasses.select, '"></select>\n   <div class="').concat(swalClasses.radio, '"></div>\n   <label for="').concat(swalClasses.checkbox, '" class="').concat(swalClasses.checkbox, '">\n     <input type="checkbox" />\n     <span class="').concat(swalClasses.label, '"></span>\n   </label>\n   <textarea class="').concat(swalClasses.textarea, '"></textarea>\n   <div class="').concat(swalClasses["validation-message"], '" id="').concat(swalClasses["validation-message"], '"></div>\n   <div class="').concat(swalClasses.actions, '">\n     <div class="').concat(swalClasses.loader, '"></div>\n     <button type="button" class="').concat(swalClasses.confirm, '"></button>\n     <button type="button" class="').concat(swalClasses.deny, '"></button>\n     <button type="button" class="').concat(swalClasses.cancel, '"></button>\n   </div>\n   <div class="').concat(swalClasses.footer, '"></div>\n   <div class="').concat(swalClasses["timer-progress-bar-container"], '">\n     <div class="').concat(swalClasses["timer-progress-bar"], '"></div>\n   </div>\n </div>\n').replace(/(^|\n)\s*/g, "");
      const resetOldContainer = /* @__PURE__ */ __name(() => {
        const oldContainer = getContainer();
        if (!oldContainer) {
          return false;
        }
        oldContainer.remove();
        removeClass([document.documentElement, document.body], [swalClasses["no-backdrop"], swalClasses["toast-shown"], swalClasses["has-column"]]);
        return true;
      }, "resetOldContainer");
      const resetValidationMessage = /* @__PURE__ */ __name(() => {
        if (Swal2.isVisible()) {
          Swal2.resetValidationMessage();
        }
      }, "resetValidationMessage");
      const addInputChangeListeners = /* @__PURE__ */ __name(() => {
        const popup = getPopup();
        const input = getChildByClass(popup, swalClasses.input);
        const file = getChildByClass(popup, swalClasses.file);
        const range = popup.querySelector(".".concat(swalClasses.range, " input"));
        const rangeOutput = popup.querySelector(".".concat(swalClasses.range, " output"));
        const select = getChildByClass(popup, swalClasses.select);
        const checkbox = popup.querySelector(".".concat(swalClasses.checkbox, " input"));
        const textarea = getChildByClass(popup, swalClasses.textarea);
        input.oninput = resetValidationMessage;
        file.onchange = resetValidationMessage;
        select.onchange = resetValidationMessage;
        checkbox.onchange = resetValidationMessage;
        textarea.oninput = resetValidationMessage;
        range.oninput = () => {
          resetValidationMessage();
          rangeOutput.value = range.value;
        };
        range.onchange = () => {
          resetValidationMessage();
          range.nextSibling.value = range.value;
        };
      }, "addInputChangeListeners");
      const getTarget = /* @__PURE__ */ __name((target) => typeof target === "string" ? document.querySelector(target) : target, "getTarget");
      const setupAccessibility = /* @__PURE__ */ __name((params) => {
        const popup = getPopup();
        popup.setAttribute("role", params.toast ? "alert" : "dialog");
        popup.setAttribute("aria-live", params.toast ? "polite" : "assertive");
        if (!params.toast) {
          popup.setAttribute("aria-modal", "true");
        }
      }, "setupAccessibility");
      const setupRTL = /* @__PURE__ */ __name((targetElement) => {
        if (window.getComputedStyle(targetElement).direction === "rtl") {
          addClass(getContainer(), swalClasses.rtl);
        }
      }, "setupRTL");
      const init = /* @__PURE__ */ __name((params) => {
        const oldContainerExisted = resetOldContainer();
        if (isNodeEnv()) {
          error("SweetAlert2 requires document to initialize");
          return;
        }
        const container = document.createElement("div");
        container.className = swalClasses.container;
        if (oldContainerExisted) {
          addClass(container, swalClasses["no-transition"]);
        }
        setInnerHtml(container, sweetHTML);
        const targetElement = getTarget(params.target);
        targetElement.appendChild(container);
        setupAccessibility(params);
        setupRTL(targetElement);
        addInputChangeListeners();
      }, "init");
      const parseHtmlToContainer = /* @__PURE__ */ __name((param, target) => {
        if (param instanceof HTMLElement) {
          target.appendChild(param);
        } else if (typeof param === "object") {
          handleObject(param, target);
        } else if (param) {
          setInnerHtml(target, param);
        }
      }, "parseHtmlToContainer");
      const handleObject = /* @__PURE__ */ __name((param, target) => {
        if (param.jquery) {
          handleJqueryElem(target, param);
        } else {
          setInnerHtml(target, param.toString());
        }
      }, "handleObject");
      const handleJqueryElem = /* @__PURE__ */ __name((target, elem) => {
        target.textContent = "";
        if (0 in elem) {
          for (let i = 0; i in elem; i++) {
            target.appendChild(elem[i].cloneNode(true));
          }
        } else {
          target.appendChild(elem.cloneNode(true));
        }
      }, "handleJqueryElem");
      const animationEndEvent = (() => {
        if (isNodeEnv()) {
          return false;
        }
        const testEl = document.createElement("div");
        const transEndEventNames = {
          WebkitAnimation: "webkitAnimationEnd",
          OAnimation: "oAnimationEnd oanimationend",
          animation: "animationend"
        };
        for (const i in transEndEventNames) {
          if (Object.prototype.hasOwnProperty.call(transEndEventNames, i) && typeof testEl.style[i] !== "undefined") {
            return transEndEventNames[i];
          }
        }
        return false;
      })();
      const measureScrollbar = /* @__PURE__ */ __name(() => {
        const scrollDiv = document.createElement("div");
        scrollDiv.className = swalClasses["scrollbar-measure"];
        document.body.appendChild(scrollDiv);
        const scrollbarWidth = scrollDiv.getBoundingClientRect().width - scrollDiv.clientWidth;
        document.body.removeChild(scrollDiv);
        return scrollbarWidth;
      }, "measureScrollbar");
      const renderActions = /* @__PURE__ */ __name((instance, params) => {
        const actions = getActions();
        const loader = getLoader();
        if (!params.showConfirmButton && !params.showDenyButton && !params.showCancelButton) {
          hide(actions);
        } else {
          show(actions);
        }
        applyCustomClass(actions, params, "actions");
        renderButtons(actions, loader, params);
        setInnerHtml(loader, params.loaderHtml);
        applyCustomClass(loader, params, "loader");
      }, "renderActions");
      function renderButtons(actions, loader, params) {
        const confirmButton = getConfirmButton();
        const denyButton = getDenyButton();
        const cancelButton = getCancelButton();
        renderButton(confirmButton, "confirm", params);
        renderButton(denyButton, "deny", params);
        renderButton(cancelButton, "cancel", params);
        handleButtonsStyling(confirmButton, denyButton, cancelButton, params);
        if (params.reverseButtons) {
          if (params.toast) {
            actions.insertBefore(cancelButton, confirmButton);
            actions.insertBefore(denyButton, confirmButton);
          } else {
            actions.insertBefore(cancelButton, loader);
            actions.insertBefore(denyButton, loader);
            actions.insertBefore(confirmButton, loader);
          }
        }
      }
      __name(renderButtons, "renderButtons");
      function handleButtonsStyling(confirmButton, denyButton, cancelButton, params) {
        if (!params.buttonsStyling) {
          return removeClass([confirmButton, denyButton, cancelButton], swalClasses.styled);
        }
        addClass([confirmButton, denyButton, cancelButton], swalClasses.styled);
        if (params.confirmButtonColor) {
          confirmButton.style.backgroundColor = params.confirmButtonColor;
          addClass(confirmButton, swalClasses["default-outline"]);
        }
        if (params.denyButtonColor) {
          denyButton.style.backgroundColor = params.denyButtonColor;
          addClass(denyButton, swalClasses["default-outline"]);
        }
        if (params.cancelButtonColor) {
          cancelButton.style.backgroundColor = params.cancelButtonColor;
          addClass(cancelButton, swalClasses["default-outline"]);
        }
      }
      __name(handleButtonsStyling, "handleButtonsStyling");
      function renderButton(button, buttonType, params) {
        toggle(button, params["show".concat(capitalizeFirstLetter(buttonType), "Button")], "inline-block");
        setInnerHtml(button, params["".concat(buttonType, "ButtonText")]);
        button.setAttribute("aria-label", params["".concat(buttonType, "ButtonAriaLabel")]);
        button.className = swalClasses[buttonType];
        applyCustomClass(button, params, "".concat(buttonType, "Button"));
        addClass(button, params["".concat(buttonType, "ButtonClass")]);
      }
      __name(renderButton, "renderButton");
      function handleBackdropParam(container, backdrop) {
        if (typeof backdrop === "string") {
          container.style.background = backdrop;
        } else if (!backdrop) {
          addClass([document.documentElement, document.body], swalClasses["no-backdrop"]);
        }
      }
      __name(handleBackdropParam, "handleBackdropParam");
      function handlePositionParam(container, position) {
        if (position in swalClasses) {
          addClass(container, swalClasses[position]);
        } else {
          warn('The "position" parameter is not valid, defaulting to "center"');
          addClass(container, swalClasses.center);
        }
      }
      __name(handlePositionParam, "handlePositionParam");
      function handleGrowParam(container, grow) {
        if (grow && typeof grow === "string") {
          const growClass = "grow-".concat(grow);
          if (growClass in swalClasses) {
            addClass(container, swalClasses[growClass]);
          }
        }
      }
      __name(handleGrowParam, "handleGrowParam");
      const renderContainer = /* @__PURE__ */ __name((instance, params) => {
        const container = getContainer();
        if (!container) {
          return;
        }
        handleBackdropParam(container, params.backdrop);
        handlePositionParam(container, params.position);
        handleGrowParam(container, params.grow);
        applyCustomClass(container, params, "container");
      }, "renderContainer");
      var privateProps = {
        awaitingPromise: new WeakMap(),
        promise: new WeakMap(),
        innerParams: new WeakMap(),
        domCache: new WeakMap()
      };
      const inputTypes = ["input", "file", "range", "select", "radio", "checkbox", "textarea"];
      const renderInput = /* @__PURE__ */ __name((instance, params) => {
        const popup = getPopup();
        const innerParams = privateProps.innerParams.get(instance);
        const rerender = !innerParams || params.input !== innerParams.input;
        inputTypes.forEach((inputType) => {
          const inputClass = swalClasses[inputType];
          const inputContainer = getChildByClass(popup, inputClass);
          setAttributes(inputType, params.inputAttributes);
          inputContainer.className = inputClass;
          if (rerender) {
            hide(inputContainer);
          }
        });
        if (params.input) {
          if (rerender) {
            showInput(params);
          }
          setCustomClass(params);
        }
      }, "renderInput");
      const showInput = /* @__PURE__ */ __name((params) => {
        if (!renderInputType[params.input]) {
          return error('Unexpected type of input! Expected "text", "email", "password", "number", "tel", "select", "radio", "checkbox", "textarea", "file" or "url", got "'.concat(params.input, '"'));
        }
        const inputContainer = getInputContainer(params.input);
        const input = renderInputType[params.input](inputContainer, params);
        show(input);
        setTimeout(() => {
          focusInput(input);
        });
      }, "showInput");
      const removeAttributes = /* @__PURE__ */ __name((input) => {
        for (let i = 0; i < input.attributes.length; i++) {
          const attrName = input.attributes[i].name;
          if (!["type", "value", "style"].includes(attrName)) {
            input.removeAttribute(attrName);
          }
        }
      }, "removeAttributes");
      const setAttributes = /* @__PURE__ */ __name((inputType, inputAttributes) => {
        const input = getInput(getPopup(), inputType);
        if (!input) {
          return;
        }
        removeAttributes(input);
        for (const attr in inputAttributes) {
          input.setAttribute(attr, inputAttributes[attr]);
        }
      }, "setAttributes");
      const setCustomClass = /* @__PURE__ */ __name((params) => {
        const inputContainer = getInputContainer(params.input);
        if (params.customClass) {
          addClass(inputContainer, params.customClass.input);
        }
      }, "setCustomClass");
      const setInputPlaceholder = /* @__PURE__ */ __name((input, params) => {
        if (!input.placeholder || params.inputPlaceholder) {
          input.placeholder = params.inputPlaceholder;
        }
      }, "setInputPlaceholder");
      const setInputLabel = /* @__PURE__ */ __name((input, prependTo, params) => {
        if (params.inputLabel) {
          input.id = swalClasses.input;
          const label = document.createElement("label");
          const labelClass = swalClasses["input-label"];
          label.setAttribute("for", input.id);
          label.className = labelClass;
          addClass(label, params.customClass.inputLabel);
          label.innerText = params.inputLabel;
          prependTo.insertAdjacentElement("beforebegin", label);
        }
      }, "setInputLabel");
      const getInputContainer = /* @__PURE__ */ __name((inputType) => {
        const inputClass = swalClasses[inputType] ? swalClasses[inputType] : swalClasses.input;
        return getChildByClass(getPopup(), inputClass);
      }, "getInputContainer");
      const renderInputType = {};
      renderInputType.text = renderInputType.email = renderInputType.password = renderInputType.number = renderInputType.tel = renderInputType.url = (input, params) => {
        if (typeof params.inputValue === "string" || typeof params.inputValue === "number") {
          input.value = params.inputValue;
        } else if (!isPromise(params.inputValue)) {
          warn('Unexpected type of inputValue! Expected "string", "number" or "Promise", got "'.concat(typeof params.inputValue, '"'));
        }
        setInputLabel(input, input, params);
        setInputPlaceholder(input, params);
        input.type = params.input;
        return input;
      };
      renderInputType.file = (input, params) => {
        setInputLabel(input, input, params);
        setInputPlaceholder(input, params);
        return input;
      };
      renderInputType.range = (range, params) => {
        const rangeInput = range.querySelector("input");
        const rangeOutput = range.querySelector("output");
        rangeInput.value = params.inputValue;
        rangeInput.type = params.input;
        rangeOutput.value = params.inputValue;
        setInputLabel(rangeInput, range, params);
        return range;
      };
      renderInputType.select = (select, params) => {
        select.textContent = "";
        if (params.inputPlaceholder) {
          const placeholder = document.createElement("option");
          setInnerHtml(placeholder, params.inputPlaceholder);
          placeholder.value = "";
          placeholder.disabled = true;
          placeholder.selected = true;
          select.appendChild(placeholder);
        }
        setInputLabel(select, select, params);
        return select;
      };
      renderInputType.radio = (radio) => {
        radio.textContent = "";
        return radio;
      };
      renderInputType.checkbox = (checkboxContainer, params) => {
        const checkbox = getInput(getPopup(), "checkbox");
        checkbox.value = 1;
        checkbox.id = swalClasses.checkbox;
        checkbox.checked = Boolean(params.inputValue);
        const label = checkboxContainer.querySelector("span");
        setInnerHtml(label, params.inputPlaceholder);
        return checkboxContainer;
      };
      renderInputType.textarea = (textarea, params) => {
        textarea.value = params.inputValue;
        setInputPlaceholder(textarea, params);
        setInputLabel(textarea, textarea, params);
        const getMargin = /* @__PURE__ */ __name((el) => parseInt(window.getComputedStyle(el).marginLeft) + parseInt(window.getComputedStyle(el).marginRight), "getMargin");
        setTimeout(() => {
          if ("MutationObserver" in window) {
            const initialPopupWidth = parseInt(window.getComputedStyle(getPopup()).width);
            const textareaResizeHandler = /* @__PURE__ */ __name(() => {
              const textareaWidth = textarea.offsetWidth + getMargin(textarea);
              if (textareaWidth > initialPopupWidth) {
                getPopup().style.width = "".concat(textareaWidth, "px");
              } else {
                getPopup().style.width = null;
              }
            }, "textareaResizeHandler");
            new MutationObserver(textareaResizeHandler).observe(textarea, {
              attributes: true,
              attributeFilter: ["style"]
            });
          }
        });
        return textarea;
      };
      const renderContent = /* @__PURE__ */ __name((instance, params) => {
        const htmlContainer = getHtmlContainer();
        applyCustomClass(htmlContainer, params, "htmlContainer");
        if (params.html) {
          parseHtmlToContainer(params.html, htmlContainer);
          show(htmlContainer, "block");
        } else if (params.text) {
          htmlContainer.textContent = params.text;
          show(htmlContainer, "block");
        } else {
          hide(htmlContainer);
        }
        renderInput(instance, params);
      }, "renderContent");
      const renderFooter = /* @__PURE__ */ __name((instance, params) => {
        const footer = getFooter();
        toggle(footer, params.footer);
        if (params.footer) {
          parseHtmlToContainer(params.footer, footer);
        }
        applyCustomClass(footer, params, "footer");
      }, "renderFooter");
      const renderCloseButton = /* @__PURE__ */ __name((instance, params) => {
        const closeButton = getCloseButton();
        setInnerHtml(closeButton, params.closeButtonHtml);
        applyCustomClass(closeButton, params, "closeButton");
        toggle(closeButton, params.showCloseButton);
        closeButton.setAttribute("aria-label", params.closeButtonAriaLabel);
      }, "renderCloseButton");
      const renderIcon = /* @__PURE__ */ __name((instance, params) => {
        const innerParams = privateProps.innerParams.get(instance);
        const icon = getIcon();
        if (innerParams && params.icon === innerParams.icon) {
          setContent(icon, params);
          applyStyles(icon, params);
          return;
        }
        if (!params.icon && !params.iconHtml) {
          return hide(icon);
        }
        if (params.icon && Object.keys(iconTypes).indexOf(params.icon) === -1) {
          error('Unknown icon! Expected "success", "error", "warning", "info" or "question", got "'.concat(params.icon, '"'));
          return hide(icon);
        }
        show(icon);
        setContent(icon, params);
        applyStyles(icon, params);
        addClass(icon, params.showClass.icon);
      }, "renderIcon");
      const applyStyles = /* @__PURE__ */ __name((icon, params) => {
        for (const iconType in iconTypes) {
          if (params.icon !== iconType) {
            removeClass(icon, iconTypes[iconType]);
          }
        }
        addClass(icon, iconTypes[params.icon]);
        setColor(icon, params);
        adjustSuccessIconBackgoundColor();
        applyCustomClass(icon, params, "icon");
      }, "applyStyles");
      const adjustSuccessIconBackgoundColor = /* @__PURE__ */ __name(() => {
        const popup = getPopup();
        const popupBackgroundColor = window.getComputedStyle(popup).getPropertyValue("background-color");
        const successIconParts = popup.querySelectorAll("[class^=swal2-success-circular-line], .swal2-success-fix");
        for (let i = 0; i < successIconParts.length; i++) {
          successIconParts[i].style.backgroundColor = popupBackgroundColor;
        }
      }, "adjustSuccessIconBackgoundColor");
      const setContent = /* @__PURE__ */ __name((icon, params) => {
        icon.textContent = "";
        if (params.iconHtml) {
          setInnerHtml(icon, iconContent(params.iconHtml));
        } else if (params.icon === "success") {
          setInnerHtml(icon, '\n      <div class="swal2-success-circular-line-left"></div>\n      <span class="swal2-success-line-tip"></span> <span class="swal2-success-line-long"></span>\n      <div class="swal2-success-ring"></div> <div class="swal2-success-fix"></div>\n      <div class="swal2-success-circular-line-right"></div>\n    ');
        } else if (params.icon === "error") {
          setInnerHtml(icon, '\n      <span class="swal2-x-mark">\n        <span class="swal2-x-mark-line-left"></span>\n        <span class="swal2-x-mark-line-right"></span>\n      </span>\n    ');
        } else {
          const defaultIconHtml = {
            question: "?",
            warning: "!",
            info: "i"
          };
          setInnerHtml(icon, iconContent(defaultIconHtml[params.icon]));
        }
      }, "setContent");
      const setColor = /* @__PURE__ */ __name((icon, params) => {
        if (!params.iconColor) {
          return;
        }
        icon.style.color = params.iconColor;
        icon.style.borderColor = params.iconColor;
        for (const sel of [".swal2-success-line-tip", ".swal2-success-line-long", ".swal2-x-mark-line-left", ".swal2-x-mark-line-right"]) {
          setStyle(icon, sel, "backgroundColor", params.iconColor);
        }
        setStyle(icon, ".swal2-success-ring", "borderColor", params.iconColor);
      }, "setColor");
      const iconContent = /* @__PURE__ */ __name((content) => '<div class="'.concat(swalClasses["icon-content"], '">').concat(content, "</div>"), "iconContent");
      const renderImage = /* @__PURE__ */ __name((instance, params) => {
        const image = getImage();
        if (!params.imageUrl) {
          return hide(image);
        }
        show(image, "");
        image.setAttribute("src", params.imageUrl);
        image.setAttribute("alt", params.imageAlt);
        applyNumericalStyle(image, "width", params.imageWidth);
        applyNumericalStyle(image, "height", params.imageHeight);
        image.className = swalClasses.image;
        applyCustomClass(image, params, "image");
      }, "renderImage");
      const createStepElement = /* @__PURE__ */ __name((step) => {
        const stepEl = document.createElement("li");
        addClass(stepEl, swalClasses["progress-step"]);
        setInnerHtml(stepEl, step);
        return stepEl;
      }, "createStepElement");
      const createLineElement = /* @__PURE__ */ __name((params) => {
        const lineEl = document.createElement("li");
        addClass(lineEl, swalClasses["progress-step-line"]);
        if (params.progressStepsDistance) {
          lineEl.style.width = params.progressStepsDistance;
        }
        return lineEl;
      }, "createLineElement");
      const renderProgressSteps = /* @__PURE__ */ __name((instance, params) => {
        const progressStepsContainer = getProgressSteps();
        if (!params.progressSteps || params.progressSteps.length === 0) {
          return hide(progressStepsContainer);
        }
        show(progressStepsContainer);
        progressStepsContainer.textContent = "";
        if (params.currentProgressStep >= params.progressSteps.length) {
          warn("Invalid currentProgressStep parameter, it should be less than progressSteps.length (currentProgressStep like JS arrays starts from 0)");
        }
        params.progressSteps.forEach((step, index) => {
          const stepEl = createStepElement(step);
          progressStepsContainer.appendChild(stepEl);
          if (index === params.currentProgressStep) {
            addClass(stepEl, swalClasses["active-progress-step"]);
          }
          if (index !== params.progressSteps.length - 1) {
            const lineEl = createLineElement(params);
            progressStepsContainer.appendChild(lineEl);
          }
        });
      }, "renderProgressSteps");
      const renderTitle = /* @__PURE__ */ __name((instance, params) => {
        const title = getTitle();
        toggle(title, params.title || params.titleText, "block");
        if (params.title) {
          parseHtmlToContainer(params.title, title);
        }
        if (params.titleText) {
          title.innerText = params.titleText;
        }
        applyCustomClass(title, params, "title");
      }, "renderTitle");
      const renderPopup = /* @__PURE__ */ __name((instance, params) => {
        const container = getContainer();
        const popup = getPopup();
        if (params.toast) {
          applyNumericalStyle(container, "width", params.width);
          popup.style.width = "100%";
          popup.insertBefore(getLoader(), getIcon());
        } else {
          applyNumericalStyle(popup, "width", params.width);
        }
        applyNumericalStyle(popup, "padding", params.padding);
        if (params.background) {
          popup.style.background = params.background;
        }
        hide(getValidationMessage());
        addClasses(popup, params);
      }, "renderPopup");
      const addClasses = /* @__PURE__ */ __name((popup, params) => {
        popup.className = "".concat(swalClasses.popup, " ").concat(isVisible(popup) ? params.showClass.popup : "");
        if (params.toast) {
          addClass([document.documentElement, document.body], swalClasses["toast-shown"]);
          addClass(popup, swalClasses.toast);
        } else {
          addClass(popup, swalClasses.modal);
        }
        applyCustomClass(popup, params, "popup");
        if (typeof params.customClass === "string") {
          addClass(popup, params.customClass);
        }
        if (params.icon) {
          addClass(popup, swalClasses["icon-".concat(params.icon)]);
        }
      }, "addClasses");
      const render = /* @__PURE__ */ __name((instance, params) => {
        renderPopup(instance, params);
        renderContainer(instance, params);
        renderProgressSteps(instance, params);
        renderIcon(instance, params);
        renderImage(instance, params);
        renderTitle(instance, params);
        renderCloseButton(instance, params);
        renderContent(instance, params);
        renderActions(instance, params);
        renderFooter(instance, params);
        if (typeof params.didRender === "function") {
          params.didRender(getPopup());
        }
      }, "render");
      const isVisible$1 = /* @__PURE__ */ __name(() => {
        return isVisible(getPopup());
      }, "isVisible$1");
      const clickConfirm = /* @__PURE__ */ __name(() => getConfirmButton() && getConfirmButton().click(), "clickConfirm");
      const clickDeny = /* @__PURE__ */ __name(() => getDenyButton() && getDenyButton().click(), "clickDeny");
      const clickCancel = /* @__PURE__ */ __name(() => getCancelButton() && getCancelButton().click(), "clickCancel");
      function fire() {
        const Swal3 = this;
        for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
          args[_key] = arguments[_key];
        }
        return new Swal3(...args);
      }
      __name(fire, "fire");
      function mixin(mixinParams) {
        class MixinSwal extends this {
          _main(params, priorityMixinParams) {
            return super._main(params, Object.assign({}, mixinParams, priorityMixinParams));
          }
        }
        __name(MixinSwal, "MixinSwal");
        return MixinSwal;
      }
      __name(mixin, "mixin");
      const showLoading = /* @__PURE__ */ __name((buttonToReplace) => {
        let popup = getPopup();
        if (!popup) {
          Swal2.fire();
        }
        popup = getPopup();
        const loader = getLoader();
        if (isToast()) {
          hide(getIcon());
        } else {
          replaceButton(popup, buttonToReplace);
        }
        show(loader);
        popup.setAttribute("data-loading", true);
        popup.setAttribute("aria-busy", true);
        popup.focus();
      }, "showLoading");
      const replaceButton = /* @__PURE__ */ __name((popup, buttonToReplace) => {
        const actions = getActions();
        const loader = getLoader();
        if (!buttonToReplace && isVisible(getConfirmButton())) {
          buttonToReplace = getConfirmButton();
        }
        show(actions);
        if (buttonToReplace) {
          hide(buttonToReplace);
          loader.setAttribute("data-button-to-replace", buttonToReplace.className);
        }
        loader.parentNode.insertBefore(loader, buttonToReplace);
        addClass([popup, actions], swalClasses.loading);
      }, "replaceButton");
      const RESTORE_FOCUS_TIMEOUT = 100;
      const globalState = {};
      const focusPreviousActiveElement = /* @__PURE__ */ __name(() => {
        if (globalState.previousActiveElement && globalState.previousActiveElement.focus) {
          globalState.previousActiveElement.focus();
          globalState.previousActiveElement = null;
        } else if (document.body) {
          document.body.focus();
        }
      }, "focusPreviousActiveElement");
      const restoreActiveElement = /* @__PURE__ */ __name((returnFocus) => {
        return new Promise((resolve) => {
          if (!returnFocus) {
            return resolve();
          }
          const x = window.scrollX;
          const y = window.scrollY;
          globalState.restoreFocusTimeout = setTimeout(() => {
            focusPreviousActiveElement();
            resolve();
          }, RESTORE_FOCUS_TIMEOUT);
          window.scrollTo(x, y);
        });
      }, "restoreActiveElement");
      const getTimerLeft = /* @__PURE__ */ __name(() => {
        return globalState.timeout && globalState.timeout.getTimerLeft();
      }, "getTimerLeft");
      const stopTimer = /* @__PURE__ */ __name(() => {
        if (globalState.timeout) {
          stopTimerProgressBar();
          return globalState.timeout.stop();
        }
      }, "stopTimer");
      const resumeTimer = /* @__PURE__ */ __name(() => {
        if (globalState.timeout) {
          const remaining = globalState.timeout.start();
          animateTimerProgressBar(remaining);
          return remaining;
        }
      }, "resumeTimer");
      const toggleTimer = /* @__PURE__ */ __name(() => {
        const timer = globalState.timeout;
        return timer && (timer.running ? stopTimer() : resumeTimer());
      }, "toggleTimer");
      const increaseTimer = /* @__PURE__ */ __name((n) => {
        if (globalState.timeout) {
          const remaining = globalState.timeout.increase(n);
          animateTimerProgressBar(remaining, true);
          return remaining;
        }
      }, "increaseTimer");
      const isTimerRunning = /* @__PURE__ */ __name(() => {
        return globalState.timeout && globalState.timeout.isRunning();
      }, "isTimerRunning");
      let bodyClickListenerAdded = false;
      const clickHandlers = {};
      function bindClickHandler() {
        let attr = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : "data-swal-template";
        clickHandlers[attr] = this;
        if (!bodyClickListenerAdded) {
          document.body.addEventListener("click", bodyClickListener);
          bodyClickListenerAdded = true;
        }
      }
      __name(bindClickHandler, "bindClickHandler");
      const bodyClickListener = /* @__PURE__ */ __name((event) => {
        for (let el = event.target; el && el !== document; el = el.parentNode) {
          for (const attr in clickHandlers) {
            const template = el.getAttribute(attr);
            if (template) {
              clickHandlers[attr].fire({
                template
              });
              return;
            }
          }
        }
      }, "bodyClickListener");
      const defaultParams = {
        title: "",
        titleText: "",
        text: "",
        html: "",
        footer: "",
        icon: void 0,
        iconColor: void 0,
        iconHtml: void 0,
        template: void 0,
        toast: false,
        showClass: {
          popup: "swal2-show",
          backdrop: "swal2-backdrop-show",
          icon: "swal2-icon-show"
        },
        hideClass: {
          popup: "swal2-hide",
          backdrop: "swal2-backdrop-hide",
          icon: "swal2-icon-hide"
        },
        customClass: {},
        target: "body",
        backdrop: true,
        heightAuto: true,
        allowOutsideClick: true,
        allowEscapeKey: true,
        allowEnterKey: true,
        stopKeydownPropagation: true,
        keydownListenerCapture: false,
        showConfirmButton: true,
        showDenyButton: false,
        showCancelButton: false,
        preConfirm: void 0,
        preDeny: void 0,
        confirmButtonText: "OK",
        confirmButtonAriaLabel: "",
        confirmButtonColor: void 0,
        denyButtonText: "No",
        denyButtonAriaLabel: "",
        denyButtonColor: void 0,
        cancelButtonText: "Cancel",
        cancelButtonAriaLabel: "",
        cancelButtonColor: void 0,
        buttonsStyling: true,
        reverseButtons: false,
        focusConfirm: true,
        focusDeny: false,
        focusCancel: false,
        returnFocus: true,
        showCloseButton: false,
        closeButtonHtml: "&times;",
        closeButtonAriaLabel: "Close this dialog",
        loaderHtml: "",
        showLoaderOnConfirm: false,
        showLoaderOnDeny: false,
        imageUrl: void 0,
        imageWidth: void 0,
        imageHeight: void 0,
        imageAlt: "",
        timer: void 0,
        timerProgressBar: false,
        width: void 0,
        padding: void 0,
        background: void 0,
        input: void 0,
        inputPlaceholder: "",
        inputLabel: "",
        inputValue: "",
        inputOptions: {},
        inputAutoTrim: true,
        inputAttributes: {},
        inputValidator: void 0,
        returnInputValueOnDeny: false,
        validationMessage: void 0,
        grow: false,
        position: "center",
        progressSteps: [],
        currentProgressStep: void 0,
        progressStepsDistance: void 0,
        willOpen: void 0,
        didOpen: void 0,
        didRender: void 0,
        willClose: void 0,
        didClose: void 0,
        didDestroy: void 0,
        scrollbarPadding: true
      };
      const updatableParams = ["allowEscapeKey", "allowOutsideClick", "background", "buttonsStyling", "cancelButtonAriaLabel", "cancelButtonColor", "cancelButtonText", "closeButtonAriaLabel", "closeButtonHtml", "confirmButtonAriaLabel", "confirmButtonColor", "confirmButtonText", "currentProgressStep", "customClass", "denyButtonAriaLabel", "denyButtonColor", "denyButtonText", "didClose", "didDestroy", "footer", "hideClass", "html", "icon", "iconColor", "iconHtml", "imageAlt", "imageHeight", "imageUrl", "imageWidth", "preConfirm", "preDeny", "progressSteps", "returnFocus", "reverseButtons", "showCancelButton", "showCloseButton", "showConfirmButton", "showDenyButton", "text", "title", "titleText", "willClose"];
      const deprecatedParams = {};
      const toastIncompatibleParams = ["allowOutsideClick", "allowEnterKey", "backdrop", "focusConfirm", "focusDeny", "focusCancel", "returnFocus", "heightAuto", "keydownListenerCapture"];
      const isValidParameter = /* @__PURE__ */ __name((paramName) => {
        return Object.prototype.hasOwnProperty.call(defaultParams, paramName);
      }, "isValidParameter");
      const isUpdatableParameter = /* @__PURE__ */ __name((paramName) => {
        return updatableParams.indexOf(paramName) !== -1;
      }, "isUpdatableParameter");
      const isDeprecatedParameter = /* @__PURE__ */ __name((paramName) => {
        return deprecatedParams[paramName];
      }, "isDeprecatedParameter");
      const checkIfParamIsValid = /* @__PURE__ */ __name((param) => {
        if (!isValidParameter(param)) {
          warn('Unknown parameter "'.concat(param, '"'));
        }
      }, "checkIfParamIsValid");
      const checkIfToastParamIsValid = /* @__PURE__ */ __name((param) => {
        if (toastIncompatibleParams.includes(param)) {
          warn('The parameter "'.concat(param, '" is incompatible with toasts'));
        }
      }, "checkIfToastParamIsValid");
      const checkIfParamIsDeprecated = /* @__PURE__ */ __name((param) => {
        if (isDeprecatedParameter(param)) {
          warnAboutDeprecation(param, isDeprecatedParameter(param));
        }
      }, "checkIfParamIsDeprecated");
      const showWarningsForParams = /* @__PURE__ */ __name((params) => {
        if (!params.backdrop && params.allowOutsideClick) {
          warn('"allowOutsideClick" parameter requires `backdrop` parameter to be set to `true`');
        }
        for (const param in params) {
          checkIfParamIsValid(param);
          if (params.toast) {
            checkIfToastParamIsValid(param);
          }
          checkIfParamIsDeprecated(param);
        }
      }, "showWarningsForParams");
      var staticMethods = /* @__PURE__ */ Object.freeze({
        isValidParameter,
        isUpdatableParameter,
        isDeprecatedParameter,
        argsToParams,
        isVisible: isVisible$1,
        clickConfirm,
        clickDeny,
        clickCancel,
        getContainer,
        getPopup,
        getTitle,
        getHtmlContainer,
        getImage,
        getIcon,
        getInputLabel,
        getCloseButton,
        getActions,
        getConfirmButton,
        getDenyButton,
        getCancelButton,
        getLoader,
        getFooter,
        getTimerProgressBar,
        getFocusableElements,
        getValidationMessage,
        isLoading,
        fire,
        mixin,
        showLoading,
        enableLoading: showLoading,
        getTimerLeft,
        stopTimer,
        resumeTimer,
        toggleTimer,
        increaseTimer,
        isTimerRunning,
        bindClickHandler
      });
      function hideLoading() {
        const innerParams = privateProps.innerParams.get(this);
        if (!innerParams) {
          return;
        }
        const domCache = privateProps.domCache.get(this);
        hide(domCache.loader);
        if (isToast()) {
          if (innerParams.icon) {
            show(getIcon());
          }
        } else {
          showRelatedButton(domCache);
        }
        removeClass([domCache.popup, domCache.actions], swalClasses.loading);
        domCache.popup.removeAttribute("aria-busy");
        domCache.popup.removeAttribute("data-loading");
        domCache.confirmButton.disabled = false;
        domCache.denyButton.disabled = false;
        domCache.cancelButton.disabled = false;
      }
      __name(hideLoading, "hideLoading");
      const showRelatedButton = /* @__PURE__ */ __name((domCache) => {
        const buttonToReplace = domCache.popup.getElementsByClassName(domCache.loader.getAttribute("data-button-to-replace"));
        if (buttonToReplace.length) {
          show(buttonToReplace[0], "inline-block");
        } else if (allButtonsAreHidden()) {
          hide(domCache.actions);
        }
      }, "showRelatedButton");
      function getInput$1(instance) {
        const innerParams = privateProps.innerParams.get(instance || this);
        const domCache = privateProps.domCache.get(instance || this);
        if (!domCache) {
          return null;
        }
        return getInput(domCache.popup, innerParams.input);
      }
      __name(getInput$1, "getInput$1");
      const fixScrollbar = /* @__PURE__ */ __name(() => {
        if (states.previousBodyPadding !== null) {
          return;
        }
        if (document.body.scrollHeight > window.innerHeight) {
          states.previousBodyPadding = parseInt(window.getComputedStyle(document.body).getPropertyValue("padding-right"));
          document.body.style.paddingRight = "".concat(states.previousBodyPadding + measureScrollbar(), "px");
        }
      }, "fixScrollbar");
      const undoScrollbar = /* @__PURE__ */ __name(() => {
        if (states.previousBodyPadding !== null) {
          document.body.style.paddingRight = "".concat(states.previousBodyPadding, "px");
          states.previousBodyPadding = null;
        }
      }, "undoScrollbar");
      const iOSfix = /* @__PURE__ */ __name(() => {
        const iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream || navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1;
        if (iOS && !hasClass(document.body, swalClasses.iosfix)) {
          const offset = document.body.scrollTop;
          document.body.style.top = "".concat(offset * -1, "px");
          addClass(document.body, swalClasses.iosfix);
          lockBodyScroll();
          addBottomPaddingForTallPopups();
        }
      }, "iOSfix");
      const addBottomPaddingForTallPopups = /* @__PURE__ */ __name(() => {
        const safari = !navigator.userAgent.match(/(CriOS|FxiOS|EdgiOS|YaBrowser|UCBrowser)/i);
        if (safari) {
          const bottomPanelHeight = 44;
          if (getPopup().scrollHeight > window.innerHeight - bottomPanelHeight) {
            getContainer().style.paddingBottom = "".concat(bottomPanelHeight, "px");
          }
        }
      }, "addBottomPaddingForTallPopups");
      const lockBodyScroll = /* @__PURE__ */ __name(() => {
        const container = getContainer();
        let preventTouchMove;
        container.ontouchstart = (e) => {
          preventTouchMove = shouldPreventTouchMove(e);
        };
        container.ontouchmove = (e) => {
          if (preventTouchMove) {
            e.preventDefault();
            e.stopPropagation();
          }
        };
      }, "lockBodyScroll");
      const shouldPreventTouchMove = /* @__PURE__ */ __name((event) => {
        const target = event.target;
        const container = getContainer();
        if (isStylys(event) || isZoom(event)) {
          return false;
        }
        if (target === container) {
          return true;
        }
        if (!isScrollable(container) && target.tagName !== "INPUT" && target.tagName !== "TEXTAREA" && !(isScrollable(getHtmlContainer()) && getHtmlContainer().contains(target))) {
          return true;
        }
        return false;
      }, "shouldPreventTouchMove");
      const isStylys = /* @__PURE__ */ __name((event) => {
        return event.touches && event.touches.length && event.touches[0].touchType === "stylus";
      }, "isStylys");
      const isZoom = /* @__PURE__ */ __name((event) => {
        return event.touches && event.touches.length > 1;
      }, "isZoom");
      const undoIOSfix = /* @__PURE__ */ __name(() => {
        if (hasClass(document.body, swalClasses.iosfix)) {
          const offset = parseInt(document.body.style.top, 10);
          removeClass(document.body, swalClasses.iosfix);
          document.body.style.top = "";
          document.body.scrollTop = offset * -1;
        }
      }, "undoIOSfix");
      const setAriaHidden = /* @__PURE__ */ __name(() => {
        const bodyChildren = toArray(document.body.children);
        bodyChildren.forEach((el) => {
          if (el === getContainer() || el.contains(getContainer())) {
            return;
          }
          if (el.hasAttribute("aria-hidden")) {
            el.setAttribute("data-previous-aria-hidden", el.getAttribute("aria-hidden"));
          }
          el.setAttribute("aria-hidden", "true");
        });
      }, "setAriaHidden");
      const unsetAriaHidden = /* @__PURE__ */ __name(() => {
        const bodyChildren = toArray(document.body.children);
        bodyChildren.forEach((el) => {
          if (el.hasAttribute("data-previous-aria-hidden")) {
            el.setAttribute("aria-hidden", el.getAttribute("data-previous-aria-hidden"));
            el.removeAttribute("data-previous-aria-hidden");
          } else {
            el.removeAttribute("aria-hidden");
          }
        });
      }, "unsetAriaHidden");
      var privateMethods = {
        swalPromiseResolve: new WeakMap(),
        swalPromiseReject: new WeakMap()
      };
      function removePopupAndResetState(instance, container, returnFocus, didClose) {
        if (isToast()) {
          triggerDidCloseAndDispose(instance, didClose);
        } else {
          restoreActiveElement(returnFocus).then(() => triggerDidCloseAndDispose(instance, didClose));
          globalState.keydownTarget.removeEventListener("keydown", globalState.keydownHandler, {
            capture: globalState.keydownListenerCapture
          });
          globalState.keydownHandlerAdded = false;
        }
        const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
        if (isSafari) {
          container.setAttribute("style", "display:none !important");
          container.removeAttribute("class");
          container.innerHTML = "";
        } else {
          container.remove();
        }
        if (isModal()) {
          undoScrollbar();
          undoIOSfix();
          unsetAriaHidden();
        }
        removeBodyClasses();
      }
      __name(removePopupAndResetState, "removePopupAndResetState");
      function removeBodyClasses() {
        removeClass([document.documentElement, document.body], [swalClasses.shown, swalClasses["height-auto"], swalClasses["no-backdrop"], swalClasses["toast-shown"]]);
      }
      __name(removeBodyClasses, "removeBodyClasses");
      function close(resolveValue) {
        resolveValue = prepareResolveValue(resolveValue);
        const swalPromiseResolve = privateMethods.swalPromiseResolve.get(this);
        const didClose = triggerClosePopup(this);
        if (this.isAwaitingPromise()) {
          if (!resolveValue.isDismissed) {
            handleAwaitingPromise(this);
            swalPromiseResolve(resolveValue);
          }
        } else if (didClose) {
          swalPromiseResolve(resolveValue);
        }
      }
      __name(close, "close");
      function isAwaitingPromise() {
        return !!privateProps.awaitingPromise.get(this);
      }
      __name(isAwaitingPromise, "isAwaitingPromise");
      const triggerClosePopup = /* @__PURE__ */ __name((instance) => {
        const popup = getPopup();
        if (!popup) {
          return false;
        }
        const innerParams = privateProps.innerParams.get(instance);
        if (!innerParams || hasClass(popup, innerParams.hideClass.popup)) {
          return false;
        }
        removeClass(popup, innerParams.showClass.popup);
        addClass(popup, innerParams.hideClass.popup);
        const backdrop = getContainer();
        removeClass(backdrop, innerParams.showClass.backdrop);
        addClass(backdrop, innerParams.hideClass.backdrop);
        handlePopupAnimation(instance, popup, innerParams);
        return true;
      }, "triggerClosePopup");
      function rejectPromise(error2) {
        const rejectPromise2 = privateMethods.swalPromiseReject.get(this);
        handleAwaitingPromise(this);
        if (rejectPromise2) {
          rejectPromise2(error2);
        }
      }
      __name(rejectPromise, "rejectPromise");
      const handleAwaitingPromise = /* @__PURE__ */ __name((instance) => {
        if (instance.isAwaitingPromise()) {
          privateProps.awaitingPromise.delete(instance);
          if (!privateProps.innerParams.get(instance)) {
            instance._destroy();
          }
        }
      }, "handleAwaitingPromise");
      const prepareResolveValue = /* @__PURE__ */ __name((resolveValue) => {
        if (typeof resolveValue === "undefined") {
          return {
            isConfirmed: false,
            isDenied: false,
            isDismissed: true
          };
        }
        return Object.assign({
          isConfirmed: false,
          isDenied: false,
          isDismissed: false
        }, resolveValue);
      }, "prepareResolveValue");
      const handlePopupAnimation = /* @__PURE__ */ __name((instance, popup, innerParams) => {
        const container = getContainer();
        const animationIsSupported = animationEndEvent && hasCssAnimation(popup);
        if (typeof innerParams.willClose === "function") {
          innerParams.willClose(popup);
        }
        if (animationIsSupported) {
          animatePopup(instance, popup, container, innerParams.returnFocus, innerParams.didClose);
        } else {
          removePopupAndResetState(instance, container, innerParams.returnFocus, innerParams.didClose);
        }
      }, "handlePopupAnimation");
      const animatePopup = /* @__PURE__ */ __name((instance, popup, container, returnFocus, didClose) => {
        globalState.swalCloseEventFinishedCallback = removePopupAndResetState.bind(null, instance, container, returnFocus, didClose);
        popup.addEventListener(animationEndEvent, function(e) {
          if (e.target === popup) {
            globalState.swalCloseEventFinishedCallback();
            delete globalState.swalCloseEventFinishedCallback;
          }
        });
      }, "animatePopup");
      const triggerDidCloseAndDispose = /* @__PURE__ */ __name((instance, didClose) => {
        setTimeout(() => {
          if (typeof didClose === "function") {
            didClose.bind(instance.params)();
          }
          instance._destroy();
        });
      }, "triggerDidCloseAndDispose");
      function setButtonsDisabled(instance, buttons, disabled) {
        const domCache = privateProps.domCache.get(instance);
        buttons.forEach((button) => {
          domCache[button].disabled = disabled;
        });
      }
      __name(setButtonsDisabled, "setButtonsDisabled");
      function setInputDisabled(input, disabled) {
        if (!input) {
          return false;
        }
        if (input.type === "radio") {
          const radiosContainer = input.parentNode.parentNode;
          const radios = radiosContainer.querySelectorAll("input");
          for (let i = 0; i < radios.length; i++) {
            radios[i].disabled = disabled;
          }
        } else {
          input.disabled = disabled;
        }
      }
      __name(setInputDisabled, "setInputDisabled");
      function enableButtons() {
        setButtonsDisabled(this, ["confirmButton", "denyButton", "cancelButton"], false);
      }
      __name(enableButtons, "enableButtons");
      function disableButtons() {
        setButtonsDisabled(this, ["confirmButton", "denyButton", "cancelButton"], true);
      }
      __name(disableButtons, "disableButtons");
      function enableInput() {
        return setInputDisabled(this.getInput(), false);
      }
      __name(enableInput, "enableInput");
      function disableInput() {
        return setInputDisabled(this.getInput(), true);
      }
      __name(disableInput, "disableInput");
      function showValidationMessage(error2) {
        const domCache = privateProps.domCache.get(this);
        const params = privateProps.innerParams.get(this);
        setInnerHtml(domCache.validationMessage, error2);
        domCache.validationMessage.className = swalClasses["validation-message"];
        if (params.customClass && params.customClass.validationMessage) {
          addClass(domCache.validationMessage, params.customClass.validationMessage);
        }
        show(domCache.validationMessage);
        const input = this.getInput();
        if (input) {
          input.setAttribute("aria-invalid", true);
          input.setAttribute("aria-describedby", swalClasses["validation-message"]);
          focusInput(input);
          addClass(input, swalClasses.inputerror);
        }
      }
      __name(showValidationMessage, "showValidationMessage");
      function resetValidationMessage$1() {
        const domCache = privateProps.domCache.get(this);
        if (domCache.validationMessage) {
          hide(domCache.validationMessage);
        }
        const input = this.getInput();
        if (input) {
          input.removeAttribute("aria-invalid");
          input.removeAttribute("aria-describedby");
          removeClass(input, swalClasses.inputerror);
        }
      }
      __name(resetValidationMessage$1, "resetValidationMessage$1");
      function getProgressSteps$1() {
        const domCache = privateProps.domCache.get(this);
        return domCache.progressSteps;
      }
      __name(getProgressSteps$1, "getProgressSteps$1");
      class Timer {
        constructor(callback, delay) {
          this.callback = callback;
          this.remaining = delay;
          this.running = false;
          this.start();
        }
        start() {
          if (!this.running) {
            this.running = true;
            this.started = new Date();
            this.id = setTimeout(this.callback, this.remaining);
          }
          return this.remaining;
        }
        stop() {
          if (this.running) {
            this.running = false;
            clearTimeout(this.id);
            this.remaining -= new Date() - this.started;
          }
          return this.remaining;
        }
        increase(n) {
          const running = this.running;
          if (running) {
            this.stop();
          }
          this.remaining += n;
          if (running) {
            this.start();
          }
          return this.remaining;
        }
        getTimerLeft() {
          if (this.running) {
            this.stop();
            this.start();
          }
          return this.remaining;
        }
        isRunning() {
          return this.running;
        }
      }
      __name(Timer, "Timer");
      var defaultInputValidators = {
        email: (string, validationMessage) => {
          return /^[a-zA-Z0-9.+_-]+@[a-zA-Z0-9.-]+\.[a-zA-Z0-9-]{2,24}$/.test(string) ? Promise.resolve() : Promise.resolve(validationMessage || "Invalid email address");
        },
        url: (string, validationMessage) => {
          return /^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-z]{2,63}\b([-a-zA-Z0-9@:%_+.~#?&/=]*)$/.test(string) ? Promise.resolve() : Promise.resolve(validationMessage || "Invalid URL");
        }
      };
      function setDefaultInputValidators(params) {
        if (!params.inputValidator) {
          Object.keys(defaultInputValidators).forEach((key) => {
            if (params.input === key) {
              params.inputValidator = defaultInputValidators[key];
            }
          });
        }
      }
      __name(setDefaultInputValidators, "setDefaultInputValidators");
      function validateCustomTargetElement(params) {
        if (!params.target || typeof params.target === "string" && !document.querySelector(params.target) || typeof params.target !== "string" && !params.target.appendChild) {
          warn('Target parameter is not valid, defaulting to "body"');
          params.target = "body";
        }
      }
      __name(validateCustomTargetElement, "validateCustomTargetElement");
      function setParameters(params) {
        setDefaultInputValidators(params);
        if (params.showLoaderOnConfirm && !params.preConfirm) {
          warn("showLoaderOnConfirm is set to true, but preConfirm is not defined.\nshowLoaderOnConfirm should be used together with preConfirm, see usage example:\nhttps://sweetalert2.github.io/#ajax-request");
        }
        validateCustomTargetElement(params);
        if (typeof params.title === "string") {
          params.title = params.title.split("\n").join("<br />");
        }
        init(params);
      }
      __name(setParameters, "setParameters");
      const swalStringParams = ["swal-title", "swal-html", "swal-footer"];
      const getTemplateParams = /* @__PURE__ */ __name((params) => {
        const template = typeof params.template === "string" ? document.querySelector(params.template) : params.template;
        if (!template) {
          return {};
        }
        const templateContent = template.content;
        showWarningsForElements(templateContent);
        const result = Object.assign(getSwalParams(templateContent), getSwalButtons(templateContent), getSwalImage(templateContent), getSwalIcon(templateContent), getSwalInput(templateContent), getSwalStringParams(templateContent, swalStringParams));
        return result;
      }, "getTemplateParams");
      const getSwalParams = /* @__PURE__ */ __name((templateContent) => {
        const result = {};
        toArray(templateContent.querySelectorAll("swal-param")).forEach((param) => {
          showWarningsForAttributes(param, ["name", "value"]);
          const paramName = param.getAttribute("name");
          let value = param.getAttribute("value");
          if (typeof defaultParams[paramName] === "boolean" && value === "false") {
            value = false;
          }
          if (typeof defaultParams[paramName] === "object") {
            value = JSON.parse(value);
          }
          result[paramName] = value;
        });
        return result;
      }, "getSwalParams");
      const getSwalButtons = /* @__PURE__ */ __name((templateContent) => {
        const result = {};
        toArray(templateContent.querySelectorAll("swal-button")).forEach((button) => {
          showWarningsForAttributes(button, ["type", "color", "aria-label"]);
          const type = button.getAttribute("type");
          result["".concat(type, "ButtonText")] = button.innerHTML;
          result["show".concat(capitalizeFirstLetter(type), "Button")] = true;
          if (button.hasAttribute("color")) {
            result["".concat(type, "ButtonColor")] = button.getAttribute("color");
          }
          if (button.hasAttribute("aria-label")) {
            result["".concat(type, "ButtonAriaLabel")] = button.getAttribute("aria-label");
          }
        });
        return result;
      }, "getSwalButtons");
      const getSwalImage = /* @__PURE__ */ __name((templateContent) => {
        const result = {};
        const image = templateContent.querySelector("swal-image");
        if (image) {
          showWarningsForAttributes(image, ["src", "width", "height", "alt"]);
          if (image.hasAttribute("src")) {
            result.imageUrl = image.getAttribute("src");
          }
          if (image.hasAttribute("width")) {
            result.imageWidth = image.getAttribute("width");
          }
          if (image.hasAttribute("height")) {
            result.imageHeight = image.getAttribute("height");
          }
          if (image.hasAttribute("alt")) {
            result.imageAlt = image.getAttribute("alt");
          }
        }
        return result;
      }, "getSwalImage");
      const getSwalIcon = /* @__PURE__ */ __name((templateContent) => {
        const result = {};
        const icon = templateContent.querySelector("swal-icon");
        if (icon) {
          showWarningsForAttributes(icon, ["type", "color"]);
          if (icon.hasAttribute("type")) {
            result.icon = icon.getAttribute("type");
          }
          if (icon.hasAttribute("color")) {
            result.iconColor = icon.getAttribute("color");
          }
          result.iconHtml = icon.innerHTML;
        }
        return result;
      }, "getSwalIcon");
      const getSwalInput = /* @__PURE__ */ __name((templateContent) => {
        const result = {};
        const input = templateContent.querySelector("swal-input");
        if (input) {
          showWarningsForAttributes(input, ["type", "label", "placeholder", "value"]);
          result.input = input.getAttribute("type") || "text";
          if (input.hasAttribute("label")) {
            result.inputLabel = input.getAttribute("label");
          }
          if (input.hasAttribute("placeholder")) {
            result.inputPlaceholder = input.getAttribute("placeholder");
          }
          if (input.hasAttribute("value")) {
            result.inputValue = input.getAttribute("value");
          }
        }
        const inputOptions = templateContent.querySelectorAll("swal-input-option");
        if (inputOptions.length) {
          result.inputOptions = {};
          toArray(inputOptions).forEach((option) => {
            showWarningsForAttributes(option, ["value"]);
            const optionValue = option.getAttribute("value");
            const optionName = option.innerHTML;
            result.inputOptions[optionValue] = optionName;
          });
        }
        return result;
      }, "getSwalInput");
      const getSwalStringParams = /* @__PURE__ */ __name((templateContent, paramNames) => {
        const result = {};
        for (const i in paramNames) {
          const paramName = paramNames[i];
          const tag = templateContent.querySelector(paramName);
          if (tag) {
            showWarningsForAttributes(tag, []);
            result[paramName.replace(/^swal-/, "")] = tag.innerHTML.trim();
          }
        }
        return result;
      }, "getSwalStringParams");
      const showWarningsForElements = /* @__PURE__ */ __name((template) => {
        const allowedElements = swalStringParams.concat(["swal-param", "swal-button", "swal-image", "swal-icon", "swal-input", "swal-input-option"]);
        toArray(template.children).forEach((el) => {
          const tagName = el.tagName.toLowerCase();
          if (allowedElements.indexOf(tagName) === -1) {
            warn("Unrecognized element <".concat(tagName, ">"));
          }
        });
      }, "showWarningsForElements");
      const showWarningsForAttributes = /* @__PURE__ */ __name((el, allowedAttributes) => {
        toArray(el.attributes).forEach((attribute) => {
          if (allowedAttributes.indexOf(attribute.name) === -1) {
            warn(['Unrecognized attribute "'.concat(attribute.name, '" on <').concat(el.tagName.toLowerCase(), ">."), "".concat(allowedAttributes.length ? "Allowed attributes are: ".concat(allowedAttributes.join(", ")) : "To set the value, use HTML within the element.")]);
          }
        });
      }, "showWarningsForAttributes");
      const SHOW_CLASS_TIMEOUT = 10;
      const openPopup = /* @__PURE__ */ __name((params) => {
        const container = getContainer();
        const popup = getPopup();
        if (typeof params.willOpen === "function") {
          params.willOpen(popup);
        }
        const bodyStyles = window.getComputedStyle(document.body);
        const initialBodyOverflow = bodyStyles.overflowY;
        addClasses$1(container, popup, params);
        setTimeout(() => {
          setScrollingVisibility(container, popup);
        }, SHOW_CLASS_TIMEOUT);
        if (isModal()) {
          fixScrollContainer(container, params.scrollbarPadding, initialBodyOverflow);
          setAriaHidden();
        }
        if (!isToast() && !globalState.previousActiveElement) {
          globalState.previousActiveElement = document.activeElement;
        }
        if (typeof params.didOpen === "function") {
          setTimeout(() => params.didOpen(popup));
        }
        removeClass(container, swalClasses["no-transition"]);
      }, "openPopup");
      const swalOpenAnimationFinished = /* @__PURE__ */ __name((event) => {
        const popup = getPopup();
        if (event.target !== popup) {
          return;
        }
        const container = getContainer();
        popup.removeEventListener(animationEndEvent, swalOpenAnimationFinished);
        container.style.overflowY = "auto";
      }, "swalOpenAnimationFinished");
      const setScrollingVisibility = /* @__PURE__ */ __name((container, popup) => {
        if (animationEndEvent && hasCssAnimation(popup)) {
          container.style.overflowY = "hidden";
          popup.addEventListener(animationEndEvent, swalOpenAnimationFinished);
        } else {
          container.style.overflowY = "auto";
        }
      }, "setScrollingVisibility");
      const fixScrollContainer = /* @__PURE__ */ __name((container, scrollbarPadding, initialBodyOverflow) => {
        iOSfix();
        if (scrollbarPadding && initialBodyOverflow !== "hidden") {
          fixScrollbar();
        }
        setTimeout(() => {
          container.scrollTop = 0;
        });
      }, "fixScrollContainer");
      const addClasses$1 = /* @__PURE__ */ __name((container, popup, params) => {
        addClass(container, params.showClass.backdrop);
        popup.style.setProperty("opacity", "0", "important");
        show(popup, "grid");
        setTimeout(() => {
          addClass(popup, params.showClass.popup);
          popup.style.removeProperty("opacity");
        }, SHOW_CLASS_TIMEOUT);
        addClass([document.documentElement, document.body], swalClasses.shown);
        if (params.heightAuto && params.backdrop && !params.toast) {
          addClass([document.documentElement, document.body], swalClasses["height-auto"]);
        }
      }, "addClasses$1");
      const handleInputOptionsAndValue = /* @__PURE__ */ __name((instance, params) => {
        if (params.input === "select" || params.input === "radio") {
          handleInputOptions(instance, params);
        } else if (["text", "email", "number", "tel", "textarea"].includes(params.input) && (hasToPromiseFn(params.inputValue) || isPromise(params.inputValue))) {
          showLoading(getConfirmButton());
          handleInputValue(instance, params);
        }
      }, "handleInputOptionsAndValue");
      const getInputValue = /* @__PURE__ */ __name((instance, innerParams) => {
        const input = instance.getInput();
        if (!input) {
          return null;
        }
        switch (innerParams.input) {
          case "checkbox":
            return getCheckboxValue(input);
          case "radio":
            return getRadioValue(input);
          case "file":
            return getFileValue(input);
          default:
            return innerParams.inputAutoTrim ? input.value.trim() : input.value;
        }
      }, "getInputValue");
      const getCheckboxValue = /* @__PURE__ */ __name((input) => input.checked ? 1 : 0, "getCheckboxValue");
      const getRadioValue = /* @__PURE__ */ __name((input) => input.checked ? input.value : null, "getRadioValue");
      const getFileValue = /* @__PURE__ */ __name((input) => input.files.length ? input.getAttribute("multiple") !== null ? input.files : input.files[0] : null, "getFileValue");
      const handleInputOptions = /* @__PURE__ */ __name((instance, params) => {
        const popup = getPopup();
        const processInputOptions = /* @__PURE__ */ __name((inputOptions) => populateInputOptions[params.input](popup, formatInputOptions(inputOptions), params), "processInputOptions");
        if (hasToPromiseFn(params.inputOptions) || isPromise(params.inputOptions)) {
          showLoading(getConfirmButton());
          asPromise(params.inputOptions).then((inputOptions) => {
            instance.hideLoading();
            processInputOptions(inputOptions);
          });
        } else if (typeof params.inputOptions === "object") {
          processInputOptions(params.inputOptions);
        } else {
          error("Unexpected type of inputOptions! Expected object, Map or Promise, got ".concat(typeof params.inputOptions));
        }
      }, "handleInputOptions");
      const handleInputValue = /* @__PURE__ */ __name((instance, params) => {
        const input = instance.getInput();
        hide(input);
        asPromise(params.inputValue).then((inputValue) => {
          input.value = params.input === "number" ? parseFloat(inputValue) || 0 : "".concat(inputValue);
          show(input);
          input.focus();
          instance.hideLoading();
        }).catch((err) => {
          error("Error in inputValue promise: ".concat(err));
          input.value = "";
          show(input);
          input.focus();
          instance.hideLoading();
        });
      }, "handleInputValue");
      const populateInputOptions = {
        select: (popup, inputOptions, params) => {
          const select = getChildByClass(popup, swalClasses.select);
          const renderOption = /* @__PURE__ */ __name((parent, optionLabel, optionValue) => {
            const option = document.createElement("option");
            option.value = optionValue;
            setInnerHtml(option, optionLabel);
            option.selected = isSelected(optionValue, params.inputValue);
            parent.appendChild(option);
          }, "renderOption");
          inputOptions.forEach((inputOption) => {
            const optionValue = inputOption[0];
            const optionLabel = inputOption[1];
            if (Array.isArray(optionLabel)) {
              const optgroup = document.createElement("optgroup");
              optgroup.label = optionValue;
              optgroup.disabled = false;
              select.appendChild(optgroup);
              optionLabel.forEach((o) => renderOption(optgroup, o[1], o[0]));
            } else {
              renderOption(select, optionLabel, optionValue);
            }
          });
          select.focus();
        },
        radio: (popup, inputOptions, params) => {
          const radio = getChildByClass(popup, swalClasses.radio);
          inputOptions.forEach((inputOption) => {
            const radioValue = inputOption[0];
            const radioLabel = inputOption[1];
            const radioInput = document.createElement("input");
            const radioLabelElement = document.createElement("label");
            radioInput.type = "radio";
            radioInput.name = swalClasses.radio;
            radioInput.value = radioValue;
            if (isSelected(radioValue, params.inputValue)) {
              radioInput.checked = true;
            }
            const label = document.createElement("span");
            setInnerHtml(label, radioLabel);
            label.className = swalClasses.label;
            radioLabelElement.appendChild(radioInput);
            radioLabelElement.appendChild(label);
            radio.appendChild(radioLabelElement);
          });
          const radios = radio.querySelectorAll("input");
          if (radios.length) {
            radios[0].focus();
          }
        }
      };
      const formatInputOptions = /* @__PURE__ */ __name((inputOptions) => {
        const result = [];
        if (typeof Map !== "undefined" && inputOptions instanceof Map) {
          inputOptions.forEach((value, key) => {
            let valueFormatted = value;
            if (typeof valueFormatted === "object") {
              valueFormatted = formatInputOptions(valueFormatted);
            }
            result.push([key, valueFormatted]);
          });
        } else {
          Object.keys(inputOptions).forEach((key) => {
            let valueFormatted = inputOptions[key];
            if (typeof valueFormatted === "object") {
              valueFormatted = formatInputOptions(valueFormatted);
            }
            result.push([key, valueFormatted]);
          });
        }
        return result;
      }, "formatInputOptions");
      const isSelected = /* @__PURE__ */ __name((optionValue, inputValue) => {
        return inputValue && inputValue.toString() === optionValue.toString();
      }, "isSelected");
      const handleConfirmButtonClick = /* @__PURE__ */ __name((instance) => {
        const innerParams = privateProps.innerParams.get(instance);
        instance.disableButtons();
        if (innerParams.input) {
          handleConfirmOrDenyWithInput(instance, "confirm");
        } else {
          confirm(instance, true);
        }
      }, "handleConfirmButtonClick");
      const handleDenyButtonClick = /* @__PURE__ */ __name((instance) => {
        const innerParams = privateProps.innerParams.get(instance);
        instance.disableButtons();
        if (innerParams.returnInputValueOnDeny) {
          handleConfirmOrDenyWithInput(instance, "deny");
        } else {
          deny(instance, false);
        }
      }, "handleDenyButtonClick");
      const handleCancelButtonClick = /* @__PURE__ */ __name((instance, dismissWith) => {
        instance.disableButtons();
        dismissWith(DismissReason.cancel);
      }, "handleCancelButtonClick");
      const handleConfirmOrDenyWithInput = /* @__PURE__ */ __name((instance, type) => {
        const innerParams = privateProps.innerParams.get(instance);
        const inputValue = getInputValue(instance, innerParams);
        if (innerParams.inputValidator) {
          handleInputValidator(instance, inputValue, type);
        } else if (!instance.getInput().checkValidity()) {
          instance.enableButtons();
          instance.showValidationMessage(innerParams.validationMessage);
        } else if (type === "deny") {
          deny(instance, inputValue);
        } else {
          confirm(instance, inputValue);
        }
      }, "handleConfirmOrDenyWithInput");
      const handleInputValidator = /* @__PURE__ */ __name((instance, inputValue, type) => {
        const innerParams = privateProps.innerParams.get(instance);
        instance.disableInput();
        const validationPromise = Promise.resolve().then(() => asPromise(innerParams.inputValidator(inputValue, innerParams.validationMessage)));
        validationPromise.then((validationMessage) => {
          instance.enableButtons();
          instance.enableInput();
          if (validationMessage) {
            instance.showValidationMessage(validationMessage);
          } else if (type === "deny") {
            deny(instance, inputValue);
          } else {
            confirm(instance, inputValue);
          }
        });
      }, "handleInputValidator");
      const deny = /* @__PURE__ */ __name((instance, value) => {
        const innerParams = privateProps.innerParams.get(instance || void 0);
        if (innerParams.showLoaderOnDeny) {
          showLoading(getDenyButton());
        }
        if (innerParams.preDeny) {
          privateProps.awaitingPromise.set(instance || void 0, true);
          const preDenyPromise = Promise.resolve().then(() => asPromise(innerParams.preDeny(value, innerParams.validationMessage)));
          preDenyPromise.then((preDenyValue) => {
            if (preDenyValue === false) {
              instance.hideLoading();
            } else {
              instance.closePopup({
                isDenied: true,
                value: typeof preDenyValue === "undefined" ? value : preDenyValue
              });
            }
          }).catch((error$$1) => rejectWith(instance || void 0, error$$1));
        } else {
          instance.closePopup({
            isDenied: true,
            value
          });
        }
      }, "deny");
      const succeedWith = /* @__PURE__ */ __name((instance, value) => {
        instance.closePopup({
          isConfirmed: true,
          value
        });
      }, "succeedWith");
      const rejectWith = /* @__PURE__ */ __name((instance, error$$1) => {
        instance.rejectPromise(error$$1);
      }, "rejectWith");
      const confirm = /* @__PURE__ */ __name((instance, value) => {
        const innerParams = privateProps.innerParams.get(instance || void 0);
        if (innerParams.showLoaderOnConfirm) {
          showLoading();
        }
        if (innerParams.preConfirm) {
          instance.resetValidationMessage();
          privateProps.awaitingPromise.set(instance || void 0, true);
          const preConfirmPromise = Promise.resolve().then(() => asPromise(innerParams.preConfirm(value, innerParams.validationMessage)));
          preConfirmPromise.then((preConfirmValue) => {
            if (isVisible(getValidationMessage()) || preConfirmValue === false) {
              instance.hideLoading();
            } else {
              succeedWith(instance, typeof preConfirmValue === "undefined" ? value : preConfirmValue);
            }
          }).catch((error$$1) => rejectWith(instance || void 0, error$$1));
        } else {
          succeedWith(instance, value);
        }
      }, "confirm");
      const addKeydownHandler = /* @__PURE__ */ __name((instance, globalState2, innerParams, dismissWith) => {
        if (globalState2.keydownTarget && globalState2.keydownHandlerAdded) {
          globalState2.keydownTarget.removeEventListener("keydown", globalState2.keydownHandler, {
            capture: globalState2.keydownListenerCapture
          });
          globalState2.keydownHandlerAdded = false;
        }
        if (!innerParams.toast) {
          globalState2.keydownHandler = (e) => keydownHandler(instance, e, dismissWith);
          globalState2.keydownTarget = innerParams.keydownListenerCapture ? window : getPopup();
          globalState2.keydownListenerCapture = innerParams.keydownListenerCapture;
          globalState2.keydownTarget.addEventListener("keydown", globalState2.keydownHandler, {
            capture: globalState2.keydownListenerCapture
          });
          globalState2.keydownHandlerAdded = true;
        }
      }, "addKeydownHandler");
      const setFocus = /* @__PURE__ */ __name((innerParams, index, increment) => {
        const focusableElements = getFocusableElements();
        if (focusableElements.length) {
          index = index + increment;
          if (index === focusableElements.length) {
            index = 0;
          } else if (index === -1) {
            index = focusableElements.length - 1;
          }
          return focusableElements[index].focus();
        }
        getPopup().focus();
      }, "setFocus");
      const arrowKeysNextButton = ["ArrowRight", "ArrowDown"];
      const arrowKeysPreviousButton = ["ArrowLeft", "ArrowUp"];
      const keydownHandler = /* @__PURE__ */ __name((instance, e, dismissWith) => {
        const innerParams = privateProps.innerParams.get(instance);
        if (!innerParams) {
          return;
        }
        if (innerParams.stopKeydownPropagation) {
          e.stopPropagation();
        }
        if (e.key === "Enter") {
          handleEnter(instance, e, innerParams);
        } else if (e.key === "Tab") {
          handleTab(e, innerParams);
        } else if ([...arrowKeysNextButton, ...arrowKeysPreviousButton].includes(e.key)) {
          handleArrows(e.key);
        } else if (e.key === "Escape") {
          handleEsc(e, innerParams, dismissWith);
        }
      }, "keydownHandler");
      const handleEnter = /* @__PURE__ */ __name((instance, e, innerParams) => {
        if (e.isComposing) {
          return;
        }
        if (e.target && instance.getInput() && e.target.outerHTML === instance.getInput().outerHTML) {
          if (["textarea", "file"].includes(innerParams.input)) {
            return;
          }
          clickConfirm();
          e.preventDefault();
        }
      }, "handleEnter");
      const handleTab = /* @__PURE__ */ __name((e, innerParams) => {
        const targetElement = e.target;
        const focusableElements = getFocusableElements();
        let btnIndex = -1;
        for (let i = 0; i < focusableElements.length; i++) {
          if (targetElement === focusableElements[i]) {
            btnIndex = i;
            break;
          }
        }
        if (!e.shiftKey) {
          setFocus(innerParams, btnIndex, 1);
        } else {
          setFocus(innerParams, btnIndex, -1);
        }
        e.stopPropagation();
        e.preventDefault();
      }, "handleTab");
      const handleArrows = /* @__PURE__ */ __name((key) => {
        const confirmButton = getConfirmButton();
        const denyButton = getDenyButton();
        const cancelButton = getCancelButton();
        if (![confirmButton, denyButton, cancelButton].includes(document.activeElement)) {
          return;
        }
        const sibling = arrowKeysNextButton.includes(key) ? "nextElementSibling" : "previousElementSibling";
        const buttonToFocus = document.activeElement[sibling];
        if (buttonToFocus) {
          buttonToFocus.focus();
        }
      }, "handleArrows");
      const handleEsc = /* @__PURE__ */ __name((e, innerParams, dismissWith) => {
        if (callIfFunction(innerParams.allowEscapeKey)) {
          e.preventDefault();
          dismissWith(DismissReason.esc);
        }
      }, "handleEsc");
      const handlePopupClick = /* @__PURE__ */ __name((instance, domCache, dismissWith) => {
        const innerParams = privateProps.innerParams.get(instance);
        if (innerParams.toast) {
          handleToastClick(instance, domCache, dismissWith);
        } else {
          handleModalMousedown(domCache);
          handleContainerMousedown(domCache);
          handleModalClick(instance, domCache, dismissWith);
        }
      }, "handlePopupClick");
      const handleToastClick = /* @__PURE__ */ __name((instance, domCache, dismissWith) => {
        domCache.popup.onclick = () => {
          const innerParams = privateProps.innerParams.get(instance);
          if (innerParams.showConfirmButton || innerParams.showDenyButton || innerParams.showCancelButton || innerParams.showCloseButton || innerParams.timer || innerParams.input) {
            return;
          }
          dismissWith(DismissReason.close);
        };
      }, "handleToastClick");
      let ignoreOutsideClick = false;
      const handleModalMousedown = /* @__PURE__ */ __name((domCache) => {
        domCache.popup.onmousedown = () => {
          domCache.container.onmouseup = function(e) {
            domCache.container.onmouseup = void 0;
            if (e.target === domCache.container) {
              ignoreOutsideClick = true;
            }
          };
        };
      }, "handleModalMousedown");
      const handleContainerMousedown = /* @__PURE__ */ __name((domCache) => {
        domCache.container.onmousedown = () => {
          domCache.popup.onmouseup = function(e) {
            domCache.popup.onmouseup = void 0;
            if (e.target === domCache.popup || domCache.popup.contains(e.target)) {
              ignoreOutsideClick = true;
            }
          };
        };
      }, "handleContainerMousedown");
      const handleModalClick = /* @__PURE__ */ __name((instance, domCache, dismissWith) => {
        domCache.container.onclick = (e) => {
          const innerParams = privateProps.innerParams.get(instance);
          if (ignoreOutsideClick) {
            ignoreOutsideClick = false;
            return;
          }
          if (e.target === domCache.container && callIfFunction(innerParams.allowOutsideClick)) {
            dismissWith(DismissReason.backdrop);
          }
        };
      }, "handleModalClick");
      function _main(userParams) {
        let mixinParams = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
        showWarningsForParams(Object.assign({}, mixinParams, userParams));
        if (globalState.currentInstance) {
          globalState.currentInstance._destroy();
          if (isModal()) {
            unsetAriaHidden();
          }
        }
        globalState.currentInstance = this;
        const innerParams = prepareParams(userParams, mixinParams);
        setParameters(innerParams);
        Object.freeze(innerParams);
        if (globalState.timeout) {
          globalState.timeout.stop();
          delete globalState.timeout;
        }
        clearTimeout(globalState.restoreFocusTimeout);
        const domCache = populateDomCache(this);
        render(this, innerParams);
        privateProps.innerParams.set(this, innerParams);
        return swalPromise(this, domCache, innerParams);
      }
      __name(_main, "_main");
      const prepareParams = /* @__PURE__ */ __name((userParams, mixinParams) => {
        const templateParams = getTemplateParams(userParams);
        const params = Object.assign({}, defaultParams, mixinParams, templateParams, userParams);
        params.showClass = Object.assign({}, defaultParams.showClass, params.showClass);
        params.hideClass = Object.assign({}, defaultParams.hideClass, params.hideClass);
        return params;
      }, "prepareParams");
      const swalPromise = /* @__PURE__ */ __name((instance, domCache, innerParams) => {
        return new Promise((resolve, reject) => {
          const dismissWith = /* @__PURE__ */ __name((dismiss) => {
            instance.closePopup({
              isDismissed: true,
              dismiss
            });
          }, "dismissWith");
          privateMethods.swalPromiseResolve.set(instance, resolve);
          privateMethods.swalPromiseReject.set(instance, reject);
          domCache.confirmButton.onclick = () => handleConfirmButtonClick(instance);
          domCache.denyButton.onclick = () => handleDenyButtonClick(instance);
          domCache.cancelButton.onclick = () => handleCancelButtonClick(instance, dismissWith);
          domCache.closeButton.onclick = () => dismissWith(DismissReason.close);
          handlePopupClick(instance, domCache, dismissWith);
          addKeydownHandler(instance, globalState, innerParams, dismissWith);
          handleInputOptionsAndValue(instance, innerParams);
          openPopup(innerParams);
          setupTimer(globalState, innerParams, dismissWith);
          initFocus(domCache, innerParams);
          setTimeout(() => {
            domCache.container.scrollTop = 0;
          });
        });
      }, "swalPromise");
      const populateDomCache = /* @__PURE__ */ __name((instance) => {
        const domCache = {
          popup: getPopup(),
          container: getContainer(),
          actions: getActions(),
          confirmButton: getConfirmButton(),
          denyButton: getDenyButton(),
          cancelButton: getCancelButton(),
          loader: getLoader(),
          closeButton: getCloseButton(),
          validationMessage: getValidationMessage(),
          progressSteps: getProgressSteps()
        };
        privateProps.domCache.set(instance, domCache);
        return domCache;
      }, "populateDomCache");
      const setupTimer = /* @__PURE__ */ __name((globalState$$1, innerParams, dismissWith) => {
        const timerProgressBar = getTimerProgressBar();
        hide(timerProgressBar);
        if (innerParams.timer) {
          globalState$$1.timeout = new Timer(() => {
            dismissWith("timer");
            delete globalState$$1.timeout;
          }, innerParams.timer);
          if (innerParams.timerProgressBar) {
            show(timerProgressBar);
            setTimeout(() => {
              if (globalState$$1.timeout && globalState$$1.timeout.running) {
                animateTimerProgressBar(innerParams.timer);
              }
            });
          }
        }
      }, "setupTimer");
      const initFocus = /* @__PURE__ */ __name((domCache, innerParams) => {
        if (innerParams.toast) {
          return;
        }
        if (!callIfFunction(innerParams.allowEnterKey)) {
          return blurActiveElement();
        }
        if (!focusButton(domCache, innerParams)) {
          setFocus(innerParams, -1, 1);
        }
      }, "initFocus");
      const focusButton = /* @__PURE__ */ __name((domCache, innerParams) => {
        if (innerParams.focusDeny && isVisible(domCache.denyButton)) {
          domCache.denyButton.focus();
          return true;
        }
        if (innerParams.focusCancel && isVisible(domCache.cancelButton)) {
          domCache.cancelButton.focus();
          return true;
        }
        if (innerParams.focusConfirm && isVisible(domCache.confirmButton)) {
          domCache.confirmButton.focus();
          return true;
        }
        return false;
      }, "focusButton");
      const blurActiveElement = /* @__PURE__ */ __name(() => {
        if (document.activeElement && typeof document.activeElement.blur === "function") {
          document.activeElement.blur();
        }
      }, "blurActiveElement");
      function update(params) {
        const popup = getPopup();
        const innerParams = privateProps.innerParams.get(this);
        if (!popup || hasClass(popup, innerParams.hideClass.popup)) {
          return warn("You're trying to update the closed or closing popup, that won't work. Use the update() method in preConfirm parameter or show a new popup.");
        }
        const validUpdatableParams = {};
        Object.keys(params).forEach((param) => {
          if (Swal2.isUpdatableParameter(param)) {
            validUpdatableParams[param] = params[param];
          } else {
            warn('Invalid parameter to update: "'.concat(param, '". Updatable params are listed here: https://github.com/sweetalert2/sweetalert2/blob/master/src/utils/params.js\n\nIf you think this parameter should be updatable, request it here: https://github.com/sweetalert2/sweetalert2/issues/new?template=02_feature_request.md'));
          }
        });
        const updatedParams = Object.assign({}, innerParams, validUpdatableParams);
        render(this, updatedParams);
        privateProps.innerParams.set(this, updatedParams);
        Object.defineProperties(this, {
          params: {
            value: Object.assign({}, this.params, params),
            writable: false,
            enumerable: true
          }
        });
      }
      __name(update, "update");
      function _destroy() {
        const domCache = privateProps.domCache.get(this);
        const innerParams = privateProps.innerParams.get(this);
        if (!innerParams) {
          disposeWeakMaps(this);
          return;
        }
        if (domCache.popup && globalState.swalCloseEventFinishedCallback) {
          globalState.swalCloseEventFinishedCallback();
          delete globalState.swalCloseEventFinishedCallback;
        }
        if (globalState.deferDisposalTimer) {
          clearTimeout(globalState.deferDisposalTimer);
          delete globalState.deferDisposalTimer;
        }
        if (typeof innerParams.didDestroy === "function") {
          innerParams.didDestroy();
        }
        disposeSwal(this);
      }
      __name(_destroy, "_destroy");
      const disposeSwal = /* @__PURE__ */ __name((instance) => {
        disposeWeakMaps(instance);
        delete instance.params;
        delete globalState.keydownHandler;
        delete globalState.keydownTarget;
        delete globalState.currentInstance;
      }, "disposeSwal");
      const disposeWeakMaps = /* @__PURE__ */ __name((instance) => {
        if (instance.isAwaitingPromise()) {
          unsetWeakMaps(privateProps, instance);
          privateProps.awaitingPromise.set(instance, true);
        } else {
          unsetWeakMaps(privateMethods, instance);
          unsetWeakMaps(privateProps, instance);
        }
      }, "disposeWeakMaps");
      const unsetWeakMaps = /* @__PURE__ */ __name((obj, instance) => {
        for (const i in obj) {
          obj[i].delete(instance);
        }
      }, "unsetWeakMaps");
      var instanceMethods = /* @__PURE__ */ Object.freeze({
        hideLoading,
        disableLoading: hideLoading,
        getInput: getInput$1,
        close,
        isAwaitingPromise,
        rejectPromise,
        closePopup: close,
        closeModal: close,
        closeToast: close,
        enableButtons,
        disableButtons,
        enableInput,
        disableInput,
        showValidationMessage,
        resetValidationMessage: resetValidationMessage$1,
        getProgressSteps: getProgressSteps$1,
        _main,
        update,
        _destroy
      });
      let currentInstance;
      class SweetAlert {
        constructor() {
          if (typeof window === "undefined") {
            return;
          }
          currentInstance = this;
          for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
            args[_key] = arguments[_key];
          }
          const outerParams = Object.freeze(this.constructor.argsToParams(args));
          Object.defineProperties(this, {
            params: {
              value: outerParams,
              writable: false,
              enumerable: true,
              configurable: true
            }
          });
          const promise = this._main(this.params);
          privateProps.promise.set(this, promise);
        }
        then(onFulfilled) {
          const promise = privateProps.promise.get(this);
          return promise.then(onFulfilled);
        }
        finally(onFinally) {
          const promise = privateProps.promise.get(this);
          return promise.finally(onFinally);
        }
      }
      __name(SweetAlert, "SweetAlert");
      Object.assign(SweetAlert.prototype, instanceMethods);
      Object.assign(SweetAlert, staticMethods);
      Object.keys(instanceMethods).forEach((key) => {
        SweetAlert[key] = function() {
          if (currentInstance) {
            return currentInstance[key](...arguments);
          }
        };
      });
      SweetAlert.DismissReason = DismissReason;
      SweetAlert.version = "11.1.10";
      const Swal2 = SweetAlert;
      Swal2.default = Swal2;
      return Swal2;
    });
    if (typeof exports !== "undefined" && exports.Sweetalert2) {
      exports.swal = exports.sweetAlert = exports.Swal = exports.SweetAlert = exports.Sweetalert2;
    }
    typeof document != "undefined" && function(e, t) {
      var n = e.createElement("style");
      if (e.getElementsByTagName("head")[0].appendChild(n), n.styleSheet)
        n.styleSheet.disabled || (n.styleSheet.cssText = t);
      else
        try {
          n.innerHTML = t;
        } catch (e2) {
          n.innerText = t;
        }
    }(document, '.swal2-popup.swal2-toast{box-sizing:border-box;grid-column:1/4!important;grid-row:1/4!important;grid-template-columns:1fr 99fr 1fr;padding:1em;overflow-y:hidden;background:#fff;box-shadow:0 0 1px rgba(0,0,0,.075),0 1px 2px rgba(0,0,0,.075),1px 2px 4px rgba(0,0,0,.075),1px 3px 8px rgba(0,0,0,.075),2px 4px 16px rgba(0,0,0,.075);pointer-events:all}.swal2-popup.swal2-toast>*{grid-column:2}.swal2-popup.swal2-toast .swal2-title{margin:.5em 1em;padding:0;font-size:1em;text-align:initial}.swal2-popup.swal2-toast .swal2-loading{justify-content:center}.swal2-popup.swal2-toast .swal2-input{height:2em;margin:.5em;font-size:1em}.swal2-popup.swal2-toast .swal2-validation-message{font-size:1em}.swal2-popup.swal2-toast .swal2-footer{margin:.5em 0 0;padding:.5em 0 0;font-size:.8em}.swal2-popup.swal2-toast .swal2-close{grid-column:3/3;grid-row:1/99;align-self:center;width:.8em;height:.8em;margin:0;font-size:2em}.swal2-popup.swal2-toast .swal2-html-container{margin:.5em 1em;padding:0;font-size:1em;text-align:initial}.swal2-popup.swal2-toast .swal2-html-container:empty{padding:0}.swal2-popup.swal2-toast .swal2-loader{grid-column:1;grid-row:1/99;align-self:center;width:2em;height:2em;margin:.25em}.swal2-popup.swal2-toast .swal2-icon{grid-column:1;grid-row:1/99;align-self:center;width:2em;min-width:2em;height:2em;margin:0 .5em 0 0}.swal2-popup.swal2-toast .swal2-icon .swal2-icon-content{display:flex;align-items:center;font-size:1.8em;font-weight:700}.swal2-popup.swal2-toast .swal2-icon.swal2-success .swal2-success-ring{width:2em;height:2em}.swal2-popup.swal2-toast .swal2-icon.swal2-error [class^=swal2-x-mark-line]{top:.875em;width:1.375em}.swal2-popup.swal2-toast .swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=left]{left:.3125em}.swal2-popup.swal2-toast .swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=right]{right:.3125em}.swal2-popup.swal2-toast .swal2-actions{justify-content:flex-start;height:auto;margin:0;margin-top:.5em;padding:0 .5em}.swal2-popup.swal2-toast .swal2-styled{margin:.25em .5em;padding:.4em .6em;font-size:1em}.swal2-popup.swal2-toast .swal2-success{border-color:#a5dc86}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-circular-line]{position:absolute;width:1.6em;height:3em;transform:rotate(45deg);border-radius:50%}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-circular-line][class$=left]{top:-.8em;left:-.5em;transform:rotate(-45deg);transform-origin:2em 2em;border-radius:4em 0 0 4em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-circular-line][class$=right]{top:-.25em;left:.9375em;transform-origin:0 1.5em;border-radius:0 4em 4em 0}.swal2-popup.swal2-toast .swal2-success .swal2-success-ring{width:2em;height:2em}.swal2-popup.swal2-toast .swal2-success .swal2-success-fix{top:0;left:.4375em;width:.4375em;height:2.6875em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-line]{height:.3125em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-line][class$=tip]{top:1.125em;left:.1875em;width:.75em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-line][class$=long]{top:.9375em;right:.1875em;width:1.375em}.swal2-popup.swal2-toast .swal2-success.swal2-icon-show .swal2-success-line-tip{-webkit-animation:swal2-toast-animate-success-line-tip .75s;animation:swal2-toast-animate-success-line-tip .75s}.swal2-popup.swal2-toast .swal2-success.swal2-icon-show .swal2-success-line-long{-webkit-animation:swal2-toast-animate-success-line-long .75s;animation:swal2-toast-animate-success-line-long .75s}.swal2-popup.swal2-toast.swal2-show{-webkit-animation:swal2-toast-show .5s;animation:swal2-toast-show .5s}.swal2-popup.swal2-toast.swal2-hide{-webkit-animation:swal2-toast-hide .1s forwards;animation:swal2-toast-hide .1s forwards}.swal2-container{display:grid;position:fixed;z-index:1060;top:0;right:0;bottom:0;left:0;box-sizing:border-box;grid-template-areas:"top-start     top            top-end" "center-start  center         center-end" "bottom-start  bottom-center  bottom-end";grid-template-rows:minmax(-webkit-min-content,auto) minmax(-webkit-min-content,auto) minmax(-webkit-min-content,auto);grid-template-rows:minmax(min-content,auto) minmax(min-content,auto) minmax(min-content,auto);height:100%;padding:.625em;overflow-x:hidden;transition:background-color .1s;-webkit-overflow-scrolling:touch}.swal2-container.swal2-backdrop-show,.swal2-container.swal2-noanimation{background:rgba(0,0,0,.4)}.swal2-container.swal2-backdrop-hide{background:0 0!important}.swal2-container.swal2-bottom-start,.swal2-container.swal2-center-start,.swal2-container.swal2-top-start{grid-template-columns:minmax(0,1fr) auto auto}.swal2-container.swal2-bottom,.swal2-container.swal2-center,.swal2-container.swal2-top{grid-template-columns:auto minmax(0,1fr) auto}.swal2-container.swal2-bottom-end,.swal2-container.swal2-center-end,.swal2-container.swal2-top-end{grid-template-columns:auto auto minmax(0,1fr)}.swal2-container.swal2-top-start>.swal2-popup{align-self:start}.swal2-container.swal2-top>.swal2-popup{grid-column:2;align-self:start;justify-self:center}.swal2-container.swal2-top-end>.swal2-popup,.swal2-container.swal2-top-right>.swal2-popup{grid-column:3;align-self:start;justify-self:end}.swal2-container.swal2-center-left>.swal2-popup,.swal2-container.swal2-center-start>.swal2-popup{grid-row:2;align-self:center}.swal2-container.swal2-center>.swal2-popup{grid-column:2;grid-row:2;align-self:center;justify-self:center}.swal2-container.swal2-center-end>.swal2-popup,.swal2-container.swal2-center-right>.swal2-popup{grid-column:3;grid-row:2;align-self:center;justify-self:end}.swal2-container.swal2-bottom-left>.swal2-popup,.swal2-container.swal2-bottom-start>.swal2-popup{grid-column:1;grid-row:3;align-self:end}.swal2-container.swal2-bottom>.swal2-popup{grid-column:2;grid-row:3;justify-self:center;align-self:end}.swal2-container.swal2-bottom-end>.swal2-popup,.swal2-container.swal2-bottom-right>.swal2-popup{grid-column:3;grid-row:3;align-self:end;justify-self:end}.swal2-container.swal2-grow-fullscreen>.swal2-popup,.swal2-container.swal2-grow-row>.swal2-popup{grid-column:1/4;width:100%}.swal2-container.swal2-grow-column>.swal2-popup,.swal2-container.swal2-grow-fullscreen>.swal2-popup{grid-row:1/4;align-self:stretch}.swal2-container.swal2-no-transition{transition:none!important}.swal2-popup{display:none;position:relative;box-sizing:border-box;grid-template-columns:minmax(0,100%);width:32em;max-width:100%;padding:0 0 1.25em;border:none;border-radius:5px;background:#fff;color:#545454;font-family:inherit;font-size:1rem}.swal2-popup:focus{outline:0}.swal2-popup.swal2-loading{overflow-y:hidden}.swal2-title{position:relative;max-width:100%;margin:0;padding:.8em 1em 0;color:#595959;font-size:1.875em;font-weight:600;text-align:center;text-transform:none;word-wrap:break-word}.swal2-actions{display:flex;z-index:1;box-sizing:border-box;flex-wrap:wrap;align-items:center;justify-content:center;width:auto;margin:1.25em auto 0;padding:0}.swal2-actions:not(.swal2-loading) .swal2-styled[disabled]{opacity:.4}.swal2-actions:not(.swal2-loading) .swal2-styled:hover{background-image:linear-gradient(rgba(0,0,0,.1),rgba(0,0,0,.1))}.swal2-actions:not(.swal2-loading) .swal2-styled:active{background-image:linear-gradient(rgba(0,0,0,.2),rgba(0,0,0,.2))}.swal2-loader{display:none;align-items:center;justify-content:center;width:2.2em;height:2.2em;margin:0 1.875em;-webkit-animation:swal2-rotate-loading 1.5s linear 0s infinite normal;animation:swal2-rotate-loading 1.5s linear 0s infinite normal;border-width:.25em;border-style:solid;border-radius:100%;border-color:#2778c4 transparent #2778c4 transparent}.swal2-styled{margin:.3125em;padding:.625em 1.1em;transition:box-shadow .1s;box-shadow:0 0 0 3px transparent;font-weight:500}.swal2-styled:not([disabled]){cursor:pointer}.swal2-styled.swal2-confirm{border:0;border-radius:.25em;background:initial;background-color:#7367f0;color:#fff;font-size:1em}.swal2-styled.swal2-confirm:focus{box-shadow:0 0 0 3px rgba(115,103,240,.5)}.swal2-styled.swal2-deny{border:0;border-radius:.25em;background:initial;background-color:#ea5455;color:#fff;font-size:1em}.swal2-styled.swal2-deny:focus{box-shadow:0 0 0 3px rgba(234,84,85,.5)}.swal2-styled.swal2-cancel{border:0;border-radius:.25em;background:initial;background-color:#6e7d88;color:#fff;font-size:1em}.swal2-styled.swal2-cancel:focus{box-shadow:0 0 0 3px rgba(110,125,136,.5)}.swal2-styled.swal2-default-outline:focus{box-shadow:0 0 0 3px rgba(100,150,200,.5)}.swal2-styled:focus{outline:0}.swal2-styled::-moz-focus-inner{border:0}.swal2-footer{justify-content:center;margin:1em 0 0;padding:1em 1em 0;border-top:1px solid #eee;color:#545454;font-size:1em}.swal2-timer-progress-bar-container{position:absolute;right:0;bottom:0;left:0;grid-column:auto!important;height:.25em;overflow:hidden;border-bottom-right-radius:5px;border-bottom-left-radius:5px}.swal2-timer-progress-bar{width:100%;height:.25em;background:rgba(0,0,0,.2)}.swal2-image{max-width:100%;margin:2em auto 1em}.swal2-close{z-index:2;align-items:center;justify-content:center;width:1.2em;height:1.2em;margin-top:0;margin-right:0;margin-bottom:-1.2em;padding:0;overflow:hidden;transition:color .1s,box-shadow .1s;border:none;border-radius:5px;background:0 0;color:#ccc;font-family:serif;font-family:monospace;font-size:2.5em;cursor:pointer;justify-self:end}.swal2-close:hover{transform:none;background:0 0;color:#f27474}.swal2-close:focus{outline:0;box-shadow:inset 0 0 0 3px rgba(100,150,200,.5)}.swal2-close::-moz-focus-inner{border:0}.swal2-html-container{z-index:1;justify-content:center;margin:1em 1.6em .3em;padding:0;overflow:auto;color:#545454;font-size:1.125em;font-weight:400;line-height:normal;text-align:center;word-wrap:break-word;word-break:break-word}.swal2-checkbox,.swal2-file,.swal2-input,.swal2-radio,.swal2-select,.swal2-textarea{margin:1em 2em 0}.swal2-file,.swal2-input,.swal2-textarea{box-sizing:border-box;width:auto;transition:border-color .1s,box-shadow .1s;border:1px solid #d9d9d9;border-radius:.1875em;background:inherit;box-shadow:inset 0 1px 1px rgba(0,0,0,.06),0 0 0 3px transparent;color:inherit;font-size:1.125em}.swal2-file.swal2-inputerror,.swal2-input.swal2-inputerror,.swal2-textarea.swal2-inputerror{border-color:#f27474!important;box-shadow:0 0 2px #f27474!important}.swal2-file:focus,.swal2-input:focus,.swal2-textarea:focus{border:1px solid #b4dbed;outline:0;box-shadow:inset 0 1px 1px rgba(0,0,0,.06),0 0 0 3px rgba(100,150,200,.5)}.swal2-file::-moz-placeholder,.swal2-input::-moz-placeholder,.swal2-textarea::-moz-placeholder{color:#ccc}.swal2-file:-ms-input-placeholder,.swal2-input:-ms-input-placeholder,.swal2-textarea:-ms-input-placeholder{color:#ccc}.swal2-file::placeholder,.swal2-input::placeholder,.swal2-textarea::placeholder{color:#ccc}.swal2-range{margin:1em 2em 0;background:#fff}.swal2-range input{width:80%}.swal2-range output{width:20%;color:inherit;font-weight:600;text-align:center}.swal2-range input,.swal2-range output{height:2.625em;padding:0;font-size:1.125em;line-height:2.625em}.swal2-input{height:2.625em;padding:0 .75em}.swal2-file{width:75%;margin-right:auto;margin-left:auto;background:inherit;font-size:1.125em}.swal2-textarea{height:6.75em;padding:.75em}.swal2-select{min-width:50%;max-width:100%;padding:.375em .625em;background:inherit;color:inherit;font-size:1.125em}.swal2-checkbox,.swal2-radio{align-items:center;justify-content:center;background:#fff;color:inherit}.swal2-checkbox label,.swal2-radio label{margin:0 .6em;font-size:1.125em}.swal2-checkbox input,.swal2-radio input{flex-shrink:0;margin:0 .4em}.swal2-input-label{display:flex;justify-content:center;margin:1em auto 0}.swal2-validation-message{align-items:center;justify-content:center;margin:1em 0 0;padding:.625em;overflow:hidden;background:#f0f0f0;color:#666;font-size:1em;font-weight:300}.swal2-validation-message::before{content:"!";display:inline-block;width:1.5em;min-width:1.5em;height:1.5em;margin:0 .625em;border-radius:50%;background-color:#f27474;color:#fff;font-weight:600;line-height:1.5em;text-align:center}.swal2-icon{position:relative;box-sizing:content-box;justify-content:center;width:5em;height:5em;margin:2.5em auto .6em;border:.25em solid transparent;border-radius:50%;border-color:#000;font-family:inherit;line-height:5em;cursor:default;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}.swal2-icon .swal2-icon-content{display:flex;align-items:center;font-size:3.75em}.swal2-icon.swal2-error{border-color:#f27474;color:#f27474}.swal2-icon.swal2-error .swal2-x-mark{position:relative;flex-grow:1}.swal2-icon.swal2-error [class^=swal2-x-mark-line]{display:block;position:absolute;top:2.3125em;width:2.9375em;height:.3125em;border-radius:.125em;background-color:#f27474}.swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=left]{left:1.0625em;transform:rotate(45deg)}.swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=right]{right:1em;transform:rotate(-45deg)}.swal2-icon.swal2-error.swal2-icon-show{-webkit-animation:swal2-animate-error-icon .5s;animation:swal2-animate-error-icon .5s}.swal2-icon.swal2-error.swal2-icon-show .swal2-x-mark{-webkit-animation:swal2-animate-error-x-mark .5s;animation:swal2-animate-error-x-mark .5s}.swal2-icon.swal2-warning{border-color:#facea8;color:#f8bb86}.swal2-icon.swal2-info{border-color:#9de0f6;color:#3fc3ee}.swal2-icon.swal2-question{border-color:#c9dae1;color:#87adbd}.swal2-icon.swal2-success{border-color:#a5dc86;color:#a5dc86}.swal2-icon.swal2-success [class^=swal2-success-circular-line]{position:absolute;width:3.75em;height:7.5em;transform:rotate(45deg);border-radius:50%}.swal2-icon.swal2-success [class^=swal2-success-circular-line][class$=left]{top:-.4375em;left:-2.0635em;transform:rotate(-45deg);transform-origin:3.75em 3.75em;border-radius:7.5em 0 0 7.5em}.swal2-icon.swal2-success [class^=swal2-success-circular-line][class$=right]{top:-.6875em;left:1.875em;transform:rotate(-45deg);transform-origin:0 3.75em;border-radius:0 7.5em 7.5em 0}.swal2-icon.swal2-success .swal2-success-ring{position:absolute;z-index:2;top:-.25em;left:-.25em;box-sizing:content-box;width:100%;height:100%;border:.25em solid rgba(165,220,134,.3);border-radius:50%}.swal2-icon.swal2-success .swal2-success-fix{position:absolute;z-index:1;top:.5em;left:1.625em;width:.4375em;height:5.625em;transform:rotate(-45deg)}.swal2-icon.swal2-success [class^=swal2-success-line]{display:block;position:absolute;z-index:2;height:.3125em;border-radius:.125em;background-color:#a5dc86}.swal2-icon.swal2-success [class^=swal2-success-line][class$=tip]{top:2.875em;left:.8125em;width:1.5625em;transform:rotate(45deg)}.swal2-icon.swal2-success [class^=swal2-success-line][class$=long]{top:2.375em;right:.5em;width:2.9375em;transform:rotate(-45deg)}.swal2-icon.swal2-success.swal2-icon-show .swal2-success-line-tip{-webkit-animation:swal2-animate-success-line-tip .75s;animation:swal2-animate-success-line-tip .75s}.swal2-icon.swal2-success.swal2-icon-show .swal2-success-line-long{-webkit-animation:swal2-animate-success-line-long .75s;animation:swal2-animate-success-line-long .75s}.swal2-icon.swal2-success.swal2-icon-show .swal2-success-circular-line-right{-webkit-animation:swal2-rotate-success-circular-line 4.25s ease-in;animation:swal2-rotate-success-circular-line 4.25s ease-in}.swal2-progress-steps{flex-wrap:wrap;align-items:center;max-width:100%;margin:1.25em auto;padding:0;background:inherit;font-weight:600}.swal2-progress-steps li{display:inline-block;position:relative}.swal2-progress-steps .swal2-progress-step{z-index:20;flex-shrink:0;width:2em;height:2em;border-radius:2em;background:#2778c4;color:#fff;line-height:2em;text-align:center}.swal2-progress-steps .swal2-progress-step.swal2-active-progress-step{background:#2778c4}.swal2-progress-steps .swal2-progress-step.swal2-active-progress-step~.swal2-progress-step{background:#add8e6;color:#fff}.swal2-progress-steps .swal2-progress-step.swal2-active-progress-step~.swal2-progress-step-line{background:#add8e6}.swal2-progress-steps .swal2-progress-step-line{z-index:10;flex-shrink:0;width:2.5em;height:.4em;margin:0 -1px;background:#2778c4}[class^=swal2]{-webkit-tap-highlight-color:transparent}.swal2-show{-webkit-animation:swal2-show .3s;animation:swal2-show .3s}.swal2-hide{-webkit-animation:swal2-hide .15s forwards;animation:swal2-hide .15s forwards}.swal2-noanimation{transition:none}.swal2-scrollbar-measure{position:absolute;top:-9999px;width:50px;height:50px;overflow:scroll}.swal2-rtl .swal2-close{margin-right:initial;margin-left:0}.swal2-rtl .swal2-timer-progress-bar{right:0;left:auto}@-webkit-keyframes swal2-toast-show{0%{transform:translateY(-.625em) rotateZ(2deg)}33%{transform:translateY(0) rotateZ(-2deg)}66%{transform:translateY(.3125em) rotateZ(2deg)}100%{transform:translateY(0) rotateZ(0)}}@keyframes swal2-toast-show{0%{transform:translateY(-.625em) rotateZ(2deg)}33%{transform:translateY(0) rotateZ(-2deg)}66%{transform:translateY(.3125em) rotateZ(2deg)}100%{transform:translateY(0) rotateZ(0)}}@-webkit-keyframes swal2-toast-hide{100%{transform:rotateZ(1deg);opacity:0}}@keyframes swal2-toast-hide{100%{transform:rotateZ(1deg);opacity:0}}@-webkit-keyframes swal2-toast-animate-success-line-tip{0%{top:.5625em;left:.0625em;width:0}54%{top:.125em;left:.125em;width:0}70%{top:.625em;left:-.25em;width:1.625em}84%{top:1.0625em;left:.75em;width:.5em}100%{top:1.125em;left:.1875em;width:.75em}}@keyframes swal2-toast-animate-success-line-tip{0%{top:.5625em;left:.0625em;width:0}54%{top:.125em;left:.125em;width:0}70%{top:.625em;left:-.25em;width:1.625em}84%{top:1.0625em;left:.75em;width:.5em}100%{top:1.125em;left:.1875em;width:.75em}}@-webkit-keyframes swal2-toast-animate-success-line-long{0%{top:1.625em;right:1.375em;width:0}65%{top:1.25em;right:.9375em;width:0}84%{top:.9375em;right:0;width:1.125em}100%{top:.9375em;right:.1875em;width:1.375em}}@keyframes swal2-toast-animate-success-line-long{0%{top:1.625em;right:1.375em;width:0}65%{top:1.25em;right:.9375em;width:0}84%{top:.9375em;right:0;width:1.125em}100%{top:.9375em;right:.1875em;width:1.375em}}@-webkit-keyframes swal2-show{0%{transform:scale(.7)}45%{transform:scale(1.05)}80%{transform:scale(.95)}100%{transform:scale(1)}}@keyframes swal2-show{0%{transform:scale(.7)}45%{transform:scale(1.05)}80%{transform:scale(.95)}100%{transform:scale(1)}}@-webkit-keyframes swal2-hide{0%{transform:scale(1);opacity:1}100%{transform:scale(.5);opacity:0}}@keyframes swal2-hide{0%{transform:scale(1);opacity:1}100%{transform:scale(.5);opacity:0}}@-webkit-keyframes swal2-animate-success-line-tip{0%{top:1.1875em;left:.0625em;width:0}54%{top:1.0625em;left:.125em;width:0}70%{top:2.1875em;left:-.375em;width:3.125em}84%{top:3em;left:1.3125em;width:1.0625em}100%{top:2.8125em;left:.8125em;width:1.5625em}}@keyframes swal2-animate-success-line-tip{0%{top:1.1875em;left:.0625em;width:0}54%{top:1.0625em;left:.125em;width:0}70%{top:2.1875em;left:-.375em;width:3.125em}84%{top:3em;left:1.3125em;width:1.0625em}100%{top:2.8125em;left:.8125em;width:1.5625em}}@-webkit-keyframes swal2-animate-success-line-long{0%{top:3.375em;right:2.875em;width:0}65%{top:3.375em;right:2.875em;width:0}84%{top:2.1875em;right:0;width:3.4375em}100%{top:2.375em;right:.5em;width:2.9375em}}@keyframes swal2-animate-success-line-long{0%{top:3.375em;right:2.875em;width:0}65%{top:3.375em;right:2.875em;width:0}84%{top:2.1875em;right:0;width:3.4375em}100%{top:2.375em;right:.5em;width:2.9375em}}@-webkit-keyframes swal2-rotate-success-circular-line{0%{transform:rotate(-45deg)}5%{transform:rotate(-45deg)}12%{transform:rotate(-405deg)}100%{transform:rotate(-405deg)}}@keyframes swal2-rotate-success-circular-line{0%{transform:rotate(-45deg)}5%{transform:rotate(-45deg)}12%{transform:rotate(-405deg)}100%{transform:rotate(-405deg)}}@-webkit-keyframes swal2-animate-error-x-mark{0%{margin-top:1.625em;transform:scale(.4);opacity:0}50%{margin-top:1.625em;transform:scale(.4);opacity:0}80%{margin-top:-.375em;transform:scale(1.15)}100%{margin-top:0;transform:scale(1);opacity:1}}@keyframes swal2-animate-error-x-mark{0%{margin-top:1.625em;transform:scale(.4);opacity:0}50%{margin-top:1.625em;transform:scale(.4);opacity:0}80%{margin-top:-.375em;transform:scale(1.15)}100%{margin-top:0;transform:scale(1);opacity:1}}@-webkit-keyframes swal2-animate-error-icon{0%{transform:rotateX(100deg);opacity:0}100%{transform:rotateX(0);opacity:1}}@keyframes swal2-animate-error-icon{0%{transform:rotateX(100deg);opacity:0}100%{transform:rotateX(0);opacity:1}}@-webkit-keyframes swal2-rotate-loading{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}@keyframes swal2-rotate-loading{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown){overflow:hidden}body.swal2-height-auto{height:auto!important}body.swal2-no-backdrop .swal2-container{background-color:transparent!important;pointer-events:none}body.swal2-no-backdrop .swal2-container .swal2-popup{pointer-events:all}body.swal2-no-backdrop .swal2-container .swal2-modal{box-shadow:0 0 10px rgba(0,0,0,.4)}@media print{body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown){overflow-y:scroll!important}body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown)>[aria-hidden=true]{display:none}body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) .swal2-container{position:static!important}}body.swal2-toast-shown .swal2-container{box-sizing:border-box;width:360px;max-width:100%;background-color:transparent;pointer-events:none}body.swal2-toast-shown .swal2-container.swal2-top{top:0;right:auto;bottom:auto;left:50%;transform:translateX(-50%)}body.swal2-toast-shown .swal2-container.swal2-top-end,body.swal2-toast-shown .swal2-container.swal2-top-right{top:0;right:0;bottom:auto;left:auto}body.swal2-toast-shown .swal2-container.swal2-top-left,body.swal2-toast-shown .swal2-container.swal2-top-start{top:0;right:auto;bottom:auto;left:0}body.swal2-toast-shown .swal2-container.swal2-center-left,body.swal2-toast-shown .swal2-container.swal2-center-start{top:50%;right:auto;bottom:auto;left:0;transform:translateY(-50%)}body.swal2-toast-shown .swal2-container.swal2-center{top:50%;right:auto;bottom:auto;left:50%;transform:translate(-50%,-50%)}body.swal2-toast-shown .swal2-container.swal2-center-end,body.swal2-toast-shown .swal2-container.swal2-center-right{top:50%;right:0;bottom:auto;left:auto;transform:translateY(-50%)}body.swal2-toast-shown .swal2-container.swal2-bottom-left,body.swal2-toast-shown .swal2-container.swal2-bottom-start{top:auto;right:auto;bottom:0;left:0}body.swal2-toast-shown .swal2-container.swal2-bottom{top:auto;right:auto;bottom:0;left:50%;transform:translateX(-50%)}body.swal2-toast-shown .swal2-container.swal2-bottom-end,body.swal2-toast-shown .swal2-container.swal2-bottom-right{top:auto;right:0;bottom:0;left:auto}');
  }
});

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

// src/Util/Commands/CommandRegistrar.ts
var CommandRegistrar = class {
  constructor($list) {
    this.list = $list;
  }
  getList() {
    return this.list;
  }
};
__name(CommandRegistrar, "CommandRegistrar");

// src/Core/Configs/Icons.ts
var ICONS = {
  "play": '<svg class="icon tonics-play-outline"><use xlink:href="#tonics-play-outline"></use></svg>',
  "playlist": '<svg class="icon tonics-music-playlist"><use xlink:href="#tonics-music-playlist"></use></svg>',
  "plus": '<svg class="icon tonics-add-new"><use xlink:href="#tonics-add-new"></use></svg>',
  "archive": '<svg class="icon tonics-archive"><use xlink:href="#tonics-archive"></use></svg>',
  "note": '<svg class="icon tonics-note"><use xlink:href="#tonics-note"></use></svg>',
  "notes": '<svg class="icon tonics-multiple-notes"><use xlink:href="#tonics-multiple-notes"></use></svg>',
  "category": '<svg class="icon tonics-categories"><use xlink:href="#tonics-categories"></use></svg>',
  "cog": '<svg class="icon tonics-cog"> <use xlink:href="#tonics-cog"></use></svg>',
  "dashboard": '<svg class="icon tonics-dashboard"> <use xlink:href="#tonics-dashboard"></use></svg>',
  "menu": '<svg class="icon tonics-menu"> <use xlink:href="#tonics-menu"></use></svg>',
  "trash-can": '<svg class="icon tonics-trash-can"> <use xlink:href="#tonics-trash-can"></use></svg>',
  "cart": '<svg class="icon tonics-cart"> <use xlink:href="#tonics-cart"></use></svg>',
  "widget": '<svg class="icon tonics-widgets"> <use xlink:href="#tonics-widgets"></use></svg>',
  "tools": '<svg class="icon tonics-tools"> <use xlink:href="#tonics-tools"></use></svg>',
  "toggle-left": '<svg class="icon tonics-toggle-left"> <use xlink:href="#tonics-toggle-left"></use></svg>',
  "toggle-right": '<svg class="icon tonics-toggle-right"> <use xlink:href="#tonics-toggle-right"></use></svg>',
  "arrow-down": '<svg class="icon tonics-arrow-down"> <use xlink:href="#tonics-arrow-down"></use></svg>',
  "arrow-up": '<svg class="icon tonics-arrow-up"> <use xlink:href="#tonics-arrow-up"></use></svg>',
  "arrow-right": '<svg class="icon tonics-chevron-with-circle-right"> <use xlink:href="#tonics-chevron-with-circle-right"></use></svg>',
  "arrow-left": '<svg class="icon tonics-chevron-with-circle-left"> <use xlink:href="#tonics-chevron-with-circle-left"></use></svg>',
  "sign-out": '<svg class="icon tonics-sign-out"> <use xlink:href="#tonics-dashboard"></use></svg>',
  "user-solid-circle": '<svg class="icon tonics-user-solid-circle"> <use xlink:href="#tonics-user-solid-circle"></use></svg>',
  "users": '<svg class="icon tonics-users"> <use xlink:href="#tonics-users"></use></svg>',
  "profile-settings": '<svg class="icon tonics-profile-settings"><use xlink:href="#tonics-profile-settings"></use></svg>',
  "more-horizontal": '<svg class="icon tonics-more-horizontal"><use xlink:href="#tonics-more-horizontal"></use></svg>',
  "more-vertical": '<svg class="icon tonics-more-vertical"><use xlink:href="#tonics-more-vertical"></use></svg>',
  "heart": '<svg class="icon tonics-heart"> <use xlink:href="#tonics-heart"></use></svg>',
  "dots-two-vertical": '<svg class="icon tonics-dots-two-vertical"><use xlink:href="#tonics-dots-two-vertical"></use></svg>',
  "dots-two-horizontal": '<svg class="icon tonics-dots-two-horizontal"><use xlink:href="#tonics-dots-two-horizontal"></use></svg>',
  "heart-fill": '<svg class="icon tonics-heart-fill"> <use xlink:href="#tonics-heart-fill"></use></svg>',
  "pending": '<svg class="icon tonics-pending"><use xlink:href="#tonics-pending"></use></svg>',
  "remove": '<svg class="icon tonics-remove"> <use xlink:href="#tonics-remove"></use></svg>',
  "shopping-cart": '<svg class="icon tonics-shopping-cart"> <use xlink:href="#tonics-shopping-cart"></use></svg>',
  "dollar": '<svg class="icon tonics-dollar"> <use xlink:href="#tonics-dollar"></use></svg>',
  "align-left": '<svg class="icon tonics-align_left"> <use xlink:href="#tonics-align_left"></use></svg>',
  "align-right": '<svg class="icon tonics-align_right"> <use xlink:href="#tonics-align_right"></use></svg>',
  "align-column": '<svg class="icon tonics-align-column"> <use xlink:href="#tonics-align-column"></use></svg>',
  "align-row": '<svg class="icon tonics-align-row"> <use xlink:href="#tonics-align-row"></use></svg>',
  MEDIA: {
    "shuffle": '<svg class="icon tonics-shuffle"> <use xlink:href="#tonics-shuffle"></use></svg>',
    "refresh": '<svg class="icon tonics-refresh"> <use xlink:href="#tonics-refresh"></use></svg>',
    "step-forward": '<svg class="icon tonics-step-forward"> <use xlink:href="#tonics-step-forward"></use></svg>',
    "step-backward": '<svg class="icon tonics-step-backward"> <use xlink:href="#tonics-step-backward"></use></svg>',
    "pause-outline": '<svg class="icon tonics-pause-outline"> <use xlink:href="#tonics-pause-outline"></use></svg>',
    "play-outline": '<svg class="icon tonics-play-outline"> <use xlink:href="#tonics-play-outline"></use></svg>'
  },
  SOCIAL: {
    "mail": '<svg class="icon tonics-mail"> <use xlink:href="#tonics-mail"></use></svg>',
    "google-plus": '<svg class="icon tonics-google-plus"> <use xlink:href="#tonics-google-plus"></use></svg>',
    "hangouts": '<svg class="icon tonics-hangouts"> <use xlink:href="#tonics-hangouts"></use></svg>',
    "facebook": '<svg class="icon tonics-facebook"> <use xlink:href="#tonics-facebook"></use></svg>',
    "instagram": '<svg class="icon tonics-instagram"> <use xlink:href="#tonics-instagram"></use></svg>',
    "whatsapp": '<svg class="icon tonics-whatsapp"> <use xlink:href="#tonics-whatsapp"></use></svg>',
    "telegram": '<svg class="icon tonics-telegram"> <use xlink:href="#tonics-telegram"></use></svg>',
    "renren": '<svg class="icon tonics-renren"> <use xlink:href="#tonics-renren"></use></svg>',
    "rss": '<svg class="icon tonics-rss"> <use xlink:href="#tonics-rss"></use></svg>',
    "twitch": '<svg class="icon tonics-twitch"> <use xlink:href="#tonics-twitch"></use></svg>',
    "vimeo": '<svg class="icon tonics-vimeo"> <use xlink:href="#tonics-vimeo"></use></svg>',
    "flickr": '<svg class="icon tonics-flickr"> <use xlink:href="#tonics-flickr"></use></svg>',
    "dribble": '<svg class="icon tonics-dribble"> <use xlink:href="#tonics-dribble"></use></svg>',
    "behance": '<svg class="icon tonics-behance"> <use xlink:href="#tonics-behance"></use></svg>',
    "deviantart": '<svg class="icon tonics-deviantart"> <use xlink:href="#tonics-deviantart"></use></svg>',
    "500px": '<svg class="icon tonics-500px"> <use xlink:href="#tonics-500px"></use></svg>',
    "steam": '<svg class="icon tonics-steam"> <use xlink:href="#tonics-steam"></use></svg>',
    "soundcloud": '<svg class="icon tonics-soundcloud"> <use xlink:href="#tonics-soundcloud"></use></svg>',
    "skype": '<svg class="icon tonics-skype"> <use xlink:href="#tonics-skype"></use></svg>',
    "lastfm": '<svg class="icon tonics-lastfm"> <use xlink:href="#tonics-lastfm"></use></svg>',
    "linkedin": '<svg class="icon tonics-linkedin"> <use xlink:href="#tonics-linkedin"></use></svg>',
    "github": '<svg class="icon tonics-github"> <use xlink:href="#tonics-github"></use></svg>',
    "twitter": '<svg class="icon tonics-twitter"> <use xlink:href="#tonics-twitter"></use></svg>',
    "youtube": '<svg class="icon tonics-youtube"> <use xlink:href="#tonics-youtube"></use></svg>',
    "reddit": '<svg class="icon reddit"> <use xlink:href="#tonics-reddit"></use></svg>',
    "delicious": '<svg class="icon tonics-delicious"> <use xlink:href="#tonics-delicious"></use></svg>',
    "stackoverflow": '<svg class="icon tonics-stackoverflow"> <use xlink:href="#tonics-stackoverflow"></use></svg>',
    "pinterest": '<svg class="icon tonics-dashboard"> <use xlink:href="#tonics-pinterest"></use></svg>',
    "xing": '<svg class="icon tonics-dashboard"> <use xlink:href="#tonics-xing"></use></svg>',
    "flattr": '<svg class="icon tonics-flattr"> <use xlink:href="#tonics-flattr"></use></svg>',
    "foursquare": '<svg class="icon tonics-foursquare"> <use xlink:href="#tonics-foursquare"></use></svg>',
    "yelp": '<svg class="icon tonics-yelp"> <use xlink:href="#tonics-yelp"></use></svg>'
  },
  FILE: {
    "file": '<svg class="icon tonics-file"> <use xlink:href="#tonics-file"></use></svg>',
    "folder": '<svg class="icon tonics-folder"> <use xlink:href="#tonics-folder"></use></svg>',
    "image": '<svg class="icon tonics-file-image"> <use xlink:href="#tonics-file-image"></use></svg>',
    "load-more": '<svg class="icon tonics-load-more"> <use xlink:href="#tonics-load-more"></use></svg>',
    "music": '<svg class="icon tonics-music"> <use xlink:href="#tonics-music"></use></svg>',
    "note": '<svg class="icon tonics-note"> <use xlink:href="#tonics-note"></use></svg>',
    "pdf": '<svg class="icon tonics-pdf"> <use xlink:href="#tonics-pdf"></use></svg>',
    "docx": '<svg class="icon tonics-docx"> <use xlink:href="#tonics-docx"></use></svg>',
    "code": '<svg class="icon tonics-code"> <use xlink:href="#tonics-code"></use></svg>',
    "zip": '<svg class="icon tonics-zip"> <use xlink:href="#tonics-zip"></use></svg>',
    "compress": '<svg class="icon tonics-compress"> <use xlink:href="#tonics-compress"></use></svg>',
    "exclamation": '<svg class="icon tonics-exclamation"> <use xlink:href="#tonics-exclamation"></use></svg>'
  },
  CONTEXT: {
    "link": '<svg class="icon tonics-download-link"> <use xlink:href="#tonics-download-link"></use></svg>',
    "preview_link": '<svg class="icon tonics-link"> <use xlink:href="#tonics-link"></use></svg>',
    "edit": '<svg class="icon tonics-edit-icon"> <use xlink:href="#tonics-edit-icon"></use></svg>',
    "cut": '<svg class="icon tonics-cut-icon"> <use xlink:href="#tonics-cut"></use></svg>',
    "trash": '<svg class="icon tonics-trash-icon"> <use xlink:href="#tonics-trash-can"></use></svg>',
    "paste": '<svg class="icon tonics-paste-icon"> <use xlink:href="#tonics-paste"></use></svg>',
    "upload": '<svg class="icon tonics-plus-icon"> <use class="svgUse" xlink:href="#tonics-upload-icon"></use></svg>',
    "refresh": '<svg class="icon tonics-refresh"><use class="svgUse" xlink:href="#tonics-refresh"></use></svg>',
    "plus": '<svg class="icon tonics-plus"><use class="svgUse" xlink:href="#tonics-plus2"></use></svg>'
  }
};

// src/Core/Configs/MenuEventAction.ts
function MenuActions() {
  return {
    EDIT_IMAGE_FILE: "EditImageFileEvent",
    DELETE_FILE: "DeleteFileEvent",
    RENAME_FILE: "RenameFileEvent",
    CUT_FILE: "CutFileEvent",
    PASTE_FILE: "PasteFileEvent",
    UPLOAD_FILE: "UploadFileEvent",
    COPY_LINK: "CopyLinkEvent",
    COPY_PREVIEW_LINK: "CopyPreviewLinkEvent",
    CREATE_FOLDER: "NewFolderEvent",
    REFRESH_FOLDER: "RefreshFolderEvent"
  };
}
__name(MenuActions, "MenuActions");

// src/Util/Others/Helpers.ts
var import_sweetalert2 = __toModule(require_sweetalert2_all());

// src/Core/Configs/FileManagerElements.ts
var FileManagerElements = {
  HEAD: {
    PARENT: ".tonics-main-header-menu",
    MENU_SECTION: ".menu-section",
    NAV: "#site-navigation"
  },
  FILES: {
    BREADCRUMB: ".breadcrumb",
    CONTEXT: ".context-menu",
    PROGRESS: {
      CONTAINER: ".upload-progress-container",
      UPLOAD_FILE_CONTAINER: ".upload-files",
      UPLOAD_STRING: ".upload-string",
      PROGRESS_PERCENTAGE: ".upload-percentage",
      CONTROL: {
        RESUME_PAUSE: ".resume-pause",
        CANCEL: ".cancel"
      }
    },
    FILE_MAIN_CONTENT: ".tonics-fm-main-content",
    FILE_PARENT: ".tonics-files-parent",
    FILE_CONTAINER: ".tonics-files-container",
    SINGLE_FILE: "li.tonics-file"
  },
  DRIVE: {
    TOGGLE: ".drive-toggle",
    DRIVE_NAVIGATION: ".tonics-fm-nav-menu",
    DISK_DRIVE_CONTAINER: ".tonics-disk-drive-container",
    DRIVE_FOLDER: ".drive-folder",
    FILE_DISK_DRIVES: ".tonics-disk-drive-container",
    INDIVIDUAL_DRIVE: ".tonics-individual-drive",
    DRIVE_SELECTED: ".tonics-drive-selected"
  },
  Button: {
    FILE_LOAD_MORE: ".file-load-more"
  },
  SEARCH: ".filter-search"
};

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
    let self2 = this;
    this.getHttp().onreadystatechange = function() {
      try {
        if (self2.http.readyState === XMLHttpRequest.DONE) {
          if (self2.http.status === 200) {
            callBack(null, self2.http.response);
          } else {
            callBack(self2.http.response);
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
    let self2 = this;
    let onProgress = self2.getCallbacks().callbacks.onProgress;
    if (onProgress !== null && typeof onProgress == "function") {
      this.getHttp().upload.addEventListener("progress", function(e) {
        onProgress(e);
      });
    }
    this.getHttp().onreadystatechange = function() {
      try {
        self2.http.onload = function() {
          callBack(null, self2.http.responseText);
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
    let self2 = this;
    let onProgress = self2.getCallbacks().callbacks.onProgress;
    if (onProgress !== null && typeof onProgress == "function") {
      this.getHttp().upload.addEventListener("progress", function(e) {
        onProgress(e);
      });
    }
    try {
      this.http.onload = function() {
        if (self2.http.status === 200) {
          callBack(null, self2.http.response);
        } else {
          callBack(self2.http.response);
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
    let self2 = this;
    try {
      this.http.onload = function() {
        if (self2.http.status === 200) {
          callBack(null, self2.http.response);
        } else {
          callBack(self2.http.response);
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

// src/Util/Others/Helpers.ts
function getFileDirectory(filePath, stringToReturnIfEmpty = "") {
  let path = "";
  if (filePath.lastIndexOf("/") !== -1) {
    path = filePath.substring(0, filePath.lastIndexOf("/"));
  }
  if (filePath.lastIndexOf("\\") !== -1) {
    path = filePath.substring(0, filePath.lastIndexOf("\\"));
  }
  return path ? path : stringToReturnIfEmpty;
}
__name(getFileDirectory, "getFileDirectory");
function titleCase(str) {
  return str.toLowerCase().replace(/\b(\w)/g, function(s) {
    return s.toLocaleUpperCase();
  });
}
__name(titleCase, "titleCase");
function copyToClipBoard(clip) {
  return new Promise((resolve, reject) => {
    navigator.clipboard.writeText(clip).then(() => {
      resolve(clip);
    }).catch((e) => {
      reject(e);
    });
  });
}
__name(copyToClipBoard, "copyToClipBoard");
function getFileExtension(fileNameOrURL, showUnixDotFiles = false) {
  let fileName;
  let fileExt;
  const hiddenLink = document.createElement("a");
  hiddenLink.style.display = "none";
  hiddenLink.setAttribute("href", fileNameOrURL);
  fileNameOrURL = fileNameOrURL.replace(hiddenLink.protocol, "");
  fileNameOrURL = fileNameOrURL.replace(hiddenLink.hostname, "");
  fileNameOrURL = fileNameOrURL.replace(":" + hiddenLink.port, "");
  fileNameOrURL = fileNameOrURL.split("?")[0];
  fileNameOrURL = fileNameOrURL.split("#")[0];
  fileNameOrURL = fileNameOrURL.substr(1 + fileNameOrURL.lastIndexOf("/"));
  fileName = fileNameOrURL;
  if (!showUnixDotFiles) {
    if (fileName.startsWith(".")) {
      return "";
    }
  }
  if (fileName.lastIndexOf(".") === -1) {
    return "";
  }
  fileExt = fileName.substr(1 + fileName.lastIndexOf("."));
  return fileExt;
}
__name(getFileExtension, "getFileExtension");
function contextMenuListCreator(name, svg, menuAction) {
  return `
<li class="context-menu-item" data-menu-action=${menuAction}>
      ${svg}
      <a class="" href="javascript:void(0);">
        ${name}
      </a>
    </li>
`;
}
__name(contextMenuListCreator, "contextMenuListCreator");
function inputToast(inputTitle, defaultValue = "", type = "text") {
  return import_sweetalert2.default.fire({
    title: inputTitle,
    input: type,
    inputValue: defaultValue,
    inputAttributes: {
      autocapitalize: "off"
    },
    showCancelButton: true,
    confirmButtonText: "Save",
    backdrop: true,
    allowOutsideClick: () => !import_sweetalert2.default.isLoading(),
    confirmButtonColor: "#0c132c",
    focusConfirm: true,
    background: "#eaeaea",
    iconColor: "#264762d1"
  });
}
__name(inputToast, "inputToast");
function successToast(message, timer = 4e3) {
  const Toast = import_sweetalert2.default.mixin({
    toast: true,
    position: "bottom-right",
    showConfirmButton: false,
    timer,
    timerProgressBar: true,
    background: "#eaeaea",
    iconColor: "#264762d1",
    didOpen: (toast) => {
      toast.addEventListener("mouseenter", import_sweetalert2.default.stopTimer);
      toast.addEventListener("mouseleave", import_sweetalert2.default.resumeTimer);
    }
  });
  return Toast.fire({
    customClass: {
      title: "swal2-title-dark"
    },
    icon: "success",
    title: message
  });
}
__name(successToast, "successToast");
function infoToast(message, timer = 4e3) {
  const Toast = import_sweetalert2.default.mixin({
    toast: true,
    position: "bottom-right",
    showConfirmButton: false,
    timer,
    timerProgressBar: true,
    background: "#eaeaea",
    iconColor: "#264762d1",
    didOpen: (toast) => {
      toast.addEventListener("mouseenter", import_sweetalert2.default.stopTimer);
      toast.addEventListener("mouseleave", import_sweetalert2.default.resumeTimer);
    }
  });
  return Toast.fire({
    customClass: {
      title: "swal2-title-dark"
    },
    icon: "info",
    title: message
  });
}
__name(infoToast, "infoToast");
function errorToast(message, timer = 5e3) {
  const Toast = import_sweetalert2.default.mixin({
    toast: true,
    position: "bottom-right",
    showConfirmButton: false,
    timer,
    timerProgressBar: true,
    background: "#eaeaea",
    iconColor: "#941943",
    didOpen: (toast) => {
      toast.addEventListener("mouseenter", import_sweetalert2.default.stopTimer);
      toast.addEventListener("mouseleave", import_sweetalert2.default.resumeTimer);
    }
  });
  return Toast.fire({
    customClass: {
      title: "swal2-title-red"
    },
    icon: "error",
    title: message
  });
}
__name(errorToast, "errorToast");
function promptToast(title, confirmText = "Proceed", onConfirmed, onDenied = null, onDismiss = null) {
  const Toast = import_sweetalert2.default.mixin({
    toast: true,
    position: "bottom-right",
    timer: 5e4,
    timerProgressBar: true,
    showCancelButton: true,
    showConfirmButton: true,
    confirmButtonText: confirmText,
    confirmButtonColor: "#0c132c",
    focusConfirm: true,
    background: "#eaeaea",
    iconColor: "#264762d1",
    didOpen: (toast) => {
      toast.addEventListener("mouseenter", import_sweetalert2.default.stopTimer);
      toast.addEventListener("mouseleave", import_sweetalert2.default.resumeTimer);
    }
  });
  Toast.fire({
    title
  }).then((result) => {
    if (result.isConfirmed) {
      if (typeof onConfirmed == "function") {
        onConfirmed();
      }
    } else if (result.isDenied) {
      if (onDenied && typeof onDenied == "function") {
        onDenied();
      }
    } else if (result.isDismissed) {
      if (onDismiss && typeof onDismiss == "function") {
        onDismiss();
      }
    }
  });
}
__name(promptToast, "promptToast");
function activateMenus($listOfMenuToActivate) {
  let headerMenu = document.querySelector(FileManagerElements.HEAD.MENU_SECTION);
  $listOfMenuToActivate.forEach(function(value, index) {
    let eventMenu = headerMenu.querySelector(`[data-menu-action="${value}"]`);
    if (eventMenu) {
      eventMenu.closest(".menu-item").classList.remove("deactivate-menu-pointer");
      eventMenu.querySelector(".icon").classList.remove("deactivate-menu");
    }
  });
}
__name(activateMenus, "activateMenus");
function deActivateMenus($listOfMenuToActivate) {
  let headerMenu = document.querySelector(FileManagerElements.HEAD.MENU_SECTION);
  $listOfMenuToActivate.forEach(function(value, index) {
    let eventMenu = headerMenu.querySelector(`[data-menu-action="${value}"]`);
    if (eventMenu) {
      eventMenu.closest(".menu-item").classList.add("deactivate-menu-pointer");
      eventMenu.querySelector(".icon").classList.add("deactivate-menu");
    }
  });
}
__name(deActivateMenus, "deActivateMenus");
function loadScriptDynamically($scriptPath, $uniqueIdentifier) {
  return new Promise((resolve, reject) => {
    let scriptCheck = document.querySelector(`[data-script_id="${$uniqueIdentifier}"]`);
    if (scriptCheck) {
      resolve();
    } else {
      const script = document.createElement("script");
      script.dataset.script_id = $uniqueIdentifier;
      document.body.appendChild(script);
      script.onload = resolve;
      script.onerror = reject;
      script.async = true;
      script.src = $scriptPath;
    }
  });
}
__name(loadScriptDynamically, "loadScriptDynamically");
function getCSRFFromInput(csrfNames) {
  let csrf = null;
  csrfNames.forEach((value, index) => {
    var _a, _b;
    let inputCSRF = (_a = document.querySelector(`input[name=${value}]`)) == null ? void 0 : _a.value;
    if (!inputCSRF) {
      inputCSRF = (_b = document.querySelector(`meta[name=${value}]`)) == null ? void 0 : _b.content;
    }
    if (!csrf && inputCSRF) {
      csrf = inputCSRF;
    }
  });
  return csrf;
}
__name(getCSRFFromInput, "getCSRFFromInput");
function fileLoadMoreButton(showLoadMoreText = true, animation = false) {
  let loadMore = document.querySelector(FileManagerElements.Button.FILE_LOAD_MORE);
  let loadingAnimation = document.querySelector(".dot-elastic.loading");
  if (showLoadMoreText) {
    loadMore == null ? void 0 : loadMore.classList.remove("display-none");
    loadMore == null ? void 0 : loadMore.classList.add("display-flex");
  } else {
    loadMore == null ? void 0 : loadMore.classList.remove("display-flex");
    loadMore == null ? void 0 : loadMore.classList.add("display-none");
  }
  animation ? loadingAnimation.classList.remove("display-none") : loadingAnimation.classList.add("display-none");
}
__name(fileLoadMoreButton, "fileLoadMoreButton");
function filesLoadingAnimation(trigger = true) {
  let fileContainerParent = document.querySelector(FileManagerElements.FILES.FILE_PARENT), loadingAnimation = document.querySelector(".dot-elastic.loading");
  if (trigger) {
    let firstFile = document.querySelector("[data-list_id]:nth-of-type(1)");
    if (!firstFile) {
      fileContainerParent == null ? void 0 : fileContainerParent.classList.remove("align-content-fs");
      loadingAnimation.classList.remove("display-none");
    }
  } else {
    fileContainerParent == null ? void 0 : fileContainerParent.classList.add("align-content-fs");
    loadingAnimation.classList.add("display-none");
  }
}
__name(filesLoadingAnimation, "filesLoadingAnimation");
if (!window.hasOwnProperty("TonicsScript")) {
  window.TonicsScript = {};
}
window.TonicsScript.promptToast = (title, confirmText = "Proceed", onConfirmed, onDenied = null, onDismiss = null) => promptToast(title, confirmText, onConfirmed, onDenied, onDismiss);
window.TonicsScript.successToast = (message, timer = 4e3) => successToast(message, timer);
window.TonicsScript.errorToast = (message, timer = 4e3) => errorToast(message, timer);
window.TonicsScript.infoToast = (message, timer = 4e3) => infoToast(message, timer);

// src/Core/Configs/Messages.ts
var Message = {
  Rename: {
    Success: "File Successfully Renamed",
    Error: "Failed To Rename File"
  },
  Folder: {
    Success: "Folder Successfully Created",
    Error: "Failed To Create Folder"
  },
  Update: {
    Success: "File Successfully Updated",
    Error: "Failed To Update File"
  },
  Upload: {
    Success: "File Successfully Uploaded",
    Error: "Failed To Upload File"
  },
  Deleted: {
    Success: "File(s) Successfully Deleted",
    Error: "Failed To Delete File(s)"
  },
  Refresh: {
    Success: "Refreshed",
    Error: "Failed To Refresh Folder"
  },
  Move: {
    Success: "File(s) Successfully Moved",
    Error: "Failed To Move File(s)"
  },
  Link: {
    Copy: {
      Preview: {
        Success: "Preview Link Copied To Clipboard",
        Error: "Failed To Copy Link To Clipboard"
      },
      Download: {
        Success: "Download Link Copied To Clipboard",
        Error: "Failed To Copy Link To Clipboard"
      }
    }
  },
  Context: {
    Media: {
      Play: "Play",
      Pause: "Pause"
    },
    Rename: "Rename",
    Link: {
      Copy: "Download Link",
      Preview: "Preview Link"
    },
    Edit: {
      Image: "Edit Image"
    },
    Cut: "Cut",
    Delete: "Delete",
    Paste: "Paste",
    Refresh: "Refresh",
    Upload: "Upload",
    New_Folder: "New Folder"
  }
};

// src/Core/Events/OnAudioFileEvent.ts
var OnAudioFileEvent = class {
  get audioFile() {
    return this._audioFile;
  }
  constructor(audioFile) {
    this._audioFile = audioFile;
  }
};
__name(OnAudioFileEvent, "OnAudioFileEvent");

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

// src/Core/Handlers/AddClickEventToFileContainer.ts
var AddClickEventToFileContainer = class {
  constructor($fileContainerEvent) {
    this._shiftClick = new Map();
    this.fileContainerEvent = $fileContainerEvent;
    this.addClickEvent();
  }
  get shiftClick() {
    return this._shiftClick;
  }
  set shiftClick(value) {
    this._shiftClick = value;
  }
  addClickEvent() {
    let self2 = this;
    let $fileContainer = document.querySelector(FileManagerElements.FILES.FILE_PARENT);
    if (!$fileContainer.hasAttribute("data-event-click")) {
      $fileContainer.addEventListener("click", (e) => {
        $fileContainer.setAttribute("data-event-click", "true");
        let el = e.target;
        let singleFile = FileManagerElements.FILES.SINGLE_FILE;
        if (el.closest(".tonics-file-filename-input")) {
          return false;
        }
        if (el.closest(singleFile)) {
          let file = el.closest(singleFile);
          if (e.ctrlKey) {
            e.preventDefault();
            file.classList.contains("selected-file") ? self2.getFileContainerEvent().unHighlightFile(file) : self2.getFileContainerEvent().highlightFile(file);
            return false;
          } else if (e.shiftKey) {
            this.getFileContainerEvent().resetPreviousFilesState();
            this.setShiftClick(file);
          } else {
            self2.getFileContainerEvent().resetPreviousFilesState();
            self2.getFileContainerEvent().highlightFile(file);
            this.resetShiftClick();
            this.setShiftClick(file);
          }
        } else {
          this.resetShiftClick();
          self2.getFileContainerEvent().resetPreviousFilesState();
        }
      }, false);
    }
  }
  setShiftClick(file) {
    let id = file.dataset.list_id;
    if (this.shiftClick.get(id)) {
      this.shiftClick.delete(id);
    }
    this.shiftClick.set(id, file);
    if (this.shiftClick.size >= 2) {
      let firstItem = [...this.shiftClick][0][0], lastItem = [...this.shiftClick][this.shiftClick.size - 1][0], listIDToLoop = [firstItem, lastItem];
      listIDToLoop.sort();
      for (let i = listIDToLoop[0]; i <= listIDToLoop[1]; i++) {
        let file2 = document.querySelector(`[data-list_id="${i}"]`);
        if (file2) {
          this.getFileContainerEvent().highlightFile(file2);
        }
      }
    }
  }
  resetShiftClick() {
    this.shiftClick = new Map();
  }
  getFileContainerEvent() {
    return this.fileContainerEvent;
  }
};
__name(AddClickEventToFileContainer, "AddClickEventToFileContainer");

// src/Core/Handlers/DoubleClickEventToFileContainer.ts
var DoubleClickEventToFileContainer = class {
  constructor($fileContainerEvent) {
    let $fileContainer = $fileContainerEvent.getFileContainer();
    if (!$fileContainer.hasAttribute("data-event-dblclick")) {
      $fileContainer.setAttribute("data-event-dblclick", "true");
      $fileContainer.addEventListener("dblclick", (e) => {
        let file = $fileContainerEvent.getSelectedFile();
        if (file.dataset.file_type === "directory") {
          file.querySelector(".svg-per-file-loading").classList.remove("display-none");
          $fileContainerEvent.currentDrive.openFolderHandler(file, $fileContainerEvent).then(() => {
            $fileContainerEvent.resetPreviousFilesState();
          }).catch(() => {
            file.querySelector(".svg-per-file-loading").classList.add("display-none");
          });
        }
        if (file.dataset.file_type === "file") {
          $fileContainerEvent.copyPreviewLinkEvent();
        }
      });
    }
  }
};
__name(DoubleClickEventToFileContainer, "DoubleClickEventToFileContainer");

// src/Core/Handlers/ContextMenuProcessor.ts
var ContextMenuProcessor = class {
  constructor($data) {
    this.fileContainerEventObject = $data;
    this.addContextMenuClickEvent();
  }
  getFileContainerEventObject() {
    return this.fileContainerEventObject;
  }
  addContextMenuClickEvent() {
    let contextMenu = document.querySelector(FileManagerElements.FILES.CONTEXT);
    document.addEventListener("click", (e) => {
      let el = e.target;
      if (el.closest(FileManagerElements.FILES.CONTEXT)) {
        return false;
      } else {
        contextMenu.classList.remove("show");
      }
    });
    if (!(contextMenu == null ? void 0 : contextMenu.hasAttribute("data-event-contextmenu"))) {
      contextMenu.setAttribute("data-event-contextmenu", "true");
      contextMenu.addEventListener("click", (e) => {
        let el = e.target;
        if (el.closest(".context-menu-item").hasAttribute("data-menu-action")) {
          let contextMenuItemEvent = el.closest(".context-menu-item").getAttribute("data-menu-action");
          this.getFileContainerEventObject().menuEventAction(contextMenuItemEvent);
        }
      });
    }
  }
};
__name(ContextMenuProcessor, "ContextMenuProcessor");

// src/Core/Commands/FilePlacement/DefaultFilePlacement.ts
var DefaultFilePlacement = class {
  extensions() {
    return [""];
  }
  run($data, ext, callback = null) {
    if (callback) {
      if (typeof callback == "function") {
        return callback(ICONS.FILE.exclamation, ext, $data);
      }
    }
  }
  fileContext($fileContainerEvent) {
    return `
    ${contextMenuListCreator(Message.Context.Rename, ICONS.CONTEXT.edit, MenuActions().RENAME_FILE)}
    ${contextMenuListCreator(Message.Context.Link.Copy, ICONS.CONTEXT.link, MenuActions().COPY_LINK)}
    ${contextMenuListCreator(Message.Context.Link.Preview, ICONS.CONTEXT.preview_link, MenuActions().COPY_PREVIEW_LINK)}
    ${contextMenuListCreator(Message.Context.Cut, ICONS.CONTEXT.cut, MenuActions().CUT_FILE)}
    ${contextMenuListCreator(Message.Context.Delete, ICONS.CONTEXT.trash, MenuActions().DELETE_FILE)}`;
  }
};
__name(DefaultFilePlacement, "DefaultFilePlacement");

// src/Core/Commands/FilePlacement/BackgroundFilePlacement.ts
var BackgroundFilePlacement = class {
  fileContext($fileContainerEvent) {
    return `
    ${contextMenuListCreator(Message.Context.Refresh, ICONS.CONTEXT.refresh, MenuActions().REFRESH_FOLDER)}
    ${contextMenuListCreator(Message.Context.New_Folder, ICONS.CONTEXT.plus, MenuActions().CREATE_FOLDER)}
    ${contextMenuListCreator(Message.Context.Upload, ICONS.CONTEXT.upload, MenuActions().UPLOAD_FILE)}`;
  }
};
__name(BackgroundFilePlacement, "BackgroundFilePlacement");

// src/Core/Handlers/ContextHandler.ts
var ContextHandler = class {
  constructor($data) {
    let contextMenu = $data.getContextMenu();
    let fileContainer = $data.getFileContainer();
    fileContainer.addEventListener("contextmenu", (e) => {
      let el = e.target;
      e.preventDefault();
      if (el.closest(FileManagerElements.FILES.SINGLE_FILE)) {
        let file = el.closest(FileManagerElements.FILES.SINGLE_FILE);
        let fileExtension = file.dataset.ext;
        let assignedAnExtension = false;
        CommandsConfig.FileByExtensions.forEach((extIdentifier) => {
          if (extIdentifier.extensions().includes(fileExtension)) {
            assignedAnExtension = true;
            $data.highlightFile(file);
            this.showContextMenu(contextMenu, extIdentifier.fileContext($data), e);
          }
        });
        if (!assignedAnExtension) {
          $data.highlightFile(file);
          this.showContextMenu(contextMenu, new DefaultFilePlacement().fileContext($data), e);
        }
      } else {
        this.showContextMenu(contextMenu, new BackgroundFilePlacement().fileContext($data), e);
      }
    });
  }
  showContextMenu(contextMenu, contextMenuChildren, e) {
    contextMenu == null ? void 0 : contextMenu.replaceChildren();
    contextMenu.insertAdjacentHTML("beforeend", contextMenuChildren);
    let x = e.clientX, y = e.clientY;
    contextMenu.classList.remove("show");
    contextMenu.style.top = `${y}px`;
    contextMenu.style.left = `${x - 30}px`;
    setTimeout(() => {
      contextMenu.classList.add("show");
    });
  }
};
__name(ContextHandler, "ContextHandler");

// src/Core/Handlers/HeaderSectionHandler.ts
var HeaderSectionHandler = class {
  constructor($data) {
    this.fileContainerEventObject = $data;
    this.addHeaderMenuClickEvent();
  }
  getFileContainerEventObject() {
    return this.fileContainerEventObject;
  }
  addHeaderMenuClickEvent() {
    let headerSection = document.querySelector(".site-navigation-ul");
    if (headerSection) {
      if (!(headerSection == null ? void 0 : headerSection.hasAttribute("data-event-menu"))) {
        headerSection.setAttribute("data-event-menu", "true");
        headerSection.addEventListener("click", (e) => {
          let el = e.target;
          if (el.closest("button")) {
            let button = el.closest("button");
            if (button.hasAttribute("data-menu-action")) {
              let ItemEvent = button.getAttribute("data-menu-action");
              this.getFileContainerEventObject().menuEventAction(ItemEvent);
            }
          }
        });
      }
    }
  }
  hideHeaderMenuOnScroll() {
    let headerMenu = document.querySelector(FileManagerElements.HEAD.PARENT), headerHeight = headerMenu == null ? void 0 : headerMenu.getBoundingClientRect().height, previousScrollPosition = 20;
    window.addEventListener("scroll", () => {
      previousScrollPosition > window.scrollY ? headerMenu.style.top = "0" : headerMenu.style.top = `-${headerHeight}px`;
      previousScrollPosition = window.scrollY;
    });
  }
};
__name(HeaderSectionHandler, "HeaderSectionHandler");

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

// src/Util/Others/DragAndDrop.ts
var DragAndDrop = class extends ElementAbstract {
  constructor($dragAndDropElement) {
    super($dragAndDropElement);
    this.$dragAndDropDetails = {};
    this.settings();
  }
  settings() {
    this.getDragAndDropElementDetails().callbacks = {
      onDragEnter: null,
      onDragOver: null,
      onDragLeave: null,
      onDragDrop: null
    };
  }
  getDragAndDropElementDetails() {
    return this.$dragAndDropDetails;
  }
  checkIfSettingsIsSet() {
    if (!this.getDragAndDropElementDetails().callbacks) {
      throw new DOMException("No Callbacks exist for the DragAndDropElement");
    }
    return true;
  }
  onDragEnter($onDragEnter) {
    if (this.checkIfSettingsIsSet()) {
      this.getDragAndDropElementDetails().callbacks.onDragEnter = $onDragEnter;
      return this;
    }
  }
  onDragOver($onDragOver) {
    if (this.checkIfSettingsIsSet()) {
      this.getDragAndDropElementDetails().callbacks.onDragOver = $onDragOver;
      return this;
    }
  }
  onDragLeave($onDragLeave) {
    if (this.checkIfSettingsIsSet()) {
      this.getDragAndDropElementDetails().callbacks.onDragLeave = $onDragLeave;
      return this;
    }
  }
  onDragDrop($onDragDrop) {
    if (this.checkIfSettingsIsSet()) {
      this.getDragAndDropElementDetails().callbacks.onDragDrop = $onDragDrop;
      return this;
    }
  }
  run() {
    let $dragAndDropElement = this.getQueryResult(), self2 = this;
    if ($dragAndDropElement) {
      $dragAndDropElement.addEventListener("dragenter", function(e) {
        self2.preventDefaults(e);
        $dragAndDropElement.classList.add("highlight");
        let onDragEnter = self2.getDragAndDropElementDetails().callbacks.onDragEnter;
        if (onDragEnter !== null && typeof onDragEnter == "function") {
          onDragEnter(e);
        }
      });
      $dragAndDropElement.addEventListener("dragover", function(e) {
        self2.preventDefaults(e);
        $dragAndDropElement.classList.add("highlight");
        let onDragOver = self2.getDragAndDropElementDetails().callbacks.onDragOver;
        if (onDragOver !== null && typeof onDragOver == "function") {
          onDragOver(e);
        }
      });
      $dragAndDropElement.addEventListener("dragleave", function(e) {
        self2.preventDefaults(e);
        $dragAndDropElement.classList.remove("highlight");
        let onDragLeave = self2.getDragAndDropElementDetails().callbacks.onDragLeave;
        if (onDragLeave !== null && typeof onDragLeave == "function") {
          onDragLeave(e);
        }
      });
      $dragAndDropElement.addEventListener("drop", function(e) {
        self2.preventDefaults(e);
        $dragAndDropElement.classList.remove("highlight");
        let onDragDrop = self2.getDragAndDropElementDetails().callbacks.onDragDrop;
        if (onDragDrop !== null && typeof onDragDrop == "function") {
          onDragDrop(e);
        }
      }, false);
    }
  }
  preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }
};
__name(DragAndDrop, "DragAndDrop");
if (!window.hasOwnProperty("TonicsScript")) {
  window.TonicsScript = {};
}
window.TonicsScript.DragAndDrop = ($dragAndDropElement) => new DragAndDrop($dragAndDropElement);

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

// src/Core/Events/UploadFileEvent.ts
var UploadFileEvent = class {
  constructor($fileContainerEvent, files, uploadTo) {
    this._$uploadedFilesObject = new Map();
    this._maxRequestToSend = 4;
    this._byteToSendPerChunk = 0.5 * 1048576;
    this._isUploadInParallel = false;
    this._$fileSequence = {};
    this.fileContainerEvent = $fileContainerEvent;
    if (files !== null && uploadTo !== null) {
      this.handleFiles(files, uploadTo);
    }
    this.UploadFileEvent = this;
  }
  get maxRequestToSend() {
    return this._maxRequestToSend;
  }
  get $uploadedFilesObject() {
    return this._$uploadedFilesObject;
  }
  get $fileSequence() {
    return this._$fileSequence;
  }
  set $fileSequence(value) {
    this._$fileSequence = value;
  }
  get byteToSendPerChunk() {
    return this._byteToSendPerChunk;
  }
  set byteToSendPerChunk(value) {
    this._byteToSendPerChunk = value;
  }
  get isUploadInParallel() {
    return this._isUploadInParallel;
  }
  set isUploadInParallel(value) {
    this._isUploadInParallel = value;
  }
  getFileContainerEvent() {
    return this.fileContainerEvent;
  }
  handleFiles(files, uploadTo) {
    this.handleUploadedFiles(files, uploadTo);
  }
  setUploadFileObject(filename, fileSettings) {
    this.$uploadedFilesObject.set(filename, fileSettings);
  }
  handleFileUpload() {
    let self2 = this;
    let fileContainerEvent = this.fileContainerEvent;
    let input = document.createElement("input");
    input.type = "file";
    input.multiple = true;
    input.click();
    input.onchange = function(e) {
      let files = e.target.files;
      const uploadTo = fileContainerEvent.getCurrentDirectory();
      self2.handleUploadedFiles(files, uploadTo);
    };
  }
  handleUploadedFiles(files, uploadTo) {
    let self2 = this;
    let $uploadFileEv;
    let fileContainerEvent = self2.fileContainerEvent;
    fileContainerEvent.removeContextMenu();
    for (let i = 0, len = files.length; i < len; i++) {
      let uploadFilename = fileContainerEvent.currentDrive.driveSignature + "_" + files[i].name;
      self2.addFileToProgressContainer(files[i], uploadFilename, null, (e, filename, fileObject, $uploadFileEv2) => {
        $uploadFileEv2 = self2.UploadFileEvent;
        fileContainerEvent.currentDrive.uploadFile(fileObject, filename, $uploadFileEv2);
      }, (fileObject, $uploadFileEv2) => {
        $uploadFileEv2 = self2.UploadFileEvent;
        fileContainerEvent.currentDrive.cancelFileUploadHandler(fileObject, $uploadFileEv2).then(() => {
          successToast(`${fileObject.fileObject.name} Upload Terminated`);
        }).catch(() => {
          errorToast("Failed To Cancel Upload");
        });
      });
      if (self2.isUploadingSequentially()) {
        self2.attachFileToSequence(uploadFilename);
      }
    }
    if (self2.isUploadingSequentially()) {
      self2.uploadFileNextSequence(fileContainerEvent.currentDrive.driveSignature);
    } else {
      self2.uploadFiles();
    }
  }
  uploadFiles() {
    this.$uploadedFilesObject.forEach((fileSettings, filename) => {
      if (!fileSettings.uploaded) {
        this.fileContainerEvent.currentDrive.uploadFile(fileSettings, filename, this);
      }
    });
  }
  uploadFileNextSequence(driveSignature) {
    let fileContainerEvent = this.fileContainerEvent;
    let $storageAdapter = fileContainerEvent.loadDriveEventClass.driveStorageManager.getDriveStorage(driveSignature);
    const nextSequenceKey = this.getUploadFileNextSequence(driveSignature);
    if (nextSequenceKey) {
      if (typeof nextSequenceKey === "string") {
        let fileObject = this.getUploadFileObject(nextSequenceKey, $storageAdapter.driveSignature);
        $storageAdapter.uploadFile(fileObject, nextSequenceKey, this);
      }
    }
  }
  getUploadFileObject(filename, driveSignature) {
    for (const value of this.$uploadedFilesObject.values()) {
      if (value.driveSignature !== driveSignature) {
        continue;
      }
      return this.$uploadedFilesObject.get(filename);
    }
  }
  getUploadFileNextSequence(driveSignature) {
    let sequences = this.$fileSequence;
    let nextSequence = false;
    for (const key in sequences) {
      if (sequences.hasOwnProperty(key)) {
        if (key.split("_")[0] !== driveSignature) {
          continue;
        }
        if (sequences[key].progressing === false) {
          this.$fileSequence[key] = { progressing: true, sequenceDone: false };
          nextSequence = key;
          break;
        }
      }
    }
    return nextSequence;
  }
  setSequenceDone(filename, driveSignature) {
    let uploadFilename = `${driveSignature}_${filename}`;
    if (this.$fileSequence.hasOwnProperty(uploadFilename)) {
      this.$fileSequence[uploadFilename] = { progressing: true, sequenceDone: true };
    }
  }
  deleteUploadFileObject(uploadFilename) {
    this.$uploadedFilesObject.delete(uploadFilename);
    delete this.$fileSequence[uploadFilename];
  }
  addFileToProgressContainer(file, uploadFilename, onFilePause = null, onFileResume = null, onFileCancel = null) {
    var _a;
    let $uploadFileEvent = this;
    let fileContainerEvent = this.fileContainerEvent, byteToSendPerChunk = this.byteToSendPerChunk, driveSignature = fileContainerEvent.currentDrive.driveSignature, fileObject = {
      fileObject: file,
      driveSignature,
      preFlightData: {
        filename: file.name,
        dataToFill: null,
        chunksToSend: Math.ceil(file.size / byteToSendPerChunk),
        Byteperchunk: byteToSendPerChunk,
        Totalblobsize: file.size,
        maxRequestToSend: fileContainerEvent.currentDrive.getMaxRequestToSend(),
        noOfReceivedResponse: 0,
        throttleSwitch: false,
        sentApi: 0,
        nextIndex: 0
      },
      newFile: true,
      uploadTo: fileContainerEvent.getCurrentDirectory(),
      fetched: false,
      pause: false,
      uploaded: false
    }, $fileProgressElement = (_a = document.querySelector(FileManagerElements.FILES.PROGRESS.UPLOAD_FILE_CONTAINER)) == null ? void 0 : _a.querySelector(`[data-filename="${uploadFilename}"]`);
    let uploadFilesContainer = document.querySelector(FileManagerElements.FILES.PROGRESS.UPLOAD_FILE_CONTAINER);
    uploadFilesContainer.classList.remove("display-none");
    if (!$fileProgressElement) {
      uploadFilesContainer.insertAdjacentHTML("beforeend", `
      <div class="inner-file-upload-container" tabindex="0" data-pause="false" data-filename="${uploadFilename}" data-uploaded="false">

        <div class="info">
         <span class="upload-string">[${fileContainerEvent.currentDrive.driveSignature} Drive] -  \u22EF Uploading [</span>
         <span class="upload-progress-name" tabindex="0">${uploadFilename.split("_")[1]}</span>
         <span class="delimiter">] \xBB </span>
         <span class="upload-percentage">0%</span>
       </div>
      <div class="control">
          <button title="Pause" class="resume-pause background:transparent border:none color:black border-width:default border:black padding:default margin-top:0 cursor:pointer">
            Pause
        </button>
        <button title="Cancel" class="cancel background:transparent border:none color:black border-width:default border:black padding:default margin-top:0 cursor:pointer">
            Cancel
        </button>
        </div>
      </div>`);
      $uploadFileEvent.$uploadedFilesObject.set(uploadFilename, fileObject);
    }
    if (!uploadFilesContainer.hasAttribute("data-event-click")) {
      uploadFilesContainer.setAttribute("data-event-click", "true");
      uploadFilesContainer.addEventListener("click", (e) => {
        var _a2;
        let el = e.target;
        if (el.closest(FileManagerElements.FILES.PROGRESS.CONTROL.RESUME_PAUSE)) {
          let resumePauseButton = el.closest(FileManagerElements.FILES.PROGRESS.CONTROL.RESUME_PAUSE);
          el.closest(".inner-file-upload-container").dataset.pause = el.closest(".inner-file-upload-container").dataset.pause === "true" ? "false" : "true";
          let innerFilePause = el.closest(".inner-file-upload-container").dataset.pause, uploadFilename2 = el.closest(".inner-file-upload-container").dataset.filename, fileObject2 = $uploadFileEvent.getUploadFileObject(uploadFilename2, fileContainerEvent.currentDrive.driveSignature);
          if (innerFilePause === "true") {
            resumePauseButton.innerText = "Resume";
            resumePauseButton.title = "Resume";
            fileObject2.pause = true;
            this.$uploadedFilesObject.set(uploadFilename2, fileObject2);
            if (typeof onFilePause == "function") {
              onFilePause(e, uploadFilename2, fileObject2, $uploadFileEvent);
            }
          }
          if (innerFilePause === "false") {
            resumePauseButton.innerText = "Pause";
            resumePauseButton.title = "Pause";
            fileObject2.pause = false;
            this.$uploadedFilesObject.set(uploadFilename2, fileObject2);
            if (typeof onFileResume == "function") {
              if (!fileObject2.uploaded && !fileObject2.pause) {
                onFileResume(e, uploadFilename2, fileObject2, $uploadFileEvent);
              }
            }
          }
        }
        if (el.closest(FileManagerElements.FILES.PROGRESS.CONTROL.CANCEL)) {
          let fileUploadProgress = el.closest(".inner-file-upload-container"), uploadFilename2 = fileUploadProgress.dataset.filename, fileObject2 = $uploadFileEvent.getUploadFileObject(uploadFilename2, fileContainerEvent.currentDrive.driveSignature);
          if (uploadFilename2 && fileObject2) {
            if (typeof onFileCancel == "function") {
              this.deleteUploadFileObject(uploadFilename2);
              onFileCancel(fileObject2, fileContainerEvent);
              fileUploadProgress.classList.add("file-upload-cancelled");
              fileUploadProgress.dataset.filename = "";
              (_a2 = fileUploadProgress.querySelector(".control")) == null ? void 0 : _a2.remove();
            }
          }
        }
      });
    }
  }
  isUploadingSequentially() {
    return !this.isUploadInParallel;
  }
  attachFileToSequence(filename) {
    if (!this.$fileSequence.hasOwnProperty(filename)) {
      this.$fileSequence[filename] = { progressing: false, sequenceDone: false };
    }
  }
  updateFileProgress(filename, uploadPercentage = 0, driveSignature, uploadString = " \u22EF Uploading") {
    let $uploadFileContainer = new Query(FileManagerElements.FILES.PROGRESS.UPLOAD_FILE_CONTAINER).getQueryResult();
    let $file = $uploadFileContainer.querySelector(`[data-filename="${filename}"]`);
    if ($file) {
      $file.querySelector(FileManagerElements.FILES.PROGRESS.UPLOAD_STRING).innerHTML = `[${driveSignature} Drive] - ${uploadString} [`;
      $file.querySelector(FileManagerElements.FILES.PROGRESS.PROGRESS_PERCENTAGE).innerHTML = `${uploadPercentage}%`;
      $file.style.background = `linear-gradient(to right, #bab8b8 ${uploadPercentage}%, #ffffff00 0%)`;
    }
  }
  setUploadCompleted(filename, driveSignature) {
    var _a, _b;
    let fileSettings = this.getUploadFileObject(filename, driveSignature), $file = (_a = document.querySelector(FileManagerElements.FILES.PROGRESS.UPLOAD_FILE_CONTAINER)) == null ? void 0 : _a.querySelector(`[data-filename="${filename}"]`);
    if ($file) {
      (_b = $file.querySelector(".control")) == null ? void 0 : _b.remove();
      this.updateFileProgress(filename, 100, driveSignature, `\u2713 Completed `);
      fileSettings.uploaded = true;
      this.$uploadedFilesObject.set(filename, fileSettings);
      $file.dataset.uploaded = "true";
      this.$uploadedFilesObject.delete($file.dataset.uploadFilename);
      successToast(Message.Upload.Success);
    }
  }
  releaseThrottle(filename, driveSignature) {
    let fileSettings = this.getUploadFileObject(filename, driveSignature);
    if (fileSettings) {
      fileSettings.preFlightData.throttleSwitch = false;
      this.$uploadedFilesObject.set(filename, fileSettings);
      fileSettings.preFlightData.sentApi = 0;
      this.$uploadedFilesObject.set(filename, fileSettings);
      fileSettings.preFlightData.noOfReceivedResponse = 0;
      this.$uploadedFilesObject.set(filename, fileSettings);
    }
  }
};
__name(UploadFileEvent, "UploadFileEvent");

// src/Core/Handlers/FileContainerDragAndDropHandler.ts
var FileContainerDragAndDropHandler = class {
  constructor($data) {
    this._maxRequestToSend = 4;
    this.fileContainerEventObject = $data;
    this.handleDragAndDropFileUploads();
  }
  getFileContainerEventObject() {
    return this.fileContainerEventObject;
  }
  handleDragAndDropFileUploads() {
    var _a;
    let self2 = this;
    let fileContainer = self2.getFileContainerEventObject().getFileContainer().closest(".tonics-files-parent");
    if (!fileContainer.hasAttribute("data-drag_drop")) {
      fileContainer.setAttribute("data-drag_drop", "true");
      let dragAndDrop = new DragAndDrop(FileManagerElements.FILES.FILE_PARENT);
      (_a = dragAndDrop == null ? void 0 : dragAndDrop.onDragDrop(function(event) {
        var _a2;
        let files = (_a2 = event == null ? void 0 : event.dataTransfer) == null ? void 0 : _a2.files;
        const uploadTo = self2.uploadToDirectory();
        new UploadFileEvent(self2.getFileContainerEventObject(), files, uploadTo);
      })) == null ? void 0 : _a.run();
    }
  }
  uploadToDirectory() {
    return this.getFileContainerEventObject().getCurrentDirectory();
  }
  get maxRequestToSend() {
    return this._maxRequestToSend;
  }
  set maxRequestToSend(value) {
    this._maxRequestToSend = value;
  }
};
__name(FileContainerDragAndDropHandler, "FileContainerDragAndDropHandler");

// src/Core/Handlers/SwitchDriveStorageHandler.ts
var SwitchDriveStorageHandler = class {
  constructor($fileContainerEvent) {
    let diskDrives = $fileContainerEvent.getDiskDrives();
    if (!diskDrives.hasAttribute("data-event-click")) {
      diskDrives.setAttribute("data-event-click", "true");
      diskDrives.addEventListener("click", (e) => {
        let el = e.target;
        if (el.closest(FileManagerElements.DRIVE.INDIVIDUAL_DRIVE)) {
          let drive = el.closest(FileManagerElements.DRIVE.INDIVIDUAL_DRIVE);
          let driveStorageManager = $fileContainerEvent.getLoadDriveEventClass();
          if (driveStorageManager.driveStorageManager.$driveSystem.has(drive.dataset.drivename)) {
            let storage = driveStorageManager.driveStorageManager.getDriveStorage(drive.dataset.drivename);
            storage.coldBootStorageDisk().then(() => {
              $fileContainerEvent.removeAllDriveSelectionMark();
              drive.querySelector(FileManagerElements.DRIVE.DRIVE_SELECTED).classList.remove("display-none");
              $fileContainerEvent.currentDrive = storage;
            }).catch(() => {
              errorToast("Failed To Switch Drive, Network Issue?");
            });
          }
        }
      });
    }
  }
};
__name(SwitchDriveStorageHandler, "SwitchDriveStorageHandler");

// src/Core/Handlers/LoadMoreFilesHandler.ts
var LoadMoreFilesHandler = class {
  constructor($fileContainerEvent) {
    this.fileContainerEvent = $fileContainerEvent;
    this.loadMoreButtonHandle();
  }
  getFileContainerEvent() {
    return this.fileContainerEvent;
  }
  loadMoreButtonHandle() {
    let loadMore = document.querySelector(FileManagerElements.Button.FILE_LOAD_MORE);
    if (!loadMore.hasAttribute("data-event-click")) {
      loadMore.addEventListener("click", (e) => {
        loadMore.setAttribute("data-event-click", "true");
        fileLoadMoreButton(false, true);
        let element = document.querySelector("[data-list_id]:last-of-type");
        this.getFileContainerEvent().currentDrive.loadMoreFiles(this.getFileContainerEvent()).then(() => {
          element.scrollIntoView({ behavior: "smooth", block: "start", inline: "nearest" });
        }).catch(() => {
          errorToast("Failed To Load More Files");
          fileLoadMoreButton();
        });
      });
    }
  }
};
__name(LoadMoreFilesHandler, "LoadMoreFilesHandler");

// src/Core/Handlers/NavigateFilesByKeyboardKeysHandler.ts
var NavigateFilesByKeyboardKeysHandler = class {
  constructor($fileContainerEvent) {
    this.fileContainerEvent = $fileContainerEvent;
    this.handleNavigationByKeyPres();
  }
  getFileContainerEvent() {
    return this.fileContainerEvent;
  }
  handleNavigationByKeyPres() {
    let fileContainer = document.querySelector(FileManagerElements.FILES.FILE_CONTAINER);
    fileContainer.addEventListener("keydown", (e) => {
      if (this.getFileContainerEvent().getSelectedFile()) {
        let selectedFile = this.getFileContainerEvent().getSelectedFile();
        switch (e.code) {
          case "ArrowDown":
            this.navigateDown(selectedFile);
            break;
          case "ArrowUp":
            this.navigateUp(selectedFile);
            break;
          case "ArrowRight":
            this.navigateRight(selectedFile);
            break;
          case "ArrowLeft":
            this.navigateLeft(selectedFile);
            break;
          case "Enter":
            this.navigateEnter(selectedFile);
            break;
        }
      }
    });
  }
  navigateDown(element) {
    let numbersOfItemPerRow = this.getNumberOfFilesPerRow();
    let itemUpListID = parseInt(element.dataset.list_id) + numbersOfItemPerRow;
    element = this.getFileContainerEvent().getFileByListID(itemUpListID);
    if (element) {
      this.getFileContainerEvent().resetPreviousFilesState();
      element.scrollIntoView();
      this.getFileContainerEvent().highlightFile(element);
      this.removeHeaderMenuFromViewPort();
    }
  }
  navigateUp(element) {
    let numbersOfItemPerRow = this.getNumberOfFilesPerRow();
    let itemUpListID = parseInt(element.dataset.list_id) - numbersOfItemPerRow;
    element = this.getFileContainerEvent().getFileByListID(itemUpListID);
    if (element) {
      this.getFileContainerEvent().resetPreviousFilesState();
      element.scrollIntoView();
      this.getFileContainerEvent().highlightFile(element);
      this.removeHeaderMenuFromViewPort();
    }
  }
  navigateRight(element) {
    let moveRightElement = element.nextElementSibling;
    if (moveRightElement) {
      this.getFileContainerEvent().resetPreviousFilesState();
      moveRightElement.scrollIntoView();
      this.getFileContainerEvent().highlightFile(moveRightElement);
      this.removeHeaderMenuFromViewPort();
    }
  }
  navigateLeft(element) {
    let moveLeftElement = element.previousElementSibling;
    if (moveLeftElement) {
      this.getFileContainerEvent().resetPreviousFilesState();
      moveLeftElement.scrollIntoView();
      this.getFileContainerEvent().highlightFile(moveLeftElement);
      this.removeHeaderMenuFromViewPort();
    }
  }
  removeHeaderMenuFromViewPort() {
    let headerMenu = document.querySelector(FileManagerElements.HEAD.PARENT), headerHeight = headerMenu == null ? void 0 : headerMenu.getBoundingClientRect().height;
    headerMenu.style.top = `-${headerHeight}px`;
  }
  getNumberOfFilesPerRow() {
    let firstFile = document.querySelector("[data-list_id]:nth-of-type(1)");
    if (!firstFile) {
      return 0;
    }
    return this.getRemainingRowsOfItemToTheRight(firstFile) + 1;
  }
  getRemainingRowsOfItemToTheRight(element) {
    let prevElement = element, currentElement = element.nextElementSibling, numberOfRows = 0;
    if (currentElement) {
      while (prevElement.offsetTop === currentElement.offsetTop) {
        numberOfRows++;
        prevElement = currentElement;
        currentElement = currentElement.nextElementSibling;
      }
    }
    return numberOfRows;
  }
  getRemainingRowsOfItemToTheLeft(element) {
    let prevElement = element, currentElement = element.previousElementSibling, numberOfRows = 0;
    if (currentElement) {
      while (prevElement.offsetTop === currentElement.offsetTop) {
        numberOfRows++;
        prevElement = currentElement;
        currentElement = currentElement.previousElementSibling;
      }
    }
    return numberOfRows;
  }
  navigateEnter(selectedFile) {
    selectedFile.querySelector(".svg-per-file-loading").classList.remove("display-none");
    this.getFileContainerEvent().currentDrive.openFolderHandler(selectedFile, this.getFileContainerEvent()).catch(() => {
      selectedFile.querySelector(".svg-per-file-loading").classList.add("display-none");
    });
  }
};
__name(NavigateFilesByKeyboardKeysHandler, "NavigateFilesByKeyboardKeysHandler");

// src/Core/Handlers/SearchFilesInFolderHandler.ts
var SearchFilesInFolderHandler = class {
  constructor($fileContainerEvent) {
    this.fileContainerEvent = $fileContainerEvent;
    this.handleSearch();
  }
  handleSearch() {
    let searchInput = document.querySelector(FileManagerElements.SEARCH);
    searchInput.addEventListener("keyup", (e) => {
      if (e.code === "Enter") {
        let searchInputValue = searchInput.value;
        this.getFileContainerEvent().currentDrive.searchFiles(searchInputValue, this.getFileContainerEvent()).then(() => {
          searchInput.value = "";
        }).catch(() => {
          errorToast("An Error Occurred While Searching");
        });
      }
    });
  }
  getFileContainerEvent() {
    return this.fileContainerEvent;
  }
};
__name(SearchFilesInFolderHandler, "SearchFilesInFolderHandler");

// src/Core/Handlers/BreadCrumbHandler.ts
var BreadCrumbHandler = class {
  constructor($fileContainerEvent) {
    this.fileContainerEvent = $fileContainerEvent;
    this.handleBreadCrumbNavigation();
  }
  getFileContainerEvent() {
    return this.fileContainerEvent;
  }
  handleBreadCrumbNavigation() {
    let crumb = document.querySelector(".breadcrumb");
    crumb.addEventListener("click", (e) => {
      let el = e.target;
      if (el.hasAttribute("data-pathtrail")) {
        let path = el.getAttribute("data-pathtrail");
        this.getFileContainerEvent().currentDrive.breadCrumbClickNavigationHandler(path).then(() => {
          this.getFileContainerEvent().resetPreviousFilesState();
        });
      }
    });
    window.addEventListener("keydown", (e) => {
      switch (e.code) {
        case "Backspace":
          this.navigateBackSpace();
          break;
      }
    });
  }
  navigateBackSpace() {
    let bCrumb = document.querySelectorAll(".breadcrumb a"), secondLast = bCrumb[bCrumb.length - 2];
    if (secondLast) {
      let path = secondLast.dataset.pathtrail;
      this.getFileContainerEvent().currentDrive.breadCrumbClickNavigationHandler(path).then(() => {
        this.getFileContainerEvent().resetPreviousFilesState();
      });
    }
  }
};
__name(BreadCrumbHandler, "BreadCrumbHandler");

// src/Core/Handlers/Third-Party/TinymceCopyLinkHandler.ts
var TinymceCopyLinkHandler = class {
  constructor($copyLinkEvent) {
    this.copyLinkEvent = $copyLinkEvent;
    let fileType = $copyLinkEvent.getCopiedLinkFile().dataset.file_type;
    let appURL = $copyLinkEvent.getFileContainerEvent().getLoadDriveEventClass().appURL;
    if (fileType === "file") {
      window.parent.postMessage({
        mceAction: "execCommand",
        cmd: "tonics:RegularLink",
        value: $copyLinkEvent.copiedLink
      }, appURL);
      if (window.hasOwnProperty("opener") && window.opener !== null) {
        window.opener.postMessage({
          cmd: "tonics:RegularLink",
          value: $copyLinkEvent.copiedLink
        }, appURL);
      }
    }
  }
};
__name(TinymceCopyLinkHandler, "TinymceCopyLinkHandler");

// src/Core/Events/OnImageFileEvent.ts
var OnImageFileEvent = class {
  get imageFile() {
    return this._imageFile;
  }
  constructor(imageFile) {
    this._imageFile = imageFile;
  }
};
__name(OnImageFileEvent, "OnImageFileEvent");

// src/Core/Commands/FilePlacement/ImageFilePlacement.ts
var ImageFilePlacement = class {
  extensions() {
    return ["jpeg", "jpg", "jpe", "jfi", "jif", "jfif", "png", "gif", "bmp", "webp", "apng", "avif", "svg"];
  }
  run($data, ext, callback = null) {
    if (callback) {
      if (typeof callback == "function") {
        let imageFile = callback(ICONS.FILE.image, ext, $data);
        dispatchEventToHandlers(new OnImageFileEvent(imageFile), OnImageFileEvent);
        return imageFile;
      }
    }
  }
  fileContext($fileContainerEvent) {
    return `
        ${contextMenuListCreator(Message.Context.Edit.Image, ICONS.FILE.image, MenuActions().EDIT_IMAGE_FILE)}
    ${contextMenuListCreator(Message.Context.Rename, ICONS.CONTEXT.edit, MenuActions().RENAME_FILE)}
    ${contextMenuListCreator(Message.Context.Link.Copy, ICONS.CONTEXT.link, MenuActions().COPY_LINK)}
    ${contextMenuListCreator(Message.Context.Link.Preview, ICONS.CONTEXT.preview_link, MenuActions().COPY_PREVIEW_LINK)}
    ${contextMenuListCreator(Message.Context.Cut, ICONS.CONTEXT.cut, MenuActions().CUT_FILE)}
    ${contextMenuListCreator(Message.Context.Delete, ICONS.CONTEXT.trash, MenuActions().DELETE_FILE)}`;
  }
};
__name(ImageFilePlacement, "ImageFilePlacement");

// src/Core/Events/OnDocumentFileEvent.ts
var OnDocumentFileEvent = class {
  get docFile() {
    return this._docFile;
  }
  constructor(docFile) {
    this._docFile = docFile;
  }
};
__name(OnDocumentFileEvent, "OnDocumentFileEvent");

// src/Core/Commands/FilePlacement/DocumentsFilePlacement.ts
var DocumentsFilePlacement = class {
  extensions() {
    return ["pdf", "docx", "doc", "txt"];
  }
  run($data, ext, callback = null) {
    if (callback) {
      if (typeof callback == "function") {
        let docFile;
        switch (ext) {
          case "docx":
          case "doc":
            docFile = callback(ICONS.FILE.docx, ext, $data);
            break;
          case "pdf":
            docFile = callback(ICONS.FILE.pdf, ext, $data);
            break;
          default:
            docFile = callback(ICONS.FILE.note, ext, $data);
        }
        dispatchEventToHandlers(new OnDocumentFileEvent(docFile), OnDocumentFileEvent);
        return docFile;
      }
    }
  }
  fileContext($fileContainerEvent) {
    return `
    ${contextMenuListCreator(Message.Context.Rename, ICONS.CONTEXT.edit, MenuActions().RENAME_FILE)}
    ${contextMenuListCreator(Message.Context.Link.Copy, ICONS.CONTEXT.link, MenuActions().COPY_LINK)}
    ${contextMenuListCreator(Message.Context.Link.Preview, ICONS.CONTEXT.preview_link, MenuActions().COPY_PREVIEW_LINK)}
    ${contextMenuListCreator(Message.Context.Cut, ICONS.CONTEXT.cut, MenuActions().CUT_FILE)}
    ${contextMenuListCreator(Message.Context.Delete, ICONS.CONTEXT.trash, MenuActions().DELETE_FILE)}`;
  }
};
__name(DocumentsFilePlacement, "DocumentsFilePlacement");

// src/Core/Handlers/Third-Party/TinymceCopyPreviewLinkHandler.ts
var TinymceCopyPreviewLinkHandler = class {
  constructor($copyPreviewLinkEvent) {
    this.copyPreviewLinkEvent = $copyPreviewLinkEvent;
    let ImageFile = new ImageFilePlacement();
    let AudioFile = new AudioFilePlacement();
    let DocFile = new DocumentsFilePlacement();
    let fileType = $copyPreviewLinkEvent.getCopiedLinkFile().dataset.file_type;
    let fileExtension = $copyPreviewLinkEvent.getCopiedLinkFile().dataset.ext;
    let appURL = this.copyPreviewLinkEvent.getFileContainerEvent().getLoadDriveEventClass().appURL;
    if (fileType === "file") {
      if (ImageFile.extensions().includes(fileExtension)) {
        window.parent.postMessage({
          mceAction: "execCommand",
          cmd: "tonics:ImageLink",
          value: $copyPreviewLinkEvent.getCopiedLink()
        }, appURL);
        if (window.hasOwnProperty("opener") && window.opener !== null) {
          window.opener.postMessage({
            cmd: "tonics:ImageLink",
            value: $copyPreviewLinkEvent.getCopiedLink()
          }, appURL);
        }
      }
      if (DocFile.extensions().includes(fileExtension)) {
        if (window.hasOwnProperty("opener") && window.opener !== null) {
          window.opener.postMessage({
            cmd: "tonics:DocLink",
            value: $copyPreviewLinkEvent.getCopiedLink()
          }, appURL);
        }
      }
      if (AudioFile.extensions().includes(fileExtension) || ["mp4", "3gp", "mov"].includes(fileExtension)) {
        window.parent.postMessage({
          mceAction: "execCommand",
          cmd: "tonics:MediaLink",
          value: $copyPreviewLinkEvent.getCopiedLink()
        }, appURL);
        if (window.hasOwnProperty("opener") && window.opener !== null) {
          window.opener.postMessage({
            cmd: "tonics:MediaLink",
            value: $copyPreviewLinkEvent.getCopiedLink()
          }, appURL);
        }
      } else {
        window.parent.postMessage({
          mceAction: "execCommand",
          cmd: "tonics:RegularLink",
          value: $copyPreviewLinkEvent.getCopiedLink()
        }, appURL);
      }
    }
  }
};
__name(TinymceCopyPreviewLinkHandler, "TinymceCopyPreviewLinkHandler");

// src/Core/Events/CopyLinkEvent.ts
var CopyLinkEvent = class {
  get copiedLink() {
    return this._copiedLink;
  }
  set copiedLink(value) {
    this._copiedLink = value;
  }
  constructor($fileContainerEvent) {
    this.fileContainerEvent = $fileContainerEvent;
    this.copiedLinkFile = $fileContainerEvent.getSelectedFile();
  }
  getFileContainerEvent() {
    return this.fileContainerEvent;
  }
  handleCopyLink(selectedFile = null) {
    return this.fileContainerEvent.currentDrive.copyLinkHandler(this.fileContainerEvent, selectedFile);
  }
  onSuccess(message = null) {
    this.copiedLinkFile = this.fileContainerEvent.getSelectedFile();
    this.copiedLink = message;
    this.getFileContainerEvent().removeContextMenu();
    successToast(Message.Link.Copy.Download.Success).then();
    this.fileContainerEvent.dispatchEventToHandlers(this, CopyLinkEvent);
  }
  onError(message = null) {
    errorToast(Message.Link.Copy.Download.Error).then();
  }
  getCopiedLinkFile() {
    return this.copiedLinkFile;
  }
};
__name(CopyLinkEvent, "CopyLinkEvent");

// src/Core/Events/OnAudioIsPlayableEvent.ts
var OnAudioIsPlayableEvent = class {
  get audioFile() {
    return this._audioFile;
  }
  constructor(audioFile) {
    this._audioFile = audioFile;
  }
};
__name(OnAudioIsPlayableEvent, "OnAudioIsPlayableEvent");

// src/Core/Handlers/RegisterAudioFileForAudioPlayerHandler.ts
var RegisterAudioFileForAudioPlayerHandler = class {
  constructor(onAudioFileEvent) {
    let audioFile = onAudioFileEvent.audioFile;
    let copyLinkEvent = new CopyLinkEvent(window.TonicsFileManager.events.fileContainerEvent);
    copyLinkEvent.handleCopyLink(audioFile).then((url) => {
      audioFile.dataset.audioplayer_play = "false";
      audioFile.dataset.audioplayer_songurl = url;
      audioFile.dataset.audioplayer_image = "";
      audioFile.dataset.audioplayer_title = audioFile.dataset.filename;
      audioFile.dataset.audioplayer_format = audioFile.dataset.ext;
      audioFile.setAttribute("data-tonics-audioplayer-track", "");
      dispatchEventToHandlers(new OnAudioIsPlayableEvent(audioFile), OnAudioIsPlayableEvent);
    });
  }
};
__name(RegisterAudioFileForAudioPlayerHandler, "RegisterAudioFileForAudioPlayerHandler");

// src/Core/Configs/EventsConfig.ts
var EventsConfig = {
  FileContainerEvent: [
    AddClickEventToFileContainer,
    DoubleClickEventToFileContainer,
    ContextMenuProcessor,
    ContextHandler,
    HeaderSectionHandler,
    FileContainerDragAndDropHandler,
    SwitchDriveStorageHandler,
    LoadMoreFilesHandler,
    NavigateFilesByKeyboardKeysHandler,
    SearchFilesInFolderHandler,
    BreadCrumbHandler
  ],
  LoadDriveDataEvent: [],
  RenameFileEvent: [],
  UploadFileEvent: [],
  CutFileEvent: [],
  PasteFileEvent: [],
  EditImageFileEvent: [],
  DeleteFileEvent: [],
  CopyLinkEvent: [
    TinymceCopyLinkHandler
  ],
  CopyPreviewLinkEvent: [
    TinymceCopyPreviewLinkHandler
  ],
  RefreshFolderEvent: [],
  OnAudioFileEvent: [
    RegisterAudioFileForAudioPlayerHandler
  ],
  OnAudioIsPlayableEvent: [],
  OnArchiveCompressFileEvent: [],
  OnCodeFileEvent: [],
  OnDirectoryFileEvent: [],
  OnDocumentFileEvent: [],
  OnImageFileEvent: [],
  NewFolderEvent: []
};

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
function dispatchEventToHandlers($eventObject, $eventClass) {
  let eventHandlers = attachEventAndHandlersToHandlerProvider(EventsConfig, $eventClass);
  let eventDispatcher = new EventDispatcher();
  eventDispatcher.setHandler(eventHandlers).dispatch($eventObject);
}
__name(dispatchEventToHandlers, "dispatchEventToHandlers");
if (!window.hasOwnProperty("TonicsEvent")) {
  window.TonicsEvent = {};
}
window.TonicsEvent.attachEventAndHandlersToHandlerProvider = ($eventConfig, $eventName) => attachEventAndHandlersToHandlerProvider($eventConfig, $eventName);

// src/Core/Commands/FilePlacement/AudioFilePlacement.ts
var AudioFilePlacement = class {
  extensions() {
    return ["mp3", "wav", "tiff", "ogg", "webm", "aac", "flac"];
  }
  run($data, ext, callback = null) {
    if (callback) {
      if (typeof callback == "function") {
        let audioFile = callback(ICONS.FILE.music, ext, $data);
        dispatchEventToHandlers(new OnAudioFileEvent(audioFile), OnAudioFileEvent);
        return audioFile;
      }
    }
  }
  fileContext($fileContainerEvent) {
    return `
    ${contextMenuListCreator(Message.Context.Rename, ICONS.CONTEXT.edit, MenuActions().RENAME_FILE)}
    ${contextMenuListCreator(Message.Context.Link.Copy, ICONS.CONTEXT.link, MenuActions().COPY_LINK)}
    ${contextMenuListCreator(Message.Context.Link.Preview, ICONS.CONTEXT.preview_link, MenuActions().COPY_PREVIEW_LINK)}
    ${contextMenuListCreator(Message.Context.Cut, ICONS.CONTEXT.cut, MenuActions().CUT_FILE)}
    ${contextMenuListCreator(Message.Context.Delete, ICONS.CONTEXT.trash, MenuActions().DELETE_FILE)}`;
  }
};
__name(AudioFilePlacement, "AudioFilePlacement");

// src/Core/Events/OnDirectoryFileEvent.ts
var OnDirectoryFileEvent = class {
  get dirFile() {
    return this._dirFile;
  }
  constructor(dirFile) {
    this._dirFile = dirFile;
  }
};
__name(OnDirectoryFileEvent, "OnDirectoryFileEvent");

// src/Core/Commands/FilePlacement/DirectoryFilePlacement.ts
var DirectoryFilePlacement = class {
  extensions() {
    return ["null", null];
  }
  run($data, ext, callback = null) {
    if (callback) {
      if (typeof callback == "function") {
        let dirFile = callback(ICONS.FILE.folder, ext, $data);
        dispatchEventToHandlers(new OnDirectoryFileEvent(dirFile), OnDirectoryFileEvent);
        return dirFile;
      }
    }
  }
  fileContext($fileContainerEvent) {
    let pasteLi = "";
    if ($fileContainerEvent.cutFile.length > 0) {
      pasteLi = contextMenuListCreator(Message.Context.Paste, ICONS.CONTEXT.paste, MenuActions().PASTE_FILE);
    }
    return `
    ${contextMenuListCreator(Message.Context.Rename, ICONS.CONTEXT.edit, MenuActions().RENAME_FILE)}
    ${contextMenuListCreator(Message.Context.Cut, ICONS.CONTEXT.cut, MenuActions().CUT_FILE)}
     ${pasteLi}
    ${contextMenuListCreator(Message.Context.Delete, ICONS.CONTEXT.trash, MenuActions().DELETE_FILE)}`;
  }
  getCutFiles() {
    return document.querySelectorAll('[data-cut="true"]');
  }
};
__name(DirectoryFilePlacement, "DirectoryFilePlacement");

// src/Core/Events/OnArchiveCompressFileEvent.ts
var OnArchiveCompressFileEvent = class {
  get archiveFile() {
    return this._archiveFile;
  }
  constructor(archiveFile) {
    this._archiveFile = archiveFile;
  }
};
__name(OnArchiveCompressFileEvent, "OnArchiveCompressFileEvent");

// src/Core/Commands/FilePlacement/ArchiveCompressFilePlacement.ts
var ArchiveCompressFilePlacement = class {
  extensions() {
    return ["zip", "tar", "gz", "rar", "7z", "bz2", "xz", "wim"];
  }
  fileContext($fileContainerEvent) {
    return `
    ${contextMenuListCreator(Message.Context.Rename, ICONS.CONTEXT.edit, MenuActions().RENAME_FILE)}
    ${contextMenuListCreator(Message.Context.Link.Copy, ICONS.CONTEXT.link, MenuActions().COPY_LINK)}
    ${contextMenuListCreator(Message.Context.Link.Preview, ICONS.CONTEXT.preview_link, MenuActions().COPY_PREVIEW_LINK)}
    ${contextMenuListCreator(Message.Context.Cut, ICONS.CONTEXT.cut, MenuActions().CUT_FILE)}
    ${contextMenuListCreator(Message.Context.Delete, ICONS.CONTEXT.trash, MenuActions().DELETE_FILE)}`;
  }
  run($data, ext, callback) {
    if (callback) {
      if (typeof callback == "function") {
        let archiveFile;
        switch (ext) {
          case "zip":
            archiveFile = callback(ICONS.FILE.zip, ext, $data);
            break;
          default:
            archiveFile = callback(ICONS.FILE.compress, ext, $data);
        }
        dispatchEventToHandlers(new OnArchiveCompressFileEvent(archiveFile), OnArchiveCompressFileEvent);
        return archiveFile;
      }
    }
  }
};
__name(ArchiveCompressFilePlacement, "ArchiveCompressFilePlacement");

// src/Core/Events/OnCodeFileEvent.ts
var OnCodeFileEvent = class {
  get codeFile() {
    return this._codeFile;
  }
  constructor(codeFile) {
    this._codeFile = codeFile;
  }
};
__name(OnCodeFileEvent, "OnCodeFileEvent");

// src/Core/Commands/FilePlacement/CodeFilePlacement.ts
var CodeFilePlacement = class {
  extensions() {
    return ["php", "js", "css", "bat", "nim", "cs", "sql", "ts", "sh", "rb", "pyo", "pl", "o", "lua", "kt"];
  }
  fileContext($fileContainerEvent) {
    return `
    ${contextMenuListCreator(Message.Context.Rename, ICONS.CONTEXT.edit, MenuActions().RENAME_FILE)}
    ${contextMenuListCreator(Message.Context.Link.Copy, ICONS.CONTEXT.link, MenuActions().COPY_LINK)}
    ${contextMenuListCreator(Message.Context.Link.Preview, ICONS.CONTEXT.preview_link, MenuActions().COPY_PREVIEW_LINK)}
    ${contextMenuListCreator(Message.Context.Cut, ICONS.CONTEXT.cut, MenuActions().CUT_FILE)}
    ${contextMenuListCreator(Message.Context.Delete, ICONS.CONTEXT.trash, MenuActions().DELETE_FILE)}`;
  }
  run($data, ext, callback) {
    if (callback) {
      if (typeof callback == "function") {
        let codeFile = callback(ICONS.FILE.code, ext, $data);
        dispatchEventToHandlers(new OnCodeFileEvent(codeFile), OnCodeFileEvent);
        return codeFile;
      }
    }
  }
};
__name(CodeFilePlacement, "CodeFilePlacement");

// src/Core/Configs/CommandsConfig.ts
var CommandsConfig = {
  FileByExtensions: [
    new AudioFilePlacement(),
    new ArchiveCompressFilePlacement(),
    new CodeFilePlacement(),
    new ImageFilePlacement(),
    new DirectoryFilePlacement(),
    new DocumentsFilePlacement()
  ]
};

// src/StorageDriver/LocalDriver/LocalFileExtensionsCommands.ts
var LocalFileExtensionsCommands = class {
  get data() {
    return this._data;
  }
  set data(value) {
    this._data = value;
  }
  constructor($commands, $data, appendFile = false) {
    this.commands = $commands;
    this.data = $data;
    this.append = appendFile;
  }
  getCommands() {
    return this.commands;
  }
  placeFileByExtension() {
    let self2 = this;
    let commands = this.getCommands().getList();
    if (!this.append) {
      try {
        document.querySelector(FileManagerElements.FILES.FILE_CONTAINER).replaceChildren();
      } catch (error) {
        console.error(error);
      }
    }
    let key;
    const data = this.data;
    for (key in data) {
      let file = data[key], list_id;
      if (document.querySelector("[data-list_id]:last-of-type")) {
        list_id = parseInt(document.querySelector("[data-list_id]:last-of-type").dataset.list_id) + 1;
      } else {
        list_id = key;
      }
      file.list_id = list_id;
      file.properties = JSON.parse(file.properties);
      let assignedAnExtension = false;
      let ext = file.properties.ext;
      commands.forEach((command, key2) => {
        if (typeof command.extensions == "function" && typeof command.run == "function") {
          if (command.extensions().includes(ext)) {
            assignedAnExtension = true;
            command.run(file, ext, function(icon, ext2, data2) {
              return self2.createFile(icon, ext2, data2);
            });
          }
        }
      });
      if (!assignedAnExtension) {
        new DefaultFilePlacement().run(file, ext, function(icon, ext2, data2) {
          return self2.createFile(icon, ext2, data2);
        });
      }
    }
  }
  createFile($icon, ext, $data) {
    let fileContainer = document.querySelector(FileManagerElements.FILES.FILE_CONTAINER);
    let file = `
        <li class="tonics-file" 
                    data-list_id="${$data.list_id}"
                    data-drive_id="${$data.drive_id}"
                    data-drive_parent_id="${$data.drive_parent_id}"
                    data-drive_unique_id="${$data.drive_unique_id}"
                    data-filename="${$data.filename}" 
                    data-file_type="${$data.type}"
                    data-size="${$data.properties.size}"
                    data-file_path="${$data.filepath}"
                    data-time_created="${$data.properties.time_created}"
                    data-time_modified="${$data.properties.time_modified}"
                    data-ext="${ext}">
          <button class="tonics-fm-link remove-button-styles">
           ${$icon}
            <div class="tonics-file-filename">
              <input class="tonics-file-filename-input" type="text" value="${$data.filename}" readonly="" aria-label="${$data.filename}">
            </div>
            <span class="svg-per-file-loading display-none"></span>
          </button>
        </li>
        `;
    fileContainer == null ? void 0 : fileContainer.insertAdjacentHTML("beforeend", file);
    return fileContainer == null ? void 0 : fileContainer.lastElementChild;
  }
};
__name(LocalFileExtensionsCommands, "LocalFileExtensionsCommands");

// src/StorageDriver/LocalDriver/LocalDiskDrive.ts
var API_PREFIX = "/api/media/";
var LocalDiskDriveAPI;
(function(LocalDiskDriveAPI2) {
  LocalDiskDriveAPI2[LocalDiskDriveAPI2["GetFiles"] = API_PREFIX + "files"] = "GetFiles";
  LocalDiskDriveAPI2[LocalDiskDriveAPI2["GetFileFromPath"] = API_PREFIX + "files?path="] = "GetFileFromPath";
  LocalDiskDriveAPI2[LocalDiskDriveAPI2["SearchFileFromPath"] = API_PREFIX + "files/search?path="] = "SearchFileFromPath";
  LocalDiskDriveAPI2["IDQuery"] = "&id=";
  LocalDiskDriveAPI2["SearchQuery"] = "&query=";
  LocalDiskDriveAPI2[LocalDiskDriveAPI2["MoveFiles"] = API_PREFIX + "files/move"] = "MoveFiles";
  LocalDiskDriveAPI2[LocalDiskDriveAPI2["PreFlight"] = API_PREFIX + "files/preflight"] = "PreFlight";
  LocalDiskDriveAPI2[LocalDiskDriveAPI2["DeleteFiles"] = API_PREFIX + "files"] = "DeleteFiles";
  LocalDiskDriveAPI2[LocalDiskDriveAPI2["PostFiles"] = API_PREFIX + "files"] = "PostFiles";
  LocalDiskDriveAPI2[LocalDiskDriveAPI2["RenameFile"] = API_PREFIX + "files/rename"] = "RenameFile";
  LocalDiskDriveAPI2["ServeFile"] = "/serve_file_path_987654321/";
  LocalDiskDriveAPI2[LocalDiskDriveAPI2["CreateFolder"] = API_PREFIX + "files/create_folder"] = "CreateFolder";
  LocalDiskDriveAPI2[LocalDiskDriveAPI2["CancelUpload"] = API_PREFIX + "files/cancel_create"] = "CancelUpload";
})(LocalDiskDriveAPI || (LocalDiskDriveAPI = {}));
var LocalDiskDrive = class {
  constructor($driveSignature, $apiBearerToken, $fqdn) {
    this._fetchInfo = {
      lastStatus: {
        ok: false,
        status: 0,
        statusText: "",
        response: null
      }
    };
    this.driveSignature = $driveSignature;
    this.bearerToken = $apiBearerToken;
    this.setFqdn($fqdn);
  }
  get currentPathID() {
    return this._currentPathID;
  }
  set currentPathID(value) {
    this._currentPathID = value;
  }
  get appendNewFiles() {
    return this._appendNewFiles;
  }
  set appendNewFiles(value) {
    this._appendNewFiles = value;
  }
  get filesFolderNextPageUrl() {
    return this._filesFolderNextPageUrl;
  }
  set filesFolderNextPageUrl(value) {
    this._filesFolderNextPageUrl = value;
  }
  get driveSignature() {
    return this._driveSignature;
  }
  set driveSignature(value) {
    this._driveSignature = value;
  }
  get currentDirectoryID() {
    return this._currentDirectoryID;
  }
  set currentDirectoryID(value) {
    this._currentDirectoryID = value;
  }
  get fetchInfo() {
    return this._fetchInfo;
  }
  get bearerToken() {
    return this._bearerToken;
  }
  set bearerToken(value) {
    this._bearerToken = value;
  }
  get fqdn() {
    return this._fqdn;
  }
  set fqdn(value) {
    this._fqdn = value;
  }
  get storageData() {
    return this._storageData;
  }
  set storageData(value) {
    this._storageData = value;
  }
  getMaxRequestToSend() {
    return 4;
  }
  getDriveIcon() {
    return "#tonics-hdd";
  }
  getDriveName() {
    return "local";
  }
  coldBootStorageDisk() {
    let self2 = this;
    return new Promise(function(resolve, reject) {
      let getFilesURL = `${self2.fqdn}${LocalDiskDriveAPI.GetFiles}`;
      let fileData = self2.fetchFileData(getFilesURL);
      self2.storageData = fileData;
      fileData.then((response) => {
        self2.createIndividualDataElement(response);
        resolve();
      }).catch(() => reject());
    });
  }
  createIndividualDataElement(fileData) {
    const $command = new CommandRegistrar(CommandsConfig.FileByExtensions);
    const $data = fileData.data;
    this.currentPathID = fileData.more.drive_id;
    if (fileData.more.has_more === true) {
      this.filesFolderNextPageUrl = fileData.more.next_page_url;
      fileLoadMoreButton();
    } else {
      fileLoadMoreButton(false, false);
    }
    if (this.appendNewFiles) {
      return new LocalFileExtensionsCommands($command, $data, true).placeFileByExtension();
    }
    this.addCrumbNavigationPathTrail(fileData.more);
    this.currentDirectoryID = fileData.more.drive_id;
    new LocalFileExtensionsCommands($command, $data).placeFileByExtension();
  }
  loadMoreFiles($fileContainerEvent) {
    return new Promise((resolve, reject) => {
      let getFilesURL = `${this.fqdn}${this.filesFolderNextPageUrl}`;
      this.fetchFileData(getFilesURL).then((response) => {
        this.appendNewFiles = true;
        this.createIndividualDataElement(response);
        this.appendNewFiles = false;
        resolve();
      });
    });
  }
  searchFiles(searchValue, $fileContainerEvent) {
    return new Promise((resolve, reject) => {
      let current_path = encodeURIComponent($fileContainerEvent.getCurrentDirectory()), queryString = `${LocalDiskDriveAPI.SearchFileFromPath}${current_path}${LocalDiskDriveAPI.IDQuery}${this.currentPathID}${LocalDiskDriveAPI.SearchQuery}${encodeURIComponent(searchValue)}`;
      let searchFileUrl = `${this.fqdn}${queryString}`;
      this.fetchFileData(searchFileUrl).then((response) => {
        this.createIndividualDataElement(response);
        resolve();
      });
    });
  }
  addCrumbNavigationPathTrail(info) {
    var _a;
    const path = info.current_path;
    let split = path.split("/");
    split = split.filter(String);
    let breadcrumb = document.querySelector(".breadcrumb");
    breadcrumb.innerHTML = "Navigating:  ";
    let pathTrail = "";
    split.forEach((path2, index, array) => {
      pathTrail = pathTrail.concat(`/${path2}`);
      let eachPath = titleCase(path2);
      breadcrumb == null ? void 0 : breadcrumb.insertAdjacentHTML("beforeend", `<a data-pathtrail="${pathTrail}" data-filename="${path2}" 
href="javascript:void(0);">${eachPath}</a><span class="delimiter"> \xBB </span>`);
    });
    (_a = breadcrumb == null ? void 0 : breadcrumb.lastElementChild) == null ? void 0 : _a.remove();
  }
  breadCrumbClickNavigationHandler(path) {
    return new Promise((resolve, reject) => {
      const pathTrail = encodeURIComponent(path);
      let getFilesURL = `${this.fqdn}${LocalDiskDriveAPI.GetFileFromPath}${pathTrail}`;
      this.fetchFileData(getFilesURL).then((response) => {
        this.createIndividualDataElement(response);
        resolve();
      }).catch(() => reject());
    });
  }
  createDirectoryElement(file_path) {
    let newFile = document.createElement("li");
    newFile.dataset.drive_id = this.currentDirectoryID;
    newFile.dataset.file_path = file_path;
    newFile.dataset.file_type = "directory";
    newFile.dataset.ext = "null";
    return newFile;
  }
  refresh($fileContainerEvent) {
    let self2 = this;
    return new Promise(function(resolve, reject) {
      let directoryElement = self2.createDirectoryElement($fileContainerEvent.getCurrentDirectory());
      self2.openFolderHandler(directoryElement, $fileContainerEvent).then(function() {
        resolve();
      });
    });
  }
  createFolder($fileContainerEvent, $newFolderProperties) {
    let self2 = this;
    return new Promise(function(resolve, reject) {
      let folderProperties = {
        filename: $newFolderProperties.name,
        uploadTo: $fileContainerEvent.getCurrentDirectory(),
        uploadToID: self2.currentDirectoryID
      };
      let createFolderAPI = `${self2.fqdn}${LocalDiskDriveAPI.CreateFolder}`;
      let XHRAPI = self2.defaultXHR({});
      XHRAPI.Post(createFolderAPI, JSON.stringify(folderProperties), function(err, data) {
        if (err) {
          err = JSON.parse(err);
          reject();
        }
        if (data) {
          data = JSON.parse(data);
          if (data.hasOwnProperty("status")) {
            if (data.status == 200) {
              resolve();
            }
          }
        }
      });
    });
  }
  openFolderHandler(file, $fileContainerEvent) {
    let self2 = this;
    return new Promise(function(resolve, reject) {
      if (file.dataset.file_type == "directory" && file.dataset.ext == "null") {
        let dirRelativePath = encodeURIComponent(file.dataset.file_path);
        let driveID = file.dataset.drive_id;
        let getFilesURL = `${self2.fqdn}${LocalDiskDriveAPI.GetFileFromPath}${dirRelativePath}${LocalDiskDriveAPI.IDQuery}${driveID}`;
        self2.fetchFileData(getFilesURL).then(function(response) {
          self2.createIndividualDataElement(response);
          resolve();
        });
      }
    });
  }
  getStorageFileData() {
    return this.storageData;
  }
  defaultXHR(requestHeaders = {}) {
    let defaultHeader = {
      "Authorization": `Bearer ${this.bearerToken}`,
      "Tonics-CSRF-Token": `${getCSRFFromInput(["tonics_csrf_token", "csrf_token"])}`
    };
    return new XHRApi(__spreadValues(__spreadValues({}, defaultHeader), requestHeaders));
  }
  headers() {
    let bearerToken = this.bearerToken;
    return new Headers([
      ["Content-Type", "application/json; charset=utf-8"],
      ["Authorization", `Bearer ${bearerToken}`],
      ["Tonics-CSRF-Token", `${getCSRFFromInput(["tonics_csrf_token", "csrf_token"])}`]
    ]);
  }
  fetchFileData(url) {
    return __async(this, null, function* () {
      let self2 = this;
      const myHeaders = this.headers();
      const myRequest = new Request(url, {
        method: "GET",
        headers: myHeaders,
        cache: "default",
        mode: "cors"
      });
      return new FetchAPI(myRequest).run().then(function(response) {
        return self2.processResponse(response);
      }).then(function(json) {
        self2.fetchInfo.lastStatus.response = json;
        if (self2.fetchInfo.lastStatus.ok) {
          return json;
        } else {
          console.log(self2.fetchInfo.lastStatus);
        }
      });
    });
  }
  uploadFile(fileSettings, filename, $uploadFileEvent) {
    let fileObject = fileSettings.fileObject;
    let preFlightData = fileSettings.preFlightData;
    let self2 = this;
    if (preFlightData.dataToFill !== null) {
      if (preFlightData.dataToFill.length > 0 && !fileSettings.uploaded) {
        if (!fileSettings.pause) {
          self2.throttleSend(fileSettings, filename, $uploadFileEvent);
        }
      }
    } else {
      let XHRAPI = this.defaultXHR({
        "UploadTo": fileSettings.uploadTo,
        "Filename": fileObject.name,
        "Filetype": fileObject.type,
        "Chunkstosend": preFlightData.chunksToSend,
        "Totalblobsize": preFlightData.Totalblobsize,
        "Byteperchunk": preFlightData.Byteperchunk
      });
      let preflight = `${this.fqdn}${LocalDiskDriveAPI.PreFlight}`;
      XHRAPI.Get(preflight, function(err, data) {
        if (data) {
          fileSettings.fetched = true;
          fileSettings.preFlightData.dataToFill = JSON.parse(data).data;
          fileSettings.preFlightData.filename = JSON.parse(data).more.filename;
          $uploadFileEvent.setUploadFileObject(filename, fileSettings);
          if (preFlightData.dataToFill.length > 0 && !fileSettings.uploaded) {
            if (!fileSettings.pause) {
              self2.throttleSend(fileSettings, filename, $uploadFileEvent);
            }
          }
        }
      });
    }
  }
  throttleSend(fileSettings, filename, $uploadFileEvent) {
    let self2 = this;
    let preFlightData = fileSettings.preFlightData, data = fileSettings.preFlightData.dataToFill, fileObject = fileSettings.fileObject;
    for (let i = preFlightData.nextIndex, len = data.length; i < len; i++) {
      if (data) {
        let chunkPart = data[i];
        let chunkPartMoreInfo = JSON.parse(chunkPart.moreBlobInfo);
        let chunk = fileObject.slice(chunkPartMoreInfo.startSlice, chunkPartMoreInfo.endSlice);
        let blobProperties = {
          id: chunkPart.id,
          filename: fileSettings.preFlightData.filename,
          filetype: fileObject.type,
          uploadTo: fileSettings.uploadTo,
          uploadToID: self2.currentDirectoryID,
          chunkPart: chunkPart.blob_chunk_part,
          chunkSize: chunkPart.blob_chunk_size,
          mbRate: 4 * 1048576,
          totalChunks: preFlightData.chunksToSend,
          totalBlobSize: fileObject.size,
          startSlice: chunkPartMoreInfo.startSlice,
          newFile: fileSettings.newFile
        };
        if (preFlightData.throttleSwitch) {
          break;
        }
        fileSettings.preFlightData.sentApi = fileSettings.preFlightData.sentApi + 1;
        $uploadFileEvent.setUploadFileObject(filename, fileSettings);
        self2.uploadBlob(chunk, blobProperties, filename, $uploadFileEvent);
        fileSettings.preFlightData.nextIndex = fileSettings.preFlightData.nextIndex + 1;
        $uploadFileEvent.setUploadFileObject(filename, fileSettings);
      }
    }
  }
  uploadBlob(chunk, blobProperties, filename, $uploadFileEvent) {
    let self2 = this, token = this.bearerToken, XHRAPI = this.defaultXHR({
      "BlobDataInfo": JSON.stringify(blobProperties)
    });
    let fileSettings = $uploadFileEvent.getUploadFileObject(filename, this.driveSignature);
    if (fileSettings.preFlightData.sentApi >= fileSettings.preFlightData.maxRequestToSend) {
      fileSettings.preFlightData.throttleSwitch = true;
      $uploadFileEvent.$uploadedFilesObject.set(filename, fileSettings);
    }
    let postFile = `${this.fqdn}${LocalDiskDriveAPI.PostFiles}`;
    XHRAPI.Post(postFile, chunk, function(err, data) {
      if (err) {
      }
      if (data) {
        let receivedData = JSON.parse(data);
        let percentageInt = Math.round(receivedData.data.uploadPercentage);
        fileSettings.preFlightData.noOfReceivedResponse = fileSettings.preFlightData.noOfReceivedResponse + 1;
        $uploadFileEvent.setUploadFileObject(filename, fileSettings);
        if (!receivedData.data.isUploadCompleted) {
          $uploadFileEvent.updateFileProgress(filename, percentageInt, self2.driveSignature);
        }
        if (fileSettings.preFlightData.noOfReceivedResponse === fileSettings.preFlightData.maxRequestToSend) {
          $uploadFileEvent.releaseThrottle(filename, self2.driveSignature);
          if (!receivedData.data.isUploadCompleted && !fileSettings.uploaded) {
            self2.uploadFile(fileSettings, filename, $uploadFileEvent);
          }
        }
        if (!fileSettings.uploaded) {
          if (receivedData.data.isUploadCompleted) {
            $uploadFileEvent.setUploadCompleted(filename, self2.driveSignature);
            if ($uploadFileEvent.isUploadingSequentially()) {
              $uploadFileEvent.setSequenceDone(filename, self2.driveSignature);
              $uploadFileEvent.uploadFileNextSequence(self2.driveSignature);
            }
          }
        }
        return receivedData;
      }
    });
  }
  cancelFileUploadHandler(fileSettings, $uploadFileEvent) {
    let self2 = this;
    fileSettings.pause = true;
    fileSettings.uploaded = true;
    fileSettings.preFlightData.throttleSwitch = true;
    let uploadFilename = this.driveSignature + "_" + fileSettings.fileObject.name;
    $uploadFileEvent.setUploadFileObject(uploadFilename, fileSettings);
    return new Promise(function(resolve, reject) {
      let preFlightData = fileSettings.preFlightData, blobProperties = {
        filename: fileSettings.preFlightData.filename,
        totalChunks: preFlightData.chunksToSend,
        uploadTo: fileSettings.uploadTo,
        totalBlobSize: fileSettings.fileObject.size
      }, XHRAPI = self2.defaultXHR({}), data = JSON.stringify(blobProperties), cancelUpload = `${self2.fqdn}${LocalDiskDriveAPI.CancelUpload}`;
      XHRAPI.Delete(cancelUpload, data, function(err, returnData) {
        if (err) {
          reject();
        }
        if (returnData) {
          returnData = JSON.parse(returnData);
          if (returnData.hasOwnProperty("status")) {
            if (returnData.status == 200) {
              resolve(returnData.message);
            }
          }
        }
      });
    });
  }
  renameFileHandler(fileToRename, $fileContainerEvent) {
    let self2 = this;
    return new Promise(function(resolve, reject) {
      let fileInput = fileToRename.querySelector(".tonics-file-filename-input");
      fileToRename.dataset.filename_new = fileInput.value;
      let payload = JSON.stringify(fileToRename.dataset);
      const myHeaders = self2.headers();
      let renameURL = `${self2.fqdn}${LocalDiskDriveAPI.RenameFile}`;
      const myRequest = new Request(renameURL, {
        method: "PUT",
        headers: myHeaders,
        cache: "default",
        mode: "cors",
        body: payload
      });
      let fetchAPI = new FetchAPI(myRequest).run();
      fetchAPI.then(function(response) {
        return self2.processResponse(response);
      }).then(function(json) {
        if (json.status > 200) {
          return reject();
        }
        if (json.hasOwnProperty("data")) {
          fileInput.style.width = 175 + "px";
          let data = json.data;
          fileInput.value = data.filename;
          fileInput.ariaLabel = data.filename;
          fileToRename.dataset.drive_id = data.drive_id;
          fileToRename.dataset.filename = data.filename;
          fileToRename.dataset.file_path = data.file_path;
          fileToRename.dataset.time_modified = data.time_modified;
          return resolve(fileToRename);
        }
      });
    });
  }
  editImageHandler($uploadFileEvent) {
    let self2 = this;
    return new Promise(function(resolve, reject) {
      let selectedFile = $uploadFileEvent.getFileContainerEvent().getSelectedFile(), url = `${self2.fqdn}${LocalDiskDriveAPI.ServeFile}` + selectedFile.dataset.drive_unique_id + "?render", config = {
        translations: {
          en: {
            "toolbar.save": "Save",
            "toolbar.apply": "Apply",
            "toolbar.download": "Save Changes"
          }
        }
      };
      if (selectedFile) {
        const onBeforeComplete = /* @__PURE__ */ __name(function(props) {
          props.canvas.toBlob(function(blob) {
            let file = new File([blob], selectedFile.dataset.filename);
            const uploadTo = $uploadFileEvent.getFileContainerEvent().getCurrentDirectory();
            let byteToSendPerChunk = $uploadFileEvent.byteToSendPerChunk;
            let fileObject = {
              fileObject: file,
              driveSignature: $uploadFileEvent.getFileContainerEvent().currentDrive.driveSignature,
              preFlightData: {
                filename: file.name,
                dataToFill: null,
                chunksToSend: Math.ceil(file.size / byteToSendPerChunk),
                Byteperchunk: byteToSendPerChunk,
                Totalblobsize: file.size,
                maxRequestToSend: $uploadFileEvent.maxRequestToSend,
                noOfReceivedResponse: 0,
                throttleSwitch: false,
                sentApi: 0,
                nextIndex: 0
              },
              newFile: false,
              uploadTo,
              fetched: false,
              pause: false,
              uploaded: false
            };
            $uploadFileEvent.updateFileProgress(file.name, 0, self2.driveSignature, " \u22EF Updating");
            $uploadFileEvent.setUploadFileObject(file.name, fileObject);
            self2.uploadFile(fileObject, file.name, $uploadFileEvent);
          });
        }, "onBeforeComplete");
        const ImageEditor = new FilerobotImageEditor(config, {
          onBeforeComplete
        });
        ImageEditor.open(url);
      }
    });
  }
  copyLinkHandler($fileContainerEvent, selectedFile) {
    let self2 = this;
    return new Promise((resolve, reject) => {
      if (selectedFile === null) {
        selectedFile = $fileContainerEvent.getSelectedFile();
      }
      let link = `${self2.fqdn}${LocalDiskDriveAPI.ServeFile}` + selectedFile.dataset.drive_unique_id;
      return copyToClipBoard(link).then(() => {
        resolve(link);
      }).catch(() => {
        reject();
      });
    });
  }
  copyPreviewLinkHandler($fileContainerEvent, selectedFile) {
    let self2 = this;
    return new Promise((resolve, reject) => {
      if (selectedFile === null) {
        selectedFile = $fileContainerEvent.getSelectedFile();
      }
      let link = `${self2.fqdn}${LocalDiskDriveAPI.ServeFile}` + selectedFile.dataset.drive_unique_id + "?render";
      return copyToClipBoard(link).then(() => {
        resolve(link);
      }).catch(() => {
        reject();
      });
    });
  }
  moveFileHandler(moveTo, $fileContainerEvent) {
    let self2 = this;
    return new Promise(function(resolve, reject) {
      let copiedFiles = [];
      let pasteTo = moveTo.dataset;
      if (!pasteTo.hasOwnProperty("drive_id")) {
        pasteTo.drive_id = self2._currentDirectoryID;
      }
      $fileContainerEvent.getCutFiles().forEach((file) => {
        if (file.dataset.drive_id == pasteTo.drive_id) {
          reject(`Destination Folder \`${pasteTo.filename}\` is a subfolder of the source folder`);
          throw new DOMException(`Destination Folder \`${pasteTo.filename}\` is a subfolder of the source folder, you can't paste the same folder into the same folder`);
        } else {
          copiedFiles.push(file.dataset);
        }
      });
      let data = JSON.stringify({ files: copiedFiles, destination: pasteTo });
      let XHRAPI = self2.defaultXHR({});
      let moveURL = `${self2.fqdn}${LocalDiskDriveAPI.MoveFiles}`;
      XHRAPI.Put(moveURL, data, function(err, returnData) {
        if (err) {
          err = JSON.parse(err);
          return reject();
        }
        if (returnData) {
          returnData = JSON.parse(returnData);
          if (returnData.hasOwnProperty("status")) {
            if (returnData.status == 200) {
              self2.refresh($fileContainerEvent).then(function() {
                resolve();
              });
            }
          }
        }
      });
    });
  }
  deleteFileHandler(filesToBeDeleted, $fileContainerEvent) {
    let self2 = this;
    return new Promise(function(resolve, reject) {
      let XHRAPI = self2.defaultXHR({});
      let data = JSON.stringify({ files: filesToBeDeleted });
      let deleteFilesURL = `${self2.fqdn}${LocalDiskDriveAPI.DeleteFiles}`;
      XHRAPI.Delete(deleteFilesURL, data, function(err, returnData) {
        if (err) {
          reject();
        }
        if (returnData) {
          returnData = JSON.parse(returnData);
          if (returnData.hasOwnProperty("status")) {
            if (returnData.status == 200) {
              self2.refresh($fileContainerEvent).then(function() {
                resolve(returnData.message);
              });
            }
          }
        }
      });
    });
  }
  processResponse(response) {
    return __async(this, null, function* () {
      this.fetchInfo.lastStatus.ok = response.ok;
      this.fetchInfo.lastStatus.status = response.status;
      this.fetchInfo.lastStatus.statusText = response.statusText;
      this.fetchInfo.lastStatus.response = null;
      if (this.fetchInfo.lastStatus.ok) {
        return yield response.json();
      } else {
        return yield response.json();
      }
    });
  }
  setFqdn($fqdn) {
    let url = this.isValidURL($fqdn);
    if (!url) {
      throw new DOMException(`${$fqdn} is not a valid domain address, an example of a valid domain is https://google.com`);
    }
    this.fqdn = url;
  }
  isValidURL($fqdn) {
    let url = document.createElement("input");
    url.setAttribute("type", "url");
    url.value = $fqdn;
    if (url.validity.valid) {
      return $fqdn;
    }
    return false;
  }
};
__name(LocalDiskDrive, "LocalDiskDrive");

// src/StorageDriver/DropBox/DropboxFileExtensionCommands.ts
var DropboxFileExtensionCommands = class {
  constructor($commands, $fileData, appendFile = false) {
    this.commands = $commands;
    this.fileData = $fileData;
    this.append = appendFile;
  }
  getCommands() {
    return this.commands;
  }
  getFileData() {
    return this.fileData;
  }
  placeFileByExtension() {
    let self2 = this;
    let commands = this.getCommands().getList();
    if (!this.append) {
      document.querySelectorAll(FileManagerElements.FILES.SINGLE_FILE).forEach((el) => el.remove());
    }
    let key, data = this.getFileData();
    for (key in data) {
      let file = data[key], list_id;
      if (file.hasOwnProperty("metadata")) {
        file = file.metadata.metadata;
      }
      if (document.querySelector("[data-list_id]:last-of-type")) {
        list_id = parseInt(document.querySelector("[data-list_id]:last-of-type").dataset.list_id) + 1;
      } else {
        list_id = key;
      }
      file.list_id = list_id;
      let assignedAnExtension = false;
      let ext = getFileExtension(file.name);
      commands.forEach((command, key2) => {
        if (typeof command.extensions == "function" && typeof command.run == "function") {
          if (file[".tag"] === "folder") {
            if (command.extensions().includes(null)) {
              assignedAnExtension = true;
              command.run(file, "null", function(icon, ext2, data2) {
                return self2.createFile(icon, ext2, data2);
              });
            }
          } else {
            if (command.extensions().includes(ext)) {
              assignedAnExtension = true;
              command.run(file, ext, function(icon, ext2, data2) {
                return self2.createFile(icon, ext2, data2);
              });
            }
          }
        }
      });
      if (!assignedAnExtension) {
        new DefaultFilePlacement().run(file, ext, function(icon, ext2, data2) {
          return self2.createFile(icon, ext2, data2);
        });
      }
    }
  }
  createFile($icon, ext, $data) {
    let fileContainer = document.querySelector(FileManagerElements.FILES.FILE_CONTAINER), size = "", modified = "";
    if ($data.hasOwnProperty("size")) {
      size = $data.size;
    }
    if ($data.hasOwnProperty("server_modified")) {
      modified = $data.server_modified;
    }
    let fileType = $data[".tag"] === "folder" ? "directory" : $data[".tag"];
    let file = `
        <li class="tonics-file" 
                    data-list_id="${$data.list_id}"
                    data-drive_id="${$data.id}"
                    data-filename="${$data.name}" 
                    data-file_type="${fileType}"
                    data-size="${size}"
                    data-file_path="${$data.path_lower}"
                    data-time_modified="${modified}"
                    data-ext="${ext}">
          <button class="tonics-fm-link remove-button-styles">
           ${$icon}
            <div class="tonics-file-filename">
              <input onkeyup="event.preventDefault()" class="tonics-file-filename-input" type="text" value="${$data.name}" readonly="" aria-label="${$data.name}">
            </div>
            <span class="svg-per-file-loading display-none"></span>
          </button>
        </li>
        `;
    fileContainer == null ? void 0 : fileContainer.insertAdjacentHTML("beforeend", file);
    return fileContainer == null ? void 0 : fileContainer.lastElementChild;
  }
};
__name(DropboxFileExtensionCommands, "DropboxFileExtensionCommands");

// src/Core/Configs/Script.ts
var Script = {
  FileRobotImageEditor: {
    ID: "filerobot-image-editor",
    PATH: "/serve_module_file_path_987654321/Core?path=/js/media/filerobot-image-editor.js"
  },
  DropboxSDK: {
    ID: "dropbox-sdk",
    PATH: "/serve_module_file_path_987654321/Core?path=/js/media/dropbox.min.js"
  }
};

// src/StorageDriver/DropBox/DropboxDiskDrive.ts
var DropboxDiskDrive = class {
  constructor($driveSignature = "", $accessToken = "") {
    this._sharedLink = new Map();
    if ($driveSignature) {
      this.driveSignature = $driveSignature;
    }
    if ($accessToken) {
      this.accessToken = $accessToken;
    }
  }
  get isSearch() {
    return this._isSearch;
  }
  set isSearch(value) {
    this._isSearch = value;
  }
  get driveSignature() {
    return this._driveSignature;
  }
  set driveSignature(value) {
    this._driveSignature = value;
  }
  get accessToken() {
    return this._accessToken;
  }
  set accessToken(value) {
    this._accessToken = value;
  }
  get sharedLink() {
    return this._sharedLink;
  }
  get storageData() {
    return this._storageData;
  }
  set storageData(value) {
    this._storageData = value;
  }
  get nextCursor() {
    return this._nextCursor;
  }
  set nextCursor(value) {
    this._nextCursor = value;
  }
  get appendNewFiles() {
    return this._appendNewFiles;
  }
  set appendNewFiles(value) {
    this._appendNewFiles = value;
  }
  getMaxRequestToSend() {
    return 1;
  }
  getDriveIcon() {
    return "#tonics-dropbox";
  }
  getDriveName() {
    return "Dropbox";
  }
  coldBootStorageDisk() {
    let self2 = this;
    return new Promise(function(resolve, reject) {
      loadScriptDynamically(Script.DropboxSDK.PATH, Script.DropboxSDK.ID).then(function() {
        self2.listFolder("").then(() => {
          resolve();
        }).catch(() => {
          reject();
        });
      }.bind(self2));
    });
  }
  addCrumbNavigationPathTrail(path) {
    var _a;
    let split = path.split("/");
    let breadcrumb = document.querySelector(".breadcrumb"), pathTrail = "", eachPath = "";
    breadcrumb.innerHTML = "Navigating:  ";
    split.forEach((path2) => {
      pathTrail = pathTrail.concat(`/${path2}`);
      if (path2) {
        eachPath = titleCase(path2);
      } else {
        pathTrail = "";
        eachPath = "Root";
      }
      breadcrumb == null ? void 0 : breadcrumb.insertAdjacentHTML("beforeend", `<a data-pathtrail="${pathTrail}" data-filename="${path2}"  href="javascript:void(0);">${eachPath}</a><span class="delimiter"> \xBB </span>`);
    });
    (_a = breadcrumb == null ? void 0 : breadcrumb.lastElementChild) == null ? void 0 : _a.remove();
  }
  getStorageFileData() {
    return this.storageData;
  }
  createIndividualDataElement(response) {
    let $command = new CommandRegistrar(CommandsConfig.FileByExtensions);
    if (response.result.has_more) {
      this.nextCursor = response.result.cursor;
      fileLoadMoreButton();
    } else {
      fileLoadMoreButton(false, false);
    }
    if (response.result.hasOwnProperty("matches")) {
      this.isSearch = true;
      let fileData = response.result.matches;
      return new DropboxFileExtensionCommands($command, fileData).placeFileByExtension();
    } else {
      this.isSearch = false;
      let fileData = response.result.entries;
      if (this.appendNewFiles) {
        return new DropboxFileExtensionCommands($command, fileData, true).placeFileByExtension();
      }
      return new DropboxFileExtensionCommands($command, fileData).placeFileByExtension();
    }
  }
  listFolder(folderpath = "") {
    let self2 = this;
    return new Promise((resolve, reject) => {
      const dbx = self2.getDropbox();
      let fileData = dbx.filesListFolder({ path: folderpath, include_media_info: true, recursive: false, limit: 20 });
      self2.storageData = fileData;
      fileData.then((response) => {
        self2.addCrumbNavigationPathTrail(folderpath);
        self2.createIndividualDataElement(response);
        resolve();
      }).catch(function(error) {
        console.log(error);
        reject(error);
      });
    });
  }
  loadMoreFiles($fileContainerEvent) {
    return new Promise((resolve, reject) => {
      if (this.isSearch) {
        this.getDropbox().filesSearchContinueV2({ cursor: this.nextCursor }).then((response) => {
          this.appendNewFiles = true;
          this.createIndividualDataElement(response);
          this.appendNewFiles = false;
          resolve();
        });
      }
      this.getDropbox().filesListFolderContinue({ cursor: this.nextCursor }).then((response) => {
        this.appendNewFiles = true;
        this.createIndividualDataElement(response);
        this.appendNewFiles = false;
        resolve();
      });
    });
  }
  searchFiles(searchValue, $fileContainerEvent) {
    return new Promise((resolve, reject) => {
      this.getDropbox().filesSearchV2({ query: searchValue }).then((response) => {
        this.createIndividualDataElement(response);
        resolve();
      });
    });
  }
  cancelFileUploadHandler(fileSettings, $uploadFileEvent) {
    fileSettings.pause = true;
    fileSettings.uploaded = true;
    fileSettings.preFlightData.throttleSwitch = true;
    let uploadFilename = this.driveSignature + "_" + fileSettings.fileObject.name;
    $uploadFileEvent.setUploadFileObject(uploadFilename, fileSettings);
    return new Promise(function(resolve, reject) {
      if (localStorage.getItem(uploadFilename)) {
        localStorage.removeItem(uploadFilename);
        resolve();
      }
      reject();
    });
  }
  moveFileHandler(moveTo, $fileContainerEvent) {
    let self2 = this, copyEntries = [], pasteTo = moveTo.dataset;
    $fileContainerEvent.getCutFiles().forEach((file) => {
      if (file.dataset.drive_id == pasteTo.drive_id) {
        throw new DOMException(`Destination Folder \`${pasteTo.filename}\` is a subfolder of the source folder, you can't paste the same folder into the same folder`);
      } else {
        let moveInfo = { from_path: file.dataset.file_path, to_path: pasteTo.file_path + file.dataset.file_path };
        if (!pasteTo.file_path) {
          moveInfo = { from_path: file.dataset.file_path, to_path: "/" + file.dataset.filename };
        }
        copyEntries.push(moveInfo);
      }
    });
    return new Promise(function(resolve, reject) {
      const dbx = self2.getDropbox();
      dbx.filesMoveBatchV2({
        entries: copyEntries,
        autorename: true,
        allow_ownership_transfer: true
      }).then(function(response) {
        return response;
      }).then(function(response) {
        dbx.filesMoveBatchCheckV2({ async_job_id: response.result.async_job_id }).then((r) => {
          console.log(r);
          resolve("Move Operation is Progressing, Refresh After Few Seconds (Might Take Longer)");
        });
      }).catch(function(error) {
        console.log(error);
        reject();
      });
    });
  }
  renameFileHandler(fileToRename, $fileContainerEvent) {
    let self2 = this;
    return new Promise(function(resolve, reject) {
      let fileInput = fileToRename.querySelector(".tonics-file-filename-input");
      let fromPath = fileToRename.dataset.file_path, fileDir = getFileDirectory(fromPath, "/"), ext = fileToRename.dataset.ext, filename = fileInput.value, toPath = fileDir + filename;
      if (fileDir !== "/") {
        toPath = fileDir + "/" + filename;
      }
      if (fromPath.endsWith(ext)) {
        if (filename.endsWith(ext)) {
          toPath = fileDir + filename;
        } else {
          toPath = fileDir + filename + "." + ext;
        }
      }
      const dbx = self2.getDropbox();
      dbx.filesMoveV2({ from_path: fromPath, to_path: toPath }).then(function(response) {
        if (response) {
          fileInput.style.width = 175 + "px";
          let data = response.result.metadata;
          fileInput.value = data.name;
          fileToRename.dataset.filename = data.name;
          fileToRename.dataset.file_path = data.path_lower;
          fileToRename.dataset.time_modified = data.server_modified;
          resolve(fileToRename);
        }
      }).catch(function(error) {
        console.log(error);
        reject();
      });
    });
  }
  editImageHandler($uploadFileEvent) {
    let self2 = this;
    return new Promise(function(resolve, reject) {
      let selectedFile = $uploadFileEvent.getFileContainerEvent().getSelectedFile();
      if (selectedFile) {
        const dbx = self2.getDropbox();
        let getTemporaryLink = dbx.filesGetTemporaryLink({ path: selectedFile.dataset.file_path });
        getTemporaryLink.then(function(response) {
          return response.result.link;
        }).then(function(link) {
          let config = {
            translations: {
              en: {
                "toolbar.save": "Save",
                "toolbar.apply": "Apply",
                "toolbar.download": "Save Changes"
              }
            }
          };
          const onBeforeComplete = /* @__PURE__ */ __name(function(props) {
            props.canvas.toBlob(function(blob) {
              let file = new File([blob], selectedFile.dataset.filename);
              dbx.filesUpload({ path: selectedFile.dataset.file_path, contents: file, mode: "overwrite" }).then(function(response) {
                $uploadFileEvent.updateFileProgress(file.name, 100, self2.driveSignature, " \u2713 Updated");
              }).catch(function(error) {
                console.log(error);
                reject();
              });
            });
          }, "onBeforeComplete");
          const ImageEditor = new FilerobotImageEditor(config, {
            onBeforeComplete
          });
          ImageEditor.open(link);
        }).catch(function(error) {
          reject();
        });
      }
    });
  }
  deleteFileHandler(filesToBeDeleted, $fileContainerEvent) {
    let self2 = this, deleteEntries = [];
    filesToBeDeleted.forEach(function(value, index, array) {
      let path = { path: value.file_path };
      deleteEntries.push(path);
    });
    return new Promise(function(resolve, reject) {
      const dbx = self2.getDropbox();
      dbx.filesDeleteBatch({
        entries: deleteEntries
      }).then((response) => {
        if (response.status !== 200) {
          reject();
        }
        resolve("File(s) Deletion Is In Progress, Refresh After Few Seconds (Might Take Longer)");
      }).catch(function(error) {
        console.log(error);
        reject();
      });
    });
  }
  openFolderHandler(file, $fileContainerEvent) {
    let self2 = this;
    return new Promise(function(resolve, reject) {
      if (file.dataset.file_type === "directory") {
        self2.listFolder(file.dataset.file_path).then(function() {
          resolve();
        }).catch(function(error) {
          console.log(error);
          reject();
        });
      }
    });
  }
  parseDropboxSharedLink(url, queryString = "?dl=1") {
    const hiddenLink = document.createElement("a");
    hiddenLink.href = url;
    return hiddenLink.origin + hiddenLink.pathname + queryString;
  }
  copyLinkHandler($fileContainerEvent, selectedFile) {
    let self2 = this;
    return new Promise((resolve, reject) => __async(this, null, function* () {
      const dbx = self2.getDropbox();
      if (selectedFile === null) {
        selectedFile = $fileContainerEvent.getSelectedFile();
      }
      let path = selectedFile.dataset.file_path, link;
      if (link = self2.sharedLink.get(path)) {
        let url = self2.parseDropboxSharedLink(link);
        yield copyToClipBoard(url).then(() => {
          resolve(url);
        });
      } else {
        let sharedLink = dbx.sharingCreateSharedLinkWithSettings({
          path,
          settings: {
            audience: "public",
            access: "viewer",
            requested_visibility: "public",
            allow_download: true
          }
        });
        sharedLink.then((response) => __async(this, null, function* () {
          let url = self2.parseDropboxSharedLink(response.result.url);
          self2.sharedLink.set(path, response.result.url);
          yield copyToClipBoard(url).then(() => {
            resolve(url);
          });
        })).catch(function(err) {
          if (!err.error) {
            reject();
          }
          return err.error;
        }).then((err) => {
          if (err.error && err.error[".tag"] === "shared_link_already_exists") {
            let getExistingSharedLink = dbx.sharingListSharedLinks({ path, direct_only: true });
            getExistingSharedLink.then((response) => __async(this, null, function* () {
              let url = self2.parseDropboxSharedLink(response.result.links[0].url);
              self2.sharedLink.set(path, response.result.links[0].url);
              yield copyToClipBoard(url).then(() => {
                self2.sharedLink.set(path, response.result.links[0].url);
                resolve(url);
              });
            }));
          }
        }).catch(() => {
          reject();
        });
      }
    }));
  }
  copyPreviewLinkHandler($fileContainerEvent, selectedFile) {
    let self2 = this;
    return new Promise((resolve, reject) => __async(this, null, function* () {
      const dbx = self2.getDropbox();
      if (selectedFile === null) {
        selectedFile = $fileContainerEvent.getSelectedFile();
      }
      let path = selectedFile.dataset.file_path, link;
      if (link = self2.sharedLink.get(path)) {
        let url = self2.parseDropboxSharedLink(link, "?raw=1");
        yield copyToClipBoard(url).then(() => resolve(url));
      } else {
        let sharedLink = dbx.sharingCreateSharedLinkWithSettings({
          path,
          settings: {
            audience: "public",
            access: "viewer",
            requested_visibility: "public",
            allow_download: true
          }
        });
        sharedLink.then((response) => __async(this, null, function* () {
          let url = self2.parseDropboxSharedLink(response.result.url, "?raw=1");
          self2.sharedLink.set(path, response.result.url);
          yield copyToClipBoard(url).then(() => {
            resolve(url);
          });
        })).catch((err) => {
          if (!err.error) {
            reject();
          }
          return err.error;
        }).then((err) => {
          if (err.error && err.error[".tag"] === "shared_link_already_exists") {
            let getExistingSharedLink = dbx.sharingListSharedLinks({ path, direct_only: true });
            getExistingSharedLink.then((response) => __async(this, null, function* () {
              let url = self2.parseDropboxSharedLink(response.result.links[0].url, "?raw=1");
              self2.sharedLink.set(path, response.result.links[0].url);
              yield copyToClipBoard(url).then(() => {
                self2.sharedLink.set(path, response.result.links[0].url);
                resolve(url);
              });
            }));
          }
        }).catch(() => {
          reject();
        });
      }
    }));
  }
  createFolder($fileContainerEvent, $newFolderProperties) {
    let self2 = this;
    return new Promise(function(resolve, reject) {
      const dbx = self2.getDropbox();
      const newFolder = $fileContainerEvent.getCurrentDirectory() + "/" + $newFolderProperties.name;
      dbx.filesCreateFolderV2({ path: newFolder }).then(function(response) {
        if (response.status === 200) {
          resolve();
        }
      }).catch(function(error) {
        console.log(error);
        reject();
      });
    });
  }
  refresh($fileContainerEvent) {
    let self2 = this;
    return new Promise(function(resolve, reject) {
      self2.listFolder($fileContainerEvent.getCurrentDirectory()).then(function() {
        resolve();
      }).catch(function(error) {
        console.log(error);
        reject();
      });
    });
  }
  getDropbox() {
    return new Dropbox.Dropbox({ accessToken: `${this.accessToken}` });
  }
  uploadFile(fileSettings, filename, $uploadFileEvent) {
    let preFlightData = fileSettings.preFlightData;
    let self2 = this;
    this.createPreflightData(fileSettings, preFlightData.chunksToSend, preFlightData.Byteperchunk, preFlightData.Totalblobsize).then(function(dataToFill) {
      fileSettings.preFlightData.dataToFill = dataToFill;
      if (fileSettings.preFlightData.dataToFill) {
        if (fileSettings.preFlightData.dataToFill.length > 0 && !fileSettings.uploaded) {
          if (!fileSettings.pause) {
            self2.throttleSend(fileSettings, filename, $uploadFileEvent);
          }
        }
      }
    });
  }
  throttleSend(fileSettings, filename, $uploadFileEvent) {
    let preFlightData = fileSettings.preFlightData, data = fileSettings.preFlightData.dataToFill, fileObject = fileSettings.fileObject;
    for (let i = 0, len = data.length; i < len; i++) {
      if (data) {
        let chunk = fileObject.slice(data[i].startSlice, data[i].endSlice);
        if (preFlightData.throttleSwitch) {
          break;
        }
        fileSettings.preFlightData.sentApi = fileSettings.preFlightData.sentApi + 1;
        $uploadFileEvent.$uploadedFilesObject.set(filename, fileSettings);
        let offset = data[i].startSlice;
        this.uploadBlob(chunk, filename, $uploadFileEvent, offset);
      }
    }
  }
  uploadBlob(chunk, filename, $uploadFileEvent, offset) {
    const dbx = this.getDropbox(), sessionId = this.getSessionID(filename), cursor = { session_id: sessionId, offset };
    let self2 = this;
    let fileSettings = $uploadFileEvent.getUploadFileObject(filename, self2.driveSignature);
    if (fileSettings.preFlightData.sentApi === fileSettings.preFlightData.maxRequestToSend) {
      fileSettings.preFlightData.throttleSwitch = true;
      $uploadFileEvent.$uploadedFilesObject.set(filename, fileSettings);
    }
    if (this.isLastOffset(filename, offset)) {
      const uploadPath = $uploadFileEvent.getFileContainerEvent().getCurrentDirectory() + "/" + fileSettings.fileObject.name;
      const commit = { path: uploadPath, mode: "add", autorename: true, mute: false };
      return dbx.filesUploadSessionFinish({
        cursor: { session_id: sessionId, offset: fileSettings.fileObject.size },
        commit,
        contents: chunk
      }).then(function() {
        $uploadFileEvent.setUploadCompleted(filename, self2.driveSignature);
        localStorage.removeItem(filename);
        if ($uploadFileEvent.isUploadingSequentially()) {
          $uploadFileEvent.setSequenceDone(filename, self2.driveSignature);
          $uploadFileEvent.uploadFileNextSequence(self2.driveSignature);
        }
      });
    } else {
      dbx.filesUploadSessionAppendV2({ cursor, close: false, contents: chunk }).then(() => {
        self2.updateOffsetDone(filename, offset);
        fileSettings.preFlightData.noOfReceivedResponse = fileSettings.preFlightData.noOfReceivedResponse + 1;
        $uploadFileEvent.$uploadedFilesObject.set(filename, fileSettings);
        let percentageInt = Math.round(self2.uploadPercentage(filename));
        if (fileSettings.preFlightData.noOfReceivedResponse === fileSettings.preFlightData.maxRequestToSend) {
          $uploadFileEvent.releaseThrottle(filename, self2.driveSignature);
          if (!fileSettings.uploaded) {
            self2.uploadFile(fileSettings, filename, $uploadFileEvent);
            $uploadFileEvent.updateFileProgress(filename, percentageInt, self2.driveSignature);
          }
        }
      }).catch((err) => {
        err = err.error;
        if (err.error.hasOwnProperty("correct_offset")) {
          let correctOffset = err.error.correct_offset;
          self2.repairOffsetPosition(filename, correctOffset);
          $uploadFileEvent.releaseThrottle(filename, self2.driveSignature);
          self2.uploadFile(fileSettings, filename, $uploadFileEvent);
        }
      });
    }
  }
  createPreflightData(fileSetting, chunksToSend, $Byteperchunk, $totalBlobSize) {
    let self2 = this;
    let uploadFilename = self2.driveSignature + "_" + fileSetting.fileObject.name;
    return new Promise(function(resolve, reject) {
      return __async(this, null, function* () {
        const dbx = self2.getDropbox();
        let $preFlight;
        if ($preFlight = JSON.parse(localStorage.getItem(uploadFilename))) {
          $preFlight = $preFlight.dataToFill.filter((chunk) => !chunk.done);
          return resolve($preFlight);
        }
        let $startSlice = 0, $endSlice = $Byteperchunk, fileObject = fileSetting.fileObject;
        $preFlight = { session_id: "", dataToFill: [] };
        for (let i = 0; i <= chunksToSend; i++) {
          if (i === 0) {
            yield dbx.filesUploadSessionStart({
              close: false,
              contents: fileObject.slice($startSlice, $endSlice)
            }).then((response) => {
              $preFlight.dataToFill.push({ startSlice: $startSlice, endSlice: $endSlice, done: true });
              $preFlight.session_id = response.result.session_id;
            }).catch(function() {
              reject();
            });
          } else {
            $preFlight.dataToFill.push({ startSlice: $startSlice, endSlice: $endSlice, done: false });
          }
          $totalBlobSize = $totalBlobSize - $Byteperchunk;
          $startSlice = $endSlice;
          $endSlice = $startSlice + $Byteperchunk;
        }
        localStorage.setItem(uploadFilename, JSON.stringify($preFlight));
        $preFlight = $preFlight.dataToFill.filter((chunk) => !chunk.done);
        return resolve($preFlight);
      });
    });
  }
  isLastOffset(filename, offset) {
    let preFlight = JSON.parse(localStorage.getItem(filename));
    if (preFlight) {
      preFlight = preFlight.dataToFill;
      return preFlight[preFlight.length - 1].startSlice === offset;
    }
    return false;
  }
  updateOffsetDone(filename, offset, updateWith = true) {
    let preFlight = JSON.parse(localStorage.getItem(filename));
    if (preFlight) {
      let data = preFlight.dataToFill;
      for (let i = 0, length = data.length; i < length; i++) {
        if (data[i].startSlice === offset) {
          data[i].done = updateWith;
          localStorage.setItem(filename, JSON.stringify({ session_id: preFlight.session_id, dataToFill: data }));
          break;
        }
      }
    }
  }
  getSessionID(filename) {
    let session_id = JSON.parse(localStorage.getItem(filename));
    if (session_id) {
      return session_id.session_id;
    }
    return false;
  }
  uploadPercentage(filename) {
    let preFlight = JSON.parse(localStorage.getItem(filename));
    if (preFlight) {
      let $totalChunks = preFlight.dataToFill.length;
      let $totalUploadedChunks = preFlight.dataToFill.filter((chunk) => chunk.done).length;
      return $totalUploadedChunks / $totalChunks * 100;
    }
  }
  repairOffsetPosition(filename, offset) {
    let preFlight = JSON.parse(localStorage.getItem(filename));
    if (preFlight) {
      let data = preFlight.dataToFill;
      for (let i = 0, length = data.length; i < length; i++) {
        if (data[i].endSlice > offset) {
          break;
        }
        if (data[i].endSlice <= offset) {
          data[i].done = true;
          localStorage.setItem(filename, JSON.stringify({ session_id: preFlight.session_id, dataToFill: data }));
        }
      }
    }
  }
  breadCrumbClickNavigationHandler(path) {
    return new Promise((resolve, reject) => {
      this.listFolder(path).then(() => resolve()).catch(() => reject());
    });
  }
};
__name(DropboxDiskDrive, "DropboxDiskDrive");

// src/Core/StorageDriversManager.ts
var StorageDriversManager = class {
  constructor() {
    this._$driveSystem = new Map();
  }
  get $driveSystem() {
    return this._$driveSystem;
  }
  attachDriveStorage($driverMethodInterface) {
    this.$driveSystem.set($driverMethodInterface.driveSignature, $driverMethodInterface);
    return this;
  }
  detachDriveStorage($driveSignature) {
    if (this.$driveSystem.has($driveSignature)) {
      this.$driveSystem.delete($driveSignature);
    }
  }
  getDriveStorage($driveSignature) {
    if (this.$driveSystem.has($driveSignature)) {
      return this.$driveSystem.get($driveSignature);
    }
    throw new DOMException(`DriveStorage "${$driveSignature}" doesn't exist`);
  }
  getFirstDriveStorage() {
    return [...this.$driveSystem][0][1];
  }
};
__name(StorageDriversManager, "StorageDriversManager");
if (!window.hasOwnProperty("TonicsMedia")) {
  window.TonicsMedia = {};
}
window.TonicsMedia.StorageDriversManager = () => new StorageDriversManager();

// src/Core/Events/CutFileEvent.ts
var CutFileEvent = class {
  constructor($fileContainerEvent) {
    this.removePreviousCutFiles($fileContainerEvent);
    this.cutSelectedFiles($fileContainerEvent);
    $fileContainerEvent.removeContextMenu();
  }
  removePreviousCutFiles($fileContainerEvent) {
    $fileContainerEvent.cutFile = [];
  }
  cutSelectedFiles($fileContainerEvent) {
    let selectedFiles = $fileContainerEvent.getAllSelectedFiles();
    if (selectedFiles.length > 0) {
      $fileContainerEvent.cutFile = selectedFiles;
    }
  }
};
__name(CutFileEvent, "CutFileEvent");

// src/Core/Events/CopyPreviewLinkEvent.ts
var CopyPreviewLinkEvent = class {
  constructor($fileContainerEvent) {
    this.fileContainerEvent = $fileContainerEvent;
    this.copiedLinkFile = $fileContainerEvent.getSelectedFile();
    this.copiedLink = "";
  }
  getFileContainerEvent() {
    return this.fileContainerEvent;
  }
  handleCopyLink(selectedFile = null) {
    let self2 = this;
    return this.getFileContainerEvent().currentDrive.copyPreviewLinkHandler(this.fileContainerEvent, selectedFile);
  }
  onSuccess(message) {
    this.setCopiedLink(message);
    this.fileContainerEvent.removeContextMenu();
    successToast(Message.Link.Copy.Preview.Success).then();
    this.fileContainerEvent.dispatchEventToHandlers(this, CopyPreviewLinkEvent);
  }
  onError(message = null) {
    errorToast(Message.Link.Copy.Preview.Error).then();
  }
  getCopiedLinkFile() {
    return this.copiedLinkFile;
  }
  setCopiedLink(val) {
    this.copiedLink = val;
  }
  getCopiedLink() {
    return this.copiedLink;
  }
};
__name(CopyPreviewLinkEvent, "CopyPreviewLinkEvent");

// src/Core/Events/RenameFileEvent.ts
var RenameFileEvent = class {
  get renamedFile() {
    return this._renamedFile;
  }
  set renamedFile(value) {
    this._renamedFile = value;
  }
  constructor($fileContainerEvent) {
    this.fileContainerEvent = $fileContainerEvent;
  }
  handleRenameFile() {
    let fileToRename = this.fileContainerEvent.getSelectedFile();
    if (fileToRename) {
      let fileInput = fileToRename.querySelector(".tonics-file-filename-input");
      this.fileContainerEvent.removeContextMenu();
      inputToast("Rename File To: ", fileInput.value).then((result) => {
        if (result.isConfirmed) {
          fileInput.value = result.value;
          this.fileContainerEvent.currentDrive.renameFileHandler(fileToRename, this.fileContainerEvent).then((message) => {
            this.onSuccess(message);
          }).catch(() => {
            this.onError();
          });
        }
      });
    }
  }
  onSuccess(renamedFile = "") {
    successToast(Message.Rename.Success).then();
    this.renamedFile = renamedFile;
    this.fileContainerEvent.clearSelection();
    this.fileContainerEvent.dispatchEventToHandlers(this, RenameFileEvent);
  }
  onError(message = "") {
    errorToast(Message.Rename.Error).then();
  }
};
__name(RenameFileEvent, "RenameFileEvent");

// src/Core/Events/DeleteFileEvent.ts
var DeleteFileEvent = class {
  constructor($fileContainerEvent) {
    this.fileContainerEvent = $fileContainerEvent;
  }
  handleDeleteFile() {
    return __async(this, null, function* () {
      let fileContainerEvent = this.fileContainerEvent;
      fileContainerEvent.removeDeletionMark(fileContainerEvent.getAllFiles());
      let allSelectedFile = fileContainerEvent.getAllSelectedFiles();
      let filesToBeDeleted = [];
      if (allSelectedFile.length > 0) {
        fileContainerEvent.markForDeletion(allSelectedFile);
        fileContainerEvent.getFilesToBeDeleted().forEach((file) => {
          filesToBeDeleted.push(file.dataset);
        });
      }
      fileContainerEvent.removeContextMenu();
      return fileContainerEvent.currentDrive.deleteFileHandler(filesToBeDeleted, fileContainerEvent);
    });
  }
  onSuccess(message = "") {
    this.fileContainerEvent.resetPreviousFilesState();
    if (!message) {
      message = Message.Deleted.Success;
    }
    successToast(message).then();
    this.fileContainerEvent.dispatchEventToHandlers(this, DeleteFileEvent);
  }
  onError(message = "") {
    errorToast(Message.Deleted.Error).then();
  }
};
__name(DeleteFileEvent, "DeleteFileEvent");

// src/Core/Events/PasteFileEvent.ts
var PasteFileEvent = class {
  constructor($fileContainerEvent) {
    this.fileContainerEvent = $fileContainerEvent;
  }
  handlePasteFile() {
    let fileContainerEvent = this.fileContainerEvent;
    fileContainerEvent.removeContextMenu();
    let directory = fileContainerEvent.getSelectedFile();
    if (!directory) {
      directory = document.createElement("li");
      directory.dataset.file_type = "directory";
      directory.dataset.file_path = fileContainerEvent.getCurrentDirectory();
      directory.dataset.filename = fileContainerEvent.getCurrentDirectoryFilename();
      directory.dataset.ext = "null";
    }
    if (directory) {
      if (directory.dataset.ext == "null" && directory.dataset.file_type == "directory") {
        directory.dataset.paste = "true";
        return fileContainerEvent.currentDrive.moveFileHandler(directory, fileContainerEvent);
      }
    }
    return false;
  }
  onSuccess(message = "") {
    this.fileContainerEvent.resetPreviousFilesState();
    this.fileContainerEvent.cutFile = [];
    if (!message) {
      message = Message.Move.Success;
    }
    successToast(message).then();
    this.fileContainerEvent.dispatchEventToHandlers(this, PasteFileEvent);
  }
  onError(message = "") {
    if (message) {
      errorToast(message).then();
    } else {
      errorToast(Message.Move.Error).then();
    }
  }
};
__name(PasteFileEvent, "PasteFileEvent");

// src/Core/Events/EditImageFileEvent.ts
var EditImageFileEvent = class {
  constructor($fileContainerEvent) {
    this.fileContainerEvent = $fileContainerEvent;
    this.handleEditImageFile();
  }
  handleEditImageFile() {
    let self2 = this;
    loadScriptDynamically(Script.FileRobotImageEditor.PATH, Script.FileRobotImageEditor.ID).then(function() {
      return self2.fileContainerEvent.currentDrive.editImageHandler(new UploadFileEvent(self2.fileContainerEvent, null, null));
    }.bind(self2));
  }
};
__name(EditImageFileEvent, "EditImageFileEvent");

// src/Core/Events/FileContainerEvent.ts
var FileContainerEvent = class {
  constructor($fileContainer, $loadDriveEventClass, currentDrive) {
    this._cutFile = [];
    this._pasteTo = null;
    this.fileContainer = $fileContainer;
    this.loadDriveEventClass = $loadDriveEventClass;
    this.currentDrive = currentDrive;
  }
  get currentDrive() {
    return this._currentDrive;
  }
  set currentDrive(value) {
    this._currentDrive = value;
  }
  get cutFile() {
    return this._cutFile;
  }
  set cutFile(value) {
    this._cutFile = value;
  }
  get pasteTo() {
    return this._pasteTo;
  }
  set pasteTo(value) {
    this._pasteTo = value;
  }
  getFileContainer() {
    return this.fileContainer;
  }
  getLoadDriveEventClass() {
    return this.loadDriveEventClass;
  }
  getCurrentDirectory() {
    var _a, _b;
    return (_b = (_a = document.querySelector(".breadcrumb")) == null ? void 0 : _a.lastElementChild) == null ? void 0 : _b.getAttribute("data-pathtrail");
  }
  getCurrentDirectoryFilename() {
    var _a, _b;
    return (_b = (_a = document.querySelector(".breadcrumb")) == null ? void 0 : _a.lastElementChild) == null ? void 0 : _b.getAttribute("data-filename");
  }
  getBreadCrumbElement() {
    return document.querySelector(".breadcrumb");
  }
  titleCase(str) {
    return str.toLowerCase().replace(/\b(\w)/g, function(s) {
      return s.toLocaleUpperCase();
    });
  }
  getSelectedFile() {
    return document.querySelector('[data-selected="true"]');
  }
  getAllSelectedFiles() {
    return document.querySelectorAll('[data-selected="true"]');
  }
  getAllFiles() {
    return document.querySelectorAll("li.tonics-file");
  }
  getCutFiles() {
    return this.cutFile;
  }
  getFilesToBeDeleted() {
    return document.querySelectorAll('[data-delete="true"]');
  }
  getFileByListID(id) {
    return document.querySelector(`[data-list_id="${id}"]`);
  }
  getCopiedFiles() {
    return document.querySelectorAll('[data-copied="true"]');
  }
  getContextMenu() {
    return document.querySelector(FileManagerElements.FILES.CONTEXT);
  }
  getDiskDrives() {
    return document.querySelector(FileManagerElements.DRIVE.FILE_DISK_DRIVES);
  }
  removeContextMenu() {
    this.getContextMenu().classList.remove("show");
  }
  highlightFile(file) {
    file.classList.add("selected-file");
    file.dataset.selected = "true";
    let headerMenu = document.querySelector(FileManagerElements.HEAD.PARENT);
    headerMenu.style.top = "0";
    switch (file.dataset.file_type) {
      case "directory":
        if (this.cutFile.length > 0) {
          return activateMenus([MenuActions().PASTE_FILE, MenuActions().RENAME_FILE, MenuActions().CUT_FILE, MenuActions().DELETE_FILE]);
        }
        return activateMenus([MenuActions().RENAME_FILE, MenuActions().CUT_FILE, MenuActions().DELETE_FILE]);
      case "file":
        if (this.cutFile.length > 0) {
          return activateMenus([MenuActions().PASTE_FILE, MenuActions().RENAME_FILE, MenuActions().CUT_FILE, MenuActions().DELETE_FILE]);
        }
        activateMenus([
          MenuActions().RENAME_FILE,
          MenuActions().CUT_FILE,
          MenuActions().DELETE_FILE,
          MenuActions().COPY_PREVIEW_LINK,
          MenuActions().COPY_LINK
        ]);
    }
  }
  unHighlightFile(file) {
    file.classList.remove("selected-file");
    file.dataset.selected = "false";
    file.setAttribute("readonly", "true");
    if (this.getAllSelectedFiles().length < 1) {
      deActivateMenus([
        MenuActions().RENAME_FILE,
        MenuActions().CUT_FILE,
        MenuActions().DELETE_FILE,
        MenuActions().PASTE_FILE,
        MenuActions().COPY_PREVIEW_LINK,
        MenuActions().COPY_LINK
      ]);
    }
  }
  resetPreviousFilesState() {
    let singleFile = FileManagerElements.FILES.SINGLE_FILE;
    let headerMenu = document.querySelector(FileManagerElements.HEAD.PARENT), headerHeight = headerMenu == null ? void 0 : headerMenu.getBoundingClientRect().height;
    headerMenu.style.top = `-${headerHeight}px`;
    document.querySelectorAll(singleFile).forEach((el) => {
      var _a;
      el.classList.remove("selected-file");
      el.setAttribute("data-selected", "false");
      (_a = el.querySelector(".tonics-file-filename-input")) == null ? void 0 : _a.setAttribute("readonly", "true");
    });
    deActivateMenus([
      MenuActions().RENAME_FILE,
      MenuActions().CUT_FILE,
      MenuActions().DELETE_FILE,
      MenuActions().PASTE_FILE,
      MenuActions().COPY_PREVIEW_LINK,
      MenuActions().COPY_LINK
    ]);
    if (this.cutFile.length > 0) {
      return activateMenus([MenuActions().PASTE_FILE]);
    }
  }
  removeAllDriveSelectionMark() {
    this.loadDriveEventClass.removeAllDriveSelectionMark();
  }
  removeDeletionMark(files) {
    if (files.length > 0) {
      files.forEach((element) => {
        element.dataset.delete = "false";
      });
    }
  }
  markForDeletion(selectedFiles) {
    if (selectedFiles.length > 0) {
      selectedFiles.forEach((element) => {
        element.dataset.delete = "true";
      });
    }
  }
  clearSelection() {
    var _a;
    (_a = window == null ? void 0 : window.getSelection()) == null ? void 0 : _a.empty();
  }
  menuEventAction(menuItemEvent) {
    let self2 = this;
    if (EventsConfig.hasOwnProperty(menuItemEvent)) {
      let menuEventActions = MenuActions(), key;
      for (key in menuEventActions) {
        let eventAction = menuEventActions[key];
        if (eventAction == menuItemEvent) {
          switch (eventAction) {
            case "EditImageFileEvent":
              return self2.editImageFileEvent();
            case "DeleteFileEvent":
              return self2.deleteFileEvent();
            case "RenameFileEvent":
              return self2.renameFileEvent();
            case "PasteFileEvent":
              return self2.pasteFileEvent();
            case "UploadFileEvent":
              return self2.uploadFileEvent();
            case "CopyLinkEvent":
              return self2.copyLinkEvent();
            case "CopyPreviewLinkEvent":
              return self2.copyPreviewLinkEvent();
            case "CutFileEvent":
              return new CutFileEvent(this);
            case "NewFolderEvent":
              inputToast("Folder Name").then((result) => {
                this.removeContextMenu();
                if (result.isConfirmed) {
                  let folderProperties = {
                    name: result.value
                  };
                  return self2.currentDrive.createFolder(this, folderProperties).then(function() {
                    successToast(Message.Folder.Success).then();
                    self2.currentDrive.refresh(self2).then();
                  }.bind(self2)).catch(function() {
                    errorToast(Message.Folder.Error).then();
                  });
                }
              });
              return;
            case "RefreshFolderEvent":
              return self2.refreshFolderEvent();
            default:
              break;
          }
        }
      }
    }
  }
  dispatchEventToHandlers($eventObject, $eventClass) {
    dispatchEventToHandlers($eventObject, $eventClass);
  }
  editImageFileEvent() {
    new EditImageFileEvent(this);
  }
  refreshFolderEvent() {
    return this.currentDrive.refresh(this).then(() => {
      this.removeContextMenu();
      successToast(Message.Refresh.Success).then();
    });
  }
  deleteFileEvent() {
    let deleteFileEvent = new DeleteFileEvent(this);
    promptToast("Are You Sure You Want To Delete File(s) ?", "Delete", () => {
      deleteFileEvent.handleDeleteFile().then((message) => {
        deleteFileEvent.onSuccess(message);
      }).catch(function() {
        deleteFileEvent.onError();
      });
    });
  }
  pasteFileEvent() {
    let pasteFileEvent = new PasteFileEvent(this);
    let promise = pasteFileEvent.handlePasteFile();
    if (promise instanceof Promise) {
      promise.then((message) => {
        pasteFileEvent.onSuccess(message);
      }).catch(function() {
        pasteFileEvent.onError();
      });
    }
    if (promise === false) {
      pasteFileEvent.onError("Failed To Paste Into Directory");
    }
  }
  copyLinkEvent() {
    let copyLinkEvent = new CopyLinkEvent(this);
    copyLinkEvent.handleCopyLink(this.getSelectedFile()).then((message) => {
      copyLinkEvent.onSuccess(message);
    }).catch((e) => {
      copyLinkEvent.onError();
    });
  }
  copyPreviewLinkEvent() {
    let copyPreviewLinkEvent = new CopyPreviewLinkEvent(this);
    copyPreviewLinkEvent.handleCopyLink(this.getSelectedFile()).then((message) => {
      copyPreviewLinkEvent.onSuccess(message);
    }).catch(function() {
      copyPreviewLinkEvent.onError();
    });
  }
  renameFileEvent() {
    let renameFileEvent = new RenameFileEvent(this);
    renameFileEvent.handleRenameFile();
  }
  uploadFileEvent() {
    let self2 = this;
    let input = document.createElement("input");
    input.type = "file";
    input.multiple = true;
    input.click();
    input.onchange = function(e) {
      let files = e.target.files;
      const uploadTo = self2.getCurrentDirectory();
      new UploadFileEvent(self2, files, uploadTo);
    };
  }
};
__name(FileContainerEvent, "FileContainerEvent");

// src/Core/Events/LoadDriveDataEvent.ts
var LoadDriveDataEvent = class {
  constructor($data, $loadDriveEventClass) {
    this.data = $data;
    this.loadDriveEventClass = $loadDriveEventClass;
  }
  getData() {
    return this.data;
  }
  getLoadDriveEventClass() {
    return this.loadDriveEventClass;
  }
  getFiles() {
    var _a;
    return (_a = this.getData()) == null ? void 0 : _a.data;
  }
  getCurrentPath() {
    var _a;
    return (_a = this.getData()) == null ? void 0 : _a.more;
  }
  getBreadCrumbElement() {
    return document.querySelector(".breadcrumb");
  }
};
__name(LoadDriveDataEvent, "LoadDriveDataEvent");
if (!window.hasOwnProperty("TonicsScript")) {
  window.TonicsMedia = {};
}
window.TonicsMedia.LoadDriveDataEvent = ($data, $loadDriveEventClass) => new LoadDriveDataEvent($data, $loadDriveEventClass);

// src/Core/Events/LoadDriveEvent.ts
var LoadDriveEvent = class {
  get appURL() {
    return this._appURL;
  }
  set appURL(value) {
    this._appURL = value;
  }
  get eventsConfig() {
    return this._eventsConfig;
  }
  set eventsConfig(value) {
    this._eventsConfig = value;
  }
  get driveStorageManager() {
    return this._driveStorageManager;
  }
  set driveStorageManager(value) {
    this._driveStorageManager = value;
  }
  constructor(driveStorageManager, AppURL = "") {
    window.TonicsFileManager = {
      events: {}
    };
    this._eventsConfig = EventsConfig;
    this.driveStorageManager = driveStorageManager;
    let fileParent = document.querySelector(".tonics-files-parent");
    let filesContainer = fileParent == null ? void 0 : fileParent.querySelector(".tonics-files-container");
    this._appURL = AppURL;
    this.processDriveRootFolder();
    if (filesContainer) {
      let fileContainerEventHandler = attachEventAndHandlersToHandlerProvider(this.eventsConfig, FileContainerEvent);
      let tonicsFileContainerEventObject = new FileContainerEvent(filesContainer, this, this.driveStorageManager.getFirstDriveStorage());
      window.TonicsFileManager.events.fileContainerEvent = tonicsFileContainerEventObject;
      window.TonicsFileManager.events.loadDriveEvent = this;
      this.getEventDispatcher().setHandler(fileContainerEventHandler).dispatch(tonicsFileContainerEventObject);
    }
  }
  processDriveRootFolder() {
    let driveContainer = document.querySelector(FileManagerElements.DRIVE.DISK_DRIVE_CONTAINER);
    driveContainer == null ? void 0 : driveContainer.replaceChildren();
    this.driveStorageManager.$driveSystem.forEach(function(value, key) {
      let icon = value.getDriveIcon();
      driveContainer == null ? void 0 : driveContainer.insertAdjacentHTML("beforeend", `
            <li class="tonics-individual-drive" data-drivename=${key}>
          <a href="javascript:void(0);" class="drive-link">
              <button class="drive-toggle" aria-expanded="false" aria-label="Expand child menu">
                  <svg class="icon tonics-drive-icon">
                      <use class="svgUse" xlink:href=${icon}></use>
                  </svg>
              </button>
              <span class="tonics-drive-selected display-none"> \u2713 </span>
              &nbsp;
              <!-- DRIVE NAME -->
              <span class="drive-name"> ${key} Drive</span>
          </a>
      </li>`);
    });
  }
  processFiles(data) {
    let driveInitDataListeners = attachEventAndHandlersToHandlerProvider(EventsConfig, LoadDriveDataEvent);
    this.getEventDispatcher().setHandler(driveInitDataListeners).dispatch(new LoadDriveDataEvent(data, this));
  }
  bootDiskDrive($driveSignature) {
    if (!this.driveStorageManager.$driveSystem.has($driveSignature)) {
      throw new DOMException(`Couldn't Boot Drive "${$driveSignature}", Perhaps, It Doesn't Exit?`);
    }
    filesLoadingAnimation();
    this.driveStorageManager.getDriveStorage($driveSignature).coldBootStorageDisk().then(() => {
      filesLoadingAnimation(false);
      this.removeAllDriveSelectionMark();
      let driveElement = document.querySelector(`[data-drivename="${$driveSignature}"]`);
      driveElement.querySelector(FileManagerElements.DRIVE.DRIVE_SELECTED).classList.remove("display-none");
    });
  }
  removeAllDriveSelectionMark() {
    let singleFile = FileManagerElements.DRIVE.DRIVE_SELECTED;
    document.querySelectorAll(singleFile).forEach((el) => {
      el.classList.add("display-none");
    });
  }
  successMessage(message) {
    return successToast(message);
  }
  errorMessage(message) {
    return errorToast(message);
  }
  getEventDispatcher() {
    return new EventDispatcher();
  }
};
__name(LoadDriveEvent, "LoadDriveEvent");
if (!window.hasOwnProperty("TonicsScript")) {
  window.TonicsMedia = {};
}
window.TonicsMedia.LoadDriveEvent = (driveStorageManager, AppURL = "") => new LoadDriveEvent(driveStorageManager, AppURL);

// src/Core/TonicsFileManager.ts
var TonicsFileManagerExcrete = class {
  constructor($toExcrete) {
    if ($toExcrete === "LocalDiskDrive") {
      return ($driveSig, $bearerToken = "", $siteURL) => new LocalDiskDrive(this.replaceSpaceWithDashAndFilter($driveSig), $bearerToken, $siteURL);
    }
    if ($toExcrete === "DropboxDiskDrive") {
      return ($driveSig, $accessToken) => new DropboxDiskDrive(this.replaceSpaceWithDashAndFilter($driveSig), $accessToken);
    }
    if ($toExcrete === "StorageDriversManager") {
      return new StorageDriversManager();
    }
    if ($toExcrete === "LoadDriveEvent") {
      return ($storageManager, appURL = "") => new LoadDriveEvent($storageManager, appURL);
    }
  }
  replaceSpaceWithDashAndFilter(str) {
    return str.replace(/\s/g, "-").match(/^[a-zA-Z0-9-]+$/g) ? str.replace(/\s/g, "-").match(/^[a-zA-Z0-9-]+$/g)[0] : "";
  }
};
__name(TonicsFileManagerExcrete, "TonicsFileManagerExcrete");
if (!window.hasOwnProperty("TonicsMedia")) {
  window.TonicsMedia = {};
}
window.TonicsMedia.TonicsFileManagerExcrete = ($toExcrete) => new TonicsFileManagerExcrete($toExcrete);
export {
  TonicsFileManagerExcrete
};
/*!
* sweetalert2 v11.1.10
* Released under the MIT License.
*/

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
            preload: true,
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

/**
 * @param requestHeaders
 * @protected
 */
function defaultXHR(requestHeaders = {}) {
    let defaultHeader = {};
    return new XHRApi({...defaultHeader, ...requestHeaders});
}

let trackMainContainer = document.querySelector('main'),
    licenseIDMap = new Map(),
    windowInstanceForDownloadURL = null,
    inputLicenseDownloadURL = null,
    selectedLicense = null,
    licenseDownloadsContainer = null;

if (trackMainContainer) {
    trackMainContainer.addEventListener('click', (e) => {
        let el = e.target;
        // MORE BUTTON
        if (el.classList.contains('more-button')) {
            e.preventDefault();
            let action = el.dataset.action,
                url = el.dataset.morepageurl;
            defaultXHR(el.dataset).Get(url, function (err, data) {
                if (data) {
                    data = JSON.parse(data);
                    if (data.hasOwnProperty('status') && data.status === 200) {
                        let ul = el.closest('.menu-box-radiobox-items'),
                            moreButton = ul.querySelector('.more-button'),
                            lastMenuItem = ul.querySelector('li:nth-last-of-type(1)');
                        if (moreButton) {
                            moreButton.remove();
                        }
                        lastMenuItem.insertAdjacentHTML('afterend', data.data);
                    }
                }
            });
        }

        // License Selector Download URL
        if (el.classList.contains('upload-license-download-url')) {
            inputLicenseDownloadURL = el.parentElement.querySelector('.input-license-download-url');
            if (tonicsFileManagerURL) {
                let windowFeatures = "left=95,top=100";
                windowInstanceForDownloadURL = window.open(tonicsFileManagerURL, 'Tonics File Manager', windowFeatures);
            }
        }
    });

    window.addEventListener('message', (e) => {
        if (e.origin !== siteURL) {
            return;
        }
        let data = e.data;
        if (data.hasOwnProperty('cmd') && data.cmd === "tonics:RegularLink") {
            if (inputLicenseDownloadURL) {
                inputLicenseDownloadURL.value = data.value;
                windowInstanceForDownloadURL.close();
            }
        }
    });

    // For SEARCH
    let searchBoxInitials = [];
    trackMainContainer.addEventListener('keydown', (e) => {
        let el = e.target;
        if (el.classList.contains('menu-box-item-search')) {
            let value = el;
            if (e.code === 'Enter') {
                // clone the loaded checkboxes, or radios
                searchBoxInitials[el.dataset.menuboxname] = el.parentElement.cloneNode(true);

                e.preventDefault();
                let searchInputValue = value.value;
                searchInputValue = searchInputValue.trim();
                if (searchInputValue.length > 0 && value.dataset.hasOwnProperty('searchvalue')) {
                    value.dataset.searchvalue = searchInputValue;
                    let url = value.dataset.query + encodeURIComponent(searchInputValue);
                    defaultXHR(value.dataset).Get(url, function (err, data) {
                        if (data) {
                            data = JSON.parse(data);
                            if (data.hasOwnProperty('status') && data.status === 200) {
                                let parentElement = value.parentElement;
                                let realSearchInput = value.cloneNode(true);
                                value.parentElement.innerHTML = data.data;
                                parentElement.prepend(realSearchInput);
                            }
                        }
                    });
                }
            }
        }
    })

    trackMainContainer.addEventListener('change', (e) => {
        let el = e.target;
        // License Selector
        if (el.classList.contains('license-selector')) {
            getLicenseDownloadInfo(el);
        }
    });

    trackMainContainer.addEventListener('input', (e) => {
        let el = e.target,
            value = el;
        if (el.classList.contains('menu-box-item-search')) {
            e.preventDefault();
            let searchInputValue = value.value;
            searchInputValue = searchInputValue.trim();
            if (searchInputValue === "") {
                // Find all the selected checkbox elements
                let selectedCheckboxes = value.parentElement.querySelectorAll('input[type="checkbox"]:checked');
                if (selectedCheckboxes.length > 0) {
                    let newInnerHTML = '';
                    // Add each selected checkbox element to the beginning of the innerHTML, if it is not already present
                    for (let i = 0; i < selectedCheckboxes.length; i++) {
                        let selectCheckboxValue = selectedCheckboxes[i].value;
                        let checkbox = searchBoxInitials[value.dataset.menuboxname].querySelector(`input[type="checkbox"][value="${selectCheckboxValue}"]`);
                        if (!checkbox){
                            newInnerHTML += selectedCheckboxes[i].parentElement.outerHTML;
                        } else if (checkbox && !checkbox.checked){
                            newInnerHTML += selectedCheckboxes[i].parentElement.outerHTML;
                        } else {
                        }
                    }
                    let initialElements = searchBoxInitials[value.dataset.menuboxname];
                    // Find the first li element
                    let firstLi = initialElements.querySelector('li');
                    // Insert the newInnerHTML string before the first li element
                    firstLi.insertAdjacentHTML('beforebegin', newInnerHTML);

                    let parentElement = value.parentElement;
                    parentElement.innerHTML = initialElements.innerHTML;
                }
            }
        }
    });
}

let licenseSelector = document.querySelector('.license-selector');
if (licenseSelector && licenseSelector.dataset.new === 'true') {
    getLicenseDownloadInfo(licenseSelector);
}

function getLicenseDownloadInfo(el) {
    licenseDownloadsContainer = el.closest('li').querySelector('.license-downloads');
    selectedLicense = el.options[el.selectedIndex];
    selectedLicense.dataset.licenseID = selectedLicense.value;
    if (licenseIDMap.has(selectedLicense.value)) {
        if (licenseDownloadsContainer) {
            licenseDownloadsContainer.innerHTML = '';
            licenseDownloadsContainer.innerHTML = licenseIDMap.get(selectedLicense.value);
            return;
        }
    }
    defaultXHR(selectedLicense.dataset).Get(window.location.href, function (err, data) {
        if (data) {
            data = JSON.parse(data);
            if (data.hasOwnProperty('status') && data.status === 200) {
                if (licenseDownloadsContainer) {
                    licenseDownloadsContainer.innerHTML = '';
                    licenseDownloadsContainer.innerHTML = data.data;
                    licenseIDMap.set(selectedLicense.value, data.data);
                }
            }
        }
    });
}