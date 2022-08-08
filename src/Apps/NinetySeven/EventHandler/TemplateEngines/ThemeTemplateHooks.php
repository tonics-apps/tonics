<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\EventHandler\TemplateEngines;

use App\Modules\Core\Events\OnSelectTonicsTemplateHooks;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class ThemeTemplateHooks implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnSelectTonicsTemplateHooks */
        $event->addMultipleHooks(['in_site_header_section', 'in_site_header_section_logo', 'in_site_header_section_nav', 'in_site_header_section_nav_ul']);
        $event->addMultipleHooks(['post_cat_filter_option',]);
    }
}