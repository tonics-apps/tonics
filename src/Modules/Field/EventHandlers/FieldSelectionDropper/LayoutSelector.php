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

namespace App\Modules\Field\EventHandlers\FieldSelectionDropper;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Field\Events\FieldSelectionDropper\OnAddFieldSelectionDropperEvent;
use App\Modules\Field\Events\OnComparedSortedFieldCategories;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateArrayLoader;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class LayoutSelector implements HandlerInterface
{
    /**
     * @param object $event
     *
     * @return void
     * @throws \Throwable
     */
    public function handleEvent (object $event): void
    {
        if ($event instanceof OnComparedSortedFieldCategories) {

            $fieldCategories = $event->getFieldCategories();
            if (isset($fieldCategories["layout-selector"])) {
                $dropper = FieldConfig::getFieldSelectionDropper();
                # Clone the field as we do not want it to affect anything that does with generating the users form field
                $cloneField = helper()->compressFieldItems($fieldCategories["layout-selector"]);
                $dropper->processLogic(helper()->unCompressFieldItems($cloneField));

                if (is_array($dropper->storageGet($dropper::HOOK_NAMES_STORAGE_KEY))) {
                    $dropper->addInputSelects('tonicsLayoutHookSelector', $dropper->storageGet($dropper::HOOK_NAMES_STORAGE_KEY));
                    apcu_store(url()->getRequestURL(), helper()->compressFieldItems($dropper->getInputSelects()));
                }

                addToGlobalVariable(AppConfig::GLOBAL_CURRENT_LAYOUT_SELECTOR, $dropper);
            }

            return;
        }

        if ($event instanceof OnAddFieldSelectionDropperEvent) {

            /**
             * For tonicsPageBuilderFieldSelector element, the settings is in the $field->_settings
             */
            $event->addFields('tonicsPageBuilderLayoutSelector',
                [
                    ...self::CoreLayoutsHandler(),
                    ...self::CoreElementsHandler(),
                    ...self::CoreElementLoopHandlers(),
                    ...self::CoreHookElementHandler(),
                ],
            );

            /**
             * -----------------------------
             * For INLINE STYLES
             * -----------------------
             */
            $event->hookIntoFieldDataKey('field_input_name', [
                'tonicsBuilderStyleColorType' => [
                    'open' => function ($field) use ($event) {
                        $class = $event->registerAndGetClass();
                        $styles = 'color:';
                        $colorType = '';
                        if (isset($field->field_data->{'tonicsBuilderStyleColorType'})) {
                            $colorType = $field->field_data->{'tonicsBuilderStyleColorType'};
                        }
                        if ($colorType === 'Background') {
                            $styles = 'background:';
                        }

                        if ($colorType === 'Link') {
                            $class = "$class a";
                        } elseif ($colorType === 'Link Hover') {
                            $class = "$class a:hover";
                        } elseif ($colorType === 'Heading') {
                            $class = "$class :is(h1, h2, h3, h4, h5, h6)";
                        }

                        $event->addStylesToOpenElementClass($styles, $class);
                    },
                ],
                'tonicsBuilderStyleColor'     => [
                    'open' => function ($field) use ($event) {
                        if (isset($field->field_data->{"tonicsBuilderStyleColor[]"})) {
                            $event->addStylesToOpenElementClass($field->field_data->{"tonicsBuilderStyleColor[]"} . ';');
                        }
                        if (isset($field->field_data->{"tonicsBuilderStyleColor"})) {
                            $event->addStylesToOpenElementClass($field->field_data->{"tonicsBuilderStyleColor"} . ';');
                        }
                    },
                ],
                'tonicsBuilderStylePadding'   => [
                    'open' => function ($field) use ($event) {
                        if (isset($field->field_data->{"tonicsBuilderStylePadding"})) {
                            $styles = '';
                            $paddings = $field->field_data->{"tonicsBuilderStylePadding"};
                            foreach ($paddings as $padding) {
                                $padding = trim($padding);
                                $name = $this->paddingAndMarginMapper()[substr($padding, 0, 2)] ?? '';
                                $value = $this->paddingAndMarginMapper()[substr($padding, -2)] ?? 1;
                                $styles .= "padding$name: calc(var(--responsive-spacing-scale) * $value);";
                            }
                            $event->addStylesToOpenElementClass($styles, $event->registerAndGetClass());
                        }
                    },
                ],
                'tonicsBuilderStyleMargin'    => [
                    'open' => function ($field) use ($event) {
                        if (isset($field->field_data->{"tonicsBuilderStyleMargin"})) {
                            $styles = '';
                            $margins = $field->field_data->{"tonicsBuilderStyleMargin"};
                            foreach ($margins as $margin) {
                                $margin = trim($margin);
                                $name = $this->paddingAndMarginMapper()[substr($margin, 0, 2)] ?? '';
                                $value = $this->paddingAndMarginMapper()[substr($margin, -2)] ?? 1;
                                $styles .= "margin$name: calc(var(--responsive-spacing-scale) * $value);";
                            }
                            $event->addStylesToOpenElementClass($styles, $event->registerAndGetClass());
                        }
                    },
                ],
                'class-utilities'             => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $utilities = $event->accessFieldData($field, 'class-utilities');
                    if (is_array($utilities)) {
                        $utilities = array_map('trim', $utilities);
                        $event->appendAttributes($event->getCOE(), ['class' => implode(' ', $utilities)]);
                    }
                },
                'style-bg-image'              => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $data = $event->autoAccessFieldData($field, 'style-bg-image');
                    $event->getCOE()->backgroundStyles = [];
                    $event->getCOE()->backgroundStyles['background-image'] = "url($data)";
                },
                'style-bg-image-custom'       => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $data = $event->autoAccessFieldData($field, 'style-bg-image-custom');
                    if (!empty($data)) {
                        $event->getCOE()->backgroundStyles['background-image'] = "url($data)";
                    }
                },
                'background-size'             => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $data = $event->autoAccessFieldData($field, 'background-size');
                    if (!empty($data)) {
                        $event->getCOE()->backgroundStyles['background-size'] = "$data";
                    }
                }, // background-attachment
                'background-size-custom'      => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $data = $event->autoAccessFieldData($field, 'background-size-custom');
                    if (!empty($data)) {
                        $event->getCOE()->backgroundStyles['background-size'] = "$data";
                    }
                },
                'background-attachment'       => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $data = $event->autoAccessFieldData($field, 'background-attachment');
                    if (!empty($data)) {
                        $event->getCOE()->backgroundStyles['background-attachment'] = "$data";
                    }
                },
                'background-repeat'           => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $data = $event->autoAccessFieldData($field, 'background-repeat');
                    if (!empty($data)) {
                        $event->getCOE()->backgroundStyles['background-repeat'] = "$data";
                    }
                },
                'background-clip'             => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $data = $event->autoAccessFieldData($field, 'background-clip');
                    if (!empty($data)) {
                        $event->getCOE()->backgroundStyles['background-clip'] = "$data";
                    }
                }, // background-blend-mode
                'background-origin'           => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $data = $event->autoAccessFieldData($field, 'background-origin');
                    if (!empty($data)) {
                        $event->getCOE()->backgroundStyles['background-origin'] = "$data";
                    }
                },
                'background-blend-mode'       => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $data = $event->autoAccessFieldData($field, 'background-blend-mode');
                    if (!empty($data)) {
                        $event->getCOE()->backgroundStyles['background-blend-mode'] = "$data";
                    }
                },
                'background-position'         => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $data = $event->autoAccessFieldData($field, 'background-position');
                    if (!empty($data)) {
                        $event->getCOE()->backgroundStyles['background-position'] = "$data";
                    }
                },
                'background-position-custom'  => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $data = $event->accessFieldData($field, 'background-position-custom');
                    $data = strtolower($data);
                    if (!empty($data)) {
                        $args = $event->getElementsAttribute($data, attributesOnly: true);
                        $y = $args['y'] ?? 'center';
                        if (isset($args['x'])) {
                            $x = $event->_escape($args['x']);
                            $y = $event->_escape($y);
                            $event->getCOE()->backgroundStyles['background-position'] = "$x $y";
                        }
                    }
                    foreach ($event->getCOE()->backgroundStyles as $property => $value) {
                        $event->addStylesToOpenElementClass("$property:$value;");
                    }
                },
                'style-animation'             => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $data = $event->accessFieldData($field, 'style-hover-animation');
                    if (isset(self::CSSAnimations()[$data]) && !isset($event->getOutputInlineStyle()[$data])) {
                        $animations = self::CSSAnimations()[$data];
                        foreach ($animations as $key => $animation) {
                            $event->addInlineStyle($key, $animation);
                        }
                        $event->appendAttributes($event->getCOE(), ['class' => "$data"]);
                    }
                },
                'style-scale-animation'       => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $data = $event->accessFieldData($field, 'style-scale-animation');
                    if (isset(self::CSSAnimations()[$data]) && !isset($event->getOutputInlineStyle()[$data])) {
                        $animations = self::CSSAnimations()[$data];
                        foreach ($animations as $key => $animation) {
                            $event->addInlineStyle($key, $animation);
                        }
                        $event->appendAttributes($event->getCOE(), ['class' => "$data"]);
                    }
                },
                'style-attribute'             => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $element = $event->getCOE();
                    $element->styleAttribute = $event->autoAccessFieldData($field, 'style-attribute');
                },
                'style-attribute-value'       => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $element = $event->getCOE();
                    $value = $event->autoAccessFieldData($field, 'style-attribute-value');
                    $value = trim($value);
                    if ($value !== "") {
                        $event->addStylesToOpenElementClass("$element->styleAttribute:$value;");
                    }
                },
                'display-flex'                => fn($field, $event) => $event->autoAddSimpleStyles($field, 'display-flex', 'display'),
                'display-grid'                => fn($field, $event) => $event->autoAddSimpleStyles($field, 'display-grid', 'display'),
                'flex-wrap'                   => fn($field, $event) => $event->autoAddSimpleStyles($field, 'flex-wrap'),
                'flex-direction'              => fn($field, $event) => $event->autoAddSimpleStyles($field, 'flex-direction'),
                'justify-items'               => fn($field, $event) => $event->autoAddSimpleStyles($field, 'justify-items'),
                'justify-content'             => fn($field, $event) => $event->autoAddSimpleStyles($field, 'justify-content'),
                'align-items'                 => fn($field, $event) => $event->autoAddSimpleStyles($field, 'align-items'),
                'align-content'               => fn($field, $event) => $event->autoAddSimpleStyles($field, 'align-content'),
                'align-self'                  => fn($field, $event) => $event->autoAddSimpleStyles($field, 'align-self'),
                'gap'                         => fn($field, $event) => $event->autoAddSimpleStyles($field, 'gap'),
                'order'                       => fn($field, $event) => $event->autoAddSimpleStyles($field, 'order'),
                'flex'                        => fn($field, $event) => $event->autoAddSimpleStyles($field, 'order'),
                'grid-template-areas'         => fn($field, $event) => $event->autoAddSimpleStyles($field, 'grid-template-areas'),
                'grid-auto-flow'              => fn($field, $event) => $event->autoAddSimpleStyles($field, 'grid-auto-flow'),
                'grid-area'                   => fn($field, $event) => $event->autoAddSimpleStyles($field, 'grid-area'),
                'grid-column'                 => fn($field, $event) => $event->autoAddSimpleStyles($field, 'grid-column'),
                'grid-row'                    => fn($field, $event) => $event->autoAddSimpleStyles($field, 'grid-row'),
                'grid-template-rows'          => fn($field, $event) => $event->autoAddSimpleStyles($field, 'grid-template-rows'),
                'grid-template-columns'       => fn($field, $event) => $event->autoAddSimpleStyles($field, 'grid-template-columns'),

                'text-transform'  => fn($field, $event) => $event->autoAddSimpleStyles($field, 'text-transform'),
                'text-align'      => fn($field, $event) => $event->autoAddSimpleStyles($field, 'text-align'),
                'text-decoration' => fn($field, $event) => $event->autoAddSimpleStyles($field, 'text-decoration'),
                'font-style'      => fn($field, $event) => $event->autoAddSimpleStyles($field, 'font-style'),
                'font-weight'     => fn($field, $event) => $event->autoAddSimpleStyles($field, 'font-weight'),
                'font-size'       => fn($field, $event) => $event->autoAddSimpleStyles($field, 'font-size'),
                'line-height'     => fn($field, $event) => $event->autoAddSimpleStyles($field, 'line-height'),
            ]);


            /**
             * ---------------------------------------------------------
             * For tonicsPageBuilderFieldSelector element
             * ---------------------------------------------------
             */

            $elementsWithLoop = [
                ...self::CoreElementsHandler(),
                ...self::CoreElementLoopHandlers(),
            ];

            $event->addFields('tonicsPageBuilderFieldSelector', [
                ...$elementsWithLoop,
            ]);

            /**
             * ---------------------------------------------------------
             * For Layout Styles Field
             * ---------------------------------------------------
             */
            $event->addFields('tonicsPageBuilderLayoutProperty', [
                'style-attribute',
                'style-animation',
                'style-background',
                'style-class-utilities',
                'style-color-picker',
                'style-color',
                'style-spacing',
                'display-flex',
                'display-grid',
                ...self::ContainerQueries(),
            ]);

            $event->addFields('tonicsPageBuilderContainerProperty', [
                'style-attribute',
                'style-animation',
                'style-background',
                'style-class-utilities',
                'style-color-picker',
                'style-color',
                'style-spacing',
                'display-flex',
                'display-grid',
                'typography',
                ...self::ContainerQueries(),
            ]);

            $event->addFields('tonicsPageBuilderTextProperty', [
                'style-attribute',
                'style-animation',
                'style-class-utilities',
                'style-color-picker',
                'style-color',
                'style-spacing',
                'display-flex',
                'display-grid',
                'typography',
                ...self::ContainerQueries(),
            ]);

            $event->addFields('tonicsPageBuilderButtonProperty', [
                'style-attribute',
                'style-animation',
                'style-class-utilities',
                'style-color-picker',
                'style-color',
                'style-spacing',
                'display-flex',
                'display-grid',
                'typography',
                ...self::ContainerQueries(),
            ]);

            $event->addFields('tonicsPageBuilderImageProperty', [
                'style-attribute',
                'style-animation',
                'style-class-utilities',
                'style-color-picker',
                'style-color',
                'style-spacing',
                'display-flex',
                'display-grid',
                'typography',
                ...self::ContainerQueries(),
            ]);

            /**
             * LOOP DATA
             */
            $event->addFields('tonicsPageBuilderLoopData', [
                'json-data',
                'post-query',
            ]);

            /**
             * FOR VIDEO DATA
             */
            $event->addFields('tonicsPageBuilderVideoElementType', [
                'youtube-video-type',
                'media-video-type',
            ]);

        }
    }

    /**
     * @return array
     */
    public function paddingAndMarginMapper (): array
    {
        return [
            'pt'  => '-top',
            'pb'  => '-bottom',
            'pl'  => '-left',
            'pr'  => '-right',
            'mt'  => '-top',
            'mb'  => '-bottom',
            'ml'  => '-left',
            'mr'  => '-right',
            'xs'  => 1,
            'sm'  => 2,
            'md'  => 3,
            'lg'  => 4,
            'xl'  => 5,
            'xxl' => 6,
        ];
    }

    public static function ContainerQueries (): array
    {
        return [
            'container-queries' => [
                'element'  => false,
                'open'     => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $field->_oldCOE = $event->getCOE();
                    $event->newCurrentOpenElementTemporary(function (OnAddFieldSelectionDropperEvent $event) use ($field) {
                        $coe = $event->getCOE();
                        $coe->containerQueries = [];
                        $event->processChildrenOnlyLogic($field->_children ?? [], $field);
                        if (!empty($coe->containerSize)) {
                            $size = $coe->containerSize;
                            $name = $coe->containerName ?? '';
                            $event->addInlineStyle("@container $name (min-width: $size)", implode('', $coe->containerQueries));
                        }
                    }, $field);
                },
                'children' => function (onAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'containerSize'  => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCOE();
                            $containerSize = $event->autoAccessFieldData($field, 'containerSize');
                            $element->containerSize = $containerSize;
                        },
                        'container-name' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCOE();
                            $containerName = $event->autoAccessFieldData($field, 'container-name');
                            $element->containerName = $containerName;
                        },
                        'classTarget'    => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCOE();
                            $elClass = '.' . $event->generateUniqueClass($element);
                            $inlineStyles = $event->getOutputInlineStyle();
                            $classTarget = $event->autoAccessFieldData($field, 'classTarget') ?: $element?->_oldCOE?->__className ?? '';

                            foreach ($inlineStyles as $key => $inlineStyle) {

                                if (!empty($inlineStyle) && str_starts_with($key, $elClass)) {

                                    $innerQuery = str_replace($elClass, $classTarget, $key);
                                    $innerQuery = $innerQuery . ' { ' . $inlineStyle . ' } ';
                                    $element->containerQueries[] = $innerQuery;
                                    unset($inlineStyles[$key]);
                                }

                            }

                            $event->setOutputInlineStyle($inlineStyles);
                        },
                        'container-type' => fn($field, $event) => $event->autoAddSimpleStyles($field, 'container-type'),
                    ]);
                },
            ],
            'container-query-type',
        ];
    }

    /**
     * @return \Closure[][]
     */
    public static function CoreLayoutsHandler (): array
    {
        return [
            'layout-1-by-1'                             => [
                'open'  => fn($field, OnAddFieldSelectionDropperEvent $event) => $event->autoOpenFieldLayoutSelector($field, 'div', ['class' => "d:grid flex-gap layout-1-by-1"]),
                'close' => fn($field, $event) => $event->autoCloseField($field),
            ],
            'layout-1-by-2'                             => [
                'open'  => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    $event->autoOpenFieldLayoutSelector($field, 'div', ['class' => "d:grid flex-gap layout-1-by-2"]);
                },
                'close' => fn($field, $event) => $event->autoCloseField($field),
            ],
            'layout-1-by-3'                             => [
                'open'  => fn($field, $event) => $event->autoOpenFieldLayoutSelector($field, 'div', ['class' => "d:grid flex-gap layout-1-by-3"]),
                'close' => fn($field, $event) => $event->autoCloseField($field),
            ],
            'layout-2-by-2'                             => [
                'open'  => fn($field, $event) => $event->autoOpenFieldLayoutSelector($field, 'div', ['class' => "d:grid flex-gap layout-2-by-2"]),
                'close' => fn($field, $event) => $event->autoCloseField($field),
            ],
            'layout-2-by-3'                             => [
                'open'  => fn($field, $event) => $event->autoOpenFieldLayoutSelector($field, 'div', ['class' => "d:grid flex-gap layout-2-by-3"]),
                'close' => fn($field, $event) => $event->autoCloseField($field),
            ],
            'layout-2-by-1'                             => [
                'open'  => fn($field, $event) => $event->autoOpenFieldLayoutSelector($field, 'div', ['class' => "d:grid flex-gap layout-2-by-1"]),
                'close' => fn($field, $event) => $event->autoCloseField($field),
            ],
            'layout-3-by-1'                             => [
                'open'  => fn($field, $event) => $event->autoOpenFieldLayoutSelector($field, 'div', ['class' => "d:grid flex-gap layout-3-by-1"]),
                'close' => fn($field, $event) => $event->autoCloseField($field),
            ],
            'layout-magazine-header-two-columns-footer' => [
                'open'     => fn($field, $event) => $event->autoOpenFieldLayoutSelector($field, 'div', ['class' => "d:grid flex-gap layout-magazine-header-two-columns-footer"]),
                'close'    => fn($field, $event) => $event->autoCloseField($field),
                'children' => function (onAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'layoutMagazineHeader'   => [
                            'open'  => fn($field) => '<div class="grid-area:a">',
                            'close' => fn($field) => '</div>',
                        ],
                        'layoutMagazineColLeft'  => [
                            'open'  => fn($field) => '<div class="grid-area:b">',
                            'close' => fn($field) => '</div>',
                        ],
                        'layoutMagazineColRight' => [
                            'open'  => fn($field) => '<div class="grid-area:c">',
                            'close' => fn($field) => '</div>',
                        ],
                        'layoutMagazineFooter'   => [
                            'open'  => fn($field) => '<div class="grid-area:d">',
                            'close' => fn($field) => '</div>',
                        ],
                    ]);
                },
            ],
            'seo-settings'                              => [
                'open'     => function ($field, OnAddFieldSelectionDropperEvent $event) {

                    $event->autoAddFieldPropertyToElement($field, $event->getPage(), fn() => $event->accessPageData('og_image'), 'seo_og_image');
                    $event->autoAddFieldPropertyToElement($field, $event->getPage(), fn() => url()->getFullURL(), 'seo_og_url');

                    $publishedTime = $event->accessPageData('published_time', $event->accessPageData('created_at'));
                    $createdTime = $event->accessPageData('modified_time', $event->accessPageData('updated_at'));
                    $timeOffset = AppConfig::initLoaderMinimal()::getGlobalVariableData('App_Config')['APP_TIME_ZONE_OFFSET'] ?? '';

                    $event->autoAddFieldPropertyToElement($field, $event->getPage(), fn() => helper()->seoTime($publishedTime, $timeOffset), 'published_time');
                    $event->autoAddFieldPropertyToElement($field, $event->getPage(), fn() => helper()->seoTime($createdTime, $timeOffset), 'modified_time');

                },
                'children' => function (OnAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'seo_title'           => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'seo_title', 'seo_title');
                        },
                        'seo_description'     => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'seo_description', 'seo_description');
                        },
                        'seo_image'           => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'seo_image', 'seo_og_image');
                        },
                        'og_title'            => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'og_title', 'seo_title');
                        },
                        'og_description'      => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'og_description', 'seo_description');
                        },
                        'seo_open_graph_type' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $event->autoAddFieldPropertyToElement($field, $event->getPage(), 'seo_open_graph_type', 'seo_og_type');
                        },
                        'seo_canonical_url'   => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $url = filter_var(trim($event->accessFieldData($field, 'seo_canonical_url')), FILTER_SANITIZE_URL);
                            $event->autoAddFieldPropertyToElement($field, $event->getPage(), fn() => $url, 'seo_canonical_url');
                        },
                        'seo_indexing'        => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $event->autoAddFieldPropertyToElement($field, $event->getPage(), fn() => $event->accessFieldData($field, 'seo_indexing') === '0', 'seo_indexing');
                        },
                        'seo_following'       => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $event->autoAddFieldPropertyToElement($field, $event->getPage(), fn() => $event->accessFieldData($field, 'seo_following') === '0', 'seo_following');
                        },
                    ]);
                },
            ],
            'module-import',
        ];
    }

    /**
     * @return \Closure[][]
     */
    public static function CoreElementsHandler (): array
    {
        return [
            'this-element-doesnt-exist-but-children-does' => [
                # If any elements has the following children in its _children field_input_name, you don't need to handle it,
                # the below would handle it for you
                'children' => function (OnAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'htmlTagType'            => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCurrentOpenElement();
                            $tagType = $event->autoAccessFieldData($field, 'htmlTagType', $element->__defaultTagType ?? 'div');
                            $event->addDefaultTag($element, $tagType);
                        },
                        'htmlTagCustomTag'       => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCurrentOpenElement();
                            $htmlTagType = $event->autoAccessFieldData($field, 'htmlTagCustomTag');
                            if (!empty($htmlTagType)) {
                                $event->addDefaultTag($element, $htmlTagType);
                            }
                        },
                        'htmlInlineClass'        => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCurrentOpenElement();
                            $inlineSelector = $event->autoAccessFieldData($field, 'htmlInlineClass');
                            if (trim($inlineSelector) && $inlineSelector !== '') {
                                $element->_htmlInlineClass = $inlineSelector;
                            }
                        },
                        'htmlTagAttribute'       => function ($field, OnAddFieldSelectionDropperEvent $event) {

                            $element = $event->getCurrentOpenElement();
                            $tagType = $event->getDefaultTag($element);
                            if ($event->skip($element)) {
                                return '';
                            }

                            if (!empty($tagType)) {

                                $class = $event->generateUniqueClass($element);
                                if (isset($element->_htmlInlineClass)) {
                                    $inlineClass = $element->_htmlInlineClass;
                                    $event->updateInlineStyleClassName(".$class", ".$inlineClass");
                                    $class = $inlineClass;
                                    $element->__className = ".$inlineClass";
                                }

                                $event->appendAttributes($element, ['class' => $class]);
                                $attributes = $event->getElementsAttribute($event->accessFieldData($field, 'htmlTagAttribute'), $event->getAttributesToAppend($element), $event->getAttributes($element));
                                $children = $event->getChildrenFragFromOpenElement();
                                return "<$tagType $attributes>" . $children;
                            } else {
                                return $event->getChildrenFragFromOpenElement();
                            }

                        },
                        'alt'                    => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCurrentOpenElement();
                            $event->addAttributes($element, ['alt' => $event->autoAccessFieldData($field, 'alt')]);
                        },
                        'loading'                => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCurrentOpenElement();
                            $event->addAttributes($element, ['loading' => $event->autoAccessFieldData($field, 'loading')]);
                        },
                        'object-fit'             => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCurrentOpenElement();
                            $data = $event->autoAccessFieldData($field, 'object-fit');
                            $event->addAttributes($element, ["data-object-fit" => $data]);
                        },
                        'posterImage'            => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $data = $event->autoAccessFieldData($field, 'posterImage');
                            if (!empty($data)) {
                                $event->addAttributes($event->getCurrentOpenElement(), ["poster" => $data]);
                            }
                        },
                        'posterImageExternalURL' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $data = $event->autoAccessFieldData($field, 'posterImageExternalURL');
                            if (!empty($data)) {
                                $event->addAttributes($event->getCurrentOpenElement(), ["poster" => $data]);
                            }
                        },
                        'controls'               => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $data = $event->autoAccessFieldData($field, 'controls');
                            if (!empty($data)) {
                                $event->addAttributes($event->getCurrentOpenElement(), ["controls" => '']);
                            }
                        },
                        'autoplay'               => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $data = $event->autoAccessFieldData($field, 'autoplay');
                            if (!empty($data)) {
                                $event->addAttributes($event->getCurrentOpenElement(), ["autoplay" => '']);
                            }
                        },
                        'preload'                => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $data = $event->autoAccessFieldData($field, 'preload');
                            if (!empty($data)) {
                                $event->addAttributes($event->getCurrentOpenElement(), ["preload" => $data]);
                            }
                        },
                        'loop'                   => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $data = $event->autoAccessFieldData($field, 'loop');
                            if (!empty($data)) {
                                $event->addAttributes($event->getCurrentOpenElement(), ["loop" => '']);
                            }
                        },
                        'muted'                  => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $data = $event->autoAccessFieldData($field, 'muted');
                            if (!empty($data)) {
                                $event->addAttributes($event->getCurrentOpenElement(), ["muted" => '']);
                            }
                        },
                        'src'                    => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $data = $event->autoAccessFieldData($field, 'src');
                            if (!empty($data)) {
                                $event->addAttributes($event->getCurrentOpenElement(), ["src" => $data]);
                            }
                        },
                        'rawTextValue'           => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $data = $event->accessFieldData($field, 'rawTextValue');
                            $event->addChildrenFragToOpenElement($data);
                        },
                    ]);
                },
            ],
            'empty-element',
            'post-fieldsettings-applier'                  => [
                'children' => function (OnAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'post_fieldsettings_location' => function ($field, OnAddFieldSelectionDropperEvent $event) {

                            $contentFragment = '';
                            $fieldSettings = $event->accessFieldData($field, 'post_fieldsettings_location');

                            if (isset($fieldSettings->{'post_content'})) {

                                if (is_array($fieldSettings->{'post_content'})) {
                                    foreach ($fieldSettings->{'post_content'} as $postContent) {
                                        if (isset($postContent->raw)) {
                                            if ($postContent->raw) {
                                                $contentFragment .= $postContent->content;
                                            } else {
                                                $postFields = json_decode($postContent->postData);
                                                $event->processLogic($postFields);
                                                $contentFragment .= $event->getProcessedFragFromOpenElement();
                                                $event->addProcessedFragToOpenElement('');
                                            }
                                        }
                                    }
                                }

                                if (is_string($fieldSettings->{'post_content'})) {
                                    $contentFragment .= $fieldSettings->{'post_content'};
                                }

                            }

                            $event->addContentIntoHookTemplate($event->getLastTemplateHook(), $contentFragment);

                            if (isset($fieldSettings->{'fieldDetails'}) && is_array($fieldSettings->{'fieldDetails'})) {
                                $event->processLogic($fieldSettings->{'fieldDetails'});
                            }

                        },
                    ]);
                },
            ],
            'input-element'                               => [
                'open'     => function ($field, $event) {
                    $event->autoOpenField($field, 'input');
                },
                'close'    => fn($field, $event) => $event->autoCloseField($field),
                'children' => function (OnAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'tonicsBuilderInputType'    => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $type = $event->autoAccessFieldData($field, 'tonicsBuilderInputType');
                            if ($type === 'text-area') {
                                $event->addDefaultTag($event->getCOE(), 'textarea');
                            }

                            $event->addAttributes($event->getCurrentOpenElement(), ['type' => $type]);
                        },
                        'tonicsBuilderDefaultValue' => fn($field, $event) => $event->addAttributes($event->getCurrentOpenElement(), ['value' => $event->accessFieldData($field, 'tonicsBuilderDefaultValue')]),
                    ]);
                },
            ],
            'image-element'                               => [
                'open'     => fn($field, $event) => $event->autoOpenField($field, 'img') . "<picture>",
                'close'    => fn($field) => "</picture>",
                'children' => function (OnAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'tonicsBuilderImage'                        => fn($field, $event) => $event->addAttributes($event->getCurrentOpenElement(),
                            [
                                'src' => $event->autoAccessFieldData($field, 'tonicsBuilderImage'),
                            ],
                        ),
                        'tonicsBuilderImageExternalURL'             => fn($field, OnAddFieldSelectionDropperEvent $event) => $event->addAttributes($event->getCurrentOpenElement(),
                            [
                                'src' => $event->autoAccessFieldData($field, 'tonicsBuilderImageExternalURL', $event->getAttribute($event->getCurrentOpenElement(), 'src')),
                            ],
                        ),
                        'tonicsBuilderImageSourcesImage'            => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCurrentOpenElement();
                            $element->srcsetImage = $event->autoAccessFieldData($field, 'tonicsBuilderImageSourcesImage');
                        },
                        'tonicsBuilderImageSourcesExternalURL'      => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCurrentOpenElement();
                            $data = $event->autoAccessFieldData($field, 'tonicsBuilderImageSourcesExternalURL');
                            if (!empty($data)) {
                                $element->srcsetImage = $data;
                            }
                        },
                        'tonicsBuilderImageSourcesBreakPoint'       => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $data = $event->autoAccessFieldData($field, 'tonicsBuilderImageSourcesBreakPoint');
                            $element = $event->getCurrentOpenElement();
                            $element->srcsetBreakPoint = $data;
                        },
                        'tonicsBuilderImageSourcesCustomBreakPoint' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $data = $event->autoAccessFieldData($field, 'tonicsBuilderImageSourcesCustomBreakPoint');
                            $element = $event->getCurrentOpenElement();
                            if (!empty($data)) {
                                $element->srcsetBreakPoint = $data;
                            }
                            $srcBreakPoint = $element->srcsetBreakPoint;
                            # Properly escape the URL
                            $srcsetImage = helper()->htmlSpecChar($element->srcsetImage);
                            if (empty($srcsetImage) && empty($srcBreakPoint)) {
                                return '';
                            }

                            $size = '(max-width: ' . helper()->htmlSpecChar($element->srcsetBreakPoint) . ')';
                            return "<source media='$size' srcset='$srcsetImage'>";
                        },
                    ]);
                },
            ],
            'video-element'                               => [
                'open'  => fn($field, $event) => $event->autoOpenField($field, 'video'),
                'close' => fn($field, $event) => $event->autoCloseField($field),
            ],
            'text-element'                                => [
                'open'  => function ($field, $event) {
                    $event->autoOpenField($field, 'p');
                },
                'close' => fn($field, $event) => $event->autoCloseField($field),
            ],
            'rich-text-element'                           => [
                'open'     => fn($field, $event) => $event->autoOpenField($field, 'div'),
                'close'    => fn($field, $event) => $event->autoCloseField($field),
                'children' => function (OnAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'richTextValue' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCurrentOpenElement();
                            $tinymceFields = $event->accessFieldDataset($field, '_tinymce_fields');
                            if (helper()->isJSON($tinymceFields)) {
                                $tinymceFields = json_decode($tinymceFields);
                            }
                            $frag = '';
                            foreach ($tinymceFields as $tinymceField) {
                                if (isset($tinymceField->raw)) {
                                    if ($tinymceField->raw === false) {
                                        $fields = json_decode($tinymceField->postData);
                                        $element->_rich_text_field_last_key = array_key_last($fields) + 1;
                                        $event->processLogic($fields);
                                        $frag .= $event->getProcessedFragFromOpenElement();
                                        $event->addProcessedFragToOpenElement('');
                                    } else {
                                        $frag .= $tinymceField->content;
                                    }
                                }
                            }
                            $event->addChildrenFragToOpenElement($frag);
                        },
                    ]);
                },
            ],
            'button-element'                              => [
                'open'  => fn($field, $event) => $event->autoOpenField($field, 'button'),
                'close' => fn($field, $event) => $event->autoCloseField($field),
            ],
            'section-element'                             => [
                'children' => function (OnAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'section-element-repeater' => [
                            'open'  => function ($field, OnAddFieldSelectionDropperEvent $event) {
                                foreach ($event->getElementFieldSettingsChildren($field) as $child) {
                                    if (isset($child->field_input_name) && $child->field_input_name === 'registerHookName') {

                                        $hookName = $event->accessFieldData($child, 'registerHookName');

                                        $hooks = $event->storageGet($event::HOOK_NAMES_STORAGE_KEY);

                                        if (!is_array($hooks)) {
                                            $hooks = [];
                                        }

                                        if ($hookName !== '') {
                                            $hooks[] = $hookName;
                                            $event->storageAdd($event::HOOK_NAMES_STORAGE_KEY, $hooks);
                                        }

                                        if ($event->storageExists($hookName) && $event->storageNotEmpty($hookName)) {
                                            $event->addSkip($field)->removeFieldChildren($field);
                                            return $event->storageGet($hookName);
                                        }

                                    }
                                }
                                return $event->autoOpenFieldLayoutSelector($field, '');
                            },
                            'close' => fn($field, $event) => $event->autoCloseField($field),
                        ],
                    ]);
                },
            ],
            'repeater-element',
            'layout-selector'                             => [
                'children' => function (OnAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'layout-selector-modular-repeater' => [
                            'open'  => fn($field, OnAddFieldSelectionDropperEvent $event) => $event->autoOpenFieldLayoutSelector($field, ''),
                            'close' => fn($field, OnAddFieldSelectionDropperEvent $event) => $event->autoCloseField($field),
                        ],
                    ]);
                },
            ],
            'tonics-template-system'                      => [
                'children' => function (OnAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'TonicsTemplateSystem' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $arrayLoader = new TonicsTemplateArrayLoader(['main' => $event->accessFieldData($field, 'TonicsTemplateSystem', '', true)]);
                            AppConfig::initLoaderOthers()->getTonicsView()->copySettingsToNewViewInstance($view = new TonicsView());
                            $view->setTemplateLoader($arrayLoader)
                                ->setVariableData(
                                    [
                                        'Loop' => $event->getCurrentLoopData($field), 'Global' => getGlobalVariableData(),
                                    ]);

                            return $view->setTemplateName('main')->render('main', TonicsView::RENDER_CONCATENATE);
                        },
                    ]);
                },
            ],
            'html-fragment'                               => [
                'children' => function (OnAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'htmlTemplateFragment' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            return $event->accessFieldData($field, 'htmlTemplateFragment');
                        },
                    ]);
                },
            ],
            'html-class'                                  => [
                'close'    => fn($field, OnAddFieldSelectionDropperEvent $event) => $event->updateInlineStyleClassName($field->__className ?? '', $event->getAttributes($field)['class'] ?? ''),
                'children' => function (OnAddFieldSelectionDropperEvent $event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'tonicsBuilderClassName' => fn($field, $event) => $event->addAttributes($event->getCurrentOpenElement(), ['class' => $event->accessFieldData($field, 'tonicsBuilderClassName')]),
                    ]);
                },
            ],
        ];
    }

    /**
     * @return array[]
     */
    public static function CoreElementLoopHandlers (): array
    {
        return [
            'loop' => [
                'open'     => function ($field, OnAddFieldSelectionDropperEvent $event) {
                    if (isset($field->_children[0]->_children[2]->_children[0]->field_input_name)
                        && $field->_children[0]->_children[2]->_children) {
                        $children = $field->_children[0]->_children[2]->_children;
                        $children = $children ?? [];
                        foreach ($children as $child) {
                            if (!empty($child->field_input_name)) {
                                $data = $event->accessFieldData($child, $child->field_input_name);
                                if ($data !== '') {
                                    $field->{"__$child->field_input_name"} = $data;
                                }
                            }
                        }
                    }
                },
                'children' => function ($event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'json-data'     => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCurrentOpenElement();
                            $json = $event->accessFieldData($field, 'json-data');
                            if (helper()->isJSON($json)) {
                                $json = json_decode($json);
                            }

                            if (is_iterable($json)) {
                                $event->addLoopDataToCache($element, $json);
                                $element->__loop_data = $json;
                                $element->__loop_count = count($json);
                            }
                        },
                        'loop-children' => function ($field, OnAddFieldSelectionDropperEvent $event) {

                            if (isset($field->__ignore_loop)) {
                                return;
                            }

                            $element = $event->getCurrentOpenElement();
                            $children = [];
                            $loopData = $element->__loop_data ?? [];
                            $loopCount = $element->__loop_count ?? 1;
                            if (empty($loopData)) {
                                $loopData = $event->pullLoopDataFromCache($element);
                                if ($event->canLoopCacheData($element)) {
                                    if (is_array($loopData)) {
                                        $loopCount = count($loopData);
                                    }
                                } else {
                                    $loopData = [$loopData];
                                    $loopCount = 1;
                                }
                            }

                            for ($i = 0; $i < $loopCount; $i++) {
                                $field->__ignore_loop = true;
                                $dt = $loopData[$i] ?? null;
                                $cloneChild = clone $field;
                                $event->cloneFieldRecursively($cloneChild, fn($childField) => $childField->__loop_current_data = $dt);
                                $children[] = $cloneChild;
                                $cloneChild->__loop_current_data = $dt;
                            }

                            $field->__skip = true;
                            $event->processLogic($children);
                            $event->removeFieldChildren($field);
                        },
                    ]);
                },
            ],
        ];
    }

    /**
     * @return array[]
     */
    public static function CoreHookElementHandler (): array
    {
        return [
            'hook' => [
                'open'     => function ($field, OnAddFieldSelectionDropperEvent $event) {},
                'children' => function ($event) {
                    $event->hookIntoFieldDataKey('field_input_name', [
                        'hook-repeater' => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $event->processChildrenOnlyLogic($event->getElementFieldSettingsChildren($field), $event->getElementFieldSettings($field));
                            $hookParent = $field->_parentField ?? $event->getCOE();
                            if (isset($hookParent->__hookSectionName)) {
                                $field->__hookSectionName = $hookParent->__hookSectionName;
                                $field->__hookSectionType = $hookParent->__hookSectionType ?? '';
                            }

                            if (!empty($field->_children) && isset($field->__hookSectionName) && trim($field->__hookSectionName) !== '') {

                                $children = $field->_children;
                                $eventFresh = $event->processLogicFresh($children, function (OnAddFieldSelectionDropperEvent $eventFresh) use ($event) {
                                    $eventFresh->setCache($event->getCache());
                                }, outputType: $event::OUTPUT_ONLY_TYPE);
                                $hookName = $field->__hookSectionName;

                                $event->setCache($eventFresh->getCache());

                                $typeHandlers = [
                                    'REPLACE' => function () use ($hookName, $eventFresh, $field, $event) {
                                        $event->storageAdd($hookName, $eventFresh->getOutput());
                                    },
                                    'APPEND'  => function () use ($hookName, $eventFresh, $field, $event) {
                                        $event->storageAppend($hookName, $eventFresh->getOutput());
                                    },
                                    'CLEAR'   => function () use ($hookName, $field, $event) {
                                        $event->storageAdd($hookName, '');
                                    },
                                    'REHOOK'  => function () use ($eventFresh, $field, $event, $hookName) {
                                        if ($event->storageExists($hookName)) {
                                            $event->storageAdd($hookName, $event->storageGet($hookName));
                                        } else {
                                            $event->storageAdd($hookName, $eventFresh->getOutput());
                                        }
                                    },
                                ];

                                if (isset($typeHandlers[$field->__hookSectionType])) {
                                    $typeHandlers[$field->__hookSectionType]();
                                }

                                $hookParent->__hookSectionName = '';
                                $hookParent->__hookSectionType = '';
                            }
                            $event->removeFieldChildren($field);
                        },
                        'hookname'      => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCurrentOpenElement();
                            $hookName = trim($event->accessFieldData($field, 'hookname'));
                            $element->__hookSectionName = $hookName;
                        },
                        'hooktype'      => function ($field, OnAddFieldSelectionDropperEvent $event) {
                            $element = $event->getCurrentOpenElement();
                            $hookName = $element->__hookSectionName;
                            $hookType = strtoupper(trim($event->accessFieldData($field, 'hooktype')));
                            $typesHandler = [
                                'REPLACE' => function () use ($element, $hookName, $event) {
                                    $event->storageAdd($hookName, '');
                                },
                                'APPEND'  => function () use ($hookName, $event) {
                                    $event->storageAppend($hookName, $event->storageGet($hookName));
                                },
                                'CLEAR'   => function () use ($hookName, $event) {
                                    $event->storageRemove($hookName);
                                },
                            ];

                            if ($hookName !== '') {
                                if (isset($typesHandler[$hookType])) {
                                    $typesHandler[$hookType]($hookType);
                                }

                                $element->__hookSectionName = $hookName;
                                $element->__hookSectionType = $hookType;
                            }

                        },
                    ]);
                },
            ],
        ];
    }

    /**
     * @return array[]
     */
    public static function CSSAnimations (): array
    {
        return [
            'float-hover'         => [
                '.float-hover'       => 'transition: .5s, color .10s;-webkit-transition: .5s, color .10s;-moz-transition: .5s, color .10s;',
                '.float-hover:hover' => 'box-shadow: 0 8px 8px 0 #000000, 0 8px 8px 0 #000000;transform: translate(0px, 5px);-webkit-transform: translate(0px, 5px);-moz-transform: translate(0px, 5px);',
            ],
            'trans-left-hover'    => [
                '.trans-left-hover'       => 'transition: .5s, color .10s;-webkit-transition: .5s, color .10s;-moz-transition: .5s, color .10s;',
                '.trans-left-hover:hover' => 'transform: translatex(-20px);-webkit-transform: translatex(-20px);-moz-transform: translatex(-20px);',
            ],
            'trans-right-hover'   => [
                '.trans-right-hover'       => 'transition: .5s, color .10s;-webkit-transition: .5s, color .10s;-moz-transition: .5s, color .10s;',
                '.trans-right-hover:hover' => 'transform: translatex(20px);-webkit-transform: translatex(20px);-moz-transform: translatex(20px);',
            ],
            'trans-up-hover'      => [
                '.trans-up-hover'       => 'transition: .5s, color .10s;-webkit-transition: .5s, color .10s;-moz-transition: .5s, color .10s;',
                '.trans-up-hover:hover' => 'transform: translatey(-20px);-webkit-transform: translatey(-20px);-moz-transform: translatey(-20px);',
            ],
            'trans-down-hover'    => [
                '.trans-down-hover'       => 'transition: .5s, color .10s;-webkit-transition: .5s, color .10s;-moz-transition: .5s, color .10s;',
                '.trans-down-hover:hover' => 'transform: translatey(20px);-webkit-transform: translatey(20px);-moz-transform: translatey(20px);',
            ],
            'shake-hover'         => [
                '.shake-hover:hover' => 'animation-name: shake;-webkit-animation-name: shake;-moz-animation-name: shake;animation-duration: 1s;-webkit-animation-duration: 1s;-moz-animation-duration: 1s;animation-iteration-count: infinite;-webkit-animation-iteration-count: infinite;-moz-animation-iteration-count: infinite;',
                '@keyframes shake'   => "
0% {transform: translate(1px, 1px) rotate(0deg);}
10% {transform: translate(-1px, -2px) rotate(-1deg);}
20% {transform: translate(-3px, 0px) rotate(1deg);}
30% {transform: translate(3px, 2px) rotate(0deg);}
40% {transform: translate(1px, -1px) rotate(1deg);}
50% {transform: translate(-1px, 2px) rotate(-1deg);}
60% {transform: translate(-3px, 1px) rotate(0deg);}
70% {transform: translate(3px, 1px) rotate(-1deg);}
80% {transform: translate(-1px, -1px) rotate(1deg);}
90% {transform: translate(1px, 2px) rotate(0deg);}
100% {transform: translate(1px, -2px) rotate(-1deg);}
",
            ],
            'shake-fix-hover'     => [
                ".shake-fix-hover:hover" => "
    animation-name: shakefix;
    -webkit-animation-name: shakefix;
    -moz-animation-name: shakefix;
    animation-duration: 1s;
    -webkit-animation-duration: 1s;
    -moz-animation-duration: 1s;
    animation-iteration-count: infinite;
    -webkit-animation-iteration-count: infinite;
    -moz-animation-iteration-count: infinite;
",

                "@keyframes shakefix" => "
    0% {
        transform: translate(1px, 1px);
    }
    10% {
        transform: translate(-1px, -2px);
    }
    20% {
        transform: translate(-3px, 0px);
    }
    30% {
        transform: translate(3px, 2px);
    }
    40% {
        transform: translate(1px, -1px);
    }
    50% {
        transform: translate(-1px, 2px);
    }
    60% {
        transform: translate(-3px, 1px);
    }
    70% {
        transform: translate(3px, 1px);
    }
    80% {
        transform: translate(-1px, -1px);
    }
    90% {
        transform: translate(1px, 2px);
    }
    100% {
        transform: translate(1px, -2px);
    }
",
            ],
            'jello-hover'         => [
                '.jello-hover:hover ' => "
    animation-name: jello;
    -webkit-animation-name: jello;
    -moz-animation-name: jello;
    animation-duration: 1s;
    -webkit-animation-duration: 1s;
    -moz-animation-duration: 1s;
    transform-origin: center;
    -webkit-transform-origin: center;
    -moz-transform-origin: center;
",

                "@keyframes jello" => "
    from, 11.1%, to {
        transform: translate3d(0, 0, 0);
    }
    22.2% {
        transform: skewX(-12.5deg) skewY(-12.5deg);
    }
    33.3% {
        transform: skewX(6.25deg) skewY(6.25deg);
    }
    44.4% {
        transform: skewX(-3.125deg) skewY(-3.125deg);
    }
    55.5% {
        transform: skewX(1.5625deg) skewY(1.5625deg);
    }
    66.6% {
        transform: skewX(-0.78125deg) skewY(-0.78125deg);
    }
    77.7% {
        transform: skewX(0.390625deg) skewY(0.390625deg);
    }
    88.8% {
        transform: skewX(-0.1953125deg) skewY(-0.1953125deg);
    }
",
            ],
            'swing-hover'         => [
                '.swing-hover:hover' => '
    animation-name: swing;
    -webkit-animation-name: swing;
    -moz-animation-name: swing;
    animation-duration: 1s;
    -webkit-animation-duration: 1s;
    -moz-animation-duration: 1s;
',

                '@keyframes swing' => '
    20% {
        transform: rotate3d(0, 0, 1, 15deg);
    }
    40% {
        transform: rotate3d(0, 0, 1, -10deg);
    }
    60% {
        transform: rotate3d(0, 0, 1, 5deg);
    }
    80% {
        transform: rotate3d(0, 0, 1, -5deg);
    }
    to {
        transform: rotate3d(0, 0, 1, 0deg);
    }
',

            ],

            # SCALES
            'scale-up-center'     => [
                '.scale-up-center'                   => "webkit-animation:scale-up-center .4s cubic-bezier(.39,.575,.565,1.000) both;animation:scale-up-center .4s cubic-bezier(.39,.575,.565,1.000) both",
                '@-webkit-keyframes scale-up-center' => "0% {-webkit-transform: scale(0.5);transform: scale(0.5);}100% {-webkit-transform: scale(1);transform: scale(1);}",
            ],
            'scale-up-top'        => [
                '.scale-up-top'                   => "animation:scale-up-top .4s cubic-bezier(.39,.575,.565,1.000) both",
                '@-webkit-keyframes scale-up-top' => "0% {-webkit-transform: scale(0.5);transform: scale(0.5);-webkit-transform-origin: 50% 0;transform-origin: 50% 0;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 50% 0;transform-origin: 50% 0;}",
            ],
            'scale-up-tr'         => [
                '.scale-up-tr'                   => "animation:scale-up-tr .4s cubic-bezier(.39,.575,.565,1.000) both",
                '@-webkit-keyframes scale-up-tr' => "0% {-webkit-transform: scale(0.5);transform: scale(0.5);-webkit-transform-origin: 100% 0;transform-origin: 100% 0;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 100% 0;transform-origin: 100% 0;}",
            ],
            'scale-up-right'      => [
                '.scale-up-right'                   => "animation:scale-up-right .4s cubic-bezier(.39,.575,.565,1.000) both",
                '@-webkit-keyframes scale-up-right' => "0% {-webkit-transform: scale(0.5);transform: scale(0.5);-webkit-transform-origin: 100% 50%;transform-origin: 100% 50%;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 100% 50%;transform-origin: 100% 50%;}",
            ],
            'scale-up-br'         => [
                '.scale-up-br'                   => "animation:scale-up-br .4s cubic-bezier(.39,.575,.565,1.000) both",
                '@-webkit-keyframes scale-up-br' => "0% {-webkit-transform: scale(0.5);transform: scale(0.5);-webkit-transform-origin: 100% 100%;transform-origin: 100% 100%;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 100% 100%;transform-origin: 100% 100%;}",
            ],
            'scale-up-bottom'     => [
                '.scale-up-bottom'                   => "animation:scale-up-bottom .4s cubic-bezier(.39,.575,.565,1.000) both",
                '@-webkit-keyframes scale-up-bottom' => "0% {-webkit-transform: scale(0.5);transform: scale(0.5);-webkit-transform-origin: 50% 100%;transform-origin: 50% 100%;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 50% 100%;transform-origin: 50% 100%;}",
            ],
            'scale-up-bl'         => [
                '.scale-up-bl'                   => "animation:scale-up-bl .4s cubic-bezier(.39,.575,.565,1.000) both",
                '@-webkit-keyframes scale-up-bl' => "0% {-webkit-transform: scale(0.5);transform: scale(0.5);-webkit-transform-origin: 0 100%;transform-origin: 0 100%;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 0 100%;transform-origin: 0 100%;}",
            ],
            'scale-up-left'       => [
                '.scale-up-left'                   => "animation:scale-up-left .4s cubic-bezier(.39,.575,.565,1.000) both",
                '@-webkit-keyframes scale-up-left' => "0% {-webkit-transform: scale(0.5);transform: scale(0.5);-webkit-transform-origin: 0 50%;transform-origin: 0 50%;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 0 50%;transform-origin: 0 50%;}",
            ],
            'scale-up-tl'         => [
                '.scale-up-tl'                   => "animation:scale-up-tl .4s cubic-bezier(.39,.575,.565,1.000) both",
                '@-webkit-keyframes scale-up-tl' => "0% {-webkit-transform: scale(0.5);transform: scale(0.5);-webkit-transform-origin: 0 0;transform-origin: 0 0;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 0 0;transform-origin: 0 0;}",
            ],
            'scale-up-hor-center' => [
                '.scale-up-hor-center'                   => "animation:scale-up-hor-center .4s cubic-bezier(.39,.575,.565,1.000) both",
                '@-webkit-keyframes scale-up-hor-center' => "0% {-webkit-transform: scaleX(0.4);transform: scaleX(0.4);}100% {-webkit-transform: scaleX(1);transform: scaleX(1);}",
            ],
            'scale-up-hor-left'   => [
                '.scale-up-hor-left'                   => "animation:scale-up-hor-left .4s cubic-bezier(.39,.575,.565,1.000) both",
                '@-webkit-keyframes scale-up-hor-left' => "0% {-webkit-transform: scaleX(0.4);transform: scaleX(0.4);-webkit-transform-origin: 0 0;transform-origin: 0 0;}100% {-webkit-transform: scaleX(1);transform: scaleX(1);-webkit-transform-origin: 0 0;transform-origin: 0 0;}",
            ],
            'scale-up-hor-right'  => [
                '.scale-up-hor-right'                   => "animation:scale-up-hor-right .4s cubic-bezier(.39,.575,.565,1.000) both",
                '@-webkit-keyframes scale-up-hor-right' => "0% {-webkit-transform: scaleX(0.4);transform: scaleX(0.4);-webkit-transform-origin: 100% 100%;transform-origin: 100% 100%;}100% {-webkit-transform: scaleX(1);transform: scaleX(1);-webkit-transform-origin: 100% 100%;transform-origin: 100% 100%;}",
            ],
            'scale-up-ver-center' => [
                '.scale-up-ver-center'                   => "animation:scale-up-ver-center .4s cubic-bezier(.39,.575,.565,1.000) both",
                '@-webkit-keyframes scale-up-ver-center' => "0% {-webkit-transform: scaleY(0.4);transform: scaleY(0.4);}100% {-webkit-transform: scaleY(1);transform: scaleY(1);}",
            ],
            'scale-up-ver-top'    => [
                '.scale-up-ver-top'                   => "animation:scale-up-ver-top .4s cubic-bezier(.39,.575,.565,1.000) both",
                '@-webkit-keyframes scale-up-ver-top' => "0% {-webkit-transform: scaleY(0.4);transform: scaleY(0.4);-webkit-transform-origin: 100% 0;transform-origin: 100% 0;}100% {-webkit-transform: scaleY(1);transform: scaleY(1);-webkit-transform-origin: 100% 0;transform-origin: 100% 0;}",
            ],
            'scale-up-ver-bottom' => [
                '.scale-up-ver-bottom'                   => "animation:scale-up-ver-bottom .4s cubic-bezier(.39,.575,.565,1.000) both",
                '@-webkit-keyframes scale-up-ver-bottom' => "0% {-webkit-transform: scaleY(0.4);transform: scaleY(0.4);-webkit-transform-origin: 0 100%;transform-origin: 0 100%;}100% {-webkit-transform: scaleY(1);transform: scaleY(1);-webkit-transform-origin: 0 100%;transform-origin: 0 100%;}",
            ],
            'scale-in-center'     => [
                '.scale-in-center'                   => "animation:scale-in-center .5s cubic-bezier(.25,.46,.45,.94) both",
                '@-webkit-keyframes scale-in-center' => "0% {-webkit-transform: scale(0);transform: scale(0);opacity: 1;}100% {-webkit-transform: scale(1);transform: scale(1);opacity: 1;}",
            ],
            'scale-in-top'        => [
                '.scale-in-top'                   => "animation:scale-in-top .5s cubic-bezier(.25,.46,.45,.94) both",
                '@-webkit-keyframes scale-in-top' => "0% {-webkit-transform: scale(0);transform: scale(0);-webkit-transform-origin: 50% 0;transform-origin: 50% 0;opacity: 1;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 50% 0;transform-origin: 50% 0;opacity: 1;}",
            ],
            'scale-in-tr'         => [
                '.scale-in-tr'                   => "animation:scale-in-tr .5s cubic-bezier(.25,.46,.45,.94) both",
                '@-webkit-keyframes scale-in-tr' => "0% {-webkit-transform: scale(0);transform: scale(0);-webkit-transform-origin: 100% 0;transform-origin: 100% 0;opacity: 1;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 100% 0;transform-origin: 100% 0;opacity: 1;}",
            ],
            'scale-in-right'      => [
                '.scale-in-right'                   => "animation:scale-in-right .5s cubic-bezier(.25,.46,.45,.94) both",
                '@-webkit-keyframes scale-in-right' => "0% {-webkit-transform: scale(0);transform: scale(0);-webkit-transform-origin: 100% 50%;transform-origin: 100% 50%;opacity: 1;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 100% 50%;transform-origin: 100% 50%;opacity: 1;}",
            ],
            'scale-in-br'         => [
                '.scale-in-br'                   => "animation:scale-in-br .5s cubic-bezier(.25,.46,.45,.94) both",
                '@-webkit-keyframes scale-in-br' => "0% {-webkit-transform: scale(0);transform: scale(0);-webkit-transform-origin: 100% 100%;transform-origin: 100% 100%;opacity: 1;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 100% 100%;transform-origin: 100% 100%;opacity: 1;}",
            ],
            'scale-in-bottom'     => [
                '.scale-in-bottom'                   => "animation:scale-in-bottom .5s cubic-bezier(.25,.46,.45,.94) both",
                '@-webkit-keyframes scale-in-bottom' => "0% {-webkit-transform: scale(0);transform: scale(0);-webkit-transform-origin: 50% 100%;transform-origin: 50% 100%;opacity: 1;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 50% 100%;transform-origin: 50% 100%;opacity: 1;}",
            ],
            'scale-in-bl'         => [
                '.scale-in-bl'                   => "animation:scale-in-bl .5s cubic-bezier(.25,.46,.45,.94) both",
                '@-webkit-keyframes scale-in-bl' => "0% {-webkit-transform: scale(0);transform: scale(0);-webkit-transform-origin: 0 100%;transform-origin: 0 100%;opacity: 1;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 0 100%;transform-origin: 0 100%;opacity: 1;}",
            ],
            'scale-in-left'       => [
                '.scale-in-left'                   => "animation:scale-in-left .5s cubic-bezier(.25,.46,.45,.94) both",
                '@-webkit-keyframes scale-in-left' => "0% {-webkit-transform: scale(0);transform: scale(0);-webkit-transform-origin: 0 50%;transform-origin: 0 50%;opacity: 1;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 0 50%;transform-origin: 0 50%;opacity: 1;}",
            ],
            'scale-in-tl'         => [
                '.scale-in-tl'                   => "animation:scale-in-tl .5s cubic-bezier(.25,.46,.45,.94) both",
                '@-webkit-keyframes scale-in-tl' => "0% {-webkit-transform: scale(0);transform: scale(0);-webkit-transform-origin: 0 0;transform-origin: 0 0;opacity: 1;}100% {-webkit-transform: scale(1);transform: scale(1);-webkit-transform-origin: 0 0;transform-origin: 0 0;opacity: 1;}",
            ],
            'scale-in-hor-center' => [
                '.scale-in-hor-center'                   => "animation:scale-in-hor-center .5s cubic-bezier(.25,.46,.45,.94) both",
                '@-webkit-keyframes scale-in-hor-center' => "0% {-webkit-transform: scaleX(0);transform: scaleX(0);opacity: 1;}100% {-webkit-transform: scaleX(1);transform: scaleX(1);opacity: 1;}",
            ],
            'scale-in-hor-left'   => [
                '.scale-in-hor-left'                   => "animation:scale-in-hor-left .5s cubic-bezier(.25,.46,.45,.94) both",
                '@-webkit-keyframes scale-in-hor-left' => "0% {-webkit-transform: scaleX(0);transform: scaleX(0);-webkit-transform-origin: 0 0;transform-origin: 0 0;opacity: 1;}100% {-webkit-transform: scaleX(1);transform: scaleX(1);-webkit-transform-origin: 0 0;transform-origin: 0 0;opacity: 1;}",
            ],
            'scale-in-hor-right'  => [
                '.scale-in-hor-right'                   => "animation:scale-in-hor-right .5s cubic-bezier(.25,.46,.45,.94) both",
                '@-webkit-keyframes scale-in-hor-right' => "0% {-webkit-transform: scaleX(0);transform: scaleX(0);-webkit-transform-origin: 100% 100%;transform-origin: 100% 100%;opacity: 1;}100% {-webkit-transform: scaleX(1);transform: scaleX(1);-webkit-transform-origin: 100% 100%;transform-origin: 100% 100%;opacity: 1;}",
            ],
            'scale-in-ver-center' => [
                '.scale-in-ver-center'                   => "animation:scale-in-ver-center .5s cubic-bezier(.25,.46,.45,.94) both",
                '@-webkit-keyframes scale-in-ver-center' => "0% {-webkit-transform: scaleY(0);transform: scaleY(0);opacity: 1;}100% {-webkit-transform: scaleY(1);transform: scaleY(1);opacity: 1;}",
            ],
            'scale-in-ver-top'    => [
                '.scale-in-ver-top'                   => "animation:scale-in-ver-top .5s cubic-bezier(.25,.46,.45,.94) both",
                '@-webkit-keyframes scale-in-ver-top' => "0% {-webkit-transform: scaleY(0);transform: scaleY(0);-webkit-transform-origin: 100% 0;transform-origin: 100% 0;opacity: 1;}100% {-webkit-transform: scaleY(1);transform: scaleY(1);-webkit-transform-origin: 100% 0;transform-origin: 100% 0;opacity: 1;}",
            ],
            'scale-in-ver-bottom' => [
                '.scale-in-ver-bottom'                   => "animation:scale-in-ver-bottom .5s cubic-bezier(.25,.46,.45,.94) both",
                '@-webkit-keyframes scale-in-ver-bottom' => "0% {-webkit-transform: scaleY(0);transform: scaleY(0);-webkit-transform-origin: 0 100%;transform-origin: 0 100%;opacity: 1;}100% {-webkit-transform: scaleY(1);transform: scaleY(1);-webkit-transform-origin: 0 100%;transform-origin: 0 100%;opacity: 1;}",
            ],
        ];
    }
}