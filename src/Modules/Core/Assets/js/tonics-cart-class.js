
/*
 *     Copyright (c) 2021-2024. Olayemi Faruq <olayemi@tonics.app>
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

/***************************************
 ---------------------------------------
 CLASSES FOR OUR CARTS FUNCTIONALITY
 ---------------------------------------
 **************************************/

// Code Template or Class for the CART Object
class CartInfo { // for getting the cart info, e.g licenseID, licensePrice, etc
    constructor(licenseID, licensePrice, ArrayofItemsID, itemID) { // the constructor
        this.licenseID = licenseID;
        this.licensePrice = licensePrice;
        this.ArrayofItemsID = ArrayofItemsID; // This get an arrays of ArrayofItemsID
        this.itemID = itemID; // This serves as the track unique id in our database
    }

    /**
     * This deals with all ajax related request for the cart experience
     * @param method
     * @param url
     * @param done
     */
    static cartAjax(method, url, done) {
        let xhr = new XMLHttpRequest();
        xhr.open(method, url, true);

        xhr.onreadystatechange = function () { // Callback function
            // onreadystatechange property defines a function to be executed when the readyState changes.
            try {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        done(null, JSON.parse(xhr.response), xhr.status);
                    }
                    else {
                        done(`${JSON.parse(xhr.response).message} + ${xhr.status}`, xhr.response, xhr.status);
                    }
                }
            }catch (e) {
                done(`${e.description} + ${JSON.parse(xhr.response).message}`, xhr.response, xhr.status);
            }
        }
        xhr.send();
    }

    static passLicenseIDPayment(){
        // Get The hidden products_id input
        let paymentID = document.querySelector('input[name="products_id"]');

        if(paymentID){ // If PaymentID INPUT ELEMENT EXIST AT ALL....DO BELOW
            // This gets the previously stored cart item
            const values = CartToLocalStorage.getCart();

            let output = [];
            for (let item in values) {
                let trackid = values[item].itemID, licenseidindex = values[item].licenseID;
                output.push({
                    'trackid': trackid,
                    'licenseidindex': licenseidindex,
                });
                // output = output + values[item].licenseID + ",";
            }
            paymentID.value = JSON.stringify(output);
        }
    }

}
// Code Template or Class for our user Interface
class CartUI {
    constructor() { // the constructor
        // Notification: This Uses SweetAlert dependencies, so, you might wanna add it to your project
        /*
        *  USAGE: cartUI.Toast.fire({
            icon: 'error',
            title: 'Choose a license from the license selector'
        })
        *
        * icon can be 'success', 'info', 'question', 'warning'
        * */
        this.Toast = Swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 7000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        }) // End Notification

    }; // End Constructor

    addItemToCart(info) {
        // Insert CartItem
        CartInfo.cartAjax('GET', `/api/tracklicense/${info.itemID}/${info.licenseID}`, function (err, data, status) {
            if(status === 404) { // If the ID doesn't exist, then it might have been deleted from the admin dashboard, we remove it and return
                let cartInfo = new CartInfo(undefined, undefined, undefined, info.itemID);
                // Remove from localStorage
                CartToLocalStorage.removeCart(cartInfo);
            } else{
                // if otherwise, catch the Ajax data and perform an operation
                for(let item of data){ // This loops through the ajax data
                    cardItemContainer.insertAdjacentHTML('beforeEnd', `
                            <li class="single-cart-item" data-itemid=${info.itemID}  data-licenseid=${info.licenseID}>
                                    <a href="/${item['track_url_slug']}/${item['track_slug']}" class="cart-flex-item">
                                        ${item['track_title']}
                                    </a>
                                    <div class="cart-license cart-flex-item">
                                        <span class="text-muted-2">
                                        ${item['license_type'].charAt(0).toUpperCase() +
                    item['license_type'].substring(1).toLowerCase()} License</span>
                                    </div>
                                    <div class="cd-price cart-flex-item">$${item['license_price']}</div>
                                    <a href="#0" class="delete-cart">
                                        <svg class="icon tonics-trash-can cart-flex-item">
                                            <use xlink:href="#tonics-trash-can"></use>
                                        </svg>
                                    </a>
                                </li>
        `);
                }
            }
        });
    } // END -- - addItemToCart(info)

    removeItemFromCart(cartInfo) {
        /*
        *   When an option is picked in the License Selector ---
        *   We loop through all the IDs attached to data-arrayofitemsid e.g we can have: data-arrayofitemsid="13,14,15"
        *   If we find one or any of the IDs, we remove all of 'em ;)
        *
         */
        if(cartInfo.ArrayofItemsID){ // If cartInfo contains multiple ID
            for(let i = 0, len = cartInfo.ArrayofItemsID.length; i < len ; i++) {
                if(document.querySelector(`li[data-itemid="${cartInfo.ArrayofItemsID[i]}"]`)){
                    document.querySelector(`li[data-itemid="${cartInfo.ArrayofItemsID[i]}"]`).remove();
                }
            }
            // The Total Price
            cartUI.calculateTotalPriceFromLocalStorage();
        } else { // This is an individual deletion
            if(document.querySelector(`li[data-itemid="${cartInfo.itemID}"]`)){
                document.querySelector(`li[data-itemid="${cartInfo.itemID}"]`).remove();
            }
            cartUI.Toast.fire({
                icon: 'info',
                title: 'Item Removed From Cart'
            });
            // The Total Price
            cartUI.calculateTotalPriceFromLocalStorage();
            // Animation to cart button
            cartUI.shakeCartButton();
        }
    } // END --- removeItemFromCart(cartInfo)

    rebuildCartListFromLocalStorage(info) {
        const addNewCart  =  new CartUI();
        // and This send it to the addItemToCart
        addNewCart.addItemToCart(info)
        // Add the number of cart item to the cart button
        cartCounter.innerHTML = JSON.parse(localStorage.getItem("cart")).length;
        // The Total Price
        cartUI.calculateTotalPriceFromLocalStorage();
        // This add all available license IDS to the hidden payment_id
        CartInfo.passLicenseIDPayment();

    } // END --- rebuildCartListFromLocalStorage(info)

    calculateTotalPriceFromLocalStorage(){
        let cart = CartToLocalStorage.getCart()
        let price = 0;
        cart.forEach(function(item){
            price = price + (parseFloat(item.licensePrice))
        });
        // Format it in USD
        // Create our CURRENCY Formatter, thanks to Intl.NumberFormat.
        // Usage is formatter.format(2500); /* $2,500.00 */
        const formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        });

        // Add The Total To The total-price element
        let totalPrice = document.querySelectorAll(".total-price")
        for(let i = 0, len = totalPrice.length ; i < len ; i++) {
            totalPrice[i].innerHTML = `${formatter.format(price)}`
        }


    } // END --- calculateTotalPriceFromLocalStorage()

    shakeCartButton(){
        cartButtonIcon.classList.add("jello-diagonal-1"); // Add Animation to cart button
        setTimeout(function () { // Remove Animation After 1 sec
            cartButtonIcon.classList.remove("jello-diagonal-1");
        }, 1000);
    }

    /***
     * Method for toggling like and dislike in beatstore or track view
     * @param trackID
     * @param customerID
     * @param isLike
     * @param Element
     */
    trackLikeDislike(trackID, customerID, isLike, Element){
        CartInfo.cartAjax('GET', `/api/track/like/${trackID}/${customerID}/${isLike}`, function (err, data, status){

            if(status === 404) { // If the UniqueTrackID doesn't exist, then it might have been deleted from the admin dashboard, we remove it and return
                cartUI.Toast.fire({
                    icon: 'error',
                    title: 'Something Went Wrong'
                })
            } else {
                if(data['likeinfo'].is_like === 1){
                    Element.querySelector(".tooltiptext-toparrow").innerHTML = "Unlike"; //Initial Text
                    // The main reason I am setting the value to 0 is to prepare the data-islike for what is likely to happen next
                    Element.setAttribute('data-islike', '0');
                    Element.querySelector('.use')
                        .setAttributeNS('http://www.w3.org/1999/xlink','xlink:href','#tonics-heart-fill');
                    cartUI.Toast.fire({
                        icon: 'success',
                        title: `Liked`,
                    })
                } else {
                    Element.setAttribute('data-islike', 1)
                    Element.querySelector('.use')
                        .setAttributeNS('http://www.w3.org/1999/xlink','xlink:href','#tonics-heart');
                    Element.querySelector(".tooltiptext-toparrow").innerHTML = "Like"; //Initial Text
                    cartUI.Toast.fire({
                        icon: 'success',
                        title: `Unliked`,
                    })
                }
            }

        });

    }

    /***
     *
     * @param trackSlugID
     * @param playHash
     */
    storePlayListening(trackSlugID, playHash){

        // if the playhash is empty then we can actually make a request to the endpoint
        // Using session this way help save from hitting the server everytime a song plays
        if (sessionStorage.getItem(playHash) === null) {
            CartInfo.cartAjax('GET', `/api/track/store/play/${trackSlugID}/${playHash}`, function (err, data, status){
                sessionStorage.setItem(playHash, 'play recorded');
                if(status === 404) { // If the UniqueTrackID doesn't exist, then it might have been deleted from the admin dashboard, we remove it and return
                    console.log(data['message']);
                } else {
                    console.log(data['message'])
                }
            });
        }


    }
}
// Class for our localStorage
class CartToLocalStorage {

