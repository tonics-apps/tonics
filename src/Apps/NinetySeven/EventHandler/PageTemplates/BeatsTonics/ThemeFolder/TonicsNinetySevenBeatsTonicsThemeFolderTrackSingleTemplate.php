<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\EventHandler\PageTemplates\BeatsTonics\ThemeFolder;

use App\Modules\Core\Library\Tables;
use App\Modules\Page\Events\AbstractClasses\PageTemplateInterface;
use App\Modules\Page\Events\OnPageTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TonicsNinetySevenBeatsTonicsThemeFolderTrackSingleTemplate implements PageTemplateInterface, HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var OnPageTemplate $event */
        $event->addTemplate($this);
    }

    public function name(): string
    {
        return 'TonicsNinetySeven_BeatsTonics_ThemeFolder_TrackSingle_Template';
    }

    /**
     * @throws \Exception
     */
    public function handleTemplate(OnPageTemplate $pageTemplate): void
    {
        $fieldSettings = $pageTemplate->getFieldSettings();
        $fieldSettings['ThemeTrackSingle'] = true;
        $isGetMarker = url()->getHeaderByKey('type') === 'getMarker';
        $isAPI = url()->getHeaderByKey('isAPI') === 'true';

        if ($isAPI){
            $pageTemplate->setViewName('Apps::NinetySeven/Views/Track/BeatsTonics/ThemeFolder/track_single');
            if (!$isGetMarker){
                $fieldSettings = [...$fieldSettings, ...ThemeFolderViewHandler::handleTrackSingleFragment()];
            }

            $freeTrackDownload = url()->getHeaderByKey('type') === 'freeTrackDownload';
            if ($freeTrackDownload){
                $data = url()->getHeaderByKey('freeTrackData');
                $trackLicense = json_decode(json_decode($data)?->dataset) ?? null;
                $licenseAttr = json_decode($fieldSettings['license_attr'] ?? []);
                $licenseAttrIDLink = json_decode($fieldSettings['license_attr_id_link'] ?? []);

                if ($this->checkLicenseObjectEquality($licenseAttr, $trackLicense)){
                    $uniqueID = $trackLicense->unique_id; $downloadArtifact = $licenseAttrIDLink->{$uniqueID} ?? null;
                    helper()->onSuccess([
                        'artifact' => $downloadArtifact,
                    ]);
                } else {
                    // User has modified the $trackLicense from the client, we don't trust that kinda user, rogue user?
                    // let's give 'em empty data
                    helper()->onSuccess([], 'You are trying to do something fishy');
                }
                return;
            }
        } else {
            $fieldSettings = [...$fieldSettings, ...ThemeFolderViewHandler::handleTrackSingleFragment()];
            $pageTemplate->setViewName('Apps::NinetySeven/Views/Track/BeatsTonics/ThemeFolder/root');
        }
        $fieldSettings[ThemeFolderViewHandler::TonicsBeatsTonicsKey] = true;
        $pageTemplate->setFieldSettings($fieldSettings);
    }

    public function checkLicenseObjectEquality($arrayOfObjects, $compareObject): bool
    {
        if (!property_exists($compareObject, 'unique_id')) {
            return false;
        }
        $result = array_filter($arrayOfObjects, function($item) use ($compareObject) {
            return property_exists($item, 'unique_id') && $item->unique_id == $compareObject->unique_id && $item == $compareObject;
        });
        return !empty($result);
    }
}