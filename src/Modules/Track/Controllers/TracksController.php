<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Controllers\Controller;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use App\Modules\Track\Data\TrackData;
use App\Modules\Track\Events\OnTrackCreate;
use App\Modules\Track\Events\OnTrackDefaultField;
use App\Modules\Track\Helper\TrackRedirection;
use App\Modules\Track\Rules\TrackValidationRules;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Exceptions\URLNotFound;
use Exception;
use JetBrains\PhpStorm\NoReturn;

class TracksController extends Controller
{
    use Validator, TrackValidationRules;

    private TrackData $trackData;

    public function __construct(TrackData $trackData)
    {
        $this->trackData = $trackData;
    }

    /**
     * @throws \Exception
     */
    public function index()
    {
        $genreURLParam = url()->getParam('genre');
        $genres = $this->getTrackData()->getGenrePaginationData();
        # For Genre Meta Box API
        $this->getTrackData()->genreMetaBox($genres, 'genre[]', 'checkbox', $genreURLParam);

        $trackTbl = Tables::getTable(Tables::TRACKS);
        $genreTbl = Tables::getTable(Tables::GENRES);

        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::TRACKS . '::' . 'track_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'track_id'],
            ['type' => 'text', 'slug' => Tables::TRACKS . '::' . 'track_title', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'track_title'],
            ['type' => 'date_time_local', 'slug' => Tables::TRACKS . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $tblCol = table()->pick([$trackTbl => ['track_id', 'track_title', 'track_slug', 'field_settings', 'updated_at']])
            . ', CONCAT("/admin/tracks/", track_slug, "/edit") as _edit_link, CONCAT_WS("/", "/tracks", track_slug) as _preview_link ';


        $data = db()->Select($tblCol)
            ->From($trackTbl)
            ->Join($genreTbl, table()->pickTable($genreTbl, ['genre_id']), table()->pickTable($trackTbl, ['fk_genre_id']))
            ->when(url()->hasParamAndValue('status'),
                function (TonicsQuery $db) {
                    $db->WhereEquals('track_status', url()->getParam('status'));
                },
                function (TonicsQuery $db) {
                    $db->WhereEquals('track_status', 1);

                })->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                $db->WhereLike('track_title', url()->getParam('query'));

            })->when(url()->hasParamAndValue('genre'), function (TonicsQuery $db) use ($genreURLParam) {
                $db->WhereIn('genre_id', $genreURLParam);

            })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($trackTbl) {
                $db->WhereBetween(table()->pickTable($trackTbl, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

            })->OrderByDesc(table()->pickTable($trackTbl, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        $genreSettings = ['genres' => $genres, 'selected' => $genreURLParam, 'type' => 'checkbox', 'inputName' => 'genre[]'];
        view('Modules::Track/Views/index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $data ?? [],
                'dataTableType' => 'EDITABLE_PREVIEW',

            ],
            'SiteURL' => AppConfig::getAppUrl(),
            'DefaultGenresMetaBox' => $this->getTrackData()->genreListing($genreSettings),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function dataTable(): void
    {
        $entityBag = null;
        if ($this->getTrackData()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deleted", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getTrackData()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->updateMultiple($entityBag)) {
                response()->onSuccess([], "Records Updated", more: AbstractDataLayer::DataTableEventTypeUpdate);
            } else {
                response()->onError(500);
            }
        }
    }


    /**
     * @throws \Exception
     */
    public function create()
    {
        $genre = $this->getTrackData()->getGenrePaginationData();

        ## FOR GENRE API META-BOX
        $this->getTrackData()->genreMetaBox($genre);

        ## FOR LICENSE API META-BOX
        $this->getTrackData()->licenseMetaBox();

        event()->dispatch($this->getTrackData()->getOnTrackDefaultField());

        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Modules::Track/Views/create', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $this->getFieldData()->generateFieldWithFieldSlug($this->getOnTrackDefaultField()->getFieldSlug(), $oldFormInput)->getHTMLFrag(),
        ]);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function store()
    {

        if (input()->fromPost()->hasValue('created_at') === false){
            $_POST['created_at'] = helper()->date();
        }
        if (input()->fromPost()->hasValue('track_slug') === false){
            $_POST['track_slug'] = helper()->slug(input()->fromPost()->retrieve('track_title'));
        }

        # Meaning The Unique_id is a link to the url_download
        $urlDownloadCombine = [];
        if (input()->fromPost()->hasValue('url_download') && input()->fromPost()->hasValue('unique_id')){
            $urlDownloadCombine = array_combine(input()->fromPost()->retrieve('unique_id'), input()->fromPost()->retrieve('url_download'));
        }

        $_POST['license_attr_id_link'] = json_encode($urlDownloadCombine);
        $getValidator = $this->getValidator();
        $getValidator->changeErrorMessage([
            "fk_license_id:required" => "Track License Is Empty",
            "fk_artist_id:required" => "Track Artist Is Empty",
            "fk_genre_id:required" => "Track Genre Is Empty",
        ]);
        $validator = $getValidator->make(input()->fromPost()->all(), $this->trackStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('tracks.create'));
        }
        dd($_POST, $urlDownloadCombine);

        # Storing db reference is the only way I got tx to work
        # this could be as a result of pass db() around in event handlers
        $db = db();
        try {
            $db->beginTransaction();
            $track = $this->getTrackData()->createTrack(['token']);
            $trackReturning = db()->insertReturning($this->getTrackData()->getTrackTable(), $track, $this->getTrackData()->getTrackColumns(), 'track_id');
            $onTrackCreate = new OnTrackCreate($trackReturning, $this->getTrackData());
            event()->dispatch($onTrackCreate);
            $db->commit();

            session()->flash(['Track Created'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('tracks.edit', ['track' => $onTrackCreate->getTrackSlug()]));
        } catch (Exception $exception){
            // Log..
            $db->rollBack();
            session()->flash(['An Error Occurred, Creating Track'], input()->fromPost()->all());
            redirect(route('tracks.create'));
        }
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function edit(string $slug)
    {
        $track = $this->getTrackData()->selectWithConditionFromTrack($this->getTrackData()->getTrackColumnsForAdminCreate(), 'track_slug = ?', [$slug]);
        if (!is_object($track)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $onTrackCreate = new OnTrackCreate($track, $this->getTrackData());

        $genre = $this->getTrackData()->getGenrePaginationData();
        ## FOR GENRE API META-BOX
        $this->getTrackData()->genreMetaBox($genre);

        ## FOR LICENSE API META-BOX
        $this->getTrackData()->licenseMetaBox($onTrackCreate);

        $fieldSettings = json_decode($track->field_settings, true);
        if (empty($fieldSettings)){
            $fieldSettings = (array)$track;
        } else {
            $fieldSettings = [...$fieldSettings, ...(array)$track];
        }

        event()->dispatch($this->getOnTrackDefaultField());
        if (isset($fieldSettings['_fieldDetails'])){
            addToGlobalVariable('Data', $fieldSettings);
            $fieldCategories = $this->getFieldData()->compareSortAndUpdateFieldItems(json_decode($fieldSettings['_fieldDetails']));
            $htmlFrag = $this->getFieldData()->getUsersFormFrag($fieldCategories);
        } else {
            $htmlFrag = $this->getFieldData()->generateFieldWithFieldSlug($this->getOnTrackDefaultField()->getFieldSlug(), $fieldSettings)->getHTMLFrag();
            addToGlobalVariable('Data', $onTrackCreate->getAllToArray());
        }

        view('Modules::Track/Views/edit', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $htmlFrag
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws Exception
     */
    #[NoReturn] public function update(string $slug)
    {
        # Meaning The Unique_id is a link to the url_download
        $urlDownloadCombine = array_combine(input()->fromPost()->retrieve('unique_id'), input()->fromPost()->retrieve('url_download'));
        $_POST['license_attr_id_link'] = json_encode($urlDownloadCombine);
        $getValidator = $this->getValidator();
        $getValidator->changeErrorMessage([
            "fk_license_id:required" => "Track License Is Empty",
            "fk_artist_id:required" => "Track Artist Is Empty",
            "fk_genre_id:required" => "Track Genre Is Empty",
        ]);
        $validator = $getValidator->make(input()->fromPost()->all(), $this->trackUpdateRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('tracks.edit', [$slug]));
        }

        try {
            $track = $this->getTrackData()->createTrack(['token']);
            $track['track_slug'] = helper()->slug(input()->fromPost()->retrieve('track_slug'));
            db()->FastUpdate($this->getTrackData()->getTrackTable(), $track, db()->Where('track_slug', '=', $slug));
        } catch (Exception){
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('tracks.edit', [$slug]));
        }

        $slug = $track['track_slug'];
        if (input()->fromPost()->has('_fieldErrorEmitted') === true){
            session()->flash(['Track Updated But Some Field Inputs Are Incorrect'], input()->fromPost()->all(), type: Session::SessionCategories_FlashMessageInfo);
        } else {
            session()->flash(['Track Updated'], type: Session::SessionCategories_FlashMessageSuccess);
        }

        apcu_clear_cache();
        redirect(route('tracks.edit', ['track' => $slug]));
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws Exception
     */
    protected function updateMultiple($entityBag): bool
    {
        return $this->getTrackData()->dataTableUpdateMultiple('track_id', Tables::getTable(Tables::TRACKS), $entityBag, $this->trackUpdateMultipleRule());
    }

    /**
     * @param $entityBag
     * @return bool
     */
    public function deleteMultiple($entityBag): bool
    {
        return $this->getTrackData()->dataTableDeleteMultiple('track_id', Tables::getTable(Tables::TRACKS), $entityBag);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function redirect($id){

        $trackRedirection = new TrackRedirection($this->getTrackData());
        $trackRedirection->setCurrentState(TrackRedirection::OnTrackInitialState);
        $trackRedirection->setRequest(request());

        $trackRedirection->runStates(false);
        if ($trackRedirection->getStateResult() === SimpleState::DONE){
            redirect($trackRedirection->getIntendedTrackURL());
        }

        throw new URLNotFound(SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE, SimpleState::ERROR_PAGE_NOT_FOUND__CODE);
    }


    /**
     * @return TrackData
     */
    public function getTrackData(): TrackData
    {
        return $this->trackData;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->getTrackData()->getFieldData();
    }

    /**
     * @return OnTrackDefaultField|null
     */
    public function getOnTrackDefaultField(): ?OnTrackDefaultField
    {
        return  $this->getTrackData()->getOnTrackDefaultField();
    }

}