    // This gets the cart details from the localStorage if there is one, otherwise we add an empty array
    static getCart() {
        let cartDetails;
        if (localStorage.getItem('cart') === null) {
            cartDetails = [];
        } else {
            cartDetails = JSON.parse(localStorage.getItem('cart'));
        }
        return cartDetails;
    }

    // This method adds a cart to the localStorage
    static addCart(info) {
        // Reinstatiate CartInfo
        let Cartdetails = new CartInfo(info['licenseID'], info['licensePrice'], undefined, info['itemID']) // undefined would omit the ArrayofItemsID paramter
        // This gets the previously stored cart item
        const values = CartToLocalStorage.getCart();
        values.push(Cartdetails); // We Push the Newly item pass to the info
        localStorage.setItem('cart', JSON.stringify(values)); // we set the item
        cartUI.Toast.fire({
            icon: 'success',
            title: 'Item Added To Cart'
        });
        cartCounter.innerHTML = JSON.parse(localStorage.getItem("cart")).length;
        // The Total Price
        cartUI.calculateTotalPriceFromLocalStorage();
        // Animation to cart button
        cartUI.shakeCartButton();
        // This add all available license IDS to the hidden payment_id
        CartInfo.passLicenseIDPayment();
    }

    // This method removes a cart from the localStorage
    static removeCart(info){
        // This gets the previously stored cart item
        if(info.ArrayofItemsID){ // This contains multiple IDS
            let values = CartToLocalStorage.getCart();
            for(let i = 0, len = info.ArrayofItemsID.length; i < len ; i++) {
                // This remove all occurence of each value in the info.ArrayofItemsID[i], and return a new copy
                // e.g if the array is [12, 12, 14, 12, 15, 15] and the value in the info.ArrayofItemsID[i] is 12, 15
                // Your return values would be [14], this is like search and delete any occurrence
                values = values.filter(e => e.itemID !== info.ArrayofItemsID[i]);
            }
            localStorage.setItem('cart', JSON.stringify(values)); // we set the item
            // Re-Add the number of cart item to the cart button
            cartCounter.innerHTML = JSON.parse(localStorage.getItem("cart")).length;
            // This add all available license IDS to the hidden payment_id
            CartInfo.passLicenseIDPayment();
        } else { // A single deletion
            let values = CartToLocalStorage.getCart();
            values = values.filter(e => e.itemID !== info.itemID);
            localStorage.setItem('cart', JSON.stringify(values)); // we set the item
            // Re-Add the number of cart item to the cart button
            cartCounter.innerHTML = JSON.parse(localStorage.getItem("cart")).length;
            // The Total Price
            cartUI.calculateTotalPriceFromLocalStorage();
            // This add all available license IDS to the hidden payment_id
            CartInfo.passLicenseIDPayment();
        }
    }

