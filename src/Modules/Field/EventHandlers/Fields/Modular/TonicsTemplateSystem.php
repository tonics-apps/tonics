<?php

namespace App\Modules\Field\EventHandlers\Fields\Modular;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TonicsTemplateSystem implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('TonicsTemplateSystem', 'Full Access To Tonics Template System',
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
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'TonicsTemplateSystem';
        $tonicsTemplateFrag =  (isset($data->tonicsTemplateFrag)) ? $data->tonicsTemplateFrag : '';
        $frag = '';
        if (isset($data->_topHTMLWrapper)) {
            $topHTMLWrapper = $data->_topHTMLWrapper;
            $slug = $data->_field->field_name ?? null;
            $frag = $topHTMLWrapper($fieldName, $slug);
        }
        $handleViewProcessingFrag = $event->handleViewProcessingFrag((isset($data->handleViewProcessing)) ? $data->handleViewProcessing : '');
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="tonicsTemplateFrag-$changeID">Template Text:
            <textarea rows="10" id="tonicsTemplateFrag-$changeID" name="tonicsTemplateFrag" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
             placeholder="Start writing the template logic, you have access to the template functions">$tonicsTemplateFrag</textarea>
    </label>
</div>

<div class="form-group">
     <label class="menu-settings-handle-name" for="handleViewProcessing-$changeID">Automatically Handle View Processing
     <select name="handleViewProcessing" class="default-selector mg-b-plus-1" id="handleViewProcessing-$changeID">
        $handleViewProcessingFrag
     </select>
    </label>
</div>
FORM;

        if (isset($data->_bottomHTMLWrapper)) {
            $frag .= $data->_bottomHTMLWrapper;
        }

        return $frag;
    }

    /**
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'TonicsTemplateSystem';
        $topHTMLWrapper = $data->_topHTMLWrapper;
        $slug = $data->field_slug;
        $form = $topHTMLWrapper($fieldName, $slug);
        if (isset($data->_bottomHTMLWrapper)) {
            $form .= $data->_bottomHTMLWrapper;
        }

        return $form;
    }

    /**
     * @throws \Exception
     */
    public function viewFrag(OnFieldMetaBox $event, $data): string
    {
        $frag = '';
        if (isset($data->handleViewProcessing) && $data->handleViewProcessing === '1'){
            $tonicsTemplateFrag =  (isset($data->tonicsTemplateFrag)) ? $data->tonicsTemplateFrag : '';
            $tonicsView = AppConfig::initLoader()->getTonicsView()->setVariableData(AppConfig::initLoader()::getGlobalVariable());
            $tonicsView->splitStringCharByChar($tonicsTemplateFrag);
            $tonicsView->reset()->tokenize();
            return $tonicsView->outputContentData($tonicsView->getContent()->getContents());
        }

        return $frag;
    }

}