<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\EventHandlers\DefaultFieldHandlers;

use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;
use Embera\Embera;

class TonicsOEmbedFieldHandler implements FieldTemplateFileInterface
{

    /**
     * @throws \Exception
     */
    public function handleFieldLogic(OnFieldMetaBox $event = null, $fields = null): string
    {
        $embedFrag = '';
        $url = '';
        $responsive = true;
        $width = 0;
        $height = 0;
        if (isset($fields[0]->_children)) {
            $OEmbed_url = 'OEmbed_url';
            $OEmbed_width = 'OEmbed_width';
            $OEmbed_height = 'OEmbed_height';
            $OEmbed_responsive = 'OEmbed_responsive';

            foreach ($fields[0]->_children as $child) {
                if ($child->field_input_name === $OEmbed_url) {
                    $url = $child->field_data[$OEmbed_url] ?? '';
                }

                if ($child->field_input_name === $OEmbed_width) {
                    $responsive = $child->field_data[$OEmbed_width] ?? '';
                    if ($responsive === '0'){
                        $responsive = false;
                    }
                }

                if ($child->field_input_name === $OEmbed_height) {
                    $width = $child->field_data[$OEmbed_height] ?? '';
                }

                if ($child->field_input_name === $OEmbed_responsive) {
                    $height = $child->field_data[$OEmbed_responsive] ?? '';
                }
            }

            $config = [
                'https_only' => true,
                'width' => (int)$width,
                'height' => (int)$height,
                'responsive' => $responsive,
                'fake_responses' => Embera::DISABLE_FAKE_RESPONSES,
            ];

            $ember = new Embera($config);
            $url = filter_var($url, FILTER_SANITIZE_URL);
            // Never Use the autoEmbed, can cause xss
            $embedFrag = $ember->getUrlData($url);
            $embedFrag = $embedFrag[$url]['html'] ?? '';
        }

        return $embedFrag;
    }

    public function name(): string
    {
        return 'OEmbed';
    }

    public function fieldSlug(): string
    {
       return 'oembed';
    }

    public function canPreSaveFieldLogic(): bool
    {
        return true;
    }
}