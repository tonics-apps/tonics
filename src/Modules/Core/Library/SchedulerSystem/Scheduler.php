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

use App\Modules\Core\Events\OnAddSchedulerTransporter;

class Scheduler
{
    const PRIORITY_EXTREME = 255; # Extreme should only be for core schedules...
    const PRIORITY_URGENT = 10;
    const PRIORITY_HIGH = 7;
    const PRIORITY_MEDIUM = 5;
    const PRIORITY_LOW = 3;

    private $onAddSchedulerTransporter;
    private string $transporterName = '';
    private object|null $transporter = null;

    /**
     * @throws \Exception
     */
    public function __construct(string $transporterName = '')
    {
        $this->transporterName = $transporterName;
        $this->onAddSchedulerTransporter = event()->dispatch(new OnAddSchedulerTransporter())->event();
        $this->setTransport($transporterName);
    }

    public function setTransport(string $transporterName): void
    {
        if ($this->getOnAddSchedulerTransporter()->exist($transporterName)){
            $this->transporterName = $transporterName;
            $this->transporter = $this->getOnAddSchedulerTransporter()->getTransporter($transporterName);
        }
    }

    /**
     * @param AbstractSchedulerInterface $event
     * @return void
     * @throws \Exception
     */
    public function enqueue(AbstractSchedulerInterface $event): void
    {
        $this->transporter->enqueue($event);
    }

    public function runSchedule(): void
    {
        $this->transporter->runSchedule();
    }

    /**
     * A second by default, to add 5 seconds, pass 5
     * @param int|null $second
     * @return int
     * Return in seconds
     */
    public static function everySecond(int $second = null): int
    {
        if ($second){
            return $second;
        }
        return 1;
    }

    /**
     * A minute by default, to add 5 minute, pass 5
     * @param int|null $minute
     * @return int
     * Return in seconds
     */
    public static function everyMinute(int $minute = null): int
    {
        if ($minute){
            return $minute * 60;
        }
        return 60;
    }

    /**
     * An hour by default, to add 2 hours, pass 2
     * @param int|null $hour
     * @return int
     * Return in seconds
     */
    public static function everyHour(int $hour = null): int
    {
        if ($hour){
            return $hour * 3600;
        }
        return 3600;
    }

    /**
     * @param int|null $day
     * @return int
     * Return in seconds
     */
    public static function everyDay(int $day = null): int
    {
        if ($day){
            return $day * 86400;
        }
        return 86400;
    }

    /**
     * @return mixed
     */
    public function getOnAddSchedulerTransporter(): mixed
    {
        return $this->onAddSchedulerTransporter;
    }

    /**
     * @param mixed $onAddSchedulerTransporter
     */
    public function setOnAddSchedulerTransporter(mixed $onAddSchedulerTransporter): void
    {
        $this->onAddSchedulerTransporter = $onAddSchedulerTransporter;
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
     * @return object|null
     */
    public function getTransporter(): ?SchedulerTransporterInterface
    {
        return $this->transporter;
    }

    /**
     * @param object|null $transporter
     */
    public function setTransporter(?object $transporter): void
    {
        $this->transporter = $transporter;
    }
}