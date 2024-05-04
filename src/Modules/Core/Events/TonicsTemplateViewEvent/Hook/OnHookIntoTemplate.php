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

namespace App\Modules\Core\Events\TonicsTemplateViewEvent\Hook;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\TonicsView;


class OnHookIntoTemplate implements EventInterface
{
    private array $hookInto = [];

    private TonicsView $tonicsView;

    public function __construct(TonicsView $tonicsView)
    {
        $this->tonicsView = $tonicsView;
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    public function hookInto(string $name, callable $handler): static
    {
        $this->hookInto[] = [
          'hook_into' => $name,
          'handler' => function() use ($handler) {
            return $handler($this->getTonicsView());
          },
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getHookInto(): array
    {
        return $this->hookInto;
    }

    /**
     * @return TonicsView
     */
    public function getTonicsView(): TonicsView
    {
        return $this->tonicsView;
    }
}