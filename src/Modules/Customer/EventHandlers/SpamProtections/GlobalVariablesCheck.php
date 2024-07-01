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

namespace App\Modules\Customer\EventHandlers\SpamProtections;

use App\Modules\Core\Library\View\CustomTokenizerState\SimpleShortCode\TonicsSimpleShortCode;
use App\Modules\Customer\Controllers\CustomerSettingsController;
use App\Modules\Customer\Interfaces\CustomerSpamProtectionInterfaceAbstract;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInputMethodsInterface;
use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateRangeException;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRenderWithTagInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsTemplateCustomRendererInterface;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateArrayLoader;
use Devsrealm\TonicsTemplateSystem\Node\Tag;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class GlobalVariablesCheck extends CustomerSpamProtectionInterfaceAbstract
{

    /**
     * @inheritDoc
     */
    public function name (): string
    {
        return 'customer-settings-spam-protection-global-variables-check';
    }

    /**
     * @inheritDoc
     */
    public function displayName (): string
    {
        return 'Global Variable Check';
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function isSpam (array $data): bool
    {
        $content = $data[CustomerSettingsController::SpamProtection_GlobalVariablesCheckInput] ?? null;
        if ($content === null) {
            return false;
        }

        $shortCode = new TonicsSimpleShortCode([
            'render' => GlobalVariablesCheck::GlobalVariableShortCodeCustomRenderer(),
        ]);
        $shortCode->getView()->addModeHandler('server', GlobalVariablesCheck::GlobalVariableShortCode()::class);
        $shortCode->getView()->addModeHandler('post', GlobalVariablesCheck::GlobalVariableShortCode()::class);
        $shortCode->getView()->setTemplateLoader(new TonicsTemplateArrayLoader(['template' => $content]));
        return $shortCode->getView()->render('template') === '1';
    }

    /**
     * @return TonicsModeRenderWithTagInterface|TonicsTemplateViewAbstract
     */
    public static function GlobalVariableShortCode (): TonicsModeRenderWithTagInterface|TonicsTemplateViewAbstract
    {
        return new class extends TonicsTemplateViewAbstract implements TonicsModeRenderWithTagInterface {

            private bool $isSpam = false;

            public function render (string $content, array $args, Tag $tag): string
            {
                $input = $this->getInput($_SERVER);

                if (strtolower($tag->getTagName()) === 'post') {
                    $input = $this->getInput($_POST);
                }

                $args['spam'] = $args['spam'] === '1';
                $condition = false;

                if ($args['key'] !== null) {

                    $condition = $input->has($args['key']);

                    if ($condition) {
                        $condition = $this->checkValue($input, $args);
                    }
                }

                if ($args['keyNot'] !== null) {
                    $condition = !$input->has($args['keyNot']);
                }

                $this->isSpam = $condition && $args['spam'];
                return '';
            }


            public function defaultArgs (): array
            {
                return [
                    'spam'               => '1',
                    'key'                => null,
                    'keyNot'             => null,
                    'value'              => null,
                    'valueStartsWith'    => null,
                    'valueEndsWith'      => null,
                    'valueContains'      => null,
                    'valueNot'           => null,
                    'valueNotStartsWith' => null,
                    'valueNotEndsWith'   => null,
                    'valueNotContains'   => null,
                    'valueEmpty'         => null,
                ];
            }

            /**
             * @param $data
             *
             * @return TonicsRouterRequestInputMethodsInterface
             */
            public function getInput ($data): TonicsRouterRequestInputMethodsInterface
            {
                return input()->fromPost($data);
            }

            /**
             * @param TonicsRouterRequestInputMethodsInterface $input
             * @param $args
             *
             * @return bool
             */
            public function checkValue (TonicsRouterRequestInputMethodsInterface $input, $args): bool
            {
                $value = $input->retrieve($args['key']);
                $conditions = $this->valueCallBacks();

                $conditionMet = true;
                foreach ($args as $argKey => $argValue) {
                    if ($argValue !== null && isset($conditions[$argKey]) && $conditions[$argKey]($value, $argValue) === false) {
                        $conditionMet = false;
                        break;
                    }
                }

                return $conditionMet;
            }

            /**
             * @return \Closure[]
             */
            public function valueCallBacks (): array
            {
                return [
                    'value'              => fn($value, $argValue) => $value === $argValue,
                    'valueStartsWith'    => fn($value, $argValue) => str_starts_with($value, $argValue),
                    'valueEndsWith'      => fn($value, $argValue) => str_ends_with($value, $argValue),
                    'valueContains'      => fn($value, $argValue) => str_contains($value, $argValue),
                    'valueNot'           => fn($value, $argValue) => $value !== $argValue,
                    'valueNotStartsWith' => fn($value, $argValue) => !str_starts_with($value, $argValue),
                    'valueNotEndsWith'   => fn($value, $argValue) => !str_ends_with($value, $argValue),
                    'valueNotContains'   => fn($value, $argValue) => !str_contains($value, $argValue),
                    'valueEmpty'         => fn($value, $argValue) => $argValue === '0' && !empty($value) || $argValue === '1' && empty($value),
                ];
            }

            /**
             * @return bool
             */
            public function isSpam (): bool
            {
                return $this->isSpam;
            }

            /**
             * @param bool $isSpam
             *
             * @return void
             */
            public function setIsSpam (bool $isSpam): void
            {
                $this->isSpam = $isSpam;
            }
        };
    }

    /**
     * @return TonicsTemplateCustomRendererInterface
     */
    public static function GlobalVariableShortCodeCustomRenderer (): TonicsTemplateCustomRendererInterface
    {
        return new class implements TonicsTemplateCustomRendererInterface {

            public function render (TonicsView $tonicsView): string
            {
                $isSpam = '';
                /**@var Tag $tag */
                foreach ($tonicsView->getStackOfOpenTagEl() as $tag) {
                    try {
                        $mode = $tonicsView->getModeRendererHandler($tag->getTagName());
                        if ($mode instanceof TonicsModeRenderWithTagInterface) {
                            $tagName = strtolower($tag->getTagName());
                            if ($tagName === 'server' || $tagName === 'post') {
                                $mode->render($tag->getContent(), helper()->mergeKeyIntersection($mode->defaultArgs(), $tag->getArgs()), $tag);
                                if ($mode->isSpam()) {
                                    $isSpam = (string)$mode->isSpam();
                                    break;
                                }
                            }
                        }
                    } catch (TonicsTemplateRangeException|\Exception) {
                    }
                }
                return $isSpam;
            }

        };
    }
}