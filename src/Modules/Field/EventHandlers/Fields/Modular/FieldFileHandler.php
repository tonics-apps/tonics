<?php

namespace App\Modules\Field\EventHandlers\Fields\Modular;

use App\Modules\Field\Events\FieldTemplateFile;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class FieldFileHandler implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('FieldFileHandler', 'Handle the Logic In a PHP File',
            'Modular',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event){
                return $this->userForm($event, $data);
            },
            handleViewProcessing: function ($data) use ($event) {
                return $this->viewFrag($event, $data);
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'FieldFileHandler';
        $templateFile =  (isset($data->templateFile)) ? $data->templateFile : '';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';

        $handlers = [];
        if (isset(event()->getHandler()->getEventQueueHandlers()[FieldTemplateFile::class])){
            $handlers = event()->getHandler()->getEventQueueHandlers()[FieldTemplateFile::class];
        }
        $handlers = (!is_array($handlers)) ? []: $handlers;

        $handlersFrag = '';
        foreach ($handlers as $handler){
            if ($handler instanceof FieldTemplateFileInterface){
                $class = $handler::class;
                $handlerSelected = ($templateFile === $class) ? 'selected': '';
                $handlersFrag .=<<<HTML
<option value="$class" $handlerSelected>{$handler->name()}</option>
HTML;
            }
        }

        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="templateFile-$changeID">Choose Template File (`User Edit Form` Should be False in Content Editing Context)
     <select name="templateFile" class="default-selector mg-b-plus-1" id="templateFile-$changeID">
        $handlersFrag
     </select>
    </label>
</div>
{$event->generateMoreSettingsFrag($data)}
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'FieldFileHandler';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $frag .= $event->_bottomHTMLWrapper(true);
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        $templateFile =  (isset($data->templateFile)) ? $data->templateFile : '';

        $handlers = [];
        if (isset(event()->getHandler()->getEventQueueHandlers()[FieldTemplateFile::class])){
            $handlers = event()->getHandler()->getEventQueueHandlers()[FieldTemplateFile::class];
        }
        $handlers = (!is_array($handlers)) ? []: $handlers;
        $valid = false;
        foreach ($handlers as $handler){
            if (is_string($templateFile) && $templateFile === $handler::class){
                $templateFile = $handler;
                $valid = true;
                break;
            }
        }

        if ($valid && $templateFile instanceof FieldTemplateFileInterface){
            return $templateFile->handleFieldLogic($event, $data);
        }

        return '';
    }
}