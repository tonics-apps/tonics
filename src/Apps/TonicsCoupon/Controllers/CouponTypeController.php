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

use App\Apps\TonicsCoupon\Data\CouponData;
use App\Apps\TonicsCoupon\Events\OnCouponTypeCreate;
use App\Apps\TonicsCoupon\Rules\CouponValidationRules;
use App\Apps\TonicsCoupon\TonicsCouponActivator;
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

        $tblCol = '*, CONCAT("/admin/tonics_coupon/type/", coupon_type_slug, "/edit" ) as _edit_link, CONCAT("/coupon_type/", coupon_type_slug) as _preview_link';

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
     * @param string $slug
     * @return void
     * @throws Exception
     */
    public function delete(string $slug): void
    {
        try {
            $this->getCouponData()->deleteWithCondition(whereCondition: "coupon_type_slug = ?", parameter: [$slug], table: $this->getCouponData()->getCouponTypeTable());
            session()->flash(['Category Deleted'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('tonicsCoupon.Type.index'));
        } catch (\Exception $e){
            $errorCode = $e->getCode();
            switch ($errorCode){
                default:
                    session()->flash(['Failed To Delete Category']);
                    break;
            }
            redirect(route('Category'));
        }
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function redirect($id): void
    {
        $redirection = new CommonResourceRedirection(
            onSlugIDState: function ($slugID){
                $category = $this->getCouponData()
                    ->selectWithConditionFromCategory(['*'], "slug_id = ?", [$slugID]);
                if (isset($category->slug_id) && isset($category->coupon_type_slug)){
                    return "/categories/$category->slug_id/$category->coupon_type_slug";
                }
                return false;
            }, onSlugState: function ($slug){
            $category = $this->getCouponData()
                ->selectWithConditionFromCategory(['*'], "coupon_type_slug = ?", [$slug]);
            if (isset($category->slug_id) && isset($category->coupon_type_slug)){
                return "/categories/$category->slug_id/$category->coupon_type_slug";
            }
            return false;
        });

        $redirection->runStates();
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