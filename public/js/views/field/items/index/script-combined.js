// FOR FEATURED IMAGE
let featuredMain = document.querySelector('main');
if (tinymce && tinymce.activeEditor && tinymce.activeEditor.dom){
    let tinySelect = tinymce.activeEditor.dom.select(".entry-content");
    if (tinySelect.length > 0){
        tinySelect[0].addEventListener('click', featuredImageHandler);
    }
}
if (featuredMain){
    featuredMain.addEventListener('click', featuredImageHandler);
}
let featuredImageWithSRC, featuredImageInput, featuredImageInputName, removeFeaturedImage, windowInstance = null;

function featuredImageHandler(e) {
    let el = e.target,
        parent = el.closest('[data-widget-form="true"]');
    if (!parent) {
        parent =  el.closest('form');
    }
    if (parent) {
        featuredImageWithSRC = parent.querySelector('[class^="image:featured-image"]');
        featuredImageInput = parent.querySelector('.tonics-featured-image');
        featuredImageInputName = parent.querySelector('[data-widget-image-name="true"]');
        removeFeaturedImage = parent.querySelector('.remove-featured-image');
    }

    if (el.classList.contains('tonics-featured-image')) {
        if (tonicsFileManagerURL) {
            let windowFeatures = "left=95,top=100";
            windowInstance = window.open(tonicsFileManagerURL, 'Tonics File Manager', windowFeatures);
        }
    } else if (el.classList.contains('remove-featured-image')) {
        if (featuredImageInputName) {
            featuredImageInputName.value = '';
        }
        featuredImageWithSRC.src = '';
        featuredImageInput.classList.remove('d:none');
        removeFeaturedImage.classList.add('d:none');
    }
}

window.addEventListener('message', (e) => {
    if (e.origin !== siteURL) {
        return;
    }
    let data = e.data;
    if (data.hasOwnProperty('cmd') && data.cmd === 'tonics:ImageLink') {
        if (featuredImageWithSRC && featuredImageInput) {
            if (featuredImageInputName) {
                featuredImageInputName.value = data.value;
            }
            featuredImageWithSRC.src = data.value;
            featuredImageInput.classList.add('d:none');
            removeFeaturedImage.classList.remove('d:none');
            windowInstance.close();
        }
    }
});

let ImageFeaturedImage = document.querySelectorAll('[class^="image:featured-image"]');
if (ImageFeaturedImage.length > 0) {
    ImageFeaturedImage.forEach((value, key) => {
        let parent = value.closest('[data-widget-form="true"]');
        if (!parent) {
            parent =  value.closest('form');
        }
        let featuredImageInput = parent.querySelector('.tonics-featured-image'),
            removeFeaturedImage = parent.querySelector('.remove-featured-image');

        let image = new Image();
        image.src = value.src;
        image.onload = function () {
            // image can be loaded
            if (featuredImageInput){
                featuredImageInput.classList.add('d:none');
            }
            if (removeFeaturedImage){
                removeFeaturedImage.classList.remove('d:none');
            }
        }
        image.onerror = () => {
            // image can't be loaded
        }
    })
}
// audio featured selection
if (document.querySelector('main')){
    document.querySelector('main').addEventListener('click', audioFeaturedHandler);
}

if (tinymce && tinymce.activeEditor && tinymce.activeEditor.dom){
    let tinySelectAudioHandler = tinymce.activeEditor.dom.select(".entry-content");
    if (tinySelectAudioHandler.length > 0){
        tinySelectAudioHandler[0].addEventListener('click', audioFeaturedHandler);
    }
}
let audioDemoInput, audioDemoInputName, removeAudioDemo, windowAudioFeaturedInstance = null;
function audioFeaturedHandler(e) {
    let el = e.target,
        parent = el.closest('[data-widget-form="true"]');
    if (!parent) {
        parent =  el.closest('form');
    }
    if (parent) {
        audioDemoInput = parent.querySelector('.tonics-audio-featured');
        audioDemoInputName = parent.querySelector('[data-widget-audio-url="true"]');
        removeAudioDemo = parent.querySelector('.remove-audio-demo');
    }

    if (el.classList.contains('tonics-audio-featured')) {
        if (tonicsFileManagerURL) {
            let windowFeatures = "left=95,top=100";
            windowAudioFeaturedInstance = window.open(tonicsFileManagerURL, 'Tonics File Manager', windowFeatures);
        }
    }else if (el.classList.contains('remove-audio-demo')) {
        if (audioDemoInputName) {
            audioDemoInputName.value = '';
        }
        audioDemoInput.classList.remove('d:none');
        removeAudioDemo.classList.add('d:none');
    }
}

