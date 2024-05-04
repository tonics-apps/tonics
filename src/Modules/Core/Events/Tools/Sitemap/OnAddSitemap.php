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

namespace App\Modules\Core\Events\Tools\Sitemap;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddSitemap implements EventInterface
{

    private array $sitemap = [];

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function addSitemapHandler(AbstractSitemapInterface $sitemap): static
    {
        $this->sitemap[strtolower($sitemap->getName())] = $sitemap;
        return $this;
    }

    public function exist(string $name): bool
    {
        $name = strtolower($name);
        return isset($this->sitemap[$name]);
    }

    /**
     * @return array
     */
    public function getSitemap(): array
    {
        return $this->sitemap;
    }

    /**
     * @param array $sitemap
     */
    public function setSitemap(array $sitemap): void
    {
        $this->sitemap = $sitemap;
    }

    /**
     * @throws \Exception
     */
    public function dispatchEvent(): OnAddSitemap
    {
        return event()->dispatch($this);
    }
}