var a=Object.defineProperty;var i=(r,e)=>a(r,"name",{value:e,configurable:!0});var t=class{constructor(){this._$driveSystem=new Map}get $driveSystem(){return this._$driveSystem}attachDriveStorage(e){return this.$driveSystem.set(e.driveSignature,e),this}detachDriveStorage(e){this.$driveSystem.has(e)&&this.$driveSystem.delete(e)}getDriveStorage(e){if(this.$driveSystem.has(e))return this.$driveSystem.get(e);throw new DOMException(`DriveStorage "${e}" doesn't exist`)}getFirstDriveStorage(){return[...this.$driveSystem][0][1]}};i(t,"StorageDriversManager");window.hasOwnProperty("TonicsMedia")||(window.TonicsMedia={});window.TonicsMedia.StorageDriversManager=()=>new t;export{t as StorageDriversManager};