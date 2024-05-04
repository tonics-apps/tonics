<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Modules\Core\Library\Router;

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