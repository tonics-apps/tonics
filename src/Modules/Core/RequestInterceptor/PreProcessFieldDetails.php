<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Core\RequestInterceptor;

use App\Modules\Core\Configs\FieldConfig;
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
            $fieldCategories = $fieldData->compareSortAndUpdateFieldItems($fieldItems);
            # re-dispatch so we can get the form values
            $onFieldMetaBox = new OnFieldMetaBox();
            $onFieldMetaBox->setSettingsType(OnFieldMetaBox::OnUserSettingsType)->dispatchEvent();

            foreach ($fieldCategories as $userFieldItems){
                foreach ($userFieldItems as $userFieldItem) {
                    $onFieldMetaBox->getUsersForm($userFieldItem->field_options->field_slug, $userFieldItem->field_options);
                    # Break it immediately, we only need one to be sure...
                    if ($onFieldMetaBox->isErrorEmitted()){
                        $_POST['_fieldErrorEmitted'] = true;
                        break;
                    }
                }
            }

        }
    }
}