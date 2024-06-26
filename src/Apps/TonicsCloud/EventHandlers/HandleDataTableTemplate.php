<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\TonicsCloud\EventHandlers;

use App\Apps\TonicsCloud\Controllers\ContainerController;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class HandleDataTableTemplate implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnHookIntoTemplate */
        $event->hookInto('Core::on_data_table_data', function (TonicsView $tonicsView) {
            $dataFrag = '';
            if ($this->isDataTableTypeTonicsCloud($tonicsView)) {
                $data = $tonicsView->accessArrayWithSeparator('dtRow->dtHeader.td');
                if ($tonicsView->accessArrayWithSeparator('__current_var_key_name') !== 'dtRow._view_links') {
                    $data = helper()->htmlSpecChar($data);
                }

                $currentVarKeyName = $tonicsView->accessArrayWithSeparator('__current_var_key_name');
                if ($currentVarKeyName === 'dtRow.service_instance_status' ||
                    $currentVarKeyName === 'dtRow.app_status' ||
                    $currentVarKeyName === 'dtRow.container_status') {
                    if (strtolower($data) === 'running') {
                        $data = "<div class='d:flex align-items:center'><div class='dataTable-status dataTable-status-started'></div>$data</div>";
                    } elseif (strtolower($data) === 'stopped' || strtolower($data) === 'error') {
                        $data = "<div class='d:flex align-items:center'><div class='dataTable-status dataTable-status-stopped'></div>$data</div>";
                    } elseif (strtolower($data) === 'offline') {
                        $data = "<div class='d:flex align-items:center'><div class='dataTable-status dataTable-status-info'></div>$data</div>";
                    } else {
                        $data = "<div class='d:flex align-items:center'><div class='dataTable-status dataTable-status-progress'></div>$data</div>";
                    }

                    $tonicsView->addToVariableData('dtRow.service_instance_status', $data);
                }

                $tonicsView->addToVariableData($tonicsView->accessArrayWithSeparator('__current_var_key_name'), $data);
            }
            return $dataFrag;
        });

        /** @var $event OnHookIntoTemplate */
        $event->hookInto('Core::before_data_table', function (TonicsView $tonicsView) {
            $dtHeaders = $tonicsView->accessArrayWithSeparator('DataTable.headers');
            if ($this->isDataTableTypeTonicsCloud($tonicsView)) {
                $dtHeaders[] = [
                    'title'  => 'Actions',
                    'minmax' => "80px, .8fr",
                    'td'     => '_view_links',
                ];
                $tonicsView->addToVariableData('DataTable.headers', $dtHeaders);
                $dtHeaders = null;
            }
        });

        $event->hookInto('Core::before_data_table_data', handler: function (TonicsView $tonicsView) {
            if ($this->isDataTableTypeTonicsCloud($tonicsView)) {
                $editButton = '';
                $dtRow = $tonicsView->accessArrayWithSeparator('dtRow');
                if (!empty($dtRow->_edit_link)) {
                    $editButton .= <<<HTML
<a class="text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:small
                        margin-top:0 cursor:pointer button:box-shadow-variant-3" href="$dtRow->_edit_link">
    <span>Edit</span>
</a>
HTML;
                }

                if (isset($dtRow->service_instance_status) && strtolower($dtRow->service_instance_status) === 'running') {
                    $containerCreateRoute = route('tonicsCloud.containers.create') . "?instance_id=$dtRow->provider_instance_id";
                    $editButton .= <<<HTML
<a class="text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:small
                        margin-top:0 cursor:pointer button:box-shadow-variant-3" href="$containerCreateRoute">
    <span>Add Container</span>
</a>
HTML;
                }

                if (isset($dtRow->_apps_link)) {
                    $editButton .= <<<HTML
<a class="text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:small
                        margin-top:0 cursor:pointer button:box-shadow-variant-3" href="$dtRow->_apps_link">
    <span>Apps</span>
</a>
HTML;
                }

                $dtRow->_view_links = $editButton;
            }

        });

    }

    /**
     * @param TonicsView $tonicsView
     *
     * @return bool
     */
    public function isDataTableTypeTonicsCloud (TonicsView $tonicsView): bool
    {
        return $tonicsView->accessArrayWithSeparator('DataTable.dataTableType') === 'TONICS_CLOUD';
    }

    /**
     * @param TonicsView $tonicsView
     *
     * @return bool
     */
    public function isTypeContainerController (TonicsView $tonicsView): bool
    {
        return $tonicsView->accessArrayWithSeparator('DataTable.controller') === ContainerController::class;
    }
}