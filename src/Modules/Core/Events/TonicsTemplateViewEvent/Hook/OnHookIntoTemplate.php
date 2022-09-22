<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Events\TonicsTemplateViewEvent\Hook;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\TonicsView;

/**
 * The OnHookIntoTemplate Class.
 *
 * <br>
 * Note: Never ever hook into the same event in one handler,
 * for example, if you have hooked into `Core::on_data_table_data` don't repeat it in a handler, it would cause duplication, off course,
 * you can hook into different events just remember not to duplicate it.
 */
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