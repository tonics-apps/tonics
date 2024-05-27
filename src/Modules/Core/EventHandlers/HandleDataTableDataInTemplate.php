<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
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
    public function handleEvent (object $event): void
    {
        /** @var $event OnHookIntoTemplate */
        $event->hookInto('Core::before_data_table', function (TonicsView $tonicsView) {
            $dtHeaders = $tonicsView->accessArrayWithSeparator('DataTable.headers');
            if ($this->isDataTableTypeEditablePreview($tonicsView)
                || $this->isDataTableTypeEdit($tonicsView)
                || $this->isDataTableTypeEditableBuilder($tonicsView)
                || $this->isDataTableTypeView($tonicsView)) {
                $dtHeaders[] = [
                    'title'  => 'Actions',
                    'minmax' => "250px, 1.2fr",
                    'td'     => '_view_links',
                ];

                $tonicsView->addToVariableData('DataTable.headers', $dtHeaders);
                $dtHeaders = null;
            }
        });

        $event->hookInto('Core::before_data_table_data', handler: function (TonicsView $tonicsView) {

            if ($this->isDataTableTypeEditablePreview($tonicsView)
                || $this->isDataTableTypeEditableBuilder($tonicsView)
                || $this->isDataTableTypeEdit($tonicsView)) {
                $editButton = '';
                $dtRow = $tonicsView->accessArrayWithSeparator('dtRow');
                if (isset($dtRow->_edit_link)) {
                    $editButton .= <<<HTML
<a class="text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:small
                        margin-top:0 cursor:pointer button:box-shadow-variant-3" href="$dtRow->_edit_link">
    <span>Edit</span>
</a>
HTML;
                }

                if ($this->isDataTableTypeEditablePreview($tonicsView)) {
                    if (isset($dtRow->_preview_link)) {
                        $editButton .= <<<HTML
<a target="_blank" class="text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:small
                        margin-top:0 cursor:pointer button:box-shadow-variant-3" href="$dtRow->_preview_link">
    <span>Preview</span>
</a>
HTML;
                    }
                }

                if ($this->isDataTableTypeEditableBuilder($tonicsView)) {
                    $editButton .= <<<HTML
<a class="text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:small
                        margin-top:0 cursor:pointer button:box-shadow-variant-3" href="$dtRow->_builder_link">
    <span>Builder</span>
</a>
HTML;
                }
                $dtRow->_view_links = $editButton;
            }

            if ($this->isDataTableTypeView($tonicsView)) {
                $editButton = '';
                $dtRow = $tonicsView->accessArrayWithSeparator('dtRow');
                $editButton .= <<<HTML
<a class="text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:small
                        margin-top:0 cursor:pointer button:box-shadow-variant-3" href="$dtRow->_view">
    <span>View</span>
</a>
HTML;
                $dtRow->_view_links = $editButton;
            }

        });

        #
        # FOR PLUGINS PAGE
        #
        /** @var $event OnHookIntoTemplate */
        $event->hookInto('Core::before_data_table', function (TonicsView $tonicsView) {
            $dtHeaders = $tonicsView->accessArrayWithSeparator('DataTable.headers');
            if ($this->isDataTableTypeApplicationView($tonicsView)) {
                $dtHeaders[] = [
                    'title'  => 'Actions',
                    'minmax' => "250px, 1.2fr",
                    'td'     => '_view_links',
                ];

                $dtHeaders = null;
            }
        });
    }

    /**
     * @param TonicsView $tonicsView
     *
     * @return bool
     */
    public function isDataTableTypeEditablePreview (TonicsView $tonicsView): bool
    {
        return $tonicsView->accessArrayWithSeparator('DataTable.dataTableType') === 'EDITABLE_PREVIEW';
    }

    /**
     * @param TonicsView $tonicsView
     *
     * @return bool
     */
    public function isDataTableTypeEditableBuilder (TonicsView $tonicsView): bool
    {
        return $tonicsView->accessArrayWithSeparator('DataTable.dataTableType') === 'EDITABLE_BUILDER';
    }

    public function isDataTableTypeApplicationView (TonicsView $tonicsView): bool
    {
        return $tonicsView->accessArrayWithSeparator('DataTable.dataTableType') === 'APPLICATION_VIEW';
    }

    public function isDataTableTypeView (TonicsView $tonicsView): bool
    {
        return $tonicsView->accessArrayWithSeparator('DataTable.dataTableType') === 'VIEW_LINK';
    }

    public function isDataTableTypeEdit (TonicsView $tonicsView): bool
    {
        return $tonicsView->accessArrayWithSeparator('DataTable.dataTableType') === 'EDIT_LINK';
    }
}