    // This sends each cart item from the localStorage to the rebuildCartListFromLocalStorage
    static displayCart() {
        const cart = CartToLocalStorage.getCart()

        cart.forEach(function(item){
            const addNewCart  =  new CartUI();
            // rebuild music to UI
            addNewCart.rebuildCartListFromLocalStorage(item)
        });
    }
}

/*
-----------------------
  Variable Declaration
----------------------
*/

let licenseSelector = document.querySelectorAll('.license-selector');
let paymentMethodSelector = document.querySelector('.payment-selector');
let cartDataID = document.querySelectorAll('[data-cartid]');
let cartCounter = document.querySelector('.cb-counter-label');
let cardItemContainer = document.querySelector('.cd-cart-items');
let cartButtonIcon = document.querySelector(".cart-button-link");
let cartTrackLikeDislike = document.querySelectorAll('.track-like-dislike');

// Instantiate Class cartUI and CartINFO
const cartUI = new CartUI();

/*
-----------------------
  Start Event Listener
----------------------
*/

// DOM Load Event
document.addEventListener('DOMContentLoaded', CartToLocalStorage.displayCart);
// document.addEventListener('DOMContentLoaded', CartToLocalStorage.removeCartStorageBasedOnStatus);
/*
*  Loop Over All The LicenseSelector and add Event EventHandlers
*  To Listen For Change Event Type In The License Selector
* */
cartFunction();
function cartFunction() {

    if(cartTrackLikeDislike){
        for(let i = 0, len = cartTrackLikeDislike.length ; i < len ; i++) {
            cartTrackLikeDislike[i].addEventListener('click', function (e) {
                let customerID = cartTrackLikeDislike[i].getAttribute('data-customerid');
                if(customerID){
                    let trackID = cartTrackLikeDislike[i].getAttribute('data-trackid');  // get the data-trackid of the selected option value
                    let isLike = cartTrackLikeDislike[i].getAttribute('data-islike');
                    cartUI.trackLikeDislike(trackID, customerID, isLike, cartTrackLikeDislike[i]);
                } else {
                    cartUI.Toast.fire({
                        icon: 'info',
                        title: `You are not signed up`,
                        html: 'You are not <b>logged in</b>, ' +
                            '<a href="/customer/login">Login</a> or <a href="/customer/register">Signup</a>'
                    })
                }

                e.preventDefault();
            });
        }
    }

    if(licenseSelector){ // If there is licenseSelector element
        for(let i = 0, len = licenseSelector.length ; i < len ; i++) {
            licenseSelector[i].addEventListener('change', function (e) {

                // Get LicenseID, LicensePrice and all the LicenseIDs in The LicenseSelectors Box
                let licensePrice = licenseSelector[i].value;

                let licenseID = this.options[this.selectedIndex]
                    .getAttribute("data-licenseID"); // Get the data-licenseID of the selected option value

                let trackID = this.options[this.selectedIndex]
                    .getAttribute("data-trackid"); // get the data-trackid of the selected option value

                function licenseOptions() {
                    let licenseIDArray = []
                    for(let j = 1; j < licenseSelector[i].options.length; j++) {
                        licenseIDArray.push( licenseSelector[i].options[j].
                        getAttribute("data-trackid")); // Get All The License ID of The Particular LicenseSelector, and push them on each iteration
                    }
                    return licenseIDArray;
                }

                // Inject The Cart Details To HTML
                licenseSelector[i].parentElement.nextElementSibling.innerHTML = `$${licensePrice}`
                licenseSelector[i].closest(".product-price").nextElementSibling
                    .querySelector('[data-cartid]')
                    .setAttribute("data-licenseID", `${licenseID}`); // add the licenseid to the current price element
                licenseSelector[i].closest(".product-price").nextElementSibling
                    .querySelector('[data-cartid]')
                    .setAttribute("data-licensePrice", `${licensePrice}`); // add the licensePrice to the current price element
                licenseSelector[i].closest(".product-price").nextElementSibling
                    .querySelector('[data-cartid]')
                    .setAttribute("data-arrayofitemsid", `${licenseOptions()}`); // add the licensePrice to the current price element

                cartUI.Toast.fire({
                    icon: 'info',
                    title: 'Now, Add The Item To Cart',
                })

                // Instantiate Class cartInfo
                let cartInfo = new CartInfo(licenseID, licensePrice, licenseOptions(), trackID);
                licenseSelector[i].closest(".product-price")
                    .nextElementSibling.querySelector('[data-cartid]').querySelector('.use')
                    .setAttributeNS('http://www.w3.org/1999/xlink','xlink:href','#tonics-shopping-cart');

                licenseSelector[i]
                    .closest(".product-price").nextElementSibling
                    .querySelector('[data-cartid]')
                    .querySelector(".tooltiptext-toparrow").innerHTML = "Add To Cart"; //Initial Text

                CartToLocalStorage.removeCart(cartInfo);
                // Remove item from UI as soon as user changes the item in the license selector
                cartUI.removeItemFromCart(cartInfo);
                e.preventDefault() // Prevent the default action
            });
        }
    }

    if(cartDataID){ // If there is cartDataID element
        for(let i = 0, len = cartDataID.length ; i < len ; i++) {
            cartDataID[i].addEventListener('click', function (e) {

                let trackID = cartDataID[i].getAttribute('data-trackid');  // get the data-trackid of the selected option value

                let currentPriceID = cartDataID[i]
                    .getAttribute("data-licenseID"); // get the licenseid of the current price element
                let currentPrice = cartDataID[i]
                    .getAttribute("data-licensePrice"); // get the licenseid of the current price element
                let allCurrentIDs = cartDataID[i]
                    .getAttribute("data-arrayofitemsid").split(","); // get the licenseid to the current price element

                // Instantiate Class cartInfo
                let cartInfo = new CartInfo(currentPriceID, currentPrice, allCurrentIDs, trackID);
                if(!cartInfo.licenseID){ // If the user did not pick anything from the licenseSelector,  we fire below, I am checking the licenseID
                    cartUI.Toast.fire({
                        icon: 'error',
                        title: 'Choose a license from the license selector'
                    })
                    e.preventDefault();
                    return;
                }
                // Toggle for shopping cart.  Either Cart Icon or Remove Icon
                cartDataID[i].querySelector(".use").
                getAttribute("xlink:href") === '#tonics-shopping-cart' ?
                    cartDataID[i].querySelector(".use").setAttributeNS('http://www.w3.org/1999/xlink','xlink:href','#tonics-remove'):
                    cartDataID[i].querySelector(".use").setAttributeNS('http://www.w3.org/1999/xlink','xlink:href','#tonics-shopping-cart');

                cartDataID[i].querySelector(".tooltiptext-toparrow").innerHTML = "Add To Cart"; //Initial Text
                if(cartDataID[i].querySelector(".use").getAttribute("xlink:href") === '#tonics-remove'){
                    cartDataID[i].querySelector(".tooltiptext-toparrow").innerHTML = "Remove From Cart"; // If the remove icon is present, add the text
                    // Add to UI
                    cartUI.addItemToCart(cartInfo);
                    // Add to localStorage
                    CartToLocalStorage.addCart(cartInfo);
                    e.preventDefault()
                    return;
                } else if(cartDataID[i].querySelector(".use").getAttribute("xlink:href") === '#tonics-shopping-cart'){
                    cartUI.Toast.fire({
                        icon: 'info',
                        title: 'Item Removed From Cart'
                    });
                    // Remove from localStorage
                    console.log(cartInfo);
                    CartToLocalStorage.removeCart(cartInfo);
                    // Remove from UI
                    cartUI.removeItemFromCart(cartInfo);
                    // Animation to cart button when item is removed
                    cartUI.shakeCartButton();
                    e.preventDefault();
                    return;
                }

                e.preventDefault();
            });
        }
    }

    // Event Listener for delete
    if(cardItemContainer){ // If the CardItemContainer is available
        cardItemContainer.addEventListener('click', function(e){ // using event delegation for this

            let itemID, licenseID;
            if(e.target.classList.contains('delete-cart')){
                itemID = e.target.parentElement.getAttribute('data-itemid');
                licenseID = e.target.parentElement.getAttribute('data-licenseid');
                // Instantiate UI
                let cartInfo = new CartInfo(licenseID, '', '', itemID);
                // Remove From UI
                cartUI.removeItemFromCart(cartInfo);
                // Remove from localStorage
                CartToLocalStorage.removeCart(cartInfo);
            }

        });
    }

    let viewCart =  document.querySelector(".view-cart");
    if(viewCart){
        document.querySelector(".view-cart").addEventListener('click', function(e) {
            document.querySelector(".cart-button-link").click();
            e.preventDefault();
        });
    }

    // Event Listener For PaymentMethodSelector
    if (paymentMethodSelector){
        paymentMethodSelector.addEventListener('change', function (e) {
            let paymentName = paymentMethodSelector.value;

            let SelectedPaymentButton = document.querySelector(`[data-buttonName=${paymentName}]`);
            let allPaymentButton = document.querySelectorAll(`[data-buttonName]`);

            for (let i=0; i < allPaymentButton.length; i++)  {
                if (allPaymentButton[i].getAttribute('data-buttonName') === SelectedPaymentButton.getAttribute('data-buttonName')){
                    if (!allPaymentButton[i].classList.contains("display-block")){
                        allPaymentButton[i].classList.add("display-block");
                        allPaymentButton[i].classList.remove("display-none");
                    }
                    continue;
                }
                allPaymentButton[i].classList.remove('display-block');
                allPaymentButton[i].classList.add('display-none');
                // console.log(allPaymentButton[i].getAttribute('data-buttonName'));
            }

           // console.log(allPaymentButton)

            document.querySelector('.paymentMethodName').innerHTML = paymentName;
            // This where we store the paymentMethod name, and would get picked up by the server-side when the form-acton is fired
            let payment_method_hidden = document.querySelector('input[name="payment_method"]');
            if(payment_method_hidden) {
                payment_method_hidden.value = paymentName;
            }
        });
    }

}
/*
-----------------------
  End Event Listener
----------------------
*/

