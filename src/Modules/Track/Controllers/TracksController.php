<?php

namespace App\Modules\Track\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Controllers\Controller;
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
use Exception;
use JetBrains\PhpStorm\NoReturn;

class TracksController extends Controller
{
    use Validator, TrackValidationRules;

    private TrackData $trackData;
    private ?FieldData $fieldData;
    private ?OnTrackDefaultField $onTrackDefaultField;

    public function __construct(TrackData $trackData, FieldData $fieldData = null, OnTrackDefaultField $onTrackDefaultField = null)
    {
        $this->trackData = $trackData;
        $this->fieldData = $fieldData;
        $this->onTrackDefaultField = $onTrackDefaultField;
    }

    /**
     * @throws \Exception
     */
    public function index()
    {
        $cols = '`track_id`, `track_status`, `slug_id`, `track_slug`, `track_title`, `fk_genre_id`, `fk_artist_id`, `fk_license_id`';
        $data = $this->getTrackData()->generatePaginationData(
            $cols,
            'track_title',
            $this->getTrackData()->getTrackTable());

        $trackListing = '';
        if ($data !== null){
            $trackListing = $this->getTrackData()->adminTrackListing($data->data, $this->trackData->getPageStatus());
            unset($data->data);
        }

        view('Modules::Track/Views/index', [
            'SiteURL' => AppConfig::getAppUrl(),
            'Data' => $data,
            'TrackListing' => $trackListing
        ]);
    }

    /**
     * @throws \Exception
     */
    public function create()
    {
        $this->fieldData->getFieldItemsAPI();

        $genre = $this->getTrackData()->getGenrePaginationData();

        ## FOR GENRE API META-BOX
        $this->getTrackData()->genreMetaBox($genre);

        ## FOR LICENSE API META-BOX
        $this->getTrackData()->licenseMetaBox();

        event()->dispatch($this->onTrackDefaultField);

        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true);
        $oldFormInput = json_decode($oldFormInput, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Modules::Track/Views/create', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldSelection' => $this->fieldData->getFieldsSelection($this->onTrackDefaultField->getTrackDefaultFieldSlug()),
            'FieldItems' => $this->fieldData->generateFieldWithFieldSlug($this->onTrackDefaultField->getTrackDefaultFieldSlug(), $oldFormInput)->getHTMLFrag(),
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

        try {
            $track = $this->getTrackData()->createTrack(['token']);
            $trackReturning = db()->insertReturning($this->getTrackData()->getTrackTable(), $track, $this->getTrackData()->getTrackColumns());
            $onTrackCreate = new OnTrackCreate($trackReturning, $this->getTrackData());
        } catch (Exception $exception){
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('tracks.create'));
        }

        event()->dispatch($onTrackCreate);
        session()->flash(['Track Created'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('tracks.edit', ['track' => $onTrackCreate->getTrackSlug()]));
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function edit(string $slug)
    {
        $this->fieldData->getFieldItemsAPI();

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

        $onTrackDefaultField = $this->onTrackDefaultField;
        $fieldIDS = ($track->field_ids === null) ? [] : json_decode($track->field_ids, true);
        $onTrackDefaultField->setTrackDefaultFieldSlug($fieldIDS);
        event()->dispatch($onTrackDefaultField);

        $fieldItems = $this->fieldData->generateFieldWithFieldSlug($onTrackDefaultField->getTrackDefaultFieldSlug(), $fieldSettings)->getHTMLFrag();

        view('Modules::Track/Views/edit', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'Data' => $onTrackCreate->getAllToArray(),
            'FieldSelection' => $this->fieldData->getFieldsSelection($onTrackDefaultField->getTrackDefaultFieldSlug()),
            'FieldItems' => $fieldItems
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
            $this->getTrackData()->updateWithCondition($track, ['track_slug' => $slug], $this->getTrackData()->getTrackTable());
        } catch (Exception){
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('tracks.edit', [$slug]));
        }

        $slug = $track['track_slug'];
        session()->flash(['Track Updated'], type: Session::SessionCategories_FlashMessageSuccess);
        apcu_clear_cache();
        redirect(route('tracks.edit', ['track' => $slug]));
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function trash(string $slug)
    {
        $toUpdate = [
            'track_status' => -1
        ];
        $this->getTrackData()->updateWithCondition($toUpdate, ['track_slug' => $slug], $this->getTrackData()->getTrackTable());
        session()->flash(['Track Moved To Trash'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('tracks.index'));
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function trashMultiple()
    {
        if (!input()->fromPost()->hasValue('itemsToTrash')){
            session()->flash(['Nothing To Trash'], type: Session::SessionCategories_FlashMessageInfo);
            redirect(route('tracks.index'));
        }
        $itemsToTrash = array_map(function ($item){
            $itemCopy = json_decode($item, true);
            $item = [];
            foreach ($itemCopy as $k => $v){
                if (key_exists($k, array_flip($this->getTrackData()->getTrackColumns()))){
                    $item[$k] = $v;
                }
            }
            $item['track_status'] = '-1';
            return $item;
        }, input()->fromPost()->retrieve('itemsToTrash'));

        try {
            db()->insertOnDuplicate(Tables::getTable(Tables::TRACKS), $itemsToTrash, ['track_status']);
        } catch (\Exception $e){
            session()->flash(['Fail To Trash Track Items']);
            redirect(route('tracks.index'));
        }
        session()->flash(['Track(s) Trashed'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('tracks.index'));
    }

    /**
     * @param string $slug
     * @return void
     * @throws \Exception
     */
    public function delete(string $slug)
    {
        try {
            $this->getTrackData()->deleteWithCondition(whereCondition: "track_slug = ?", parameter: [$slug], table: $this->getTrackData()->getTrackTable());
            session()->flash(['Track Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('tracks.index'));
        } catch (\Exception $e){
            $errorCode = $e->getCode();
            switch ($errorCode){
                default:
                    session()->flash(['Failed To Delete Track']);
                    break;
            }
            redirect(route('tracks.index'));
        }
    }

    /**
     * @throws \Exception
     */
    public function deleteMultiple()
    {
        if (!input()->fromPost()->hasValue('itemsToDelete')){
            session()->flash(['Nothing To Delete'], type: Session::SessionCategories_FlashMessageInfo);
            redirect(route('tracks.index'));
        }

        $this->getTrackData()->deleteMultiple(
            $this->getTrackData()->getTrackTable(),
            array_flip($this->getTrackData()->getTrackColumns()),
            'track_id',
            onSuccess: function (){
                session()->flash(['Track Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
                redirect(route('tracks.index'));
            },
            onError: function ($e){
                $errorCode = $e->getCode();
                switch ($errorCode){
                    default:
                        session()->flash(['Failed To Delete Track']);
                        break;
                }
                redirect(route('tracks.index'));
            },
        );
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

        SimpleState::displayUnauthorizedErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
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
        return $this->fieldData;
    }

    /**
     * @return OnTrackDefaultField|null
     */
    public function getOnTrackDefaultField(): ?OnTrackDefaultField
    {
        return $this->onTrackDefaultField;
    }

}
