<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App;

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
        } catch (\ReflectionException | \Throwable $e) {
             $redirect_to = $this->tryURLRedirection();
             if ($redirect_to === false){
                 // SimpleState::displayErrorMessage($e->getCode(),  $e->getMessage());
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
        $requestURL = url()->getRequestURL();
       $result = db()->row(<<<SQL
SELECT JSON_EXTRACT(value, ?) AS redirect_to FROM $table WHERE `key` = 'url_redirections';
SQL, '$.' .$requestURL);
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