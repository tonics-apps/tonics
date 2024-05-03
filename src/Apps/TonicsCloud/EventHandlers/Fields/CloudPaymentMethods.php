<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\EventHandlers\Fields;


use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Payment\Events\TonicsCloud\OnAddTonicsCloudPaymentEvent;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class CloudPaymentMethods implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('CloudPaymentMethods', 'Cloud Payment Methods', 'TonicsCloud',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
            handleViewProcessing: function () {
            }
        );
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Payment Method(s)';
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
</div>
FORM;
        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Payment Method(s)';
        $frag = $event->_topHTMLWrapper($fieldName, $data);

        /** @var OnAddTonicsCloudPaymentEvent $event */
        $onAddTonicsCloudPayment = event()->dispatch(new OnAddTonicsCloudPaymentEvent());
        $paymentMethods = $onAddTonicsCloudPayment->getPaymentsHooker();
        $frag .= <<<HTML
<div class="form-group row d:flex flex-d:row checkout-payment-gateways-buttons flex-gap:small flex-wrap:wrap">
    $paymentMethods
    <span class="svg-per-file-loading loading-button-payment-gateway color:black bg:pure-black"></span>
</div>
HTML;
        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

}