<?php

namespace App\Modules\Menu\Helper;

use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class MenuLoop implements QueryModeHandlerInterface
{

    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string
    {
        $frag = '';
        $menuPrefix = '/admin/tools/menu/';
        $menus = (isset($queryData->data)) ? $queryData->data : [];
        foreach ($menus as $k => $menu) {
            $menu->list_id = $k;
            $menu->menu_name = strip_tags($menu->menu_name);
            $menu->edit_link = $menuPrefix . $menu->menu_slug . '/edit';
            $menu->builder_link = $menuPrefix . 'items/' . $menu->menu_slug . '/builder';
            $menu->destroy_link = $menuPrefix . $menu->menu_slug . '/delete';
            $menu->destroy_text = 'Delete';

            if ($callback !== null){
                $queryMode = $tonicsView->getVariableData()['QUERY_MODE'];
                $queryMode[$query_name] = $menu;
                $tonicsView->setDontCacheVariable(true);
                $tonicsView->addToVariableData('QUERY_MODE', $queryMode);
                $frag .= $callback();
            }
        }
        return $frag;
    }
}