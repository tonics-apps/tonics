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

namespace App\Modules\Core\Jobs;

use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use App\Modules\Core\States\UpdateMechanismState;

class HandleOnUpdate extends AbstractJobInterface implements JobHandlerInterface
{
    public function __construct(string $classString = '', $timestamp = null)
    {
        $this->setData(['activator' => $classString, 'timestamp' => $timestamp]);
        $this->setJobName('HandleOnUpdate');
    }

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        try {

            if (isset($this->getDataAsArray()['timestamp'])) {
                $timestamp = $this->getDataAsArray()['timestamp'];
                $restartTimestamp = UpdateMechanismState::getBinRestartServiceTimestamp();
                # If the timestamp and restartTimestamp is same, then it means the system hasn't restarted and thus not safe to run onUpdate
                if ((is_int($timestamp) && is_int($restartTimestamp)) && $timestamp === $restartTimestamp) {
                    $this->infoMessage('Not Safe Yet To Run OnUpdate, Re-Queueing');
                    $this->setJobStatusAfterJobHandled(Job::JobStatus_Queued);
                    return;
                }
            }

            if (isset($this->getDataAsArray()['activator'])) {
                $activator = $this->getDataAsArray()['activator'];
                /** @var ExtensionConfig $activator */
                $activator = container()->get($activator);
                $activator->onUpdate();
                return;
            }


        } catch (\Exception $e) {
            // Log...
            $this->errorMessage($e->getMessage());

        }

        $this->errorMessage("An Error Occurred Running The OnUpdate Method of The Activator");
    }
}