<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Track\Controllers\Artist;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Track\Data\TrackData;
use App\Modules\Track\Events\Artist\OnArtistCreate;
use App\Modules\Track\Events\Artist\OnArtistDelete;
use App\Modules\Track\Events\Artist\OnArtistUpdate;
use App\Modules\Track\Rules\TrackValidationRules;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JetBrains\PhpStorm\NoReturn;

class ArtistController
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
        $table = Tables::getTable(Tables::ARTISTS);
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::ARTISTS . '::' . 'artist_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'artist_id'],
            ['type' => 'text', 'slug' => Tables::ARTISTS . '::' . 'artist_name', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'artist_name'],
            ['type' => 'date_time_local', 'slug' => Tables::ARTISTS . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $data = null;
        db(onGetDB: function ($db) use ($table, &$data){
            $tblCol = '*, CONCAT("/admin/artists/", artist_slug, "/edit" ) as _edit_link, CONCAT("/artists/", artist_slug) as _preview_link';
            $data = $db->Select($tblCol)
                ->From($table)
                ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('artist_name', url()->getParam('query'));

                })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($table) {
                    $db->WhereBetween(table()->pickTable($table, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

                })->OrderByDesc(table()->pickTable($table, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        });

        view('Modules::Track/Views/Artist/index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $data ?? [],
                'dataTableType' => 'EDITABLE_PREVIEW',

            ],
            'SiteURL' => AppConfig::getAppUrl(),
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
    public function create(): void
    {
        view('Modules::Track/Views/Artist/create');
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Exception
     */
    #[NoReturn] public function store(): void
    {

        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->artistStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('artists.create'));
        }

        try {
            $artist = $this->getTrackData()->createArtist();
            $artistReturning = null;
            db(onGetDB: function ($db) use ($artist, &$artistReturning){
                $artistReturning = $db->insertReturning($this->getTrackData()::getArtistTable(), $artist, $this->getTrackData()->getArtistColumns(), 'artist_id');
            });

            $onArtistCreate = new OnArtistCreate($artistReturning, $this->getTrackData());
            event()->dispatch($onArtistCreate);

            session()->flash(['Artist Created'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('artists.edit', ['artist' => $onArtistCreate->getArtistSlug()]));
        } catch (\Exception){
            session()->flash(['An Error Occurred Creating Artist'], input()->fromPost()->all());
            redirect(route('artists.create'));
        }
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function edit(string $slug): void
    {
        $artist = $this->getTrackData()->selectWithCondition($this->getTrackData()::getArtistTable(), ['*'], "artist_slug = ?", [$slug]);
        if (!is_object($artist)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $onArtistCreate = new OnArtistCreate($artist, $this->getTrackData());
        view('Modules::Track/Views/Artist/edit', [
            'Data' => $onArtistCreate->getAllToArray(),
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    #[NoReturn] public function update(string $slug)
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->artistUpdateRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors());
            redirect(route('artists.edit', [$slug]));
        }

        try {
            $artistToUpdate = $this->getTrackData()->createArtist();
            $artistToUpdate['artist_slug'] = helper()->slug(input()->fromPost()->retrieve('artist_slug'));

            db(onGetDB: function ($db) use ($slug, $artistToUpdate){
                $db->FastUpdate($this->getTrackData()::getArtistTable(), $artistToUpdate, db()->Where('artist_slug', '=', $slug));
            });

            $slug = $artistToUpdate['artist_slug'];
            $onArtistUpdate = new OnArtistUpdate((object)$artistToUpdate, $this->getTrackData());
            event()->dispatch($onArtistUpdate);

            session()->flash(['Artist Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('artists.edit', ['artist' => $slug]));
        }catch (\Exception){
            session()->flash(['An Error Occurred Updating Artist']);
            redirect(route('artists.edit', [$slug]));
        }
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws \Exception
     */
    protected function updateMultiple($entityBag): bool
    {
        return $this->getTrackData()->dataTableUpdateMultiple([
            'id' => 'artist_id',
            'table' => Tables::getTable(Tables::ARTISTS),
            'rules' => $this->artistUpdateMultipleRule(),
            'entityBag' => $entityBag,
        ]);
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws \Exception
     */
    public function deleteMultiple($entityBag): bool
    {
        $table = Tables::getTable(Tables::ARTISTS);
        return $this->getTrackData()->dataTableDeleteMultiple([
            'id' => 'artist_id',
            'table' => $table,
            'entityBag' => $entityBag,
            'onBeforeDelete' => function($artistIDS) use ($table) {
                $artists = null;
                db(onGetDB: function ($db) use ($table, $artistIDS, &$artists){
                    $artists = $db->Select('artist_slug, artist_name, artist_id')->From($table)
                        ->WhereIn('artist_id', $artistIDS)->FetchResult();
                });

                foreach ($artists as $artist){
                    $onArtistDelete = new OnArtistDelete($artist, $this->getTrackData());
                    event()->dispatch($onArtistDelete);
                }
            }
        ]);
    }


    /**
     * @return TrackData
     */
    public function getTrackData(): TrackData
    {
        return $this->trackData;
    }

}
