<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Core\EventHandlers\Theme;

use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use App\Modules\Field\Events\FieldSelectionDropper\OnAddFieldSelectionDropperEvent;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class HookLayoutSelectors implements HandlerInterface
{
    /**
     * @inheritDoc
     * @throws \Throwable
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnHookIntoTemplate */
        $event->hookInto(OnAddFieldSelectionDropperEvent::HOOK_NAME_THEME_BOOTSTRAP, function () use ($event, &$dropper) {

            $dropper = $event->getTonicsView()->accessArrayWithSeparator('Dropper');
            if ($dropper instanceof OnAddFieldSelectionDropperEvent) {
                foreach ($dropper->getProcessedLogicData() as $hookName => $hookString) {
                    $event->hookInto($hookName, fn(TonicsView $tonicsView) => $hookString);
                }

                $event->hookInto('in_head_inline_styles', fn() => $dropper->processInlineStyles());
            }

        }, true);
    }
}