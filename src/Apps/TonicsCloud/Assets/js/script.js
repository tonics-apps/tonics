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

//----------------------
//--- PAYMENT HANDLERS
//---------------------

function getTonicsCloudCreditAmount() {
    return parseFloat(document.querySelector(`input[name="payment_amount"]`)?.value);
}

function getTonicsCloudCustomerEmail() {
    return document.querySelector(`input[name="customer_email"]`)?.value;
}

function paymentTonicsCloudValidation() {
    if (getTonicsCloudCreditAmount() < 1) {
        window.TonicsScript.infoToast('Payment Amount Must Be At Least $1.00 or Higher', 10000);
        throw new DOMException("Payment Amount Must Be At Least $1.00");
    }
}

class TonicsCloudTonicsPayStackGateway extends DefaultTonicsPayStackWaveGateway {

    constructor(event) {
        super(event);
    }

    updateSettings() {
        this.get_request_flow_address = '/customer/tonics_cloud/payment/get_request_flow';
        this.post_request_flow_address = '/customer/tonics_cloud/payment/post_request_flow';
    }

    getPaymentName() {
        return "TonicsCloudPayStackHandler";
    }

    initPayStackButton(event) {
        let self = this;
        const ButtonHandler = document.getElementById(this.getPaymentName());
        if (ButtonHandler) {
            ButtonHandler.addEventListener('click', (e) => {
                let el = e.target;
                return new Promise((resolve, reject) => {
                    const currency = 'USD';
                    const totalPrice = getTonicsCloudCreditAmount();
                    const checkOutEmail = getTonicsCloudCustomerEmail();
                    paymentTonicsCloudValidation();
                    event.generateInvoiceID(self.getPaymentName(), self.get_request_flow_address, (data) => {
                        self.invoice_id = data?.data;
                        if (self.invoice_id) {
                            const paystack = new PaystackPop();
                            paystack.newTransaction({
                                key: self.client_id,
                                email: checkOutEmail,
                                // PayStack stupid way of storing amount, very stupid
                                currency: 'USD',
                                amount: totalPrice * 100,
                                ref: self.invoice_id,
                                onSuccess: (transaction) => {
                                    // Payment complete! Reference: transaction.reference
                                    // Send AJAX verification request to backend
                                    if (transaction.status === 'success') {
                                        const body = {
                                            invoice_id: self.invoice_id,
                                            amount: totalPrice,
                                            checkout_email: checkOutEmail,
                                            orderData: transaction
                                        };

                                        event.sendBody(self.getPaymentName(), self.post_request_flow_address,
                                            body,
                                            (data) => {
                                                // Show a success message within this page, e.g.
                                                const element = document.querySelector('.checkout-payment-gateways-buttons');
                                                element.innerHTML = '';
                                                element.innerHTML = data?.message;
                                            },
                                            (error) => {
                                                console.log(error);
                                            });
                                    }
                                },
                                onCancel: () => {
                                    console.log("Closed")
                                }
                            });
                        } else {
                            reject('Invalid Invoice ID');
                        }

                    }, () => {
                        reject('Something Went Wrong Processing Payment');
                    });
                });
            });
        }
    }
}

class TonicsCloudTonicsFlutterWaveGateway extends DefaultTonicsFlutterWaveGateway {

    constructor(event) {
        super(event);
    }

    updateSettings() {
        this.get_request_flow_address = '/customer/tonics_cloud/payment/get_request_flow';
        this.post_request_flow_address = '/customer/tonics_cloud/payment/post_request_flow';
    }

    getPaymentName() {
        return "TonicsCloudFlutterWaveHandler";
    }

