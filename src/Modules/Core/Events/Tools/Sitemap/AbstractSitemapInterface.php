<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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
    public function getDataCount(): ?int
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
    public function getData(): array
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
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnAddSitemap */
        $event->addSitemapHandler($this);
    }
}