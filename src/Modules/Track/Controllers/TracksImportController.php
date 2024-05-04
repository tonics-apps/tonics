<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Track\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Field\Data\FieldData;
use App\Modules\Media\FileManager\LocalDriver;
use App\Modules\Track\Data\TrackData;
use App\Modules\Track\Jobs\TrackFileImporter;
use JetBrains\PhpStorm\NoReturn;

class TracksImportController
{
    const CACHE_KEY = 'TonicsModules_TonicsTrackImportSettings';
    
    private TrackData $trackData;

    public function __construct(TrackData $trackData)
    {
        $this->trackData = $trackData;
    }
    /**
     * @throws \Exception
     */
    public function importTrackItems(): void
    {
        $fieldSettings = $this->getSettingsData();
        if (isset($fieldSettings['_fieldDetails'])){
            addToGlobalVariable('Data', $fieldSettings);
            $fieldCategories = $this->getFieldData()->compareSortAndUpdateFieldItems(json_decode($fieldSettings['_fieldDetails']));
            $htmlFrag = $this->getFieldData()->getUsersFormFrag($fieldCategories);
        } else {
            $htmlFrag = $this->getFieldData()->generateFieldWithFieldSlug(
                ['track-page-import-settings'],
                $fieldSettings
            )->getHTMLFrag();
        }

        view('Modules::Track/Views/import_index', [
            'FieldItems' => $htmlFrag,
        ]);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function importTrackItemsStore(): void
    {
        FieldConfig::savePluginFieldSettings(self::getCacheKey(), $_POST);
        $fileInfo = null;
        if (input()->fromPost()->hasValue('track_page_import_file_URL')){
            $urlUniqueID = explode('/', input()->fromPost()->retrieve('track_page_import_file_URL'));
            $urlUniqueID = end($urlUniqueID);
            $localDriver = new LocalDriver();
            $fileInfo = $localDriver->getInfoOfUniqueID($urlUniqueID);
            if ($fileInfo === null){
                session()->flash(['File Invalid, Ensure File is Json and is Locally Stored'], input()->fromPost()->all());
                redirect(route('tonicsCoupon.importCouponItems'));
            }
        }

        $trackFileImporter = new TrackFileImporter();
        $trackFileImporter->setJobName('TrackFileImporter');
        $trackFileImporter->setData(['fileInfo' => $fileInfo, 'settings' => $_POST]);
        job()->enqueue($trackFileImporter);

        session()->flash(['TrackFileImporter Job Enqueued, Check Job Lists For Progress'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('tracks.index'));
    }

    public static function getCacheKey(): string
    {
        return AppConfig::getAppCacheKey() . self::CACHE_KEY;
    }

    /**
     * @throws \Exception
     */
    public static function getSettingsData()
    {
        $settings = apcu_fetch(self::getCacheKey());
        if ($settings === false){
            $settings = FieldConfig::loadPluginSettings(self::getCacheKey());
        }

        return $settings ?? [];
    }

    /**
     * @return TrackData
     */
    public function getTrackData(): TrackData
    {
        return $this->trackData;
    }

    /**
     * @param TrackData $trackData
     */
    public function setTrackData(TrackData $trackData): void
    {
        $this->trackData = $trackData;
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->getTrackData()->getFieldData();
    }

}