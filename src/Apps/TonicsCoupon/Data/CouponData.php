<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge, 
 * you shouldn't and can't freely copy, modify, merge, 
 * publish, distribute, sublicense, 
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCoupon\Data;

use App\Apps\TonicsCoupon\Controllers\CouponSettingsController;
use App\Apps\TonicsCoupon\Events\OnCouponDefaultField;
use App\Apps\TonicsCoupon\Events\OnCouponTypeCreate;
use App\Apps\TonicsCoupon\Events\OnCouponTypeDefaultField;
use App\Apps\TonicsCoupon\TonicsCouponActivator;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\CustomClasses\UniqueSlug;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CouponData extends AbstractDataLayer
{
    use UniqueSlug;

    const Coupon_INT = 1;
    const CouponType_INT = 2;
    const CouponToCouponType_INT = 3;

    private ?FieldData $fieldData;
    private ?OnCouponDefaultField $onCouponDefaultField;
    private ?OnCouponTypeDefaultField $onCouponTypeDefaultField;

    public function __construct(FieldData $fieldData = null, OnCouponDefaultField $onCouponDefaultField = null, OnCouponTypeDefaultField $onCouponTypeDefaultField = null)
    {
        $this->fieldData = $fieldData;
        $this->onCouponDefaultField = $onCouponDefaultField;
        $this->onCouponTypeDefaultField = $onCouponTypeDefaultField;
    }

    public static function COUPON_TABLES(): array
    {
        return [
            self::Coupon_INT => TonicsCouponActivator::couponTableName(),
            self::CouponType_INT => TonicsCouponActivator::couponTypeTableName(),
            self::CouponToCouponType_INT => TonicsCouponActivator::couponToTypeTableName(),
        ];
    }


    /**
     * @return mixed
     * @throws \Exception
     */
    public function getCouponType(): mixed
    {
        $couponTypeTable = $this->getCouponTypeTable();
        // tcs stands for tonics category system ;)
        return db()->run("
        WITH RECURSIVE coupon_type_recursive AS 
	( SELECT coupon_type_id, coupon_type_parent_id, coupon_type_slug, coupon_type_name, CAST(coupon_type_slug AS VARCHAR (255))
            AS path
      FROM {$couponTypeTable} WHERE coupon_type_parent_id IS NULL
      UNION ALL
      SELECT tcs.coupon_type_id, tcs.coupon_type_parent_id, tcs.coupon_type_slug, tcs.coupon_type_name, CONCAT(path, '/' , tcs.coupon_type_slug)
      FROM coupon_type_recursive as fr JOIN {$couponTypeTable} as tcs ON fr.coupon_type_id = tcs.coupon_type_parent_id
      ) 
     SELECT * FROM coupon_type_recursive;
        ");
    }
    
    /**
     * @param null $currentCatData
     * @return string
     * @throws \Exception
     */
    public function getCouponTypeHTMLSelect($currentCatData = null): string
    {
        $categories = helper()->generateTree(['parent_id' => 'coupon_type_parent_id', 'id' => 'coupon_type_id'], $this->getCouponType());
        $catSelectFrag = '';
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $catSelectFrag .= $this->getCouponTypeHTMLSelectFrag($category, $currentCatData);
            }
        }

        return $catSelectFrag;
    }

    /**
     * @param $category
     * @param null $currentCatIDS
     * @return string
     * @throws \Exception
     */
    private function getCouponTypeHTMLSelectFrag($category, $currentCatIDS = null): string
    {
        $currentCatIDS = (is_object($currentCatIDS) && property_exists($currentCatIDS, 'coupon_type_parent_id')) ? $currentCatIDS->coupon_type_parent_id : $currentCatIDS;

        if (!is_array($currentCatIDS)){
            $currentCatIDS = [$currentCatIDS];
        }

        $catSelectFrag = '';
        $catID = $category->coupon_type_id;
        if ($category->depth === 0) {
            $catSelectFrag .= <<<CAT
    <option data-is-parent="yes" data-depth="$category->depth"
            data-slug="$category->coupon_type_slug" data-path="/$category->path/" value="$catID"
CAT;
            foreach ($currentCatIDS as $currentCatID){
                if ($currentCatID == $category->coupon_type_id) {
                    $catSelectFrag .= 'selected';
                }
            }

            $catSelectFrag .= ">" . $category->coupon_type_name;
        } else {
            $catSelectFrag .= <<<CAT
    <option data-slug="$category->coupon_type_slug" data-depth="$category->depth" data-path="/$category->path/"
            value="$catID"
CAT;
            foreach ($currentCatIDS as $currentCatID){
                if ($currentCatID == $category->coupon_type_id) {
                    $catSelectFrag .= 'selected';
                }
            }

            $catSelectFrag .= ">" . str_repeat("&nbsp;&nbsp;&nbsp;", $category->depth + 1);
            $catSelectFrag .= $category->coupon_type_name;
        }
        $catSelectFrag .= "</option>";

        if (isset($category->_children)) {
            foreach ($category->_children as $catChildren) {
                $catSelectFrag .= $this->getCouponTypeHTMLSelectFrag($catChildren, $currentCatIDS);
            }
        }

        return $catSelectFrag;

    }

    public function couponTypeCheckBoxListing(array $couponTypes, $selected = [], string $inputName = 'coupon_type[]', string $type = 'radio'): string
    {
        $htmlFrag = '';
        $type = ($type !== 'radio') ? 'checkbox' : 'radio';
        $selected = array_combine($selected, $selected);

        foreach ($couponTypes as $couponType) {
            $id = 'coupon_type' . $couponType->coupon_type_id . '_' . $couponType->coupon_type_slug;
            if (key_exists($couponType->coupon_type_id, $selected)) {
                $htmlFrag .= <<<HTML
<li class="menu-item">
    <input type="$type"
    id="$id" checked="checked" name="$inputName" value="$couponType->coupon_type_id">
    <label for="$id">$couponType->coupon_type_name</label>
</li>
HTML;
                continue;
            }
            $htmlFrag .= <<<HTML
<li class="menu-item">
    <input type="$type"
    id="$id" name="$inputName" value="{$couponType->coupon_type_id}">
    <label for="$id">{$couponType->coupon_type_name}</label>
</li>
HTML;
        }
        return $htmlFrag;
    }

    /**
     * @throws \Exception
     */
    public function createCouponType(array $ignore = [], bool $prepareFieldSettings = true): array
    {
        $slug = $this->generateUniqueSlug($this->getCouponTypeTable(),
            'coupon_type_slug',
            helper()->slug(input()->fromPost()->retrieve('coupon_type_slug')));

        $couponType = [];
        $couponTypeCols = array_flip($this->getCouponTypeColumns());
        if (input()->fromPost()->hasValue('coupon_type_parent_id')) {
            $couponType['coupon_type_parent_id'] = input()->fromPost()->retrieve('coupon_type_parent_id');
        }

        foreach (input()->fromPost()->all() as $inputKey => $inputValue) {
            if (key_exists($inputKey, $couponTypeCols) && input()->fromPost()->has($inputKey)) {
                if ($inputKey === 'coupon_type_parent_id' && empty($inputValue)) {
                    $couponType[$inputKey] = null;
                    continue;
                }

                if ($inputKey === 'created_at') {
                    $couponType[$inputKey] = helper()->date(datetime: $inputValue);
                    continue;
                }

                if ($inputKey === 'coupon_type_slug') {
                    $couponType[$inputKey] = $slug;
                    continue;
                }
                $couponType[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $couponType);
        if (!empty($ignores)) {
            foreach ($ignores as $v) {
                unset($couponType[$v]);
            }
        }

        if ($prepareFieldSettings){
            return $this->getFieldData()->prepareFieldSettingsDataForCreateOrUpdate($couponType, 'coupon_type_name', 'coupon_type_content');
        }

        return $couponType;
    }

    /**
     * @param string|int $idSlug
     * @return array|bool
     * @throws \Exception
     */
    public function getChildCouponTypesOfParent(string|int $idSlug): bool|array
    {
        $couponTypeTableName = TonicsCouponActivator::couponTypeTableName();

        $where = "coupon_type_slug = ?";
        if (is_numeric($idSlug)) {
            $where = "coupon_type_id = ?";
        }
        $couponTypeRootPath = CouponSettingsController::getTonicsCouponTypeRootPath();
        return db()->run("
        WITH RECURSIVE coupon_type_recursive AS 
	( SELECT coupon_type_id, coupon_type_parent_id, coupon_type_slug, coupon_type_name, slug_id, field_settings, 
	        JSON_UNQUOTE(JSON_EXTRACT(field_settings, '$.seo_description')) AS _description,
            CAST(coupon_type_slug AS VARCHAR (255)) AS path
      FROM {$couponTypeTableName} WHERE $where
      UNION ALL
      SELECT tcs.coupon_type_id, tcs.coupon_type_parent_id, tcs.coupon_type_slug, tcs.coupon_type_name, tcs.slug_id, tcs.field_settings,
      JSON_UNQUOTE(JSON_EXTRACT(tcs.field_settings, '$.seo_description')) AS _description,
      CONCAT(path, '/' , tcs.coupon_type_slug)
      FROM coupon_type_recursive as fr JOIN {$couponTypeTableName} as tcs ON fr.coupon_type_id = tcs.coupon_type_parent_id
      ) 
     SELECT *, CONCAT_WS('/', '/$couponTypeRootPath', slug_id, coupon_type_slug) as _preview_link FROM coupon_type_recursive;
        ", $idSlug);
    }

    /**
     * @throws \Exception
     */
    public function getCouponTypesParent(string|int $idSlug)
    {
        $couponTypeTableName = TonicsCouponActivator::couponTypeTableName();
        $couponTypeRootPath = CouponSettingsController::getTonicsCouponTypeRootPath();
        $where = "coupon_type_slug = ?";
        if (is_numeric($idSlug)) {
            $where = "coupon_type_id = ?";
        }
        return db()->run("
        WITH RECURSIVE child_to_parent AS 
	( SELECT coupon_type_id, coupon_type_parent_id, slug_id, coupon_type_slug, coupon_type_status, coupon_type_name, CAST(coupon_type_slug AS VARCHAR (255))
            AS path
      FROM $couponTypeTableName WHERE $where
      UNION ALL
      SELECT fr.coupon_type_id, fr.coupon_type_parent_id, fr.slug_id, fr.coupon_type_slug, fr.coupon_type_status, fr.coupon_type_name, CONCAT(fr.coupon_type_slug, '/', path)
      FROM $couponTypeTableName as fr INNER JOIN child_to_parent as cp ON fr.coupon_type_id = cp.coupon_type_parent_id
      ) 
     SELECT *, CONCAT_WS('/', '/$couponTypeRootPath', slug_id, coupon_type_slug) as _preview_link FROM child_to_parent;
        ", $idSlug);
    }

    /**
     * You should get an array
     * @param $ID
     * @param string $column
     * @return array|mixed
     * @throws \Exception
     */
    public function getCouponUniqueID($ID, string $column = 'slug_id'): mixed
    {
        $userTable = Tables::getTable(Tables::USERS);
        $couponTableName = TonicsCouponActivator::couponTableName();
        $couponTypeTableName = TonicsCouponActivator::couponTypeTableName();
        $couponToTypeTableName = TonicsCouponActivator::couponToTypeTableName();

        # verify coupon column
        if (!table()->hasColumn($couponTableName, $column)){
            return [];
        }

        # Role ACCESS Key
        $role = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role);

        $couponData = db()->Select(CouponData::getCouponTableJoiningRelatedColumns())
            ->From($couponToTypeTableName)
            ->Join($couponTableName, table()->pickTable($couponTableName, ['coupon_id']), table()->pickTable($couponToTypeTableName, ['fk_coupon_id']))
            ->Join($couponTypeTableName, table()->pickTable($couponTypeTableName, ['coupon_type_id']), table()->pickTable($couponToTypeTableName, ['fk_coupon_type_id']))
            ->Join($userTable, table()->pickTable($userTable, ['user_id']), table()->pickTable($couponTableName, ['user_id']))
            ->WhereEquals('coupon_type_status', 1)
            ->WhereEquals(table()->pickTable($couponTableName, [$column]), $ID)
            # If User doesn't have read access, then, they are probably not logged in, so, we check if the post is live
            ->when(Roles::RoleHasPermission($role, Roles::CAN_READ) === false, function (TonicsQuery $db) use ($couponTableName) {
                $db->Where(table()->pickTable($couponTableName, ['created_at']), '<=', helper()->date());
            })
            ->FetchFirst();

        if (empty($couponData) || $couponData?->coupon_status === null){
            return [];
        }

        if (isset($couponData->fk_coupon_type_id)) {
            $couponTypes = explode(',', $couponData->fk_coupon_type_id);
            foreach ($couponTypes as $couponType){
                $couponType = explode('::', $couponType);
                if (count($couponType) === 2){
                    $reverseCategory = array_reverse($this->getCouponTypesParent($couponType[0]));
                    $couponData->couponTypes[] = $reverseCategory;
                }
            }
        }

        return (array)$couponData;
    }


    /**
     * @throws \Exception
     */
    public function createCoupon(array $ignore = [], bool $prepareFieldSettings = true): array
    {
        $slug = $this->generateUniqueSlug($this->getCouponTable(),
            'coupon_slug', helper()->slug(input()->fromPost()->retrieve('coupon_slug')));

        $coupon = [];
        $postColumns = array_flip($this->getCouponColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue) {
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)) {

                if ($inputKey === 'created_at') {
                    $coupon[$inputKey] = helper()->date(datetime: $inputValue);
                    continue;
                }

                if ($inputKey === 'coupon_slug') {
                    $coupon[$inputKey] = $slug;
                    continue;
                }
                $coupon[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $coupon);
        if (!empty($ignores)) {
            foreach ($ignores as $v) {
                unset($coupon[$v]);
            }
        }

        if ($prepareFieldSettings){
            return $this->getFieldData()->prepareFieldSettingsDataForCreateOrUpdate($coupon, 'coupon_name', 'coupon_content');
        }

        return $coupon;
    }

    /**
     * @throws \Exception
     */
    public function setDefaultCouponTypeIfNotSet()
    {
        if (input()->fromPost()->hasValue('fk_coupon_type_id') === false) {
            $findDefault = $this->findDefaultCouponType();

            if (isset($findDefault->coupon_type_id)) {
                $_POST['fk_coupon_type_id'] = [$findDefault->coupon_type_id];
                return;
            }

            $defaultCategory = [
                'coupon_type_name' => 'Default Coupon',
                'coupon_type_slug' => 'default-coupon',
                'coupon_type_status' => 1,
            ];

            $returning = $this->insertForCoupon($defaultCategory, self::CouponType_INT);
            $_POST['fk_coupon_type_id'] = [$returning->coupon_type_id];
            $couponTypeCreate = new OnCouponTypeCreate($returning, $this);
            event()->dispatch($couponTypeCreate);
        }
    }

    /**
     * @throws \Exception
     */
    public function findDefaultCouponType()
    {
        return db()->Select(table()->pickTable($this->getCouponTypeTable(), ['coupon_type_slug', 'coupon_type_id']))
            ->From($this->getCouponTypeTable())->WhereEquals('coupon_type_slug', 'default-coupon')
            ->FetchFirst();
    }

    /**
     * @throws \Exception
     */
    public function insertForCoupon(array $data, int $type = self::Coupon_INT, array $return = []): bool|\stdClass
    {
        if (!key_exists($type, self::COUPON_TABLES())) {
            throw new \Exception("Invalid Coupon Table Type");
        }

        if (empty($return)) {
            $return = $this->getCouponTypeColumns();
        }

        $table = table()->getTable(self::COUPON_TABLES()[$type]);
        $primaryKey = 'coupon_id';
        if ($type === self::CouponType_INT){
            $primaryKey = 'coupon_type_id';
        } elseif ($type === self::CouponToCouponType_INT){
            $primaryKey = 'id';
        }

        return db()->insertReturning($table, $data, $return, $primaryKey);
    }

    /**
     * @return string
     */
    public function getCouponTable(): string
    {
        return TonicsCouponActivator::couponTableName();
    }
    
    /**
     * @return string
     */
    public function getCouponTypeTable(): string
    {
        return TonicsCouponActivator::couponTypeTableName();
    }

    /**
     * @return string
     */
    public function getCouponToTypeTable(): string
    {
        return TonicsCouponActivator::couponToTypeTableName();
    }

    public function getCouponTypeColumns(): array
    {
        return TonicsCouponActivator::$TABLES[TonicsCouponActivator::COUPON_TYPE];
    }

    /**
     * @throws \Exception
     */
    public static function getCouponTableJoiningRelatedColumns(): string
    {
        $couponRootPath = CouponSettingsController::getTonicsCouponRootPath();
        $couponTableName = TonicsCouponActivator::couponTableName();
        return  table()->pick(
                [
                    $couponTableName => ['coupon_id', 'slug_id', 'coupon_name', 'coupon_slug', 'coupon_status', 'field_settings', 'created_at', 'started_at', 'expired_at', 'updated_at', 'image_url'],
                    Tables::getTable(Tables::USERS) => ['user_name', 'email']
                ]
            )
            . ", DATE_FORMAT($couponTableName.started_at, '%d %M, %Y') AS coupon_validity_start, DATE_FORMAT($couponTableName.expired_at, '%d %M, %Y') AS coupon_validity_end"
            . ", JSON_UNQUOTE(JSON_EXTRACT($couponTableName.field_settings, '$.coupon_label')) as coupon_label"
            . ", JSON_UNQUOTE(JSON_EXTRACT($couponTableName.field_settings, '$.coupon_url')) as coupon_out_url"
            . ", JSON_UNQUOTE(JSON_EXTRACT($couponTableName.field_settings, '$.seo_description')) as seo_description"
            . ', GROUP_CONCAT(CONCAT(coupon_type_id, "::", coupon_type_slug ) ) as fk_coupon_type_id'
            . ", CONCAT('/admin/tonics_coupon/', coupon_slug, '/edit') as _edit_link, CONCAT_WS('/', '/$couponRootPath', $couponTableName.slug_id, coupon_slug) as _preview_link ";
    }

    /**
     * @param $coupon
     * @param string $fieldSettingsKey
     * @return void
     * @throws \Exception
     */
    public function unwrapForCoupon(&$coupon, string $fieldSettingsKey = 'field_settings'): void
    {
        $fieldSettings = json_decode($coupon[$fieldSettingsKey], true);
        $this->getFieldData()->unwrapFieldContent($fieldSettings, contentKey: 'coupon_content');
        $coupon = [...$fieldSettings, ...$coupon];
    }

    public function getCouponColumns(): array
    {
        return TonicsCouponActivator::$TABLES[TonicsCouponActivator::COUPON];
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData|null $fieldData
     */
    public function setFieldData(?FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @return OnCouponDefaultField|null
     */
    public function getOnCouponDefaultField(): ?OnCouponDefaultField
    {
        return $this->onCouponDefaultField;
    }

    /**
     * @param OnCouponDefaultField|null $onCouponDefaultField
     */
    public function setOnCouponDefaultField(?OnCouponDefaultField $onCouponDefaultField): void
    {
        $this->onCouponDefaultField = $onCouponDefaultField;
    }

    /**
     * @return OnCouponTypeDefaultField|null
     */
    public function getOnCouponTypeDefaultField(): ?OnCouponTypeDefaultField
    {
        return $this->onCouponTypeDefaultField;
    }

    /**
     * @param OnCouponTypeDefaultField|null $onCouponTypeDefaultField
     */
    public function setOnCouponTypeDefaultField(?OnCouponTypeDefaultField $onCouponTypeDefaultField): void
    {
        $this->onCouponTypeDefaultField = $onCouponTypeDefaultField;
    }
}