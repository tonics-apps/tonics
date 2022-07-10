<?php

namespace App\Modules\Field\EventHandlers\Fields\Modular;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\Content;

class TonicsTemplateSystem implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('TonicsTemplateSystem', 'Full Access To Tonics Template System',
            'Modular',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'TonicsTemplateSystem';
        $tonicsTemplateFrag = (isset($data->tonicsTemplateFrag)) ? $data->tonicsTemplateFrag : '';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="tonicsTemplateFrag-$changeID">Template Text: (You can only use hooks, any other thing is discarded)
            <textarea rows="10" id="tonicsTemplateFrag-$changeID" name="tonicsTemplateFrag" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
             placeholder="Start writing the template logic, you have access to the template functions">$tonicsTemplateFrag</textarea>
    </label>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'TonicsTemplateSystem';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $frag .= $event->_bottomHTMLWrapper(true);
        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        $data->templateEngine = 'Native';
        $event->handleTemplateEngineView($data);
        return '';
    }

}