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
import * as myModule from "./script-combined.js";try {
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