window.addEventListener('message', (e) => {
    if (e.origin !== siteURL) {
        return;
    }
    let data = e.data;
    if (data.hasOwnProperty('cmd') && data.cmd === 'tonics:MediaLink') {
        if (audioDemoInput) {
            if (audioDemoInputName) {
                audioDemoInputName.value = data.value;
            }
            audioDemoInput.classList.add('d:none');
            removeAudioDemo.classList.remove('d:none');
            windowAudioFeaturedInstance.close();
        }
    }
});
// FOR FEATURED IMAGE
if (document.querySelector('main')){
    document.querySelector('main').addEventListener('click', featuredLinkHandler);
}

if (tinymce && tinymce.activeEditor && tinymce.activeEditor.dom){
    let tinySelectLinkHandler = tinymce.activeEditor.dom.select(".entry-content");
    if (tinySelectLinkHandler.length > 0){
        tinySelectLinkHandler[0].addEventListener('click', featuredLinkHandler);
    }
}
let  featuredLinkInput, featuredLinkWindowInstance = null;
function featuredLinkHandler(e){
    let el = e.target,
        parent = el.closest('[data-widget-form="true"]');
    if (parent) {
        featuredLinkInput = parent.querySelector('[data-widget-file-url="true"]');
    }

    if (el.classList.contains('tonics-featured-link')) {
        if (tonicsFileManagerURL) {
            let windowFeatures = "left=95,top=100";
            featuredLinkWindowInstance = window.open(tonicsFileManagerURL, 'Tonics File Manager', windowFeatures);
        }
    }
}