    initFlutterWaveButton(event) {
        let self = this;
        const ButtonHandler = document.getElementById(this.getPaymentName());
        if (ButtonHandler) {
            ButtonHandler.addEventListener('click', (e) => {
                let el = e.target;
                return new Promise((resolve, reject) => {
                    const currency = 'USD';
                    const totalPrice = getTonicsCloudCreditAmount();
                    const checkOutEmail = getTonicsCloudCustomerEmail();
                    paymentTonicsCloudValidation();
                    event.generateInvoiceID(self.getPaymentName(), self.get_request_flow_address, (data) => {
                        self.invoice_id = data?.data;
                        if (self.invoice_id) {
                            FlutterwaveCheckout({
                                public_key: self.client_id,
                                tx_ref: self.invoice_id,
                                amount: totalPrice,
                                currency: currency,
                                payment_options: "card, account, banktransfer",
                                callback: function (orderData) {
                                    // Send AJAX verification request to backend
                                    if (orderData.status === 'successful') {
                                        const body = {
                                            invoice_id: self.invoice_id,
                                            checkout_email: checkOutEmail,
                                            orderData: orderData
                                        };

                                        event.sendBody(self.getPaymentName(), self.post_request_flow_address,
                                            body,
                                            (data) => {
                                                // Show a success message within this page, e.g.
                                                const element = document.querySelector('.checkout-payment-gateways-buttons');
                                                element.innerHTML = '';
                                                element.innerHTML = data?.message;
                                            },
                                            (error) => {

                                            });
                                    }
                                },
                                onclose: function (incomplete) {
                                    console.log("Closed", incomplete)
                                },
                                customer: {
                                    email: checkOutEmail,
                                },
                            });
                        } else {
                            reject('Invalid Invoice ID');
                        }

                    }, () => {
                        reject('Something Went Wrong Processing Payment');
                    });
                });
            });
        }
    }
}

class TonicsCloudTonicsPayPalGateway extends DefaultTonicsPayPalGateway {

    constructor(event) {
        super(event);
    }

    updateSettings() {
        this.get_request_flow_address = '/customer/tonics_cloud/payment/get_request_flow';
        this.post_request_flow_address = '/customer/tonics_cloud/payment/post_request_flow';
    }

    getPaymentName() {
        return "TonicsCloudPayPalHandler";
    }

    initPayPalButton(event) {
        let self = this;
        paypal.Buttons({
            style: {
                shape: 'pill',
                color: 'white',
                layout: 'vertical',
                label: 'pay',
            },
            createOrder: (data, actions) => {
                //Make an AJAX request to the server to generate the invoice_id
                return new Promise((resolve, reject) => {
                    const currency = 'USD';
                    const totalPrice = getTonicsCloudCreditAmount();
                    paymentTonicsCloudValidation();
                    event.generateInvoiceID(self.getPaymentName(), self.get_request_flow_address, (data) => {
                        self.invoice_id = data?.data;
                        if (self.invoice_id) {
                            resolve(actions.order.create({
                                "purchase_units": [{
                                    "amount": {
                                        "currency_code": currency,
                                        "value": totalPrice,
                                        "breakdown": {
                                            "item_total": {
                                                "currency_code": currency,
                                                "value": totalPrice
                                            }
                                        }
                                    },
                                    "invoice_id": self.invoice_id
                                }]
                            }));
                        } else {
                            reject('Invalid Invoice ID');
                        }

                    }, () => {
                        reject('Something Went Wrong Processing Payment');
                    });

                }).catch(function (error) {
                    console.log("Error creating order: ", error);
                });
            },

            onApprove: (data, actions) => {
                return actions.order.capture().then((orderData) => {

                    if (orderData.status === 'COMPLETED') {
                        const checkOutEmail = getTonicsCloudCustomerEmail();
                        const body = {
                            invoice_id: self.invoice_id,
                            totalPrice: getTonicsCloudCreditAmount(),
                            checkout_email: checkOutEmail,
                            orderData: orderData
                        };

                        event.sendBody(self.getPaymentName(), self.post_request_flow_address,
                            body,
                            (data) => {
                                // Show a success message within this page, e.g.
                                const element = document.querySelector('.checkout-payment-gateways-buttons');
                                element.innerHTML = '';
                                element.innerHTML = data?.message;
                            },
                            (error) => {

                            });

                        // Full available details
                        // console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));

                    } else {

                    }
                });
            },

            onError: function (err) {
                // console.log('An Error Occurred Processing Payment')
                // console.log(err);
            }
        }).render('#paypal-button-container');
    }
}

//---------------------------
//--- HANDLER AND EVENT SETUP
//---------------------------
if (window?.TonicsEvent?.EventConfig) {
    window.TonicsEvent.EventConfig.OnPaymentGatewayCollatorEvent.push(
        ...[
            TonicsCloudTonicsFlutterWaveGateway, TonicsCloudTonicsPayStackGateway, TonicsCloudTonicsPayPalGateway
        ]
    );

    // Fire Payment Gateways
    let OnGatewayCollator = new OnPaymentGatewayCollatorEvent();
    window.TonicsEvent.EventDispatcher.dispatchEventToHandlers(window.TonicsEvent.EventConfig, OnGatewayCollator, OnPaymentGatewayCollatorEvent);
}
