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
    private int $every = 60;
    private int $parallel = 1;
    private array $chains = [];
    private mixed $data = null;

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
     * @return AbstractSchedulerInterface
     */
    public function setName(string $name): AbstractSchedulerInterface
    {
        $this->name = $name;
        return $this;
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
     * @return AbstractSchedulerInterface
     */
    public function setPriority(int $priority): AbstractSchedulerInterface
    {
        $this->priority = $priority;
        return $this;
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
     * @return AbstractSchedulerInterface
     */
    public function setParallel(int $parallel): AbstractSchedulerInterface
    {
        $this->parallel = $parallel;
        return $this;
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
     * @return AbstractSchedulerInterface
     */
    public function setChains(array $chains): AbstractSchedulerInterface
    {
        $this->chains = $chains;
        return $this;
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
     * @return AbstractSchedulerInterface
     */
    public function setEvery(int $every): AbstractSchedulerInterface
    {
        $this->every = $every;
        return $this;
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
     * @return AbstractSchedulerInterface
     */
    public function setParent(?AbstractSchedulerInterface $parent): AbstractSchedulerInterface
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getDataAsArray(): array
    {
        return (array)$this->data;
    }

    /**
     * @return object
     */
    public function getDataAsObject(): object
    {
        return (object)$this->data;
    }

    /**
     * @param mixed $data
     * @return static
     */
    public function setData(mixed $data): static
    {
        $this->data = $data;
        return $this;
    }
}