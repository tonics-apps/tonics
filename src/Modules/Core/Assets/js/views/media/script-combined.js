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
        if (this.getMenuDetails().menu.propagate) {
          e.stopPropagation();
        }
        let el = e.target;
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
};var __defProp = Object.defineProperty;
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
var Nr=Object.create;var ze=Object.defineProperty;var Ur=Object.getOwnPropertyDescriptor;var jr=Object.getOwnPropertyNames,Gn=Object.getOwnPropertySymbols,Br=Object.getPrototypeOf,Jn=Object.prototype.hasOwnProperty,qr=Object.prototype.propertyIsEnumerable;var Qn=(u,e,i)=>e in u?ze(u,e,{enumerable:!0,configurable:!0,writable:!0,value:i}):u[e]=i,rn=(u,e)=>{for(var i in e||(e={}))Jn.call(e,i)&&Qn(u,i,e[i]);if(Gn)for(var i of Gn(e))qr.call(e,i)&&Qn(u,i,e[i]);return u};var $r=u=>ze(u,"__esModule",{value:!0}),s=(u,e)=>ze(u,"name",{value:e,configurable:!0});var Vr=(u,e)=>()=>(e||u((e={exports:{}}).exports,e),e.exports);var zr=(u,e,i)=>{if(e&&typeof e=="object"||typeof e=="function")for(let o of jr(e))!Jn.call(u,o)&&o!=="default"&&ze(u,o,{get:()=>e[o],enumerable:!(i=Ur(e,o))||i.enumerable});return u},Xr=u=>zr($r(ze(u!=null?Nr(Br(u)):{},"default",u&&u.__esModule&&"default"in u?{get:()=>u.default,enumerable:!0}:{value:u,enumerable:!0})),u);var $=(u,e,i)=>new Promise((o,a)=>{var l=m=>{try{f(i.next(m))}catch(v){a(v)}},d=m=>{try{f(i.throw(m))}catch(v){a(v)}},f=m=>m.done?o(m.value):Promise.resolve(m.value).then(l,d);f((i=i.apply(u,e)).next())});var Zn=Vr((te,sn)=>{(function(u,e){typeof te=="object"&&typeof sn!="undefined"?sn.exports=e():typeof define=="function"&&define.amd?define(e):(u=u||self,u.Sweetalert2=e())})(te,function(){"use strict";let u=Object.freeze({cancel:"cancel",backdrop:"backdrop",close:"close",esc:"esc",timer:"timer"}),e="SweetAlert2:",i=s(t=>{let n=[];for(let r=0;r<t.length;r++)n.indexOf(t[r])===-1&&n.push(t[r]);return n},"uniqueArray"),o=s(t=>t.charAt(0).toUpperCase()+t.slice(1),"capitalizeFirstLetter"),a=s(t=>Array.prototype.slice.call(t),"toArray"),l=s(t=>{console.warn("".concat(e," ").concat(typeof t=="object"?t.join(" "):t))},"warn"),d=s(t=>{console.error("".concat(e," ").concat(t))},"error"),f=[],m=s(t=>{f.includes(t)||(f.push(t),l(t))},"warnOnce"),v=s((t,n)=>{m('"'.concat(t,'" is deprecated and will be removed in the next major release. Please use "').concat(n,'" instead.'))},"warnAboutDeprecation"),h=s(t=>typeof t=="function"?t():t,"callIfFunction"),y=s(t=>t&&typeof t.toPromise=="function","hasToPromiseFn"),b=s(t=>y(t)?t.toPromise():Promise.resolve(t),"asPromise"),E=s(t=>t&&Promise.resolve(t)===t,"isPromise"),P=s(t=>typeof t=="object"&&t.jquery,"isJqueryElement"),_=s(t=>t instanceof Element||P(t),"isElement"),lt=s(t=>{let n={};return typeof t[0]=="object"&&!_(t[0])?Object.assign(n,t[0]):["title","html","icon"].forEach((r,c)=>{let g=t[c];typeof g=="string"||_(g)?n[r]=g:g!==void 0&&d("Unexpected type of ".concat(r,'! Expected "string" or "Element", got ').concat(typeof g))}),n},"argsToParams"),G="swal2-",oe=s(t=>{let n={};for(let r in t)n[t[r]]=G+t[r];return n},"prefix"),p=oe(["container","shown","height-auto","iosfix","popup","modal","no-backdrop","no-transition","toast","toast-shown","show","hide","close","title","html-container","actions","confirm","deny","cancel","default-outline","footer","icon","icon-content","image","input","file","range","select","radio","checkbox","label","textarea","inputerror","input-label","validation-message","progress-steps","active-progress-step","progress-step","progress-step-line","loader","loading","styled","top","top-start","top-end","top-left","top-right","center","center-start","center-end","center-left","center-right","bottom","bottom-start","bottom-end","bottom-left","bottom-right","grow-row","grow-column","grow-fullscreen","rtl","timer-progress-bar","timer-progress-bar-container","scrollbar-measure","icon-success","icon-warning","icon-info","icon-question","icon-error"]),V=oe(["success","warning","info","question","error"]),N=s(()=>document.body.querySelector(".".concat(p.container)),"getContainer"),Re=s(t=>{let n=N();return n?n.querySelector(t):null},"elementBySelector"),W=s(t=>Re(".".concat(t)),"elementByClass"),I=s(()=>W(p.popup),"getPopup"),Ne=s(()=>W(p.icon),"getIcon"),un=s(()=>W(p.title),"getTitle"),ct=s(()=>W(p["html-container"]),"getHtmlContainer"),pn=s(()=>W(p.image),"getImage"),fn=s(()=>W(p["progress-steps"]),"getProgressSteps"),dt=s(()=>W(p["validation-message"]),"getValidationMessage"),J=s(()=>Re(".".concat(p.actions," .").concat(p.confirm)),"getConfirmButton"),se=s(()=>Re(".".concat(p.actions," .").concat(p.deny)),"getDenyButton"),ii=s(()=>W(p["input-label"]),"getInputLabel"),we=s(()=>Re(".".concat(p.loader)),"getLoader"),ue=s(()=>Re(".".concat(p.actions," .").concat(p.cancel)),"getCancelButton"),ut=s(()=>W(p.actions),"getActions"),mn=s(()=>W(p.footer),"getFooter"),pt=s(()=>W(p["timer-progress-bar"]),"getTimerProgressBar"),Bt=s(()=>W(p.close),"getCloseButton"),oi=`
  a[href],
  area[href],
  input:not([disabled]),
  select:not([disabled]),
  textarea:not([disabled]),
  button:not([disabled]),
  iframe,
  object,
  embed,
  [tabindex="0"],
  [contenteditable],
  audio[controls],
  video[controls],
  summary
`,qt=s(()=>{let t=a(I().querySelectorAll('[tabindex]:not([tabindex="-1"]):not([tabindex="0"])')).sort((r,c)=>(r=parseInt(r.getAttribute("tabindex")),c=parseInt(c.getAttribute("tabindex")),r>c?1:r<c?-1:0)),n=a(I().querySelectorAll(oi)).filter(r=>r.getAttribute("tabindex")!=="-1");return i(t.concat(n)).filter(r=>K(r))},"getFocusableElements"),$t=s(()=>!Ue()&&!document.body.classList.contains(p["no-backdrop"]),"isModal"),Ue=s(()=>document.body.classList.contains(p["toast-shown"]),"isToast"),ri=s(()=>I().hasAttribute("data-loading"),"isLoading"),Ee={previousBodyPadding:null},U=s((t,n)=>{if(t.textContent="",n){let c=new DOMParser().parseFromString(n,"text/html");a(c.querySelector("head").childNodes).forEach(g=>{t.appendChild(g)}),a(c.querySelector("body").childNodes).forEach(g=>{t.appendChild(g)})}},"setInnerHtml"),je=s((t,n)=>{if(!n)return!1;let r=n.split(/\s+/);for(let c=0;c<r.length;c++)if(!t.classList.contains(r[c]))return!1;return!0},"hasClass"),si=s((t,n)=>{a(t.classList).forEach(r=>{!Object.values(p).includes(r)&&!Object.values(V).includes(r)&&!Object.values(n.showClass).includes(r)&&t.classList.remove(r)})},"removeCustomClasses"),Q=s((t,n,r)=>{if(si(t,n),n.customClass&&n.customClass[r]){if(typeof n.customClass[r]!="string"&&!n.customClass[r].forEach)return l("Invalid type of customClass.".concat(r,'! Expected string or iterable object, got "').concat(typeof n.customClass[r],'"'));D(t,n.customClass[r])}},"applyCustomClass"),Vt=s((t,n)=>{if(!n)return null;switch(n){case"select":case"textarea":case"file":return ee(t,p[n]);case"checkbox":return t.querySelector(".".concat(p.checkbox," input"));case"radio":return t.querySelector(".".concat(p.radio," input:checked"))||t.querySelector(".".concat(p.radio," input:first-child"));case"range":return t.querySelector(".".concat(p.range," input"));default:return ee(t,p.input)}},"getInput"),gn=s(t=>{if(t.focus(),t.type!=="file"){let n=t.value;t.value="",t.value=n}},"focusInput"),hn=s((t,n,r)=>{!t||!n||(typeof n=="string"&&(n=n.split(/\s+/).filter(Boolean)),n.forEach(c=>{t.forEach?t.forEach(g=>{r?g.classList.add(c):g.classList.remove(c)}):r?t.classList.add(c):t.classList.remove(c)}))},"toggleClass"),D=s((t,n)=>{hn(t,n,!0)},"addClass"),Z=s((t,n)=>{hn(t,n,!1)},"removeClass"),ee=s((t,n)=>{for(let r=0;r<t.childNodes.length;r++)if(je(t.childNodes[r],n))return t.childNodes[r]},"getChildByClass"),Be=s((t,n,r)=>{r==="".concat(parseInt(r))&&(r=parseInt(r)),r||parseInt(r)===0?t.style[n]=typeof r=="number"?"".concat(r,"px"):r:t.style.removeProperty(n)},"applyNumericalStyle"),R=s(function(t){let n=arguments.length>1&&arguments[1]!==void 0?arguments[1]:"flex";t.style.display=n},"show"),j=s(t=>{t.style.display="none"},"hide"),vn=s((t,n,r,c)=>{let g=t.querySelector(n);g&&(g.style[r]=c)},"setStyle"),ft=s((t,n,r)=>{n?R(t,r):j(t)},"toggle"),K=s(t=>!!(t&&(t.offsetWidth||t.offsetHeight||t.getClientRects().length)),"isVisible"),ai=s(()=>!K(J())&&!K(se())&&!K(ue()),"allButtonsAreHidden"),wn=s(t=>t.scrollHeight>t.clientHeight,"isScrollable"),En=s(t=>{let n=window.getComputedStyle(t),r=parseFloat(n.getPropertyValue("animation-duration")||"0"),c=parseFloat(n.getPropertyValue("transition-duration")||"0");return r>0||c>0},"hasCssAnimation"),zt=s(function(t){let n=arguments.length>1&&arguments[1]!==void 0?arguments[1]:!1,r=pt();K(r)&&(n&&(r.style.transition="none",r.style.width="100%"),setTimeout(()=>{r.style.transition="width ".concat(t/1e3,"s linear"),r.style.width="0%"},10))},"animateTimerProgressBar"),li=s(()=>{let t=pt(),n=parseInt(window.getComputedStyle(t).width);t.style.removeProperty("transition"),t.style.width="100%";let r=parseInt(window.getComputedStyle(t).width),c=parseInt(n/r*100);t.style.removeProperty("transition"),t.style.width="".concat(c,"%")},"stopTimerProgressBar"),bn=s(()=>typeof window=="undefined"||typeof document=="undefined","isNodeEnv"),ci=`
 <div aria-labelledby="`.concat(p.title,'" aria-describedby="').concat(p["html-container"],'" class="').concat(p.popup,`" tabindex="-1">
   <button type="button" class="`).concat(p.close,`"></button>
   <ul class="`).concat(p["progress-steps"],`"></ul>
   <div class="`).concat(p.icon,`"></div>
   <img class="`).concat(p.image,`" />
   <h2 class="`).concat(p.title,'" id="').concat(p.title,`"></h2>
   <div class="`).concat(p["html-container"],'" id="').concat(p["html-container"],`"></div>
   <input class="`).concat(p.input,`" />
   <input type="file" class="`).concat(p.file,`" />
   <div class="`).concat(p.range,`">
     <input type="range" />
     <output></output>
   </div>
   <select class="`).concat(p.select,`"></select>
   <div class="`).concat(p.radio,`"></div>
   <label for="`).concat(p.checkbox,'" class="').concat(p.checkbox,`">
     <input type="checkbox" />
     <span class="`).concat(p.label,`"></span>
   </label>
   <textarea class="`).concat(p.textarea,`"></textarea>
   <div class="`).concat(p["validation-message"],'" id="').concat(p["validation-message"],`"></div>
   <div class="`).concat(p.actions,`">
     <div class="`).concat(p.loader,`"></div>
     <button type="button" class="`).concat(p.confirm,`"></button>
     <button type="button" class="`).concat(p.deny,`"></button>
     <button type="button" class="`).concat(p.cancel,`"></button>
   </div>
   <div class="`).concat(p.footer,`"></div>
   <div class="`).concat(p["timer-progress-bar-container"],`">
     <div class="`).concat(p["timer-progress-bar"],`"></div>
   </div>
 </div>
`).replace(/(^|\n)\s*/g,""),di=s(()=>{let t=N();return t?(t.remove(),Z([document.documentElement,document.body],[p["no-backdrop"],p["toast-shown"],p["has-column"]]),!0):!1},"resetOldContainer"),pe=s(()=>{ge.isVisible()&&ge.resetValidationMessage()},"resetValidationMessage"),ui=s(()=>{let t=I(),n=ee(t,p.input),r=ee(t,p.file),c=t.querySelector(".".concat(p.range," input")),g=t.querySelector(".".concat(p.range," output")),x=ee(t,p.select),A=t.querySelector(".".concat(p.checkbox," input")),X=ee(t,p.textarea);n.oninput=pe,r.onchange=pe,x.onchange=pe,A.onchange=pe,X.oninput=pe,c.oninput=()=>{pe(),g.value=c.value},c.onchange=()=>{pe(),c.nextSibling.value=c.value}},"addInputChangeListeners"),pi=s(t=>typeof t=="string"?document.querySelector(t):t,"getTarget"),fi=s(t=>{let n=I();n.setAttribute("role",t.toast?"alert":"dialog"),n.setAttribute("aria-live",t.toast?"polite":"assertive"),t.toast||n.setAttribute("aria-modal","true")},"setupAccessibility"),mi=s(t=>{window.getComputedStyle(t).direction==="rtl"&&D(N(),p.rtl)},"setupRTL"),gi=s(t=>{let n=di();if(bn()){d("SweetAlert2 requires document to initialize");return}let r=document.createElement("div");r.className=p.container,n&&D(r,p["no-transition"]),U(r,ci);let c=pi(t.target);c.appendChild(r),fi(t),mi(c),ui()},"init"),Xt=s((t,n)=>{t instanceof HTMLElement?n.appendChild(t):typeof t=="object"?hi(t,n):t&&U(n,t)},"parseHtmlToContainer"),hi=s((t,n)=>{t.jquery?vi(n,t):U(n,t.toString())},"handleObject"),vi=s((t,n)=>{if(t.textContent="",0 in n)for(let r=0;r in n;r++)t.appendChild(n[r].cloneNode(!0));else t.appendChild(n.cloneNode(!0))},"handleJqueryElem"),qe=(()=>{if(bn())return!1;let t=document.createElement("div"),n={WebkitAnimation:"webkitAnimationEnd",OAnimation:"oAnimationEnd oanimationend",animation:"animationend"};for(let r in n)if(Object.prototype.hasOwnProperty.call(n,r)&&typeof t.style[r]!="undefined")return n[r];return!1})(),wi=s(()=>{let t=document.createElement("div");t.className=p["scrollbar-measure"],document.body.appendChild(t);let n=t.getBoundingClientRect().width-t.clientWidth;return document.body.removeChild(t),n},"measureScrollbar"),Ei=s((t,n)=>{let r=ut(),c=we();!n.showConfirmButton&&!n.showDenyButton&&!n.showCancelButton?j(r):R(r),Q(r,n,"actions"),bi(r,c,n),U(c,n.loaderHtml),Q(c,n,"loader")},"renderActions");function bi(t,n,r){let c=J(),g=se(),x=ue();Wt(c,"confirm",r),Wt(g,"deny",r),Wt(x,"cancel",r),Ci(c,g,x,r),r.reverseButtons&&(r.toast?(t.insertBefore(x,c),t.insertBefore(g,c)):(t.insertBefore(x,n),t.insertBefore(g,n),t.insertBefore(c,n)))}s(bi,"renderButtons");function Ci(t,n,r,c){if(!c.buttonsStyling)return Z([t,n,r],p.styled);D([t,n,r],p.styled),c.confirmButtonColor&&(t.style.backgroundColor=c.confirmButtonColor,D(t,p["default-outline"])),c.denyButtonColor&&(n.style.backgroundColor=c.denyButtonColor,D(n,p["default-outline"])),c.cancelButtonColor&&(r.style.backgroundColor=c.cancelButtonColor,D(r,p["default-outline"]))}s(Ci,"handleButtonsStyling");function Wt(t,n,r){ft(t,r["show".concat(o(n),"Button")],"inline-block"),U(t,r["".concat(n,"ButtonText")]),t.setAttribute("aria-label",r["".concat(n,"ButtonAriaLabel")]),t.className=p[n],Q(t,r,"".concat(n,"Button")),D(t,r["".concat(n,"ButtonClass")])}s(Wt,"renderButton");function yi(t,n){typeof n=="string"?t.style.background=n:n||D([document.documentElement,document.body],p["no-backdrop"])}s(yi,"handleBackdropParam");function Fi(t,n){n in p?D(t,p[n]):(l('The "position" parameter is not valid, defaulting to "center"'),D(t,p.center))}s(Fi,"handlePositionParam");function xi(t,n){if(n&&typeof n=="string"){let r="grow-".concat(n);r in p&&D(t,p[r])}}s(xi,"handleGrowParam");let ki=s((t,n)=>{let r=N();!r||(yi(r,n.backdrop),Fi(r,n.position),xi(r,n.grow),Q(r,n,"container"))},"renderContainer");var L={awaitingPromise:new WeakMap,promise:new WeakMap,innerParams:new WeakMap,domCache:new WeakMap};let Si=["input","file","range","select","radio","checkbox","textarea"],Ti=s((t,n)=>{let r=I(),c=L.innerParams.get(t),g=!c||n.input!==c.input;Si.forEach(x=>{let A=p[x],X=ee(r,A);Ii(x,n.inputAttributes),X.className=A,g&&j(X)}),n.input&&(g&&Li(n),Pi(n))},"renderInput"),Li=s(t=>{if(!z[t.input])return d('Unexpected type of input! Expected "text", "email", "password", "number", "tel", "select", "radio", "checkbox", "textarea", "file" or "url", got "'.concat(t.input,'"'));let n=Cn(t.input),r=z[t.input](n,t);R(r),setTimeout(()=>{gn(r)})},"showInput"),Di=s(t=>{for(let n=0;n<t.attributes.length;n++){let r=t.attributes[n].name;["type","value","style"].includes(r)||t.removeAttribute(r)}},"removeAttributes"),Ii=s((t,n)=>{let r=Vt(I(),t);if(!!r){Di(r);for(let c in n)r.setAttribute(c,n[c])}},"setAttributes"),Pi=s(t=>{let n=Cn(t.input);t.customClass&&D(n,t.customClass.input)},"setCustomClass"),Kt=s((t,n)=>{(!t.placeholder||n.inputPlaceholder)&&(t.placeholder=n.inputPlaceholder)},"setInputPlaceholder"),$e=s((t,n,r)=>{if(r.inputLabel){t.id=p.input;let c=document.createElement("label"),g=p["input-label"];c.setAttribute("for",t.id),c.className=g,D(c,r.customClass.inputLabel),c.innerText=r.inputLabel,n.insertAdjacentElement("beforebegin",c)}},"setInputLabel"),Cn=s(t=>{let n=p[t]?p[t]:p.input;return ee(I(),n)},"getInputContainer"),z={};z.text=z.email=z.password=z.number=z.tel=z.url=(t,n)=>(typeof n.inputValue=="string"||typeof n.inputValue=="number"?t.value=n.inputValue:E(n.inputValue)||l('Unexpected type of inputValue! Expected "string", "number" or "Promise", got "'.concat(typeof n.inputValue,'"')),$e(t,t,n),Kt(t,n),t.type=n.input,t),z.file=(t,n)=>($e(t,t,n),Kt(t,n),t),z.range=(t,n)=>{let r=t.querySelector("input"),c=t.querySelector("output");return r.value=n.inputValue,r.type=n.input,c.value=n.inputValue,$e(r,t,n),t},z.select=(t,n)=>{if(t.textContent="",n.inputPlaceholder){let r=document.createElement("option");U(r,n.inputPlaceholder),r.value="",r.disabled=!0,r.selected=!0,t.appendChild(r)}return $e(t,t,n),t},z.radio=t=>(t.textContent="",t),z.checkbox=(t,n)=>{let r=Vt(I(),"checkbox");r.value=1,r.id=p.checkbox,r.checked=Boolean(n.inputValue);let c=t.querySelector("span");return U(c,n.inputPlaceholder),t},z.textarea=(t,n)=>{t.value=n.inputValue,Kt(t,n),$e(t,t,n);let r=s(c=>parseInt(window.getComputedStyle(c).marginLeft)+parseInt(window.getComputedStyle(c).marginRight),"getMargin");return setTimeout(()=>{if("MutationObserver"in window){let c=parseInt(window.getComputedStyle(I()).width),g=s(()=>{let x=t.offsetWidth+r(t);x>c?I().style.width="".concat(x,"px"):I().style.width=null},"textareaResizeHandler");new MutationObserver(g).observe(t,{attributes:!0,attributeFilter:["style"]})}}),t};let Oi=s((t,n)=>{let r=ct();Q(r,n,"htmlContainer"),n.html?(Xt(n.html,r),R(r,"block")):n.text?(r.textContent=n.text,R(r,"block")):j(r),Ti(t,n)},"renderContent"),_i=s((t,n)=>{let r=mn();ft(r,n.footer),n.footer&&Xt(n.footer,r),Q(r,n,"footer")},"renderFooter"),Mi=s((t,n)=>{let r=Bt();U(r,n.closeButtonHtml),Q(r,n,"closeButton"),ft(r,n.showCloseButton),r.setAttribute("aria-label",n.closeButtonAriaLabel)},"renderCloseButton"),Ai=s((t,n)=>{let r=L.innerParams.get(t),c=Ne();if(r&&n.icon===r.icon){Fn(c,n),yn(c,n);return}if(!n.icon&&!n.iconHtml)return j(c);if(n.icon&&Object.keys(V).indexOf(n.icon)===-1)return d('Unknown icon! Expected "success", "error", "warning", "info" or "question", got "'.concat(n.icon,'"')),j(c);R(c),Fn(c,n),yn(c,n),D(c,n.showClass.icon)},"renderIcon"),yn=s((t,n)=>{for(let r in V)n.icon!==r&&Z(t,V[r]);D(t,V[n.icon]),Ri(t,n),Hi(),Q(t,n,"icon")},"applyStyles"),Hi=s(()=>{let t=I(),n=window.getComputedStyle(t).getPropertyValue("background-color"),r=t.querySelectorAll("[class^=swal2-success-circular-line], .swal2-success-fix");for(let c=0;c<r.length;c++)r[c].style.backgroundColor=n},"adjustSuccessIconBackgoundColor"),Fn=s((t,n)=>{t.textContent="",n.iconHtml?U(t,xn(n.iconHtml)):n.icon==="success"?U(t,`
      <div class="swal2-success-circular-line-left"></div>
      <span class="swal2-success-line-tip"></span> <span class="swal2-success-line-long"></span>
      <div class="swal2-success-ring"></div> <div class="swal2-success-fix"></div>
      <div class="swal2-success-circular-line-right"></div>
    `):n.icon==="error"?U(t,`
      <span class="swal2-x-mark">
        <span class="swal2-x-mark-line-left"></span>
        <span class="swal2-x-mark-line-right"></span>
      </span>
    `):U(t,xn({question:"?",warning:"!",info:"i"}[n.icon]))},"setContent"),Ri=s((t,n)=>{if(!!n.iconColor){t.style.color=n.iconColor,t.style.borderColor=n.iconColor;for(let r of[".swal2-success-line-tip",".swal2-success-line-long",".swal2-x-mark-line-left",".swal2-x-mark-line-right"])vn(t,r,"backgroundColor",n.iconColor);vn(t,".swal2-success-ring","borderColor",n.iconColor)}},"setColor"),xn=s(t=>'<div class="'.concat(p["icon-content"],'">').concat(t,"</div>"),"iconContent"),Ni=s((t,n)=>{let r=pn();if(!n.imageUrl)return j(r);R(r,""),r.setAttribute("src",n.imageUrl),r.setAttribute("alt",n.imageAlt),Be(r,"width",n.imageWidth),Be(r,"height",n.imageHeight),r.className=p.image,Q(r,n,"image")},"renderImage"),Ui=s(t=>{let n=document.createElement("li");return D(n,p["progress-step"]),U(n,t),n},"createStepElement"),ji=s(t=>{let n=document.createElement("li");return D(n,p["progress-step-line"]),t.progressStepsDistance&&(n.style.width=t.progressStepsDistance),n},"createLineElement"),Bi=s((t,n)=>{let r=fn();if(!n.progressSteps||n.progressSteps.length===0)return j(r);R(r),r.textContent="",n.currentProgressStep>=n.progressSteps.length&&l("Invalid currentProgressStep parameter, it should be less than progressSteps.length (currentProgressStep like JS arrays starts from 0)"),n.progressSteps.forEach((c,g)=>{let x=Ui(c);if(r.appendChild(x),g===n.currentProgressStep&&D(x,p["active-progress-step"]),g!==n.progressSteps.length-1){let A=ji(n);r.appendChild(A)}})},"renderProgressSteps"),qi=s((t,n)=>{let r=un();ft(r,n.title||n.titleText,"block"),n.title&&Xt(n.title,r),n.titleText&&(r.innerText=n.titleText),Q(r,n,"title")},"renderTitle"),$i=s((t,n)=>{let r=N(),c=I();n.toast?(Be(r,"width",n.width),c.style.width="100%",c.insertBefore(we(),Ne())):Be(c,"width",n.width),Be(c,"padding",n.padding),n.background&&(c.style.background=n.background),j(dt()),Vi(c,n)},"renderPopup"),Vi=s((t,n)=>{t.className="".concat(p.popup," ").concat(K(t)?n.showClass.popup:""),n.toast?(D([document.documentElement,document.body],p["toast-shown"]),D(t,p.toast)):D(t,p.modal),Q(t,n,"popup"),typeof n.customClass=="string"&&D(t,n.customClass),n.icon&&D(t,p["icon-".concat(n.icon)])},"addClasses"),kn=s((t,n)=>{$i(t,n),ki(t,n),Bi(t,n),Ai(t,n),Ni(t,n),qi(t,n),Mi(t,n),Oi(t,n),Ei(t,n),_i(t,n),typeof n.didRender=="function"&&n.didRender(I())},"render"),zi=s(()=>K(I()),"isVisible$1"),Sn=s(()=>J()&&J().click(),"clickConfirm"),Xi=s(()=>se()&&se().click(),"clickDeny"),Wi=s(()=>ue()&&ue().click(),"clickCancel");function Ki(){let t=this;for(var n=arguments.length,r=new Array(n),c=0;c<n;c++)r[c]=arguments[c];return new t(...r)}s(Ki,"fire");function Yi(t){class n extends this{_main(c,g){return super._main(c,Object.assign({},t,g))}}return s(n,"MixinSwal"),n}s(Yi,"mixin");let be=s(t=>{let n=I();n||ge.fire(),n=I();let r=we();Ue()?j(Ne()):Gi(n,t),R(r),n.setAttribute("data-loading",!0),n.setAttribute("aria-busy",!0),n.focus()},"showLoading"),Gi=s((t,n)=>{let r=ut(),c=we();!n&&K(J())&&(n=J()),R(r),n&&(j(n),c.setAttribute("data-button-to-replace",n.className)),c.parentNode.insertBefore(c,n),D([t,r],p.loading)},"replaceButton"),Ji=100,T={},Qi=s(()=>{T.previousActiveElement&&T.previousActiveElement.focus?(T.previousActiveElement.focus(),T.previousActiveElement=null):document.body&&document.body.focus()},"focusPreviousActiveElement"),Zi=s(t=>new Promise(n=>{if(!t)return n();let r=window.scrollX,c=window.scrollY;T.restoreFocusTimeout=setTimeout(()=>{Qi(),n()},Ji),window.scrollTo(r,c)}),"restoreActiveElement"),eo=s(()=>T.timeout&&T.timeout.getTimerLeft(),"getTimerLeft"),Tn=s(()=>{if(T.timeout)return li(),T.timeout.stop()},"stopTimer"),Ln=s(()=>{if(T.timeout){let t=T.timeout.start();return zt(t),t}},"resumeTimer"),to=s(()=>{let t=T.timeout;return t&&(t.running?Tn():Ln())},"toggleTimer"),no=s(t=>{if(T.timeout){let n=T.timeout.increase(t);return zt(n,!0),n}},"increaseTimer"),io=s(()=>T.timeout&&T.timeout.isRunning(),"isTimerRunning"),Dn=!1,Yt={};function oo(){let t=arguments.length>0&&arguments[0]!==void 0?arguments[0]:"data-swal-template";Yt[t]=this,Dn||(document.body.addEventListener("click",ro),Dn=!0)}s(oo,"bindClickHandler");let ro=s(t=>{for(let n=t.target;n&&n!==document;n=n.parentNode)for(let r in Yt){let c=n.getAttribute(r);if(c){Yt[r].fire({template:c});return}}},"bodyClickListener"),Ce={title:"",titleText:"",text:"",html:"",footer:"",icon:void 0,iconColor:void 0,iconHtml:void 0,template:void 0,toast:!1,showClass:{popup:"swal2-show",backdrop:"swal2-backdrop-show",icon:"swal2-icon-show"},hideClass:{popup:"swal2-hide",backdrop:"swal2-backdrop-hide",icon:"swal2-icon-hide"},customClass:{},target:"body",backdrop:!0,heightAuto:!0,allowOutsideClick:!0,allowEscapeKey:!0,allowEnterKey:!0,stopKeydownPropagation:!0,keydownListenerCapture:!1,showConfirmButton:!0,showDenyButton:!1,showCancelButton:!1,preConfirm:void 0,preDeny:void 0,confirmButtonText:"OK",confirmButtonAriaLabel:"",confirmButtonColor:void 0,denyButtonText:"No",denyButtonAriaLabel:"",denyButtonColor:void 0,cancelButtonText:"Cancel",cancelButtonAriaLabel:"",cancelButtonColor:void 0,buttonsStyling:!0,reverseButtons:!1,focusConfirm:!0,focusDeny:!1,focusCancel:!1,returnFocus:!0,showCloseButton:!1,closeButtonHtml:"&times;",closeButtonAriaLabel:"Close this dialog",loaderHtml:"",showLoaderOnConfirm:!1,showLoaderOnDeny:!1,imageUrl:void 0,imageWidth:void 0,imageHeight:void 0,imageAlt:"",timer:void 0,timerProgressBar:!1,width:void 0,padding:void 0,background:void 0,input:void 0,inputPlaceholder:"",inputLabel:"",inputValue:"",inputOptions:{},inputAutoTrim:!0,inputAttributes:{},inputValidator:void 0,returnInputValueOnDeny:!1,validationMessage:void 0,grow:!1,position:"center",progressSteps:[],currentProgressStep:void 0,progressStepsDistance:void 0,willOpen:void 0,didOpen:void 0,didRender:void 0,willClose:void 0,didClose:void 0,didDestroy:void 0,scrollbarPadding:!0},so=["allowEscapeKey","allowOutsideClick","background","buttonsStyling","cancelButtonAriaLabel","cancelButtonColor","cancelButtonText","closeButtonAriaLabel","closeButtonHtml","confirmButtonAriaLabel","confirmButtonColor","confirmButtonText","currentProgressStep","customClass","denyButtonAriaLabel","denyButtonColor","denyButtonText","didClose","didDestroy","footer","hideClass","html","icon","iconColor","iconHtml","imageAlt","imageHeight","imageUrl","imageWidth","preConfirm","preDeny","progressSteps","returnFocus","reverseButtons","showCancelButton","showCloseButton","showConfirmButton","showDenyButton","text","title","titleText","willClose"],ao={},lo=["allowOutsideClick","allowEnterKey","backdrop","focusConfirm","focusDeny","focusCancel","returnFocus","heightAuto","keydownListenerCapture"],In=s(t=>Object.prototype.hasOwnProperty.call(Ce,t),"isValidParameter"),co=s(t=>so.indexOf(t)!==-1,"isUpdatableParameter"),Gt=s(t=>ao[t],"isDeprecatedParameter"),uo=s(t=>{In(t)||l('Unknown parameter "'.concat(t,'"'))},"checkIfParamIsValid"),po=s(t=>{lo.includes(t)&&l('The parameter "'.concat(t,'" is incompatible with toasts'))},"checkIfToastParamIsValid"),fo=s(t=>{Gt(t)&&v(t,Gt(t))},"checkIfParamIsDeprecated"),mo=s(t=>{!t.backdrop&&t.allowOutsideClick&&l('"allowOutsideClick" parameter requires `backdrop` parameter to be set to `true`');for(let n in t)uo(n),t.toast&&po(n),fo(n)},"showWarningsForParams");var go=Object.freeze({isValidParameter:In,isUpdatableParameter:co,isDeprecatedParameter:Gt,argsToParams:lt,isVisible:zi,clickConfirm:Sn,clickDeny:Xi,clickCancel:Wi,getContainer:N,getPopup:I,getTitle:un,getHtmlContainer:ct,getImage:pn,getIcon:Ne,getInputLabel:ii,getCloseButton:Bt,getActions:ut,getConfirmButton:J,getDenyButton:se,getCancelButton:ue,getLoader:we,getFooter:mn,getTimerProgressBar:pt,getFocusableElements:qt,getValidationMessage:dt,isLoading:ri,fire:Ki,mixin:Yi,showLoading:be,enableLoading:be,getTimerLeft:eo,stopTimer:Tn,resumeTimer:Ln,toggleTimer:to,increaseTimer:no,isTimerRunning:io,bindClickHandler:oo});function Pn(){let t=L.innerParams.get(this);if(!t)return;let n=L.domCache.get(this);j(n.loader),Ue()?t.icon&&R(Ne()):ho(n),Z([n.popup,n.actions],p.loading),n.popup.removeAttribute("aria-busy"),n.popup.removeAttribute("data-loading"),n.confirmButton.disabled=!1,n.denyButton.disabled=!1,n.cancelButton.disabled=!1}s(Pn,"hideLoading");let ho=s(t=>{let n=t.popup.getElementsByClassName(t.loader.getAttribute("data-button-to-replace"));n.length?R(n[0],"inline-block"):ai()&&j(t.actions)},"showRelatedButton");function vo(t){let n=L.innerParams.get(t||this),r=L.domCache.get(t||this);return r?Vt(r.popup,n.input):null}s(vo,"getInput$1");let wo=s(()=>{Ee.previousBodyPadding===null&&document.body.scrollHeight>window.innerHeight&&(Ee.previousBodyPadding=parseInt(window.getComputedStyle(document.body).getPropertyValue("padding-right")),document.body.style.paddingRight="".concat(Ee.previousBodyPadding+wi(),"px"))},"fixScrollbar"),Eo=s(()=>{Ee.previousBodyPadding!==null&&(document.body.style.paddingRight="".concat(Ee.previousBodyPadding,"px"),Ee.previousBodyPadding=null)},"undoScrollbar"),bo=s(()=>{if((/iPad|iPhone|iPod/.test(navigator.userAgent)&&!window.MSStream||navigator.platform==="MacIntel"&&navigator.maxTouchPoints>1)&&!je(document.body,p.iosfix)){let n=document.body.scrollTop;document.body.style.top="".concat(n*-1,"px"),D(document.body,p.iosfix),yo(),Co()}},"iOSfix"),Co=s(()=>{if(!navigator.userAgent.match(/(CriOS|FxiOS|EdgiOS|YaBrowser|UCBrowser)/i)){let n=44;I().scrollHeight>window.innerHeight-n&&(N().style.paddingBottom="".concat(n,"px"))}},"addBottomPaddingForTallPopups"),yo=s(()=>{let t=N(),n;t.ontouchstart=r=>{n=Fo(r)},t.ontouchmove=r=>{n&&(r.preventDefault(),r.stopPropagation())}},"lockBodyScroll"),Fo=s(t=>{let n=t.target,r=N();return xo(t)||ko(t)?!1:n===r||!wn(r)&&n.tagName!=="INPUT"&&n.tagName!=="TEXTAREA"&&!(wn(ct())&&ct().contains(n))},"shouldPreventTouchMove"),xo=s(t=>t.touches&&t.touches.length&&t.touches[0].touchType==="stylus","isStylys"),ko=s(t=>t.touches&&t.touches.length>1,"isZoom"),So=s(()=>{if(je(document.body,p.iosfix)){let t=parseInt(document.body.style.top,10);Z(document.body,p.iosfix),document.body.style.top="",document.body.scrollTop=t*-1}},"undoIOSfix"),To=s(()=>{a(document.body.children).forEach(n=>{n===N()||n.contains(N())||(n.hasAttribute("aria-hidden")&&n.setAttribute("data-previous-aria-hidden",n.getAttribute("aria-hidden")),n.setAttribute("aria-hidden","true"))})},"setAriaHidden"),On=s(()=>{a(document.body.children).forEach(n=>{n.hasAttribute("data-previous-aria-hidden")?(n.setAttribute("aria-hidden",n.getAttribute("data-previous-aria-hidden")),n.removeAttribute("data-previous-aria-hidden")):n.removeAttribute("aria-hidden")})},"unsetAriaHidden");var Ve={swalPromiseResolve:new WeakMap,swalPromiseReject:new WeakMap};function _n(t,n,r,c){Ue()?An(t,c):(Zi(r).then(()=>An(t,c)),T.keydownTarget.removeEventListener("keydown",T.keydownHandler,{capture:T.keydownListenerCapture}),T.keydownHandlerAdded=!1),/^((?!chrome|android).)*safari/i.test(navigator.userAgent)?(n.setAttribute("style","display:none !important"),n.removeAttribute("class"),n.innerHTML=""):n.remove(),$t()&&(Eo(),So(),On()),Lo()}s(_n,"removePopupAndResetState");function Lo(){Z([document.documentElement,document.body],[p.shown,p["height-auto"],p["no-backdrop"],p["toast-shown"]])}s(Lo,"removeBodyClasses");function mt(t){t=Oo(t);let n=Ve.swalPromiseResolve.get(this),r=Io(this);this.isAwaitingPromise()?t.isDismissed||(Mn(this),n(t)):r&&n(t)}s(mt,"close");function Do(){return!!L.awaitingPromise.get(this)}s(Do,"isAwaitingPromise");let Io=s(t=>{let n=I();if(!n)return!1;let r=L.innerParams.get(t);if(!r||je(n,r.hideClass.popup))return!1;Z(n,r.showClass.popup),D(n,r.hideClass.popup);let c=N();return Z(c,r.showClass.backdrop),D(c,r.hideClass.backdrop),_o(t,n,r),!0},"triggerClosePopup");function Po(t){let n=Ve.swalPromiseReject.get(this);Mn(this),n&&n(t)}s(Po,"rejectPromise");let Mn=s(t=>{t.isAwaitingPromise()&&(L.awaitingPromise.delete(t),L.innerParams.get(t)||t._destroy())},"handleAwaitingPromise"),Oo=s(t=>typeof t=="undefined"?{isConfirmed:!1,isDenied:!1,isDismissed:!0}:Object.assign({isConfirmed:!1,isDenied:!1,isDismissed:!1},t),"prepareResolveValue"),_o=s((t,n,r)=>{let c=N(),g=qe&&En(n);typeof r.willClose=="function"&&r.willClose(n),g?Mo(t,n,c,r.returnFocus,r.didClose):_n(t,c,r.returnFocus,r.didClose)},"handlePopupAnimation"),Mo=s((t,n,r,c,g)=>{T.swalCloseEventFinishedCallback=_n.bind(null,t,r,c,g),n.addEventListener(qe,function(x){x.target===n&&(T.swalCloseEventFinishedCallback(),delete T.swalCloseEventFinishedCallback)})},"animatePopup"),An=s((t,n)=>{setTimeout(()=>{typeof n=="function"&&n.bind(t.params)(),t._destroy()})},"triggerDidCloseAndDispose");function Hn(t,n,r){let c=L.domCache.get(t);n.forEach(g=>{c[g].disabled=r})}s(Hn,"setButtonsDisabled");function Rn(t,n){if(!t)return!1;if(t.type==="radio"){let c=t.parentNode.parentNode.querySelectorAll("input");for(let g=0;g<c.length;g++)c[g].disabled=n}else t.disabled=n}s(Rn,"setInputDisabled");function Ao(){Hn(this,["confirmButton","denyButton","cancelButton"],!1)}s(Ao,"enableButtons");function Ho(){Hn(this,["confirmButton","denyButton","cancelButton"],!0)}s(Ho,"disableButtons");function Ro(){return Rn(this.getInput(),!1)}s(Ro,"enableInput");function No(){return Rn(this.getInput(),!0)}s(No,"disableInput");function Uo(t){let n=L.domCache.get(this),r=L.innerParams.get(this);U(n.validationMessage,t),n.validationMessage.className=p["validation-message"],r.customClass&&r.customClass.validationMessage&&D(n.validationMessage,r.customClass.validationMessage),R(n.validationMessage);let c=this.getInput();c&&(c.setAttribute("aria-invalid",!0),c.setAttribute("aria-describedby",p["validation-message"]),gn(c),D(c,p.inputerror))}s(Uo,"showValidationMessage");function jo(){let t=L.domCache.get(this);t.validationMessage&&j(t.validationMessage);let n=this.getInput();n&&(n.removeAttribute("aria-invalid"),n.removeAttribute("aria-describedby"),Z(n,p.inputerror))}s(jo,"resetValidationMessage$1");function Bo(){return L.domCache.get(this).progressSteps}s(Bo,"getProgressSteps$1");class Nn{constructor(n,r){this.callback=n,this.remaining=r,this.running=!1,this.start()}start(){return this.running||(this.running=!0,this.started=new Date,this.id=setTimeout(this.callback,this.remaining)),this.remaining}stop(){return this.running&&(this.running=!1,clearTimeout(this.id),this.remaining-=new Date-this.started),this.remaining}increase(n){let r=this.running;return r&&this.stop(),this.remaining+=n,r&&this.start(),this.remaining}getTimerLeft(){return this.running&&(this.stop(),this.start()),this.remaining}isRunning(){return this.running}}s(Nn,"Timer");var Un={email:(t,n)=>/^[a-zA-Z0-9.+_-]+@[a-zA-Z0-9.-]+\.[a-zA-Z0-9-]{2,24}$/.test(t)?Promise.resolve():Promise.resolve(n||"Invalid email address"),url:(t,n)=>/^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-z]{2,63}\b([-a-zA-Z0-9@:%_+.~#?&/=]*)$/.test(t)?Promise.resolve():Promise.resolve(n||"Invalid URL")};function qo(t){t.inputValidator||Object.keys(Un).forEach(n=>{t.input===n&&(t.inputValidator=Un[n])})}s(qo,"setDefaultInputValidators");function $o(t){(!t.target||typeof t.target=="string"&&!document.querySelector(t.target)||typeof t.target!="string"&&!t.target.appendChild)&&(l('Target parameter is not valid, defaulting to "body"'),t.target="body")}s($o,"validateCustomTargetElement");function Vo(t){qo(t),t.showLoaderOnConfirm&&!t.preConfirm&&l(`showLoaderOnConfirm is set to true, but preConfirm is not defined.
showLoaderOnConfirm should be used together with preConfirm, see usage example:
https://sweetalert2.github.io/#ajax-request`),$o(t),typeof t.title=="string"&&(t.title=t.title.split(`
`).join("<br />")),gi(t)}s(Vo,"setParameters");let jn=["swal-title","swal-html","swal-footer"],zo=s(t=>{let n=typeof t.template=="string"?document.querySelector(t.template):t.template;if(!n)return{};let r=n.content;return Qo(r),Object.assign(Xo(r),Wo(r),Ko(r),Yo(r),Go(r),Jo(r,jn))},"getTemplateParams"),Xo=s(t=>{let n={};return a(t.querySelectorAll("swal-param")).forEach(r=>{fe(r,["name","value"]);let c=r.getAttribute("name"),g=r.getAttribute("value");typeof Ce[c]=="boolean"&&g==="false"&&(g=!1),typeof Ce[c]=="object"&&(g=JSON.parse(g)),n[c]=g}),n},"getSwalParams"),Wo=s(t=>{let n={};return a(t.querySelectorAll("swal-button")).forEach(r=>{fe(r,["type","color","aria-label"]);let c=r.getAttribute("type");n["".concat(c,"ButtonText")]=r.innerHTML,n["show".concat(o(c),"Button")]=!0,r.hasAttribute("color")&&(n["".concat(c,"ButtonColor")]=r.getAttribute("color")),r.hasAttribute("aria-label")&&(n["".concat(c,"ButtonAriaLabel")]=r.getAttribute("aria-label"))}),n},"getSwalButtons"),Ko=s(t=>{let n={},r=t.querySelector("swal-image");return r&&(fe(r,["src","width","height","alt"]),r.hasAttribute("src")&&(n.imageUrl=r.getAttribute("src")),r.hasAttribute("width")&&(n.imageWidth=r.getAttribute("width")),r.hasAttribute("height")&&(n.imageHeight=r.getAttribute("height")),r.hasAttribute("alt")&&(n.imageAlt=r.getAttribute("alt"))),n},"getSwalImage"),Yo=s(t=>{let n={},r=t.querySelector("swal-icon");return r&&(fe(r,["type","color"]),r.hasAttribute("type")&&(n.icon=r.getAttribute("type")),r.hasAttribute("color")&&(n.iconColor=r.getAttribute("color")),n.iconHtml=r.innerHTML),n},"getSwalIcon"),Go=s(t=>{let n={},r=t.querySelector("swal-input");r&&(fe(r,["type","label","placeholder","value"]),n.input=r.getAttribute("type")||"text",r.hasAttribute("label")&&(n.inputLabel=r.getAttribute("label")),r.hasAttribute("placeholder")&&(n.inputPlaceholder=r.getAttribute("placeholder")),r.hasAttribute("value")&&(n.inputValue=r.getAttribute("value")));let c=t.querySelectorAll("swal-input-option");return c.length&&(n.inputOptions={},a(c).forEach(g=>{fe(g,["value"]);let x=g.getAttribute("value"),A=g.innerHTML;n.inputOptions[x]=A})),n},"getSwalInput"),Jo=s((t,n)=>{let r={};for(let c in n){let g=n[c],x=t.querySelector(g);x&&(fe(x,[]),r[g.replace(/^swal-/,"")]=x.innerHTML.trim())}return r},"getSwalStringParams"),Qo=s(t=>{let n=jn.concat(["swal-param","swal-button","swal-image","swal-icon","swal-input","swal-input-option"]);a(t.children).forEach(r=>{let c=r.tagName.toLowerCase();n.indexOf(c)===-1&&l("Unrecognized element <".concat(c,">"))})},"showWarningsForElements"),fe=s((t,n)=>{a(t.attributes).forEach(r=>{n.indexOf(r.name)===-1&&l(['Unrecognized attribute "'.concat(r.name,'" on <').concat(t.tagName.toLowerCase(),">."),"".concat(n.length?"Allowed attributes are: ".concat(n.join(", ")):"To set the value, use HTML within the element.")])})},"showWarningsForAttributes"),Bn=10,Zo=s(t=>{let n=N(),r=I();typeof t.willOpen=="function"&&t.willOpen(r);let g=window.getComputedStyle(document.body).overflowY;nr(n,r,t),setTimeout(()=>{er(n,r)},Bn),$t()&&(tr(n,t.scrollbarPadding,g),To()),!Ue()&&!T.previousActiveElement&&(T.previousActiveElement=document.activeElement),typeof t.didOpen=="function"&&setTimeout(()=>t.didOpen(r)),Z(n,p["no-transition"])},"openPopup"),qn=s(t=>{let n=I();if(t.target!==n)return;let r=N();n.removeEventListener(qe,qn),r.style.overflowY="auto"},"swalOpenAnimationFinished"),er=s((t,n)=>{qe&&En(n)?(t.style.overflowY="hidden",n.addEventListener(qe,qn)):t.style.overflowY="auto"},"setScrollingVisibility"),tr=s((t,n,r)=>{bo(),n&&r!=="hidden"&&wo(),setTimeout(()=>{t.scrollTop=0})},"fixScrollContainer"),nr=s((t,n,r)=>{D(t,r.showClass.backdrop),n.style.setProperty("opacity","0","important"),R(n,"grid"),setTimeout(()=>{D(n,r.showClass.popup),n.style.removeProperty("opacity")},Bn),D([document.documentElement,document.body],p.shown),r.heightAuto&&r.backdrop&&!r.toast&&D([document.documentElement,document.body],p["height-auto"])},"addClasses$1"),ir=s((t,n)=>{n.input==="select"||n.input==="radio"?lr(t,n):["text","email","number","tel","textarea"].includes(n.input)&&(y(n.inputValue)||E(n.inputValue))&&(be(J()),cr(t,n))},"handleInputOptionsAndValue"),or=s((t,n)=>{let r=t.getInput();if(!r)return null;switch(n.input){case"checkbox":return rr(r);case"radio":return sr(r);case"file":return ar(r);default:return n.inputAutoTrim?r.value.trim():r.value}},"getInputValue"),rr=s(t=>t.checked?1:0,"getCheckboxValue"),sr=s(t=>t.checked?t.value:null,"getRadioValue"),ar=s(t=>t.files.length?t.getAttribute("multiple")!==null?t.files:t.files[0]:null,"getFileValue"),lr=s((t,n)=>{let r=I(),c=s(g=>dr[n.input](r,Jt(g),n),"processInputOptions");y(n.inputOptions)||E(n.inputOptions)?(be(J()),b(n.inputOptions).then(g=>{t.hideLoading(),c(g)})):typeof n.inputOptions=="object"?c(n.inputOptions):d("Unexpected type of inputOptions! Expected object, Map or Promise, got ".concat(typeof n.inputOptions))},"handleInputOptions"),cr=s((t,n)=>{let r=t.getInput();j(r),b(n.inputValue).then(c=>{r.value=n.input==="number"?parseFloat(c)||0:"".concat(c),R(r),r.focus(),t.hideLoading()}).catch(c=>{d("Error in inputValue promise: ".concat(c)),r.value="",R(r),r.focus(),t.hideLoading()})},"handleInputValue"),dr={select:(t,n,r)=>{let c=ee(t,p.select),g=s((x,A,X)=>{let q=document.createElement("option");q.value=X,U(q,A),q.selected=$n(X,r.inputValue),x.appendChild(q)},"renderOption");n.forEach(x=>{let A=x[0],X=x[1];if(Array.isArray(X)){let q=document.createElement("optgroup");q.label=A,q.disabled=!1,c.appendChild(q),X.forEach(ye=>g(q,ye[1],ye[0]))}else g(c,X,A)}),c.focus()},radio:(t,n,r)=>{let c=ee(t,p.radio);n.forEach(x=>{let A=x[0],X=x[1],q=document.createElement("input"),ye=document.createElement("label");q.type="radio",q.name=p.radio,q.value=A,$n(A,r.inputValue)&&(q.checked=!0);let on=document.createElement("span");U(on,X),on.className=p.label,ye.appendChild(q),ye.appendChild(on),c.appendChild(ye)});let g=c.querySelectorAll("input");g.length&&g[0].focus()}},Jt=s(t=>{let n=[];return typeof Map!="undefined"&&t instanceof Map?t.forEach((r,c)=>{let g=r;typeof g=="object"&&(g=Jt(g)),n.push([c,g])}):Object.keys(t).forEach(r=>{let c=t[r];typeof c=="object"&&(c=Jt(c)),n.push([r,c])}),n},"formatInputOptions"),$n=s((t,n)=>n&&n.toString()===t.toString(),"isSelected"),ur=s(t=>{let n=L.innerParams.get(t);t.disableButtons(),n.input?Vn(t,"confirm"):Zt(t,!0)},"handleConfirmButtonClick"),pr=s(t=>{let n=L.innerParams.get(t);t.disableButtons(),n.returnInputValueOnDeny?Vn(t,"deny"):Qt(t,!1)},"handleDenyButtonClick"),fr=s((t,n)=>{t.disableButtons(),n(u.cancel)},"handleCancelButtonClick"),Vn=s((t,n)=>{let r=L.innerParams.get(t),c=or(t,r);r.inputValidator?mr(t,c,n):t.getInput().checkValidity()?n==="deny"?Qt(t,c):Zt(t,c):(t.enableButtons(),t.showValidationMessage(r.validationMessage))},"handleConfirmOrDenyWithInput"),mr=s((t,n,r)=>{let c=L.innerParams.get(t);t.disableInput(),Promise.resolve().then(()=>b(c.inputValidator(n,c.validationMessage))).then(x=>{t.enableButtons(),t.enableInput(),x?t.showValidationMessage(x):r==="deny"?Qt(t,n):Zt(t,n)})},"handleInputValidator"),Qt=s((t,n)=>{let r=L.innerParams.get(t||void 0);r.showLoaderOnDeny&&be(se()),r.preDeny?(L.awaitingPromise.set(t||void 0,!0),Promise.resolve().then(()=>b(r.preDeny(n,r.validationMessage))).then(g=>{g===!1?t.hideLoading():t.closePopup({isDenied:!0,value:typeof g=="undefined"?n:g})}).catch(g=>Xn(t||void 0,g))):t.closePopup({isDenied:!0,value:n})},"deny"),zn=s((t,n)=>{t.closePopup({isConfirmed:!0,value:n})},"succeedWith"),Xn=s((t,n)=>{t.rejectPromise(n)},"rejectWith"),Zt=s((t,n)=>{let r=L.innerParams.get(t||void 0);r.showLoaderOnConfirm&&be(),r.preConfirm?(t.resetValidationMessage(),L.awaitingPromise.set(t||void 0,!0),Promise.resolve().then(()=>b(r.preConfirm(n,r.validationMessage))).then(g=>{K(dt())||g===!1?t.hideLoading():zn(t,typeof g=="undefined"?n:g)}).catch(g=>Xn(t||void 0,g))):zn(t,n)},"confirm"),gr=s((t,n,r,c)=>{n.keydownTarget&&n.keydownHandlerAdded&&(n.keydownTarget.removeEventListener("keydown",n.keydownHandler,{capture:n.keydownListenerCapture}),n.keydownHandlerAdded=!1),r.toast||(n.keydownHandler=g=>vr(t,g,c),n.keydownTarget=r.keydownListenerCapture?window:I(),n.keydownListenerCapture=r.keydownListenerCapture,n.keydownTarget.addEventListener("keydown",n.keydownHandler,{capture:n.keydownListenerCapture}),n.keydownHandlerAdded=!0)},"addKeydownHandler"),en=s((t,n,r)=>{let c=qt();if(c.length)return n=n+r,n===c.length?n=0:n===-1&&(n=c.length-1),c[n].focus();I().focus()},"setFocus"),Wn=["ArrowRight","ArrowDown"],hr=["ArrowLeft","ArrowUp"],vr=s((t,n,r)=>{let c=L.innerParams.get(t);!c||(c.stopKeydownPropagation&&n.stopPropagation(),n.key==="Enter"?wr(t,n,c):n.key==="Tab"?Er(n,c):[...Wn,...hr].includes(n.key)?br(n.key):n.key==="Escape"&&Cr(n,c,r))},"keydownHandler"),wr=s((t,n,r)=>{if(!n.isComposing&&n.target&&t.getInput()&&n.target.outerHTML===t.getInput().outerHTML){if(["textarea","file"].includes(r.input))return;Sn(),n.preventDefault()}},"handleEnter"),Er=s((t,n)=>{let r=t.target,c=qt(),g=-1;for(let x=0;x<c.length;x++)if(r===c[x]){g=x;break}t.shiftKey?en(n,g,-1):en(n,g,1),t.stopPropagation(),t.preventDefault()},"handleTab"),br=s(t=>{let n=J(),r=se(),c=ue();if(![n,r,c].includes(document.activeElement))return;let g=Wn.includes(t)?"nextElementSibling":"previousElementSibling",x=document.activeElement[g];x&&x.focus()},"handleArrows"),Cr=s((t,n,r)=>{h(n.allowEscapeKey)&&(t.preventDefault(),r(u.esc))},"handleEsc"),yr=s((t,n,r)=>{L.innerParams.get(t).toast?Fr(t,n,r):(xr(n),kr(n),Sr(t,n,r))},"handlePopupClick"),Fr=s((t,n,r)=>{n.popup.onclick=()=>{let c=L.innerParams.get(t);c.showConfirmButton||c.showDenyButton||c.showCancelButton||c.showCloseButton||c.timer||c.input||r(u.close)}},"handleToastClick"),gt=!1,xr=s(t=>{t.popup.onmousedown=()=>{t.container.onmouseup=function(n){t.container.onmouseup=void 0,n.target===t.container&&(gt=!0)}}},"handleModalMousedown"),kr=s(t=>{t.container.onmousedown=()=>{t.popup.onmouseup=function(n){t.popup.onmouseup=void 0,(n.target===t.popup||t.popup.contains(n.target))&&(gt=!0)}}},"handleContainerMousedown"),Sr=s((t,n,r)=>{n.container.onclick=c=>{let g=L.innerParams.get(t);if(gt){gt=!1;return}c.target===n.container&&h(g.allowOutsideClick)&&r(u.backdrop)}},"handleModalClick");function Tr(t){let n=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{};mo(Object.assign({},n,t)),T.currentInstance&&(T.currentInstance._destroy(),$t()&&On()),T.currentInstance=this;let r=Lr(t,n);Vo(r),Object.freeze(r),T.timeout&&(T.timeout.stop(),delete T.timeout),clearTimeout(T.restoreFocusTimeout);let c=Ir(this);return kn(this,r),L.innerParams.set(this,r),Dr(this,c,r)}s(Tr,"_main");let Lr=s((t,n)=>{let r=zo(t),c=Object.assign({},Ce,n,r,t);return c.showClass=Object.assign({},Ce.showClass,c.showClass),c.hideClass=Object.assign({},Ce.hideClass,c.hideClass),c},"prepareParams"),Dr=s((t,n,r)=>new Promise((c,g)=>{let x=s(A=>{t.closePopup({isDismissed:!0,dismiss:A})},"dismissWith");Ve.swalPromiseResolve.set(t,c),Ve.swalPromiseReject.set(t,g),n.confirmButton.onclick=()=>ur(t),n.denyButton.onclick=()=>pr(t),n.cancelButton.onclick=()=>fr(t,x),n.closeButton.onclick=()=>x(u.close),yr(t,n,x),gr(t,T,r,x),ir(t,r),Zo(r),Pr(T,r,x),Or(n,r),setTimeout(()=>{n.container.scrollTop=0})}),"swalPromise"),Ir=s(t=>{let n={popup:I(),container:N(),actions:ut(),confirmButton:J(),denyButton:se(),cancelButton:ue(),loader:we(),closeButton:Bt(),validationMessage:dt(),progressSteps:fn()};return L.domCache.set(t,n),n},"populateDomCache"),Pr=s((t,n,r)=>{let c=pt();j(c),n.timer&&(t.timeout=new Nn(()=>{r("timer"),delete t.timeout},n.timer),n.timerProgressBar&&(R(c),setTimeout(()=>{t.timeout&&t.timeout.running&&zt(n.timer)})))},"setupTimer"),Or=s((t,n)=>{if(!n.toast){if(!h(n.allowEnterKey))return Mr();_r(t,n)||en(n,-1,1)}},"initFocus"),_r=s((t,n)=>n.focusDeny&&K(t.denyButton)?(t.denyButton.focus(),!0):n.focusCancel&&K(t.cancelButton)?(t.cancelButton.focus(),!0):n.focusConfirm&&K(t.confirmButton)?(t.confirmButton.focus(),!0):!1,"focusButton"),Mr=s(()=>{document.activeElement&&typeof document.activeElement.blur=="function"&&document.activeElement.blur()},"blurActiveElement");function Ar(t){let n=I(),r=L.innerParams.get(this);if(!n||je(n,r.hideClass.popup))return l("You're trying to update the closed or closing popup, that won't work. Use the update() method in preConfirm parameter or show a new popup.");let c={};Object.keys(t).forEach(x=>{ge.isUpdatableParameter(x)?c[x]=t[x]:l('Invalid parameter to update: "'.concat(x,`". Updatable params are listed here: https://github.com/sweetalert2/sweetalert2/blob/master/src/utils/params.js

If you think this parameter should be updatable, request it here: https://github.com/sweetalert2/sweetalert2/issues/new?template=02_feature_request.md`))});let g=Object.assign({},r,c);kn(this,g),L.innerParams.set(this,g),Object.defineProperties(this,{params:{value:Object.assign({},this.params,t),writable:!1,enumerable:!0}})}s(Ar,"update");function Hr(){let t=L.domCache.get(this),n=L.innerParams.get(this);if(!n){Kn(this);return}t.popup&&T.swalCloseEventFinishedCallback&&(T.swalCloseEventFinishedCallback(),delete T.swalCloseEventFinishedCallback),T.deferDisposalTimer&&(clearTimeout(T.deferDisposalTimer),delete T.deferDisposalTimer),typeof n.didDestroy=="function"&&n.didDestroy(),Rr(this)}s(Hr,"_destroy");let Rr=s(t=>{Kn(t),delete t.params,delete T.keydownHandler,delete T.keydownTarget,delete T.currentInstance},"disposeSwal"),Kn=s(t=>{t.isAwaitingPromise()?(tn(L,t),L.awaitingPromise.set(t,!0)):(tn(Ve,t),tn(L,t))},"disposeWeakMaps"),tn=s((t,n)=>{for(let r in t)t[r].delete(n)},"unsetWeakMaps");var Yn=Object.freeze({hideLoading:Pn,disableLoading:Pn,getInput:vo,close:mt,isAwaitingPromise:Do,rejectPromise:Po,closePopup:mt,closeModal:mt,closeToast:mt,enableButtons:Ao,disableButtons:Ho,enableInput:Ro,disableInput:No,showValidationMessage:Uo,resetValidationMessage:jo,getProgressSteps:Bo,_main:Tr,update:Ar,_destroy:Hr});let nn;class me{constructor(){if(typeof window=="undefined")return;nn=this;for(var n=arguments.length,r=new Array(n),c=0;c<n;c++)r[c]=arguments[c];let g=Object.freeze(this.constructor.argsToParams(r));Object.defineProperties(this,{params:{value:g,writable:!1,enumerable:!0,configurable:!0}});let x=this._main(this.params);L.promise.set(this,x)}then(n){return L.promise.get(this).then(n)}finally(n){return L.promise.get(this).finally(n)}}s(me,"SweetAlert"),Object.assign(me.prototype,Yn),Object.assign(me,go),Object.keys(Yn).forEach(t=>{me[t]=function(){if(nn)return nn[t](...arguments)}}),me.DismissReason=u,me.version="11.1.10";let ge=me;return ge.default=ge,ge});typeof te!="undefined"&&te.Sweetalert2&&(te.swal=te.sweetAlert=te.Swal=te.SweetAlert=te.Sweetalert2);typeof document!="undefined"&&function(u,e){var i=u.createElement("style");if(u.getElementsByTagName("head")[0].appendChild(i),i.styleSheet)i.styleSheet.disabled||(i.styleSheet.cssText=e);else try{i.innerHTML=e}catch(o){i.innerText=e}}(document,'.swal2-popup.swal2-toast{box-sizing:border-box;grid-column:1/4!important;grid-row:1/4!important;grid-template-columns:1fr 99fr 1fr;padding:1em;overflow-y:hidden;background:#fff;box-shadow:0 0 1px rgba(0,0,0,.075),0 1px 2px rgba(0,0,0,.075),1px 2px 4px rgba(0,0,0,.075),1px 3px 8px rgba(0,0,0,.075),2px 4px 16px rgba(0,0,0,.075);pointer-events:all}.swal2-popup.swal2-toast>*{grid-column:2}.swal2-popup.swal2-toast .swal2-title{margin:.5em 1em;padding:0;font-size:1em;text-align:initial}.swal2-popup.swal2-toast .swal2-loading{justify-content:center}.swal2-popup.swal2-toast .swal2-input{height:2em;margin:.5em;font-size:1em}.swal2-popup.swal2-toast .swal2-validation-message{font-size:1em}.swal2-popup.swal2-toast .swal2-footer{margin:.5em 0 0;padding:.5em 0 0;font-size:.8em}.swal2-popup.swal2-toast .swal2-close{grid-column:3/3;grid-row:1/99;align-self:center;width:.8em;height:.8em;margin:0;font-size:2em}.swal2-popup.swal2-toast .swal2-html-container{margin:.5em 1em;padding:0;font-size:1em;text-align:initial}.swal2-popup.swal2-toast .swal2-html-container:empty{padding:0}.swal2-popup.swal2-toast .swal2-loader{grid-column:1;grid-row:1/99;align-self:center;width:2em;height:2em;margin:.25em}.swal2-popup.swal2-toast .swal2-icon{grid-column:1;grid-row:1/99;align-self:center;width:2em;min-width:2em;height:2em;margin:0 .5em 0 0}.swal2-popup.swal2-toast .swal2-icon .swal2-icon-content{display:flex;align-items:center;font-size:1.8em;font-weight:700}.swal2-popup.swal2-toast .swal2-icon.swal2-success .swal2-success-ring{width:2em;height:2em}.swal2-popup.swal2-toast .swal2-icon.swal2-error [class^=swal2-x-mark-line]{top:.875em;width:1.375em}.swal2-popup.swal2-toast .swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=left]{left:.3125em}.swal2-popup.swal2-toast .swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=right]{right:.3125em}.swal2-popup.swal2-toast .swal2-actions{justify-content:flex-start;height:auto;margin:0;margin-top:.5em;padding:0 .5em}.swal2-popup.swal2-toast .swal2-styled{margin:.25em .5em;padding:.4em .6em;font-size:1em}.swal2-popup.swal2-toast .swal2-success{border-color:#a5dc86}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-circular-line]{position:absolute;width:1.6em;height:3em;transform:rotate(45deg);border-radius:50%}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-circular-line][class$=left]{top:-.8em;left:-.5em;transform:rotate(-45deg);transform-origin:2em 2em;border-radius:4em 0 0 4em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-circular-line][class$=right]{top:-.25em;left:.9375em;transform-origin:0 1.5em;border-radius:0 4em 4em 0}.swal2-popup.swal2-toast .swal2-success .swal2-success-ring{width:2em;height:2em}.swal2-popup.swal2-toast .swal2-success .swal2-success-fix{top:0;left:.4375em;width:.4375em;height:2.6875em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-line]{height:.3125em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-line][class$=tip]{top:1.125em;left:.1875em;width:.75em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-line][class$=long]{top:.9375em;right:.1875em;width:1.375em}.swal2-popup.swal2-toast .swal2-success.swal2-icon-show .swal2-success-line-tip{-webkit-animation:swal2-toast-animate-success-line-tip .75s;animation:swal2-toast-animate-success-line-tip .75s}.swal2-popup.swal2-toast .swal2-success.swal2-icon-show .swal2-success-line-long{-webkit-animation:swal2-toast-animate-success-line-long .75s;animation:swal2-toast-animate-success-line-long .75s}.swal2-popup.swal2-toast.swal2-show{-webkit-animation:swal2-toast-show .5s;animation:swal2-toast-show .5s}.swal2-popup.swal2-toast.swal2-hide{-webkit-animation:swal2-toast-hide .1s forwards;animation:swal2-toast-hide .1s forwards}.swal2-container{display:grid;position:fixed;z-index:1060;top:0;right:0;bottom:0;left:0;box-sizing:border-box;grid-template-areas:"top-start     top            top-end" "center-start  center         center-end" "bottom-start  bottom-center  bottom-end";grid-template-rows:minmax(-webkit-min-content,auto) minmax(-webkit-min-content,auto) minmax(-webkit-min-content,auto);grid-template-rows:minmax(min-content,auto) minmax(min-content,auto) minmax(min-content,auto);height:100%;padding:.625em;overflow-x:hidden;transition:background-color .1s;-webkit-overflow-scrolling:touch}.swal2-container.swal2-backdrop-show,.swal2-container.swal2-noanimation{background:rgba(0,0,0,.4)}.swal2-container.swal2-backdrop-hide{background:0 0!important}.swal2-container.swal2-bottom-start,.swal2-container.swal2-center-start,.swal2-container.swal2-top-start{grid-template-columns:minmax(0,1fr) auto auto}.swal2-container.swal2-bottom,.swal2-container.swal2-center,.swal2-container.swal2-top{grid-template-columns:auto minmax(0,1fr) auto}.swal2-container.swal2-bottom-end,.swal2-container.swal2-center-end,.swal2-container.swal2-top-end{grid-template-columns:auto auto minmax(0,1fr)}.swal2-container.swal2-top-start>.swal2-popup{align-self:start}.swal2-container.swal2-top>.swal2-popup{grid-column:2;align-self:start;justify-self:center}.swal2-container.swal2-top-end>.swal2-popup,.swal2-container.swal2-top-right>.swal2-popup{grid-column:3;align-self:start;justify-self:end}.swal2-container.swal2-center-left>.swal2-popup,.swal2-container.swal2-center-start>.swal2-popup{grid-row:2;align-self:center}.swal2-container.swal2-center>.swal2-popup{grid-column:2;grid-row:2;align-self:center;justify-self:center}.swal2-container.swal2-center-end>.swal2-popup,.swal2-container.swal2-center-right>.swal2-popup{grid-column:3;grid-row:2;align-self:center;justify-self:end}.swal2-container.swal2-bottom-left>.swal2-popup,.swal2-container.swal2-bottom-start>.swal2-popup{grid-column:1;grid-row:3;align-self:end}.swal2-container.swal2-bottom>.swal2-popup{grid-column:2;grid-row:3;justify-self:center;align-self:end}.swal2-container.swal2-bottom-end>.swal2-popup,.swal2-container.swal2-bottom-right>.swal2-popup{grid-column:3;grid-row:3;align-self:end;justify-self:end}.swal2-container.swal2-grow-fullscreen>.swal2-popup,.swal2-container.swal2-grow-row>.swal2-popup{grid-column:1/4;width:100%}.swal2-container.swal2-grow-column>.swal2-popup,.swal2-container.swal2-grow-fullscreen>.swal2-popup{grid-row:1/4;align-self:stretch}.swal2-container.swal2-no-transition{transition:none!important}.swal2-popup{display:none;position:relative;box-sizing:border-box;grid-template-columns:minmax(0,100%);width:32em;max-width:100%;padding:0 0 1.25em;border:none;border-radius:5px;background:#fff;color:#545454;font-family:inherit;font-size:1rem}.swal2-popup:focus{outline:0}.swal2-popup.swal2-loading{overflow-y:hidden}.swal2-title{position:relative;max-width:100%;margin:0;padding:.8em 1em 0;color:#595959;font-size:1.875em;font-weight:600;text-align:center;text-transform:none;word-wrap:break-word}.swal2-actions{display:flex;z-index:1;box-sizing:border-box;flex-wrap:wrap;align-items:center;justify-content:center;width:auto;margin:1.25em auto 0;padding:0}.swal2-actions:not(.swal2-loading) .swal2-styled[disabled]{opacity:.4}.swal2-actions:not(.swal2-loading) .swal2-styled:hover{background-image:linear-gradient(rgba(0,0,0,.1),rgba(0,0,0,.1))}.swal2-actions:not(.swal2-loading) .swal2-styled:active{background-image:linear-gradient(rgba(0,0,0,.2),rgba(0,0,0,.2))}.swal2-loader{display:none;align-items:center;justify-content:center;width:2.2em;height:2.2em;margin:0 1.875em;-webkit-animation:swal2-rotate-loading 1.5s linear 0s infinite normal;animation:swal2-rotate-loading 1.5s linear 0s infinite normal;border-width:.25em;border-style:solid;border-radius:100%;border-color:#2778c4 transparent #2778c4 transparent}.swal2-styled{margin:.3125em;padding:.625em 1.1em;transition:box-shadow .1s;box-shadow:0 0 0 3px transparent;font-weight:500}.swal2-styled:not([disabled]){cursor:pointer}.swal2-styled.swal2-confirm{border:0;border-radius:.25em;background:initial;background-color:#7367f0;color:#fff;font-size:1em}.swal2-styled.swal2-confirm:focus{box-shadow:0 0 0 3px rgba(115,103,240,.5)}.swal2-styled.swal2-deny{border:0;border-radius:.25em;background:initial;background-color:#ea5455;color:#fff;font-size:1em}.swal2-styled.swal2-deny:focus{box-shadow:0 0 0 3px rgba(234,84,85,.5)}.swal2-styled.swal2-cancel{border:0;border-radius:.25em;background:initial;background-color:#6e7d88;color:#fff;font-size:1em}.swal2-styled.swal2-cancel:focus{box-shadow:0 0 0 3px rgba(110,125,136,.5)}.swal2-styled.swal2-default-outline:focus{box-shadow:0 0 0 3px rgba(100,150,200,.5)}.swal2-styled:focus{outline:0}.swal2-styled::-moz-focus-inner{border:0}.swal2-footer{justify-content:center;margin:1em 0 0;padding:1em 1em 0;border-top:1px solid #eee;color:#545454;font-size:1em}.swal2-timer-progress-bar-container{position:absolute;right:0;bottom:0;left:0;grid-column:auto!important;height:.25em;overflow:hidden;border-bottom-right-radius:5px;border-bottom-left-radius:5px}.swal2-timer-progress-bar{width:100%;height:.25em;background:rgba(0,0,0,.2)}.swal2-image{max-width:100%;margin:2em auto 1em}.swal2-close{z-index:2;align-items:center;justify-content:center;width:1.2em;height:1.2em;margin-top:0;margin-right:0;margin-bottom:-1.2em;padding:0;overflow:hidden;transition:color .1s,box-shadow .1s;border:none;border-radius:5px;background:0 0;color:#ccc;font-family:serif;font-family:monospace;font-size:2.5em;cursor:pointer;justify-self:end}.swal2-close:hover{transform:none;background:0 0;color:#f27474}.swal2-close:focus{outline:0;box-shadow:inset 0 0 0 3px rgba(100,150,200,.5)}.swal2-close::-moz-focus-inner{border:0}.swal2-html-container{z-index:1;justify-content:center;margin:1em 1.6em .3em;padding:0;overflow:auto;color:#545454;font-size:1.125em;font-weight:400;line-height:normal;text-align:center;word-wrap:break-word;word-break:break-word}.swal2-checkbox,.swal2-file,.swal2-input,.swal2-radio,.swal2-select,.swal2-textarea{margin:1em 2em 0}.swal2-file,.swal2-input,.swal2-textarea{box-sizing:border-box;width:auto;transition:border-color .1s,box-shadow .1s;border:1px solid #d9d9d9;border-radius:.1875em;background:inherit;box-shadow:inset 0 1px 1px rgba(0,0,0,.06),0 0 0 3px transparent;color:inherit;font-size:1.125em}.swal2-file.swal2-inputerror,.swal2-input.swal2-inputerror,.swal2-textarea.swal2-inputerror{border-color:#f27474!important;box-shadow:0 0 2px #f27474!important}.swal2-file:focus,.swal2-input:focus,.swal2-textarea:focus{border:1px solid #b4dbed;outline:0;box-shadow:inset 0 1px 1px rgba(0,0,0,.06),0 0 0 3px rgba(100,150,200,.5)}.swal2-file::-moz-placeholder,.swal2-input::-moz-placeholder,.swal2-textarea::-moz-placeholder{color:#ccc}.swal2-file:-ms-input-placeholder,.swal2-input:-ms-input-placeholder,.swal2-textarea:-ms-input-placeholder{color:#ccc}.swal2-file::placeholder,.swal2-input::placeholder,.swal2-textarea::placeholder{color:#ccc}.swal2-range{margin:1em 2em 0;background:#fff}.swal2-range input{width:80%}.swal2-range output{width:20%;color:inherit;font-weight:600;text-align:center}.swal2-range input,.swal2-range output{height:2.625em;padding:0;font-size:1.125em;line-height:2.625em}.swal2-input{height:2.625em;padding:0 .75em}.swal2-file{width:75%;margin-right:auto;margin-left:auto;background:inherit;font-size:1.125em}.swal2-textarea{height:6.75em;padding:.75em}.swal2-select{min-width:50%;max-width:100%;padding:.375em .625em;background:inherit;color:inherit;font-size:1.125em}.swal2-checkbox,.swal2-radio{align-items:center;justify-content:center;background:#fff;color:inherit}.swal2-checkbox label,.swal2-radio label{margin:0 .6em;font-size:1.125em}.swal2-checkbox input,.swal2-radio input{flex-shrink:0;margin:0 .4em}.swal2-input-label{display:flex;justify-content:center;margin:1em auto 0}.swal2-validation-message{align-items:center;justify-content:center;margin:1em 0 0;padding:.625em;overflow:hidden;background:#f0f0f0;color:#666;font-size:1em;font-weight:300}.swal2-validation-message::before{content:"!";display:inline-block;width:1.5em;min-width:1.5em;height:1.5em;margin:0 .625em;border-radius:50%;background-color:#f27474;color:#fff;font-weight:600;line-height:1.5em;text-align:center}.swal2-icon{position:relative;box-sizing:content-box;justify-content:center;width:5em;height:5em;margin:2.5em auto .6em;border:.25em solid transparent;border-radius:50%;border-color:#000;font-family:inherit;line-height:5em;cursor:default;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}.swal2-icon .swal2-icon-content{display:flex;align-items:center;font-size:3.75em}.swal2-icon.swal2-error{border-color:#f27474;color:#f27474}.swal2-icon.swal2-error .swal2-x-mark{position:relative;flex-grow:1}.swal2-icon.swal2-error [class^=swal2-x-mark-line]{display:block;position:absolute;top:2.3125em;width:2.9375em;height:.3125em;border-radius:.125em;background-color:#f27474}.swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=left]{left:1.0625em;transform:rotate(45deg)}.swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=right]{right:1em;transform:rotate(-45deg)}.swal2-icon.swal2-error.swal2-icon-show{-webkit-animation:swal2-animate-error-icon .5s;animation:swal2-animate-error-icon .5s}.swal2-icon.swal2-error.swal2-icon-show .swal2-x-mark{-webkit-animation:swal2-animate-error-x-mark .5s;animation:swal2-animate-error-x-mark .5s}.swal2-icon.swal2-warning{border-color:#facea8;color:#f8bb86}.swal2-icon.swal2-info{border-color:#9de0f6;color:#3fc3ee}.swal2-icon.swal2-question{border-color:#c9dae1;color:#87adbd}.swal2-icon.swal2-success{border-color:#a5dc86;color:#a5dc86}.swal2-icon.swal2-success [class^=swal2-success-circular-line]{position:absolute;width:3.75em;height:7.5em;transform:rotate(45deg);border-radius:50%}.swal2-icon.swal2-success [class^=swal2-success-circular-line][class$=left]{top:-.4375em;left:-2.0635em;transform:rotate(-45deg);transform-origin:3.75em 3.75em;border-radius:7.5em 0 0 7.5em}.swal2-icon.swal2-success [class^=swal2-success-circular-line][class$=right]{top:-.6875em;left:1.875em;transform:rotate(-45deg);transform-origin:0 3.75em;border-radius:0 7.5em 7.5em 0}.swal2-icon.swal2-success .swal2-success-ring{position:absolute;z-index:2;top:-.25em;left:-.25em;box-sizing:content-box;width:100%;height:100%;border:.25em solid rgba(165,220,134,.3);border-radius:50%}.swal2-icon.swal2-success .swal2-success-fix{position:absolute;z-index:1;top:.5em;left:1.625em;width:.4375em;height:5.625em;transform:rotate(-45deg)}.swal2-icon.swal2-success [class^=swal2-success-line]{display:block;position:absolute;z-index:2;height:.3125em;border-radius:.125em;background-color:#a5dc86}.swal2-icon.swal2-success [class^=swal2-success-line][class$=tip]{top:2.875em;left:.8125em;width:1.5625em;transform:rotate(45deg)}.swal2-icon.swal2-success [class^=swal2-success-line][class$=long]{top:2.375em;right:.5em;width:2.9375em;transform:rotate(-45deg)}.swal2-icon.swal2-success.swal2-icon-show .swal2-success-line-tip{-webkit-animation:swal2-animate-success-line-tip .75s;animation:swal2-animate-success-line-tip .75s}.swal2-icon.swal2-success.swal2-icon-show .swal2-success-line-long{-webkit-animation:swal2-animate-success-line-long .75s;animation:swal2-animate-success-line-long .75s}.swal2-icon.swal2-success.swal2-icon-show .swal2-success-circular-line-right{-webkit-animation:swal2-rotate-success-circular-line 4.25s ease-in;animation:swal2-rotate-success-circular-line 4.25s ease-in}.swal2-progress-steps{flex-wrap:wrap;align-items:center;max-width:100%;margin:1.25em auto;padding:0;background:inherit;font-weight:600}.swal2-progress-steps li{display:inline-block;position:relative}.swal2-progress-steps .swal2-progress-step{z-index:20;flex-shrink:0;width:2em;height:2em;border-radius:2em;background:#2778c4;color:#fff;line-height:2em;text-align:center}.swal2-progress-steps .swal2-progress-step.swal2-active-progress-step{background:#2778c4}.swal2-progress-steps .swal2-progress-step.swal2-active-progress-step~.swal2-progress-step{background:#add8e6;color:#fff}.swal2-progress-steps .swal2-progress-step.swal2-active-progress-step~.swal2-progress-step-line{background:#add8e6}.swal2-progress-steps .swal2-progress-step-line{z-index:10;flex-shrink:0;width:2.5em;height:.4em;margin:0 -1px;background:#2778c4}[class^=swal2]{-webkit-tap-highlight-color:transparent}.swal2-show{-webkit-animation:swal2-show .3s;animation:swal2-show .3s}.swal2-hide{-webkit-animation:swal2-hide .15s forwards;animation:swal2-hide .15s forwards}.swal2-noanimation{transition:none}.swal2-scrollbar-measure{position:absolute;top:-9999px;width:50px;height:50px;overflow:scroll}.swal2-rtl .swal2-close{margin-right:initial;margin-left:0}.swal2-rtl .swal2-timer-progress-bar{right:0;left:auto}@-webkit-keyframes swal2-toast-show{0%{transform:translateY(-.625em) rotateZ(2deg)}33%{transform:translateY(0) rotateZ(-2deg)}66%{transform:translateY(.3125em) rotateZ(2deg)}100%{transform:translateY(0) rotateZ(0)}}@keyframes swal2-toast-show{0%{transform:translateY(-.625em) rotateZ(2deg)}33%{transform:translateY(0) rotateZ(-2deg)}66%{transform:translateY(.3125em) rotateZ(2deg)}100%{transform:translateY(0) rotateZ(0)}}@-webkit-keyframes swal2-toast-hide{100%{transform:rotateZ(1deg);opacity:0}}@keyframes swal2-toast-hide{100%{transform:rotateZ(1deg);opacity:0}}@-webkit-keyframes swal2-toast-animate-success-line-tip{0%{top:.5625em;left:.0625em;width:0}54%{top:.125em;left:.125em;width:0}70%{top:.625em;left:-.25em;width:1.625em}84%{top:1.0625em;left:.75em;width:.5em}100%{top:1.125em;left:.1875em;width:.75em}}@keyframes swal2-toast-animate-success-line-tip{0%{top:.5625em;left:.0625em;width:0}54%{top:.125em;left:.125em;width:0}70%{top:.625em;left:-.25em;width:1.625em}84%{top:1.0625em;left:.75em;width:.5em}100%{top:1.125em;left:.1875em;width:.75em}}@-webkit-keyframes swal2-toast-animate-success-line-long{0%{top:1.625em;right:1.375em;width:0}65%{top:1.25em;right:.9375em;width:0}84%{top:.9375em;right:0;width:1.125em}100%{top:.9375em;right:.1875em;width:1.375em}}@keyframes swal2-toast-animate-success-line-long{0%{top:1.625em;right:1.375em;width:0}65%{top:1.25em;right:.9375em;width:0}84%{top:.9375em;right:0;width:1.125em}100%{top:.9375em;right:.1875em;width:1.375em}}@-webkit-keyframes swal2-show{0%{transform:scale(.7)}45%{transform:scale(1.05)}80%{transform:scale(.95)}100%{transform:scale(1)}}@keyframes swal2-show{0%{transform:scale(.7)}45%{transform:scale(1.05)}80%{transform:scale(.95)}100%{transform:scale(1)}}@-webkit-keyframes swal2-hide{0%{transform:scale(1);opacity:1}100%{transform:scale(.5);opacity:0}}@keyframes swal2-hide{0%{transform:scale(1);opacity:1}100%{transform:scale(.5);opacity:0}}@-webkit-keyframes swal2-animate-success-line-tip{0%{top:1.1875em;left:.0625em;width:0}54%{top:1.0625em;left:.125em;width:0}70%{top:2.1875em;left:-.375em;width:3.125em}84%{top:3em;left:1.3125em;width:1.0625em}100%{top:2.8125em;left:.8125em;width:1.5625em}}@keyframes swal2-animate-success-line-tip{0%{top:1.1875em;left:.0625em;width:0}54%{top:1.0625em;left:.125em;width:0}70%{top:2.1875em;left:-.375em;width:3.125em}84%{top:3em;left:1.3125em;width:1.0625em}100%{top:2.8125em;left:.8125em;width:1.5625em}}@-webkit-keyframes swal2-animate-success-line-long{0%{top:3.375em;right:2.875em;width:0}65%{top:3.375em;right:2.875em;width:0}84%{top:2.1875em;right:0;width:3.4375em}100%{top:2.375em;right:.5em;width:2.9375em}}@keyframes swal2-animate-success-line-long{0%{top:3.375em;right:2.875em;width:0}65%{top:3.375em;right:2.875em;width:0}84%{top:2.1875em;right:0;width:3.4375em}100%{top:2.375em;right:.5em;width:2.9375em}}@-webkit-keyframes swal2-rotate-success-circular-line{0%{transform:rotate(-45deg)}5%{transform:rotate(-45deg)}12%{transform:rotate(-405deg)}100%{transform:rotate(-405deg)}}@keyframes swal2-rotate-success-circular-line{0%{transform:rotate(-45deg)}5%{transform:rotate(-45deg)}12%{transform:rotate(-405deg)}100%{transform:rotate(-405deg)}}@-webkit-keyframes swal2-animate-error-x-mark{0%{margin-top:1.625em;transform:scale(.4);opacity:0}50%{margin-top:1.625em;transform:scale(.4);opacity:0}80%{margin-top:-.375em;transform:scale(1.15)}100%{margin-top:0;transform:scale(1);opacity:1}}@keyframes swal2-animate-error-x-mark{0%{margin-top:1.625em;transform:scale(.4);opacity:0}50%{margin-top:1.625em;transform:scale(.4);opacity:0}80%{margin-top:-.375em;transform:scale(1.15)}100%{margin-top:0;transform:scale(1);opacity:1}}@-webkit-keyframes swal2-animate-error-icon{0%{transform:rotateX(100deg);opacity:0}100%{transform:rotateX(0);opacity:1}}@keyframes swal2-animate-error-icon{0%{transform:rotateX(100deg);opacity:0}100%{transform:rotateX(0);opacity:1}}@-webkit-keyframes swal2-rotate-loading{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}@keyframes swal2-rotate-loading{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown){overflow:hidden}body.swal2-height-auto{height:auto!important}body.swal2-no-backdrop .swal2-container{background-color:transparent!important;pointer-events:none}body.swal2-no-backdrop .swal2-container .swal2-popup{pointer-events:all}body.swal2-no-backdrop .swal2-container .swal2-modal{box-shadow:0 0 10px rgba(0,0,0,.4)}@media print{body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown){overflow-y:scroll!important}body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown)>[aria-hidden=true]{display:none}body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) .swal2-container{position:static!important}}body.swal2-toast-shown .swal2-container{box-sizing:border-box;width:360px;max-width:100%;background-color:transparent;pointer-events:none}body.swal2-toast-shown .swal2-container.swal2-top{top:0;right:auto;bottom:auto;left:50%;transform:translateX(-50%)}body.swal2-toast-shown .swal2-container.swal2-top-end,body.swal2-toast-shown .swal2-container.swal2-top-right{top:0;right:0;bottom:auto;left:auto}body.swal2-toast-shown .swal2-container.swal2-top-left,body.swal2-toast-shown .swal2-container.swal2-top-start{top:0;right:auto;bottom:auto;left:0}body.swal2-toast-shown .swal2-container.swal2-center-left,body.swal2-toast-shown .swal2-container.swal2-center-start{top:50%;right:auto;bottom:auto;left:0;transform:translateY(-50%)}body.swal2-toast-shown .swal2-container.swal2-center{top:50%;right:auto;bottom:auto;left:50%;transform:translate(-50%,-50%)}body.swal2-toast-shown .swal2-container.swal2-center-end,body.swal2-toast-shown .swal2-container.swal2-center-right{top:50%;right:0;bottom:auto;left:auto;transform:translateY(-50%)}body.swal2-toast-shown .swal2-container.swal2-bottom-left,body.swal2-toast-shown .swal2-container.swal2-bottom-start{top:auto;right:auto;bottom:0;left:0}body.swal2-toast-shown .swal2-container.swal2-bottom{top:auto;right:auto;bottom:0;left:50%;transform:translateX(-50%)}body.swal2-toast-shown .swal2-container.swal2-bottom-end,body.swal2-toast-shown .swal2-container.swal2-bottom-right{top:auto;right:0;bottom:0;left:auto}')});var Xe=class{constructor(e){return this.$request=e,this}run(){return $(this,null,function*(){return yield fetch(this.getRequest())})}getRequest(){return this.$request}};s(Xe,"FetchAPI");var Fe=class{constructor(e){this.list=e}getList(){return this.list}};s(Fe,"CommandRegistrar");var F={play:'<svg class="icon tonics-play-outline"><use xlink:href="#tonics-play-outline"></use></svg>',playlist:'<svg class="icon tonics-music-playlist"><use xlink:href="#tonics-music-playlist"></use></svg>',plus:'<svg class="icon tonics-add-new"><use xlink:href="#tonics-add-new"></use></svg>',archive:'<svg class="icon tonics-archive"><use xlink:href="#tonics-archive"></use></svg>',note:'<svg class="icon tonics-note"><use xlink:href="#tonics-note"></use></svg>',notes:'<svg class="icon tonics-multiple-notes"><use xlink:href="#tonics-multiple-notes"></use></svg>',category:'<svg class="icon tonics-categories"><use xlink:href="#tonics-categories"></use></svg>',cog:'<svg class="icon tonics-cog"> <use xlink:href="#tonics-cog"></use></svg>',dashboard:'<svg class="icon tonics-dashboard"> <use xlink:href="#tonics-dashboard"></use></svg>',menu:'<svg class="icon tonics-menu"> <use xlink:href="#tonics-menu"></use></svg>',"trash-can":'<svg class="icon tonics-trash-can"> <use xlink:href="#tonics-trash-can"></use></svg>',cart:'<svg class="icon tonics-cart"> <use xlink:href="#tonics-cart"></use></svg>',widget:'<svg class="icon tonics-widgets"> <use xlink:href="#tonics-widgets"></use></svg>',tools:'<svg class="icon tonics-tools"> <use xlink:href="#tonics-tools"></use></svg>',"toggle-left":'<svg class="icon tonics-toggle-left"> <use xlink:href="#tonics-toggle-left"></use></svg>',"toggle-right":'<svg class="icon tonics-toggle-right"> <use xlink:href="#tonics-toggle-right"></use></svg>',"arrow-down":'<svg class="icon tonics-arrow-down"> <use xlink:href="#tonics-arrow-down"></use></svg>',"arrow-up":'<svg class="icon tonics-arrow-up"> <use xlink:href="#tonics-arrow-up"></use></svg>',"arrow-right":'<svg class="icon tonics-chevron-with-circle-right"> <use xlink:href="#tonics-chevron-with-circle-right"></use></svg>',"arrow-left":'<svg class="icon tonics-chevron-with-circle-left"> <use xlink:href="#tonics-chevron-with-circle-left"></use></svg>',"sign-out":'<svg class="icon tonics-sign-out"> <use xlink:href="#tonics-dashboard"></use></svg>',"user-solid-circle":'<svg class="icon tonics-user-solid-circle"> <use xlink:href="#tonics-user-solid-circle"></use></svg>',users:'<svg class="icon tonics-users"> <use xlink:href="#tonics-users"></use></svg>',"profile-settings":'<svg class="icon tonics-profile-settings"><use xlink:href="#tonics-profile-settings"></use></svg>',"more-horizontal":'<svg class="icon tonics-more-horizontal"><use xlink:href="#tonics-more-horizontal"></use></svg>',"more-vertical":'<svg class="icon tonics-more-vertical"><use xlink:href="#tonics-more-vertical"></use></svg>',heart:'<svg class="icon tonics-heart"> <use xlink:href="#tonics-heart"></use></svg>',"dots-two-vertical":'<svg class="icon tonics-dots-two-vertical"><use xlink:href="#tonics-dots-two-vertical"></use></svg>',"dots-two-horizontal":'<svg class="icon tonics-dots-two-horizontal"><use xlink:href="#tonics-dots-two-horizontal"></use></svg>',"heart-fill":'<svg class="icon tonics-heart-fill"> <use xlink:href="#tonics-heart-fill"></use></svg>',pending:'<svg class="icon tonics-pending"><use xlink:href="#tonics-pending"></use></svg>',remove:'<svg class="icon tonics-remove"> <use xlink:href="#tonics-remove"></use></svg>',"shopping-cart":'<svg class="icon tonics-shopping-cart"> <use xlink:href="#tonics-shopping-cart"></use></svg>',dollar:'<svg class="icon tonics-dollar"> <use xlink:href="#tonics-dollar"></use></svg>',"align-left":'<svg class="icon tonics-align_left"> <use xlink:href="#tonics-align_left"></use></svg>',"align-right":'<svg class="icon tonics-align_right"> <use xlink:href="#tonics-align_right"></use></svg>',"align-column":'<svg class="icon tonics-align-column"> <use xlink:href="#tonics-align-column"></use></svg>',"align-row":'<svg class="icon tonics-align-row"> <use xlink:href="#tonics-align-row"></use></svg>',MEDIA:{shuffle:'<svg class="icon tonics-shuffle"> <use xlink:href="#tonics-shuffle"></use></svg>',refresh:'<svg class="icon tonics-refresh"> <use xlink:href="#tonics-refresh"></use></svg>',"step-forward":'<svg class="icon tonics-step-forward"> <use xlink:href="#tonics-step-forward"></use></svg>',"step-backward":'<svg class="icon tonics-step-backward"> <use xlink:href="#tonics-step-backward"></use></svg>',"pause-outline":'<svg class="icon tonics-pause-outline"> <use xlink:href="#tonics-pause-outline"></use></svg>',"play-outline":'<svg class="icon tonics-play-outline"> <use xlink:href="#tonics-play-outline"></use></svg>'},SOCIAL:{mail:'<svg class="icon tonics-mail"> <use xlink:href="#tonics-mail"></use></svg>',"google-plus":'<svg class="icon tonics-google-plus"> <use xlink:href="#tonics-google-plus"></use></svg>',hangouts:'<svg class="icon tonics-hangouts"> <use xlink:href="#tonics-hangouts"></use></svg>',facebook:'<svg class="icon tonics-facebook"> <use xlink:href="#tonics-facebook"></use></svg>',instagram:'<svg class="icon tonics-instagram"> <use xlink:href="#tonics-instagram"></use></svg>',whatsapp:'<svg class="icon tonics-whatsapp"> <use xlink:href="#tonics-whatsapp"></use></svg>',telegram:'<svg class="icon tonics-telegram"> <use xlink:href="#tonics-telegram"></use></svg>',renren:'<svg class="icon tonics-renren"> <use xlink:href="#tonics-renren"></use></svg>',rss:'<svg class="icon tonics-rss"> <use xlink:href="#tonics-rss"></use></svg>',twitch:'<svg class="icon tonics-twitch"> <use xlink:href="#tonics-twitch"></use></svg>',vimeo:'<svg class="icon tonics-vimeo"> <use xlink:href="#tonics-vimeo"></use></svg>',flickr:'<svg class="icon tonics-flickr"> <use xlink:href="#tonics-flickr"></use></svg>',dribble:'<svg class="icon tonics-dribble"> <use xlink:href="#tonics-dribble"></use></svg>',behance:'<svg class="icon tonics-behance"> <use xlink:href="#tonics-behance"></use></svg>',deviantart:'<svg class="icon tonics-deviantart"> <use xlink:href="#tonics-deviantart"></use></svg>',"500px":'<svg class="icon tonics-500px"> <use xlink:href="#tonics-500px"></use></svg>',steam:'<svg class="icon tonics-steam"> <use xlink:href="#tonics-steam"></use></svg>',soundcloud:'<svg class="icon tonics-soundcloud"> <use xlink:href="#tonics-soundcloud"></use></svg>',skype:'<svg class="icon tonics-skype"> <use xlink:href="#tonics-skype"></use></svg>',lastfm:'<svg class="icon tonics-lastfm"> <use xlink:href="#tonics-lastfm"></use></svg>',linkedin:'<svg class="icon tonics-linkedin"> <use xlink:href="#tonics-linkedin"></use></svg>',github:'<svg class="icon tonics-github"> <use xlink:href="#tonics-github"></use></svg>',twitter:'<svg class="icon tonics-twitter"> <use xlink:href="#tonics-twitter"></use></svg>',youtube:'<svg class="icon tonics-youtube"> <use xlink:href="#tonics-youtube"></use></svg>',reddit:'<svg class="icon reddit"> <use xlink:href="#tonics-reddit"></use></svg>',delicious:'<svg class="icon tonics-delicious"> <use xlink:href="#tonics-delicious"></use></svg>',stackoverflow:'<svg class="icon tonics-stackoverflow"> <use xlink:href="#tonics-stackoverflow"></use></svg>',pinterest:'<svg class="icon tonics-dashboard"> <use xlink:href="#tonics-pinterest"></use></svg>',xing:'<svg class="icon tonics-dashboard"> <use xlink:href="#tonics-xing"></use></svg>',flattr:'<svg class="icon tonics-flattr"> <use xlink:href="#tonics-flattr"></use></svg>',foursquare:'<svg class="icon tonics-foursquare"> <use xlink:href="#tonics-foursquare"></use></svg>',yelp:'<svg class="icon tonics-yelp"> <use xlink:href="#tonics-yelp"></use></svg>'},FILE:{file:'<svg class="icon tonics-file"> <use xlink:href="#tonics-file"></use></svg>',folder:'<svg class="icon tonics-folder"> <use xlink:href="#tonics-folder"></use></svg>',image:'<svg class="icon tonics-file-image"> <use xlink:href="#tonics-file-image"></use></svg>',"load-more":'<svg class="icon tonics-load-more"> <use xlink:href="#tonics-load-more"></use></svg>',music:'<svg class="icon tonics-music"> <use xlink:href="#tonics-music"></use></svg>',note:'<svg class="icon tonics-note"> <use xlink:href="#tonics-note"></use></svg>',pdf:'<svg class="icon tonics-pdf"> <use xlink:href="#tonics-pdf"></use></svg>',docx:'<svg class="icon tonics-docx"> <use xlink:href="#tonics-docx"></use></svg>',code:'<svg class="icon tonics-code"> <use xlink:href="#tonics-code"></use></svg>',zip:'<svg class="icon tonics-zip"> <use xlink:href="#tonics-zip"></use></svg>',compress:'<svg class="icon tonics-compress"> <use xlink:href="#tonics-compress"></use></svg>',exclamation:'<svg class="icon tonics-exclamation"> <use xlink:href="#tonics-exclamation"></use></svg>'},CONTEXT:{link:'<svg class="icon tonics-download-link"> <use xlink:href="#tonics-download-link"></use></svg>',preview_link:'<svg class="icon tonics-link"> <use xlink:href="#tonics-link"></use></svg>',edit:'<svg class="icon tonics-edit-icon"> <use xlink:href="#tonics-edit-icon"></use></svg>',cut:'<svg class="icon tonics-cut-icon"> <use xlink:href="#tonics-cut"></use></svg>',trash:'<svg class="icon tonics-trash-icon"> <use xlink:href="#tonics-trash-can"></use></svg>',paste:'<svg class="icon tonics-paste-icon"> <use xlink:href="#tonics-paste"></use></svg>',upload:'<svg class="icon tonics-plus-icon"> <use class="svgUse" xlink:href="#tonics-upload-icon"></use></svg>',refresh:'<svg class="icon tonics-refresh"><use class="svgUse" xlink:href="#tonics-refresh"></use></svg>',plus:'<svg class="icon tonics-plus"><use class="svgUse" xlink:href="#tonics-plus2"></use></svg>'}};function w(){return{EDIT_IMAGE_FILE:"EditImageFileEvent",DELETE_FILE:"DeleteFileEvent",RENAME_FILE:"RenameFileEvent",CUT_FILE:"CutFileEvent",PASTE_FILE:"PasteFileEvent",UPLOAD_FILE:"UploadFileEvent",COPY_LINK:"CopyLinkEvent",COPY_PREVIEW_LINK:"CopyPreviewLinkEvent",CREATE_FOLDER:"NewFolderEvent",REFRESH_FOLDER:"RefreshFolderEvent"}}s(w,"MenuActions");var Y=Xr(Zn());var k={HEAD:{PARENT:".tonics-main-header-menu",MENU_SECTION:".menu-section",NAV:"#site-navigation"},FILES:{BREADCRUMB:".breadcrumb",CONTEXT:".context-menu",PROGRESS:{CONTAINER:".upload-progress-container",UPLOAD_FILE_CONTAINER:".upload-files",UPLOAD_STRING:".upload-string",PROGRESS_PERCENTAGE:".upload-percentage",CONTROL:{RESUME_PAUSE:".resume-pause",CANCEL:".cancel"}},FILE_MAIN_CONTENT:".tonics-fm-main-content",FILE_PARENT:".tonics-files-parent",FILE_CONTAINER:".tonics-files-container",SINGLE_FILE:"li.tonics-file"},DRIVE:{TOGGLE:".drive-toggle",DRIVE_NAVIGATION:".tonics-fm-nav-menu",DISK_DRIVE_CONTAINER:".tonics-disk-drive-container",DRIVE_FOLDER:".drive-folder",FILE_DISK_DRIVES:".tonics-disk-drive-container",INDIVIDUAL_DRIVE:".tonics-individual-drive",DRIVE_SELECTED:".tonics-drive-selected"},Button:{FILE_LOAD_MORE:".file-load-more"},SEARCH:".filter-search"};var We=class{constructor(e={}){this.$callbacks={};this.http=new XMLHttpRequest,this.headers=e,this.settings()}getCallbacks(){return this.$callbacks}settings(){this.getCallbacks().callbacks={onProgress:null}}checkIfCallbackIsSet(){if(!this.getCallbacks().callbacks)throw new DOMException("No Callbacks exist");return!0}onProgress(e){if(this.checkIfCallbackIsSet())return this.getCallbacks().callbacks.onProgress=e,this}Get(e,i){this.getHttp().open("GET",e,!0),this.setHeaders(),this.getHttp().send();let o=this;this.getHttp().onreadystatechange=function(){try{o.http.readyState===XMLHttpRequest.DONE&&(o.http.status===200?i(null,o.http.response):i(o.http.response))}catch(a){i("Something Went Wrong: "+a.description)}}}Post(e,i,o){this.getHttp().open("POST",e,!0),this.setHeaders(),this.getHttp().send(i);let a=this,l=a.getCallbacks().callbacks.onProgress;l!==null&&typeof l=="function"&&this.getHttp().upload.addEventListener("progress",function(d){l(d)});try{this.http.onload=function(){o(null,a.http.responseText)}}catch(d){o("Something Went Wrong: "+d.description)}}Put(e,i,o){this.getHttp().open("PUT",e,!0),this.setHeaders(),this.getHttp().send(i);let a=this,l=a.getCallbacks().callbacks.onProgress;l!==null&&typeof l=="function"&&this.getHttp().upload.addEventListener("progress",function(d){l(d)});try{this.http.onload=function(){a.http.status===200?o(null,a.http.response):o(a.http.response)}}catch(d){o("Something Went Wrong: "+d.description)}}Delete(e,i=null,o){this.http.open("DELETE",e,!0),this.setHeaders(),i?this.http.send(i):this.http.send();let a=this;try{this.http.onload=function(){a.http.status===200?o(null,a.http.response):o(a.http.response)}}catch(l){o("Something Went Wrong: "+l.description)}}getHeaders(){return this.headers}setHeaders(){if(this.getHeaders())for(let e in this.getHeaders())this.getHttp().setRequestHeader(e,this.getHeaders()[e])}getHttp(){return this.http}};s(We,"XHRApi");var Ke=class{constructor(){this.$eventHandlers=new Map}attachHandlerToEvent(e,i){var o;return this.getHandlers().has(e)?((o=this.getHandlers().get(e))==null||o.push(i),this):(this.getHandlers().set(e,[i]),this)}getHandlers(){return this.$eventHandlers}detachHandlerFromEvent(e){if(this.getHandlers().has(e))return this.getHandlers().delete(e),this}getEventHandlers(e){var i;return this.getHandlers().has(e)?(i=this.getHandlers().get(e))!=null?i:[]:[]}};s(Ke,"EventQueue");window.hasOwnProperty("TonicsEvent")||(window.TonicsEvent={});window.TonicsEvent.EventQueue=()=>new Ke;function ae(u,e){let i=new Ke,o=e.name;if(u.hasOwnProperty(o)){let a=u[o];return a.length>0&&(a==null||a.forEach((l,d)=>{i.attachHandlerToEvent(e,l)})),i}throw new DOMException(`Can't attach ${e} to listeners because it doesn't exist`)}s(ae,"attachEventAndHandlersToHandlerProvider");window.hasOwnProperty("TonicsEvent")||(window.TonicsEvent={});window.TonicsEvent.attachEventAndHandlersToHandlerProvider=(u,e)=>ae(u,e);var ht=class{constructor(e){this._shiftClick=new Map;this.fileContainerEvent=e,this.addClickEvent()}get shiftClick(){return this._shiftClick}set shiftClick(e){this._shiftClick=e}addClickEvent(){let e=this,i=document.querySelector(k.FILES.FILE_PARENT);i.hasAttribute("data-event-click")||i.addEventListener("click",o=>{i.setAttribute("data-event-click","true");let a=o.target,l=k.FILES.SINGLE_FILE;if(a.closest(".tonics-file-filename-input"))return!1;if(a.closest(l)){let d=a.closest(l);if(o.ctrlKey)return o.preventDefault(),d.classList.contains("selected-file")?e.getFileContainerEvent().unHighlightFile(d):e.getFileContainerEvent().highlightFile(d),!1;o.shiftKey?(this.getFileContainerEvent().resetPreviousFilesState(),this.setShiftClick(d)):(e.getFileContainerEvent().resetPreviousFilesState(),e.getFileContainerEvent().highlightFile(d),this.resetShiftClick(),this.setShiftClick(d))}else this.resetShiftClick(),e.getFileContainerEvent().resetPreviousFilesState()},!1)}setShiftClick(e){let i=e.dataset.list_id;if(this.shiftClick.get(i)&&this.shiftClick.delete(i),this.shiftClick.set(i,e),this.shiftClick.size>=2){let o=[...this.shiftClick][0][0],a=[...this.shiftClick][this.shiftClick.size-1][0],l=[o,a];l.sort();for(let d=l[0];d<=l[1];d++){let f=document.querySelector(`[data-list_id="${d}"]`);f&&this.getFileContainerEvent().highlightFile(f)}}}resetShiftClick(){this.shiftClick=new Map}getFileContainerEvent(){return this.fileContainerEvent}};s(ht,"AddClickEventToFileContainer");var vt=class{constructor(e){let i=e.getFileContainer();i.hasAttribute("data-event-dblclick")||(i.setAttribute("data-event-dblclick","true"),i.addEventListener("dblclick",o=>{let a=e.getSelectedFile();a.dataset.file_type==="directory"&&(a.querySelector(".svg-per-file-loading").classList.remove("display-none"),e.currentDrive.openFolderHandler(a,e).then(()=>{e.resetPreviousFilesState()}).catch(()=>{a.querySelector(".svg-per-file-loading").classList.add("display-none")})),a.dataset.file_type==="file"&&e.copyPreviewLinkEvent()}))}};s(vt,"DoubleClickEventToFileContainer");var wt=class{constructor(e){this.fileContainerEventObject=e,this.addContextMenuClickEvent()}getFileContainerEventObject(){return this.fileContainerEventObject}addContextMenuClickEvent(){let e=document.querySelector(k.FILES.CONTEXT);document.addEventListener("click",i=>{if(i.target.closest(k.FILES.CONTEXT))return!1;e.classList.remove("show")}),(e==null?void 0:e.hasAttribute("data-event-contextmenu"))||(e.setAttribute("data-event-contextmenu","true"),e.addEventListener("click",i=>{let o=i.target;if(o.closest(".context-menu-item").hasAttribute("data-menu-action")){let a=o.closest(".context-menu-item").getAttribute("data-menu-action");this.getFileContainerEventObject().menuEventAction(a)}}))}};s(wt,"ContextMenuProcessor");var C={Rename:{Success:"File Successfully Renamed",Error:"Failed To Rename File"},Folder:{Success:"Folder Successfully Created",Error:"Failed To Create Folder"},Update:{Success:"File Successfully Updated",Error:"Failed To Update File"},Upload:{Success:"File Successfully Uploaded",Error:"Failed To Upload File"},Deleted:{Success:"File(s) Successfully Deleted",Error:"Failed To Delete File(s)"},Refresh:{Success:"Refreshed",Error:"Failed To Refresh Folder"},Move:{Success:"File(s) Successfully Moved",Error:"Failed To Move File(s)"},Link:{Copy:{Preview:{Success:"Preview Link Copied To Clipboard",Error:"Failed To Copy Link To Clipboard"},Download:{Success:"Download Link Copied To Clipboard",Error:"Failed To Copy Link To Clipboard"}}},Context:{Media:{Play:"Play",Pause:"Pause"},Rename:"Rename",Link:{Copy:"Download Link",Preview:"Preview Link"},Edit:{Image:"Edit Image"},Cut:"Cut",Delete:"Delete",Paste:"Paste",Refresh:"Refresh",Upload:"Upload",New_Folder:"New Folder"}};var le=class{extensions(){return[""]}run(e,i,o=null){if(o&&typeof o=="function")return o(F.FILE.exclamation,i,e)}fileContext(e){return`
    ${S(C.Context.Rename,F.CONTEXT.edit,w().RENAME_FILE)}
    ${S(C.Context.Link.Copy,F.CONTEXT.link,w().COPY_LINK)}
    ${S(C.Context.Link.Preview,F.CONTEXT.preview_link,w().COPY_PREVIEW_LINK)}
    ${S(C.Context.Cut,F.CONTEXT.cut,w().CUT_FILE)}
    ${S(C.Context.Delete,F.CONTEXT.trash,w().DELETE_FILE)}`}};s(le,"DefaultFilePlacement");var Et=class{fileContext(e){return`
    ${S(C.Context.Refresh,F.CONTEXT.refresh,w().REFRESH_FOLDER)}
    ${S(C.Context.New_Folder,F.CONTEXT.plus,w().CREATE_FOLDER)}
    ${S(C.Context.Upload,F.CONTEXT.upload,w().UPLOAD_FILE)}`}};s(Et,"BackgroundFilePlacement");var bt=class{constructor(e){let i=e.getContextMenu();e.getFileContainer().addEventListener("contextmenu",a=>{let l=a.target;if(a.preventDefault(),l.closest(k.FILES.SINGLE_FILE)){let d=l.closest(k.FILES.SINGLE_FILE),f=d.dataset.ext,m=!1;xe.FileByExtensions.forEach(v=>{v.extensions().includes(f)&&(m=!0,e.highlightFile(d),this.showContextMenu(i,v.fileContext(e),a))}),m||(e.highlightFile(d),this.showContextMenu(i,new le().fileContext(e),a))}else this.showContextMenu(i,new Et().fileContext(e),a)})}showContextMenu(e,i,o){e==null||e.replaceChildren(),e.insertAdjacentHTML("beforeend",i);let a=o.clientX,l=o.clientY;e.classList.remove("show"),e.style.top=`${l}px`,e.style.left=`${a-30}px`,setTimeout(()=>{e.classList.add("show")})}};s(bt,"ContextHandler");var Ct=class{constructor(e){this.fileContainerEventObject=e,this.addHeaderMenuClickEvent()}getFileContainerEventObject(){return this.fileContainerEventObject}addHeaderMenuClickEvent(){let e=document.querySelector(".site-navigation-ul");e&&((e==null?void 0:e.hasAttribute("data-event-menu"))||(e.setAttribute("data-event-menu","true"),e.addEventListener("click",i=>{let o=i.target;if(o.closest("button")){let a=o.closest("button");if(a.hasAttribute("data-menu-action")){let l=a.getAttribute("data-menu-action");this.getFileContainerEventObject().menuEventAction(l)}}})))}hideHeaderMenuOnScroll(){let e=document.querySelector(k.HEAD.PARENT),i=e==null?void 0:e.getBoundingClientRect().height,o=20;window.addEventListener("scroll",()=>{o>window.scrollY?e.style.top="0":e.style.top=`-${i}px`,o=window.scrollY})}};s(Ct,"HeaderSectionHandler");var ke=class{constructor(e){return e?this.query(e):this}query(e){let i=document.querySelector(`${e}`);if(i)return this.setQueryResult(i),this;console.log(`Invalid class or id name - ${e}`)}setQueryResult(e){return this.$queryResult=e,this}getQueryResult(){return this.$queryResult}};s(ke,"ElementAbstract");var Ye=class extends ke{constructor(e){super(e);this.$dragAndDropDetails={};this.settings()}settings(){this.getDragAndDropElementDetails().callbacks={onDragEnter:null,onDragOver:null,onDragLeave:null,onDragDrop:null}}getDragAndDropElementDetails(){return this.$dragAndDropDetails}checkIfSettingsIsSet(){if(!this.getDragAndDropElementDetails().callbacks)throw new DOMException("No Callbacks exist for the DragAndDropElement");return!0}onDragEnter(e){if(this.checkIfSettingsIsSet())return this.getDragAndDropElementDetails().callbacks.onDragEnter=e,this}onDragOver(e){if(this.checkIfSettingsIsSet())return this.getDragAndDropElementDetails().callbacks.onDragOver=e,this}onDragLeave(e){if(this.checkIfSettingsIsSet())return this.getDragAndDropElementDetails().callbacks.onDragLeave=e,this}onDragDrop(e){if(this.checkIfSettingsIsSet())return this.getDragAndDropElementDetails().callbacks.onDragDrop=e,this}run(){let e=this.getQueryResult(),i=this;e&&(e.addEventListener("dragenter",function(o){i.preventDefaults(o),e.classList.add("highlight");let a=i.getDragAndDropElementDetails().callbacks.onDragEnter;a!==null&&typeof a=="function"&&a(o)}),e.addEventListener("dragover",function(o){i.preventDefaults(o),e.classList.add("highlight");let a=i.getDragAndDropElementDetails().callbacks.onDragOver;a!==null&&typeof a=="function"&&a(o)}),e.addEventListener("dragleave",function(o){i.preventDefaults(o),e.classList.remove("highlight");let a=i.getDragAndDropElementDetails().callbacks.onDragLeave;a!==null&&typeof a=="function"&&a(o)}),e.addEventListener("drop",function(o){i.preventDefaults(o),e.classList.remove("highlight");let a=i.getDragAndDropElementDetails().callbacks.onDragDrop;a!==null&&typeof a=="function"&&a(o)},!1))}preventDefaults(e){e.preventDefault(),e.stopPropagation()}};s(Ye,"DragAndDrop");window.hasOwnProperty("TonicsScript")||(window.TonicsScript={});window.TonicsScript.DragAndDrop=u=>new Ye(u);var Ge=class extends ke{addNodeElement(e){return this.setQueryResult(e),this}forward(e){let i=this.getQueryResult().nextElementSibling;for(;i;){if(i.matches(e))return this.setQueryResult(i),this;i=i.nextElementSibling}return null}backward(e){let i=this.getQueryResult().previousElementSibling;for(;i;){if(i.matches(e))return this.setQueryResult(i),this;i=i.previousElementSibling}return null}in(){let e=this.getQueryResult().firstElementChild;return e?(this.setQueryResult(e),this):null}out(){let e=this.getQueryResult().parentElement;return e?(this.setQueryResult(e),this):null}queryChildren(e,i=!0){let o=this.getQueryResult().querySelector(e);return o?i?(this.setQueryResult(o),this):o:null}setSVGUseAttribute(e){let i=this.getQueryResult();if(i.tagName=="use")i.removeAttribute("xlink:href"),i.setAttributeNS("http://www.w3.org/1999/xlink","xlink:href",e);else throw new DOMException("Not a valid svg use element")}};s(Ge,"Query");window.hasOwnProperty("TonicsScript")||(window.TonicsScript={});window.TonicsScript.Query=()=>new Ge;var ce=class{constructor(e,i,o){this._$uploadedFilesObject=new Map;this._maxRequestToSend=4;this._byteToSendPerChunk=.5*1048576;this._isUploadInParallel=!1;this._$fileSequence={};this.fileContainerEvent=e,i!==null&&o!==null&&this.handleFiles(i,o),this.UploadFileEvent=this}get maxRequestToSend(){return this._maxRequestToSend}get $uploadedFilesObject(){return this._$uploadedFilesObject}get $fileSequence(){return this._$fileSequence}set $fileSequence(e){this._$fileSequence=e}get byteToSendPerChunk(){return this._byteToSendPerChunk}set byteToSendPerChunk(e){this._byteToSendPerChunk=e}get isUploadInParallel(){return this._isUploadInParallel}set isUploadInParallel(e){this._isUploadInParallel=e}getFileContainerEvent(){return this.fileContainerEvent}handleFiles(e,i){this.handleUploadedFiles(e,i)}setUploadFileObject(e,i){this.$uploadedFilesObject.set(e,i)}handleFileUpload(){let e=this,i=this.fileContainerEvent,o=document.createElement("input");o.type="file",o.multiple=!0,o.click(),o.onchange=function(a){let l=a.target.files,d=i.getCurrentDirectory();e.handleUploadedFiles(l,d)}}handleUploadedFiles(e,i){let o=this,a,l=o.fileContainerEvent;l.removeContextMenu();for(let d=0,f=e.length;d<f;d++){let m=l.currentDrive.driveSignature+"_"+e[d].name;o.addFileToProgressContainer(e[d],m,null,(v,h,y,b)=>{b=o.UploadFileEvent,l.currentDrive.uploadFile(y,h,b)},(v,h)=>{h=o.UploadFileEvent,l.currentDrive.cancelFileUploadHandler(v,h).then(()=>{H(`${v.fileObject.name} Upload Terminated`)}).catch(()=>{O("Failed To Cancel Upload")})}),o.isUploadingSequentially()&&o.attachFileToSequence(m)}o.isUploadingSequentially()?o.uploadFileNextSequence(l.currentDrive.driveSignature):o.uploadFiles()}uploadFiles(){this.$uploadedFilesObject.forEach((e,i)=>{e.uploaded||this.fileContainerEvent.currentDrive.uploadFile(e,i,this)})}uploadFileNextSequence(e){let o=this.fileContainerEvent.loadDriveEventClass.driveStorageManager.getDriveStorage(e),a=this.getUploadFileNextSequence(e);if(a&&typeof a=="string"){let l=this.getUploadFileObject(a,o.driveSignature);o.uploadFile(l,a,this)}}getUploadFileObject(e,i){for(let o of this.$uploadedFilesObject.values())if(o.driveSignature===i)return this.$uploadedFilesObject.get(e)}getUploadFileNextSequence(e){let i=this.$fileSequence,o=!1;for(let a in i)if(i.hasOwnProperty(a)){if(a.split("_")[0]!==e)continue;if(i[a].progressing===!1){this.$fileSequence[a]={progressing:!0,sequenceDone:!1},o=a;break}}return o}setSequenceDone(e,i){let o=`${i}_${e}`;this.$fileSequence.hasOwnProperty(o)&&(this.$fileSequence[o]={progressing:!0,sequenceDone:!0})}deleteUploadFileObject(e){this.$uploadedFilesObject.delete(e),delete this.$fileSequence[e]}addFileToProgressContainer(e,i,o=null,a=null,l=null){var E;let d=this,f=this.fileContainerEvent,m=this.byteToSendPerChunk,v=f.currentDrive.driveSignature,h={fileObject:e,driveSignature:v,preFlightData:{filename:e.name,dataToFill:null,chunksToSend:Math.ceil(e.size/m),Byteperchunk:m,Totalblobsize:e.size,maxRequestToSend:f.currentDrive.getMaxRequestToSend(),noOfReceivedResponse:0,throttleSwitch:!1,sentApi:0,nextIndex:0},newFile:!0,uploadTo:f.getCurrentDirectory(),fetched:!1,pause:!1,uploaded:!1},y=(E=document.querySelector(k.FILES.PROGRESS.UPLOAD_FILE_CONTAINER))==null?void 0:E.querySelector(`[data-filename="${i}"]`),b=document.querySelector(k.FILES.PROGRESS.UPLOAD_FILE_CONTAINER);b.classList.remove("display-none"),y||(b.insertAdjacentHTML("beforeend",`
      <div class="inner-file-upload-container" tabindex="0" data-pause="false" data-filename="${i}" data-uploaded="false">

        <div class="info">
         <span class="upload-string">[${f.currentDrive.driveSignature} Drive] -  \u22EF Uploading [</span>
         <span class="upload-progress-name" tabindex="0">${i.split("_")[1]}</span>
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
      </div>`),d.$uploadedFilesObject.set(i,h)),b.hasAttribute("data-event-click")||(b.setAttribute("data-event-click","true"),b.addEventListener("click",P=>{var lt;let _=P.target;if(_.closest(k.FILES.PROGRESS.CONTROL.RESUME_PAUSE)){let G=_.closest(k.FILES.PROGRESS.CONTROL.RESUME_PAUSE);_.closest(".inner-file-upload-container").dataset.pause=_.closest(".inner-file-upload-container").dataset.pause==="true"?"false":"true";let oe=_.closest(".inner-file-upload-container").dataset.pause,p=_.closest(".inner-file-upload-container").dataset.filename,V=d.getUploadFileObject(p,f.currentDrive.driveSignature);oe==="true"&&(G.innerText="Resume",G.title="Resume",V.pause=!0,this.$uploadedFilesObject.set(p,V),typeof o=="function"&&o(P,p,V,d)),oe==="false"&&(G.innerText="Pause",G.title="Pause",V.pause=!1,this.$uploadedFilesObject.set(p,V),typeof a=="function"&&!V.uploaded&&!V.pause&&a(P,p,V,d))}if(_.closest(k.FILES.PROGRESS.CONTROL.CANCEL)){let G=_.closest(".inner-file-upload-container"),oe=G.dataset.filename,p=d.getUploadFileObject(oe,f.currentDrive.driveSignature);oe&&p&&typeof l=="function"&&(this.deleteUploadFileObject(oe),l(p,f),G.classList.add("file-upload-cancelled"),G.dataset.filename="",(lt=G.querySelector(".control"))==null||lt.remove())}}))}isUploadingSequentially(){return!this.isUploadInParallel}attachFileToSequence(e){this.$fileSequence.hasOwnProperty(e)||(this.$fileSequence[e]={progressing:!1,sequenceDone:!1})}updateFileProgress(e,i=0,o,a=" \u22EF Uploading"){let d=new Ge(k.FILES.PROGRESS.UPLOAD_FILE_CONTAINER).getQueryResult().querySelector(`[data-filename="${e}"]`);d&&(d.querySelector(k.FILES.PROGRESS.UPLOAD_STRING).innerHTML=`[${o} Drive] - ${a} [`,d.querySelector(k.FILES.PROGRESS.PROGRESS_PERCENTAGE).innerHTML=`${i}%`,d.style.background=`linear-gradient(to right, #bab8b8 ${i}%, #ffffff00 0%)`)}setUploadCompleted(e,i){var l,d;let o=this.getUploadFileObject(e,i),a=(l=document.querySelector(k.FILES.PROGRESS.UPLOAD_FILE_CONTAINER))==null?void 0:l.querySelector(`[data-filename="${e}"]`);a&&((d=a.querySelector(".control"))==null||d.remove(),this.updateFileProgress(e,100,i,"\u2713 Completed "),o.uploaded=!0,this.$uploadedFilesObject.set(e,o),a.dataset.uploaded="true",this.$uploadedFilesObject.delete(a.dataset.uploadFilename),H(C.Upload.Success))}releaseThrottle(e,i){let o=this.getUploadFileObject(e,i);o&&(o.preFlightData.throttleSwitch=!1,this.$uploadedFilesObject.set(e,o),o.preFlightData.sentApi=0,this.$uploadedFilesObject.set(e,o),o.preFlightData.noOfReceivedResponse=0,this.$uploadedFilesObject.set(e,o))}};s(ce,"UploadFileEvent");var yt=class{constructor(e){this._maxRequestToSend=4;this.fileContainerEventObject=e,this.handleDragAndDropFileUploads()}getFileContainerEventObject(){return this.fileContainerEventObject}handleDragAndDropFileUploads(){var o;let e=this,i=e.getFileContainerEventObject().getFileContainer().closest(".tonics-files-parent");if(!i.hasAttribute("data-drag_drop")){i.setAttribute("data-drag_drop","true");let a=new Ye(k.FILES.FILE_PARENT);(o=a==null?void 0:a.onDragDrop(function(l){var m;let d=(m=l==null?void 0:l.dataTransfer)==null?void 0:m.files,f=e.uploadToDirectory();new ce(e.getFileContainerEventObject(),d,f)}))==null||o.run()}}uploadToDirectory(){return this.getFileContainerEventObject().getCurrentDirectory()}get maxRequestToSend(){return this._maxRequestToSend}set maxRequestToSend(e){this._maxRequestToSend=e}};s(yt,"FileContainerDragAndDropHandler");var Ft=class{constructor(e){let i=e.getDiskDrives();i.hasAttribute("data-event-click")||(i.setAttribute("data-event-click","true"),i.addEventListener("click",o=>{let a=o.target;if(a.closest(k.DRIVE.INDIVIDUAL_DRIVE)){let l=a.closest(k.DRIVE.INDIVIDUAL_DRIVE),d=e.getLoadDriveEventClass();if(d.driveStorageManager.$driveSystem.has(l.dataset.drivename)){let f=d.driveStorageManager.getDriveStorage(l.dataset.drivename);f.coldBootStorageDisk().then(()=>{e.removeAllDriveSelectionMark(),l.querySelector(k.DRIVE.DRIVE_SELECTED).classList.remove("display-none"),e.currentDrive=f}).catch(()=>{O("Failed To Switch Drive, Network Issue?")})}}}))}};s(Ft,"SwitchDriveStorageHandler");var xt=class{constructor(e){this.fileContainerEvent=e,this.loadMoreButtonHandle()}getFileContainerEvent(){return this.fileContainerEvent}loadMoreButtonHandle(){let e=document.querySelector(k.Button.FILE_LOAD_MORE);e.hasAttribute("data-event-click")||e.addEventListener("click",i=>{e.setAttribute("data-event-click","true"),re(!1,!0);let o=document.querySelector("[data-list_id]:last-of-type");this.getFileContainerEvent().currentDrive.loadMoreFiles(this.getFileContainerEvent()).then(()=>{o.scrollIntoView({behavior:"smooth",block:"start",inline:"nearest"})}).catch(()=>{O("Failed To Load More Files"),re()})})}};s(xt,"LoadMoreFilesHandler");var kt=class{constructor(e){this.fileContainerEvent=e,this.handleNavigationByKeyPres()}getFileContainerEvent(){return this.fileContainerEvent}handleNavigationByKeyPres(){document.querySelector(k.FILES.FILE_CONTAINER).addEventListener("keydown",i=>{if(this.getFileContainerEvent().getSelectedFile()){let o=this.getFileContainerEvent().getSelectedFile();switch(i.code){case"ArrowDown":this.navigateDown(o);break;case"ArrowUp":this.navigateUp(o);break;case"ArrowRight":this.navigateRight(o);break;case"ArrowLeft":this.navigateLeft(o);break;case"Enter":this.navigateEnter(o);break}}})}navigateDown(e){let i=this.getNumberOfFilesPerRow(),o=parseInt(e.dataset.list_id)+i;e=this.getFileContainerEvent().getFileByListID(o),e&&(this.getFileContainerEvent().resetPreviousFilesState(),e.scrollIntoView(),this.getFileContainerEvent().highlightFile(e),this.removeHeaderMenuFromViewPort())}navigateUp(e){let i=this.getNumberOfFilesPerRow(),o=parseInt(e.dataset.list_id)-i;e=this.getFileContainerEvent().getFileByListID(o),e&&(this.getFileContainerEvent().resetPreviousFilesState(),e.scrollIntoView(),this.getFileContainerEvent().highlightFile(e),this.removeHeaderMenuFromViewPort())}navigateRight(e){let i=e.nextElementSibling;i&&(this.getFileContainerEvent().resetPreviousFilesState(),i.scrollIntoView(),this.getFileContainerEvent().highlightFile(i),this.removeHeaderMenuFromViewPort())}navigateLeft(e){let i=e.previousElementSibling;i&&(this.getFileContainerEvent().resetPreviousFilesState(),i.scrollIntoView(),this.getFileContainerEvent().highlightFile(i),this.removeHeaderMenuFromViewPort())}removeHeaderMenuFromViewPort(){let e=document.querySelector(k.HEAD.PARENT),i=e==null?void 0:e.getBoundingClientRect().height;e.style.top=`-${i}px`}getNumberOfFilesPerRow(){let e=document.querySelector("[data-list_id]:nth-of-type(1)");return e?this.getRemainingRowsOfItemToTheRight(e)+1:0}getRemainingRowsOfItemToTheRight(e){let i=e,o=e.nextElementSibling,a=0;if(o)for(;i.offsetTop===o.offsetTop;)a++,i=o,o=o.nextElementSibling;return a}getRemainingRowsOfItemToTheLeft(e){let i=e,o=e.previousElementSibling,a=0;if(o)for(;i.offsetTop===o.offsetTop;)a++,i=o,o=o.previousElementSibling;return a}navigateEnter(e){e.querySelector(".svg-per-file-loading").classList.remove("display-none"),this.getFileContainerEvent().currentDrive.openFolderHandler(e,this.getFileContainerEvent()).catch(()=>{e.querySelector(".svg-per-file-loading").classList.add("display-none")})}};s(kt,"NavigateFilesByKeyboardKeysHandler");var St=class{constructor(e){this.fileContainerEvent=e,this.handleSearch()}handleSearch(){let e=document.querySelector(k.SEARCH);e.addEventListener("keyup",i=>{if(i.code==="Enter"){let o=e.value;this.getFileContainerEvent().currentDrive.searchFiles(o,this.getFileContainerEvent()).then(()=>{e.value=""}).catch(()=>{O("An Error Occurred While Searching")})}})}getFileContainerEvent(){return this.fileContainerEvent}};s(St,"SearchFilesInFolderHandler");var Tt=class{constructor(e){this.fileContainerEvent=e,this.handleBreadCrumbNavigation()}getFileContainerEvent(){return this.fileContainerEvent}handleBreadCrumbNavigation(){document.querySelector(".breadcrumb").addEventListener("click",i=>{let o=i.target;if(o.hasAttribute("data-pathtrail")){let a=o.getAttribute("data-pathtrail");this.getFileContainerEvent().currentDrive.breadCrumbClickNavigationHandler(a).then(()=>{this.getFileContainerEvent().resetPreviousFilesState()})}}),window.addEventListener("keydown",i=>{switch(i.code){case"Backspace":this.navigateBackSpace();break}})}navigateBackSpace(){let e=document.querySelectorAll(".breadcrumb a"),i=e[e.length-2];if(i){let o=i.dataset.pathtrail;this.getFileContainerEvent().currentDrive.breadCrumbClickNavigationHandler(o).then(()=>{this.getFileContainerEvent().resetPreviousFilesState()})}}};s(Tt,"BreadCrumbHandler");var Lt=class{constructor(e){this.copyLinkEvent=e;let i=e.getCopiedLinkFile().dataset.file_type,o=e.getFileContainerEvent().getLoadDriveEventClass().appURL;i==="file"&&(window.parent.postMessage({mceAction:"execCommand",cmd:"tonics:RegularLink",value:e.copiedLink},o),window.hasOwnProperty("opener")&&window.opener!==null&&window.opener.postMessage({cmd:"tonics:RegularLink",value:e.copiedLink},o))}};s(Lt,"TinymceCopyLinkHandler");var Je=class{get imageFile(){return this._imageFile}constructor(e){this._imageFile=e}};s(Je,"OnImageFileEvent");var Se=class{extensions(){return["jpeg","jpg","jpe","jfi","jif","jfif","png","gif","bmp","webp","apng","avif","svg"]}run(e,i,o=null){if(o&&typeof o=="function"){let a=o(F.FILE.image,i,e);return B(new Je(a),Je),a}}fileContext(e){return`
        ${S(C.Context.Edit.Image,F.FILE.image,w().EDIT_IMAGE_FILE)}
    ${S(C.Context.Rename,F.CONTEXT.edit,w().RENAME_FILE)}
    ${S(C.Context.Link.Copy,F.CONTEXT.link,w().COPY_LINK)}
    ${S(C.Context.Link.Preview,F.CONTEXT.preview_link,w().COPY_PREVIEW_LINK)}
    ${S(C.Context.Cut,F.CONTEXT.cut,w().CUT_FILE)}
    ${S(C.Context.Delete,F.CONTEXT.trash,w().DELETE_FILE)}`}};s(Se,"ImageFilePlacement");var Qe=class{get docFile(){return this._docFile}constructor(e){this._docFile=e}};s(Qe,"OnDocumentFileEvent");var Te=class{extensions(){return["pdf","docx","doc","txt"]}run(e,i,o=null){if(o&&typeof o=="function"){let a;switch(i){case"docx":case"doc":a=o(F.FILE.docx,i,e);break;case"pdf":a=o(F.FILE.pdf,i,e);break;default:a=o(F.FILE.note,i,e)}return B(new Qe(a),Qe),a}}fileContext(e){return`
    ${S(C.Context.Rename,F.CONTEXT.edit,w().RENAME_FILE)}
    ${S(C.Context.Link.Copy,F.CONTEXT.link,w().COPY_LINK)}
    ${S(C.Context.Link.Preview,F.CONTEXT.preview_link,w().COPY_PREVIEW_LINK)}
    ${S(C.Context.Cut,F.CONTEXT.cut,w().CUT_FILE)}
    ${S(C.Context.Delete,F.CONTEXT.trash,w().DELETE_FILE)}`}};s(Te,"DocumentsFilePlacement");var Dt=class{constructor(e){this.copyPreviewLinkEvent=e;let i=new Se,o=new Le,a=new Te,l=e.getCopiedLinkFile().dataset.file_type,d=e.getCopiedLinkFile().dataset.ext,f=this.copyPreviewLinkEvent.getFileContainerEvent().getLoadDriveEventClass().appURL;l==="file"&&(i.extensions().includes(d)&&(window.parent.postMessage({mceAction:"execCommand",cmd:"tonics:ImageLink",value:e.getCopiedLink()},f),window.hasOwnProperty("opener")&&window.opener!==null&&window.opener.postMessage({cmd:"tonics:ImageLink",value:e.getCopiedLink()},f)),a.extensions().includes(d)&&window.hasOwnProperty("opener")&&window.opener!==null&&window.opener.postMessage({cmd:"tonics:DocLink",value:e.getCopiedLink()},f),o.extensions().includes(d)||["mp4","3gp","mov"].includes(d)?(window.parent.postMessage({mceAction:"execCommand",cmd:"tonics:MediaLink",value:e.getCopiedLink()},f),window.hasOwnProperty("opener")&&window.opener!==null&&window.opener.postMessage({cmd:"tonics:MediaLink",value:e.getCopiedLink()},f)):window.parent.postMessage({mceAction:"execCommand",cmd:"tonics:RegularLink",value:e.getCopiedLink()},f))}};s(Dt,"TinymceCopyPreviewLinkHandler");var de=class{get copiedLink(){return this._copiedLink}set copiedLink(e){this._copiedLink=e}constructor(e){this.fileContainerEvent=e,this.copiedLinkFile=e.getSelectedFile()}getFileContainerEvent(){return this.fileContainerEvent}handleCopyLink(e=null){return this.fileContainerEvent.currentDrive.copyLinkHandler(this.fileContainerEvent,e)}onSuccess(e=null){this.copiedLinkFile=this.fileContainerEvent.getSelectedFile(),this.copiedLink=e,this.getFileContainerEvent().removeContextMenu(),H(C.Link.Copy.Download.Success).then(),this.fileContainerEvent.dispatchEventToHandlers(this,de)}onError(e=null){O(C.Link.Copy.Download.Error).then()}getCopiedLinkFile(){return this.copiedLinkFile}};s(de,"CopyLinkEvent");var Ze=class{get audioFile(){return this._audioFile}constructor(e){this._audioFile=e}};s(Ze,"OnAudioIsPlayableEvent");var It=class{constructor(e){let i=e.audioFile;new de(window.TonicsFileManager.events.fileContainerEvent).handleCopyLink(i).then(a=>{i.dataset.audioplayer_play="false",i.dataset.audioplayer_songurl=a,i.dataset.audioplayer_image="",i.dataset.audioplayer_title=i.dataset.filename,i.dataset.audioplayer_format=i.dataset.ext,i.setAttribute("data-tonics-audioplayer-track",""),B(new Ze(i),Ze)})}};s(It,"RegisterAudioFileForAudioPlayerHandler");var he={FileContainerEvent:[ht,vt,wt,bt,Ct,yt,Ft,xt,kt,St,Tt],LoadDriveDataEvent:[],RenameFileEvent:[],UploadFileEvent:[],CutFileEvent:[],PasteFileEvent:[],EditImageFileEvent:[],DeleteFileEvent:[],CopyLinkEvent:[Lt],CopyPreviewLinkEvent:[Dt],RefreshFolderEvent:[],OnAudioFileEvent:[It],OnAudioIsPlayableEvent:[],OnArchiveCompressFileEvent:[],OnCodeFileEvent:[],OnDirectoryFileEvent:[],OnDocumentFileEvent:[],OnImageFileEvent:[],NewFolderEvent:[]};var ve=class{constructor(e){if(e)return this.$handleProvider=e,this}dispatch(e){let i=e.constructor,o=this.getHandler().getEventHandlers(i);for(let a=0;a<o.length;a++)Object.getOwnPropertyNames(o[a]).includes("arguments")?o[a](e):new o[a](e);return e}setHandler(e){return this.$handleProvider=e,this}getHandler(){return this.$handleProvider}dispatchEventToHandlers(e,i,o){let a=ae(e,o);this.setHandler(a).dispatch(i)}};s(ve,"EventDispatcher");window.hasOwnProperty("TonicsEvent")||(window.TonicsEvent={});window.TonicsEvent.EventDispatcher=new ve;function ei(u,e=""){let i="";return u.lastIndexOf("/")!==-1&&(i=u.substring(0,u.lastIndexOf("/"))),u.lastIndexOf("\\")!==-1&&(i=u.substring(0,u.lastIndexOf("\\"))),i||e}s(ei,"getFileDirectory");function Pt(u){return u.toLowerCase().replace(/\b(\w)/g,function(e){return e.toLocaleUpperCase()})}s(Pt,"titleCase");function ne(u){return new Promise((e,i)=>{navigator.clipboard.writeText(u).then(()=>{e(u)}).catch(o=>{i(o)})})}s(ne,"copyToClipBoard");function ti(u,e=!1){let i,o,a=document.createElement("a");return a.style.display="none",a.setAttribute("href",u),u=u.replace(a.protocol,""),u=u.replace(a.hostname,""),u=u.replace(":"+a.port,""),u=u.split("?")[0],u=u.split("#")[0],u=u.substr(1+u.lastIndexOf("/")),i=u,!e&&i.startsWith(".")||i.lastIndexOf(".")===-1?"":(o=i.substr(1+i.lastIndexOf(".")),o)}s(ti,"getFileExtension");function S(u,e,i){return`
<li class="context-menu-item" data-menu-action=${i}>
      ${e}
      <a class="" href="javascript:void(0);">
        ${u}
      </a>
    </li>
`}s(S,"contextMenuListCreator");function Ot(u,e="",i="text"){return Y.default.fire({title:u,input:i,inputValue:e,inputAttributes:{autocapitalize:"off"},showCancelButton:!0,confirmButtonText:"Save",backdrop:!0,allowOutsideClick:()=>!Y.default.isLoading(),confirmButtonColor:"#0c132c",focusConfirm:!0,background:"#eaeaea",iconColor:"#264762d1"})}s(Ot,"inputToast");function H(u,e=4e3){return Y.default.mixin({toast:!0,position:"bottom-right",showConfirmButton:!1,timer:e,timerProgressBar:!0,background:"#eaeaea",iconColor:"#264762d1",didOpen:o=>{o.addEventListener("mouseenter",Y.default.stopTimer),o.addEventListener("mouseleave",Y.default.resumeTimer)}}).fire({customClass:{title:"swal2-title-dark"},icon:"success",title:u})}s(H,"successToast");function O(u,e=5e3){return Y.default.mixin({toast:!0,position:"bottom-right",showConfirmButton:!1,timer:e,timerProgressBar:!0,background:"#eaeaea",iconColor:"#941943",didOpen:o=>{o.addEventListener("mouseenter",Y.default.stopTimer),o.addEventListener("mouseleave",Y.default.resumeTimer)}}).fire({customClass:{title:"swal2-title-red"},icon:"error",title:u})}s(O,"errorToast");function ni(u,e="Proceed",i,o=null,a=null){Y.default.mixin({toast:!0,position:"bottom-right",timer:5e4,timerProgressBar:!0,showCancelButton:!0,showConfirmButton:!0,confirmButtonText:e,confirmButtonColor:"#0c132c",focusConfirm:!0,background:"#eaeaea",iconColor:"#264762d1",didOpen:d=>{d.addEventListener("mouseenter",Y.default.stopTimer),d.addEventListener("mouseleave",Y.default.resumeTimer)}}).fire({title:u}).then(d=>{d.isConfirmed?typeof i=="function"&&i():d.isDenied?o&&typeof o=="function"&&o():d.isDismissed&&a&&typeof a=="function"&&a()})}s(ni,"promptToast");function De(u){let e=document.querySelector(k.HEAD.MENU_SECTION);u.forEach(function(i,o){let a=e.querySelector(`[data-menu-action="${i}"]`);a&&(a.closest(".menu-item").classList.remove("deactivate-menu-pointer"),a.querySelector(".icon").classList.remove("deactivate-menu"))})}s(De,"activateMenus");function an(u){let e=document.querySelector(k.HEAD.MENU_SECTION);u.forEach(function(i,o){let a=e.querySelector(`[data-menu-action="${i}"]`);a&&(a.closest(".menu-item").classList.add("deactivate-menu-pointer"),a.querySelector(".icon").classList.add("deactivate-menu"))})}s(an,"deActivateMenus");function _t(u,e){return new Promise((i,o)=>{if(document.querySelector(`[data-script_id="${e}"]`))i();else{let l=document.createElement("script");l.dataset.script_id=e,document.body.appendChild(l),l.onload=i,l.onerror=o,l.async=!0,l.src=u}})}s(_t,"loadScriptDynamically");function ln(u){let e="";return u.forEach((i,o)=>{let a=document.querySelector(`input[name=${i}]`);a&&(e=a.value)}),e}s(ln,"getCSRFFromInput");function re(u=!0,e=!1){let i=document.querySelector(k.Button.FILE_LOAD_MORE),o=document.querySelector(".dot-elastic.loading");u?(i==null||i.classList.remove("display-none"),i==null||i.classList.add("display-flex")):(i==null||i.classList.remove("display-flex"),i==null||i.classList.add("display-none")),e?o.classList.remove("display-none"):o.classList.add("display-none")}s(re,"fileLoadMoreButton");function cn(u=!0){let e=document.querySelector(k.FILES.FILE_PARENT),i=document.querySelector(".dot-elastic.loading");u?document.querySelector("[data-list_id]:nth-of-type(1)")||(e==null||e.classList.remove("align-content-fs"),i.classList.remove("display-none")):(e==null||e.classList.add("align-content-fs"),i.classList.add("display-none"))}s(cn,"filesLoadingAnimation");function B(u,e){let i=ae(he,e);new ve().setHandler(i).dispatch(u)}s(B,"dispatchEventToHandlers");var et=class{get audioFile(){return this._audioFile}constructor(e){this._audioFile=e}};s(et,"OnAudioFileEvent");var Le=class{extensions(){return["mp3","wav","tiff","ogg","webm","aac","flac"]}run(e,i,o=null){if(o&&typeof o=="function"){let a=o(F.FILE.music,i,e);return B(new et(a),et),a}}fileContext(e){return`
    ${S(C.Context.Rename,F.CONTEXT.edit,w().RENAME_FILE)}
    ${S(C.Context.Link.Copy,F.CONTEXT.link,w().COPY_LINK)}
    ${S(C.Context.Link.Preview,F.CONTEXT.preview_link,w().COPY_PREVIEW_LINK)}
    ${S(C.Context.Cut,F.CONTEXT.cut,w().CUT_FILE)}
    ${S(C.Context.Delete,F.CONTEXT.trash,w().DELETE_FILE)}`}};s(Le,"AudioFilePlacement");var tt=class{get dirFile(){return this._dirFile}constructor(e){this._dirFile=e}};s(tt,"OnDirectoryFileEvent");var Mt=class{extensions(){return["null",null]}run(e,i,o=null){if(o&&typeof o=="function"){let a=o(F.FILE.folder,i,e);return B(new tt(a),tt),a}}fileContext(e){let i="";return e.cutFile.length>0&&(i=S(C.Context.Paste,F.CONTEXT.paste,w().PASTE_FILE)),`
    ${S(C.Context.Rename,F.CONTEXT.edit,w().RENAME_FILE)}
    ${S(C.Context.Cut,F.CONTEXT.cut,w().CUT_FILE)}
     ${i}
    ${S(C.Context.Delete,F.CONTEXT.trash,w().DELETE_FILE)}`}getCutFiles(){return document.querySelectorAll('[data-cut="true"]')}};s(Mt,"DirectoryFilePlacement");var nt=class{get archiveFile(){return this._archiveFile}constructor(e){this._archiveFile=e}};s(nt,"OnArchiveCompressFileEvent");var At=class{extensions(){return["zip","tar","gz","rar","7z","bz2","xz","wim"]}fileContext(e){return`
    ${S(C.Context.Rename,F.CONTEXT.edit,w().RENAME_FILE)}
    ${S(C.Context.Link.Copy,F.CONTEXT.link,w().COPY_LINK)}
    ${S(C.Context.Link.Preview,F.CONTEXT.preview_link,w().COPY_PREVIEW_LINK)}
    ${S(C.Context.Cut,F.CONTEXT.cut,w().CUT_FILE)}
    ${S(C.Context.Delete,F.CONTEXT.trash,w().DELETE_FILE)}`}run(e,i,o){if(o&&typeof o=="function"){let a;switch(i){case"zip":a=o(F.FILE.zip,i,e);break;default:a=o(F.FILE.compress,i,e)}return B(new nt(a),nt),a}}};s(At,"ArchiveCompressFilePlacement");var it=class{get codeFile(){return this._codeFile}constructor(e){this._codeFile=e}};s(it,"OnCodeFileEvent");var Ht=class{extensions(){return["php","js","css","bat","nim","cs","sql","ts","sh","rb","pyo","pl","o","lua","kt"]}fileContext(e){return`
    ${S(C.Context.Rename,F.CONTEXT.edit,w().RENAME_FILE)}
    ${S(C.Context.Link.Copy,F.CONTEXT.link,w().COPY_LINK)}
    ${S(C.Context.Link.Preview,F.CONTEXT.preview_link,w().COPY_PREVIEW_LINK)}
    ${S(C.Context.Cut,F.CONTEXT.cut,w().CUT_FILE)}
    ${S(C.Context.Delete,F.CONTEXT.trash,w().DELETE_FILE)}`}run(e,i,o){if(o&&typeof o=="function"){let a=o(F.FILE.code,i,e);return B(new it(a),it),a}}};s(Ht,"CodeFilePlacement");var xe={FileByExtensions:[new Le,new At,new Ht,new Se,new Mt,new Te]};var ot=class{get data(){return this._data}set data(e){this._data=e}constructor(e,i,o=!1){this.commands=e,this.data=i,this.append=o}getCommands(){return this.commands}placeFileByExtension(){let e=this,i=this.getCommands().getList();if(!this.append)try{document.querySelector(k.FILES.FILE_CONTAINER).replaceChildren()}catch(l){console.error(l)}let o,a=this.data;for(o in a){let l=a[o],d;document.querySelector("[data-list_id]:last-of-type")?d=parseInt(document.querySelector("[data-list_id]:last-of-type").dataset.list_id)+1:d=o,l.list_id=d,l.properties=JSON.parse(l.properties);let f=!1,m=l.properties.ext;i.forEach((v,h)=>{typeof v.extensions=="function"&&typeof v.run=="function"&&v.extensions().includes(m)&&(f=!0,v.run(l,m,function(y,b,E){return e.createFile(y,b,E)}))}),f||new le().run(l,m,function(v,h,y){return e.createFile(v,h,y)})}}createFile(e,i,o){let a=document.querySelector(k.FILES.FILE_CONTAINER),l=`
        <li class="tonics-file" 
                    data-list_id="${o.list_id}"
                    data-drive_id="${o.drive_id}"
                    data-drive_parent_id="${o.drive_parent_id}"
                    data-drive_unique_id="${o.drive_unique_id}"
                    data-filename="${o.filename}" 
                    data-file_type="${o.type}"
                    data-size="${o.properties.size}"
                    data-file_path="${o.filepath}"
                    data-time_created="${o.properties.time_created}"
                    data-time_modified="${o.properties.time_modified}"
                    data-ext="${i}">
          <button class="tonics-fm-link remove-button-styles">
           ${e}
            <div class="tonics-file-filename">
              <input class="tonics-file-filename-input" type="text" value="${o.filename}" readonly="" aria-label="${o.filename}">
            </div>
            <span class="svg-per-file-loading display-none"></span>
          </button>
        </li>
        `;return a==null||a.insertAdjacentHTML("beforeend",l),a==null?void 0:a.lastElementChild}};s(ot,"LocalFileExtensionsCommands");var ie="/api/media/",M;(function(E){E[E.GetFiles=ie+"files"]="GetFiles",E[E.GetFileFromPath=ie+"files?path="]="GetFileFromPath",E[E.SearchFileFromPath=ie+"files/search?path="]="SearchFileFromPath",E.IDQuery="&id=",E.SearchQuery="&query=",E[E.MoveFiles=ie+"files/move"]="MoveFiles",E[E.PreFlight=ie+"files/preflight"]="PreFlight",E[E.DeleteFiles=ie+"files"]="DeleteFiles",E[E.PostFiles=ie+"files"]="PostFiles",E[E.RenameFile=ie+"files/rename"]="RenameFile",E.ServeFile="/serve_file_path_987654321/",E[E.CreateFolder=ie+"files/create_folder"]="CreateFolder",E[E.CancelUpload=ie+"files/cancel_create"]="CancelUpload"})(M||(M={}));var Rt=class{constructor(e,i,o){this._fetchInfo={lastStatus:{ok:!1,status:0,statusText:"",response:null}};this.driveSignature=e,this.bearerToken=i,this.setFqdn(o)}get currentPathID(){return this._currentPathID}set currentPathID(e){this._currentPathID=e}get appendNewFiles(){return this._appendNewFiles}set appendNewFiles(e){this._appendNewFiles=e}get filesFolderNextPageUrl(){return this._filesFolderNextPageUrl}set filesFolderNextPageUrl(e){this._filesFolderNextPageUrl=e}get driveSignature(){return this._driveSignature}set driveSignature(e){this._driveSignature=e}get currentDirectoryID(){return this._currentDirectoryID}set currentDirectoryID(e){this._currentDirectoryID=e}get fetchInfo(){return this._fetchInfo}get bearerToken(){return this._bearerToken}set bearerToken(e){this._bearerToken=e}get fqdn(){return this._fqdn}set fqdn(e){this._fqdn=e}get storageData(){return this._storageData}set storageData(e){this._storageData=e}getMaxRequestToSend(){return 4}getDriveIcon(){return"#tonics-hdd"}getDriveName(){return"local"}coldBootStorageDisk(){let e=this;return new Promise(function(i,o){let a=`${e.fqdn}${M.GetFiles}`,l=e.fetchFileData(a);e.storageData=l,l.then(d=>{e.createIndividualDataElement(d),i()}).catch(()=>o())})}createIndividualDataElement(e){let i=new Fe(xe.FileByExtensions),o=e.data;if(this.currentPathID=e.more.drive_id,e.more.has_more===!0?(this.filesFolderNextPageUrl=e.more.next_page_url,re()):re(!1,!1),this.appendNewFiles)return new ot(i,o,!0).placeFileByExtension();this.addCrumbNavigationPathTrail(e.more),this.currentDirectoryID=e.more.drive_id,new ot(i,o).placeFileByExtension()}loadMoreFiles(e){return new Promise((i,o)=>{let a=`${this.fqdn}${this.filesFolderNextPageUrl}`;this.fetchFileData(a).then(l=>{this.appendNewFiles=!0,this.createIndividualDataElement(l),this.appendNewFiles=!1,i()})})}searchFiles(e,i){return new Promise((o,a)=>{let l=encodeURIComponent(i.getCurrentDirectory()),d=`${M.SearchFileFromPath}${l}${M.IDQuery}${this.currentPathID}${M.SearchQuery}${encodeURIComponent(e)}`,f=`${this.fqdn}${d}`;this.fetchFileData(f).then(m=>{this.createIndividualDataElement(m),o()})})}addCrumbNavigationPathTrail(e){var d;let o=e.current_path.split("/");o=o.filter(String);let a=document.querySelector(".breadcrumb");a.innerHTML="Navigating:  ";let l="";o.forEach((f,m,v)=>{l=l.concat(`/${f}`);let h=Pt(f);a==null||a.insertAdjacentHTML("beforeend",`<a data-pathtrail="${l}" data-filename="${f}" 
href="javascript:void(0);">${h}</a><span class="delimiter"> \xBB </span>`)}),(d=a==null?void 0:a.lastElementChild)==null||d.remove()}breadCrumbClickNavigationHandler(e){return new Promise((i,o)=>{let a=encodeURIComponent(e),l=`${this.fqdn}${M.GetFileFromPath}${a}`;this.fetchFileData(l).then(d=>{this.createIndividualDataElement(d),i()}).catch(()=>o())})}createDirectoryElement(e){let i=document.createElement("li");return i.dataset.drive_id=this.currentDirectoryID,i.dataset.file_path=e,i.dataset.file_type="directory",i.dataset.ext="null",i}refresh(e){let i=this;return new Promise(function(o,a){let l=i.createDirectoryElement(e.getCurrentDirectory());i.openFolderHandler(l,e).then(function(){o()})})}createFolder(e,i){let o=this;return new Promise(function(a,l){let d={filename:i.name,uploadTo:e.getCurrentDirectory(),uploadToID:o.currentDirectoryID},f=`${o.fqdn}${M.CreateFolder}`;o.defaultXHR({}).Post(f,JSON.stringify(d),function(v,h){v&&(v=JSON.parse(v),l()),h&&(h=JSON.parse(h),h.hasOwnProperty("status")&&h.status==200&&a())})})}openFolderHandler(e,i){let o=this;return new Promise(function(a,l){if(e.dataset.file_type=="directory"&&e.dataset.ext=="null"){let d=encodeURIComponent(e.dataset.file_path),f=e.dataset.drive_id,m=`${o.fqdn}${M.GetFileFromPath}${d}${M.IDQuery}${f}`;o.fetchFileData(m).then(function(v){o.createIndividualDataElement(v),a()})}})}getStorageFileData(){return this.storageData}defaultXHR(e={}){let i={Authorization:`Bearer ${this.bearerToken}`,"Tonics-CSRF-Token":`${ln(["tonics_csrf_token","csrf_token"])}`};return new We(rn(rn({},i),e))}headers(){let e=this.bearerToken;return new Headers([["Content-Type","application/json; charset=utf-8"],["Authorization",`Bearer ${e}`],["Tonics-CSRF-Token",`${ln(["tonics_csrf_token","csrf_token"])}`]])}fetchFileData(e){return $(this,null,function*(){let i=this,o=this.headers(),a=new Request(e,{method:"GET",headers:o,cache:"default",mode:"cors"});return new Xe(a).run().then(function(l){return i.processResponse(l)}).then(function(l){if(i.fetchInfo.lastStatus.response=l,i.fetchInfo.lastStatus.ok)return l;console.log(i.fetchInfo.lastStatus)})})}uploadFile(e,i,o){let a=e.fileObject,l=e.preFlightData,d=this;if(l.dataToFill!==null)l.dataToFill.length>0&&!e.uploaded&&(e.pause||d.throttleSend(e,i,o));else{let f=this.defaultXHR({UploadTo:e.uploadTo,Filename:a.name,Filetype:a.type,Chunkstosend:l.chunksToSend,Totalblobsize:l.Totalblobsize,Byteperchunk:l.Byteperchunk}),m=`${this.fqdn}${M.PreFlight}`;f.Get(m,function(v,h){h&&(e.fetched=!0,e.preFlightData.dataToFill=JSON.parse(h).data,e.preFlightData.filename=JSON.parse(h).more.filename,o.setUploadFileObject(i,e),l.dataToFill.length>0&&!e.uploaded&&(e.pause||d.throttleSend(e,i,o)))})}}throttleSend(e,i,o){let a=this,l=e.preFlightData,d=e.preFlightData.dataToFill,f=e.fileObject;for(let m=l.nextIndex,v=d.length;m<v;m++)if(d){let h=d[m],y=JSON.parse(h.moreBlobInfo),b=f.slice(y.startSlice,y.endSlice),E={id:h.id,filename:e.preFlightData.filename,filetype:f.type,uploadTo:e.uploadTo,uploadToID:a.currentDirectoryID,chunkPart:h.blob_chunk_part,chunkSize:h.blob_chunk_size,mbRate:4*1048576,totalChunks:l.chunksToSend,totalBlobSize:f.size,startSlice:y.startSlice,newFile:e.newFile};if(l.throttleSwitch)break;e.preFlightData.sentApi=e.preFlightData.sentApi+1,o.setUploadFileObject(i,e),a.uploadBlob(b,E,i,o),e.preFlightData.nextIndex=e.preFlightData.nextIndex+1,o.setUploadFileObject(i,e)}}uploadBlob(e,i,o,a){let l=this,d=this.bearerToken,f=this.defaultXHR({BlobDataInfo:JSON.stringify(i)}),m=a.getUploadFileObject(o,this.driveSignature);m.preFlightData.sentApi>=m.preFlightData.maxRequestToSend&&(m.preFlightData.throttleSwitch=!0,a.$uploadedFilesObject.set(o,m));let v=`${this.fqdn}${M.PostFiles}`;f.Post(v,e,function(h,y){if(y){let b=JSON.parse(y),E=Math.round(b.data.uploadPercentage);return m.preFlightData.noOfReceivedResponse=m.preFlightData.noOfReceivedResponse+1,a.setUploadFileObject(o,m),b.data.isUploadCompleted||a.updateFileProgress(o,E,l.driveSignature),m.preFlightData.noOfReceivedResponse===m.preFlightData.maxRequestToSend&&(a.releaseThrottle(o,l.driveSignature),!b.data.isUploadCompleted&&!m.uploaded&&l.uploadFile(m,o,a)),m.uploaded||b.data.isUploadCompleted&&(a.setUploadCompleted(o,l.driveSignature),a.isUploadingSequentially()&&(a.setSequenceDone(o,l.driveSignature),a.uploadFileNextSequence(l.driveSignature))),b}})}cancelFileUploadHandler(e,i){let o=this;e.pause=!0,e.uploaded=!0,e.preFlightData.throttleSwitch=!0;let a=this.driveSignature+"_"+e.fileObject.name;return i.setUploadFileObject(a,e),new Promise(function(l,d){let f=e.preFlightData,m={filename:e.preFlightData.filename,totalChunks:f.chunksToSend,uploadTo:e.uploadTo,totalBlobSize:e.fileObject.size},v=o.defaultXHR({}),h=JSON.stringify(m),y=`${o.fqdn}${M.CancelUpload}`;v.Delete(y,h,function(b,E){b&&d(),E&&(E=JSON.parse(E),E.hasOwnProperty("status")&&E.status==200&&l(E.message))})})}renameFileHandler(e,i){let o=this;return new Promise(function(a,l){let d=e.querySelector(".tonics-file-filename-input");e.dataset.filename_new=d.value;let f=JSON.stringify(e.dataset),m=o.headers(),v=`${o.fqdn}${M.RenameFile}`,h=new Request(v,{method:"PUT",headers:m,cache:"default",mode:"cors",body:f});new Xe(h).run().then(function(b){return o.processResponse(b)}).then(function(b){if(b.status>200)return l();if(b.hasOwnProperty("data")){d.style.width=175+"px";let E=b.data;return d.value=E.filename,d.ariaLabel=E.filename,e.dataset.drive_id=E.drive_id,e.dataset.filename=E.filename,e.dataset.file_path=E.file_path,e.dataset.time_modified=E.time_modified,a(e)}})})}editImageHandler(e){let i=this;return new Promise(function(o,a){let l=e.getFileContainerEvent().getSelectedFile(),d=`${i.fqdn}${M.ServeFile}`+l.dataset.drive_unique_id+"?render",f={translations:{en:{"toolbar.save":"Save","toolbar.apply":"Apply","toolbar.download":"Save Changes"}}};if(l){let m=s(function(h){h.canvas.toBlob(function(y){let b=new File([y],l.dataset.filename),E=e.getFileContainerEvent().getCurrentDirectory(),P=e.byteToSendPerChunk,_={fileObject:b,driveSignature:e.getFileContainerEvent().currentDrive.driveSignature,preFlightData:{filename:b.name,dataToFill:null,chunksToSend:Math.ceil(b.size/P),Byteperchunk:P,Totalblobsize:b.size,maxRequestToSend:e.maxRequestToSend,noOfReceivedResponse:0,throttleSwitch:!1,sentApi:0,nextIndex:0},newFile:!1,uploadTo:E,fetched:!1,pause:!1,uploaded:!1};e.updateFileProgress(b.name,0,i.driveSignature," \u22EF Updating"),e.setUploadFileObject(b.name,_),i.uploadFile(_,b.name,e)})},"onBeforeComplete");new FilerobotImageEditor(f,{onBeforeComplete:m}).open(d)}})}copyLinkHandler(e,i){let o=this;return new Promise((a,l)=>{i===null&&(i=e.getSelectedFile());let d=`${o.fqdn}${M.ServeFile}`+i.dataset.drive_unique_id;return ne(d).then(()=>{a(d)}).catch(()=>{l()})})}copyPreviewLinkHandler(e,i){let o=this;return new Promise((a,l)=>{i===null&&(i=e.getSelectedFile());let d=`${o.fqdn}${M.ServeFile}`+i.dataset.drive_unique_id+"?render";return ne(d).then(()=>{a(d)}).catch(()=>{l()})})}moveFileHandler(e,i){let o=this;return new Promise(function(a,l){let d=[],f=e.dataset;f.hasOwnProperty("drive_id")||(f.drive_id=o._currentDirectoryID),i.getCutFiles().forEach(y=>{if(y.dataset.drive_id==f.drive_id)throw l(`Destination Folder \`${f.filename}\` is a subfolder of the source folder`),new DOMException(`Destination Folder \`${f.filename}\` is a subfolder of the source folder, you can't paste the same folder into the same folder`);d.push(y.dataset)});let m=JSON.stringify({files:d,destination:f}),v=o.defaultXHR({}),h=`${o.fqdn}${M.MoveFiles}`;v.Put(h,m,function(y,b){if(y)return y=JSON.parse(y),l();b&&(b=JSON.parse(b),b.hasOwnProperty("status")&&b.status==200&&o.refresh(i).then(function(){a()}))})})}deleteFileHandler(e,i){let o=this;return new Promise(function(a,l){let d=o.defaultXHR({}),f=JSON.stringify({files:e}),m=`${o.fqdn}${M.DeleteFiles}`;d.Delete(m,f,function(v,h){v&&l(),h&&(h=JSON.parse(h),h.hasOwnProperty("status")&&h.status==200&&o.refresh(i).then(function(){a(h.message)}))})})}processResponse(e){return $(this,null,function*(){return this.fetchInfo.lastStatus.ok=e.ok,this.fetchInfo.lastStatus.status=e.status,this.fetchInfo.lastStatus.statusText=e.statusText,this.fetchInfo.lastStatus.response=null,this.fetchInfo.lastStatus.ok?yield e.json():yield e.json()})}setFqdn(e){let i=this.isValidURL(e);if(!i)throw new DOMException(`${e} is not a valid domain address, an example of a valid domain is https://google.com`);this.fqdn=i}isValidURL(e){let i=document.createElement("input");return i.setAttribute("type","url"),i.value=e,i.validity.valid?e:!1}};s(Rt,"LocalDiskDrive");var Ie=class{constructor(e,i,o=!1){this.commands=e,this.fileData=i,this.append=o}getCommands(){return this.commands}getFileData(){return this.fileData}placeFileByExtension(){let e=this,i=this.getCommands().getList();this.append||document.querySelectorAll(k.FILES.SINGLE_FILE).forEach(l=>l.remove());let o,a=this.getFileData();for(o in a){let l=a[o],d;l.hasOwnProperty("metadata")&&(l=l.metadata.metadata),document.querySelector("[data-list_id]:last-of-type")?d=parseInt(document.querySelector("[data-list_id]:last-of-type").dataset.list_id)+1:d=o,l.list_id=d;let f=!1,m=ti(l.name);i.forEach((v,h)=>{typeof v.extensions=="function"&&typeof v.run=="function"&&(l[".tag"]==="folder"?v.extensions().includes(null)&&(f=!0,v.run(l,"null",function(y,b,E){return e.createFile(y,b,E)})):v.extensions().includes(m)&&(f=!0,v.run(l,m,function(y,b,E){return e.createFile(y,b,E)})))}),f||new le().run(l,m,function(v,h,y){return e.createFile(v,h,y)})}}createFile(e,i,o){let a=document.querySelector(k.FILES.FILE_CONTAINER),l="",d="";o.hasOwnProperty("size")&&(l=o.size),o.hasOwnProperty("server_modified")&&(d=o.server_modified);let f=o[".tag"]==="folder"?"directory":o[".tag"],m=`
        <li class="tonics-file" 
                    data-list_id="${o.list_id}"
                    data-drive_id="${o.id}"
                    data-filename="${o.name}" 
                    data-file_type="${f}"
                    data-size="${l}"
                    data-file_path="${o.path_lower}"
                    data-time_modified="${d}"
                    data-ext="${i}">
          <button class="tonics-fm-link remove-button-styles">
           ${e}
            <div class="tonics-file-filename">
              <input onkeyup="event.preventDefault()" class="tonics-file-filename-input" type="text" value="${o.name}" readonly="" aria-label="${o.name}">
            </div>
            <span class="svg-per-file-loading display-none"></span>
          </button>
        </li>
        `;return a==null||a.insertAdjacentHTML("beforeend",m),a==null?void 0:a.lastElementChild}};s(Ie,"DropboxFileExtensionCommands");var Pe={FileRobotImageEditor:{ID:"filerobot-image-editor",PATH:"/serve_module_file_path_987654321/Core?path=/js/media/filerobot-image-editor.js"},DropboxSDK:{ID:"dropbox-sdk",PATH:"/serve_module_file_path_987654321/Core?path=/js/media/dropbox.min.js"}};var Nt=class{constructor(e="",i=""){this._sharedLink=new Map;e&&(this.driveSignature=e),i&&(this.accessToken=i)}get isSearch(){return this._isSearch}set isSearch(e){this._isSearch=e}get driveSignature(){return this._driveSignature}set driveSignature(e){this._driveSignature=e}get accessToken(){return this._accessToken}set accessToken(e){this._accessToken=e}get sharedLink(){return this._sharedLink}get storageData(){return this._storageData}set storageData(e){this._storageData=e}get nextCursor(){return this._nextCursor}set nextCursor(e){this._nextCursor=e}get appendNewFiles(){return this._appendNewFiles}set appendNewFiles(e){this._appendNewFiles=e}getMaxRequestToSend(){return 1}getDriveIcon(){return"#tonics-dropbox"}getDriveName(){return"Dropbox"}coldBootStorageDisk(){let e=this;return new Promise(function(i,o){_t(Pe.DropboxSDK.PATH,Pe.DropboxSDK.ID).then(function(){e.listFolder("").then(()=>{i()}).catch(()=>{o()})}.bind(e))})}addCrumbNavigationPathTrail(e){var d;let i=e.split("/"),o=document.querySelector(".breadcrumb"),a="",l="";o.innerHTML="Navigating:  ",i.forEach(f=>{a=a.concat(`/${f}`),f?l=Pt(f):(a="",l="Root"),o==null||o.insertAdjacentHTML("beforeend",`<a data-pathtrail="${a}" data-filename="${f}"  href="javascript:void(0);">${l}</a><span class="delimiter"> \xBB </span>`)}),(d=o==null?void 0:o.lastElementChild)==null||d.remove()}getStorageFileData(){return this.storageData}createIndividualDataElement(e){let i=new Fe(xe.FileByExtensions);if(e.result.has_more?(this.nextCursor=e.result.cursor,re()):re(!1,!1),e.result.hasOwnProperty("matches")){this.isSearch=!0;let o=e.result.matches;return new Ie(i,o).placeFileByExtension()}else{this.isSearch=!1;let o=e.result.entries;return this.appendNewFiles?new Ie(i,o,!0).placeFileByExtension():new Ie(i,o).placeFileByExtension()}}listFolder(e=""){let i=this;return new Promise((o,a)=>{let d=i.getDropbox().filesListFolder({path:e,include_media_info:!0,recursive:!1,limit:20});i.storageData=d,d.then(f=>{i.addCrumbNavigationPathTrail(e),i.createIndividualDataElement(f),o()}).catch(function(f){console.log(f),a(f)})})}loadMoreFiles(e){return new Promise((i,o)=>{this.isSearch&&this.getDropbox().filesSearchContinueV2({cursor:this.nextCursor}).then(a=>{this.appendNewFiles=!0,this.createIndividualDataElement(a),this.appendNewFiles=!1,i()}),this.getDropbox().filesListFolderContinue({cursor:this.nextCursor}).then(a=>{this.appendNewFiles=!0,this.createIndividualDataElement(a),this.appendNewFiles=!1,i()})})}searchFiles(e,i){return new Promise((o,a)=>{this.getDropbox().filesSearchV2({query:e}).then(l=>{this.createIndividualDataElement(l),o()})})}cancelFileUploadHandler(e,i){e.pause=!0,e.uploaded=!0,e.preFlightData.throttleSwitch=!0;let o=this.driveSignature+"_"+e.fileObject.name;return i.setUploadFileObject(o,e),new Promise(function(a,l){localStorage.getItem(o)&&(localStorage.removeItem(o),a()),l()})}moveFileHandler(e,i){let o=this,a=[],l=e.dataset;return i.getCutFiles().forEach(d=>{if(d.dataset.drive_id==l.drive_id)throw new DOMException(`Destination Folder \`${l.filename}\` is a subfolder of the source folder, you can't paste the same folder into the same folder`);{let f={from_path:d.dataset.file_path,to_path:l.file_path+d.dataset.file_path};l.file_path||(f={from_path:d.dataset.file_path,to_path:"/"+d.dataset.filename}),a.push(f)}}),new Promise(function(d,f){let m=o.getDropbox();m.filesMoveBatchV2({entries:a,autorename:!0,allow_ownership_transfer:!0}).then(function(v){return v}).then(function(v){m.filesMoveBatchCheckV2({async_job_id:v.result.async_job_id}).then(h=>{console.log(h),d("Move Operation is Progressing, Refresh After Few Seconds (Might Take Longer)")})}).catch(function(v){console.log(v),f()})})}renameFileHandler(e,i){let o=this;return new Promise(function(a,l){let d=e.querySelector(".tonics-file-filename-input"),f=e.dataset.file_path,m=ei(f,"/"),v=e.dataset.ext,h=d.value,y=m+h;m!=="/"&&(y=m+"/"+h),f.endsWith(v)&&(h.endsWith(v)?y=m+h:y=m+h+"."+v),o.getDropbox().filesMoveV2({from_path:f,to_path:y}).then(function(E){if(E){d.style.width=175+"px";let P=E.result.metadata;d.value=P.name,e.dataset.filename=P.name,e.dataset.file_path=P.path_lower,e.dataset.time_modified=P.server_modified,a(e)}}).catch(function(E){console.log(E),l()})})}editImageHandler(e){let i=this;return new Promise(function(o,a){let l=e.getFileContainerEvent().getSelectedFile();if(l){let d=i.getDropbox();d.filesGetTemporaryLink({path:l.dataset.file_path}).then(function(m){return m.result.link}).then(function(m){let v={translations:{en:{"toolbar.save":"Save","toolbar.apply":"Apply","toolbar.download":"Save Changes"}}},h=s(function(b){b.canvas.toBlob(function(E){let P=new File([E],l.dataset.filename);d.filesUpload({path:l.dataset.file_path,contents:P,mode:"overwrite"}).then(function(_){e.updateFileProgress(P.name,100,i.driveSignature," \u2713 Updated")}).catch(function(_){console.log(_),a()})})},"onBeforeComplete");new FilerobotImageEditor(v,{onBeforeComplete:h}).open(m)}).catch(function(m){a()})}})}deleteFileHandler(e,i){let o=this,a=[];return e.forEach(function(l,d,f){let m={path:l.file_path};a.push(m)}),new Promise(function(l,d){o.getDropbox().filesDeleteBatch({entries:a}).then(m=>{m.status!==200&&d(),l("File(s) Deletion Is In Progress, Refresh After Few Seconds (Might Take Longer)")}).catch(function(m){console.log(m),d()})})}openFolderHandler(e,i){let o=this;return new Promise(function(a,l){e.dataset.file_type==="directory"&&o.listFolder(e.dataset.file_path).then(function(){a()}).catch(function(d){console.log(d),l()})})}parseDropboxSharedLink(e,i="?dl=1"){let o=document.createElement("a");return o.href=e,o.origin+o.pathname+i}copyLinkHandler(e,i){let o=this;return new Promise((a,l)=>$(this,null,function*(){let d=o.getDropbox();i===null&&(i=e.getSelectedFile());let f=i.dataset.file_path,m;if(m=o.sharedLink.get(f)){let v=o.parseDropboxSharedLink(m);yield ne(v).then(()=>{a(v)})}else d.sharingCreateSharedLinkWithSettings({path:f,settings:{audience:"public",access:"viewer",requested_visibility:"public",allow_download:!0}}).then(h=>$(this,null,function*(){let y=o.parseDropboxSharedLink(h.result.url);o.sharedLink.set(f,h.result.url),yield ne(y).then(()=>{a(y)})})).catch(function(h){return h.error||l(),h.error}).then(h=>{h.error&&h.error[".tag"]==="shared_link_already_exists"&&d.sharingListSharedLinks({path:f,direct_only:!0}).then(b=>$(this,null,function*(){let E=o.parseDropboxSharedLink(b.result.links[0].url);o.sharedLink.set(f,b.result.links[0].url),yield ne(E).then(()=>{o.sharedLink.set(f,b.result.links[0].url),a(E)})}))}).catch(()=>{l()})}))}copyPreviewLinkHandler(e,i){let o=this;return new Promise((a,l)=>$(this,null,function*(){let d=o.getDropbox();i===null&&(i=e.getSelectedFile());let f=i.dataset.file_path,m;if(m=o.sharedLink.get(f)){let v=o.parseDropboxSharedLink(m,"?raw=1");yield ne(v).then(()=>a(v))}else d.sharingCreateSharedLinkWithSettings({path:f,settings:{audience:"public",access:"viewer",requested_visibility:"public",allow_download:!0}}).then(h=>$(this,null,function*(){let y=o.parseDropboxSharedLink(h.result.url,"?raw=1");o.sharedLink.set(f,h.result.url),yield ne(y).then(()=>{a(y)})})).catch(h=>(h.error||l(),h.error)).then(h=>{h.error&&h.error[".tag"]==="shared_link_already_exists"&&d.sharingListSharedLinks({path:f,direct_only:!0}).then(b=>$(this,null,function*(){let E=o.parseDropboxSharedLink(b.result.links[0].url,"?raw=1");o.sharedLink.set(f,b.result.links[0].url),yield ne(E).then(()=>{o.sharedLink.set(f,b.result.links[0].url),a(E)})}))}).catch(()=>{l()})}))}createFolder(e,i){let o=this;return new Promise(function(a,l){let d=o.getDropbox(),f=e.getCurrentDirectory()+"/"+i.name;d.filesCreateFolderV2({path:f}).then(function(m){m.status===200&&a()}).catch(function(m){console.log(m),l()})})}refresh(e){let i=this;return new Promise(function(o,a){i.listFolder(e.getCurrentDirectory()).then(function(){o()}).catch(function(l){console.log(l),a()})})}getDropbox(){return new Dropbox.Dropbox({accessToken:`${this.accessToken}`})}uploadFile(e,i,o){let a=e.preFlightData,l=this;this.createPreflightData(e,a.chunksToSend,a.Byteperchunk,a.Totalblobsize).then(function(d){e.preFlightData.dataToFill=d,e.preFlightData.dataToFill&&e.preFlightData.dataToFill.length>0&&!e.uploaded&&(e.pause||l.throttleSend(e,i,o))})}throttleSend(e,i,o){let a=e.preFlightData,l=e.preFlightData.dataToFill,d=e.fileObject;for(let f=0,m=l.length;f<m;f++)if(l){let v=d.slice(l[f].startSlice,l[f].endSlice);if(a.throttleSwitch)break;e.preFlightData.sentApi=e.preFlightData.sentApi+1,o.$uploadedFilesObject.set(i,e);let h=l[f].startSlice;this.uploadBlob(v,i,o,h)}}uploadBlob(e,i,o,a){let l=this.getDropbox(),d=this.getSessionID(i),f={session_id:d,offset:a},m=this,v=o.getUploadFileObject(i,m.driveSignature);if(v.preFlightData.sentApi===v.preFlightData.maxRequestToSend&&(v.preFlightData.throttleSwitch=!0,o.$uploadedFilesObject.set(i,v)),this.isLastOffset(i,a)){let y={path:o.getFileContainerEvent().getCurrentDirectory()+"/"+v.fileObject.name,mode:"add",autorename:!0,mute:!1};return l.filesUploadSessionFinish({cursor:{session_id:d,offset:v.fileObject.size},commit:y,contents:e}).then(function(){o.setUploadCompleted(i,m.driveSignature),localStorage.removeItem(i),o.isUploadingSequentially()&&(o.setSequenceDone(i,m.driveSignature),o.uploadFileNextSequence(m.driveSignature))})}else l.filesUploadSessionAppendV2({cursor:f,close:!1,contents:e}).then(()=>{m.updateOffsetDone(i,a),v.preFlightData.noOfReceivedResponse=v.preFlightData.noOfReceivedResponse+1,o.$uploadedFilesObject.set(i,v);let h=Math.round(m.uploadPercentage(i));v.preFlightData.noOfReceivedResponse===v.preFlightData.maxRequestToSend&&(o.releaseThrottle(i,m.driveSignature),v.uploaded||(m.uploadFile(v,i,o),o.updateFileProgress(i,h,m.driveSignature)))}).catch(h=>{if(h=h.error,h.error.hasOwnProperty("correct_offset")){let y=h.error.correct_offset;m.repairOffsetPosition(i,y),o.releaseThrottle(i,m.driveSignature),m.uploadFile(v,i,o)}})}createPreflightData(e,i,o,a){let l=this,d=l.driveSignature+"_"+e.fileObject.name;return new Promise(function(f,m){return $(this,null,function*(){let v=l.getDropbox(),h;if(h=JSON.parse(localStorage.getItem(d)))return h=h.dataToFill.filter(P=>!P.done),f(h);let y=0,b=o,E=e.fileObject;h={session_id:"",dataToFill:[]};for(let P=0;P<=i;P++)P===0?yield v.filesUploadSessionStart({close:!1,contents:E.slice(y,b)}).then(_=>{h.dataToFill.push({startSlice:y,endSlice:b,done:!0}),h.session_id=_.result.session_id}).catch(function(){m()}):h.dataToFill.push({startSlice:y,endSlice:b,done:!1}),a=a-o,y=b,b=y+o;return localStorage.setItem(d,JSON.stringify(h)),h=h.dataToFill.filter(P=>!P.done),f(h)})})}isLastOffset(e,i){let o=JSON.parse(localStorage.getItem(e));return o?(o=o.dataToFill,o[o.length-1].startSlice===i):!1}updateOffsetDone(e,i,o=!0){let a=JSON.parse(localStorage.getItem(e));if(a){let l=a.dataToFill;for(let d=0,f=l.length;d<f;d++)if(l[d].startSlice===i){l[d].done=o,localStorage.setItem(e,JSON.stringify({session_id:a.session_id,dataToFill:l}));break}}}getSessionID(e){let i=JSON.parse(localStorage.getItem(e));return i?i.session_id:!1}uploadPercentage(e){let i=JSON.parse(localStorage.getItem(e));if(i){let o=i.dataToFill.length;return i.dataToFill.filter(l=>l.done).length/o*100}}repairOffsetPosition(e,i){let o=JSON.parse(localStorage.getItem(e));if(o){let a=o.dataToFill;for(let l=0,d=a.length;l<d&&!(a[l].endSlice>i);l++)a[l].endSlice<=i&&(a[l].done=!0,localStorage.setItem(e,JSON.stringify({session_id:o.session_id,dataToFill:a})))}}breadCrumbClickNavigationHandler(e){return new Promise((i,o)=>{this.listFolder(e).then(()=>i()).catch(()=>o())})}};s(Nt,"DropboxDiskDrive");var rt=class{constructor(){this._$driveSystem=new Map}get $driveSystem(){return this._$driveSystem}attachDriveStorage(e){return this.$driveSystem.set(e.driveSignature,e),this}detachDriveStorage(e){this.$driveSystem.has(e)&&this.$driveSystem.delete(e)}getDriveStorage(e){if(this.$driveSystem.has(e))return this.$driveSystem.get(e);throw new DOMException(`DriveStorage "${e}" doesn't exist`)}getFirstDriveStorage(){return[...this.$driveSystem][0][1]}};s(rt,"StorageDriversManager");window.hasOwnProperty("TonicsMedia")||(window.TonicsMedia={});window.TonicsMedia.StorageDriversManager=()=>new rt;var Ut=class{constructor(e){this.removePreviousCutFiles(e),this.cutSelectedFiles(e),e.removeContextMenu()}removePreviousCutFiles(e){e.cutFile=[]}cutSelectedFiles(e){let i=e.getAllSelectedFiles();i.length>0&&(e.cutFile=i)}};s(Ut,"CutFileEvent");var Oe=class{constructor(e){this.fileContainerEvent=e,this.copiedLinkFile=e.getSelectedFile(),this.copiedLink=""}getFileContainerEvent(){return this.fileContainerEvent}handleCopyLink(e=null){let i=this;return this.getFileContainerEvent().currentDrive.copyPreviewLinkHandler(this.fileContainerEvent,e)}onSuccess(e){this.setCopiedLink(e),this.fileContainerEvent.removeContextMenu(),H(C.Link.Copy.Preview.Success).then(),this.fileContainerEvent.dispatchEventToHandlers(this,Oe)}onError(e=null){O(C.Link.Copy.Preview.Error).then()}getCopiedLinkFile(){return this.copiedLinkFile}setCopiedLink(e){this.copiedLink=e}getCopiedLink(){return this.copiedLink}};s(Oe,"CopyPreviewLinkEvent");var _e=class{get renamedFile(){return this._renamedFile}set renamedFile(e){this._renamedFile=e}constructor(e){this.fileContainerEvent=e}handleRenameFile(){let e=this.fileContainerEvent.getSelectedFile();if(e){let i=e.querySelector(".tonics-file-filename-input");this.fileContainerEvent.removeContextMenu(),Ot("Rename File To: ",i.value).then(o=>{o.isConfirmed&&(i.value=o.value,this.fileContainerEvent.currentDrive.renameFileHandler(e,this.fileContainerEvent).then(a=>{this.onSuccess(a)}).catch(()=>{this.onError()}))})}}onSuccess(e=""){H(C.Rename.Success).then(),this.renamedFile=e,this.fileContainerEvent.clearSelection(),this.fileContainerEvent.dispatchEventToHandlers(this,_e)}onError(e=""){O(C.Rename.Error).then()}};s(_e,"RenameFileEvent");var Me=class{constructor(e){this.fileContainerEvent=e}handleDeleteFile(){return $(this,null,function*(){let e=this.fileContainerEvent;e.removeDeletionMark(e.getAllFiles());let i=e.getAllSelectedFiles(),o=[];return i.length>0&&(e.markForDeletion(i),e.getFilesToBeDeleted().forEach(a=>{o.push(a.dataset)})),e.removeContextMenu(),e.currentDrive.deleteFileHandler(o,e)})}onSuccess(e=""){this.fileContainerEvent.resetPreviousFilesState(),e||(e=C.Deleted.Success),H(e).then(),this.fileContainerEvent.dispatchEventToHandlers(this,Me)}onError(e=""){O(C.Deleted.Error).then()}};s(Me,"DeleteFileEvent");var Ae=class{constructor(e){this.fileContainerEvent=e}handlePasteFile(){let e=this.fileContainerEvent;e.removeContextMenu();let i=e.getSelectedFile();return i||(i=document.createElement("li"),i.dataset.file_type="directory",i.dataset.file_path=e.getCurrentDirectory(),i.dataset.filename=e.getCurrentDirectoryFilename(),i.dataset.ext="null"),i&&i.dataset.ext=="null"&&i.dataset.file_type=="directory"?(i.dataset.paste="true",e.currentDrive.moveFileHandler(i,e)):!1}onSuccess(e=""){this.fileContainerEvent.resetPreviousFilesState(),this.fileContainerEvent.cutFile=[],e||(e=C.Move.Success),H(e).then(),this.fileContainerEvent.dispatchEventToHandlers(this,Ae)}onError(e=""){e?O(e).then():O(C.Move.Error).then()}};s(Ae,"PasteFileEvent");var jt=class{constructor(e){this.fileContainerEvent=e,this.handleEditImageFile()}handleEditImageFile(){let e=this;_t(Pe.FileRobotImageEditor.PATH,Pe.FileRobotImageEditor.ID).then(function(){return e.fileContainerEvent.currentDrive.editImageHandler(new ce(e.fileContainerEvent,null,null))}.bind(e))}};s(jt,"EditImageFileEvent");var st=class{constructor(e,i,o){this._cutFile=[];this._pasteTo=null;this.fileContainer=e,this.loadDriveEventClass=i,this.currentDrive=o}get currentDrive(){return this._currentDrive}set currentDrive(e){this._currentDrive=e}get cutFile(){return this._cutFile}set cutFile(e){this._cutFile=e}get pasteTo(){return this._pasteTo}set pasteTo(e){this._pasteTo=e}getFileContainer(){return this.fileContainer}getLoadDriveEventClass(){return this.loadDriveEventClass}getCurrentDirectory(){var e,i;return(i=(e=document.querySelector(".breadcrumb"))==null?void 0:e.lastElementChild)==null?void 0:i.getAttribute("data-pathtrail")}getCurrentDirectoryFilename(){var e,i;return(i=(e=document.querySelector(".breadcrumb"))==null?void 0:e.lastElementChild)==null?void 0:i.getAttribute("data-filename")}getBreadCrumbElement(){return document.querySelector(".breadcrumb")}titleCase(e){return e.toLowerCase().replace(/\b(\w)/g,function(i){return i.toLocaleUpperCase()})}getSelectedFile(){return document.querySelector('[data-selected="true"]')}getAllSelectedFiles(){return document.querySelectorAll('[data-selected="true"]')}getAllFiles(){return document.querySelectorAll("li.tonics-file")}getCutFiles(){return this.cutFile}getFilesToBeDeleted(){return document.querySelectorAll('[data-delete="true"]')}getFileByListID(e){return document.querySelector(`[data-list_id="${e}"]`)}getCopiedFiles(){return document.querySelectorAll('[data-copied="true"]')}getContextMenu(){return document.querySelector(k.FILES.CONTEXT)}getDiskDrives(){return document.querySelector(k.DRIVE.FILE_DISK_DRIVES)}removeContextMenu(){this.getContextMenu().classList.remove("show")}highlightFile(e){e.classList.add("selected-file"),e.dataset.selected="true";let i=document.querySelector(k.HEAD.PARENT);switch(i.style.top="0",e.dataset.file_type){case"directory":return this.cutFile.length>0?De([w().PASTE_FILE,w().RENAME_FILE,w().CUT_FILE,w().DELETE_FILE]):De([w().RENAME_FILE,w().CUT_FILE,w().DELETE_FILE]);case"file":if(this.cutFile.length>0)return De([w().PASTE_FILE,w().RENAME_FILE,w().CUT_FILE,w().DELETE_FILE]);De([w().RENAME_FILE,w().CUT_FILE,w().DELETE_FILE,w().COPY_PREVIEW_LINK,w().COPY_LINK])}}unHighlightFile(e){e.classList.remove("selected-file"),e.dataset.selected="false",e.setAttribute("readonly","true"),this.getAllSelectedFiles().length<1&&an([w().RENAME_FILE,w().CUT_FILE,w().DELETE_FILE,w().PASTE_FILE,w().COPY_PREVIEW_LINK,w().COPY_LINK])}resetPreviousFilesState(){let e=k.FILES.SINGLE_FILE,i=document.querySelector(k.HEAD.PARENT),o=i==null?void 0:i.getBoundingClientRect().height;if(i.style.top=`-${o}px`,document.querySelectorAll(e).forEach(a=>{var l;a.classList.remove("selected-file"),a.setAttribute("data-selected","false"),(l=a.querySelector(".tonics-file-filename-input"))==null||l.setAttribute("readonly","true")}),an([w().RENAME_FILE,w().CUT_FILE,w().DELETE_FILE,w().PASTE_FILE,w().COPY_PREVIEW_LINK,w().COPY_LINK]),this.cutFile.length>0)return De([w().PASTE_FILE])}removeAllDriveSelectionMark(){this.loadDriveEventClass.removeAllDriveSelectionMark()}removeDeletionMark(e){e.length>0&&e.forEach(i=>{i.dataset.delete="false"})}markForDeletion(e){e.length>0&&e.forEach(i=>{i.dataset.delete="true"})}clearSelection(){var e;(e=window==null?void 0:window.getSelection())==null||e.empty()}menuEventAction(e){let i=this;if(he.hasOwnProperty(e)){let o=w(),a;for(a in o){let l=o[a];if(l==e)switch(l){case"EditImageFileEvent":return i.editImageFileEvent();case"DeleteFileEvent":return i.deleteFileEvent();case"RenameFileEvent":return i.renameFileEvent();case"PasteFileEvent":return i.pasteFileEvent();case"UploadFileEvent":return i.uploadFileEvent();case"CopyLinkEvent":return i.copyLinkEvent();case"CopyPreviewLinkEvent":return i.copyPreviewLinkEvent();case"CutFileEvent":return new Ut(this);case"NewFolderEvent":Ot("Folder Name").then(d=>{if(this.removeContextMenu(),d.isConfirmed){let f={name:d.value};return i.currentDrive.createFolder(this,f).then(function(){H(C.Folder.Success).then(),i.currentDrive.refresh(i).then()}.bind(i)).catch(function(){O(C.Folder.Error).then()})}});return;case"RefreshFolderEvent":return i.refreshFolderEvent();default:break}}}}dispatchEventToHandlers(e,i){B(e,i)}editImageFileEvent(){new jt(this)}refreshFolderEvent(){return this.currentDrive.refresh(this).then(()=>{this.removeContextMenu(),H(C.Refresh.Success).then()})}deleteFileEvent(){let e=new Me(this);ni("Are You Sure You Want To Delete File(s) ?","Delete",()=>{e.handleDeleteFile().then(i=>{e.onSuccess(i)}).catch(function(){e.onError()})})}pasteFileEvent(){let e=new Ae(this),i=e.handlePasteFile();i instanceof Promise&&i.then(o=>{e.onSuccess(o)}).catch(function(){e.onError()}),i===!1&&e.onError("Failed To Paste Into Directory")}copyLinkEvent(){let e=new de(this);e.handleCopyLink(this.getSelectedFile()).then(i=>{e.onSuccess(i)}).catch(i=>{e.onError()})}copyPreviewLinkEvent(){let e=new Oe(this);e.handleCopyLink(this.getSelectedFile()).then(i=>{e.onSuccess(i)}).catch(function(){e.onError()})}renameFileEvent(){new _e(this).handleRenameFile()}uploadFileEvent(){let e=this,i=document.createElement("input");i.type="file",i.multiple=!0,i.click(),i.onchange=function(o){let a=o.target.files,l=e.getCurrentDirectory();new ce(e,a,l)}}};s(st,"FileContainerEvent");var He=class{constructor(e,i){this.data=e,this.loadDriveEventClass=i}getData(){return this.data}getLoadDriveEventClass(){return this.loadDriveEventClass}getFiles(){var e;return(e=this.getData())==null?void 0:e.data}getCurrentPath(){var e;return(e=this.getData())==null?void 0:e.more}getBreadCrumbElement(){return document.querySelector(".breadcrumb")}};s(He,"LoadDriveDataEvent");window.hasOwnProperty("TonicsScript")||(window.TonicsMedia={});window.TonicsMedia.LoadDriveDataEvent=(u,e)=>new He(u,e);var at=class{get appURL(){return this._appURL}set appURL(e){this._appURL=e}get eventsConfig(){return this._eventsConfig}set eventsConfig(e){this._eventsConfig=e}get driveStorageManager(){return this._driveStorageManager}set driveStorageManager(e){this._driveStorageManager=e}constructor(e,i=""){window.TonicsFileManager={events:{}},this._eventsConfig=he,this.driveStorageManager=e;let o=document.querySelector(".tonics-files-parent"),a=o==null?void 0:o.querySelector(".tonics-files-container");if(this._appURL=i,this.processDriveRootFolder(),a){let l=ae(this.eventsConfig,st),d=new st(a,this,this.driveStorageManager.getFirstDriveStorage());window.TonicsFileManager.events.fileContainerEvent=d,window.TonicsFileManager.events.loadDriveEvent=this,this.getEventDispatcher().setHandler(l).dispatch(d)}}processDriveRootFolder(){let e=document.querySelector(k.DRIVE.DISK_DRIVE_CONTAINER);e==null||e.replaceChildren(),this.driveStorageManager.$driveSystem.forEach(function(i,o){let a=i.getDriveIcon();e==null||e.insertAdjacentHTML("beforeend",`
            <li class="tonics-individual-drive" data-drivename=${o}>
          <a href="javascript:void(0);" class="drive-link">
              <button class="drive-toggle" aria-expanded="false" aria-label="Expand child menu">
                  <svg class="icon tonics-drive-icon">
                      <use class="svgUse" xlink:href=${a}></use>
                  </svg>
              </button>
              <span class="tonics-drive-selected display-none"> \u2713 </span>
              &nbsp;
              <!-- DRIVE NAME -->
              <span class="drive-name"> ${o} Drive</span>
          </a>
      </li>`)})}processFiles(e){let i=ae(he,He);this.getEventDispatcher().setHandler(i).dispatch(new He(e,this))}bootDiskDrive(e){if(!this.driveStorageManager.$driveSystem.has(e))throw new DOMException(`Couldn't Boot Drive "${e}", Perhaps, It Doesn't Exit?`);cn(),this.driveStorageManager.getDriveStorage(e).coldBootStorageDisk().then(()=>{cn(!1),this.removeAllDriveSelectionMark(),document.querySelector(`[data-drivename="${e}"]`).querySelector(k.DRIVE.DRIVE_SELECTED).classList.remove("display-none")})}removeAllDriveSelectionMark(){let e=k.DRIVE.DRIVE_SELECTED;document.querySelectorAll(e).forEach(i=>{i.classList.add("display-none")})}successMessage(e){return H(e)}errorMessage(e){return O(e)}getEventDispatcher(){return new ve}};s(at,"LoadDriveEvent");window.hasOwnProperty("TonicsScript")||(window.TonicsMedia={});window.TonicsMedia.LoadDriveEvent=(u,e="")=>new at(u,e);var dn=class{constructor(e){if(e==="LocalDiskDrive")return(i,o="",a)=>new Rt(i,o,a);if(e==="DropboxDiskDrive")return(i,o)=>new Nt(i,o);if(e==="StorageDriversManager")return new rt;if(e==="LoadDriveEvent")return(i,o="")=>new at(i,o)}};s(dn,"TonicsFileManagerExcrete");window.hasOwnProperty("TonicsMedia")||(window.TonicsMedia={});window.TonicsMedia.TonicsFileManagerExcrete=u=>new dn(u);export{dn as TonicsFileManagerExcrete};
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
!function(){"use strict";var e=function(){this.init()};e.prototype={init:function(){var e=this||n;return e._counter=1e3,e._html5AudioPool=[],e.html5PoolSize=10,e._codecs={},e._howls=[],e._muted=!1,e._volume=1,e._canPlayEvent="canplaythrough",e._navigator="undefined"!=typeof window&&window.navigator?window.navigator:null,e.masterGain=null,e.noAudio=!1,e.usingWebAudio=!0,e.autoSuspend=!0,e.ctx=null,e.autoUnlock=!0,e._setup(),e},volume:function(e){var o=this||n;if(e=parseFloat(e),o.ctx||_(),void 0!==e&&e>=0&&e<=1){if(o._volume=e,o._muted)return o;o.usingWebAudio&&o.masterGain.gain.setValueAtTime(e,n.ctx.currentTime);for(var t=0;t<o._howls.length;t++)if(!o._howls[t]._webAudio)for(var r=o._howls[t]._getSoundIds(),a=0;a<r.length;a++){var u=o._howls[t]._soundById(r[a]);u&&u._node&&(u._node.volume=u._volume*e)}return o}return o._volume},mute:function(e){var o=this||n;o.ctx||_(),o._muted=e,o.usingWebAudio&&o.masterGain.gain.setValueAtTime(e?0:o._volume,n.ctx.currentTime);for(var t=0;t<o._howls.length;t++)if(!o._howls[t]._webAudio)for(var r=o._howls[t]._getSoundIds(),a=0;a<r.length;a++){var u=o._howls[t]._soundById(r[a]);u&&u._node&&(u._node.muted=!!e||u._muted)}return o},stop:function(){for(var e=this||n,o=0;o<e._howls.length;o++)e._howls[o].stop();return e},unload:function(){for(var e=this||n,o=e._howls.length-1;o>=0;o--)e._howls[o].unload();return e.usingWebAudio&&e.ctx&&void 0!==e.ctx.close&&(e.ctx.close(),e.ctx=null,_()),e},codecs:function(e){return(this||n)._codecs[e.replace(/^x-/,"")]},_setup:function(){var e=this||n;if(e.state=e.ctx?e.ctx.state||"suspended":"suspended",e._autoSuspend(),!e.usingWebAudio)if("undefined"!=typeof Audio)try{var o=new Audio;void 0===o.oncanplaythrough&&(e._canPlayEvent="canplay")}catch(n){e.noAudio=!0}else e.noAudio=!0;try{var o=new Audio;o.muted&&(e.noAudio=!0)}catch(e){}return e.noAudio||e._setupCodecs(),e},_setupCodecs:function(){var e=this||n,o=null;try{o="undefined"!=typeof Audio?new Audio:null}catch(n){return e}if(!o||"function"!=typeof o.canPlayType)return e;var t=o.canPlayType("audio/mpeg;").replace(/^no$/,""),r=e._navigator?e._navigator.userAgent:"",a=r.match(/OPR\/([0-6].)/g),u=a&&parseInt(a[0].split("/")[1],10)<33,d=-1!==r.indexOf("Safari")&&-1===r.indexOf("Chrome"),i=r.match(/Version\/(.*?) /),_=d&&i&&parseInt(i[1],10)<15;return e._codecs={mp3:!(u||!t&&!o.canPlayType("audio/mp3;").replace(/^no$/,"")),mpeg:!!t,opus:!!o.canPlayType('audio/ogg; codecs="opus"').replace(/^no$/,""),ogg:!!o.canPlayType('audio/ogg; codecs="vorbis"').replace(/^no$/,""),oga:!!o.canPlayType('audio/ogg; codecs="vorbis"').replace(/^no$/,""),wav:!!(o.canPlayType('audio/wav; codecs="1"')||o.canPlayType("audio/wav")).replace(/^no$/,""),aac:!!o.canPlayType("audio/aac;").replace(/^no$/,""),caf:!!o.canPlayType("audio/x-caf;").replace(/^no$/,""),m4a:!!(o.canPlayType("audio/x-m4a;")||o.canPlayType("audio/m4a;")||o.canPlayType("audio/aac;")).replace(/^no$/,""),m4b:!!(o.canPlayType("audio/x-m4b;")||o.canPlayType("audio/m4b;")||o.canPlayType("audio/aac;")).replace(/^no$/,""),mp4:!!(o.canPlayType("audio/x-mp4;")||o.canPlayType("audio/mp4;")||o.canPlayType("audio/aac;")).replace(/^no$/,""),weba:!(_||!o.canPlayType('audio/webm; codecs="vorbis"').replace(/^no$/,"")),webm:!(_||!o.canPlayType('audio/webm; codecs="vorbis"').replace(/^no$/,"")),dolby:!!o.canPlayType('audio/mp4; codecs="ec-3"').replace(/^no$/,""),flac:!!(o.canPlayType("audio/x-flac;")||o.canPlayType("audio/flac;")).replace(/^no$/,"")},e},_unlockAudio:function(){var e=this||n;if(!e._audioUnlocked&&e.ctx){e._audioUnlocked=!1,e.autoUnlock=!1,e._mobileUnloaded||44100===e.ctx.sampleRate||(e._mobileUnloaded=!0,e.unload()),e._scratchBuffer=e.ctx.createBuffer(1,1,22050);var o=function(n){for(;e._html5AudioPool.length<e.html5PoolSize;)try{var t=new Audio;t._unlocked=!0,e._releaseHtml5Audio(t)}catch(n){e.noAudio=!0;break}for(var r=0;r<e._howls.length;r++)if(!e._howls[r]._webAudio)for(var a=e._howls[r]._getSoundIds(),u=0;u<a.length;u++){var d=e._howls[r]._soundById(a[u]);d&&d._node&&!d._node._unlocked&&(d._node._unlocked=!0,d._node.load())}e._autoResume();var i=e.ctx.createBufferSource();i.buffer=e._scratchBuffer,i.connect(e.ctx.destination),void 0===i.start?i.noteOn(0):i.start(0),"function"==typeof e.ctx.resume&&e.ctx.resume(),i.onended=function(){i.disconnect(0),e._audioUnlocked=!0,document.removeEventListener("touchstart",o,!0),document.removeEventListener("touchend",o,!0),document.removeEventListener("click",o,!0),document.removeEventListener("keydown",o,!0);for(var n=0;n<e._howls.length;n++)e._howls[n]._emit("unlock")}};return document.addEventListener("touchstart",o,!0),document.addEventListener("touchend",o,!0),document.addEventListener("click",o,!0),document.addEventListener("keydown",o,!0),e}},_obtainHtml5Audio:function(){var e=this||n;if(e._html5AudioPool.length)return e._html5AudioPool.pop();var o=(new Audio).play();return o&&"undefined"!=typeof Promise&&(o instanceof Promise||"function"==typeof o.then)&&o.catch(function(){console.warn("HTML5 Audio pool exhausted, returning potentially locked audio object.")}),new Audio},_releaseHtml5Audio:function(e){var o=this||n;return e._unlocked&&o._html5AudioPool.push(e),o},_autoSuspend:function(){var e=this;if(e.autoSuspend&&e.ctx&&void 0!==e.ctx.suspend&&n.usingWebAudio){for(var o=0;o<e._howls.length;o++)if(e._howls[o]._webAudio)for(var t=0;t<e._howls[o]._sounds.length;t++)if(!e._howls[o]._sounds[t]._paused)return e;return e._suspendTimer&&clearTimeout(e._suspendTimer),e._suspendTimer=setTimeout(function(){if(e.autoSuspend){e._suspendTimer=null,e.state="suspending";var n=function(){e.state="suspended",e._resumeAfterSuspend&&(delete e._resumeAfterSuspend,e._autoResume())};e.ctx.suspend().then(n,n)}},3e4),e}},_autoResume:function(){var e=this;if(e.ctx&&void 0!==e.ctx.resume&&n.usingWebAudio)return"running"===e.state&&"interrupted"!==e.ctx.state&&e._suspendTimer?(clearTimeout(e._suspendTimer),e._suspendTimer=null):"suspended"===e.state||"running"===e.state&&"interrupted"===e.ctx.state?(e.ctx.resume().then(function(){e.state="running";for(var n=0;n<e._howls.length;n++)e._howls[n]._emit("resume")}),e._suspendTimer&&(clearTimeout(e._suspendTimer),e._suspendTimer=null)):"suspending"===e.state&&(e._resumeAfterSuspend=!0),e}};var n=new e,o=function(e){var n=this;if(!e.src||0===e.src.length)return void console.error("An array of source files must be passed with any new Howl.");n.init(e)};o.prototype={init:function(e){var o=this;return n.ctx||_(),o._autoplay=e.autoplay||!1,o._format="string"!=typeof e.format?e.format:[e.format],o._html5=e.html5||!1,o._muted=e.mute||!1,o._loop=e.loop||!1,o._pool=e.pool||5,o._preload="boolean"!=typeof e.preload&&"metadata"!==e.preload||e.preload,o._rate=e.rate||1,o._sprite=e.sprite||{},o._src="string"!=typeof e.src?e.src:[e.src],o._volume=void 0!==e.volume?e.volume:1,o._xhr={method:e.xhr&&e.xhr.method?e.xhr.method:"GET",headers:e.xhr&&e.xhr.headers?e.xhr.headers:null,withCredentials:!(!e.xhr||!e.xhr.withCredentials)&&e.xhr.withCredentials},o._duration=0,o._state="unloaded",o._sounds=[],o._endTimers={},o._queue=[],o._playLock=!1,o._onend=e.onend?[{fn:e.onend}]:[],o._onfade=e.onfade?[{fn:e.onfade}]:[],o._onload=e.onload?[{fn:e.onload}]:[],o._onloaderror=e.onloaderror?[{fn:e.onloaderror}]:[],o._onplayerror=e.onplayerror?[{fn:e.onplayerror}]:[],o._onpause=e.onpause?[{fn:e.onpause}]:[],o._onplay=e.onplay?[{fn:e.onplay}]:[],o._onstop=e.onstop?[{fn:e.onstop}]:[],o._onmute=e.onmute?[{fn:e.onmute}]:[],o._onvolume=e.onvolume?[{fn:e.onvolume}]:[],o._onrate=e.onrate?[{fn:e.onrate}]:[],o._onseek=e.onseek?[{fn:e.onseek}]:[],o._onunlock=e.onunlock?[{fn:e.onunlock}]:[],o._onresume=[],o._webAudio=n.usingWebAudio&&!o._html5,void 0!==n.ctx&&n.ctx&&n.autoUnlock&&n._unlockAudio(),n._howls.push(o),o._autoplay&&o._queue.push({event:"play",action:function(){o.play()}}),o._preload&&"none"!==o._preload&&o.load(),o},load:function(){var e=this,o=null;if(n.noAudio)return void e._emit("loaderror",null,"No audio support.");"string"==typeof e._src&&(e._src=[e._src]);for(var r=0;r<e._src.length;r++){var u,d;if(e._format&&e._format[r])u=e._format[r];else{if("string"!=typeof(d=e._src[r])){e._emit("loaderror",null,"Non-string found in selected audio sources - ignoring.");continue}u=/^data:audio\/([^;,]+);/i.exec(d),u||(u=/\.([^.]+)$/.exec(d.split("?",1)[0])),u&&(u=u[1].toLowerCase())}if(u||console.warn('No file extension was found. Consider using the "format" property or specify an extension.'),u&&n.codecs(u)){o=e._src[r];break}}return o?(e._src=o,e._state="loading","https:"===window.location.protocol&&"http:"===o.slice(0,5)&&(e._html5=!0,e._webAudio=!1),new t(e),e._webAudio&&a(e),e):void e._emit("loaderror",null,"No codec support for selected audio sources.")},play:function(e,o){var t=this,r=null;if("number"==typeof e)r=e,e=null;else{if("string"==typeof e&&"loaded"===t._state&&!t._sprite[e])return null;if(void 0===e&&(e="__default",!t._playLock)){for(var a=0,u=0;u<t._sounds.length;u++)t._sounds[u]._paused&&!t._sounds[u]._ended&&(a++,r=t._sounds[u]._id);1===a?e=null:r=null}}var d=r?t._soundById(r):t._inactiveSound();if(!d)return null;if(r&&!e&&(e=d._sprite||"__default"),"loaded"!==t._state){d._sprite=e,d._ended=!1;var i=d._id;return t._queue.push({event:"play",action:function(){t.play(i)}}),i}if(r&&!d._paused)return o||t._loadQueue("play"),d._id;t._webAudio&&n._autoResume();var _=Math.max(0,d._seek>0?d._seek:t._sprite[e][0]/1e3),s=Math.max(0,(t._sprite[e][0]+t._sprite[e][1])/1e3-_),l=1e3*s/Math.abs(d._rate),c=t._sprite[e][0]/1e3,f=(t._sprite[e][0]+t._sprite[e][1])/1e3;d._sprite=e,d._ended=!1;var p=function(){d._paused=!1,d._seek=_,d._start=c,d._stop=f,d._loop=!(!d._loop&&!t._sprite[e][2])};if(_>=f)return void t._ended(d);var m=d._node;if(t._webAudio){var v=function(){t._playLock=!1,p(),t._refreshBuffer(d);var e=d._muted||t._muted?0:d._volume;m.gain.setValueAtTime(e,n.ctx.currentTime),d._playStart=n.ctx.currentTime,void 0===m.bufferSource.start?d._loop?m.bufferSource.noteGrainOn(0,_,86400):m.bufferSource.noteGrainOn(0,_,s):d._loop?m.bufferSource.start(0,_,86400):m.bufferSource.start(0,_,s),l!==1/0&&(t._endTimers[d._id]=setTimeout(t._ended.bind(t,d),l)),o||setTimeout(function(){t._emit("play",d._id),t._loadQueue()},0)};"running"===n.state&&"interrupted"!==n.ctx.state?v():(t._playLock=!0,t.once("resume",v),t._clearTimer(d._id))}else{var h=function(){m.currentTime=_,m.muted=d._muted||t._muted||n._muted||m.muted,m.volume=d._volume*n.volume(),m.playbackRate=d._rate;try{var r=m.play();if(r&&"undefined"!=typeof Promise&&(r instanceof Promise||"function"==typeof r.then)?(t._playLock=!0,p(),r.then(function(){t._playLock=!1,m._unlocked=!0,o?t._loadQueue():t._emit("play",d._id)}).catch(function(){t._playLock=!1,t._emit("playerror",d._id,"Playback was unable to start. This is most commonly an issue on mobile devices and Chrome where playback was not within a user interaction."),d._ended=!0,d._paused=!0})):o||(t._playLock=!1,p(),t._emit("play",d._id)),m.playbackRate=d._rate,m.paused)return void t._emit("playerror",d._id,"Playback was unable to start. This is most commonly an issue on mobile devices and Chrome where playback was not within a user interaction.");"__default"!==e||d._loop?t._endTimers[d._id]=setTimeout(t._ended.bind(t,d),l):(t._endTimers[d._id]=function(){t._ended(d),m.removeEventListener("ended",t._endTimers[d._id],!1)},m.addEventListener("ended",t._endTimers[d._id],!1))}catch(e){t._emit("playerror",d._id,e)}};"data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA"===m.src&&(m.src=t._src,m.load());var y=window&&window.ejecta||!m.readyState&&n._navigator.isCocoonJS;if(m.readyState>=3||y)h();else{t._playLock=!0,t._state="loading";var g=function(){t._state="loaded",h(),m.removeEventListener(n._canPlayEvent,g,!1)};m.addEventListener(n._canPlayEvent,g,!1),t._clearTimer(d._id)}}return d._id},pause:function(e){var n=this;if("loaded"!==n._state||n._playLock)return n._queue.push({event:"pause",action:function(){n.pause(e)}}),n;for(var o=n._getSoundIds(e),t=0;t<o.length;t++){n._clearTimer(o[t]);var r=n._soundById(o[t]);if(r&&!r._paused&&(r._seek=n.seek(o[t]),r._rateSeek=0,r._paused=!0,n._stopFade(o[t]),r._node))if(n._webAudio){if(!r._node.bufferSource)continue;void 0===r._node.bufferSource.stop?r._node.bufferSource.noteOff(0):r._node.bufferSource.stop(0),n._cleanBuffer(r._node)}else isNaN(r._node.duration)&&r._node.duration!==1/0||r._node.pause();arguments[1]||n._emit("pause",r?r._id:null)}return n},stop:function(e,n){var o=this;if("loaded"!==o._state||o._playLock)return o._queue.push({event:"stop",action:function(){o.stop(e)}}),o;for(var t=o._getSoundIds(e),r=0;r<t.length;r++){o._clearTimer(t[r]);var a=o._soundById(t[r]);a&&(a._seek=a._start||0,a._rateSeek=0,a._paused=!0,a._ended=!0,o._stopFade(t[r]),a._node&&(o._webAudio?a._node.bufferSource&&(void 0===a._node.bufferSource.stop?a._node.bufferSource.noteOff(0):a._node.bufferSource.stop(0),o._cleanBuffer(a._node)):isNaN(a._node.duration)&&a._node.duration!==1/0||(a._node.currentTime=a._start||0,a._node.pause(),a._node.duration===1/0&&o._clearSound(a._node))),n||o._emit("stop",a._id))}return o},mute:function(e,o){var t=this;if("loaded"!==t._state||t._playLock)return t._queue.push({event:"mute",action:function(){t.mute(e,o)}}),t;if(void 0===o){if("boolean"!=typeof e)return t._muted;t._muted=e}for(var r=t._getSoundIds(o),a=0;a<r.length;a++){var u=t._soundById(r[a]);u&&(u._muted=e,u._interval&&t._stopFade(u._id),t._webAudio&&u._node?u._node.gain.setValueAtTime(e?0:u._volume,n.ctx.currentTime):u._node&&(u._node.muted=!!n._muted||e),t._emit("mute",u._id))}return t},volume:function(){var e,o,t=this,r=arguments;if(0===r.length)return t._volume;if(1===r.length||2===r.length&&void 0===r[1]){t._getSoundIds().indexOf(r[0])>=0?o=parseInt(r[0],10):e=parseFloat(r[0])}else r.length>=2&&(e=parseFloat(r[0]),o=parseInt(r[1],10));var a;if(!(void 0!==e&&e>=0&&e<=1))return a=o?t._soundById(o):t._sounds[0],a?a._volume:0;if("loaded"!==t._state||t._playLock)return t._queue.push({event:"volume",action:function(){t.volume.apply(t,r)}}),t;void 0===o&&(t._volume=e),o=t._getSoundIds(o);for(var u=0;u<o.length;u++)(a=t._soundById(o[u]))&&(a._volume=e,r[2]||t._stopFade(o[u]),t._webAudio&&a._node&&!a._muted?a._node.gain.setValueAtTime(e,n.ctx.currentTime):a._node&&!a._muted&&(a._node.volume=e*n.volume()),t._emit("volume",a._id));return t},fade:function(e,o,t,r){var a=this;if("loaded"!==a._state||a._playLock)return a._queue.push({event:"fade",action:function(){a.fade(e,o,t,r)}}),a;e=Math.min(Math.max(0,parseFloat(e)),1),o=Math.min(Math.max(0,parseFloat(o)),1),t=parseFloat(t),a.volume(e,r);for(var u=a._getSoundIds(r),d=0;d<u.length;d++){var i=a._soundById(u[d]);if(i){if(r||a._stopFade(u[d]),a._webAudio&&!i._muted){var _=n.ctx.currentTime,s=_+t/1e3;i._volume=e,i._node.gain.setValueAtTime(e,_),i._node.gain.linearRampToValueAtTime(o,s)}a._startFadeInterval(i,e,o,t,u[d],void 0===r)}}return a},_startFadeInterval:function(e,n,o,t,r,a){var u=this,d=n,i=o-n,_=Math.abs(i/.01),s=Math.max(4,_>0?t/_:t),l=Date.now();e._fadeTo=o,e._interval=setInterval(function(){var r=(Date.now()-l)/t;l=Date.now(),d+=i*r,d=Math.round(100*d)/100,d=i<0?Math.max(o,d):Math.min(o,d),u._webAudio?e._volume=d:u.volume(d,e._id,!0),a&&(u._volume=d),(o<n&&d<=o||o>n&&d>=o)&&(clearInterval(e._interval),e._interval=null,e._fadeTo=null,u.volume(o,e._id),u._emit("fade",e._id))},s)},_stopFade:function(e){var o=this,t=o._soundById(e);return t&&t._interval&&(o._webAudio&&t._node.gain.cancelScheduledValues(n.ctx.currentTime),clearInterval(t._interval),t._interval=null,o.volume(t._fadeTo,e),t._fadeTo=null,o._emit("fade",e)),o},loop:function(){var e,n,o,t=this,r=arguments;if(0===r.length)return t._loop;if(1===r.length){if("boolean"!=typeof r[0])return!!(o=t._soundById(parseInt(r[0],10)))&&o._loop;e=r[0],t._loop=e}else 2===r.length&&(e=r[0],n=parseInt(r[1],10));for(var a=t._getSoundIds(n),u=0;u<a.length;u++)(o=t._soundById(a[u]))&&(o._loop=e,t._webAudio&&o._node&&o._node.bufferSource&&(o._node.bufferSource.loop=e,e&&(o._node.bufferSource.loopStart=o._start||0,o._node.bufferSource.loopEnd=o._stop,t.playing(a[u])&&(t.pause(a[u],!0),t.play(a[u],!0)))));return t},rate:function(){var e,o,t=this,r=arguments;if(0===r.length)o=t._sounds[0]._id;else if(1===r.length){var a=t._getSoundIds(),u=a.indexOf(r[0]);u>=0?o=parseInt(r[0],10):e=parseFloat(r[0])}else 2===r.length&&(e=parseFloat(r[0]),o=parseInt(r[1],10));var d;if("number"!=typeof e)return d=t._soundById(o),d?d._rate:t._rate;if("loaded"!==t._state||t._playLock)return t._queue.push({event:"rate",action:function(){t.rate.apply(t,r)}}),t;void 0===o&&(t._rate=e),o=t._getSoundIds(o);for(var i=0;i<o.length;i++)if(d=t._soundById(o[i])){t.playing(o[i])&&(d._rateSeek=t.seek(o[i]),d._playStart=t._webAudio?n.ctx.currentTime:d._playStart),d._rate=e,t._webAudio&&d._node&&d._node.bufferSource?d._node.bufferSource.playbackRate.setValueAtTime(e,n.ctx.currentTime):d._node&&(d._node.playbackRate=e);var _=t.seek(o[i]),s=(t._sprite[d._sprite][0]+t._sprite[d._sprite][1])/1e3-_,l=1e3*s/Math.abs(d._rate);!t._endTimers[o[i]]&&d._paused||(t._clearTimer(o[i]),t._endTimers[o[i]]=setTimeout(t._ended.bind(t,d),l)),t._emit("rate",d._id)}return t},seek:function(){var e,o,t=this,r=arguments;if(0===r.length)t._sounds.length&&(o=t._sounds[0]._id);else if(1===r.length){var a=t._getSoundIds(),u=a.indexOf(r[0]);u>=0?o=parseInt(r[0],10):t._sounds.length&&(o=t._sounds[0]._id,e=parseFloat(r[0]))}else 2===r.length&&(e=parseFloat(r[0]),o=parseInt(r[1],10));if(void 0===o)return 0;if("number"==typeof e&&("loaded"!==t._state||t._playLock))return t._queue.push({event:"seek",action:function(){t.seek.apply(t,r)}}),t;var d=t._soundById(o);if(d){if(!("number"==typeof e&&e>=0)){if(t._webAudio){var i=t.playing(o)?n.ctx.currentTime-d._playStart:0,_=d._rateSeek?d._rateSeek-d._seek:0;return d._seek+(_+i*Math.abs(d._rate))}return d._node.currentTime}var s=t.playing(o);s&&t.pause(o,!0),d._seek=e,d._ended=!1,t._clearTimer(o),t._webAudio||!d._node||isNaN(d._node.duration)||(d._node.currentTime=e);var l=function(){s&&t.play(o,!0),t._emit("seek",o)};if(s&&!t._webAudio){var c=function(){t._playLock?setTimeout(c,0):l()};setTimeout(c,0)}else l()}return t},playing:function(e){var n=this;if("number"==typeof e){var o=n._soundById(e);return!!o&&!o._paused}for(var t=0;t<n._sounds.length;t++)if(!n._sounds[t]._paused)return!0;return!1},duration:function(e){var n=this,o=n._duration,t=n._soundById(e);return t&&(o=n._sprite[t._sprite][1]/1e3),o},state:function(){return this._state},unload:function(){for(var e=this,o=e._sounds,t=0;t<o.length;t++)o[t]._paused||e.stop(o[t]._id),e._webAudio||(e._clearSound(o[t]._node),o[t]._node.removeEventListener("error",o[t]._errorFn,!1),o[t]._node.removeEventListener(n._canPlayEvent,o[t]._loadFn,!1),o[t]._node.removeEventListener("ended",o[t]._endFn,!1),n._releaseHtml5Audio(o[t]._node)),delete o[t]._node,e._clearTimer(o[t]._id);var a=n._howls.indexOf(e);a>=0&&n._howls.splice(a,1);var u=!0;for(t=0;t<n._howls.length;t++)if(n._howls[t]._src===e._src||e._src.indexOf(n._howls[t]._src)>=0){u=!1;break}return r&&u&&delete r[e._src],n.noAudio=!1,e._state="unloaded",e._sounds=[],e=null,null},on:function(e,n,o,t){var r=this,a=r["_on"+e];return"function"==typeof n&&a.push(t?{id:o,fn:n,once:t}:{id:o,fn:n}),r},off:function(e,n,o){var t=this,r=t["_on"+e],a=0;if("number"==typeof n&&(o=n,n=null),n||o)for(a=0;a<r.length;a++){var u=o===r[a].id;if(n===r[a].fn&&u||!n&&u){r.splice(a,1);break}}else if(e)t["_on"+e]=[];else{var d=Object.keys(t);for(a=0;a<d.length;a++)0===d[a].indexOf("_on")&&Array.isArray(t[d[a]])&&(t[d[a]]=[])}return t},once:function(e,n,o){var t=this;return t.on(e,n,o,1),t},_emit:function(e,n,o){for(var t=this,r=t["_on"+e],a=r.length-1;a>=0;a--)r[a].id&&r[a].id!==n&&"load"!==e||(setTimeout(function(e){e.call(this,n,o)}.bind(t,r[a].fn),0),r[a].once&&t.off(e,r[a].fn,r[a].id));return t._loadQueue(e),t},_loadQueue:function(e){var n=this;if(n._queue.length>0){var o=n._queue[0];o.event===e&&(n._queue.shift(),n._loadQueue()),e||o.action()}return n},_ended:function(e){var o=this,t=e._sprite;if(!o._webAudio&&e._node&&!e._node.paused&&!e._node.ended&&e._node.currentTime<e._stop)return setTimeout(o._ended.bind(o,e),100),o;var r=!(!e._loop&&!o._sprite[t][2]);if(o._emit("end",e._id),!o._webAudio&&r&&o.stop(e._id,!0).play(e._id),o._webAudio&&r){o._emit("play",e._id),e._seek=e._start||0,e._rateSeek=0,e._playStart=n.ctx.currentTime;var a=1e3*(e._stop-e._start)/Math.abs(e._rate);o._endTimers[e._id]=setTimeout(o._ended.bind(o,e),a)}return o._webAudio&&!r&&(e._paused=!0,e._ended=!0,e._seek=e._start||0,e._rateSeek=0,o._clearTimer(e._id),o._cleanBuffer(e._node),n._autoSuspend()),o._webAudio||r||o.stop(e._id,!0),o},_clearTimer:function(e){var n=this;if(n._endTimers[e]){if("function"!=typeof n._endTimers[e])clearTimeout(n._endTimers[e]);else{var o=n._soundById(e);o&&o._node&&o._node.removeEventListener("ended",n._endTimers[e],!1)}delete n._endTimers[e]}return n},_soundById:function(e){for(var n=this,o=0;o<n._sounds.length;o++)if(e===n._sounds[o]._id)return n._sounds[o];return null},_inactiveSound:function(){var e=this;e._drain();for(var n=0;n<e._sounds.length;n++)if(e._sounds[n]._ended)return e._sounds[n].resetTrackGroups();return new t(e)},_drain:function(){var e=this,n=e._pool,o=0,t=0;if(!(e._sounds.length<n)){for(t=0; t<e._sounds.length; t++)e._sounds[t]._ended&&o++;for(t=e._sounds.length-1; t>=0; t--){if(o<=n)return;e._sounds[t]._ended&&(e._webAudio&&e._sounds[t]._node&&e._sounds[t]._node.disconnect(0),e._sounds.splice(t,1),o--)}}},_getSoundIds:function(e){var n=this;if(void 0===e){for(var o=[],t=0; t<n._sounds.length; t++)o.push(n._sounds[t]._id);return o}return[e]},_refreshBuffer:function(e){var o=this;return e._node.bufferSource=n.ctx.createBufferSource(),e._node.bufferSource.buffer=r[o._src],e._panner?e._node.bufferSource.connect(e._panner):e._node.bufferSource.connect(e._node),e._node.bufferSource.loop=e._loop,e._loop&&(e._node.bufferSource.loopStart=e._start||0,e._node.bufferSource.loopEnd=e._stop||0),e._node.bufferSource.playbackRate.setValueAtTime(e._rate,n.ctx.currentTime),o},_cleanBuffer:function(e){var o=this,t=n._navigator&&n._navigator.vendor.indexOf("Apple")>=0;if(n._scratchBuffer&&e.bufferSource&&(e.bufferSource.onended=null,e.bufferSource.disconnect(0),t))try{e.bufferSource.buffer=n._scratchBuffer}catch(e){}return e.bufferSource=null,o},_clearSound:function(e){/MSIE |Trident\//.test(n._navigator&&n._navigator.userAgent)||(e.src="data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA")}};var t=function(e){this._parent=e,this.init()};t.prototype={init:function(){var e=this,o=e._parent;return e._muted=o._muted,e._loop=o._loop,e._volume=o._volume,e._rate=o._rate,e._seek=0,e._paused=!0,e._ended=!0,e._sprite="__default",e._id=++n._counter,o._sounds.push(e),e.create(),e},create:function(){var e=this,o=e._parent,t=n._muted||e._muted||e._parent._muted?0:e._volume;return o._webAudio?(e._node=void 0===n.ctx.createGain?n.ctx.createGainNode():n.ctx.createGain(),e._node.gain.setValueAtTime(t,n.ctx.currentTime),e._node.paused=!0,e._node.connect(n.masterGain)):n.noAudio||(e._node=n._obtainHtml5Audio(),e._errorFn=e._errorListener.bind(e),e._node.addEventListener("error",e._errorFn,!1),e._loadFn=e._loadListener.bind(e),e._node.addEventListener(n._canPlayEvent,e._loadFn,!1),e._endFn=e._endListener.bind(e),e._node.addEventListener("ended",e._endFn,!1),e._node.src=o._src,e._node.preload=!0===o._preload?"auto":o._preload,e._node.volume=t*n.volume(),e._node.load()),e},reset:function(){var e=this,o=e._parent;return e._muted=o._muted,e._loop=o._loop,e._volume=o._volume,e._rate=o._rate,e._seek=0,e._rateSeek=0,e._paused=!0,e._ended=!0,e._sprite="__default",e._id=++n._counter,e},_errorListener:function(){var e=this;e._parent._emit("loaderror",e._id,e._node.error?e._node.error.code:0),e._node.removeEventListener("error",e._errorFn,!1)},_loadListener:function(){var e=this,o=e._parent;o._duration=Math.ceil(10*e._node.duration)/10,0===Object.keys(o._sprite).length&&(o._sprite={__default:[0,1e3*o._duration]}),"loaded"!==o._state&&(o._state="loaded",o._emit("load"),o._loadQueue()),e._node.removeEventListener(n._canPlayEvent,e._loadFn,!1)},_endListener:function(){var e=this,n=e._parent;n._duration===1/0&&(n._duration=Math.ceil(10*e._node.duration)/10,n._sprite.__default[1]===1/0&&(n._sprite.__default[1]=1e3*n._duration),n._ended(e)),e._node.removeEventListener("ended",e._endFn,!1)}};var r={},a=function(e){var n=e._src;if(r[n])return e._duration=r[n].duration,void i(e);if(/^data:[^;]+;base64,/.test(n)){for(var o=atob(n.split(",")[1]),t=new Uint8Array(o.length),a=0; a<o.length; ++a)t[a]=o.charCodeAt(a);d(t.buffer,e)}else{var _=new XMLHttpRequest;_.open(e._xhr.method,n,!0),_.withCredentials=e._xhr.withCredentials,_.responseType="arraybuffer",e._xhr.headers&&Object.keys(e._xhr.headers).forEach(function(n){_.setRequestHeader(n,e._xhr.headers[n])}),_.onload=function(){var n=(_.status+"")[0];if("0"!==n&&"2"!==n&&"3"!==n)return void e._emit("loaderror",null,"Failed loading audio file with status: "+_.status+".");d(_.response,e)},_.onerror=function(){e._webAudio&&(e._html5=!0,e._webAudio=!1,e._sounds=[],delete r[n],e.load())},u(_)}},u=function(e){try{e.send()}catch(n){e.onerror()}},d=function(e, o){var t=function(){o._emit("loaderror",null,"Decoding audio data failed.")},a=function(e){e&&o._sounds.length>0?(r[o._src]=e,i(o,e)):t()};"undefined"!=typeof Promise&&1===n.ctx.decodeAudioData.length?n.ctx.decodeAudioData(e).then(a).catch(t):n.ctx.decodeAudioData(e,a,t)},i=function(e, n){n&&!e._duration&&(e._duration=n.duration),0===Object.keys(e._sprite).length&&(e._sprite={__default:[0,1e3*e._duration]}),"loaded"!==e._state&&(e._state="loaded",e._emit("load"),e._loadQueue())},_=function(){if(n.usingWebAudio){try{"undefined"!=typeof AudioContext?n.ctx=new AudioContext:"undefined"!=typeof webkitAudioContext?n.ctx=new webkitAudioContext:n.usingWebAudio=!1}catch(e){n.usingWebAudio=!1}n.ctx||(n.usingWebAudio=!1);var e=/iP(hone|od|ad)/.test(n._navigator&&n._navigator.platform),o=n._navigator&&n._navigator.appVersion.match(/OS (\d+)_(\d+)_?(\d+)?/),t=o?parseInt(o[1],10):null;if(e&&t&&t<9){var r=/safari/.test(n._navigator&&n._navigator.userAgent.toLowerCase());n._navigator&&!r&&(n.usingWebAudio=!1)}n.usingWebAudio&&(n.masterGain=void 0===n.ctx.createGain?n.ctx.createGainNode():n.ctx.createGain(),n.masterGain.gain.setValueAtTime(n._muted?0:n._volume,n.ctx.currentTime),n.masterGain.connect(n.ctx.destination)),n._setup()}};"function"==typeof define&&define.amd&&define([],function(){return{Howler:n,Howl:o}}),"undefined"!=typeof exports&&(exports.Howler=n,exports.Howl=o),"undefined"!=typeof global?(global.HowlerGlobal=e,global.Howler=n,global.Howl=o,global.Sound=t):"undefined"!=typeof window&&(window.HowlerGlobal=e,window.Howler=n,window.Howl=o,window.Sound=t)}();
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
        this.userIsSeekingSongSlider = false;
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
                    if (tonics_audio_seeking === false && el.dataset.audioplayer_next === 'true') {
                        this.next();
                    }
                }

                // prev
                if (el.dataset.hasOwnProperty('audioplayer_prev')) {
                    if (tonics_audio_seeking === false  && el.dataset.audioplayer_prev === 'true') {
                        this.prev();
                    }
                }

                // Remove any possible seeking
                removeSeeking();

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

    onPageReload() {
        let self = this;
        const storedVolume = localStorage.getItem('HowlerJSVolume');
        if (storedVolume) {
            Howler.volume(parseFloat(storedVolume));
            const volumeSlider = document.querySelector('.volume-slider');
            if (volumeSlider){
                volumeSlider.value = storedVolume;
            }
        }

        // Get the current main browser URL
        const currentURL = window.location.href;
        // Retrieve the stored position from localStorage
        let storedData = localStorage.getItem(currentURL);
        if (storedData) {
            storedData = JSON.parse(storedData);
            let groupSongs = null;
            if (self.audioPlayerSettings.has(storedData.currentGroupID)) {
                groupSongs = self.audioPlayerSettings.get(storedData.currentGroupID);
                if (groupSongs.has(storedData.songKey)) {
                    self.playlistIndex = groupSongs.get(storedData.songKey).songID;
                    // Load Howl
                    self.play();

                    // Seek to the stored position once the file is loaded
                    self.currentHowl.once('load', () => {
                        let progress = storedData.currentPos /  self.currentHowl.duration() * 100 || 0;
                        this.songSlider.value = progress;
                        self.seek(progress);
                    });
                }
            }
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

    newHowlPlay(onload = null) {
        let self = this,
            songData = self.getSongData();
        return new Howl({
            preload:true,
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
        if (songData){
            songData.seek(skipToDuration);
        }

       // if (songData.playing()) {}
    }

    step() {
        let self = this;
        let howl = self.getCurrentHowl();
        if (howl.playing()) {
            // Determine our current seek position.
            let seek = howl.seek() || 0;
            let progress = seek / howl.duration() * 100 || 0;
            progress = Math.round(progress);
            if (self.userIsSeekingSongSlider === false) {
                self.songSlider.value = progress;
            }
            self.storeSongPosition()
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

if (document.querySelector('.audio-player')){
    let audioPlayer = new AudioPlayer();
    audioPlayer.run();
    let parent = '.audio-player-queue-list',
        widgetChild = `.track-in-queue`,
        top = false, bottom = false,
        sensitivity = 0, sensitivityMax = 5;
    if (window?.TonicsScript.hasOwnProperty('Draggables')){
        window.TonicsScript.Draggables(parent)
            .settings(widgetChild, ['.track-license'], false) // draggable element
            .onDragDrop(function (element, self) {
                let elementDropped = self.getDroppedTarget().closest(widgetChild);
                let elementDragged = self.getDragging().closest(widgetChild);
                if (elementDropped !== elementDragged && top || bottom){
                    // swap element
                    swapNodes(elementDragged, elementDropped, self.draggingOriginalRect, () => {
                        audioPlayer.resetQueue();
                    });
                    sensitivity = 0;
                    top = false; bottom = false;
                }
            }).onDragTop((element) => {
            if (sensitivity++ >= sensitivityMax){
                let dragToTheTop = element.previousElementSibling;
                if (dragToTheTop && dragToTheTop.classList.contains('track-in-queue')){
                    top = true;
                }
            }
        }).onDragBottom( (element) => {
            if (sensitivity++ >= sensitivityMax){
                let dragToTheBottom = element.nextElementSibling;
                if (dragToTheBottom && dragToTheBottom.classList.contains('track-in-queue')) {
                    bottom = true;
                }
            }
        }).run();

    }

    if (window?.TonicsScript.hasOwnProperty('MenuToggle') && window?.TonicsScript.hasOwnProperty('Query')){
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