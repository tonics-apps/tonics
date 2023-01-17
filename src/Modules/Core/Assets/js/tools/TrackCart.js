
export class TrackCart extends SimpleState {

    constructor() {
        super();
        // some logic here
        super.setCurrentState(this.InitialState);

        // For Cart Toggle
        window.TonicsScript.MenuToggle('.tonics-cart-container', window.TonicsScript.Query())
            .settings('.cart-button-counter', '.cart-button', '.cart-child-container')
            .menuIsOff(["swing-out-top-fwd", "d:none"], ["swing-in-top-fwd", "d:flex"])
            .menuIsOn(["swing-in-top-fwd", "d:flex"], ["swing-out-top-fwd", "d:none"])
            .closeOnClickOutSide(true)
            .stopPropagation(true)
            .run();
    }

    InitialState() {
        console.log('You Entered The InitialState, Move To UpdateLicenseNameAndPrice State');
        return this.switchState(this.UpdateLicenseNameAndPrice, SimpleState.NEXT);
    }

    AddItemToCartState() {

    }

    RemoveItemFromCartState() {

    }

    UpdateLicenseNameAndPrice() {
        console.log('You Moved Into UpdateLicenseNameAndPrice State, Move To UpdateCartBasketNumber');
        return this.switchState(this.UpdateCartBasketNumberState, SimpleState.NEXT);
    }

    UpdateCartBasketNumberState() {
        console.log('You Moved Into UpdateCartBasketNumberState State');
    }

    TotalItemsPriceInCartState() {

    }

    AddCartToLocalStorageState() {

    }

    ReloadCartFromLocalStorageState() {

    }
}

new TrackCart().runStates();