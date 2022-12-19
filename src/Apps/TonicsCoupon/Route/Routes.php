<?php

namespace App\Apps\TonicsCoupon\Route;

use App\Apps\TonicsCoupon\Controllers\CouponController;
use App\Apps\TonicsCoupon\Controllers\CouponSettingsController;
use App\Apps\TonicsCoupon\Controllers\CouponTypeController;
use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Core\RequestInterceptor\AppAccess;
use App\Modules\Core\RequestInterceptor\PreProcessFieldDetails;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function routeWeb(Route $route): Route
    {
        $couponRootPath = CouponSettingsController::getTonicsCouponRootPath();
        $couponTypeRootPath = CouponSettingsController::getTonicsCouponTypeRootPath();

         $route->get("$couponRootPath/:id/", [CouponController::class, 'redirect']);
         $route->get("$couponRootPath/:slug-id/:slug", [CouponController::class, 'singleCoupon']);
         $route->get("$couponTypeRootPath/:id/", [CouponTypeController::class, 'redirect']);
         $route->get("$couponTypeRootPath/:slug-id/:slug", [CouponTypeController::class, 'singleCouponType']);

        $route->group('/feed', function (Route $route) use ($couponTypeRootPath, $couponRootPath) {
            $route->get("$couponRootPath/$couponTypeRootPath/:coupon-type-name", [CouponTypeController::class, 'rssCouponType']);
        });

        ## For WEB
        $route->group('/admin/tonics_coupon', function (Route $route){

            #---------------------------------
            # POST RESOURCES...
            #---------------------------------
            $route->get('', [CouponController::class, 'index'],  alias: 'index');
            $route->post('', [CouponController::class, 'dataTable'],  alias: 'dataTables');

            $route->post('store', [CouponController::class, 'store'], [PreProcessFieldDetails::class]);
            $route->get('create', [CouponController::class, 'create'], alias: 'create');
            $route->get(':coupon/edit', [CouponController::class, 'edit'], alias: 'edit');
            $route->match(['post', 'put'], ':coupon/update', [CouponController::class, 'update'], [PreProcessFieldDetails::class]);
            $route->post( ':coupon/trash', [CouponController::class, 'trash'], alias: 'trash');
            $route->post( '/trash/multiple', [CouponController::class, 'trashMultiple'], alias: 'trashMultiple');
            $route->match(['post', 'delete'], ':coupon/delete', [CouponController::class, 'delete'], alias: 'delete');
            $route->match(['post', 'delete'], 'delete/multiple', [CouponController::class, 'deleteMultiple'], alias: 'deleteMultiple');

            $route->get('import-coupon-items', [CouponController::class, 'importCouponItems'], alias: 'importCouponItems');
            $route->post('import-coupon-items', [CouponController::class, 'importCouponItemsStore'], alias: 'importCouponItems');

            #---------------------------------
            # POST CATEGORIES...
            #---------------------------------
            $route->group('/type', function (Route $route){
                $route->get('', [CouponTypeController::class, 'index'], alias: 'index');
                $route->post('', [CouponTypeController::class, 'dataTable'], alias: 'dataTables');

                $route->get(':couponType/edit', [CouponTypeController::class, 'edit'], alias: 'edit');
                $route->get('create', [CouponTypeController::class, 'create'], alias: 'create');
                $route->post('store', [CouponTypeController::class, 'store'], [PreProcessFieldDetails::class]);
                $route->post(':couponType/trash', [CouponTypeController::class, 'trash']);
                $route->post( '/trash/multiple', [CouponTypeController::class, 'trashMultiple'], alias: 'trashMultiple');
                $route->match(['post', 'put', 'patch'], ':couponType/update', [CouponTypeController::class, 'update'], [PreProcessFieldDetails::class]);
                $route->match(['post', 'delete'], ':couponType/delete', [CouponTypeController::class, 'delete']);
            }, alias: 'Type');

        },AuthConfig::getAuthRequestInterceptor([AppAccess::class]), 'tonicsCoupon');

        $route->group('/admin/tools/apps', function (Route $route) {
            $route->get('tonics_coupon/settings', [CouponSettingsController::class, 'edit'], alias: 'tonicsCoupon.settings');
            $route->post('tonics_coupon/settings', [CouponSettingsController::class, 'update']);
        }, AuthConfig::getAuthRequestInterceptor());

        return $route;
    }

    /**
     * @throws \ReflectionException
     */
    public function routeApi(Route $routes): Route
    {
        $routes->group('/api', function (Route $route){

        });

        return $routes;
    }
}