
export class TrackCart extends SimpleState {

    static cartStorageKey = 'Tonics_Cart_Key_Audio_Store';
    shakeCartButtonAnimation = true;
    cartStorageData = new Map();
    licenseData = null;
    cartItemToRemove = null;

    constructor() {
        super();
    }

    getCartStorageKey()
    {
        return TrackCart.cartStorageKey;
    }

    InitialState() {
        let cart = this.getCart();
        cart.set(this.licenseData.slug_id, this.licenseData);
        this.cartStorageData = cart;
        return this.switchState(this.AddCartToLocalStorageState, SimpleState.NEXT);
    }

    AddCartToLocalStorageState() {
        localStorage.setItem(TrackCart.cartStorageKey, JSON.stringify(Array.from(this.cartStorageData)));
        return this.switchState(this.UpdateCartItemInfo, SimpleState.NEXT);
    }

    UpdateCartItemInfo() {
        let cartHeader = document.querySelector('.tonics-cart-items-container');
        if (cartHeader){
            const cart = this.getCart();
            // Remove All Cart Items
            let cartItems = document.querySelectorAll(`.cart-item[data-slug_id]`);
            if (cartItems){
                cartItems.forEach((cartItem) => {
                    cartItem.remove();
                });
            }
            for (let [key, value] of cart.entries()) {
                cartHeader.insertAdjacentHTML('beforeend', this.getLicenseFrag(value));
            }
        }

        return this.switchState(this.UpdateCartBasketNumberState, SimpleState.NEXT);
    }

    UpdateCartBasketNumberState() {
        let cartCounter = document.querySelector('.cb-counter-label');
        if (cartCounter){
            cartCounter.innerHTML = `${this.getCart().size}`;
            if (this.shakeCartButtonAnimation){
                this.shakeCartButton();
            }
        }
        return this.switchState(this.TotalItemsPriceInCartState, SimpleState.NEXT);
    }

    RemoveItemFromCartState() {
        if (this.cartItemToRemove){
            let slug_id = this.cartItemToRemove?.dataset?.slug_id;
            let cart = this.getCart();
            if (cart.has(slug_id)){
                this.cartItemToRemove.remove();
                cart.delete(slug_id);
                localStorage.setItem(TrackCart.cartStorageKey, JSON.stringify(Array.from(cart)));
            }
        }

        return this.switchState(this.UpdateCartItemInfo, SimpleState.NEXT);
    }

    TotalItemsPriceInCartState() {
        let tonicsCheckoutPrice = document.querySelector('.tonics-checkout-price');

        if (tonicsCheckoutPrice){
            let currency = 'USD', locale = 'en-US';
            let totalPrice = this.getTotalItemPrice();

            // Format it in USD
            // Create our CURRENCY Formatter, thanks to Intl.NumberFormat.
            // Usage is formatter.format(2500); /* $2,500.00 */
            const formatter = new Intl.NumberFormat(locale, {
                style: 'currency',
                currency: currency,
            });
            totalPrice = formatter.format(totalPrice);
            tonicsCheckoutPrice.innerHTML = `${totalPrice}`;
        }

        return SimpleState.DONE;
    }

    ReloadCartFromLocalStorageState() {
        return this.switchState(this.UpdateCartItemInfo, SimpleState.NEXT);
    }

    // That is if a cart is added to the cart menu, we change the cart icon to remove icon
    // this way, a user can remove the cart icon
    UpdateCartIconAdditionToTheCartMenuState(args) {
        if(args.length > 0){
            let trackDownloadContainer = args[0];
            let trackSlugID = trackDownloadContainer.closest('[data-slug_id]')?.dataset.slug_id;
            let licenses = trackDownloadContainer.querySelectorAll('[data-unique_id]');
            let cart = this.getCart();
            if(licenses.length > 0){
                licenses.forEach((license) => {
                    // By Default, we remove the remove icon even if we would later add it when the unique_id matches
                    this.removeIconDeleteButton(license);

                    for (let [key, value] of cart.entries()) {

                        if (trackSlugID !== key){
                            continue;
                        }

                        let licenseUniqueID = license.dataset?.unique_id;
                        let cartStorageUniqueID = value?.unique_id;
                        if ((licenseUniqueID && cartStorageUniqueID) && (licenseUniqueID === cartStorageUniqueID)){
                            let buttonTitle = license.title;
                            let svgElement = license.querySelector('svg');
                            let useElement = license.querySelector('use');

                            if (svgElement && useElement){
                                license.dataset.remove_from_cart = 'true';
                                license.title = 'Remove From Cart'
                                svgElement.dataset.prev_button_title = buttonTitle;
                                svgElement.classList.add('color:red')
                                useElement.setAttribute("xlink:href", "#tonics-remove");
                            }
                            break;
                        }
                    }
                });
            }
            return SimpleState.DONE;
        }

    }

