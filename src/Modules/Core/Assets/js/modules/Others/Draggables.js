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
        if (el.closest(".draggable") && startDrag) {
          self == null ? void 0 : self.setDragging(el.closest(".draggable"));
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
        if (el.closest(".draggable") && startDrag && draggable) {
          draggable.classList.add("pointer-events:none");
          let elemBelow = document.elementFromPoint(e.clientX, e.clientY);
          self.setDroppedTarget(elemBelow.closest(".draggable"));
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
