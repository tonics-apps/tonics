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

namespace App\Modules\Track\Controllers\Genre;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Track\Data\TrackData;
use App\Modules\Track\Events\Genres\OnGenreCreate;
use App\Modules\Track\Events\Genres\OnGenreDelete;
use App\Modules\Track\Events\Genres\OnGenreUpdate;
use App\Modules\Track\Rules\TrackValidationRules;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JetBrains\PhpStorm\NoReturn;

class GenreController
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
        $table = Tables::getTable(Tables::GENRES);
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::GENRES . '::' . 'genre_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'genre_id'],
            ['type' => 'text', 'slug' => Tables::GENRES . '::' . 'genre_name', 'title' => 'Title', 'minmax' => '150px, 1fr', 'td' => 'genre_name'],
            ['type' => 'date_time_local', 'slug' => Tables::GENRES . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $data = null;
        db(onGetDB: function ($db) use ($table, &$data){
            $tblCol = '*, CONCAT("/admin/genres/", genre_slug, "/edit" ) as _edit_link, CONCAT("/genres/", genre_slug) as _preview_link';
            $data = $db->Select($tblCol)
                ->From($table)
                ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('genre_name', url()->getParam('query'));

                })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($table) {
                    $db->WhereBetween(table()->pickTable($table, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

                })->OrderByDesc(table()->pickTable($table, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));
        });

        view('Modules::Track/Views/Genre/index', [
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
    public function create()
    {
        view('Modules::Track/Views/Genre/create');
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Exception
     */
    #[NoReturn] public function store(): void
    {

        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->genreStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('genres.create'));
        }

        try {
            $genre = $this->getTrackData()->createGenre();
            $genre['can_delete'] = 1;
            $genreReturning = null;
            db(onGetDB: function ($db) use ($genre, &$genreReturning){
                $genreReturning = $db->insertReturning($this->getTrackData()::getGenreTable(), $genre, $this->getTrackData()->getGenreColumns(), 'genre_id');
            });

            $onGenreCreate = new OnGenreCreate($genreReturning, $this->getTrackData());
            event()->dispatch($onGenreCreate);

            session()->flash(['Genre Created'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('genres.edit', ['genre' => $onGenreCreate->getGenreSlug()]));
        } catch (\Exception){
            session()->flash(['An Error Occurred Creating Genre'], input()->fromPost()->all());
            redirect(route('genres.create'));
        }
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function edit(string $slug): void
    {
        $genre = $this->getTrackData()->selectWithCondition($this->getTrackData()::getGenreTable(), ['*'], "genre_slug = ?", [$slug]);
        if (!is_object($genre)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $onGenreCreate = new OnGenreCreate($genre, $this->getTrackData());
        view('Modules::Track/Views/Genre/edit', [
            'Data' => $onGenreCreate->getAllToArray(),
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    #[NoReturn] public function update(string $slug): void
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->genreUpdateRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors());
            redirect(route('genres.edit', [$slug]));
        }

        try {
            $genreToUpdate = $this->getTrackData()->createGenre();
            $genreToUpdate['genre_slug'] = helper()->slug(input()->fromPost()->retrieve('genre_slug'));

            db(onGetDB: function ($db) use ($slug, $genreToUpdate) {
                $db->FastUpdate($this->getTrackData()::getGenreTable(), $genreToUpdate, db()->Where('genre_slug', '=', $slug));
            });

            $slug = $genreToUpdate['genre_slug'];
            $onGenreUpdate = new OnGenreUpdate((object)$genreToUpdate, $this->getTrackData());
            event()->dispatch($onGenreUpdate);

            session()->flash(['Genre Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('genres.edit', ['genre' => $slug]));
        }catch (\Exception){
            session()->flash(['An Error Occurred Updating Genre']);
            redirect(route('genres.edit', [$slug]));
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
            'id' => 'genre_id',
            'table' => Tables::getTable(Tables::GENRES),
            'entityBag' => $entityBag,
            'rules' => $this->genreUpdateMultipleRule(),
        ]);
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws \Exception
     */
    public function deleteMultiple($entityBag): bool
    {
        $table = Tables::getTable(Tables::GENRES);
        return $this->getTrackData()->dataTableDeleteMultiple([
            'id' => 'genre_id',
            'table' => $table,
            'entityBag' => $entityBag,
            'onBeforeDelete' => function($genreIDS) use ($table) {
                $genres = null;
                db(onGetDB: function ($db) use ($table, $genreIDS, &$genres){
                    $genres = $db->Select('genre_slug, genre_name, genre_id')->From($table)
                        ->WhereIn('genre_id', $genreIDS)->FetchResult();
                });

                foreach ($genres as $genre){
                    $onGenreDelete = new OnGenreDelete($genre, $this->getTrackData());
                    event()->dispatch($onGenreDelete);
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