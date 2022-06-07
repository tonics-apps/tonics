<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Library\Router;

use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterResolverInterface;
use Exception;

class RouteResolver implements TonicsRouterResolverInterface
{

    private ?Container $container = null;

    public function __construct(Container $container = null)
    {
        if ($container){
            $this->container = $container;
        }
    }

    /**
     * @throws Exception
     */
    public function resolveClass(string $class): mixed
    {

        if (class_exists($class) === false) {
            throw new Exception("Class $class does not exist", 404);
        }

        try {
            return $this->container->get($class);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function resolveThroughClassMethod($class, string $method, array $parameters): mixed
    {
        try {
            return $this->container->call([$class, $method], $parameters, true);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function resolveThroughClosure(callable $closure, array $parameters): mixed
    {
        try {
            return $this->getContainer()->call($closure, $parameters);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
}