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
export {
  StorageDriversManager
};
