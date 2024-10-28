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

namespace App\Modules\Post\EventHandlers\FieldSelectionDropper;

use App\Modules\Field\Events\FieldSelectionDropper\OnAddFieldSelectionDropperEvent;
use App\Modules\Post\Services\PostService;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class PostLayoutSelectorHandler implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnAddFieldSelectionDropperEvent */
        // simpleParameterParameterTypeValue
        $event->addFields('NON-EXISTED-FIELD-NAME', [
            'post-query' => [
                'children' => function (OnAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'postSimpleParameterParameterType'     => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $this->addQueryLoopAttributeKey($field, $event, 'postSimpleParameterParameterType');
                            $event->getCurrentOpenElement()->__queryType = 'postSimpleParameterParameterType';
                        },
                        'categorySimpleParameterParameterType' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $this->addQueryLoopAttributeKey($field, $event, 'categorySimpleParameterParameterType');
                            $event->getCurrentOpenElement()->__queryType = 'categorySimpleParameterParameterType';
                        },
                        'simpleParameterParameterTypeValue'    => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            if (isset($event->getCurrentOpenElement()->__queryType)) {
                                $this->addQueryLoopAttributeValue($field, $event, $event->getCurrentOpenElement()->__queryType, 'simpleParameterParameterTypeValue');
                            }
                        },
                        'DateParametersDateType'               => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $this->addQueryLoopAttributeKey($field, $event, 'DateParametersDateType');
                        },
                        'DateParametersDate'                   => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $this->addQueryLoopAttributeValue($field, $event, 'DateParametersDateType', 'DateParametersDate', function ($value) {
                                return helper()->date(datetime: $value);
                            });
                        },
                        'OrderParametersDirection'             => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $this->addQueryLoopAttributeKey($field, $event, 'OrderParametersDirection');
                        },
                        'OrderParametersBy'                    => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $this->addQueryLoopAttributeValue($field, $event, 'OrderParametersDirection', 'OrderParametersBy');
                        },
                        'StatusParametersType'                 => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $this->addQueryLoopAttributeKey($field, $event, 'StatusParametersType');
                        },
                        'StatusParametersStatus'               => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $this->addQueryLoopAttributeValue($field, $event, 'StatusParametersType', 'StatusParametersStatus');
                        },
                        'PaginationParametersPerPage'          => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $event->addAttributes($event->getCurrentOpenElement(), ['PaginationParametersPerPage' => $event->accessFieldData($field, 'PaginationParametersPerPage')]);
                        },
                        'ChildrenNested'                       => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $event->addAttributes($event->getCurrentOpenElement(), ['ChildrenNested' => $event->accessFieldData($field, 'ChildrenNested')]);
                        },
                        'searchParameter'                      => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCurrentOpenElement();
                            $queryName = $event->accessFieldData($field, 'searchParameter');
                            if (url()->hasParamAndValue($queryName)) {
                                $queryValue = trim(url()->getParam($queryName));
                                $event->addAttributes($event->getCurrentOpenElement(), ['searchParameter' => $queryValue]);
                            }

                            $queryLoop = null;
                            if (isset($element->__LoopCacheKey) && trim($element->__LoopCacheKey) !== '' && $event->hasCacheKey($element->__LoopCacheKey)) {
                                $queryLoop = $event->getCache()[$element->__LoopCacheKey];
                            }

                            if ($queryLoop === null) {
                                if (isset($event->getCurrentOpenElement()->__queryType)) {
                                    if ($event->getCurrentOpenElement()->__queryType == 'postSimpleParameterParameterType') {
                                        $queryLoop = PostService::QueryLoop($event->getAttributes($event->getCurrentOpenElement()));
                                    }
                                    if ($event->getCurrentOpenElement()->__queryType == 'categorySimpleParameterParameterType') {
                                        $queryLoop = PostService::QueryLoopCategory($event->getAttributes($event->getCurrentOpenElement()));
                                    }
                                } else {
                                    $queryLoop = PostService::QueryLoop($event->getAttributes($event->getCurrentOpenElement()));
                                }
                            }

                            $queryLoopData = $queryLoop->data ?? [];
                            if (!empty($queryLoopData) && is_iterable($queryLoopData)) {
                                $event->addLoopDataToCache($element, $queryLoop);
                                $element->__loop_data = $queryLoopData;
                                $element->__loop_count = count($queryLoopData);
                            }
                        },
                    ]);
                },
            ],
        ]);
    }

    /**
     * @param \stdClass $field
     * @param OnAddFieldSelectionDropperEvent $event
     * @param string $accessKey
     *
     * @return void
     */
    private function addQueryLoopAttributeKey (\stdClass $field, OnAddFieldSelectionDropperEvent $event, string $accessKey): void
    {
        $type = $event->accessFieldData($field, $accessKey);
        if (!empty($type)) {
            $event->addAttributes($event->getCurrentOpenElement(), [$accessKey => $type]);
        }
    }

    /**
     * @param \stdClass $field
     * @param OnAddFieldSelectionDropperEvent $event
     * @param string $accessKey
     * @param string $accessValue
     * @param callable|null $onValue
     *
     * @return void
     */
    private function addQueryLoopAttributeValue (\stdClass $field, OnAddFieldSelectionDropperEvent $event, string $accessKey, string $accessValue, callable $onValue = null): void
    {
        $element = $event->getCurrentOpenElement();
        $value = $event->accessFieldData($field, $accessValue);
        if (!empty($value) && $event->attributeExists($element, $accessKey)) {
            $type = $event->getAttribute($element, $accessKey);
            $event->removeAttributes($element, $accessKey);
            if ($onValue) {
                $value = $onValue($value);
            }
            $event->addAttributes($element, [$type => $value]);
        }
    }
}