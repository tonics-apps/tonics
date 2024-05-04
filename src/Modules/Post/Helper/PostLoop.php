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

namespace App\Modules\Post\Helper;

use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use App\Modules\Core\Library\View\Extensions\Traits\QueryModeHandlerHelper;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class PostLoop implements QueryModeHandlerInterface
{

    public function handleQueryData(TonicsView $tonicsView, string $query_name, \stdClass $queryData, callable $callback = null): string
    {
        $frag = '';
        $posts = (isset($queryData->data)) ? $queryData->data : [];
        $postPrefix = '/admin/posts/';
        foreach ($posts as $k => $post) {
            $post->list_id = $k;
            $post->post_title = strip_tags($post->post_title);
            $post->edit_link = $postPrefix . $post->post_slug . '/edit';
            $post->preview_link = '/posts/' . $post->post_slug_id . '/' . $post->post_slug;
            if ($post->post_status === -1){
                $post->destroy_link = $postPrefix . $post->post_slug . '/delete';
                $post->destroy_text = 'Delete';
                $post->button_data_attr = 'data-click-onconfirmdelete="true"';
            } else {
                $post->destroy_link = $postPrefix . $post->post_slug . '/trash';
                $post->destroy_text = 'Trash';
                $post->button_data_attr = 'data-click-onconfirmtrash="true"';
            }

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