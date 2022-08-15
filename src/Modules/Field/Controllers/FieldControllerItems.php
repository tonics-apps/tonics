<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Controllers\Controller;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnEditorFieldSelection;
use App\Modules\Field\Events\OnFieldItemsSave;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Rules\FieldValidationRules;

class FieldControllerItems extends Controller
{
    use FieldValidationRules, Validator;
    
    private FieldData $fieldData;

    public function __construct(FieldData $fieldData)
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @throws \Exception
     */
    public function index(string $slug)
    {

        $fieldID = $this->getFieldData()->getFieldID($slug);
        if ($fieldID === null){
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $onFieldMetaBox = new OnFieldMetaBox();
        $dispatched = event()->dispatch($onFieldMetaBox);

        if (url()->getParam('action')){
            $action = url()->getParam('action');
            $slug = url()->getParam('slug');
            if ($action === 'getForm' && $slug){
                helper()->onSuccess($onFieldMetaBox->getSettingsForm($slug));
            }
        }

        view('Modules::Field/Views/Items/index', [
            'MetaBox' => $dispatched->generateFieldMetaBox(),
            'FieldItems' => $this->getFieldData()->getFieldItemsListing($this->getFieldData()->getFieldItems($fieldID)),
            'FieldBuilderName' => ucwords(str_replace('-', ' ', $slug)),
            'FieldSlug' => $slug,
            'FieldID' => $fieldID,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function store()
    {
        $fieldSlug = input()->fromPost()->retrieve('fieldSlug', '');

        # Stage One: Extract The fieldDetails...
        try {
            $fieldDetails = json_decode(input()->fromPost()->retrieve('fieldDetails'), true);
            $validator = $this->getValidator()->make($fieldDetails, $this->fieldItemsStoreRule());
        }catch (\Exception){
            session()->flash(['An Error Occurred Extracting Field Data'], []);
            redirect(route('fields.items.index', ['field' => $fieldSlug]));
        }

        # Stage Two: Working On The Extracted Data and Dumping In DB...
        $error = false;
        if ($validator->passes()){
            try {
                db()->beginTransaction();
                # Delete All the Field Items Related to $fieldDetails->fieldID
                $this->getFieldData()->deleteWithCondition(
                    whereCondition: "fk_field_id = ?", parameter: [$fieldDetails['fieldID']], table: $this->getFieldData()->getFieldItemsTable());
                # Reinsert it
                db()->insertBatch($this->getFieldData()->getFieldItemsTable(), $fieldDetails['fieldItems']);
                db()->commit();
                event()->dispatch(new OnFieldItemsSave($fieldDetails));
                $error = true;
            }catch (\Exception $exception){
                // log..
            }
        }

        if ($error === false) {
            $fieldDetails = json_decode(input()->fromPost()->retrieve('fieldDetails'), true);
            session()->flash($validator->getErrors(), $fieldDetails ?? []);
            redirect(route('fields.items.index', ['field' => $fieldSlug]));
        }
        session()->flash(['Field Items Successfully Saved'], $fieldDetails ?? [], type: Session::SessionCategories_FlashMessageSuccess);
        helper()->clearAPCUCache();
        redirect(route('fields.items.index', ['field' => $fieldSlug]));
    }

    /**
     * @throws \Exception
     */
    public function fieldSelectionManager()
    {
        $this->getFieldData()->getFieldItemsAPIForEditor();

        $onEditorFieldSelection = new OnEditorFieldSelection();
        $dispatched = event()->dispatch($onEditorFieldSelection);

        view('Modules::Field/Views/Items/selection-manager', [
            'FieldItems' => $dispatched->getFields()
        ]);
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }
    
}