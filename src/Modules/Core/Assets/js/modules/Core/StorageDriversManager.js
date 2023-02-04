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
