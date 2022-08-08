<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Widget\Helper;

use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class WidgetLoop implements QueryModeHandlerInterface
{

    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string
    {
        $frag = '';
        $menuPrefix = '/admin/tools/widget/';
        $widgets = (isset($queryData->data)) ? $queryData->data : [];
        foreach ($widgets as $k => $widget) {
            $widget->list_id = $k;
            $widget->widget_name = strip_tags($widget->widget_name);
            $widget->edit_link = $menuPrefix . $widget->widget_slug . '/edit';
            $widget->builder_link = $menuPrefix . 'items/' . $widget->widget_slug . '/builder';
            $widget->destroy_link = $menuPrefix . $widget->widget_slug . '/delete';
            $widget->destroy_text = 'Delete';

            if ($callback !== null){
                $queryMode = $tonicsView->getVariableData()['QUERY_MODE'];
                $queryMode[$query_name] = $widget;
                $tonicsView->setDontCacheVariable(true);
                $tonicsView->addToVariableData('QUERY_MODE', $queryMode);
                $frag .= $callback();
            }
        }
        return $frag;
    }
    
}