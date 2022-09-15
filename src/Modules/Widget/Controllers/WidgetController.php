<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Widget\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Widget\Data\WidgetData;
use App\Modules\Widget\Events\OnWidgetCreate;
use App\Modules\Widget\Rules\WidgetValidationRules;
use JetBrains\PhpStorm\NoReturn;

class WidgetController
{
    use UniqueSlug, Validator, WidgetValidationRules;

    private WidgetData $widgetData;

    public function __construct(WidgetData $widgetData)
    {
        $this->widgetData = $widgetData;
    }

    /**
     * @throws \Exception
     */
    public function index()
    {
        $cols = '`widget_id`, `widget_name`, `widget_slug`, `created_at`';
        $data = $this->getWidgetData()->generatePaginationData(
            $cols,
            'widget_name',
            $this->getWidgetData()->getWidgetTable());

        $widgetListing = '';
        if ($data !== null){
            $widgetListing = $this->getWidgetData()->adminWidgetListing($data->data);
            unset($data->data);
        }

        view('Modules::Widget/Views/index', [
            'Data' => $data,
            'WidgetListing' => $widgetListing
        ]);
    }

    /**
     * @throws \Exception
     */
    public function create()
    {
        view('Modules::Widget/Views/create');
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function store()
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->widgetStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('widgets.create'));
        }

        try {
            $widget = $this->getWidgetData()->createWidget();
            $widgetReturning = db()->insertReturning($this->getWidgetData()->getWidgetTable(), $widget, $this->getWidgetData()->getWidgetColumns(), 'widget_id');

            $onWidgetCreate = new OnWidgetCreate($widgetReturning, $this->getWidgetData());
            event()->dispatch($onWidgetCreate);

            session()->flash(['Widget Created'], type: Session::SessionCategories_FlashMessageSuccess);
            
            redirect(route('widgets.edit', ['widget' => $onWidgetCreate->getWidgetSlug()]));
        } catch (\Exception){
            session()->flash(['An Error Occurred Creating The Widget Item'], input()->fromPost()->all());
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
        $menu = $this->getWidgetData()->selectWithCondition($this->getWidgetData()->getWidgetTable(), ['*'], "widget_slug = ?", [$slug]);
        if (!is_object($menu)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $onWidgetCreate = new OnWidgetCreate($menu, $this->getWidgetData());
        view('Modules::Widget/Views/edit', [
            'Data' => $onWidgetCreate->getAllToArray(),
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    #[NoReturn] public function update(string $slug)
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->widgetUpdateRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors());
            redirect(route('widgets.edit', [$slug]));
        }

        try {
            $widgetToUpdate = $this->getWidgetData()->createWidget();
            $widgetToUpdate['widget_slug'] = helper()->slug(input()->fromPost()->retrieve('widget_slug'));
            db()->FastUpdate($this->getWidgetData()->getWidgetTable(), $widgetToUpdate, db()->Where('widget_slug', '=', $slug));

            $slug = $widgetToUpdate['widget_slug'];
            session()->flash(['Widget Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            
            redirect(route('widgets.edit', ['widget' => $slug]));
        }catch (\Exception){
            session()->flash(['An Error Occurred Updating The Widget Item']);
            redirect(route('widgets.edit', [$slug]));
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
            $this->getWidgetData()->deleteWithCondition(whereCondition: "widget_slug = ?", parameter: [$slug], table: $this->getWidgetData()->getWidgetTable());
            session()->flash(['Widget Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
            
            redirect(route('widgets.index'));
        } catch (\Exception){
            session()->flash(['Failed To Delete Widget']);
            redirect(route('widgets.index'));
        }
    }

    /**
     * @throws \Exception
     */
    public function deleteMultiple()
    {
        if (!input()->fromPost()->hasValue('itemsToDelete')){
            session()->flash(['Nothing To Delete'], type: Session::SessionCategories_FlashMessageInfo);
            redirect(route('widgets.index'));
        }

        $this->getWidgetData()->deleteMultiple(
            $this->getWidgetData()->getWidgetTable(),
            array_flip($this->getWidgetData()->getWidgetColumns()),
            'widget_id',
            onSuccess: function (){
                session()->flash(['Widget Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
                
                redirect(route('widgets.index'));
            },
            onError: function (){
                session()->flash(['Failed To Delete Widget']);
                redirect(route('widgets.index'));
            },
        );
    }

    /**
     * @return WidgetData
     */
    public function getWidgetData(): WidgetData
    {
        return $this->widgetData;
    }


}
