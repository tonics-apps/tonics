<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\Controllers\Artist;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Track\Data\TrackData;
use App\Modules\Track\Events\OnArtistCreate;
use App\Modules\Track\Rules\TrackValidationRules;
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
        view('Modules::Track/Views/Artist/index');
    }

    /**
     * @throws \Exception
     */
    public function create()
    {
        view('Modules::Track/Views/Artist/create');
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Exception
     */
    #[NoReturn] public function store()
    {

        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->artistStoreRule());
        if ($validator->fails()) {
            session()->flash($validator->getErrors(), input()->fromPost()->all());
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
            session()->flash(['An Error Occurred Creating Artist'], input()->fromPost()->all());
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
            $this->getTrackData()->updateWithCondition($artistToUpdate, ['artist_slug' => $slug], $this->getTrackData()->getArtistTable());

            $slug = $artistToUpdate['artist_slug'];
            session()->flash(['Artist Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('artists.edit', ['artist' => $slug]));
        }catch (\Exception){
            session()->flash(['An Error Occurred Updating Artist']);
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
                    session()->flash(["Artist is Currently Assigned to Track(s)"]);
                    break;
                default:
                    session()->flash(['Failed To Delete Artist']);
                    break;
            }
            redirect(route('artists.index'));
        }
    }

    /**
     * @throws \Exception
     */
    public function deleteMultiple(): void
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
                        session()->flash(["Artist is Currently Assigned to Track(s)"]);
                        break;
                    default:
                        session()->flash(['Failed To Delete Artist']);
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
