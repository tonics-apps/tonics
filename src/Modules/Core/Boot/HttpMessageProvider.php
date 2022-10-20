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
                     try {
                         db()->Update($table)
                             ->Set('value', db()->JsonArrayAppend('value', ['$' => $urlRedirection]))
                             ->WhereEquals('`key`', 'url_redirections')
                             ->FetchFirst();
                     }catch (\Exception $exception){
                         // Log..
                     }
                 }

             } else {
                 if (isset($redirect_to->redirect_to) && !empty($redirect_to->redirect_to)){
                     redirect($redirect_to->redirect_to, $redirect_to->redirection_type);
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
            $table = Tables::getTable(Tables::GLOBAL);
            $result = db()->row(<<<SQL
SELECT redirect_to, redirection_type 
FROM $table tg, JSON_TABLE(tg.value, '$[*]' 
  columns(
   from_url  varchar(500) path '$.from', 
   redirect_to  varchar(500) path '$.to',
   redirection_type int(4) path '$.redirection_type' ) 
) as jt WHERE tg.`key` = 'url_redirections' AND from_url = ?;
SQL, url()->getRequestURL());

            if (is_object($result)){
                return $result;
            }
        } catch (\Exception){
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