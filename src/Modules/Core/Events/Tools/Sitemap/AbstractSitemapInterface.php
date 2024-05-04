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

abstract class AbstractSitemapInterface
{
    private int $limit = 1000;
    protected ?int $dataCount = null;
    private array $data = [];

    /**
     * @return string
     * @throws \Exception
     */
    public function getName(): string
    {
        return helper()->getObjectShortClassName($this);
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return AbstractSitemapInterface
     */
    public function setLimit(int $limit): AbstractSitemapInterface
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSitemapDataCount(): ?int
    {
        return $this->dataCount;
    }

    /**
     * @return int|null
     */
    public function getSitemapNewsDataCount(): ?int
    {
        return $this->dataCount;
    }


    /**
     * @param int|null $dataCount
     * @return AbstractSitemapInterface
     */
    public function setDataCount(?int $dataCount): AbstractSitemapInterface
    {
        $this->dataCount = $dataCount;
        return $this;
    }

    /**
     * @return array
     */
    public function getSitemapData(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getSitemapNewsData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnAddSitemap */
        $event->addSitemapHandler($this);
    }
}