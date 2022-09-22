<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Post\EventHandlers;

use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class PostHookIntoDataTable implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnHookIntoTemplate */
        $event->hookInto('Core::before_data_table', function (TonicsView $tonicsView){
            $dtHeaders = $tonicsView->accessArrayWithSeparator('DataTable.headers');
            if ($this->isDataTypePost($tonicsView)){
                $dtHeaders[] = [
                    'title' => 'Actions',
                    'minmax' => "250px, 1.2fr",
                ];

                $tonicsView->addToVariableData('DataTable.headers', $dtHeaders);
            }
        });

        $event->hookInto('Core::before_data_table_data', function (TonicsView $tonicsView) {
            if ($this->isDataTypePost($tonicsView)){
                $dtRow = $tonicsView->accessArrayWithSeparator('dtRow');
                $editButton = <<<HTML
<a class="d:flex flex-gap:small  text-align:center bg:transparent border:none color:black bg:white-one 
border:black padding:small margin-top:0 cursor:pointer button:box-shadow-variant-2" href="/admin/posts/$dtRow->post_slug/edit">
    <span>Edit</span>
</a>

<a target="_blank" class="d:flex flex-gap:small  text-align:center bg:transparent border:none color:black bg:white-one 
border:black padding:small margin-top:0 cursor:pointer button:box-shadow-variant-2" href="/posts/$dtRow->post_slug">
    <span>Preview</span>
</a>
HTML;
                $dtRow->postView = $editButton;
            }
        });
    }

    /**
     * @param TonicsView $tonicsView
     * @return mixed
     */
    public function isDataTypePost(TonicsView $tonicsView): mixed
    {
        return $tonicsView->accessArrayWithSeparator('DataTable.dataTableType') === 'POST';
    }
}