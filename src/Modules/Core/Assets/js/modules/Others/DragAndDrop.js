
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
    let $dragAndDropElement = this.getQueryResult(), self = this;
    if ($dragAndDropElement) {
      $dragAndDropElement.addEventListener("dragenter", function(e) {
        self.preventDefaults(e);
        $dragAndDropElement.classList.add("highlight");
        let onDragEnter = self.getDragAndDropElementDetails().callbacks.onDragEnter;
        if (onDragEnter !== null && typeof onDragEnter == "function") {
          onDragEnter(e);
        }
      });
      $dragAndDropElement.addEventListener("dragover", function(e) {
        self.preventDefaults(e);
        $dragAndDropElement.classList.add("highlight");
        let onDragOver = self.getDragAndDropElementDetails().callbacks.onDragOver;
        if (onDragOver !== null && typeof onDragOver == "function") {
          onDragOver(e);
        }
      });
      $dragAndDropElement.addEventListener("dragleave", function(e) {
        self.preventDefaults(e);
        $dragAndDropElement.classList.remove("highlight");
        let onDragLeave = self.getDragAndDropElementDetails().callbacks.onDragLeave;
        if (onDragLeave !== null && typeof onDragLeave == "function") {
          onDragLeave(e);
        }
      });
      $dragAndDropElement.addEventListener("drop", function(e) {
        self.preventDefaults(e);
        $dragAndDropElement.classList.remove("highlight");
        let onDragDrop = self.getDragAndDropElementDetails().callbacks.onDragDrop;
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
export {
  DragAndDrop
};
