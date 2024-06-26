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

namespace App\Apps\NinetySeven\EventHandler\PageTemplates\WriTonics;

use App\Modules\Core\Library\Tables;
use App\Modules\Field\Helper\FieldHelpers;
use App\Modules\Page\Events\AbstractClasses\PageTemplateInterface;
use App\Modules\Page\Events\OnPageTemplate;
use App\Modules\Post\Data\PostData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class TonicsNinetySevenWriTonicsPostPageTemplate implements PageTemplateInterface, HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var OnPageTemplate $event */
        $event->addTemplate($this);
    }

    public function name(): string
    {
        return 'TonicsNinetySeven_WriTonics_PostPageTemplate';
    }

    /**
     * @throws \Exception
     */
    public function handleTemplate(OnPageTemplate $pageTemplate): void
    {

        $CatTbl = Tables::getTable(Tables::CATEGORIES);

        $fieldSettings = $pageTemplate->getFieldSettings();

        try {
            $pageTemplate->setViewName('Apps::NinetySeven/Views/Page/single');

            if (isset($pageTemplate->getFieldSettings()['_fieldDetails']) && is_array($fieldDetails = json_decode($pageTemplate->getFieldSettings()['_fieldDetails']))) {
                $fieldDetails = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $fieldDetails, onData: function ($field) {
                    if (isset($field->field_options) && helper()->isJSON($field->field_options)) {
                        $fieldOption = json_decode($field->field_options);
                        $field->field_options = $fieldOption;
                    }
                    return $field;
                });

                $queryBuilderLoop = null;
                $postPagePostSettingsQueryBuilderLabel = 'app_ninetyseven_writonics_post_page_settings_postSettings_queryBuilder';
                helper()->recursivelyLoop(
                    [
                        'items' => $fieldDetails,
                        'children_name' => '_children',
                        'onItem' => function ($field) use (&$queryBuilderLoop, $postPagePostSettingsQueryBuilderLabel) {
                            if (isset($field->field_input_name) && $field->field_input_name === $postPagePostSettingsQueryBuilderLabel) {
                                $queryBuilderLoop = $field;
                                return true;
                            }
                        }
                    ]
                );

                $fieldSettings['NinetySeven_WriTonics_EnableSearch'] = false;
                $fieldSettings['NinetySeven_WriTonics_EnableCategorySelect'] = false;
                $fieldSettings['NinetySeven_WriTonics_EnableFeaturedImage'] = false;

                if (isset($fieldSettings['app_ninetyseven_writonics_post_page_settings_postSettings_featuredImage']) &&
                    $fieldSettings['app_ninetyseven_writonics_post_page_settings_postSettings_featuredImage'] === '1'){
                    $fieldSettings['NinetySeven_WriTonics_EnableFeaturedImage'] = true;
                }

                if (isset($fieldSettings['app_ninetyseven_writonics_post_page_settings_formSettings_search']) &&
                    $fieldSettings['app_ninetyseven_writonics_post_page_settings_formSettings_search'] === '1'){
                    $fieldSettings['NinetySeven_WriTonics_EnableSearch'] = true;
                }

                if (isset($fieldSettings['app_ninetyseven_writonics_post_page_settings_formSettings_categoriesSelect']) &&
                    $fieldSettings['app_ninetyseven_writonics_post_page_settings_formSettings_categoriesSelect'] === '1'){

                    $fieldSettings['NinetySeven_WriTonics_EnableCategorySelect'] = true;

                    $categories = null;
                    db(onGetDB: function (TonicsQuery $db) use ($CatTbl, &$categories){
                        $categories = $db->Select(table()->pickTableExcept($CatTbl, ['field_settings', 'created_at', 'updated_at']))
                            ->From(Tables::getTable(Tables::CATEGORIES))->FetchResult();
                    });
                    $fieldSettings['NinetySeven_WriTonics_PostCategoryData'] = (new PostData())->categoryCheckBoxListing($categories, url()->getParam('cat') ?? [], type: 'checkbox');
                }

                $postData = [];
                if (isset($queryBuilderLoop->_children[0]->_children)){
                    try {
                        $postData = FieldHelpers::postDataFromPostQueryBuilderField($queryBuilderLoop->_children[0]->_children, settings: [
                            'search' => $fieldSettings['NinetySeven_WriTonics_EnableSearch'],
                            'cat_id' => $fieldSettings['NinetySeven_WriTonics_EnableCategorySelect'],
                        ]
                        );
                    }catch (\Throwable $throwable){
                        // Log...
                    }
                }

                $fieldSettings['NinetySeven_WriTonics_PostData'] = $postData;
                $pageTemplate->setFieldSettings($fieldSettings);

            }

        } catch (\Exception $exception) {
            // Log..
        }


    }
}