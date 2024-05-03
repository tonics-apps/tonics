<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Controllers;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class ImageController
{
    use Validator;

    private FieldData $fieldData;
    private AbstractDataLayer $abstractDataLayer;

    /**
     * @param FieldData $fieldData
     * @param AbstractDataLayer $abstractDataLayer
     */
    public function __construct(FieldData $fieldData, AbstractDataLayer $abstractDataLayer)
    {
        $this->fieldData = $fieldData;
        $this->abstractDataLayer = $abstractDataLayer;
    }

    /**
     * @return void
     * @throws \Exception|\Throwable
     */
    public function index(): void
    {
        $dataTableHeaders = [
            ['type' => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_CONTAINER_IMAGES . '::' . 'container_image_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'container_image_id'],
            ['type' => 'text', 'slug' => TonicsCloudActivator::TONICS_CLOUD_CONTAINER_IMAGES . '::' . 'container_image_name', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'container_image_name'],
            ['type' => 'date_time_local', 'slug' => TonicsCloudActivator::TONICS_CLOUD_CONTAINER_IMAGES . '::' . 'updated_at', 'title' => 'Date Updated', 'minmax' => '150px, 1fr', 'td' => 'updated_at'],
        ];

        $data = null;
        db( onGetDB: function (TonicsQuery $db) use (&$data){
            $imagesTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINER_IMAGES);

            $data = $db->Select('container_image_id, container_image_name, container_image_description, updated_at, 
                CONCAT("/admin/tonics_cloud/images/", container_image_id, "/edit" ) as _edit_link')
                ->From("$imagesTable")
                ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('container_image_name', url()->getParam('query'));
                })
                ->OrderByDesc(table()->pickTable($imagesTable, ['created_at']))
                ->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));
        });

        view('Apps::TonicsCloud/Views/Image/index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $data ?? [],
                'dataTableType' => 'EDITABLE_PREVIEW',
            ],
            'SiteURL' => AppConfig::getAppUrl(),
        ]);
    }

    /**
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function dataTable(): void
    {
        $entityBag = null;
        if ($this->getAbstractDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deleted", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500, 'An Error Occurred Deleting Records');
            }
        } elseif ($this->getAbstractDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->updateMultiple($entityBag)) {
                response()->onSuccess([], "Records Updated, Reload For Changes", more: AbstractDataLayer::DataTableEventTypeUpdate);
            } else {
                response()->onError(500, 'An Error Occurred Updating Records');
            }
        }
    }

    /**
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function create(): void
    {
        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Apps::TonicsCloud/Views/Image/create', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $this->getFieldData()
                ->generateFieldWithFieldSlug(['app-tonicscloud-image-page'], $oldFormInput)->getHTMLFrag()
        ]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     * @throws \Exception
     * @throws \Throwable
     */
    public function store(): void
    {
        $validator = $this->getValidator();
        $validation = $validator->make(input()->fromPost()->all(), $this->getImageCreateRule());
        if ($validation->fails()){
            session()->flash($validation->getErrors(), input()->fromPost()->all());
            redirect(route('tonicsCloud.admin.images.create'));
        }

        try {
            db( onGetDB: function (TonicsQuery $db){
                $imageTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINER_IMAGES);
                $db->Q()->Insert($imageTable, $this->prepareImageForInsertion());
            });
            session()->flash(['Image Created Successful'], [], Session::SessionCategories_FlashMessageSuccess);
            redirect(route('tonicsCloud.admin.images.index'));
        } catch (\Exception $exception){
            // Log..
        }

        session()->flash(['An Error Occurred Creating Image'], input()->fromPost()->all());
        redirect(route('tonicsCloud.admin.images.create'));
    }

    /**
     * @param $ID
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function edit($ID): void
    {
        $imageData = null;
        db(onGetDB: function (TonicsQuery $db) use ($ID, &$imageData) {
            $imageTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINER_IMAGES);
            $imageData = $db->Select('*')->From($imageTable)
                ->WhereEquals('container_image_id', $ID)
                ->FetchFirst();
        });

        if (!is_object($imageData)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $fieldSettings = json_decode($imageData->others, true);
        if (empty($fieldSettings)) {
            $fieldSettings = (array)$imageData;
        } else {
            $fieldSettings = [...$fieldSettings, ...(array)$imageData];
        }

        if (isset($fieldSettings['_fieldDetails'])){
            addToGlobalVariable('Data', $fieldSettings);
            $fieldCategories = $this->getFieldData()->compareSortAndUpdateFieldItems(json_decode($fieldSettings['_fieldDetails']));
            $htmlFrag = $this->getFieldData()->getUsersFormFrag($fieldCategories);
        } else {
            $fieldForm = $this->getFieldData()->generateFieldWithFieldSlug(['app-tonicscloud-image-page'], $fieldSettings);
            $htmlFrag = $fieldForm->getHTMLFrag();
            addToGlobalVariable('Data', $imageData);
        }

        view('Apps::TonicsCloud/Views/Image/edit', [
            'ImageData' => $imageData,
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $htmlFrag
        ]);
    }

    /**
     * @param $ID
     * @return void
     * @throws \ReflectionException
     * @throws \Exception
     * @throws \Throwable
     */
    public function update($ID): void
    {
        $validator = $this->getValidator();
        $validation = $validator->make(input()->fromPost()->all(), $this->getImageCreateRule());
        if ($validation->fails()){
            session()->flash($validation->getErrors(), input()->fromPost()->all());
            redirect(route('tonicsCloud.admin.images.edit', [$ID]));
        }

        try {
            db( onGetDB: function (TonicsQuery $db) use ($ID) {
                $imageTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINER_IMAGES);
                $db->FastUpdate($imageTable, $this->prepareImageForInsertion(), db()->WhereEquals('container_image_id', $ID));
            });
            session()->flash(['Image Updated Successful'], [], Session::SessionCategories_FlashMessageSuccess);
            redirect(route('tonicsCloud.admin.images.edit', [$ID]));
        } catch (\Exception $exception){
            // Log..
        }

        session()->flash(['An Error Occurred Updating Image'], input()->fromPost()->all());
        redirect(route('tonicsCloud.admin.images.edit', [$ID]));
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws \Exception
     */
    public function updateMultiple($entityBag): bool
    {
        return $this->getAbstractDataLayer()->dataTableUpdateMultiple([
            'id' => 'container_image_id',
            'table' => TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINER_IMAGES),
            'rules' => $this->getImageMultipleRule(),
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
        return $this->getAbstractDataLayer()->dataTableDeleteMultiple([
            'id' => 'container_image_id',
            'table' => TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINER_IMAGES),
            'entityBag' => $entityBag,
        ]);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function getHeaders($imageID): void
    {
        if (!hash_equals(AppConfig::getKey(), input()->fromGet()->retrieve('token', ''))) {
            response()->httpResponseCode(400);
            exit("Unauthorized Access");
        }

        $imageData = self::getImageData($imageID);
        if ($imageData === null){
            response()->httpResponseCode(400);
            exit("Image Does Not Exist");
        }

        if (helper()->isJSON($imageData->others)){
            $imageOthers = json_decode($imageData->others);
            if (isset($imageOthers->images)){
                if (input()->fromGet()->hasValue('version')){
                    $imageVersion = input()->fromGet()->retrieve('version');
                } else {
                    $imageVersion = array_key_last($imageOthers->images);
                }
                if (isset($imageOthers->images->{$imageVersion})){
                    $image = $imageOthers->images->{$imageVersion};
                    $imageHash = $image->image_hash;
                    $urlMirrors = $image->mirrors;
                    $pickRandomMirror = $urlMirrors[array_rand($urlMirrors)];
                    response()->headers([
                        'Incus-Image-Hash: ' . $imageHash,
                        'Incus-Image-URL: ' . $pickRandomMirror,
                    ]);
                    return;
                }
            }
        }
        response()->httpResponseCode(400);
        exit("Failed To Decode Image Fields");
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData $fieldData
     */
    public function setFieldData(FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @return AbstractDataLayer
     */
    public function getAbstractDataLayer(): AbstractDataLayer
    {
        return $this->abstractDataLayer;
    }

    /**
     * @throws \Exception
     */
    public function getImageCreateRule(): array
    {
        return [
            'container_image_name' => ['required', 'string', 'CharLen' => ['min' => 3, 'max' => 255]],
            'container_image_description' => ['string'],
            'image_link_mirror' => ['array']
        ];
    }

    /**
     * @throws \Exception
     */
    public function getImageMultipleRule(): array
    {
        return [
            'container_image_name' => ['required', 'string', 'CharLen' => ['min' => 3, 'max' => 255]]
        ];
    }


    /**
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    private function prepareImageForInsertion(): array
    {
        $imageName = input()->fromPost()->retrieve('container_image_name');
        $imageDescription = input()->fromPost()->retrieve('container_image_description');
        $imageLogo = input()->fromPost()->retrieve('container_image_logo');
        $imageApps = [];
        if (input()->fromPost()->has('image_apps')){
            $imageApps = input()->fromPost()->retrieve('image_apps');
            $imageApps = explode(',', $imageApps);
            $imageApps = array_map(fn($image) => trim($image), $imageApps);
        }
        $fields = json_decode(input()->fromPost()->retrieve('_fieldDetails'));
        $images = [];
        $version = '1.0';
        foreach ($fields as $field) {

            if (isset($field->main_field_slug)) {

                $fieldOptions = json_decode($field->field_options);
                $value = $fieldOptions->{$field->field_input_name} ?? null;

                if ($field->field_input_name === 'image_link_mirrors_version'){
                    $version = $value;
                    $images[$version] = [
                        'mirrors' => [],
                        'image_hash' => null,
                    ];
                }
                if ($field->field_input_name === 'image_hash'){
                    $images[$version]['image_hash'] = $value;
                }
                if ($field->field_input_name === 'image_link_mirror[]'){
                    $images[$version]['mirrors'][] = $value;
                }
            }

        }

        return [
            'container_image_name' => $imageName,
            'container_image_description' => $imageDescription,
            'container_image_logo' => $imageLogo,
            'others' => json_encode(['images' => $images, 'apps' => $imageApps, 'image_hash' => input()->fromPost()->retrieve('image_hash'),
        '_fieldDetails' => input()->fromPost()->retrieve('_fieldDetails')]),
        ];
    }

    /**
     * @param $imageID
     * @return mixed|null
     * @throws \Exception
     */
    public static function getImageData($imageID): mixed
    {
        $image = null;
        db(onGetDB: function (TonicsQuery $db) use ($imageID, &$image) {
            $image = $db->Select("*")
                ->From(TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINER_IMAGES))
                ->WhereIn('container_image_id', $imageID)->FetchFirst();
        });

        return $image;
    }

    /**
     * Get all images
     * @return array|bool|null
     * @throws \Exception
     */
    public static function getImages(): bool|array|null
    {
        $containerImages = null;
        db(onGetDB: function (TonicsQuery $db) use (&$containerImages) {
            $containerImageTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINER_IMAGES);
            $containerImages = $db->Select('container_image_id, container_image_name, container_image_logo, container_image_description, others')->From($containerImageTable)->FetchResult();
        });
        return $containerImages;
    }

}