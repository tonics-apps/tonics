<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\EventHandlers\Fields;

use App\Apps\TonicsCloud\Controllers\ContainerController;
use App\Apps\TonicsCloud\Controllers\ImageController;
use App\Apps\TonicsCloud\Controllers\InstanceController;
use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\Interfaces\CloudServerInterface;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Linode\Entity\Image;

class CloudContainerImages implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldMetaBox */
        $event->addFieldBox('CloudContainerImages', 'All Cloud Container Images', 'TonicsCloud',
            settingsForm: function ($data) use ($event) {
                return $this->settingsForm($event, $data);
            },
            userForm: function ($data) use ($event) {
                return $this->userForm($event, $data);
            },
            handleViewProcessing: function () {
            }
        );
    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function settingsForm(OnFieldMetaBox $event, $data = null): string
    {
        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Cloud Container Images';
        $inputName = (isset($data->inputName)) ? $data->inputName : '';
        $changeID = isset($data->_field) ? helper()->randString(10) : 'CHANGEID';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $frag .= <<<FORM
<div class="form-group d:flex flex-gap align-items:flex-end">
     <label class="menu-settings-handle-name" for="fieldName-$changeID">Field Name
            <input id="fieldName-$changeID" name="fieldName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$fieldName" placeholder="Field Name">
    </label>
    <label class="menu-settings-handle-name" for="inputName-$changeID">Input Name
            <input id="inputName-$changeID" name="inputName" type="text" class="menu-name color:black border-width:default border:black placeholder-color:gray"
            value="$inputName" placeholder="(Optional) Input Name">
    </label>
</div>
FORM;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;

    }

    /**
     * @param OnFieldMetaBox $event
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function userForm(OnFieldMetaBox $event, $data): string
    {

        $fieldName = (isset($data->fieldName)) ? $data->fieldName : 'Container Images';
        $frag = $event->_topHTMLWrapper($fieldName, $data);
        $changeID = (isset($data->field_slug_unique_hash)) ? $data->field_slug_unique_hash : 'CHANGEID';
        $slug = $data->field_slug;
        $inputName = (isset($data->inputName)) ? $data->inputName : "{$slug}_$changeID";

        $tableFrag = <<<FRAG
<style>
input[type='radio'] {
    accent-color: #000000;
}
</style>
<section class="dataTable disable-select owl" data-event-click="true" data-event-dblclick="true" data-event-scroll-bottom="true">
    <table id="dt" style="grid-template-columns: minmax(150px, 1fr) minmax(100px, .7fr) minmax(300px, 1.6fr);">
            <thead>
            <tr>
                    <th title="Name" data-title="Name" data-slug="name">Name</th>
                    <th title="Version" data-title="Version" data-slug="version">Version</th>
                    <th title="Description" data-title="Description" data-slug="description">Description</th>
            </tr>
            </thead>
            <tbody class="max-height:300px overflow-x:auto">
FRAG;
        $trFrag = '';

        $editPage = ContainerController::getCurrentControllerMethod() === ContainerController::EDIT_METHOD;

        $selectedContainerImage = $event->getKeyValueInData($data, $data->inputName);
        $emptyImage = (object)['container_image_id' => '', 'container_image_name' => 'Empty Image', 'container_image_description' => 'Empty Image'];
        if ($editPage === false){
            $containerImages = ImageController::getImages();
            if (empty($containerImages)){
                return '';
            }
            array_unshift($containerImages, $emptyImage);
        } else {
            $containerImage = ImageController::getImageData($selectedContainerImage);
            if (empty($containerImage)){
                $containerImage = $emptyImage;
            }
        }

        if ($editPage){
            $imageVersion = $event->getKeyValueInData($data, 'image_version');
            $trFrag .=<<<Frag
        <tr class="">
             <td tabindex="-1" style="opacity: 50%;">
                <label aria-label="$containerImage->container_image_name" class="d:flex flex-gap align-items:center">
                    <div aria-label="Selected Image" style="padding: 5px; border: 2px solid black;"><span>Selected Image</span></div>
                    {$this->getLogoFrag($containerImage)}
                    $containerImage->container_image_name
                </label>
            </td>
            <td tabindex="-1" style="opacity: 50%;">$imageVersion</td>
            <td tabindex="-1" style="opacity: 50%;">$containerImage->container_image_description</td>
        </tr>
Frag;
        } else {
            foreach ($containerImages as $containerImage) {
                $selected = '';
                if ($containerImage->container_image_id == $selectedContainerImage){
                    $selected = 'checked';
                }

                $trFrag .=<<<Frag
        <tr class="">
            <td tabindex="-1">
                <label aria-label="$containerImage->container_image_name" class="d:flex flex-gap align-items:center">
                    <input type="radio" $selected name="$inputName" role="radio" value="$containerImage->container_image_id">
                    {$this->getLogoFrag($containerImage)}
                    $containerImage->container_image_name
                </label>
            </td>
            <td tabindex="-1">
                <select class=" mg-b-plus-1" name="{$containerImage->container_image_id}_image_version">
                 <option value="">Choose Version</option>
                  {$this->getImageVersions($containerImage)}
                </select>
            </td>
            <td tabindex="-1">$containerImage->container_image_description</td>
        </tr>
Frag;
            }
        }

        $frag .= $tableFrag . $trFrag;
        $frag .= <<<CLOSE
            </tbody>
    </table>
</section>
CLOSE;

        $frag .= $event->_bottomHTMLWrapper();
        return $frag;
    }

    /**
     * @param $containerImage
     * @return string
     * @throws \Exception
     */
    private function getLogoFrag($containerImage): string
    {
        if (empty($containerImage->container_image_logo)){
            $logo = "/logo/o-ola-micky-logo.svg";
        } else {
            $logo = $containerImage->container_image_logo;
        }

        $logo = helper()->htmlSpecChar($logo);
        return <<<LOGO
<div>
    <img src="$logo" style="width: 50px; height: 50px; border: 1px solid rgb(0 0 0 / 9%); padding: 3px;" alt="$containerImage->container_image_name" title="$containerImage->container_image_name">
</div>
LOGO;
    }

    /**
     * @param $containerImage
     * @return string
     * @throws \Exception
     */
    private function getImageVersions($containerImage): string
    {
        $versions = '';
        if (isset($containerImage->others) && helper()->isJSON($containerImage->others)){
            $containerImage->others = json_decode($containerImage->others);
            if (isset($containerImage->others->images)){
                $imageVersions = array_keys((array)$containerImage->others->images);
                foreach ($imageVersions as $imageVersion){
                    $versions .=<<<VER
<option title="$imageVersion" value="$imageVersion">$imageVersion</option>
VER;
                }
            }
        }

        return $versions;
    }

}