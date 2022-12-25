<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCoupon\Controllers;

use App\Apps\NinetySeven\Controller\NinetySevenController;
use App\Apps\TonicsCoupon\Data\CouponData;
use App\Apps\TonicsCoupon\Events\OnCouponTypeCreate;
use App\Apps\TonicsCoupon\RequestInterceptor\CouponAccessView;
use App\Apps\TonicsCoupon\Rules\CouponValidationRules;
use App\Apps\TonicsCoupon\TonicsCouponActivator;
use App\Apps\TonicsSeo\Controller\TonicsSeoController;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\States\CommonResourceRedirection;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Exceptions\URLNotFound;
use Exception;
use JetBrains\PhpStorm\NoReturn;

class CouponTypeController
{
    private CouponData $couponData;
    private UserData $userData;

    use Validator, CouponValidationRules, UniqueSlug;

    public function __construct(CouponData $couponData, UserData $userData)
    {
        $this->couponData = $couponData;
        $this->userData = $userData;
    }

    /**
     * @throws Exception
     */
    public function index()
    {
        $table = TonicsCouponActivator::couponTypeTableName();
        $dataTableHeaders = [
            ['type' => '', 'slug' => TonicsCouponActivator::COUPON_TYPE . '::' . 'coupon_type_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'coupon_type_id'],
            ['type' => 'text', 'slug' => TonicsCouponActivator::COUPON_TYPE . '::' . 'coupon_type_name', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'coupon_type_name'],
            ['type' => 'date_time_local', 'slug' => TonicsCouponActivator::COUPON_TYPE . '::' . 'created_at', 'title' => 'Created', 'minmax' => '50px, .8fr', 'td' => 'created_at'],
            ['type' => 'date_time_local', 'slug' => TonicsCouponActivator::COUPON_TYPE . '::' . 'updated_at', 'title' => 'Updated', 'minmax' => '50px, .8fr', 'td' => 'updated_at'],
        ];
        $couponTypeRootPath = CouponSettingsController::getTonicsCouponTypeRootPath();
        $tblCol = "*, CONCAT('/admin/tonics_coupon/type/', coupon_type_slug, '/edit' ) as _edit_link, CONCAT_WS('/', '/$couponTypeRootPath', slug_id, coupon_type_slug) as _preview_link";
        $data = db()->Select($tblCol)
            ->From($table)
            ->when(url()->hasParamAndValue('status'),
                function (TonicsQuery $db) {
                    $db->WhereEquals('coupon_type_status', url()->getParam('status'));
                },
                function (TonicsQuery $db) {
                    $db->WhereEquals('coupon_type_status', 1);

                })->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                $db->WhereLike('coupon_type_name', url()->getParam('query'));

            })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($table) {
                $db->WhereBetween(table()->pickTable($table, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

            })->OrderByDesc(table()->pickTable($table, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        view('Apps::TonicsCoupon/Views/CouponType/index', [
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
        if ($this->getCouponData()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deleted", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getCouponData()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
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
        event()->dispatch($this->getCouponData()->getOnCouponTypeDefaultField());

        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Apps::TonicsCoupon/Views/CouponType/create', [
            'Categories' => $this->getCouponData()->getCouponTypeHTMLSelect(),
            'FieldItems' => $this->getFieldData()->generateFieldWithFieldSlug($this->getCouponData()->getOnCouponTypeDefaultField()->getFieldSlug(), $oldFormInput)->getHTMLFrag()
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

        if (input()->fromPost()->hasValue('coupon_type_slug') === false){
            $_POST['coupon_type_slug'] = helper()->slug(input()->fromPost()->retrieve('coupon_type_name'));
        }

        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->couponTypeStoreRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('tonicsCoupon.Type.create'));
        }

        # Storing db reference is the only way I got tx to work
        # this could be as a result of pass db() around in event handlers
        $db = db();
        try {
            $db->beginTransaction();
            $couponType = $this->couponData->createCouponType();
            $couponTypeReturning = $this->couponData->insertForCoupon($couponType, CouponData::CouponType_INT);
            $onCouponTypeCreate = new OnCouponTypeCreate($couponTypeReturning, $this->couponData);
            event()->dispatch($onCouponTypeCreate);
            $db->commit();

            session()->flash(['Coupon Type Created'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('tonicsCoupon.Type.edit', ['couponType' => $onCouponTypeCreate->getCouponTypeSlug()]));
        }catch (Exception $exception){
            // Log..
            $db->rollBack();
            session()->flash(['An Error Occurred, Creating Coupon Type'], input()->fromPost()->all());
            redirect(route('tonicsCoupon.Type.create'));
        }

    }

    /**
     * @param string $slug
     * @throws \Exception
     */
    public function edit(string $slug): void
    {
        $couponType = db()->Select('*')->From(TonicsCouponActivator::couponTypeTableName())->WhereEquals('coupon_type_slug', $slug)->FetchFirst();

        if (!is_object($couponType)){
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $fieldSettings = json_decode($couponType->field_settings, true);
        if (empty($fieldSettings)){
            $fieldSettings = (array)$couponType;
        } else {
            $fieldSettings = [...$fieldSettings, ...(array)$couponType];
        }

        event()->dispatch($this->getCouponData()->getOnCouponTypeDefaultField());
        if (isset($fieldSettings['_fieldDetails'])){
            addToGlobalVariable('Data', $fieldSettings);
            $fieldCategories = $this->getFieldData()
                ->compareSortAndUpdateFieldItems(json_decode($fieldSettings['_fieldDetails']));
            $htmlFrag = $this->getFieldData()->getUsersFormFrag($fieldCategories);
        } else {
            $fieldForm = $this->getFieldData()->generateFieldWithFieldSlug($this->getCouponData()->getOnCouponTypeDefaultField()->getFieldSlug(), $fieldSettings);
            $htmlFrag = $fieldForm->getHTMLFrag();
            addToGlobalVariable('Data', $couponType);
        }

        view('Apps::TonicsCoupon/Views/CouponType/edit', [
            'FieldItems' => $htmlFrag,
        ]);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function update(string $slug): void
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->couponTypeUpdateRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors());
            redirect(route('tonicsCoupon.Type.edit', [$slug]));
        }

        if (input()->fromPost()->hasValue('coupon_type_parent_id') && input()->fromPost()->hasValue('coupon_type_id')){
            $trackCatParentID = input()->fromPost()->retrieve('coupon_type_parent_id');
            $trackCatID = input()->fromPost()->retrieve('coupon_type_id');
            $category = db()->Select('*')->From($this->getCouponData()->getCouponTypeTable())->WhereEquals('coupon_type_slug', $slug)->FetchFirst();
            // Coupon Type Parent ID Cant Be a Parent of Itself, Silently Revert it To Initial Parent
            if ($trackCatParentID === $trackCatID){
                $_POST['coupon_type_parent_id'] = $category->coupon_type_parent_id;
                // Log..
                // Error Message is: Coupon Type Parent ID Cant Be a Parent of Itself, Silently Revert it To Initial Parent
            }
        }

        $updateChanges = $this->couponData->createCouponType();
        $updateChanges['coupon_type_slug'] = helper()->slug(input()->fromPost()->retrieve('coupon_type_slug'));

        db()->FastUpdate($this->couponData->getCouponTypeTable(), $updateChanges, db()->Where('coupon_type_slug', '=', $slug));
        $slug = $updateChanges['coupon_type_slug'];

        if (input()->fromPost()->has('_fieldErrorEmitted') === true){
            session()->flash(['Coupon Type Updated But Some Field Inputs Are Incorrect'], input()->fromPost()->all(), type: Session::SessionCategories_FlashMessageInfo);
        } else {
            session()->flash(['Coupon Type Updated'], type: Session::SessionCategories_FlashMessageSuccess);
        }
        redirect(route('tonicsCoupon.Type.edit', ['couponType' => $slug]));
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws Exception
     */
    protected function updateMultiple($entityBag): bool
    {
        return $this->getCouponData()->dataTableUpdateMultiple('coupon_type_id', TonicsCouponActivator::couponTypeTableName(), $entityBag, $this->couponTypeUpdateMultipleRule());
    }

    /**
     * @param $entityBag
     * @return bool
     */
    public function deleteMultiple($entityBag): bool
    {
        return $this->getCouponData()->dataTableDeleteMultiple('coupon_type_id', TonicsCouponActivator::couponTypeTableName(), $entityBag);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function redirect($id): void
    {
        $redirection = new CommonResourceRedirection(
            onSlugIDState: function ($slugID) {
                $coupon = db()->Select('*')->From(TonicsCouponActivator::couponTypeTableName())
                    ->WhereEquals('slug_id', $slugID)->FetchFirst();
                if (isset($coupon->slug_id) && isset($coupon->coupon_type_slug)) {
                    return TonicsCouponActivator::getCouponTypeAbsoluteURLPath((array)$coupon);
                }
                return false;
            }, onSlugState: function ($slug) {
            $coupon = db()->Select('*')->From(TonicsCouponActivator::couponTypeTableName())
                ->WhereEquals('coupon_type_slug', $slug)->FetchFirst();
            if (isset($coupon->slug_id) && isset($coupon->coupon_type_slug)) {
                return TonicsCouponActivator::getCouponTypeAbsoluteURLPath((array)$coupon);
            }
            return false;
        });

        $redirection->runStates();
    }

    /**
     * @throws \Exception
     */
    public function singleCouponType()
    {
        $couponAccessView = new CouponAccessView($this->getCouponData());
        $couponAccessView->handleCouponTypes();
        $couponAccessView->showCouponType('Apps::TonicsCoupon/Views/FrontPage/coupon-type-single', NinetySevenController::getSettingData());
    }

    /**
     * @param string $couponTypeName
     * @return void
     * @throws \Exception
     */
    public function rssCouponType(string $couponTypeName)
    {
        $couponTableName = TonicsCouponActivator::couponTableName();
        $couponTypeTableName = TonicsCouponActivator::couponTypeTableName();
        $couponToTypeTableName = TonicsCouponActivator::couponToTypeTableName();

        $couponTypeData = db()
            ->Select('*, JSON_UNQUOTE(JSON_EXTRACT(field_settings, "$.seo_description")) as coupon_type_description')
            ->From($couponTypeTableName)->WhereEquals('coupon_type_slug', $couponTypeName)->FetchFirst();

        if ($couponTypeData){
            $settings = TonicsSeoController::getSettingsData();
            $rssSettingsData = [
                'Title' => $settings['app_tonicsseo_site_title'] ?? null,
                'Description' => $couponTypeData->coupon_type_description,
                'Logo' => $settings['app_tonicsseo_rss_settings_logo'] ?? null,
                'RequestURL' => AppConfig::getAppUrl(),
                'Language' => $settings['app_tonicsseo_rss_settings_language'] ?? 'en',
            ];

            $couponRootPath = CouponSettingsController::getTonicsCouponRootPath();
            $postFieldSettings = $couponTableName . '.field_settings';
            $tblCol = table()->pick([$couponTableName => ['coupon_id', 'coupon_name', 'coupon_slug', 'field_settings', 'slug_id', 'created_at', 'updated_at', 'image_url']])
                . ", $couponTableName.coupon_name as _title, $couponTableName.image_url as _image "
                . ", CONCAT(coupon_type_id, '::', coupon_type_slug ) as fk_coupon_type_id, CONCAT_WS('/', '/$couponRootPath', $couponTableName.slug_id, coupon_slug) as _preview_link "
                . ", JSON_UNQUOTE(JSON_EXTRACT($postFieldSettings, '$.seo_description')) as _description"
                . ", DATE_FORMAT($couponTableName.created_at, '%a, %d %b %Y %T') as rssPubDate";

            $couponTypeIDSResult = $this->getCouponData()->getChildCouponTypesOfParent($couponTypeData->coupon_type_id);
            $couponTypeIDS = [];
            foreach ($couponTypeIDSResult as $couponTypeItem){
                $couponTypeIDS[] = $couponTypeItem->coupon_type_id;
            }

            $rssSettingsData['Query'] = db()->Select($tblCol)
                ->From($couponToTypeTableName)
                ->Join($couponTableName, table()->pickTable($couponTableName, ['coupon_id']), table()->pickTable($couponToTypeTableName, ['fk_coupon_id']))
                ->Join($couponTypeTableName, table()->pickTable($couponTypeTableName, ['coupon_type_id']), table()->pickTable($couponToTypeTableName, ['fk_coupon_type_id']))
                ->WhereEquals('coupon_status', 1)
                ->Where("$couponTableName.created_at", '<=', helper()->date())
                ->WhereIn('coupon_type_id', $couponTypeIDS)->GroupBy('coupon_id')
                ->OrderByDesc(table()->pickTable($couponTableName, ['created_at']))->SimplePaginate(50);

            response()->header("content-type: text/xml; charset=UTF-8");

            view('Apps::TonicsSeo/Views/rss', [
                'rssData' => $rssSettingsData,
            ]);
        } else {
            throw new URLNotFound('RSS Feed Not Found', SimpleState::ERROR_PAGE_NOT_FOUND__CODE);
        }
    }

    /**
     * @return CouponData
     */
    public function getCouponData(): CouponData
    {
        return $this->couponData;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->getCouponData()->getFieldData();
    }

    /**
     * @return UserData
     */
    public function getUserData(): UserData
    {
        return $this->userData;
    }

}