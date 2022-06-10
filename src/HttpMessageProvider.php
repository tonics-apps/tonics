<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App;

use App\Modules\Core\Library\SimpleState;
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
             SimpleState::displayErrorMessage($e->getCode(),  $e->getMessage());
             // SimpleState::displayErrorMessage($e->getCode(),  $e->getMessage() . $e->getTraceAsString());
        }
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}