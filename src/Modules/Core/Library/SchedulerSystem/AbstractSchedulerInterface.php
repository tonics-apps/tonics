<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library\SchedulerSystem;

class AbstractSchedulerInterface
{
    private string $name = '';
    private int $priority = Scheduler::PRIORITY_MEDIUM;
    private ?int $maxTicks = null;
    private int $every = 60;
    private int $parallel = 1;
    private array $chains = [];

    private ?AbstractSchedulerInterface $parent = null;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function chainsEmpty(): bool
    {
        return empty($this->chains);
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return int|null
     */
    public function getMaxTicks(): ?int
    {
        return $this->maxTicks;
    }

    /**
     * @param int|null $maxTicks
     */
    public function setMaxTicks(?int $maxTicks): void
    {
        $this->maxTicks = $maxTicks;
    }

    /**
     * @return int
     */
    public function getParallel(): int
    {
        return $this->parallel;
    }

    /**
     * @param int $parallel
     */
    public function setParallel(int $parallel): void
    {
        $this->parallel = $parallel;
    }

    /**
     * @return array
     */
    public function getChains(): array
    {
        return $this->chains;
    }

    /**
     * @param array $chains
     */
    public function setChains(array $chains): void
    {
        $this->chains = $chains;
    }

    /**
     * @return int
     */
    public function getEvery(): int
    {
        return $this->every;
    }

    /**
     * @param int $every
     */
    public function setEvery(int $every): void
    {
        $this->every = $every;
    }

    /**
     * @return AbstractSchedulerInterface|null
     */
    public function getParent(): ?AbstractSchedulerInterface
    {
        return $this->parent;
    }

    /**
     * @param AbstractSchedulerInterface|null $parent
     */
    public function setParent(?AbstractSchedulerInterface $parent): void
    {
        $this->parent = $parent;
    }

}