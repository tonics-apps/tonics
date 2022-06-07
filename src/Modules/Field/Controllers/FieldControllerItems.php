<?php

namespace App\Modules\Field\Controllers;

use App\Configs\AppConfig;
use App\Library\Authentication\Session;
use App\Library\SimpleState;
use App\Modules\Core\Controllers\Controller;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Rules\FieldValidationRules;
use App\Modules\Widget\Data\WidgetData;
use App\Modules\Widget\Events\OnMenuWidgetMetaBox;
use App\Modules\Widget\Rules\WidgetValidationRules;
use PDO;

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
            'SiteURL' => AppConfig::getAppUrl(),
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
            session()->flash(['An Error Occurred Extracting Field Data'], [], type: Session::SessionCategories_FlashMessageError);
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
                $error = true;
            }catch (\Exception $exception){
            }
        }

        if ($error === false) {
            $fieldDetails = json_decode(input()->fromPost()->retrieve('fieldDetails'), true);
            session()->flash($validator->getErrors(), $fieldDetails ?? [], type: Session::SessionCategories_FlashMessageError);
            redirect(route('fields.items.index', ['field' => $fieldSlug]));
        }
        session()->flash(['Field Items Successfully Saved'], $fieldDetails ?? [], type: Session::SessionCategories_FlashMessageSuccess);
        helper()->clearAPCUCache();
        redirect(route('fields.items.index', ['field' => $fieldSlug]));
    }



    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }
    
}