window.addEventListener('message', (e) => {
    if (e.origin !== siteURL) {
        return;
    }
    let data = e.data;
    if (data.hasOwnProperty('cmd')) {
        if (featuredLinkInput) {
            if (featuredLinkInput) {
                featuredLinkInput.value = data.value;
            }
            featuredLinkWindowInstance.close();
        }
    }
});
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
      return this;
    }
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
export {
  Query
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

// src/Util/Others/Draggables.ts
var Draggables = class extends ElementAbstract {
  constructor($draggableContainer) {
    super($draggableContainer);
    this.dragging = null;
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
        if (el.closest('.draggable') && startDrag) {
          self == null ? void 0 : self.setDragging(el.closest('.draggable'));
          let draggable = self.getDragging();
          shiftX = e.clientX;
          shiftY = e.clientY;
          draggable.classList.add("draggable-start");
          draggable.classList.add("touch-action:none");
          draggable.classList.remove("draggable-animation");
          self._draggingOriginalRect = draggable.getBoundingClientRect();
          let draggables = document.querySelectorAll(self.getDraggableElementDetails().draggable.draggableElement);
          if (draggables) {
            for (let i = 0, len = draggables.length; i < len; i++) {
              if (draggables[i] !== draggable) {
                let hiddenAboveDraggable = draggables[i].querySelector(".draggable-hidden-over");
                if (hiddenAboveDraggable) {
                  hiddenAboveDraggable.classList.add("position:absolute");
                }
              }
            }
          }
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
          let draggables = document.querySelectorAll(self.getDraggableElementDetails().draggable.draggableElement);
          if (draggables) {
            for (let i = 0, len = draggables.length; i < len; i++) {
              let hiddenAboveDraggable = draggables[i].querySelector(".draggable-hidden-over");
              if (hiddenAboveDraggable) {
                hiddenAboveDraggable.classList.remove("position:absolute");
              }
            }
          }
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
        if (el.closest(".draggable") && startDrag && draggable) {
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
  isMouseActive() {
    return this.mouseActive;
  }
  setMouseActive(result) {
    this.mouseActive = result;
  }
};
__name(Draggables, "Draggables");
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
var __create = Object.create;
var __defProp = Object.defineProperty;
var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
var __getOwnPropNames = Object.getOwnPropertyNames;
var __getProtoOf = Object.getPrototypeOf;
var __hasOwnProp = Object.prototype.hasOwnProperty;
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
    try {
      this.http.onload = function() {
        callBack(null, self2.http.responseText);
      };
    } catch (e) {
      callBack("Something Went Wrong: " + e.description);
    }
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
    navigator.permissions.query({ name: "clipboard-write" }).then((result) => {
      if (result.state == "granted" || result.state == "prompt") {
        navigator.clipboard.writeText(clip).then(() => {
          resolve(clip);
        });
      }
    }).catch(() => {
      reject();
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
    showLoaderOnConfirm: true,
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
function getAllSelectedFiles() {
  return document.querySelectorAll('[data-selected="true"]');
}
__name(getAllSelectedFiles, "getAllSelectedFiles");
function addHiddenInputToForm(form, key, value) {
  const input = document.createElement("input");
  input.type = "hidden";
  input.name = key;
  input.value = value;
  form.appendChild(input);
}
__name(addHiddenInputToForm, "addHiddenInputToForm");
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
function getAppURL() {
  let APP_URL = "/api/media/app_url";
  return new Promise(function(resolve, reject) {
    let XHRAPI = new XHRApi();
    XHRAPI.Get(APP_URL, function(err, data) {
      if (err) {
        reject();
      }
      if (data) {
        data = JSON.parse(data);
        if (data.hasOwnProperty("status")) {
          if (data.status == 200) {
            resolve(data.message);
          }
        }
      }
    });
  });
}
__name(getAppURL, "getAppURL");
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
  let csrf = "";
  csrfNames.forEach((value, index) => {
    let inputCSRF = document.querySelector(`input[name=${value}]`);
    if (inputCSRF) {
      csrf = inputCSRF.value;
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
function storageAvailable(type = "localStorage") {
  let storage;
  try {
    storage = window[type];
    const x = "__storage_test__";
    storage.setItem(x, x);
    storage.removeItem(x);
    return true;
  } catch (e) {
    return e instanceof DOMException && (e.code === 22 || e.code === 1014 || e.name === "QuotaExceededError" || e.name === "NS_ERROR_DOM_QUOTA_REACHED") && (storage && storage.length !== 0);
  }
}
__name(storageAvailable, "storageAvailable");
export {
  activateMenus,
  addHiddenInputToForm,
  contextMenuListCreator,
  copyToClipBoard,
  deActivateMenus,
  errorToast,
  fileLoadMoreButton,
  filesLoadingAnimation,
  getAllSelectedFiles,
  getAppURL,
  getCSRFFromInput,
  getFileDirectory,
  getFileExtension,
  infoToast,
  inputToast,
  loadScriptDynamically,
  promptToast,
  slug,
  storageAvailable,
  str_replace,
  successToast,
  titleCase
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

        let copyEl2 = el2.cloneNode(true);
        el1.parentNode.insertBefore(copyEl2, el1);
        el2.parentNode.insertBefore(el1, el2);
        el2.parentNode.replaceChild(el2, copyEl2);
    }

    el2.addEventListener("transitionend", () => {
        swap();
        if (onSwapDone){
            onSwapDone();
        }
    }, { once: true });
}hookTinyMCE();

function hookTinyMCE() {
    if (typeof tinymce !== 'undefined') {
        let allTinyArea = document.querySelectorAll('.tinyMCEBodyArea');
        allTinyArea.forEach(tinyArea => {
            tinyArea.dataset.tinyinstance = 'true';
            tinyArea.id = 'tinyMCEBodyArea' + new Date().valueOf();
            addTiny('#' + tinyArea.id);
        });

        const tinyDialogObserver = new MutationObserver(((mutationsList, observer) => {
            for (const mutation of mutationsList) {
                // added nodes.
                let addedNode = mutation.addedNodes[0];
                if (mutation.addedNodes.length > 0 && addedNode.nodeType === Node.ELEMENT_NODE) {
                    let tinyArea = addedNode.querySelector('.tinyMCEBodyArea');
                    if (tinyArea) {
                        // if tinyInstance is available, re-initialize it
                        if (tinyArea.dataset.tinyinstance === 'true') {
                            let allTinyArea = document.querySelectorAll('.tinyMCEBodyArea');
                            allTinyArea.forEach(tinyArea => {
                                tinymce.execCommand("mceRemoveEditor", false, tinyArea.id);
                                tinyArea.id = 'tinyMCEBodyArea' + new Date().valueOf();
                                addTiny('#' + tinyArea.id);
                            });
                            return;
                        }

                        // else...
                        tinyArea.dataset.tinyinstance = 'true';
                        tinyArea.id = 'tinyMCEBodyArea' + new Date().valueOf();
                        addTiny('#' + tinyArea.id);
                    }
                }
            }
        }));
        // Start observing the target node for configured mutations
        tinyDialogObserver.observe(document.querySelector('main'), {attributes: false, childList: true, subtree: true});
    }
}

let previousTinyPositionBeforeFullScreenStateChange = null,
    fromOnFullScreenState = false;

function addTiny(editorID) {
    let tinyAssets = document.querySelector('template.tiny-mce-assets'),
        content_css = '',
        tinyJSAssets = null, tinyCSSAssets = null;
        if(tinyAssets){
            tinyJSAssets = tinyAssets.content.querySelectorAll('.js');
            tinyCSSAssets = tinyAssets.content.querySelectorAll('.css');

            tinyCSSAssets.forEach((css) => {
                content_css += css.value + ',';
            });
            content_css = content_css.slice(0, -1);
        }

    return tinymce.init({
        // add support for image lazy loading
        extended_valid_elements: "img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name|loading=lazy|decoding=async]," +
        "svg[*],path[*],def[*],script[*],use[*]",
        selector: editorID,
        height: 900,
        menubar: true,
        plugins: [
            'advlist', 'tonics-drivemanager', 'tonics-fieldselectionmanager', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor',
            'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount',
        ],
        noneditable_class: 'tonics-legend',
        editable_class: 'widgetSettings,dropdown-toggle',
        // fullscreen_native: true,
        toolbar: 'undo redo | tonics-drivemanager tonics-fieldselectionmanager link image media | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist | help',
        content_style: 'body { font-family:IBMPlexSans-Regular,Times New Roman,serif; font-size:20px }',
        contextmenu: "link image | copy searchreplace tonics-drivemanager | tonics-fieldselectionmanager | bold italic blocks align",
        content_css : content_css,
        body_class : "entry-content",
        remove_trailing_brs: true,
        setup: function (editor) {
            editor.on('init', function (e) {
                if (tinyJSAssets.length > 0) {
                    tinyJSAssets.forEach((js) => {
                        let script = document.createElement("script");
                        script.type = 'module';
                        script.src = js.value;
                        script.async = true;
                        tinymce.activeEditor.dom.select('head')[0].appendChild(script);
                    });
                }

                let svgInline = document.querySelector('.tonics-inline-svg');
                if (svgInline){
                    svgInline = svgInline.cloneNode(true);
                    editor.getBody().previousElementSibling.insertAdjacentElement('afterbegin', svgInline);
                }

                if (fromOnFullScreenState) {
                    tinymce.execCommand("mceFullScreen", false, e.target.id);
                }
            });
            editor.on('init change blur', function (e) {
                if (editor.getBody().hasChildNodes()){
                    let nodesData = {}, key = 0;
                    let nodesMap = new Map();
                    let bodyNode = editor.getBody().childNodes;
                    bodyNode.forEach((node) => {
                        if (node.classList.contains('tonics-field-items-unique')){
                            if (nodesData.hasOwnProperty(key)){
                                ++key;
                            }
                            nodesData[key] = {content: node.outerHTML, raw: false};
                        } else {
                            if (nodesData.hasOwnProperty(key) && nodesData[key].raw === false){
                                ++key;
                            }

                            let previousContent = (nodesData.hasOwnProperty(key)) ?  nodesData[key].content : '';
                            nodesData[key] = {content: previousContent + node.outerHTML, raw: true};
                        }
                    });
                    console.log(nodesData);
                }
                tinymce.triggerSave();
            });

            editor.on('FullscreenStateChanged', function (e) {
                // hack to get full screen to work from a nested container
                if (fromOnFullScreenState === false){
                    let tinyArea = e.target.container,
                        tinyID = e.target.id,
                        IDQuery = document.querySelector('#' + tinyID);

                    if (previousTinyPositionBeforeFullScreenStateChange === null) {
                        previousTinyPositionBeforeFullScreenStateChange = tinyArea.parentElement;
                    }
                    if (tinyArea.classList.contains('tox-fullscreen')) {
                        // we add the editor to body first child, this way, fullscreen works with no quirks
                        document.querySelector('body').insertAdjacentElement('afterbegin', IDQuery);
                        tinymce.execCommand("mceRemoveEditor", false, IDQuery.id);
                        IDQuery.id = 'tinyMCEBodyArea' + new Date().valueOf();
                        fromOnFullScreenState = true;
                        addTiny('#' + IDQuery.id).then(function(editors) {
                            // reset for next event, this would be called after editor.on('init')
                            fromOnFullScreenState = false;
                        });
                    } else {
                        // we return the editor back to its position
                        previousTinyPositionBeforeFullScreenStateChange.insertAdjacentElement('beforeend', IDQuery);
                        tinymce.execCommand("mceRemoveEditor", false, IDQuery.id);
                        IDQuery.id = 'tinyMCEBodyArea' + new Date().valueOf();
                        fromOnFullScreenState = false;
                        previousTinyPositionBeforeFullScreenStateChange = null;
                        addTiny('#' + IDQuery.id);
                    }
                }
            });
        }
    });
}import * as myModule from "./script-combined.js";
window.myModule = myModule;

// Load Fields Scripts:
let scripts = document.querySelectorAll("[data-script_path]");
scripts.forEach((script) => {
    myModule.loadScriptDynamically(script.dataset.script_path, script.dataset.script_path).then()
});

let draggable = document.getElementsByClassName('draggable'),
    parent = '.menu-arranger',
    fieldChild = `.menu-arranger-li`,
    top = false, bottom = false,
    sensitivity = 0, sensitivityMax = 5,
    fieldFormCollected = new Map();

let menuArranger = document.getElementsByClassName('menu-arranger')[0];
let fieldPickerContainer = document.getElementsByClassName('menu-field')[0];

let fieldSlug = document.querySelector('input[name="field_slug"]'),
    fieldID = document.querySelector('input[name="field_id"]');
if (fieldSlug){
    fieldSlug = fieldSlug.value
}
if (fieldID){
    fieldID = fieldID.value
}

try {
    let menuField = document.querySelector('.menu-field');
    if (menuField){
        new myModule.MenuToggle('.menu-field', new myModule.Query())
            .settings('.menu-box-li', '.dropdown-toggle', '.child-menu')
            .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
            .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
            .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
            .stopPropagation(false)
            .closeOnClickOutSide(false)
            .run();
    }

    if (menuArranger){
        new myModule.MenuToggle('.menu-arranger', new myModule.Query())
            .settings('.menu-arranger-li', '.dropdown-toggle', '.menu-widget-information')
            .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
            .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
            .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
            .closeOnClickOutSide(false)
            .stopPropagation(false)
            .run();
    }
} catch (e) {
    console.log("Can't set MenuToggle: menu-widget or menu-arranger");
}

new myModule.Draggables(parent)
    .settings(fieldChild, ['legend', 'input', 'textarea', 'select', 'label'], false) // draggable element
    .onDragDrop(function (element, self) {
        // to the right
        let elementDragged = self.getDragging().closest(fieldChild);

        let dragToTheBottom = document.querySelector(parent).querySelector('.drag-to-the-bottom');
        if (bottom && dragToTheBottom) {
            swapNodes(elementDragged, dragToTheBottom, self.draggingOriginalRect);
            dragToTheBottom.classList.remove('drag-to-the-bottom', 'drag-to-the-top', 'nested-to-the-left', 'nested-to-the-right');
            bottom = false;
        }

        let dragToTheTop = document.querySelector(parent).querySelector('.drag-to-the-top');
        if (top && dragToTheTop){
            swapNodes(elementDragged, dragToTheTop, self.draggingOriginalRect);
            dragToTheTop.classList.remove('drag-to-the-bottom', 'drag-to-the-top', 'nested-to-the-left', 'nested-to-the-right');
            top = false;
        }
        setListDataArray();
    }).onDragTop((element) => {
    if (sensitivity++ >= sensitivityMax){
        let dragToTheTop = element.previousElementSibling;
        if (dragToTheTop && dragToTheTop.classList.contains('menu-arranger-li')){
            top = true;
            dragToTheTop.classList.add('drag-to-the-top');
        }
        sensitivity = 0;
    }
}).onDragBottom( (element) => {
    if (sensitivity++ >= sensitivityMax){
        let dragToTheBottom = element.nextElementSibling;
        if (dragToTheBottom && dragToTheBottom.classList.contains('menu-arranger-li')) {
            bottom = true;
            dragToTheBottom.classList.add('drag-to-the-bottom');
        }
        sensitivity = 0;
    }
}).run();

function setListDataArray() {
    if(draggable){
        for(let i = 0, len = draggable.length ; i < len ; i++) {
            let id = i + 1;
            draggable[i].setAttribute("data-id", id); // add ID's to all draggable item
            let parentID = null;
            let parentDraggable = draggable[i].parentElement.closest('.draggable');
            if (parentDraggable){
                parentID = parentDraggable.getAttribute("data-id");
            }
            draggable[i].setAttribute("data-parentid",
                (draggable[i].classList.contains('menu-arranger-li'))  ? parentID : null)
        }
        for(let i = 0, len = draggable.length ; i < len ; i++) {
            let cell = 1;
            let cellsEl = draggable[i].querySelectorAll('.row-col-item');
            cellsEl.forEach((cellEl) => {
                if (cellEl.querySelector('.draggable')){
                    if (cellEl.querySelector('.draggable').dataset.parentid === draggable[i].dataset.id){
                        cellEl.dataset.cell =`${cell}`;
                        cell++;
                    }
                }
            });
        }
        return getListDataArray();
    }
}

function getListDataArray() {
    if(draggable){
        let ListArray = [],
            fieldName = '',
            fieldSettingsEl = document.querySelectorAll('.widgetSettings'),
            i = 0,
            parentID = null;
        fieldSettingsEl.forEach(form => {
            let formTagname = form.tagName.toLowerCase();
            if (formTagname === 'form' || formTagname === 'div'){
                let draggable = form.closest('.draggable');
                parentID = draggable.getAttribute('data-parentid');
                if (parentID === 'null'){
                    parentID = null;
                }
                if(draggable.querySelector('input[name="field_slug"]') ){
                    fieldName = draggable.querySelector('input[name="field_slug"]').value;
                }
                let elements = form.querySelectorAll('input, textarea, select'),
                    firstElementParentID = elements[0].closest('.draggable').getAttribute('data-id');

                let widgetSettings = {};
                let collectCheckboxes = draggable.querySelectorAll("[data-collect_checkboxes]");
                collectCheckboxes.forEach((checkbox) => {
                    let checkboxName = checkbox.name;
                    if (!widgetSettings.hasOwnProperty(checkboxName)){
                        widgetSettings[checkboxName] = [];
                    }
                    if (checkbox.checked){
                        widgetSettings[checkboxName].push(checkbox.value);
                    }
                });

                elements.forEach((inputs) => {
                    if (inputs.closest('.draggable').dataset.id === firstElementParentID){
                        if (!widgetSettings.hasOwnProperty(inputs.name)){
                            widgetSettings[inputs.name] = inputs.value;
                            if (draggable.closest("[data-cell]")){
                                widgetSettings[`${fieldName}_cell`] = draggable.closest("[data-cell]").dataset.cell;
                            }
                        }
                    }
                });
                i = i+1;
                ListArray.push({
                    "fk_field_id": fieldID,
                    "field_id": i,
                    "field_parent_id": (draggable.classList.contains('menu-arranger-li')) ? parentID : null,
                    "field_name": fieldName,
                    "field_options": JSON.stringify(widgetSettings),
                });
            }
        });
        return ListArray;
    }
}

/**
 * @param requestHeaders
 * @protected
 */
function defaultXHR(requestHeaders = {})
{
    let defaultHeader = {};
    return new XHRApi({...defaultHeader, ...requestHeaders});
}

if(fieldPickerContainer){
    fieldPickerContainer.addEventListener('click',  (e) => {
        let el = e.target
        if(el.classList.contains('is-menu-checked')) {
            let checkedItems = el.parentNode.querySelectorAll('input[name=field-item]:checked');
            if (checkedItems.length > 0){
                checkedItems.forEach(((checkbox, key) => {
                    if (checkbox.dataset.hasOwnProperty('script_path')) {
                        myModule.loadScriptDynamically(checkbox.dataset.script_path, checkbox.dataset.script_path).then((e) => {
                            fieldSelectedHandler(checkbox);
                        });
                    } else {
                        fieldSelectedHandler(checkbox);
                    }
                }));
            }
        }
    });
}

function fieldSelectedHandler(checkbox) {
    let selectedCellItems = document.querySelectorAll('input[name=cell]:checked');
    checkbox.checked = false;
    let action = checkbox.dataset.action,
        name = checkbox.dataset.name,
        slug = checkbox.dataset.slug,
        url = window.location.href + `?action=${action}&slug=${slug}`;

    let form = '';
    if (fieldFormCollected.has(slug)) {
        form = fieldFormCollected.get(slug);
        if (selectedCellItems.length > 0){
            selectedCellItems.forEach(cell => {
                cell = cell.closest('.row-col-item');
                cell.insertAdjacentHTML('beforeend', generateFieldData(name, slug, form))
            });
        } else {
            if (menuArranger) {
                menuArranger.insertAdjacentHTML('beforeend', generateFieldData(name, slug, form))
            }
        }
    } else {
        defaultXHR().Get(url, function (err, data) {
            if (data) {
                data = JSON.parse(data);
                if (data.hasOwnProperty('status') && data.status === 200) {
                    fieldFormCollected.set(slug, data.data);
                    form = fieldFormCollected.get(slug);
                    if (selectedCellItems.length > 0){
                        selectedCellItems.forEach(cell => {
                            cell = cell.closest('.row-col-item');
                            cell.insertAdjacentHTML('beforeend', generateFieldData(name, slug, form))
                        });
                    } else {
                        if (menuArranger) {
                            menuArranger.insertAdjacentHTML('beforeend', generateFieldData(name, slug, form))
                        }
                    }
                }
            }
        });
    }
}

function unSelectSelectedCell() {
    let selectedCellItems = document.querySelectorAll('[data-selected="true"]');
    selectedCellItems.forEach(cell => {
        unHighlightFile(cell);
    });
}

function generateFieldData(name, slug, more) {
    let changeID = (Math.random()*1e32).toString(36);
    return  more.replace(/CHANGEID/gi, changeID);
}

// delete menu or widget
if (menuArranger){
    menuArranger.addEventListener('click', (e) => {
        let el = e.target;
        if (el.classList.contains('delete-menu-arrange-item')){
            let arranger = el.closest('.draggable');
            if (arranger){
                arranger.remove();
                setListDataArray();
            }
        }
    });
}


// save menu builder
let saveAllMenu = document.querySelector('.save-menu-builder-changes'),
    saveMenuChangesForm = document.getElementById('saveFieldBuilderItems');
if(saveAllMenu && saveMenuChangesForm){
    saveAllMenu.addEventListener('click', function (e) {
        e.preventDefault();
        setListDataArray();
        addHiddenInputToForm(saveMenuChangesForm, 'fieldSlug', fieldSlug);
        addHiddenInputToForm(saveMenuChangesForm, 'fieldDetails', JSON.stringify({
            fieldID: fieldID, // This is the field_slug that houses the menu items
            fieldSlug: fieldSlug, // This is the field_slug that houses the menu items
            fieldItems: getListDataArray(),
        }));
        saveMenuChangesForm.submit();
    })
}
try {
    if (tonicsErrorMessages instanceof Array && tonicsErrorMessages.length > 0){
        tonicsErrorMessages.forEach((value) => {
            myModule.errorToast(value, 6000);
        });
    }

    if (tonicsInfoMessages instanceof Array && tonicsInfoMessages.length > 0){
        tonicsInfoMessages.forEach((value) => {
            myModule.infoToast(value, 6000);
        });
    }

    if (tonicsSuccesssMessages instanceof Array && tonicsSuccesssMessages.length > 0){
        tonicsSuccesssMessages.forEach((value) => {
            myModule.successToast(value, 6000);
        });
    }

} catch (e) {
   // console.log(e.toLocaleString());
}try {
    new myModule.MenuToggle('.site-nav', new myModule.Query())
        .settings('.menu-block', '.dropdown-toggle', '.child-menu')
        .buttonIcon('#tonics-arrow-up', '#tonics-arrow-down')
        .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
        .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
        .closeOnClickOutSide(true)
        .run();
}catch (e) {
    console.error("An Error Occur Setting MenuToggle: Site-Nav")
}