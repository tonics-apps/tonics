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

        $event->hookInto('Core::after_data_table_header', function (TonicsView $tonicsView){
            return '';
            return <<<HTML
<th data-type="" class="position:sticky top:0">Edit</th>
HTML;
        });

        $event->hookInto('Core::before_data_table_data', function (TonicsView $tonicsView) {
            $dtRow = $tonicsView->accessArrayWithSeparator('dtRow');
            return '1';
            dd($tonicsView->getVariableData(), $dtRow);
            $editButton = <<<HTML
<a class="d:flex flex-gap:small  text-align:center bg:transparent border:none color:black bg:white-one 
border:black padding:small margin-top:0 cursor:pointer button:box-shadow-variant-2" href="/admin/posts/$dtRow->post_slug/edit">
    <span>Edit</span>
</a>
HTML;
           $dtRow->postView = $editButton;
            // return '<td tabindex="-1">Checkmate</td>';
            // dd($tonicsView->getVariableData(), $dtRow);
        });
    }

    public function test()
    {
        return 'test';
    }
}