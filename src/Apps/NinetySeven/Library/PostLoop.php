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

namespace App\Apps\NinetySeven\Library;

use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class PostLoop implements QueryModeHandlerInterface
{

    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string
    {
        $frag = '';
        $posts = (isset($queryData->data)) ? $queryData->data : [];
        foreach ($posts as $post) {
            $post->post_title = strip_tags($post->post_title);
            $post->_full_link = "/posts/$post->post_slug_id/$post->post_slug";
            $post->post_field_settings = json_decode($post->post_field_settings);
            $post->_og_description = $post->post_field_settings->seo_description;
            if ($callback !== null){
                $queryMode = $tonicsView->getVariableData()['QUERY_MODE'];
                $queryMode[$query_name] = $post;
                $tonicsView->addToVariableData('QUERY_MODE', $queryMode);
                $frag .= $callback();
            }
        }
        return $frag;
    }
}