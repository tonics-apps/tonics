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
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\States\CommonResourceRedirection;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use App\Modules\Post\Helper\PostRedirection;
use App\Modules\Track\Data\TrackData;
use App\Modules\Track\Events\OnTrackCreate;
use App\Modules\Track\Events\OnTrackDefaultField;
use App\Modules\Track\Events\OnTrackUpdate;
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
    private bool $isUserInCLI = false;

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

        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::TRACKS . '::' . 'track_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'track_id'],
            ['type' => 'text', 'slug' => Tables::TRACKS . '::' . 'track_title', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'track_title'],
            ['type' => 'date_time_local', 'slug' => Tables::TRACKS . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $data = null;
        db(onGetDB: function ($db) use ($genreURLParam, &$data){
            $trackTable = Tables::getTable(Tables::TRACKS);
            $tblCol = table()->pick([$trackTable => ['track_id', 'track_title', 'track_slug', 'updated_at']])
                . ', CONCAT("/admin/tracks/", track_slug, "/edit") as _edit_link, CONCAT_WS("/", "/tracks", track_slug) as _preview_link ';

            $data = $db->Select($tblCol)
                ->From($trackTable)
                // we only join the table when we have query, that is user is filtering...
                ->when(url()->hasParam('query'), function (TonicsQuery $query){
                    $trackTable = Tables::getTable(Tables::TRACKS);
                    $trackCategoriesTable = Tables::getTable(Tables::TRACK_CATEGORIES);
                    $trackTracksCategoriesTable = Tables::getTable(Tables::TRACK_TRACK_CATEGORIES);
                    $genreTable = Tables::getTable(Tables::GENRES);
                    $trackGenreTable = Tables::getTable(Tables::TRACK_GENRES);

                    $query
                        ->Join($trackGenreTable, "$trackGenreTable.fk_track_id", "$trackTable.track_id")
                        ->Join($genreTable, "$genreTable.genre_id", "$trackGenreTable.fk_genre_id")
                        ->Join($trackTracksCategoriesTable, "$trackTracksCategoriesTable.fk_track_id", "$trackTable.track_id")
                        ->Join($trackCategoriesTable, "$trackCategoriesTable.track_cat_id", "$trackTracksCategoriesTable.fk_track_cat_id");
                })
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

                })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($trackTable) {
                    $db->WhereBetween(table()->pickTable($trackTable, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));
                })
                ->GroupBy("$trackTable.track_id")
                ->OrderByDesc(table()->pickTable($trackTable, ['updated_at']))
                ->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));
        });

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
    public function store()
    {
        if (input()->fromPost()->hasValue('created_at') === false){
            $_POST['created_at'] = helper()->date();
        }
        if (input()->fromPost()->hasValue('track_slug') === false){
            $_POST['track_slug'] = helper()->slug(input()->fromPost()->retrieve('track_title'));
        }

        $this->getTrackData()->setDefaultTrackCategoryIfNotSet();
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
            if (!$this->isUserInCLI){
                session()->flash($validator->getErrors(), input()->fromPost()->all());
                redirect(route('tracks.create'));
            }

            throw new \Exception($validator->errorsAsString());
        }

        # Storing db reference is the only way I got tx to work
        # this could be as a result of pass db() around in event handlers
        $dbTx = db();
        try {
            $dbTx->beginTransaction();
            $track = $this->getTrackData()->createTrack(['token']);
            $trackReturning = null;
            db(onGetDB: function ($db) use ($track, &$trackReturning){
                $trackReturning = $db->insertReturning($this->getTrackData()::getTrackTable(), $track, $this->getTrackData()->getTrackColumns(), 'track_id');
            });

            if (is_object($trackReturning)) {
                $trackReturning->fk_track_cat_id = input()->fromPost()->retrieve('fk_track_cat_id', '');
                $trackReturning->fk_genre_id = input()->fromPost()->retrieve('fk_genre_id', '');
            }
            $onTrackCreate = new OnTrackCreate($trackReturning, $this->getTrackData());
            event()->dispatch($onTrackCreate);
            $dbTx->commit();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
            if (!$this->isUserInCLI){
                session()->flash(['Track Created'], type: Session::SessionCategories_FlashMessageSuccess);
                redirect(route('tracks.edit', ['track' => $onTrackCreate->getTrackSlug()]));
            }
        } catch (Exception $exception){
            // Log..
            $dbTx->rollBack();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
            if (!$this->isUserInCLI){
                session()->flash(['An Error Occurred, Creating Track'], input()->fromPost()->all());
                redirect(route('tracks.create'));
            }
            # Rethrow in CLI
            throw $exception;
        }
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function edit(string $slug)
    {
        $track = null;
        db(onGetDB: function ($db) use ($slug, &$track){
            $trackData = TrackData::class;
            $select = "{$trackData::getTrackTable()}.*, {$trackData::getLicenseTable()}.*,
       GROUP_CONCAT(DISTINCT {$trackData::getGenreTable()}.genre_id) AS `fk_genre_id[]`,
       GROUP_CONCAT(DISTINCT {$trackData::getTrackTracksCategoryTable()}.fk_track_cat_id) AS fk_track_cat_id";

            $track = $db->Select($select)->From($trackData::getTrackTable())
                ->Join($trackData::getTrackToGenreTable(), "{$trackData::getTrackToGenreTable()}.fk_track_id", "{$trackData::getTrackTable()}.track_id")
                ->Join($trackData::getGenreTable(), "{$trackData::getGenreTable()}.genre_id", "{$trackData::getTrackToGenreTable()}.fk_genre_id")
                ->Join($trackData::getTrackTracksCategoryTable(), "{$trackData::getTrackTracksCategoryTable()}.fk_track_id", "{$trackData::getTrackTable()}.track_id")
                ->Join($trackData::getTrackCategoryTable(), "{$trackData::getTrackCategoryTable()}.track_cat_id", "{$trackData::getTrackTracksCategoryTable()}.fk_track_cat_id")
                ->Join($trackData::getLicenseTable(), "{$trackData::getLicenseTable()}.license_id", "{$trackData::getTrackTable()}.fk_license_id")
                ->WhereEquals("{$trackData::getTrackTable()}.track_slug", $slug)
                ->GroupBy("{$trackData::getTrackTable()}.track_id")
                ->FetchFirst();
        });

        if (!is_object($track)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        if (isset($track->{'fk_genre_id[]'})) {
            $track->{'fk_genre_id[]'} = explode(',', $track->{'fk_genre_id[]'});
        }

        if (isset($track->fk_track_cat_id)){
            $track->fk_track_cat_id = explode(',', $track->fk_track_cat_id);
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
    public function update(string $slug)
    {
        $this->getTrackData()->setDefaultTrackCategoryIfNotSet();

        # Meaning The Unique_id is a link to the url_download
        $uniqueID = input()->fromPost()->retrieve('unique_id');
        $urlDownload = input()->fromPost()->retrieve('url_download');

        if (!is_array($uniqueID)){
            $uniqueID = [];
        }

        if (!is_array($urlDownload)){
            $urlDownload = [];
        }

        $urlDownloadCombine = array_combine($uniqueID, $urlDownload);
        $_POST['license_attr_id_link'] = json_encode($urlDownloadCombine);
        $getValidator = $this->getValidator();
        $getValidator->changeErrorMessage([
            "fk_license_id:required" => "Track License Is Empty",
            "fk_artist_id:required" => "Track Artist Is Empty",
            "fk_genre_id:required" => "Track Genre Is Empty",
        ]);
        $validator = $getValidator->make(input()->fromPost()->all(), $this->trackUpdateRule());
        if ($validator->fails()) {
            if (!$this->isUserInCLI){
                session()->flash($validator->getErrors(), input()->fromPost()->all());
                redirect(route('tracks.edit', [$slug]));
            }

            throw new \Exception($validator->errorsAsString());
        }

        $dbTx = db();
        $dbTx->beginTransaction();
        $trackToUpdate = $this->getTrackData()->createTrack(['token']);
        try {
            $trackToUpdate['track_slug'] = helper()->slug(input()->fromPost()->retrieve('track_slug'));
            db(onGetDB: function ($db) use ($slug, $trackToUpdate) {
                $db->FastUpdate($this->getTrackData()::getTrackTable(), $trackToUpdate, db()->Where('track_slug', '=', $slug));
            });

            $trackToUpdate['fk_track_cat_id'] = input()->fromPost()->retrieve('fk_track_cat_id', '');
            $trackToUpdate['track_id'] = input()->fromPost()->retrieve('track_id', '');
            $trackToUpdate['fk_genre_id'] = input()->fromPost()->retrieve('fk_genre_id', '');
            $onTrackToUpdate = new OnTrackUpdate((object)$trackToUpdate, $this->getTrackData());
            event()->dispatch($onTrackToUpdate);

            $dbTx->commit();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();

            $slug = $trackToUpdate['track_slug'];
            if (!$this->isUserInCLI){
                if (input()->fromPost()->has('_fieldErrorEmitted') === true){
                    session()->flash(['Track Updated But Some Field Inputs Are Incorrect'], input()->fromPost()->all(), type: Session::SessionCategories_FlashMessageInfo);
                } else {
                    session()->flash(['Track Updated'], type: Session::SessionCategories_FlashMessageSuccess);
                }
                apcu_clear_cache();
                redirect(route('tracks.edit', ['track' => $slug]));
            }
        } catch (\Exception $exception){
            // Log..
            $dbTx->rollBack();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
            if (!$this->isUserInCLI){
                session()->flash($validator->getErrors(), input()->fromPost()->all());
                redirect(route('tracks.edit', [$slug]));
            }
            # Rethrow in CLI
            throw $exception;
        }
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws Exception
     */
    protected function updateMultiple($entityBag): bool
    {
        return $this->getTrackData()->dataTableUpdateMultiple([
            'id' => 'track_id',
            'table' => Tables::getTable(Tables::TRACKS),
            'rules' => $this->trackUpdateMultipleRule(),
            'entityBag' => $entityBag,
        ]);
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws Exception
     */
    public function deleteMultiple($entityBag): bool
    {
        return $this->getTrackData()->dataTableDeleteMultiple([
            'id' => 'track_id',
            'table' => Tables::getTable(Tables::TRACKS),
            'entityBag' => $entityBag,
        ]);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function redirect($id){

        $redirection = new CommonResourceRedirection(
            onSlugIDState: function ($slugID) {
                $track = null;
                db(onGetDB: function ($db) use ($slugID, &$track){
                    $track = $db->Select('*')->From(Tables::getTable(Tables::TRACKS))
                        ->WhereEquals('slug_id', $slugID)->FetchFirst();
                });
                if (isset($track->slug_id) && isset($track->track_slug)) {
                    return TrackRedirection::getTrackAbsoluteURLPath((array)$track);
                }
                return false;
            }, onSlugState: function ($slug) {
            $track = null;
            db(onGetDB: function ($db) use ($slug, &$track){
                $track = $db->Select('*')->From(Tables::getTable(Tables::TRACKS))
                    ->WhereEquals('track_slug', $slug)->FetchFirst();
            });

            if (isset($track->slug_id) && isset($track->track_slug)) {
                return TrackRedirection::getTrackAbsoluteURLPath((array)$track);
            }
            return false;
        });

        $redirection->runStates();
    }

    const SessionCategories_TrackPlays = 'tonics_track_plays_info';

    /**
     * @return void
     * @throws Exception
     */
    public function updateTrackPlays(): void
    {
        $trackToUpdate = [];
        try {
            $incrementPlay = false;
            $requestBody = json_decode(request()->getEntityBody(), true);
            $slug = (isset($requestBody['slug_id'])) ? $requestBody['slug_id'] : null;
            # limit the char to 16 since that is the definition of slug_id
            $slug = substr($slug, 0, 16);
            $trackToUpdate['slug_id'] = $slug;
            $key = self::SessionCategories_TrackPlays . '.' . $slug;

            if (\session()->hasKey($key)){
                $trackPlaysInfo = \session()->retrieve($key, jsonDecode: true);

                if (is_string($trackPlaysInfo->expire_lock_time)){
                    $trackPlaysInfo->expire_lock_time = strtotime($trackPlaysInfo->expire_lock_time);
                }

                // reset if the wait time has elapsed
                if ($trackPlaysInfo->expire_lock_time < time()) {
                    $incrementPlay = true;
                    $trackPlaysInfo->expire_lock_time = time() + Scheduler::everyHour(1);
                }
            } else {
                $incrementPlay = true; # First Time
                $trackPlaysInfo = (object) [
                    'expire_lock_time' => time() + Scheduler::everyHour(1),
                    'slug_id' => $slug,
                ];
            }

            session()->append($key, $trackPlaysInfo);

            if ($incrementPlay && isset($trackPlaysInfo->slug_id)) {
                db(onGetDB: function (TonicsQuery $db) use ($slug, &$trackToUpdate) {
                    $table = $this->getTrackData()::getTrackTable();
                    $track = $db->Select('track_plays, track_title')->From($table)
                        ->WhereEquals('slug_id', $slug)->FetchFirst();

                    if (isset($track->track_title)){
                        $trackToUpdate['track_plays'] = $track->track_plays + 1;
                        $db->FastUpdate($table, $trackToUpdate, db()->Where('slug_id', '=', $slug));
                    }

                });

                response()->onSuccess($trackToUpdate);
            }

        } catch (Exception $exception){
            // Log..
        }

        response()->onSuccess($trackToUpdate);
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

    /**
     * @return bool
     */
    public function isUserInCLI(): bool
    {
        return $this->isUserInCLI;
    }

    /**
     * @param bool $isUserInCLI
     */
    public function setIsUserInCLI(bool $isUserInCLI): void
    {
        $this->isUserInCLI = $isUserInCLI;
    }

}