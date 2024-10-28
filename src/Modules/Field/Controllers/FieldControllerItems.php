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

namespace App\Modules\Field\Controllers;

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

    public function __construct (FieldData $fieldData)
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function index (string $slug)
    {

        $fieldID = $this->getFieldData()->getFieldID($slug);
        if ($fieldID === null) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $onFieldMetaBox = new OnFieldMetaBox();
        $onFieldMetaBox->setSettingsType(OnFieldMetaBox::OnBackEndSettingsType)->dispatchEvent();

        if (url()->getParam('action')) {
            $action = url()->getParam('action');
            $slug = url()->getParam('slug');
            if ($action === 'getForm' && $slug) {
                helper()->onSuccess($onFieldMetaBox->getSettingsForm($slug));
            }
        }

        view('Modules::Field/Views/Items/index', [
            'MetaBox'          => $onFieldMetaBox->generateFieldMetaBox(),
            'FieldItems'       => $this->getFieldData()->getFieldItemsListing($this->getFieldData()->getFieldItems($fieldID)),
            'FieldBuilderName' => ucwords(str_replace('-', ' ', $slug)),
            'FieldSlug'        => $slug,
            'FieldID'          => $fieldID,
        ]);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function store ()
    {
        $fieldSlug = input()->fromPost()->retrieve('fieldSlug', '');

        # Stage One: Extract The fieldDetails...
        try {
            $fieldDetails = json_decode(input()->fromPost()->retrieve('fieldDetails'), true);
            $validator = $this->getValidator()->make($fieldDetails, $this->fieldItemsStoreRule());
        } catch (\Exception) {
            session()->flash(['An Error Occurred Extracting Field Data'], []);
            redirect(route('fields.items.index', ['field' => $fieldSlug]));
        }

        $errorMessages = [];
        # Stage Two: Working On The Extracted Data and Dumping In DB...
        $error = false;
        if ($validator->passes()) {
            $dbTx = db();
            try {
                $dbTx->beginTransaction();

                # Delete All the Field Items Related to $fieldDetails->fieldID
                $dbTx->Q()->FastDelete($this->getFieldData()->getFieldItemsTable(), db()->WhereIn('fk_field_id', $fieldDetails['fieldID']));
                # Reinsert it
                $dbTx->Q()->Insert($this->getFieldData()->getFieldItemsTable(), $fieldDetails['fieldItems']);
                $dbTx->commit();
                $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
                event()->dispatch(new OnFieldItemsSave($fieldDetails));
                $error = true;
            } catch (\Exception $exception) {
                $errorMessages[] = $exception->getMessage();
                $dbTx->rollBack();
                $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
                // log..
            }
        } else {
            $errorMessages = $validator->getErrors();
        }

        if ($error === false) {
            $fieldDetails = json_decode(input()->fromPost()->retrieve('fieldDetails'), true);
            session()->flash($errorMessages, $fieldDetails ?? []);
            redirect(route('fields.items.index', ['field' => $fieldSlug]));
        }
        session()->flash(['Field Items Successfully Saved'], $fieldDetails ?? [], type: Session::SessionCategories_FlashMessageSuccess);
        helper()->clearAPCUCache();
        redirect(route('fields.items.index', ['field' => $fieldSlug]));
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function fieldSelectionManager (): void
    {
        $this->getFieldData()->getFieldItemsAPIForEditor();

        $onEditorFieldSelection = new OnEditorFieldSelection();
        $dispatched = event()->dispatch($onEditorFieldSelection);

        view('Modules::Field/Views/Items/selection-manager', [
            'FieldItems' => $dispatched->getFields(),
        ]);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function fieldPreview (): void
    {
        $null = null;
        $this->getFieldData()->unwrapFieldContent($null, FieldData::UNWRAP_FIELD_CONTENT_PREVIEW_MODE);
    }

    /**
     * @return FieldData
     */
    public function getFieldData (): FieldData
    {
        return $this->fieldData;
    }

}