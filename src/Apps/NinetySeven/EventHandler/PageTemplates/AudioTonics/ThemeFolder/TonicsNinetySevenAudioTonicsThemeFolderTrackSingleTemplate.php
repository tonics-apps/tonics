<?php
/*
 *     Copyright (c) 2022-2025. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\NinetySeven\EventHandler\PageTemplates\AudioTonics\ThemeFolder;

use App\Modules\Core\Library\SimpleState;
use App\Modules\Page\Events\AbstractClasses\PageTemplateInterface;
use App\Modules\Page\Events\OnPageTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsRouterSystem\Exceptions\URLNotFound;

class TonicsNinetySevenAudioTonicsThemeFolderTrackSingleTemplate implements PageTemplateInterface, HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var OnPageTemplate $event */
        $event->addTemplate($this);
    }

    public function name(): string
    {
        return 'TonicsNinetySeven_AudioTonics_ThemeFolder_TrackSingle_Template';
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

        if ($isAPI) {
            $pageTemplate->setViewName('Apps::NinetySeven/Views/Track/AudioTonics/ThemeFolder/track_single');
            if (!$isGetMarker) {
                $fieldSettings = [...$fieldSettings, ...ThemeFolderViewHandler::handleTrackSingleFragment()];
            }

            $freeTrackDownload = url()->getHeaderByKey('type') === 'freeTrackDownload';
            if ($freeTrackDownload) {
                $data = url()->getHeaderByKey('freeTrackData');
                $trackLicense = json_decode(json_decode($data)?->dataset) ?? null;
                $licenseAttr = json_decode($fieldSettings['license_attr'] ?? []);
                $licenseAttrIDLink = json_decode($fieldSettings['license_attr_id_link'] ?? []);

                // If LicenseObjectEquality from Request is same as the one in the db and the price is 0,
                // get the artifact, else, user is trying something crazy
                if ($this->checkLicenseObjectEquality($licenseAttr, $trackLicense) && helper()->checkMoneyEquality($trackLicense->price, 0)) {
                    $uniqueID = $trackLicense->unique_id;
                    $downloadArtifact = $licenseAttrIDLink->{$uniqueID} ?? null;
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
            $track = ThemeFolderViewHandler::handleTrackSingleFragment();
            if (!isset($track['_name'])) {
                throw new URLNotFound(SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE, SimpleState::ERROR_PAGE_NOT_FOUND__CODE);
            }
            $fieldSettings = [...$fieldSettings, ...$track];
            $pageTemplate->setViewName('Apps::NinetySeven/Views/Track/AudioTonics/ThemeFolder/root');
        }
        $fieldSettings[ThemeFolderViewHandler::TonicsAudioTonicsKey] = true;
        $pageTemplate->setFieldSettings($fieldSettings);
    }

    public function checkLicenseObjectEquality($arrayOfObjects, $compareObject): bool
    {
        if (!property_exists($compareObject, 'unique_id')) {
            return false;
        }
        $result = array_filter($arrayOfObjects, function ($item) use ($compareObject) {
            return property_exists($item, 'unique_id') && $item->unique_id == $compareObject->unique_id && $item == $compareObject;
        });
        return !empty($result);
    }
}