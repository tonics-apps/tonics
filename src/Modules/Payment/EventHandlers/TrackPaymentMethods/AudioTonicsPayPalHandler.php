<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\EventHandlers\TrackPaymentMethods;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\Tables;
use App\Modules\Payment\Controllers\PaymentSettingsController;
use App\Modules\Payment\Events\OnAddTrackPaymentEvent;
use App\Modules\Payment\Events\AudioTonicsPaymentInterface;
use App\Modules\Payment\Events\OnPurchaseCreate;
use App\Modules\Track\Data\TrackData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class AudioTonicsPayPalHandler implements HandlerInterface, AudioTonicsPaymentInterface
{
    const Query_ClientCredentials = 'ClientPaymentCredentials';
    const Query_GenerateInvoiceID = 'GenerateInvoiceID';
    const Query_CapturedPaymentDetails = 'CapturedPaymentDetails';

    public function handleEvent(object $event): void
    {
        /** @var $event OnAddTrackPaymentEvent */
        $event->addPaymentHandler($this);
    }

    public function name(): string
    {
        return 'AudioTonicsPayPalHandler';
    }

    /**
     * @throws \Exception
     */
    public function handlePayment(): void
    {

        $queryType = url()->getHeaderByKey('PaymentQueryType');
        if ($queryType === self::Query_GenerateInvoiceID){
            $this->generateInvoiceID();
            return;
        }

        if ($queryType === self::Query_CapturedPaymentDetails){
            $body = url()->getEntityBody();
            $body = json_decode($body);

            $userData = new UserData();
            $checkoutEmail = $body->checkout_email ?? '';
            $customerData = $userData->doesCustomerExist($checkoutEmail);

            // if customer does not exist, we create a guest user
            if (!$customerData) {
                $guestCustomersData = [
                    'user_name' => helper()->extractNameFromEmail($checkoutEmail) ?? $checkoutEmail,
                    'email' => $checkoutEmail,
                    // add a random password
                    'user_password' => helper()->securePass(helper()->randomString()),
                    'settings'=> UserData::generateCustomerJSONSettings(),
                    'is_guest' => 1,
                    'role' => Roles::getRoleIDFromDB(Roles::ROLE_GUEST)
                ];

                $customerData = $userData->insertForCustomer($guestCustomersData, ['user_id', 'user_name', 'email', 'is_guest']);
            }

            if (isset($body->cartItems) && is_array($body->cartItems)){
                $cartItemsSlugID = [];
                foreach ($body->cartItems as $cartItem){
                    $cartItemsSlugID[] = (isset($cartItem[0])) ? $cartItem[0] : '';
                }

                try {
                    $trackData = TrackData::class;
                    $purchaseTracks = db()->Select('track_id, slug_id, track_slug, track_title, license_attr_id_link, license_attr')
                        ->From($trackData::getTrackTable())
                        ->Join($trackData::getLicenseTable(), "{$trackData::getLicenseTable()}.license_id", "{$trackData::getTrackTable()}.fk_license_id")
                        ->WhereIn("{$trackData::getTrackTable()}.slug_id", $cartItemsSlugID)
                        ->GroupBy("{$trackData::getTrackTable()}.slug_id")
                        ->FetchResult();

                    $purchaseInfo = $this->getPurchaseTracksInfo($body->cartItems, $purchaseTracks);

                    if ($customerData->email){
                        $purchaseData = [
                            'fk_customer_id' => $customerData->user_id,
                            'total_price' => $purchaseInfo['totalPrice'] ?? 0,
                            'others' => json_encode([
                                'downloadables' => $purchaseInfo['downloadables'] ?? [], // would be used to confirm the item the user is actually buying
                                'itemIds' => $cartItemsSlugID, // would be used to confirm the item the user is actually buying
                                'invoice_id' => $body->invoice_id,
                                'tx_ref' => null, // this is for flutterwave
                                'order_id' => $body->orderData->id, // this is for PayPal
                                'payment_method' => 'TonicsPayPal', // i.e PayPal, FlutterWave
                                'tonics_solution' => PaymentSettingsController::TonicsSolution_AudioTonics
                            ]),
                        ];

                        $purchaseDataReturn = db()->insertReturning(Tables::getTable(Tables::PURCHASES), $purchaseData, Tables::$TABLES[Tables::PURCHASES], 'purchase_id');
                        $onPurchaseCreate = new OnPurchaseCreate($purchaseDataReturn);
                        event()->dispatch($onPurchaseCreate);

                        $customer_purchase_history = route('customer.purchase.history', ['slug_id' => $onPurchaseCreate->getSlugID()]);
                        $message = <<<MESSAGE
<h6>Pending Review, Check $checkoutEmail mailbox or spam folder in few minutes for files, please contact us if you got stucked.</h6>
<br>
Alternatively, If you have an account, check <a href="$customer_purchase_history" target="_blank">Purchase Files</a> for your file(s)
<br>


MESSAGE;
                        response()->onSuccess(['email' => $checkoutEmail], $message);
                    }
                } catch (\Exception $exception){
                    // Log..
                }
            }
        }

        if ($queryType === self::Query_ClientCredentials){
            $settings = PaymentSettingsController::getSettingsData();
            $credentials = ''; $live = false;
            if (key_exists(PaymentSettingsController::Key_IsLive, $settings) && $settings[PaymentSettingsController::Key_IsLive] === '1'){
                $live = true;
            }

            if ($live){
                if (isset($settings[PaymentSettingsController::Key_LiveClientID])){
                    $credentials = $settings[PaymentSettingsController::Key_LiveClientID];
                }
            } else {
                if (isset($settings[PaymentSettingsController::Key_SandBoxClientID])){
                    $credentials = $settings[PaymentSettingsController::Key_SandBoxClientID];
                }
            }

            response()->onSuccess($credentials);
        }

    }


    /**
     * @param $cartItems
     * @param $purchaseTracks
     * @return array
     *
     * You get the following:
     * [
    'totalPrice' => $totalPrice,
    'downloadables' => $downloadables
    ]
     *
     */
    public function getPurchaseTracksInfo($cartItems, $purchaseTracks): array
    {
       $licenseUniqueID = [];
        foreach ($cartItems as $cartItem){
            $licenseUniqueID[(isset($cartItem[0])) ? $cartItem[0] : ''] = (isset($cartItem[1]->unique_id)) ? $cartItem[1]->unique_id : '';
        }

        $totalPrice = 0;
        $downloadables = [];
        // Loop PurchaseTracks
        foreach ($purchaseTracks as $purchaseTrack){
            $licenseAttributes = json_decode($purchaseTrack->license_attr);
            $licenseAttributesDownloadLink = json_decode($purchaseTrack->license_attr_id_link);
            // Check if slug_is existed in $licenseUniqueID, $licenseUniqueID is formatted as
            // [slug_id] => license unique ID of the item user paid for
            if (isset($licenseUniqueID[$purchaseTrack->slug_id])){
                $uniqueID = $licenseUniqueID[$purchaseTrack->slug_id];

                // Loop licenses attached to the track
                foreach ($licenseAttributes as $attribute) {
                    // check if the $uniqueID matches with the one in the attribute
                    // if so, add to the totalPrice and break
                    if ($attribute->unique_id === $uniqueID){
                        $totalPrice += $attribute->price;
                        $downloadables[$purchaseTrack->slug_id] = [
                          'track_title' =>   $purchaseTrack->track_title,
                          'license' =>   $attribute->name,
                        ];

                        if (isset($licenseAttributesDownloadLink->{$uniqueID})){
                            $downloadables[$purchaseTrack->slug_id]['download_link'] = $licenseAttributesDownloadLink->{$uniqueID};
                        }

                        break;
                    }
                }

            }
        }

        return [
            'totalPrice' => $totalPrice,
            'downloadables' => $downloadables
            ];
    }

    /**
     * @throws \Exception
     */
    public function generateInvoiceID()
    {
        response()->onSuccess(uniqid('AudioTonics_', true));
    }
}