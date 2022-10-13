<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\RequestInterceptor;

use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInterceptorInterface;

class PreProcessFieldDetails implements TonicsRouterRequestInterceptorInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handle(OnRequestProcess $request): void
    {
        $fieldDetails = input()->fromPost()->retrieve('_fieldDetails');
        if (helper()->isJSON($fieldDetails)){
            $fieldData = new FieldData();
            $fieldItems = json_decode($fieldDetails);
            $fieldCategories = $fieldData->compareSortAndUpdateFieldItems(input()->fromPost()->retrieve('field_ids', []), $fieldItems);
            # re-dispatch so we can get the form values
            $onFieldMetaBox = new OnFieldMetaBox();
            $onFieldMetaBox->setSettingsType(OnFieldMetaBox::OnUserSettingsType)->dispatchEvent();

            foreach ($fieldCategories as $userFieldItems){
                foreach ($userFieldItems as $userFieldItem) {
                    $onFieldMetaBox->getUsersForm($userFieldItem->field_options->field_slug, $userFieldItem->field_options);
                }
            }

            if ($onFieldMetaBox->isErrorEmitted()){
                $_POST['_fieldErrorEmitted'] = true;
            }

        }
    }
}