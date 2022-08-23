<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Controllers\ImportExport;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Data\ImportData;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\States\WordPressImportState;
use App\Modules\Core\Validation\Traits\Validator;
use Devsrealm\TonicsRouterSystem\RequestMethods;

class ImportController
{
    use Validator;

    private ImportData $importData;

    public function __construct(ImportData $importData)
    {
        $this->importData = $importData;
    }

    /**
     * @throws \Exception
     */
    public function index()
    {
        $importListing = $this->getImportData()->adminImportListing($this->getImportData()->getImportTypes());
        view('Modules::Core/Views/Import/index', [
            'ImportListing' => $importListing,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function wordpress()
    {
        if (key_exists(request()->getRequestMethod(), RequestMethods::$requestTypesPost)) {
            $requestBody = json_decode(request()->getEntityBody());
            $validator = $this->getValidator()->make($requestBody, $this->getWordPressImportRules());
            if ($validator->fails()) {
                helper()->onError(400, message: 'An Error Occurred Validating WordPress Import Data');
            } else {
                session()->append(Session::SessionCategories_WordPressImport, $requestBody);
                helper()->onSuccess([], 'Success');
            }
        } else {
            view('Modules::Core/Views/Import/WordPress/index');
        }
    }

    /**
     * @throws \Exception
     */
    public function wordpressEvent()
    {
        set_time_limit(0);
        helper()->addEventStreamHeader(1000000000000000);
        $startImportError = true;
        try {
            $importInfo = session()->retrieve(Session::SessionCategories_WordPressImport, jsonDecode: true);
            $validator = $this->getValidator()->make($importInfo, $this->getWordPressImportRules());
            if ($validator->fails()) {
                $startImportError = false;
            }
        } catch (\Exception) {
            $startImportError = false;
        }

        if ($startImportError === false) {
            helper()->sendMsg('WordPressEvent', 'An Error Occurred Starting WordPress Importation', 'close');
            session()->delete(Session::SessionCategories_WordPressImport);
            helper()->sendMsg('WordPressEvent', 'Closed', 'close');
        }

        $uploadZipped = (isset($importInfo->uploads_zipped)) ? $importInfo->uploads_zipped : '';
        $uploadXML = $importInfo->uploads_xml;
        $siteURL = $importInfo->site_url;

        $wordpressImportState = new WordPressImportState($uploadZipped, $uploadXML, $siteURL);
        $initState = WordPressImportState::InitialState;
        $wordpressImportState->setCurrentState($initState);
        $wordpressImportState->runStates(false);

        if ($wordpressImportState->getStateResult() === SimpleState::DONE) {
            session()->delete(Session::SessionCategories_WordPressImport);
            $adminPage = ['page' => route('posts.index')];
            helper()->sendMsg($wordpressImportState->getCurrentState(), json_encode($adminPage), 'redirect');
        }

        session()->delete(Session::SessionCategories_WordPressImport);
        helper()->sendMsg('WordPressEvent', '', 'close');
    }

    public function beatstars()
    {
        die('BeatStars Import Coming Soon');
    }

    public function airbit()
    {
        die('AirBit Import Coming Soon');
    }


    public function getWordPressImportRules(): array
    {
        return [
            'uploads_zipped' => ['string'], # optional
            'uploads_xml' => ['required', 'url'],
            'site_url' => ['required', 'string'],
        ];
    }

    /**
     * @return ImportData
     */
    public function getImportData(): ImportData
    {
        return $this->importData;
    }
}