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