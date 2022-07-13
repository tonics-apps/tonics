<?php

namespace App\Themes\NinetySeven\Library;

use App\Modules\Core\Library\Tables;
use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class CategoryLoop implements QueryModeHandlerInterface
{

    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string
    {
        $frag = '';
        $categories = (isset($queryData->data)) ? $queryData->data : [];
        foreach ($categories as $cat) {
            $cat->cat_name = strip_tags($cat->cat_name);
            $cat->_full_link = "/categories/$cat->slug_id/$cat->cat_slug"; $stripTagsContent = strip_tags($cat->cat_content);
            $cat->_og_description = substr($stripTagsContent, 0, 200);
            if (strlen($stripTagsContent) > 200){
                $cat->_og_description .="...";
            }
            if ($callback !== null){
                $queryMode = $tonicsView->getVariableData()['QUERY_MODE'];
                $queryMode[$query_name] = $cat;
                $tonicsView->setDontCacheVariable(true);
                $tonicsView->addToVariableData('QUERY_MODE', $queryMode);
                $frag .= $callback();
            }
        }
        $tonicsView->setDontCacheVariable(false);
        return $frag;
    }
}