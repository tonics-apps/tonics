<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\EventHandler\PageTemplates\BeatsTonics\ThemeFolder;

use App\Modules\Field\Helper\FieldHelpers;
use App\Modules\Page\Events\AbstractClasses\PageTemplateInterface;
use App\Modules\Page\Events\OnPageTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TonicsNinetySevenBeatsTonicsThemeFolderHomePageTemplate implements PageTemplateInterface, HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var OnPageTemplate $event */
        $event->addTemplate($this);
    }

    public function name(): string
    {
        return 'TonicsNinetySeven_BeatsTonics_ThemeFolderHomePageTemplate';
    }

    /**
     * @throws \Exception
     */
    public function handleTemplate(OnPageTemplate $pageTemplate): void
    {
        dd($pageTemplate, 'This is a test');
        $ninetySevenHomePage = [
            'Featured' => [],
            'Best' => [],
            'OtherCategories' => [],
        ];

        $featuredAndTopBestParent = 'ninetySeven_featured_and_top_best_modular';
        $featuredLabelInputName = 'ninetySeven_featured_label';
        $featuredPostQuery = 'ninetySeven_featured_postQuery';
        $bestLabelInputName = 'ninetySeven_best_label';
        $bestPostQueryLabel = 'ninetySeven_best_postQuery';

        # Other Post Category...

        $otherPostCategoryParent = 'ninetySeven_other_post_category_repeater';
        $otherPostCategoryTitle = 'ninetySeven_category_title';
        $otherPostCategoryLink = 'ninetySeven_category_link';
        $otherPostCategoryDesc = 'ninetySeven_category_description';
        $otherPostCategoryQuery = 'ninetySeven_categoryQuery';

        $pageTemplate->setViewName('Apps::NinetySeven/Views/Page/single');

        if (isset($pageTemplate->getFieldSettings()['_fieldDetails']) && is_array($fieldDetails = json_decode($pageTemplate->getFieldSettings()['_fieldDetails']))){
            $fieldDetails = helper()->generateTree(['parent_id' => 'field_parent_id', 'id' => 'field_id'], $fieldDetails, onData: function ($field){
                if (isset($field->field_options) && helper()->isJSON($field->field_options)) {
                    $fieldOption = json_decode($field->field_options);
                    $field->field_options = $fieldOption;
                }
                return $field;
            });

            foreach ($fieldDetails as $field){
                if (isset($field->field_options)){
                    if ($field->field_input_name === $featuredAndTopBestParent && isset($field->_children)){
                        foreach ($field->_children as $child){

                            if ($child->field_input_name === $featuredLabelInputName){
                                $ninetySevenHomePage['Featured']['Label'] = $child->field_options->ninetySeven_featured_label;
                            }
                            if ($child->field_input_name === $featuredPostQuery){
                                if (isset($child->_children[0]->_children)){
                                    $ninetySevenHomePage['Featured']['QueryData'] = FieldHelpers::postDataFromPostQueryBuilderField($child->_children[0]->_children);
                                }
                            }
                            if ($child->field_input_name === $bestLabelInputName){
                                $ninetySevenHomePage['Best']['Label'] = $child->field_options->ninetySeven_best_label;
                            }
                            if ($child->field_input_name === $bestPostQueryLabel){
                                if (isset($child->_children[0]->_children)){
                                    try {
                                        $ninetySevenHomePage['Best']['QueryData'] = FieldHelpers::postDataFromPostQueryBuilderField($child->_children[0]->_children);
                                    }catch (\Throwable $throwable){
                                        // Log...
                                        $ninetySevenHomePage['Best']['QueryData'] = null;
                                    }
                                }
                            }
                        }

                    }

                    # Other Post Category...
                    if ($field->field_input_name === $otherPostCategoryParent){
                        if (isset($field->_children)){
                            $currentOther = [];
                            foreach ($field->_children as $child){

                                if ($child->field_input_name === $otherPostCategoryTitle){
                                    $currentOther['Title'] = $child->field_options->ninetySeven_category_title ?? '';
                                }

                                if ($child->field_input_name === $otherPostCategoryLink){
                                    $currentOther['Link'] = $child->field_options->ninetySeven_category_link ?? '';
                                }

                                if ($child->field_input_name === $otherPostCategoryDesc){
                                    $currentOther['Desc'] = $child->field_options->ninetySeven_category_description ?? '';
                                }

                                if ($child->field_input_name === $otherPostCategoryQuery){
                                    if (isset($child->_children[0]->_children)){
                                        try {
                                            $currentOther['QueryData'] = FieldHelpers::postDataFromPostQueryBuilderField($child->_children[0]->_children);
                                        }catch (\Throwable $throwable){
                                            // Log...
                                            $currentOther['QueryData'] = null;
                                        }
                                    }
                                    $ninetySevenHomePage['OtherCategories'][] = $currentOther;
                                }
                            }
                        }
                    }
                }
            }
        }

        $fieldSettings = $pageTemplate->getFieldSettings();
        $fieldSettings['NinetySevenHomePage'] = $ninetySevenHomePage;
        $pageTemplate->setFieldSettings($fieldSettings);
        $ninetySevenHomePage = null;
    }
}