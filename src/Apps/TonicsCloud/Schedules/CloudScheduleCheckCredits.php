<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\TonicsCloud\Schedules;

use App\Apps\TonicsCloud\Controllers\BillingController;
use App\Apps\TonicsCloud\Controllers\InstanceController;
use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\Jobs\Billing\CloudJobBillingLowCreditNotification;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SchedulerSystem\AbstractSchedulerInterface;
use App\Modules\Core\Library\SchedulerSystem\ScheduleHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudScheduleCheckCredits extends AbstractSchedulerInterface implements ScheduleHandlerInterface
{
    use ConsoleColor;

    public function __construct()
    {
        $this->setName('TonicsCloud_ScheduleCheckCredits');
        $this->setPriority(Scheduler::PRIORITY_LOW);
        $this->setEvery(Scheduler::everyMinute(1));
    }

    /**
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle(): void
    {
        if (TonicsCloudSettingsController::billingEnabled() === false) {
            $this->setEvery(Scheduler::everyDay(30)); # Delay it for extreme days
            return;
        }

        db(onGetDB: function (TonicsQuery $db){
            $db->beginTransaction();

            $helper = helper();
            $creditsTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CREDITS);
            $credits = $db->run("SELECT * FROM $creditsTable WHERE last_checked < NOW() - INTERVAL 1 HOUR LIMIT 100;");

            foreach ($credits as $credit){
                $updates = ['last_checked' => $helper->date()];
                $remainingCredit = BillingController::RemainingCredit($credit->fk_customer_id);

                if ($helper->moneyLessThan($remainingCredit, 0)){
                    # Terminate Server(s) Immediately
                    InstanceController::TerminateInstances(InstanceController::GetServiceInstances(['user_id' => $credit->fk_customer_id, 'fetch_all' => true]));
                    $updates['last_checked'] = null;
                }
                # Warn User About Low Credit
                elseif ($helper->moneyLessThan($remainingCredit, TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::NotifyIfCreditBalanceIsLessThan))){
                    $credit->others = json_decode($credit->others);
                    $updates['others'] = json_encode(['sent_time' => $helper->date()]);
                    $instance = InstanceController::GetServiceInstances(['user_id' => $credit->fk_customer_id]);
                    $jobData = [
                        'RemainingCredit' => $remainingCredit,
                        'Email' => $instance->email,
                    ];
                    $jobs = [
                        [
                            'job' => new CloudJobBillingLowCreditNotification(),
                        ]
                    ];

                    # If sent_time is not set then notification has never been sent before
                    if (!isset($credit->others->sent_time)){
                        TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
                    } else {
                        $currentDate = new \DateTime(); # Current date and time
                        $targetDate = new \DateTime($credit->others->sent_time); # Given date and time

                        # Add 24 hours to the given date
                        $targetDate->add(new \DateInterval('PT24H'));

                        # The TargetDate Has Passed 24hours, send a warning notification
                        if ($targetDate < $currentDate) {
                            TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
                        }
                    }
                }

                $db->FastUpdate($creditsTable, $updates, db()->WhereEquals('credit_id', $credit->credit_id));
            }

            $db->commit();
        });
    }

}