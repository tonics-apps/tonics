export class TrackCart extends SimpleState {

    static cartStorageKey = 'Tonics_Cart_Key_Audio_Store';
    cartStorageData = new Map();
    licenseData = null;
    static cartStorageData = new Map();

    constructor(licenseData = null) {
        super();
        this.licenseData = licenseData;
        // super.setCurrentState(initialState);
    }

    InitialState() {
        let cart = this.getCart();
        cart.set(this.licenseData.slug_id, this.licenseData);
        this.cartStorageData = cart;
        return this.switchState(this.AddCartToLocalStorageState, SimpleState.NEXT);
    }

    AddCartToLocalStorageState() {
        localStorage.setItem(TrackCart.cartStorageKey, JSON.stringify(Array.from(this.cartStorageData)));
        return this.switchState(this.UpdateCartLicenseInfo, SimpleState.NEXT);
    }

    UpdateCartLicenseInfo() {
        let cartHeader = document.querySelector('.tonics-cart-items-container');
        if (cartHeader){
            for (let [key, value] of this.getCart().entries()) {
                let cartItem = document.querySelector(`.cart-item[data-slug_id="${key}"`);
                if (cartItem){
                    cartItem.remove();
                }
                cartHeader.insertAdjacentHTML('beforeend', this.getLicenseFrag(value));
            }
        }



        // console.log(this.getCart(), cart);
        // console.log('You Moved Into UpdateLicenseNameAndPrice State, Move To UpdateCartBasketNumber');
        // return this.switchState(this.UpdateCartBasketNumberState, SimpleState.NEXT);
    }

    AddItemToCartState() {

    }

    RemoveItemFromCartState() {

    }

    UpdateCartBasketNumberState() {
        console.log('You Moved Into UpdateCartBasketNumberState State');
    }

    TotalItemsPriceInCartState() {

    }

    ReloadCartFromLocalStorageState() {

    }

     getCart() {
        if (localStorage.getItem(TrackCart.cartStorageKey) !== null) {
            let storedMap = localStorage.getItem(TrackCart.cartStorageKey);
            this.cartStorageData = new Map(JSON.parse(storedMap));
        }
        return this.cartStorageData;
    }

    getLicenseFrag(data) {
        let currency = '$';
        return `            
            <div data-slug_id="${data.slug_id}" class="cart-item d:flex flex-wrap:wrap padding:2rem-1rem align-items:center flex-gap">
                <img data-audioplayer_globalart src="${data.track_image}" class="image:avatar" 
                alt="${data.track_title}">
                <div class="cart-detail">
                    <span class="text cart-title">${data.track_title}</span>
                    <span class="text cart-license-price">${data.name}
                <span> → (${currency}${data.price})</span>
            </span>
                    <button class="background:transparent border:none color:black bg:white-one border-width:default border:black padding:small cursor:pointer button:box-shadow-variant-1">
                        <span class="text text:no-wrap">Remove</span>
                    </button>
                </div>
            </div>`;
    }
}



