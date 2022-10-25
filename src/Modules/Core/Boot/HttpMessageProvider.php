<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Boot;

use App\Apps\TonicsAmazonAffiliate\Controller\TonicsAmazonAffiliateController;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Media\FileManager\LocalDriver;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsContainer\Interfaces\ServiceProvider;
use Devsrealm\TonicsRouterSystem\Handler\Router;

/**
 * Class HttpMessageProvider
 * @package App
 */
class HttpMessageProvider implements ServiceProvider
{

    private Router $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router){
        $this->router = $router;
    }

    /**
     * @param Container $container
     * @throws \Exception
     */
    public function provide(Container $container): void
    {
       // $tonicsAmazonAffiliate = new TonicsAmazonAffiliateController();
        //dd($tonicsAmazonAffiliate);
        try {
            $this->getRouter()->dispatchRequestURL();
        } catch (\Exception | \Throwable $e) {
             $redirect_to = $this->tryURLRedirection();
            $reURL = url()->getRequestURL();
             if ($redirect_to === false){
                 if (AppConfig::canLog404()){
                     try {
                         db()->Insert(
                             Tables::getTable(Tables::BROKEN_LINKS),
                             [
                                 'from' => $reURL,
                                 'to'   => null,
                             ]
                         );
                     }catch (\Exception $exception){
                         // Log..
                     }
                 }

             } else {
                 if (isset($redirect_to->to) && !empty($redirect_to->to)){
                     redirect($redirect_to->to, $redirect_to->redirection_type);
                 } else {
                     if (!empty($reURL)){
                         $hit = $redirect_to->hit ?? 1;
                         try {
                             db()->FastUpdate(
                                 Tables::getTable(Tables::BROKEN_LINKS),
                                 [
                                     '`from`' => $reURL,
                                     '`to`'   => null,
                                     '`hit`'   => ++$hit,
                                 ],
                                 db()->WhereEquals('`from`', $reURL)
                             );
                         } catch (\Exception $exception){
                             // Log..
                         }
                     }
                 }
             }

            if (AppConfig::isProduction()){
                SimpleState::displayErrorMessage($e->getCode(),  $e->getMessage());
            } else {
                SimpleState::displayErrorMessage($e->getCode(),  $e->getMessage() . $e->getTraceAsString());
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function tryURLRedirection():object|bool
    {
        try {
            $table = Tables::getTable(Tables::BROKEN_LINKS);
            $result = db()->Select('*')->From($table)->WhereEquals(table()->pickTable($table, ['from']), url()->getRequestURL())->FetchFirst();
            if (is_object($result)){
                return $result;
            }
        } catch (\Exception $exception){
            // Log..
        }

       return false;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}