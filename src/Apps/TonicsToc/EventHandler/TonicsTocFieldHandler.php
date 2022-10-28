<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsToc\EventHandler;

use App\Apps\TonicsToc\Controller\TonicsTocController;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;

class TonicsTocFieldHandler implements FieldTemplateFileInterface
{

    /**
     * @throws \Exception
     */
    public function handleFieldLogic(OnFieldMetaBox $event = null, $data = null): string
    {
        if (FieldConfig::hasPreSavedFieldData()){
            return FieldConfig::getPreSavedFieldData();
        }
        return $this->getTocResult();
    }

    public function fieldSlug(): string
    {
        return 'app-tonicstoc';
    }

    public function name(): string
    {
        return 'Tonics Table Of Content';
    }

    public function canPreSaveFieldLogic(): bool
    {
        return true;
    }

    /**
     * @throws \Exception
     */
    public function getTocResult(): string
    {
        $settings = TonicsTocController::getSettingsData();
        $result = '';
        if (isset(getPostData()['tableOfContentData'])){
            if (getPostData()['tableOfContentData']['headersFound'] >= $settings['toc_trigger']){
                $settings['toc_label'] = (empty(getPostData()['toc_label'])) ? $settings['toc_label'] : getPostData()['toc_label'];
                foreach(getPostData()['tableOfContentData']['tree'] as $item){
                    $result .= $item['data'];
                }
            }
        }

        $tocClass = trim($settings['toc_class'], '.');
        return "<div class='{$tocClass}'><ul class='tonics-toc-ul'><{$settings['toc_label_tag']} class='tonics-toc-label-tag-class'> {$settings['toc_label']} </{$settings['toc_label_tag']}> $result </ul></div>";
    }
}