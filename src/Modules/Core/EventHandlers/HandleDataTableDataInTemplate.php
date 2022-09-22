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

        /** @var $event OnHookIntoTemplate */
        $event->hookInto('Core::before_data_table', function (TonicsView $tonicsView){
            $dtHeaders = $tonicsView->accessArrayWithSeparator('DataTable.headers');
            if ($this->isDataTableTypeEditablePreview($tonicsView)){
                $dtHeaders[] = [
                    'title' => 'Actions',
                    'minmax' => "250px, 1.2fr",
                    'td' => 'postView'
                ];

                $tonicsView->addToVariableData('DataTable.headers', $dtHeaders);
            }
        });

        $event->hookInto('Core::before_data_table_data', function (TonicsView $tonicsView) {
            if ($this->isDataTableTypeEditablePreview($tonicsView)){
                $dtRow = $tonicsView->accessArrayWithSeparator('dtRow');
                $editButton = <<<HTML
<a class="text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:small
                        margin-top:0 cursor:pointer button:box-shadow-variant-3" href="$dtRow->_edit_link">
    <span>Edit</span>
</a>

<a target="_blank" class="text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:small
                        margin-top:0 cursor:pointer button:box-shadow-variant-3" href="$dtRow->_preview_link">
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
    public function isDataTableTypeEditablePreview(TonicsView $tonicsView): mixed
    {
        return $tonicsView->accessArrayWithSeparator('DataTable.dataTableType') === 'EDITABLE_PREVIEW';
    }
}