/*------------------------------
  CLOSE OR EXIST FUNCTIONALITY
------------------------------*/

let cdCart = document.querySelector(".cd-cart");
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeContainer('.cd-cart',
            ['display-block', 'swing-in-top-fwd'],
            ['swing-out-top-fwd'], ['display-none'])
    }
});

/*------------------------------------------
  SIDEBAR STICKY (MOVE THIS FUNCTION LATER)
-------------------------------------------*/
// if site-header exist
let siteHeader = document.querySelector('.site-header')
if (siteHeader){
    // This returns the height of an site-header, including vertical padding and borders, as an integer.
    let siteHeaderOffset = siteHeader.offsetHeight + 10;
    // we then add it to the sidebar top rule, this way when user scrolls...the sidebar can be beneath the site-header
    let sidebar = document.querySelector('.aside-sidebar');
    if (sidebar){ // if sidebar exist
        sidebar.style.cssText = `top: ${siteHeaderOffset}px;`;
    }
}

/*---------------------------------------------------------------
  TRACK ORIGINAL IMAGE CONTAINER COLOR (MOVE THIS FUNCTION LATER)
----------------------------------------------------------------*/

// This is a simple way to get the dominate color in an image, which we then set in the track-single-container of every track page
let trackOriginalImage = document.querySelector('.track-original-image');
if (trackOriginalImage){
    let trackSinglularContainer = document.querySelector('.track-single-container');
    // Set the background color
    trackSinglularContainer.style.backgroundImage = `linear-gradient( rgb(0 0 0 / 20%), rgba(0, 0, 0, 0.5) ), url("${trackOriginalImage.src}")`;
    trackSinglularContainer.style.backgroundSize = '1px 1px';
    trackSinglularContainer.style.filter = 'saturate(2)';
}
