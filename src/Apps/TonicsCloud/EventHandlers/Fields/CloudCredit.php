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

namespace App\Apps\TonicsCloud\EventHandlers\Fields;

use App\Apps\TonicsCloud\Controllers\BillingController;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class CloudCredit implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('CloudCredit', 'Cloud Credit Usage', 'TonicsCloud',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
        );
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     *
     * @return string
     * @throws \Exception
     */
    public function settingsForm (OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Cloud Credit';
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
</div>
FORM;
        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function userForm (OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Cloud Credits';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $remainingCredit = round(BillingController::RemainingCredit(), 4);
        $tableFrag = <<<FRAG
<style>
input[type='radio'] {
    accent-color: #000000;
}
</style>
<section class="dataTable disable-select owl" data-event-click="true" data-event-dblclick="true" data-event-scroll-bottom="true">
    <table id="dt" style="grid-template-columns: minmax(150px, 1fr);">
            <thead>
            <tr>
                    <th title="Remaining Credit" data-type="" data-minmax="150px, 1fr" data-td="description">Remaining Credit</th>
            </tr>
            </thead>
            <tbody class="max-height:300px overflow-x:auto">
                <tr>
                    <td tabindex="-1">$$remainingCredit</td>  
                </tr>
            </tbody>
    </table>
</section>
FRAG;

        $frag .= $tableFrag;
        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

}