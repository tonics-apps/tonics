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
        $fieldSlugIDS = [];
        if (helper()->isJSON($fieldDetails)){
            $fieldDetails = json_decode($fieldDetails);
            $tree = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $fieldDetails, onData: function ($field) use ($fieldSlugIDS) {
                if (isset($field->field_options) && helper()->isJSON($field->field_options)){
                    $fieldOption = json_decode($field->field_options);
                    $field->field_data = (array)$fieldOption;
                }
                if (!key_exists($field->main_field_slug, $fieldSlugIDS)){
                    $fieldSlugIDS[$field->main_field_slug] = $field->main_field_slug;
                }
                return $field;
            });

            $_POST['_fieldDetails'] = json_encode($tree);
            dd($_POST, $request, $tree);
        }
    }
}