    RemoveItemFromCartWithUniqueID(args) {
        if(args.length > 0){
            let licenseButton = args[0];
            let licenseUniqueID = licenseButton.dataset?.unique_id;
            let trackSlugID = licenseButton.closest('[data-slug_id]')?.dataset.slug_id;
            let cart = this.getCart();

            for (let [key, value] of cart.entries()) {

                if (trackSlugID !== key){
                    continue;
                }

                let cartStorageUniqueID = value?.unique_id;
                if ((licenseUniqueID && cartStorageUniqueID) && (licenseUniqueID === cartStorageUniqueID)){
                    this.removeIconDeleteButton(licenseButton);
                    cart.delete(key);
                    localStorage.setItem(TrackCart.cartStorageKey, JSON.stringify(Array.from(cart)));
                    break;
                }
            }
        }

        return this.switchState(this.UpdateCartItemInfo, SimpleState.NEXT);
    }

    removeIconDeleteButton(licenseButton){
        let svgElement = licenseButton.querySelector('svg');
        let useElement = licenseButton.querySelector('use')

        if (!licenseButton.dataset.hasOwnProperty('indie_license_type_is_free') && (svgElement && useElement)){
            licenseButton.removeAttribute("data-remove_from_cart");
            licenseButton.title = svgElement?.dataset?.prev_button_title ?? licenseButton.title;
            svgElement.removeAttribute("data-prev_button_title");
            svgElement.classList.remove('color:red')
            useElement.setAttribute("xlink:href", "#tonics-cart");
        }
    }

    getCart() {
        if (localStorage.getItem(TrackCart.cartStorageKey) !== null) {
            let storedMap = localStorage.getItem(TrackCart.cartStorageKey);
            this.cartStorageData = new Map(JSON.parse(storedMap));
        }
        return this.cartStorageData;
    }

    /**
     * This method calculates the total price of all items in the cart, taking into account the quantity of each item.
     * @returns {unknown}
     */
    getTotalItemPrice() {
        // Convert the Map returned by `this.getCart()` into an array using `Array.from()`, and then use the `Array.reduce()` method to calculate the total price.
        return Array.from(this.getCart().values())
            // if quantity is not available, we default to 1
            .reduce((total, { price, quantity = 1 }) => {
                // For each item in the cart, check if it has a valid `price` property, and if so, calculate the item price by multiplying the price by the quantity.
                if (price) {
                    total += parseFloat(price) * parseInt(quantity);
                } else {
                    // If the item is missing a `price` property, log an error message to the console with details of the invalid item.
                    console.error(`Invalid item in cart: ${JSON.stringify({ price, quantity })}`);
                }

                return total; // Return the running total of item prices.
            }, 0); // The initial value of the total is set to 0.
    }

    getLicenseFrag(data) {
        let currency = '$';
        return `            
            <div data-slug_id="${data.slug_id}" class="cart-item d:flex flex-d:row flex-wrap:wrap padding:2rem-1rem align-items:center flex-gap">
                <img data-audioplayer_globalart src="${data.track_image}" class="image:avatar" 
                alt="${data.track_title}">
                <div class="cart-detail">
                    <a data-tonics_navigate data-url_page="${data.url_page}" href="${data.url_page}"><span class="text cart-title color:black">${data.track_title}</span></a> 
                    <span class="text cart-license-price">${data.name}
                <span> → (${currency}${data.price})</span>
            </span>
                    <button data-slug_id="${data.slug_id}" class="tonics-remove-cart-item background:transparent border:none color:black bg:white-one border-width:default border:black padding:small cursor:pointer button:box-shadow-variant-1">
                        <span class="text text:no-wrap">Remove</span>
                    </button>
                </div>
            </div>`;
    }

    shakeCartButton(){
        let cartButton = document.querySelector('.cart-button');
        if (cartButton){
            cartButton.classList.add("jello-diagonal-1"); // Add Animation to cart button
            setTimeout(function () { // Remove Animation After 1 sec
                cartButton.classList.remove("jello-diagonal-1");
            }, 1000);
        }
    }
}


