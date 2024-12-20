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

namespace App\Modules\Field\EventHandlers\Fields\Post;

use App\Modules\Core\Library\Tables;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class PostRecent implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('PostRecent', 'Recent Post', 'Post',
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Posts Recent';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $postTake = (isset($data->postTake)) ? $data->postTake : '5';
        $frag = $event->_topHTMLWrapper($fieldName, $data);

        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
    <label class="menu-settings-handle-name" for="inputName-$changeID">Input Name
            <input id="inputName-$changeID" name="inputName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$inputName" placeholder="(Optional) Input Name">
    </label>
</div>

<div class="form-group">
    <label class="menu-settings-handle-name" for="recent-post-name">Number of Posts
        <input name="postTake" id="recent-post-name" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray"
         value="$postTake">
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'PostAuthorSelect';
        $keyValue = $event->getKeyValueInData($data, $data->inputName);
        $postTake = (isset($data->postTake)) ? $data->postTake : '';
        $postTake = $keyValue ?: $postTake;
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';

        $slug = $data->field_slug;
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";
        $frag .= <<<FORM
<div class="form-group">
    <label class="menu-settings-handle-name" for="recent-post-name">Number of Posts
        <input name="$inputName" id="recent-post-name" type="number" class="menu-name color:black border-width:default border:black placeholder-color:gray"
         value="$postTake">
    </label>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewData (OnFieldMetaBox $event, $data = null)
    {
        $fieldData = (isset($data->_field->field_data)) ? $data->_field->field_data : '';
        $postData = !empty(getPostData()) ? getPostData() : $fieldData;
        $postTake = (isset($postData[$data->inputName])) ? $postData[$data->inputName] : $data->postTake;
        $postTbl = Tables::getTable(Tables::POSTS);
        $postData = [];
        try {
            db(onGetDB: function ($db) use ($postTake, $postTbl, &$postData) {
                $tblCol = table()->pickTableExcept($postTbl, ['updated_at']) . ', CONCAT_WS("/", "/posts", post_slug) as _preview_link';
                $postData = $db->Select($tblCol)
                    ->From($postTbl)
                    ->WhereEquals('post_status', 1)
                    ->Where("$postTbl.created_at", '<=', helper()->date())
                    ->OrderByDesc(table()->pickTable($postTbl, ['updated_at']))->Limit($postTake)->FetchResult();
            });
        } catch (\Exception $exception) {
            // log..
        }

        addToGlobalVariable("PostRecent_$data->inputName", ['Data' => $postData]);
    }
}