<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\TonicsCloud\EventHandlers\Fields;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Field\Events\OnFieldTopHTMLWrapperUserSettings;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleFieldTopHTMLWrapper  implements HandlerInterface
{

    /**
     * @throws \Throwable
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnFieldTopHTMLWrapperUserSettings */
        $data = $event->getData();
        $this->generateDNSInstruction($data);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function generateDNSInstruction($data): void
    {
        if (isset($data->inputName) && $data->inputName === 'tonicsCloud_domain_records_container') {
            $info = '';
            if (isset(getPostData()['dnsHandler'])) {
                $providerName = getPostData()['dnsHandler'];
                $DNSHandler = TonicsCloudActivator::getCloudDNSHandler($providerName);
                $nameserverList = '';
                foreach ($DNSHandler->nameServers() as $nameServer){
                    $nameserverList .="$nameServer" . "<br>";
                }
                $info =<<<HTML
If your want the domain records to take effect, please change your nameservers in your domain registrar to the following:
<br>
<br>
<code>
$nameserverList
</code>
HTML;
            }
            $data->info = $info;
        }
    }


}