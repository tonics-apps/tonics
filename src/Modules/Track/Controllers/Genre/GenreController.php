<?php

namespace App\Modules\Track\Controllers\Genre;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Track\Data\TrackData;
use App\Modules\Track\Events\OnGenreCreate;
use App\Modules\Track\Rules\TrackValidationRules;
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
        view('Modules::Track/Views/Genre/index', [
            'SiteURL' => AppConfig::getAppUrl(),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function create()
    {
        view('Modules::Track/Views/Genre/create', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone()
        ]);
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
            $genreReturning = db()->insertReturning($this->getTrackData()->getGenreTable(), $genre, $this->getTrackData()->getGenreColumns());

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
        $genre = $this->getTrackData()->selectWithCondition($this->getTrackData()->getGenreTable(), ['*'], "genre_slug = ?", [$slug]);
        if (!is_object($genre)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $onGenreCreate = new OnGenreCreate($genre, $this->getTrackData());
        view('Modules::Track/Views/Genre/edit', [
            'SiteURL' => AppConfig::getAppUrl(),
            'Data' => $onGenreCreate->getAllToArray(),
            'TimeZone' => AppConfig::getTimeZone()
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
            $genreToUpdate = $this->getTrackData()->createArtist();
            $genreToUpdate['genre_slug'] = helper()->slug(input()->fromPost()->retrieve('genre_slug'));
            $this->getTrackData()->updateWithCondition($genreToUpdate, ['genre_slug' => $slug], $this->getTrackData()->getGenreTable());

            $slug = $genreToUpdate['genre_slug'];
            session()->flash(['Genre Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('genres.edit', ['genre' => $slug]));
        }catch (\Exception){
            session()->flash(['An Error Occurred Updating Genre']);
            redirect(route('genres.edit', [$slug]));
        }
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function delete(string $slug): void
    {
        try {
            $genre = $this->getTrackData()->selectWithCondition($this->getTrackData()->getGenreTable(), ['*'], "genre_slug = ?", [$slug]);
            if (isset($genre->can_delete) && $genre->can_delete === 0){
                session()->flash(["You Can't Delete a Default Genre"]);
                redirect(route('genres.index'));
            }

            $this->getTrackData()->deleteWithCondition(whereCondition: "genre_slug = ?", parameter: [$slug], table: $this->getTrackData()->getGenreTable());
            session()->flash(['Genre Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('genres.index'));
        } catch (\Exception){
            session()->flash(['Failed To Delete Genre']);
            redirect(route('genres.index'));
        }
    }

    /**
     * @return TrackData
     */
    public function getTrackData(): TrackData
    {
        return $this->trackData;
    }
}