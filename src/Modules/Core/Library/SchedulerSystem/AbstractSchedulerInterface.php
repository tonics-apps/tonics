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

namespace App\Modules\Core\Library\SchedulerSystem;

class AbstractSchedulerInterface
{
    private string $name = '';
    private int $priority = Scheduler::PRIORITY_MEDIUM;
    private int $every = 60;
    private int $parallel = 1;
    private array $chains = [];
    private mixed $data = null;

    private ?AbstractSchedulerInterface $parentObject = null;

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
    public function getParentObject(): ?AbstractSchedulerInterface
    {
        return $this->parentObject;
    }

    /**
     * @param AbstractSchedulerInterface|null $parentObject
     * @return AbstractSchedulerInterface
     */
    public function setParentObject(?AbstractSchedulerInterface $parentObject): AbstractSchedulerInterface
    {
        $this->parentObject = $parentObject;
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