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
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldCreate;
use App\Modules\Field\Rules\FieldValidationRules;
use JetBrains\PhpStorm\NoReturn;

class FieldController
{
    use UniqueSlug, Validator, FieldValidationRules;

    private FieldData $fieldData;

    public function __construct(FieldData $fieldData)
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @throws \Exception
     */
    public function index()
    {
        $cols = '`field_id`, `field_name`, `field_slug`, `created_at`';
        $data = $this->getFieldData()->generatePaginationData(
            $cols,
            'field_name',
            $this->getFieldData()->getFieldTable());

        $widgetListing = '';
        if ($data !== null){
            $widgetListing = $this->getFieldData()->adminFieldListing($data->data);
            unset($data->data);
        }

        view('Modules::Field/Views/index', [
            'SiteURL' => AppConfig::getAppUrl(),
            'Data' => $data,
            'FieldListing' => $widgetListing
        ]);
    }

    /**
     * @throws \Exception
     */
    public function create()
    {
        view('Modules::Field/Views/create', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone()
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function store()
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->fieldStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('fields.create'));
        }

        try {
            $widget = $this->getFieldData()->createField();
            $widgetReturning = db()->insertReturning($this->getFieldData()->getFieldTable(), $widget, $this->getFieldData()->getFieldColumns());

            $onFieldCreate = new OnFieldCreate($widgetReturning, $this->getFieldData());
            event()->dispatch($onFieldCreate);

            session()->flash(['Field Created'], type: Session::SessionCategories_FlashMessageSuccess);
            
            redirect(route('fields.edit', ['field' => $onFieldCreate->getFieldSlug()]));
        } catch (\Exception){
            session()->flash(['An Error Occurred Creating The Field Item'], input()->fromPost()->all());
            redirect(route('widgets.create'));
        }
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function edit(string $slug)
    {
        $menu = $this->getFieldData()->selectWithCondition($this->getFieldData()->getFieldTable(), ['*'], "field_slug = ?", [$slug]);
        if (!is_object($menu)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $onWidgetCreate = new OnFieldCreate($menu, $this->getFieldData());
        view('Modules::Field/Views/edit', [
            'SiteURL' => AppConfig::getAppUrl(),
            'Data' => $onWidgetCreate->getAllToArray(),
            'TimeZone' => AppConfig::getTimeZone()
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    #[NoReturn] public function update(string $slug)
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->fieldUpdateRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors());
            redirect(route('fields.edit', [$slug]));
        }

        try {
            $widgetToUpdate = $this->getFieldData()->createField();
            $widgetToUpdate['field_slug'] = helper()->slug(input()->fromPost()->retrieve('field_slug'));
            $this->getFieldData()->updateWithCondition($widgetToUpdate, ['field_slug' => $slug], $this->getFieldData()->getFieldTable());

            $slug = $widgetToUpdate['field_slug'];
            session()->flash(['Field Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            
            redirect(route('fields.edit', ['field' => $slug]));
        }catch (\Exception){
            session()->flash(['An Error Occurred Updating The Field Item']);
            redirect(route('fields.edit', [$slug]));
        }
    }


    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function delete(string $slug)
    {
        try {
            $this->getFieldData()->deleteWithCondition(whereCondition: "field_slug = ? AND can_delete = 1", parameter: [$slug], table: $this->getFieldData()->getFieldTable());
            session()->flash(['Field Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
            
            redirect(route('fields.index'));
        } catch (\Exception){
            session()->flash(['Failed To Delete Field']);
            redirect(route('fields.index'));
        }
    }

    /**
     * @throws \Exception
     */
    public function deleteMultiple()
    {
        if (!input()->fromPost()->hasValue('itemsToDelete')){
            session()->flash(['Nothing To Delete'], type: Session::SessionCategories_FlashMessageInfo);
            redirect(route('fields.index'));
        }

        $this->getFieldData()->deleteMultiple(
            $this->getFieldData()->getFieldTable(),
            array_flip($this->getFieldData()->getFieldColumns()),
            'field_id',
            onSuccess: function (){
                session()->flash(['Field Deleted'], type: Session::SessionCategories_FlashMessageSuccess);

                redirect(route('fields.index'));
            },
            onError: function (){
                session()->flash(['Failed To Delete Field']);
                redirect(route('fields.index'));
            },
            moreWhereCondition: 'AND can_delete = 1',
        );
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }


}
