<?php
/*
 *     Copyright (c) 2024-2025. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Field\Events\FieldSelectionDropper;

use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\View\CustomTokenizerState\SimpleShortCode\TonicsSimpleShortCode;
use App\Modules\Core\Services\SimpleShortCodeService;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateArrayLoader;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class OnAddFieldSelectionDropperEvent implements EventInterface
{
    const FRONTEND_LOGIC_OPEN = 'open';
    const FRONTEND_LOGIC_CLOSE = 'close';
    const FRONTEND_LOGIC_Element = 'element';

    const FIELD_HOOK_TYPE_FIELD_SELECTOR = 'FIELD_SELECTOR';
    const FIELD_HOOK_TYPE_FIELD_DATA_KEY = 'FIELD_DATA_KEY';

    # HOOK NAMES FOR THEME
    const HOOK_NAME_THEME_BOOTSTRAP = 'Theme::Bootstrap';
    const HOOK_NAME_BEFORE_HTML = 'before_html';
    const HOOK_NAME_IN_HTML_ATTRIBUTE = 'in_html_attribute';
    const HOOK_NAME_IN_HTML_LANG = 'in_html_lang';
    const HOOK_NAME_IN_HTML_CLASS_ATTRIBUTE = 'in_html_class_attribute';
    const HOOK_NAME_IN_HTML_ID_ATTRIBUTE = 'in_html_id_attribute';
    const HOOK_NAME_BEFORE_HEAD = 'before_head';
    const HOOK_NAME_IN_HEAD_ATTRIBUTE = 'in_head_attribute';
    const HOOK_NAME_BEFORE_META_TAGS = 'before_meta_tags';
    const HOOK_NAME_AFTER_META_TAGS = 'after_meta_tags';
    const HOOK_NAME_IN_HEAD = 'in_head';
    const HOOK_NAME_IN_HEAD_STYLESHEET = 'in_head_stylesheet';
    const HOOK_NAME_IN_HEAD_INLINE_STYLES = 'in_head_inline_styles';
    const HOOK_NAME_BEFORE_CLOSING_HEAD = 'before_closing_head';
    const HOOK_NAME_AFTER_HEAD = 'after_head';
    const HOOK_NAME_BEFORE_BODY = 'before_body';
    const HOOK_NAME_IN_BODY_ATTRIBUTE = 'in_body_attribute';
    const HOOK_NAME_IN_BODY_CLASS_ATTRIBUTE = 'in_body_class_attribute';
    const HOOK_NAME_IN_BODY_ID_ATTRIBUTE = 'in_body_id_attribute';
    const HOOK_NAME_BEFORE_SVG = 'before_svg';
    const HOOK_NAME_IN_SVG_DEFS = 'in_svg_defs';
    const HOOK_NAME_AFTER_SVG = 'after_svg';
    const HOOK_NAME_IN_BODY = 'in_body';
    const HOOK_NAME_BEFORE_HEADER = 'before_header';
    const HOOK_NAME_IN_HEADER_ATTRIBUTE = 'in_header_attribute';
    const HOOK_NAME_IN_HEADER_CLASS_ATTRIBUTE = 'in_header_class_attribute';
    const HOOK_NAME_IN_HEADER_ID_ATTRIBUTE = 'in_header_id_attribute';
    const HOOK_NAME_IN_HEADER = 'in_header';
    const HOOK_NAME_AFTER_HEADER = 'after_header';
    const HOOK_NAME_BEFORE_MAIN_CONTENT = 'before_main_content';
    const HOOK_NAME_IN_MAIN_ATTRIBUTE = 'in_main_attribute';
    const HOOK_NAME_IN_MAIN_CLASS_ATTRIBUTE = 'in_main_class_attribute';
    const HOOK_NAME_IN_MAIN_ID_ATTRIBUTE = 'in_main_id_attribute';
    const HOOK_NAME_IN_MAIN_CONTENT = 'in_main_content';
    const HOOK_NAME_AFTER_MAIN_CONTENT = 'after_main_content';
    const HOOK_NAME_BEFORE_CLOSING_BODY = 'before_closing_body';
    const HOOK_NAME_AFTER_BODY = 'after_body';
    const HOOK_NAME_BEFORE_FOOTER = 'before_footer';
    const HOOK_NAME_IN_FOOTER_ATTRIBUTE = 'in_footer_attribute';
    const HOOK_NAME_IN_FOOTER_CLASS_ATTRIBUTE = 'in_footer_class_attribute';
    const HOOK_NAME_IN_FOOTER_ID_ATTRIBUTE = 'in_footer_id_attribute';
    const HOOK_NAME_IN_FOOTER = 'in_footer';
    const HOOK_NAME_AFTER_FOOTER = 'after_footer';


    /**
     * In this mode, the content would be added to the layout hook name
     */
    const OUTPUT_TEMPLATE_HOOK_TYPE = 'OUTPUT_TEMPLATE_HOOK_TYPE';

    /**
     * In this mode, the content would be added to the output only
     */
    const OUTPUT_ONLY_TYPE = 'OUTPUT_ONLY_TYPE';

    const HOOK_NAMES_STORAGE_KEY = 'HOOK_NAMES_STORAGE_KEY_9876543210';
    const GLOBAL_VARIABLE_STORAGE_KEY = 'GLOBAL_VARIABLE_STORAGE_KEY_9876543210';

    private ?TonicsView $tonicsView = null;
    private array $fields = [];
    private array $inputSelects = [];
    private array $fieldsCallBack = [];
    private array $fieldsDataKeyCallBack = [];
    private array $cache = [];
    private ?\stdClass $currentOpenElement = null;
    private ?\stdClass $currentParentElement = null;
    private string $contentOutputType = self::OUTPUT_TEMPLATE_HOOK_TYPE;

    private string $output = '';
    private string $lastTemplateHook = '';
    private array $outputArray = [];
    private array $onBeforeProcessLogic = [];
    private array $onAfterProcessLogic = [];
    private array $storage = [];
    private array $outputInlineStyle = [];
    private ?\stdClass $page = null;
    private string $objectUniqueID = '';


    /**
     * @throws \Exception
     */
    public function __construct()
    {
        if (empty($this->page)) {
            $this->setPage(new \stdClass());
        }
        $this->objectUniqueID = helper()->randString(5);
    }

    /**
     * @return array
     */
    public function storages(): array
    {
        return $this->storage;
    }

    /**
     * @param array $storage
     *
     * @return $this
     */
    public function setStorage(array $storage): OnAddFieldSelectionDropperEvent
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function storageAppend(string $key, mixed $value): void
    {
        if (!$this->storageExists($key)) {
            $this->storage[$key] = '';
        }

        $this->storage[$key] .= $value;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function storageExists(string $key): bool
    {
        return array_key_exists($key, $this->storage);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function storageRemove(string $key): void
    {
        unset($this->storage[$key]);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function storageNotEmpty(string $key): bool
    {
        return $this->storageExists($key) && trim($this->storageGet($key)) !== '';
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function storageGet(string $key): mixed
    {
        if (array_key_exists($key, $this->storage)) {
            return $this->storage[$key];
        }
        return '';
    }

    public function event(): static
    {
        return $this;
    }

    /**
     * This is for hooking into selects field element.
     *
     * Hook `$fields` into fieldInput, ensure there is hookName that relates to the `$fieldHookName` when creating the field in the backend field creator.
     *
     * The `$selects` are the select you want to merge to whatever is already in the input field select
     *
     *  ```
     *  $event->addInputSelects('tonicsLayoutHookSelector', ['orange', 'blue', 'red']);
     *  ```
     *
     * @param string $fieldInputHookSelect
     * @param array $selects
     *
     * @return OnAddFieldSelectionDropperEvent
     */
    public function addInputSelects(string $fieldInputHookSelect, array $selects): OnAddFieldSelectionDropperEvent
    {
        if (!isset($this->inputSelects[$fieldInputHookSelect])) {
            $this->inputSelects[$fieldInputHookSelect] = [];
        }

        foreach ($selects as $select) {
            if (!in_array($select, $this->inputSelects[$fieldInputHookSelect])) {
                $this->inputSelects[$fieldInputHookSelect][] = $select;
            }
        }

        return $this;
    }

    /**
     * @param string $name
     * @param callable $handler
     *
     * @return void
     */
    public function onAfterProcessLogic(string $name, callable $handler): void
    {
        $this->onAfterProcessLogic[$name] = $handler;
    }

    /**
     * @param string $name
     * @param callable $handler
     *
     * @return void
     */
    public function onBeforeProcessLogic(string $name, callable $handler): void
    {
        $this->onBeforeProcessLogic[$name] = $handler;
    }

    /**
     * Alias of
     * ```
     * $this->addFields($dataName, $fields, self::FIELD_HOOK_TYPE_FIELD_DATA_KEY)
     * ```
     *
     * @param string $dataName
     * @param array $fields
     *
     * @return void
     */
    public function hookIntoFieldDataKey(string $dataName, array $fields): void
    {
        $this->addFields($dataName, $fields, self::FIELD_HOOK_TYPE_FIELD_DATA_KEY);
    }

    /**
     * Hook `$fields` into fieldInput, ensure there is hookName that relates to the `$fieldHookName` when creating the field in the backend field creator
     *
     * The `$fields` can have a callable to handle it in the frontend, the `$field` is passed for the callable, here is an example usage:
     *
     * ```
     * $event->addFields('tonicsPageBuilderFieldSelector', [
     *      'field-ul' => [
     *          'open' => function ($field) {
     *              // Handle open logic
     *              return '<ul>';
     *          },
     *          'close' => function ($field) {
     *              // Handle close logic
     *              return '</ul>';
     *          }
     *      ],
     * ]);
     * ```
     *
     *  You can exclude either of open or close if there is no point handling it, e.g, input doesn't need no close
     *    ```
     *    $event->addFields('tonicsPageBuilderFieldSelector', [
     *         'input-element' => [
     *             'open'  => fn($field) => '<input type="color">',
     *         ],
     *    ]);
     *    ```
     *
     * There are cases where you want to listen on the field_data key(s) without setting any hook, for example, to listen on the field_input_name, you do:
     *
     *  ```
     *  $event->addFields('field_input_name', [
     *       'layoutMagazineHeader' => [ // this is the input value
     *           'open'  => fn($field) => '<div style="grid-area: header;">',
     *           'close' => fn($field) => '</div>',
     *       ],
     *  ], $event::FIELD_HOOK_TYPE_FIELD_DATA_KEY);
     *  ```
     *
     * If you know you would only be using open callback, you can shorten it to:
     *
     * ```
     * $event->addFields('field_input_name', [
     *        'flex-wrap'  => fn($field, $event) => $event->autoAddSimpleStyles($field, 'flex-wrap'),
     *   ], $event::FIELD_HOOK_TYPE_FIELD_DATA_KEY);
     * ```
     *
     * Lastly, you can combine different types like so (this can help with organization):
     *
     * ```
     * $event->addFields('tonicsPageBuilderFieldSelector', [
     *      'input-element' => [
     *          'open'     => function ($field) use ($event) {
     *              $class = $event->registerAndGetClass();
     *              return "<input class='$class' ";
     *       },
     *          'children' => function () use ($event) {
     *              $event->addFields(...)
     *          },
     *      ],
     * ]);
     * ```
     *
     *
     * @param string $fieldHookName
     * @param array $fields
     * @param string $type
     *
     * @return void
     */
    public function addFields(string $fieldHookName, array $fields, string $type = self::FIELD_HOOK_TYPE_FIELD_SELECTOR): void
    {
        $fieldsKey = [];
        foreach ($fields as $fieldKey => $fieldValue) {

            if (is_string($fieldValue)) {
                $fieldsKey[] = $fieldValue;
                continue;
            }

            if (is_string($fieldKey)) {
                $fieldsKey[] = $fieldKey;

                if (is_array($fieldValue)) {

                    if (isset($fieldValue['children']) && is_callable($fieldValue['children'])) {
                        $fieldValue['children']($this);
                    }

                    if ($type === self::FIELD_HOOK_TYPE_FIELD_SELECTOR) {

                        # By Default, all field under field_selector is an element, pass false otherwise.
                        # Element that are true are the ones that their currentOpenElement be set
                        if (!isset($fieldValue[self::FRONTEND_LOGIC_Element])) {
                            $fieldValue[self::FRONTEND_LOGIC_Element] = true;
                        }

                        $this->fieldsCallBack[$fieldHookName][$fieldKey] = $fieldValue;

                        # This ensures the open element is set correctly in the case of element that doesn't have open callback
                        # checking the open callback is how we set the current open element
                        if (!isset($fieldValue[self::FRONTEND_LOGIC_OPEN])) {
                            $fieldValue[self::FRONTEND_LOGIC_OPEN] = fn() => '';
                            $this->fieldsCallBack[$fieldHookName][$fieldKey] = $fieldValue;
                        }

                    }

                    if ($type === self::FIELD_HOOK_TYPE_FIELD_DATA_KEY) {
                        $this->fieldsDataKeyCallBack[$fieldHookName][$fieldKey] = $fieldValue;
                    }
                }

                if (is_callable($fieldValue)) {
                    if ($type === self::FIELD_HOOK_TYPE_FIELD_SELECTOR) {
                        $this->fieldsCallBack[$fieldHookName][$fieldKey] = [
                            self::FRONTEND_LOGIC_OPEN => $fieldValue,
                        ];
                    }

                    if ($type === self::FIELD_HOOK_TYPE_FIELD_DATA_KEY) {
                        $this->fieldsDataKeyCallBack[$fieldHookName][$fieldKey] = [
                            self::FRONTEND_LOGIC_OPEN => $fieldValue,
                        ];
                    }
                }

            }

        }

        if ($type === self::FIELD_HOOK_TYPE_FIELD_SELECTOR) {
            if (isset($this->fields[$fieldHookName])) {
                $this->fields[$fieldHookName] = [
                    ...$this->fields[$fieldHookName], ...$fieldsKey,
                ];
            } else {
                $this->fields[$fieldHookName] = $fieldsKey;
            }
        }
    }

    /**
     * @param \stdClass $field
     * @param string $key
     *
     * @return mixed
     */
    public function accessFieldDataset(\stdClass $field, string $key): mixed
    {
        $field = $this->convertFieldPropertyToStdClass($field);
        if (!empty($field->field_data->_dataset->{$key})) {
            return $field->field_data->_dataset->{$key};
        }

        return null;
    }

    /**
     * This converts the property to stdclass if in array
     *
     * @param \stdClass $field
     * @param string $property
     *
     * @return \stdClass
     */
    public function convertFieldPropertyToStdClass(\stdClass $field, string $property = 'field_data'): \stdClass
    {
        if (is_array($field->{$property})) {
            $field->{$property} = json_decode(json_encode($field->{$property}));
        }

        return $field;
    }

    /**
     * @param \stdClass $field
     * @param string $key
     *
     * @return mixed
     */
    public function accessFieldOption(\stdClass $field, string $key): mixed
    {
        $field = $this->convertFieldPropertyToStdClass($field, 'field_options');
        if (!empty($field->field_options->{$key})) {
            return $field->field_options->{$key};
        }

        return null;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function accessPageData(string $key, mixed $default = null): mixed
    {
        if (isset($this->getPage()->{$key})) {
            return $this->getPage()->{$key};
        }

        return $default;
    }

    public function getPage(): ?\stdClass
    {
        return $this->page;
    }

    public function setPage(?\stdClass $page): OnAddFieldSelectionDropperEvent
    {
        $this->page = $page;
        return $this;
    }

    /**
     * This access the field data and escapes it
     *
     * @param \stdClass $field
     * @param string $key
     * @param string $default
     * @param bool $raw
     *
     * @return string
     * @throws \Exception
     */
    public function autoAccessFieldData(\stdClass $field, string $key, string $default = '', bool $raw = false): string
    {
        $data = $this->accessFieldData($field, $key, $default, $raw);
        return (is_string($data)) ? helper()->htmlSpecChar($data) : '';
    }

    /**
     * This will either access the field data or translate it for dynamic fieldData, if the result of the dynamic data is not a string,
     * it will return its value instead of translating it.
     *
     * @param \stdClass $field
     * @param string $key
     * @param string $default
     * @param bool $raw
     *
     * @return mixed
     */
    public function accessFieldData(\stdClass $field, string $key, string $default = '', bool $raw = false): mixed
    {
        $field = $this->convertFieldPropertyToStdClass($field);
        $loopData = $this->getCurrentLoopData($field);

        if (isset($field->field_data->{$key})) {

            $fieldData = $field->field_data->{$key};
            if ($raw) {
                return $fieldData;
            }

            if ($loopData && is_object($loopData)) {

                $placeHolders = [];
                $placeHoldersSafe = [];

                if (isset($loopData->____tonics__placeholdersSafe)) {

                    $placeHolders = $loopData->____tonics__placeholders;
                    $placeHoldersSafe = $loopData->____tonics__placeholdersSafe;

                } else {

                    foreach ($loopData as $key => $value) {

                        if (is_string($value)) {
                            $placeHoldersSafe["[[$key]]"] = $value;
                        } else {
                            $placeHolders["[[$key]]"] = $value;
                        }

                    }

                    $loopData->____tonics__placeholdersSafe = $placeHoldersSafe;
                    $loopData->____tonics__placeholders = $placeHolders;

                }

                if (is_string($fieldData)) {
                    if (isset($placeHolders[$fieldData]) && !is_string($placeHolders[$fieldData])) {
                        return $placeHolders[$fieldData];
                    }

                    $fieldData = strtr($fieldData, $placeHoldersSafe);
                }
            }

            if ($fieldData === '') {
                $fieldData = $default;
            }

            return $fieldData;
        }

        return $default;
    }

    /**
     * @param \stdClass $field
     *
     * @return mixed
     */
    public function getCurrentLoopData(\stdClass $field): mixed
    {
        $data = $field->__loop_current_data ?? null;

        if ($data === null) {

            $storageGlobal = $this->storageGet(self::GLOBAL_VARIABLE_STORAGE_KEY);

            if (is_array($storageGlobal)) {
                $storageGlobal = (object)$storageGlobal;
            }

            if (is_object($storageGlobal)) {
                return $storageGlobal;
            }

        }

        return $data;

    }

    /**
     * @param \stdClass $field
     * @param mixed $loopData
     *
     * @return void
     */
    public function addLoopDataToCache(\stdClass $field, mixed $loopData): void
    {
        if (isset($field->__LoopCacheKey)) {
            $this->cache[$field->__LoopCacheKey] = $loopData;
        }
    }

    /**
     * @param \stdClass $field
     *
     * @return string
     */
    public function getLoopDataCacheKey(\stdClass $field): string
    {
        if (isset($field->__LoopCacheKey)) {
            return $field->__LoopCacheKey;
        }
        return '';
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasCacheKey(string $key): bool
    {
        return isset($this->cache[$key]);
    }

    /**
     * @param \stdClass $field
     *
     * @return mixed
     * @throws \Exception
     */
    public function pullLoopDataFromCache(\stdClass $field): mixed
    {
        $key = $this->getLoopPullDataCacheKey($field);
        $data = helper()->accessDataWithSeparator($key, $this->cache);
        if ($data !== '') {
            return $data;
        }
        return [];
    }

    /**
     * @param \stdClass $field
     *
     * @return string
     */
    public function getLoopPullDataCacheKey(\stdClass $field): string
    {
        if (isset($field->__LoopCachePull)) {
            return $field->__LoopCachePull;
        }
        return '';
    }

    /**
     * @param \stdClass $field
     *
     * @return bool
     */
    public function canLoopCacheData(\stdClass $field): bool
    {
        $loop = false;
        if (isset($field->__LoopCacheData)) {
            $loop = (bool)$field->__LoopCacheData;
        }

        return $loop;
    }

    /**
     * @param \stdClass|null $field
     * @param array $attributes
     *
     * @return void
     */
    public function addAttributes(?\stdClass $field, array $attributes): void
    {
        if ($field === null) {
            return;
        }

        if (!isset($field->__attributes)) {
            $field->__attributes = [];
        }

        $field->__attributes = [...$field->__attributes, ...$attributes];
    }

    /**
     * @param \stdClass|null $field
     * @param string $key
     *
     * @return void
     */
    public function removeAttributes(?\stdClass $field, string $key): void
    {
        if ($field === null) {
            return;
        }

        if ($this->attributeExists($field, $key)) {
            unset($field->__attributes[$key]);
        }
    }

    /**
     * @param \stdClass|null $field
     * @param string $key
     *
     * @return bool
     */
    public function attributeExists(?\stdClass $field, string $key): bool
    {
        if ($field === null) {
            return false;
        }

        return isset($field->__attributes[$key]);
    }

    /**
     * @param \stdClass $field
     *
     * @return array
     */
    public function getAttributesToAppend(\stdClass $field): array
    {
        return $field->__attributesAppend ?? [];
    }

    /**
     * @param \stdClass|null $field
     * @param string $key
     *
     * @return string
     */
    public function getAttribute(?\stdClass $field, string $key): string
    {
        if ($field === null) {
            return '';
        }

        if ($this->attributeExists($field, $key)) {
            return $field->__attributes[$key];
        }
        return '';
    }

    /**
     * @param \stdClass $field
     *
     * @return array
     */
    public function getAttributes(\stdClass $field): array
    {
        return $field->__attributes ?? [];
    }

    /**
     * @param \stdClass $field
     *
     * @return string
     */
    public function generateUniqueClass(\stdClass $field): string
    {
        $field = $this->convertFieldPropertyToStdClass($field);
        $classUnique = null;
        if (isset($field->field_data->{$field->field_input_name})) {
            $classUnique = $field->field_data->{$field->field_input_name};
        }

        if ($classUnique === null && isset($field->field_input_name)) {
            $classUnique = $field->field_input_name;
        }

        $className = 'tonics-' . $classUnique ?? 'tonics-field-element';
        return $className . '-' . $this->objectUniqueID . '-' . $field->field_id;

    }

    /**
     * @param string $frag
     *
     * @return void
     */
    public function addChildrenFragToOpenElement(string $frag): void
    {
        if ($this->getCurrentOpenElement()) {
            $this->getCurrentOpenElement()->_childrenFrag = $frag;
        }
    }

    public function getCurrentOpenElement(): ?\stdClass
    {
        return $this->currentOpenElement;
    }

    /**
     * @param \stdClass|null $currentOpenElement
     *
     * @return $this
     */
    public function setCurrentOpenElement(?\stdClass $currentOpenElement): OnAddFieldSelectionDropperEvent
    {
        $this->currentOpenElement = $currentOpenElement;
        return $this;
    }

    /**
     * @param \stdClass|null $element
     *
     * @return string
     */
    public function getChildrenFragFromOpenElement(\stdClass $element = null): string
    {
        if ($element === null) {
            $element = $this->getCurrentOpenElement();
        }

        if (isset($element->_childrenFrag)) {
            return $element->_childrenFrag;
        }

        return '';
    }

    /**
     * @param string $frag
     *
     * @return void
     */
    public function addProcessedFragToOpenElement(string $frag): void
    {
        if ($this->getCurrentOpenElement()) {
            $this->getCurrentOpenElement()->processedFrag = $frag;
        }
    }

    /**
     * @return string
     */
    public function getProcessedFragFromOpenElement(): string
    {
        if (isset($this->getCurrentOpenElement()->processedFrag)) {
            return $this->getCurrentOpenElement()->processedFrag;
        }

        return '';
    }

    /**
     * @param \stdClass $field
     *                             The field object
     * @param string $propertyName
     *                             The property where the attribute is located
     * @param string $attributeName
     *                             Alternative attribute name, it would use this as the style attribute if not empty
     *
     * @return void
     */
    public function autoAddSimpleStyles(\stdClass $field, string $propertyName, string $attributeName = ''): void
    {
        $attribute = $propertyName;
        $field = $this->convertFieldPropertyToStdClass($field);
        if (!empty($field->field_data->{$propertyName})) {

            if (!empty($attributeName)) {
                $attribute = $attributeName;
            }

            $this->addStylesToOpenElementClass("$attribute:" . $field->field_data->{$propertyName} . ';');

        }
    }

    /**
     * The styles are what styles you want to apply to the class, and for the hookClass, by default it uses the openElement class,
     * override it by adding yours if needed.
     *
     * @param string $styles
     * @param string|null $hookClass
     *
     * @return void
     */
    public function addStylesToOpenElementClass(string $styles, string $hookClass = null): void
    {
        $openElement = $this->getCurrentOpenElement();
        if ($hookClass) {
            $class = $hookClass;
        } else {
            $class = $this->registerAndGetClass();
        }

        if ($openElement) {
            if ($hookClass === null && isset($openElement->__className)) {
                $class = $openElement->__className;
            } else {
                $openElement->__className = $class;
            }
        }

        if (!empty($class)) {
            if (isset($this->outputInlineStyle[$class])) {
                $this->outputInlineStyle[$class] .= $styles;
            } else {
                $this->outputInlineStyle["$class"] = $styles;
            }
        }
    }

    /**
     * By default, it would generate a class for you using the openElement,
     * you can override that by adding the class yourself
     *
     * @param string|null $class
     *
     * @return string
     */
    public function registerAndGetClass(string $class = null): string
    {
        $className = '';
        if ($class) {
            $className = $class;
        } else {
            $openElement = $this->getCurrentOpenElement();
            if ($openElement) {

                $classUnique = null;
                $openElement = $this->convertFieldPropertyToStdClass($openElement);
                if (isset($openElement->field_data->{$openElement->field_input_name})) {
                    $classUnique = $openElement->field_data->{$openElement->field_input_name};
                }

                if ($classUnique === null && isset($openElement->field_input_name)) {
                    $classUnique = $openElement->field_input_name;
                }

                $className = 'tonics-' . $classUnique ?? 'tonics-field-element';
                $className = $className . '-' . $this->objectUniqueID . '-' . $openElement->field_id;
            }
        }

        $className = ".$className";

        if (!isset($this->outputInlineStyle[$className])) {
            $this->outputInlineStyle[$className] = '';
        }

        return "$className";
    }

    /**
     * @param string $key
     * @param \stdClass $field
     *
     * @return bool
     */
    public function propertyExistInField(string $key, \stdClass $field): bool
    {
        return property_exists($field, $key);
    }

    /**
     * @param string $key
     * @param $default
     *
     * @return mixed|null
     */
    public function accessDataFromPage(string $key, $default = null): mixed
    {
        if (property_exists($this->getPage(), $key)) {
            return $this->getPage()->{$key};
        }

        return $default;
    }

    /**
     * @param \stdClass $field
     *                           The field in question
     * @param \stdClass|null $element
     *                           Where to attach the property
     * @param string|callable $keyValue
     *                           The key to get the property in accessFieldData, if callable, you can add the value yourself
     * @param string $newPropertyName
     *                           The new key property to be added to the $element, if this ends with [], it would assume you want an array
     * @param bool $autoEscape
     *
     * @return void
     * @throws \Exception
     */
    public function autoAddFieldPropertyToElement(\stdClass $field, ?\stdClass $element, string|callable $keyValue, string $newPropertyName, bool $autoEscape = true): void
    {

        $insert = function ($element, $newPropertyName, $value) use ($autoEscape) {
            if (is_string($value)) {
                if ($autoEscape) {
                    $value = helper()->htmlSpecChar($value);
                }
            }
            if (str_ends_with($newPropertyName, '[]')) {
                $newPropertyName = str_replace('[]', '', $newPropertyName);
                if (isset($element->{$newPropertyName})) {
                    $element->{$newPropertyName}[] = $value;
                } else {
                    $element->{$newPropertyName} = [$value];
                }
            } else {
                $element->{$newPropertyName} = $value;
            }
        };

        $value = null;
        if ($element) {
            if (is_callable($keyValue)) {
                $value = $keyValue();
            } else {
                $value = $this->accessFieldData($field, $keyValue);
            }
        }

        $insert($element, $newPropertyName, $value);
    }

    /**
     * An example usage:
     *
     * ```
     *  $event->getElementsAttribute("id='hello' class='class-one class-two' lang='en'");
     *  // output:
     *  id="hello" class="class-one class-two" lang="en"
     *  ```
     *
     * You can append to an attribute by passing what you want to append in the second param
     *
     * ```
     * $event->getElementsAttribute("id='hello' class='class-one class-two' lang='en'", ['id' => 'test-one']);
     * // output:
     * id="hello test-one" class="class-one class-two" lang="en"
     * ```
     *
     * Lastly, you can use the third paramater to replace the attribute
     *
     * ```
     *  $event->getElementsAttribute("id='hello' class='class-one class-two' lang='en'", [], ['id' => 'test-one']);
     *  // output:
     *  id="test-one" class="class-one class-two" lang="en"
     *  ```
     *
     * Note: if attributesOnly is set to true, it would return array of the attribute and its value
     *
     * @param string $content
     * @param array $append
     * @param array $replace
     * @param bool $attributesOnly
     *
     * @return string|array
     * @throws \Exception
     */
    public function getElementsAttribute(string $content, array $append = [], array $replace = [], bool $attributesOnly = false): string|array
    {
        $content = trim($content);
        $attributes = [];

        if (!empty($content)) {
            $render = SimpleShortCodeService::GlobalVariableShortCodeCustomRendererForAttributes();
            $shortCode = new TonicsSimpleShortCode([
                'render' => $render,
            ]);

            $shortCode->getView()->addModeHandler('attributes', SimpleShortCodeService::AttributesShortCode()::class);
            $shortCode->getView()->setTemplateLoader(new TonicsTemplateArrayLoader(['template' => "[attributes $content]"]))
                ->render('template');

            $attributes = $render->getArgs();

            if ($attributesOnly) {
                return $attributes;
            }
        }

        // append attributes
        foreach ($append as $key => $value) {
            if (isset($attributes[$key])) {
                $attributes[$key] .= ' ' . $value;
            } else {
                $attributes[$key] = $value;
            }
        }

        // replace attributes
        foreach ($replace as $key => $value) {
            $attributes[$key] = $value;
        }

        $htmlAttributes = '';
        foreach ($attributes as $key => $value) {
            if (!empty($value)) {
                $htmlAttributes .= $this->_escape($key) . '="' . $this->_escape($value) . '" ';
            } else {
                $htmlAttributes .= $this->_escape($key) . ' ';
            }
        }

        // Trim the trailing space and return the result
        return trim($htmlAttributes);
    }

    /**
     * @param $value
     *
     * @return string
     * @throws \Exception
     */
    public function _escape($value): string
    {
        return helper()->htmlSpecChar($value);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function exist(string $name): bool
    {
        return isset($this->fields[$name]);
    }

    public function getInputSelects(): array
    {
        return $this->inputSelects;
    }

    /**
     * @param array $inputSelects
     *
     * @return $this
     */
    public function setInputSelects(array $inputSelects): OnAddFieldSelectionDropperEvent
    {
        $this->inputSelects = $inputSelects;
        return $this;
    }

    /**
     * @param string $name
     * @param bool $fromCache
     *
     * @return array
     * @throws \Throwable
     */
    public function getInputSelectsByName(string $name, bool $fromCache = false): array
    {
        $selected = [];
        if ($fromCache) {
            $url = url()->getHeaderByKey('CURRENTURL'); # This is usually from API (fired from JS when a field is selected with selector dropper)
            $selects = apcu_fetch($url);
            if (empty($selects)) {
                $url = url()->getRequestURL();
                $selects = apcu_fetch($url);
            }

            if (!empty($selects)) {
                $selects = helper()->unCompressFieldItems($selects);
                if (isset($selects[$name])) {
                    $selected = $selects[$name];
                }
            }
        }

        if (isset($this->inputSelects[$name])) {
            $selected = $this->inputSelects[$name];
        }

        return $selected;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): OnAddFieldSelectionDropperEvent
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getFieldsByName(string $name): array
    {
        if (isset($this->fields[$name])) {
            return $this->fields[$name];
        }

        return [];
    }

    /**
     * @param \stdClass $field
     *
     * @return \Generator
     */
    public function getFieldRecursively(\stdClass $field): \Generator
    {
        yield $field;

        if (isset($field->_children)) {
            foreach ($field->_children as $child) {
                yield from $this->getFieldRecursively($child);
            }
        }
    }

    /**
     * @param \stdClass $field
     * @param callable|null $onChildField
     *
     * @return void
     */
    public function cloneFieldRecursively(\stdClass $field, callable $onChildField = null): void
    {
        if (isset($field->_children)) {

            foreach ($field->_children as $key => $child) {

                $clone = json_decode(json_encode($child));
                $field->_children[$key] = $clone;
                $field->_children[$key]->field_data = $clone->field_data;
                $field->_children[$key]->field_options = $clone->field_options;

                if ($onChildField) {
                    $onChildField($field->_children[$key]);
                }

                $this->cloneFieldRecursively($child);

            }

        }
    }

    /**
     * @param array $fieldItems
     *
     * @return void
     * @throws \Exception
     */
    public function processLogicWithEarlyAndLateCallbacks(array $fieldItems): void
    {
        $this->fireOnBeforeProcessLogic();
        $this->processLogic($fieldItems);
        $this->fireOnAfterProcessLogic();
    }

    /**
     * @return void
     */
    public function fireOnBeforeProcessLogic(): void
    {
        foreach ($this->onBeforeProcessLogic as $name => $handler) {
            $handler($this);
            unset($this->onBeforeProcessLogic[$name]);
        }
    }

    /**
     * @param array $fieldItems
     *
     * @return void
     * @throws \Exception
     */
    public function processLogic(array $fieldItems): void
    {
        $this->handleFieldLogic($fieldItems);
    }

    /**
     * @param array $fieldItems
     * @param \stdClass|null $parent
     *
     * @return void
     * @throws \Exception
     */
    private function handleFieldLogic(array $fieldItems, \stdClass $parent = null): void
    {
        $this->currentParentElement = $parent;

        foreach ($fieldItems as $fieldItem) {

            if (isset($fieldItem->field_input_name) && isset($fieldItem->main_field_slug)) {

                if (isset($fieldItem->field_data) && is_array($fieldItem->field_data)) {
                    $fieldItem->field_data = json_decode(json_encode($fieldItem->field_data));
                }

                # Give each parent child loop_current_data
                if (!isset($fieldItem->__loop_current_data) && isset($parent->__loop_current_data)) {
                    $fieldItem->__loop_current_data = $parent->__loop_current_data;
                }

                $fieldInputName = $fieldItem->field_input_name;
                $key = $fieldItem->main_field_slug;
                $hookName = $fieldItem->field_data->{'hook_name'} ?? '';

                # Once we spot templateHooks, enough, we store what we have for it in the outputArray and reset for new possible templateHooks
                if ($fieldInputName === 'templateHook' && !empty($fieldItem->field_data->{'templateHook'})) {
                    $this->lastTemplateHook = $fieldItem->field_data->{'templateHook'};
                    $this->addContentIntoHookTemplate($this->lastTemplateHook, $this->output);
                    $this->clearOutput();
                }

                # We also give the callBack the option to find it itself in the _parent if there is a need
                $fieldItem->_parentField = $parent;

                # If there is one for data_key
                $this->callableForFieldDataKey($fieldItem);

                if (!empty($hookName) && $this->fieldCallableExist($hookName, $fieldItem->field_data->{$fieldInputName})) {

                    $key = $fieldItem->field_data->{$fieldInputName};
                    $callable = $this->getFieldCallBack($hookName, $key);

                    if ($this->fieldCanBeOpenElement($hookName, $key)) {
                        $this->setCurrentOpenElement($fieldItem);
                    }

                    $this->fireCallable($callable, $fieldItem);

                }

                // Recursively process children
                $this->handleFieldLogic($this->getFieldChildren($fieldItem), $fieldItem);

                # If there is one for data_key
                $this->callableForFieldDataKey($fieldItem, self::FRONTEND_LOGIC_CLOSE);

                if (!empty($hookName) && $this->fieldCallableExist($hookName, $fieldItem->field_data->{$fieldInputName}, self::FRONTEND_LOGIC_CLOSE)) {
                    $callable = $this->getFieldCallBack($hookName, $key, self::FRONTEND_LOGIC_CLOSE);
                    $this->fireCallable($callable, $fieldItem);
                }

                if (!empty($this->lastTemplateHook)) {
                    $this->addContentIntoHookTemplate($this->lastTemplateHook, $this->output);
                    $this->clearOutput();
                }
            }

        }
    }

    /**
     * @param string $hookName
     * @param string|array|null $contents
     * @param string $lineSeparator
     *
     * @return void
     */
    public function addContentIntoHookTemplate(string $hookName, string|array|null $contents, string $lineSeparator = "\n"): void
    {
        if ($contents === null) {
            return;
        }

        if ($this->getContentOutputType() === self::OUTPUT_TEMPLATE_HOOK_TYPE) {

            $addContent = function (string $content) use ($lineSeparator, $hookName) {
                if (empty($content)) {
                    $lineSeparator = '';
                }
                if (isset($this->outputArray[$hookName])) {
                    $this->outputArray[$hookName] .= $lineSeparator . $content;
                } else {
                    $this->outputArray[$hookName] = $content;
                }
            };

            if (is_array($contents)) {
                foreach ($contents as $content) {
                    $addContent($content);
                }
            }

            if (is_string($contents)) {
                $addContent($contents);
            }

        }
    }

    /**
     * @return string
     */
    public function getContentOutputType(): string
    {
        return $this->contentOutputType;
    }

    /**
     * @param string $contentOutputType
     *
     * @return $this
     */
    public function setContentOutputType(string $contentOutputType): OnAddFieldSelectionDropperEvent
    {
        $this->contentOutputType = $contentOutputType;
        return $this;
    }

    /**
     * @return void
     */
    public function clearOutput(): void
    {
        if ($this->getContentOutputType() === self::OUTPUT_ONLY_TYPE) {
            return;
        }

        $this->output = '';
    }

    /**
     * @param $fieldItem
     * @param string $logic
     *
     * @return void
     */
    private function callableForFieldDataKey($fieldItem, string $logic = self::FRONTEND_LOGIC_OPEN): void
    {
        $fieldData = null;
        if (isset($fieldItem->field_data)) {
            $fieldData = is_string($fieldItem->field_data) ? json_decode($fieldItem->field_data) : $fieldItem->field_data;
            $fieldData = is_array($fieldData) ? json_decode(json_encode($fieldData)) : $fieldData;
            $fieldItem->field_data = $fieldData;
        }

        if ($fieldData === null && isset($fieldItem->field_options)) {
            $fieldData = is_string($fieldItem->field_options) ? json_decode($fieldItem->field_options) : $fieldItem->field_options;
            $fieldData = is_array($fieldData) ? json_decode(json_encode($fieldData)) : $fieldData;
            $fieldItem->field_options = $fieldData;
            $fieldItem->field_data = $fieldItem->field_options;
        }

        if (is_object($fieldData)) {
            foreach ($fieldData as $fieldItemDataKey => $fieldItemData) {

                if (!is_string($fieldItemData)) {
                    continue;
                }

                if ($this->fieldDataKeyCallableExist($fieldItemDataKey, $fieldItemData, $logic)) {
                    $callable = $this->getFieldDataKeyCallBack($fieldItemDataKey, $fieldItemData, $logic);
                    $this->fireCallable($callable, $fieldItem);
                    break;
                }

            }
        }
    }

    /**
     * @param string $inputName
     * @param string $name
     * @param string $logic
     *
     * @return bool
     */
    public function fieldDataKeyCallableExist(string $inputName, string $name, string $logic = self::FRONTEND_LOGIC_OPEN): bool
    {
        return isset($this->fieldsDataKeyCallBack[$inputName][$name][$logic]);
    }

    /**
     * @param string $inputName
     * @param string $name
     * @param string $logic
     *
     * @return callable|null
     */
    public function getFieldDataKeyCallBack(string $inputName, string $name, string $logic = self::FRONTEND_LOGIC_OPEN): ?callable
    {
        if ($this->fieldDataKeyCallableExist($inputName, $name, $logic)) {
            return $this->fieldsDataKeyCallBack[$inputName][$name][$logic];
        }

        return null;
    }

    /**
     * @param callable $callable
     * @param $fieldItem
     *
     * @return void
     */
    private function fireCallable(callable $callable, $fieldItem): void
    {
        $skip = function ($fieldItem) {
            if ($this->skip($fieldItem)) {
                $this->removeFieldChildren($fieldItem);
                return true;
            }
            return false;
        };

        if ($skip($fieldItem)) {
            return;
        }

        $this->addContentToOutput($callable($fieldItem, $this));
    }

    /**
     * @param \stdClass $fieldItem
     *
     * @return bool
     */
    public function skip(\stdClass $fieldItem): bool
    {
        return isset($fieldItem->__skip);
    }

    /**
     * @param \stdClass $fieldItem
     *
     * @return OnAddFieldSelectionDropperEvent
     */
    public function removeFieldChildren(\stdClass $fieldItem): OnAddFieldSelectionDropperEvent
    {
        if (isset($fieldItem->_children)) {
            $fieldItem->_children = null;
            unset($fieldItem->_children);
        }

        return $this;
    }

    /**
     * @param string|null $content
     *
     * @return void
     */
    public function addContentToOutput(string $content = null): void
    {
        if (is_string($content)) {
            $this->output .= $content;
        }
    }

    /**
     * @param string $inputName
     * @param string $name
     * @param string $logic
     *
     * @return bool
     */
    public function fieldCallableExist(string $inputName, string $name, string $logic = self::FRONTEND_LOGIC_OPEN): bool
    {
        return isset($this->fieldsCallBack[$inputName][$name][$logic]);
    }

    /**
     * @param string $inputName
     * @param string $name
     * @param string $logic
     *
     * @return callable|null
     */
    public function getFieldCallBack(string $inputName, string $name, string $logic = self::FRONTEND_LOGIC_OPEN): ?callable
    {
        if ($this->fieldCallableExist($inputName, $name, $logic)) {
            return $this->fieldsCallBack[$inputName][$name][$logic];
        }

        return null;
    }

    /**
     * @param string $inputName
     * @param string $name
     *
     * @return bool
     */
    public function fieldCanBeOpenElement(string $inputName, string $name): bool
    {
        if ($this->fieldCallableExist($inputName, $name, self::FRONTEND_LOGIC_Element)) {
            return $this->fieldsCallBack[$inputName][$name][self::FRONTEND_LOGIC_Element];
        }

        return false;
    }

    /**
     * @param \stdClass $fieldItem
     *
     * @return array
     */
    public function getFieldChildren(\stdClass $fieldItem): array
    {
        if (isset($fieldItem->_children) && is_iterable($fieldItem->_children)) {
            return $fieldItem->_children;
        }

        return [];
    }

    /**
     * @return void
     */
    public function fireOnAfterProcessLogic(): void
    {
        foreach ($this->onAfterProcessLogic as $name => $handler) {
            $handler($this);
            unset($this->onAfterProcessLogic[$name]);
        }
    }

    /**
     * @param array $fieldItems
     * @param callable|null $onEvent
     * @param string $outputType
     *
     * @return OnAddFieldSelectionDropperEvent
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function processLogicFresh(array $fieldItems, callable $onEvent = null, string $outputType = self::OUTPUT_TEMPLATE_HOOK_TYPE): OnAddFieldSelectionDropperEvent
    {
        $ev = FieldConfig::getFieldSelectionDropper();
        $ev->setContentOutputType($outputType)
            ->setOutputInlineStyle($this->getOutputInlineStyle())
            ->storageAdd(self::GLOBAL_VARIABLE_STORAGE_KEY, $this->storageGet(self::GLOBAL_VARIABLE_STORAGE_KEY));

        if ($onEvent) {
            $onEvent($ev);
        }

        $ev->processLogic($fieldItems);

        return $ev;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function storageAdd(string $key, mixed $value): void
    {
        if (is_array($value)) {

            if (!isset($this->storage[$key])) {
                $this->storage[$key] = [];
            }

            foreach ($value as $item) {
                if (!in_array($item, $this->storage[$key])) {
                    $this->storage[$key][] = $item;
                }
            }

        } else {

            $this->storage[$key] = $value;

        }

    }

    public function getOutputInlineStyle(): array
    {
        return $this->outputInlineStyle;
    }

    public function setOutputInlineStyle(array $outputInlineStyle): OnAddFieldSelectionDropperEvent
    {
        $this->outputInlineStyle = $outputInlineStyle;
        return $this;
    }

    /**
     * @param \stdClass $field
     * @param string $defaultTagName
     * @param array $attributes
     *
     * @return string
     */
    public function autoOpenFieldLayoutSelector(\stdClass $field, string $defaultTagName = 'div', array $attributes = []): string
    {
        # Just in case
        $oldOpenElement = $this->getCurrentOpenElement();
        $this->autoOpenField($field, $defaultTagName, $attributes);
        $children = $this->getElementFieldSettingsChildren($field);

        if (!empty($children)) {
            $this->setCurrentOpenElement($field);
            $this->processChildrenOnlyLogic($children, $this->getElementFieldSettings($field));
        }

        # Restore old element
        $this->setCurrentOpenElement($oldOpenElement);
        return '';
    }

    /**
     * @param \stdClass $field
     * @param string $defaultTagName
     * @param array $attributes
     *
     * @return string
     */
    public function autoOpenField(\stdClass $field, string $defaultTagName = 'div', array $attributes = []): string
    {
        $this->appendAttributes($field, $attributes);
        $this->addDefaultTag($field, $defaultTagName);
        return '';
    }

    /**
     * @param \stdClass $field
     * @param array $attributesToAppend
     *
     * @return void
     */
    public function appendAttributes(\stdClass $field, array $attributesToAppend): void
    {
        if (!isset($field->__attributesAppend)) {
            $field->__attributesAppend = [];
        }

        foreach ($attributesToAppend as $key => $attr) {
            if (isset($field->__attributesAppend[$key])) {
                $field->__attributesAppend[$key] .= " " . $attr;
            } else {
                $field->__attributesAppend[$key] = $attr;
            }
        }
    }

    /**
     * @param \stdClass $field
     * @param string $tagName
     *
     * @return void
     */
    public function addDefaultTag(\stdClass $field, string $tagName): void
    {
        $tagName = trim($tagName);
        $field->__defaultTagType = $tagName;
    }

    /**
     * @param \stdClass $fieldItem
     *
     * @return array
     */
    public function getElementFieldSettingsChildren(\stdClass $fieldItem): array
    {
        $elementSettings = $this->getElementFieldSettings($fieldItem);
        if ($elementSettings && !empty($elementSettings->_children)) {
            return $elementSettings->_children;
        }

        return [];
    }

    /**
     * @param \stdClass $fieldItem
     *
     * @return \stdClass|null
     */
    public function getElementFieldSettings(\stdClass $fieldItem): ?\stdClass
    {
        # if fieldItem is settings itself
        if (isset($fieldItem->field_input_name) && $fieldItem->field_input_name === 'tonicsPageBuilderSettings') {
            return $fieldItem;
        }

        # This when the field is from input_name (typically when hooked to hookIntoFieldDataKey)
        if (isset($fieldItem->_children[1]->field_input_name) && $fieldItem->_children[1]->field_input_name === 'tonicsPageBuilderSettings') {
            return $fieldItem->_children[1] ?? null;
        }

        # This works for field element
        # if settings is from fieldSelection, we use that, otherwise, we grab it from the children path
        $settings = $fieldItem->_children[0]->_children[1] ?? null;
        if (isset($settings->field_name) && $settings->field_name === 'modular_fieldselection') {
            return $settings->_children[0] ?? null;
        }

        return $fieldItem->_children[0]->_children[1] ?? null;
    }

    /**
     * @param array $fieldItems The children
     * @param \stdClass|null $settingsField Pass this so the processed children can be removed
     *
     * @return void
     * @throws \Exception
     */
    public function processChildrenOnlyLogic(array $fieldItems, \stdClass $settingsField = null): void
    {
        $this->processLogic($fieldItems);
        if ($settingsField) {
            $this->removeFieldChildren($settingsField);
        }
    }

    /**
     * @param \stdClass $field
     *
     * @return string
     * @throws \Exception
     */
    public function autoCloseField(\stdClass $field): string
    {
        return $this->getClosingTag($this->getDefaultTag($field));
    }

    /**
     * @param string $elementTagName
     *
     * @return string
     * @throws \Exception
     */
    public function getClosingTag(string $elementTagName): string
    {
        return helper()->getHTMLClosingTag($elementTagName);
    }

    /**
     * @param \stdClass $field
     *
     * @return string
     */
    public function getDefaultTag(\stdClass $field): string
    {
        return $field->__defaultTagType ?? '';
    }

    /**
     * @return array
     */
    public function getProcessedLogicData(): array
    {
        return $this->outputArray;
    }

    /**
     * @param array $pages
     * @param string|null $templateHook
     *
     * @return void
     * @throws \Exception
     */
    public function handlePageInheritance(array $pages): void
    {
        $oldTemplateHook = $this->lastTemplateHook;

        $layoutSelectors = FieldConfig::LayoutSelectorsForPages($pages);
        $this->processLogic($layoutSelectors);

        # Restore Template Hook
        $this->lastTemplateHook = $oldTemplateHook;
    }

    /**
     * @param \stdClass $field
     *
     * @return OnAddFieldSelectionDropperEvent
     */
    public function addSkip(\stdClass $field): OnAddFieldSelectionDropperEvent
    {
        $field->__skip = true;
        return $this;
    }

    /**
     * @param $fieldItem
     *
     * @return \stdClass|null
     */
    public function getElementField($fieldItem): ?\stdClass
    {
        return $fieldItem->_children[0]->_children[0] ?? null;
    }

    /**
     * @param \stdClass|null $fieldItem
     *
     * @return \stdClass|null
     */
    public function getRootOpenElement(?\stdClass $fieldItem): ?\stdClass
    {
        return $fieldItem->__open_element ?? null;
    }

    /**
     * This lets you use a new openElement without disrupting the tree process,
     * meaning, it is going to keep the old one and when you are done, it restores it.
     *
     * @param callable $callable
     * @param \stdClass|null $newOpenElement
     *
     * @return void
     */
    public function newCurrentOpenElementTemporary(callable $callable, \stdClass $newOpenElement = null): void
    {
        $oldCOE = $this->getCOE();

        if ($callable) {

            if ($newOpenElement) {
                $this->currentOpenElement = $newOpenElement;
            }

            $callable($this);

        }

        $this->currentOpenElement = $oldCOE;
    }

    /**
     * Alias for `$this->>getCurrentOpenElement()`
     * @return \stdClass|null
     */
    public function getCOE(): ?\stdClass
    {
        return $this->getCurrentOpenElement();
    }

    /**
     * @param string $propertyOrClass
     * @param string $value
     *
     * @return $this
     */
    public function addInlineStyle(string $propertyOrClass, string $value): OnAddFieldSelectionDropperEvent
    {
        $this->outputInlineStyle[$propertyOrClass] = $value;
        return $this;
    }

    /**
     * @param string $oldClassName
     * @param string $newClassName
     *
     * @return void
     */
    public function updateInlineStyleClassName(string $oldClassName, string $newClassName): void
    {
        foreach ($this->outputInlineStyle as $className => $style) {
            if (str_starts_with($className, $oldClassName)) {
                $newClassNameUpdated = str_replace($oldClassName, $newClassName, $className);
                $this->outputInlineStyle[$newClassNameUpdated] = $style;
                unset($this->outputInlineStyle[$className]);
            }
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function processInlineStyles(): string
    {
        $styles = '';
        foreach ($this->outputInlineStyle as $class => $style) {
            $class = helper()->htmlSpecChar($class);
            $style = strip_tags($style);
            $styles .= "$class" . '{' . "$style" . '}' . "\n";
        }
        return $styles;
    }

    /**
     * @param bool $clone
     *
     * @return array
     */
    public function getFieldsCallBack(): array
    {
        return $this->fieldsCallBack;
    }

    public function setFieldsCallBack(array $fieldsCallBack): OnAddFieldSelectionDropperEvent
    {
        $this->fieldsCallBack = $fieldsCallBack;
        return $this;
    }

    /**
     * @param bool $clone
     *
     * @return array
     */
    public function getFieldsDataKeyCallBack(): array
    {
        return $this->fieldsDataKeyCallBack;
    }

    public function setFieldsDataKeyCallBack(array $fieldsDataKeyCallBack): OnAddFieldSelectionDropperEvent
    {
        $this->fieldsDataKeyCallBack = $fieldsDataKeyCallBack;
        return $this;
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    public function setOutput(string $output): void
    {
        $this->output = $output;
    }

    /**
     * @return TonicsView|null
     */
    public function getTonicsView(): ?TonicsView
    {
        return $this->tonicsView;
    }

    /**
     * @param TonicsView|null $tonicsView
     *
     * @return OnAddFieldSelectionDropperEvent
     */
    public function setTonicsView(?TonicsView $tonicsView): OnAddFieldSelectionDropperEvent
    {
        $this->tonicsView = $tonicsView;
        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function stringIsNotEmpty(mixed $value): bool
    {
        return !$this->stringEmpty($value);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function stringEmpty(mixed $value): bool
    {
        return trim($value ?? '') === "";
    }

    public function getCache(): array
    {
        return $this->cache;
    }

    /**
     * @param array $cache
     *
     * @return $this
     */
    public function setCache(array $cache): OnAddFieldSelectionDropperEvent
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastTemplateHook(): string
    {
        return $this->lastTemplateHook;
    }

    /**
     * @param string $lastTemplateHook
     *
     * @return $this
     */
    public function setLastTemplateHook(string $lastTemplateHook): OnAddFieldSelectionDropperEvent
    {
        $this->lastTemplateHook = $lastTemplateHook;
        return $this;
    }
}