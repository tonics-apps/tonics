<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library\JobSystem;

use App\Modules\Core\Events\OnAddJobTransporter;
use Devsrealm\TonicsEventSystem\Interfaces\EventDispatcherInterface;

/**
 * The Job System is an event dispatcher in disguise, except that different transporter dispatches or transports
 * events differently, some might use a database in place of a queue, some might use a real queue system, etc.
 *
 * <br>
 * The way the `JobEventDispatcher` works is you first enqueue a possible job you are planning to dispatch,
 * then depending on whether the transporter is async or sync, the object is then dispatched to handlers that want to handle the event
 *
 */
class JobEventDispatcher implements EventDispatcherInterface
{

    private array $transporters = [];
    private string $transporterName = '';
    private object|null $transporter = null;

    /**
     * @throws \Exception
     */
    public function __construct(string $transporterName = '')
    {
        $this->transporterName = $transporterName;
        $transporters = $this->setTransport($transporterName);
        $this->transporters = $transporters->getTransporters();
    }

    /**
     * @param string $transporterName
     * @return object
     * @throws \Exception
     */
    public function setTransport(string $transporterName): OnAddJobTransporter
    {
        $transporters = event()->dispatch(new OnAddJobTransporter());
        if ($transporters->exist($transporterName)){
            $this->transporterName = $transporterName;
            $this->transporter = $transporters->getTransporter($transporterName);
        }

        return $transporters;
    }

    public function enqueue(object $event): void
    {
        $this->transporter->enqueue($event);
        if ($this->transporter->isStatic()){
            $this->dispatch($event);
        }
    }

    public function dispatch(object $event): object
    {
        $this->transporter->dispatch($event);
        return $event;
    }

    /**
     * @return object
     */
    public function getTransporter(): EventDispatcherInterface&TransporterInterface
    {
        return $this->transporter;
    }

    /**
     * @param EventDispatcherInterface&TransporterInterface $transporter
     */
    public function setTransporter(EventDispatcherInterface&TransporterInterface $transporter): void
    {
        $this->transporter = $transporter;
    }

    /**
     * @return string
     */
    public function getTransporterName(): string
    {
        return $this->transporterName;
    }

    /**
     * @param string $transporterName
     */
    public function setTransporterName(string $transporterName): void
    {
        $this->transporterName = $transporterName;
    }

    /**
     * @return array
     */
    public function getTransporters(): array
    {
        return $this->transporters;
    }

    /**
     * @param array $transporters
     */
    public function setTransporters(array $transporters): void
    {
        $this->transporters = $transporters;
    }
}