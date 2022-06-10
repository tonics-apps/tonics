<?php

namespace App\Modules\Track\Helper;

use App\Modules\Core\Library\SimpleState;
use App\Modules\Track\Data\TrackData;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;

class TrackRedirection extends SimpleState
{
    private OnRequestProcess $request;
    private string $intendedTrackURL = '';
    private string $trackID = '';
    private TrackData $trackData;

    # States For TrackRedirection
    const OnTrackInitialState = 'OnTrackInitialState';
    const OnTrackNumericIDState = 'OnTrackNumericIDState';
    const OnTrackStringIDState = 'OnTrackStringIDState';

    /**
     * @throws \Exception
     */
    public function __construct(TrackData $trackData){
        $this->trackData = $trackData;
        $this->request = request();
    }

    public function OnTrackInitialState(): string
    {
        $trackID = $this->getRequest()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[0];
        $this->setTrackID($trackID);
        if (is_numeric($trackID)){
            $this->switchState(self::OnTrackNumericIDState);
            return self::NEXT;
        }

        $this->switchState(self::OnTrackStringIDState);
        return self::NEXT;
    }

    /**
     * @throws \Exception
     */
    public function OnTrackNumericIDState(): string
    {
        try {
            $track = $this->getTrackData()
                ->selectWithConditionFromTrack(['*'], "slug_id = ?", [$this->getTrackID()]);
            if (isset($track->slug_id) && isset($track->track_slug)){
                $this->intendedTrackURL = "/tracks/$track->slug_id/$track->track_slug";
                return self::DONE;
            }
        } catch (\Exception){
            return self::ERROR;
        }

        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function OnTrackStringIDState(): string
    {
        try {
            $track = $this->getTrackData()
                ->selectWithConditionFromTrack(['*'], "track_slug = ?", [$this->getTrackID()]);
            if (isset($track->slug_id) && isset($track->track_slug)){
                $this->intendedTrackURL = "/tracks/$track->slug_id/$track->track_slug";
                return self::DONE;
            }
        } catch (\Exception){
            return self::ERROR;
        }

        return self::ERROR;
    }

    /**
     * @return OnRequestProcess|null
     */
    public function getRequest(): ?OnRequestProcess
    {
        return $this->request;
    }

    /**
     * @param OnRequestProcess|null $request
     */
    public function setRequest(?OnRequestProcess $request): void
    {
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getIntendedTrackURL(): string
    {
        return $this->intendedTrackURL;
    }

    /**
     * @param string $intendedTrackURL
     */
    public function setIntendedTrackURL(string $intendedTrackURL): void
    {
        $this->intendedTrackURL = $intendedTrackURL;
    }

    /**
     * @return TrackData
     */
    public function getTrackData(): TrackData
    {
        return $this->trackData;
    }

    /**
     * @return string
     */
    public function getTrackID(): string
    {
        return $this->trackID;
    }

    /**
     * @param string $trackID
     */
    public function setTrackID(string $trackID): void
    {
        $this->trackID = $trackID;
    }
}