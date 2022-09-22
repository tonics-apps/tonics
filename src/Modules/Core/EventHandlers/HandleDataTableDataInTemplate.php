<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\EventHandlers;

use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class HandleDataTableDataInTemplate implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnHookIntoTemplate */
        $event->hookInto('Core::on_data_table_data', function (TonicsView $tonicsView){
            $dataFrag = '';
            $headers = $tonicsView->accessArrayWithSeparator('DataTable.headers');
            $dtRows = $tonicsView->accessArrayWithSeparator('dtRow');
            foreach ($headers as $header){
                if (isset($header['td'])){
                    if (property_exists($dtRows, $header['td'])){
                        $data = $dtRows->{$header['td']};
                        $dataFrag .=<<<HTML
<td tabindex="-1">$data</td>
HTML;
                    }
                }
            }
            return $dataFrag;
        });

    }
}