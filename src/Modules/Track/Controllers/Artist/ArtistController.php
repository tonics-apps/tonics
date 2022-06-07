<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Track\Controllers\Artist;

use App\Configs\AppConfig;
use App\Library\Authentication\Session;
use App\Library\SimpleState;
use App\Modules\Track\Data\TrackData;
use App\Modules\Track\Events\OnArtistCreate;
use App\Modules\Track\Rules\TrackValidationRules;
use App\Modules\Core\Validation\Traits\Validator;
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
        $cols = '`artist_id`, `artist_name`, `artist_slug`, `artist_bio`, `image_url`';
        $data = $this->getTrackData()->generatePaginationData(
            $cols,
            'artist_name',
            $this->getTrackData()->getArtistTable());

        $artistListing = '';
        if ($data !== null){
            $artistListing = $this->getTrackData()->adminArtistListing($data->data);
            unset($data->data);
        }

        view('Modules::Track/Views/Artist/index', [
            'SiteURL' => AppConfig::getAppUrl(),
            'Data' => $data,
            'ArtistListing' => $artistListing
        ]);
    }

    /**
     * @throws \Exception
     */
    public function create()
    {
        view('Modules::Track/Views/Artist/create', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Exception
     */
    #[NoReturn] public function store()
    {

        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->artistStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all(), Session::SessionCategories_FlashMessageError);
            redirect(route('artists.create'));
        }

        try {
            $artist = $this->getTrackData()->createArtist();
            $artistReturning = db()->insertReturning($this->getTrackData()->getArtistTable(), $artist, $this->getTrackData()->getArtistColumns());

            $onArtistCreate = new OnArtistCreate($artistReturning, $this->getTrackData());
            event()->dispatch($onArtistCreate);

            session()->flash(['Artist Created'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('artists.edit', ['artist' => $onArtistCreate->getArtistSlug()]));
        } catch (\Exception){
            session()->flash(['An Error Occurred Creating Artist'], input()->fromPost()->all(), Session::SessionCategories_FlashMessageError);
            redirect(route('artists.create'));
        }
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function edit(string $slug)
    {
        $artist = $this->getTrackData()->selectWithCondition($this->getTrackData()->getArtistTable(), ['*'], "artist_slug = ?", [$slug]);
        if (!is_object($artist)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $onArtistCreate = new OnArtistCreate($artist, $this->getTrackData());
        view('Modules::Track/Views/Artist/edit', [
            'SiteURL' => AppConfig::getAppUrl(),
            'Data' => $onArtistCreate->getAllToArray(),
            'TimeZone' => AppConfig::getTimeZone()
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
            session()->flash($validator->getErrors(), type: Session::SessionCategories_FlashMessageError);
            redirect(route('artists.edit', [$slug]));
        }

        try {
            $artistToUpdate = $this->getTrackData()->createArtist();
            $artistToUpdate['artist_slug'] = helper()->slug(input()->fromPost()->retrieve('artist_slug'));
            $this->getTrackData()->updateWithCondition($artistToUpdate, ['artist_slug' => $slug], $this->getTrackData()->getArtistTable());

            $slug = $artistToUpdate['artist_slug'];
            session()->flash(['Artist Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('artists.edit', ['artist' => $slug]));
        }catch (\Exception){
            session()->flash(['An Error Occurred Updating Artist'], type: Session::SessionCategories_FlashMessageError);
            redirect(route('artists.edit', [$slug]));
        }
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function delete(string $slug)
    {
        try {
            $this->getTrackData()->deleteWithCondition(whereCondition: "artist_slug = ?", parameter: [$slug], table: $this->getTrackData()->getArtistTable());
            session()->flash(['Artist Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('artists.index'));
        } catch (\Exception $e){
            $errorCode = $e->getCode();
            switch ($errorCode){
                case "23000";
                    session()->flash(["Artist is Currently Assigned to Track(s)"], type: Session::SessionCategories_FlashMessageError);
                    break;
                default:
                    session()->flash(['Failed To Delete Artist'], type: Session::SessionCategories_FlashMessageError);
                    break;
            }
            redirect(route('artists.index'));
        }
    }

    /**
     * @throws \Exception
     */
    public function deleteMultiple()
    {
        if (!input()->fromPost()->hasValue('itemsToDelete')){
            session()->flash(['Nothing To Delete'], type: Session::SessionCategories_FlashMessageInfo);
            redirect(route('artists.index'));
        }

        $this->getTrackData()->deleteMultiple(
            $this->getTrackData()->getArtistTable(),
            array_flip($this->getTrackData()->getArtistColumns()),
            'artist_id',
            onSuccess: function (){
                session()->flash(['Artist Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
                redirect(route('artists.index'));
            },
            onError: function ($e){
                $errorCode = $e->getCode();
                switch ($errorCode){
                    case "23000";
                        session()->flash(["Artist is Currently Assigned to Track(s)"], type: Session::SessionCategories_FlashMessageError);
                        break;
                    default:
                        session()->flash(['Failed To Delete Artist'], type: Session::SessionCategories_FlashMessageError);
                        break;
                }
                redirect(route('artists.index'));
            },
        );
    }

    /**
     * @return TrackData
     */
    public function getTrackData(): TrackData
    {
        return $this->trackData;
    }

}
