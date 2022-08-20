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
 * then depending on whether the transporter is async or sync, the object is then dispatched to handlers that want to handle the event.
 *
 * If async, you call them with a worker or directly if you prefer...
 *
 */
class Job
{

    private string $transporterName = '';
    private object|null $transporter = null;

    private OnAddJobTransporter $onAddJobTransporter;

    /**
     * @throws \Exception
     */
    public function __construct(string $transporterName = '')
    {
        $this->transporterName = $transporterName;
        $this->onAddJobTransporter = event()->dispatch(new OnAddJobTransporter())->event();
        $this->setTransport($transporterName);
    }

    /**
     * @param string $transporterName
     * @throws \Exception
     */
    public function setTransport(string $transporterName): void
    {
        if ($this->getOnAddJobTransporter()->exist($transporterName)){
            $this->transporterName = $transporterName;
            $this->transporter = $this->getOnAddJobTransporter()->getTransporter($transporterName);
        }
    }

    public function enqueue(object $event): void
    {
        $this->transporter->enqueue($event);
        if ($this->transporter->isStatic()){
            $this->runJob();
        }
    }

    public function runJob(): void
    {
        $this->transporter->runJob();
    }

    /**
     * @return object
     * @throws \Exception
     */
    private function getTransporter(): EventDispatcherInterface&JobTransporterInterface
    {
        return $this->transporter;
    }

    /**
     * @param EventDispatcherInterface&JobTransporterInterface $transporter
     */
    private function setTransporter(EventDispatcherInterface&JobTransporterInterface $transporter): void
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
     * @return OnAddJobTransporter
     */
    private function getOnAddJobTransporter(): OnAddJobTransporter
    {
        return $this->onAddJobTransporter;
    }

    /**
     * @param OnAddJobTransporter $onAddJobTransporter
     */
    private function setOnAddJobTransporter(OnAddJobTransporter $onAddJobTransporter): void
    {
        $this->onAddJobTransporter = $onAddJobTransporter;
    }


}