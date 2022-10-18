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

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
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
        try {
            $this->getRouter()->dispatchRequestURL();
        } catch (\Exception | \Throwable $e) {
             $redirect_to = $this->tryURLRedirection();
             if ($redirect_to === false){
                 if (AppConfig::canLog404()){
                     $reURL = url()->getRequestURL();
                     $urlRedirection = [
                         'from' => $reURL,
                         'to'   => null,
                         'date' => helper()->date(),
                         'redirection_type' => 301
                     ];
                     $urlRedirection = json_encode($urlRedirection, JSON_UNESCAPED_SLASHES);
                     $table = Tables::getTable(Tables::GLOBAL);
                     $db = db();
                     try {
                         $db->Update($table)
                             ->Set('value', db()->JsonArrayAppend('value', ['$' => $urlRedirection]))
                             ->WhereEquals('`key`', 'url_redirections')
                             ->FetchFirst();
                     }catch (\Exception $exception){
                         // Log..
                     }
                 }

                 if (AppConfig::isProduction()){
                     SimpleState::displayErrorMessage($e->getCode(),  $e->getMessage());
                 }
                 SimpleState::displayErrorMessage($e->getCode(),  $e->getMessage() . $e->getTraceAsString());
             } else {
                 redirect($redirect_to, 302);
             }
        }
    }

    /**
     * @throws \Exception
     */
    public function tryURLRedirection():string|bool
    {
        $table = Tables::getTable(Tables::GLOBAL);
        $result = db()->Select()->JsonExtract('value', url()->getRequestURL())->As('redirect_to')
            ->From($table)->Where('`key`', '=', 'url_redirections')->FetchFirst();
       if (isset($result->redirect_to) && !empty($result->redirect_to)){
           return json_decode($result->redirect_to);
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