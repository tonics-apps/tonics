<?php

namespace App\Modules\Field\Controllers;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnFieldItemsSave implements EventInterface
{

    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
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
}