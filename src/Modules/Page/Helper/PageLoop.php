<?php

namespace App\Modules\Page\Helper;

use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class PageLoop implements QueryModeHandlerInterface
{

    /**
     * @throws \Exception
     */
    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string
    {
        $frag = '';
        $pagePrefix = '/admin/pages/';
        $pages = (isset($queryData->data)) ? $queryData->data : [];
        foreach ($pages as $k => $page) {
            $page->list_id = $k;
            $page->page_title = strip_tags($page->page_title);
            $page->edit_link = $pagePrefix . $page->page_id . '/edit';
            $page->preview_link = helper()->slugForPage('/' .$page->page_slug);

            if ($page->page_status === -1){
                $page->destroy_link = $pagePrefix . $page->page_id . '/delete';
                $page->destroy_text = 'Delete';
            } else {
                $page->destroy_link = $pagePrefix . $page->page_id . '/trash';
                $page->destroy_text = 'Trash';
            }

            if ($callback !== null){
                $queryMode = $tonicsView->getVariableData()['QUERY_MODE'];
                $queryMode[$query_name] = $page;
                $tonicsView->setDontCacheVariable(true);
                $tonicsView->addToVariableData('QUERY_MODE', $queryMode);
                $frag .= $callback();
            }

        }
        return $frag;
    }
}