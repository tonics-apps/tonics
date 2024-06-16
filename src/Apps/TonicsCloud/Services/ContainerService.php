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

namespace App\Apps\TonicsCloud\Services;

use App\Apps\TonicsCloud\EventHandlers\CloudAutomationsHandler\TonicsContainerDefaultAutomation;
use App\Apps\TonicsCloud\Events\OnAddCloudAutomationEvent;
use App\Apps\TonicsCloud\Interfaces\CloudAutomationInterface;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueContainerHasStopped;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueContainerIsRunning;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueDeleteContainer;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueStartContainer;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueStopContainer;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueUpdateContainer;
use App\Apps\TonicsCloud\Library\Incus\Client;
use App\Apps\TonicsCloud\Library\Incus\URL;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\AbstractService;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInputMethodsInterface;
use Devsrealm\TonicsValidation\Validation;

class ContainerService extends AbstractService
{
    public function __construct () {}

    /**
     * @param string $imageName
     *
     * @return mixed
     * @throws \Exception
     */
    public static function getContainerImageByName (string $imageName): mixed
    {
        $image = null;
        db(onGetDB: function (TonicsQuery $db) use ($imageName, &$image) {
            $containerImageTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINER_IMAGES);
            $image = $db->Select("container_image_id, container_image_name, others")
                ->From($containerImageTable)
                ->WhereEquals('container_image_name', $imageName)
                ->FetchFirst();
        });

        return $image;
    }

    /**
     * @param array $profileNames
     *
     * @return array|null
     * @throws \Exception
     */
    public static function getProfilesByName (array $profileNames): ?array
    {
        if (empty($profileNames)) {
            return null;
        }

        $profiles = null;
        db(onGetDB: function (TonicsQuery $db) use ($profileNames, &$profiles) {
            $profiles = $db->Select("*")
                ->From(TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINER_PROFILES))
                ->WhereIn('container_profile_name', $profileNames)->FetchResult();
        });
        return $profiles;
    }

    /**
     * @param $containerID
     *
     * @return mixed
     * @throws \Exception
     */
    public static function getAppsInContainer ($containerID): mixed
    {
        $containerApps = [];
        db(onGetDB: function (TonicsQuery $db) use (&$containerApps, $containerID) {
            $cloudAppTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_APPS);
            $cloudAppsToContainersTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_APPS_TO_CONTAINERS);
            $containerApps = $db->Select("id, app_status_msg, app_status, app_name, app_id")
                ->From($cloudAppTable)
                ->Join("$cloudAppsToContainersTable", "$cloudAppsToContainersTable.fk_app_id", "$cloudAppTable.app_id")
                ->WhereEquals('fk_container_id', $containerID)->FetchResult();
        });

        return $containerApps;
    }

    /**
     * This function must be called whenever you need the container name of the incus container,
     * this is because when the container was created, we use the slug_id of the container table column which
     * is a UUID, and since incus container can't start with a number and a UUID can, this function prepends `tc` as a
     * workaround.
     *
     * @param string $uuid
     *
     * @return string
     */
    public static function getIncusContainerName (string $uuid): string
    {
        return 'tc-' . $uuid;
    }

    /**
     * @param \stdClass $serviceInstance
     *
     * @return Client
     * @throws \Exception
     * @throws \Throwable
     */
    public static function getIncusClient (\stdClass $serviceInstance): Client
    {
        if (isset($serviceInstance->ip->ipv4[0]) && isset($serviceInstance->security->cert)) {

            $handler = TonicsCloudActivator::getCloudServerHandler($serviceInstance->serverHandlerName);
            $port = $handler::IncusPort();
            $certAndKey = $serviceInstance->security->cert;
            $ipv4 = $serviceInstance->ip->ipv4[0];
            return new Client(new URL("https://$ipv4:$port"), $certAndKey);
        }

        throw new \Exception("An Error Occurred Getting Incus Client Instance, A Field is Not Set in ServiceInstance Variable");
    }

    /**
     * In Incus, profiles is actually different from this, you can add a device to a profile among other things,
     * however to make things a bit easy for me, I would only limit it to config device so, that would be our definition
     * of profiles in TonicsCloud, between, these are the default ones, user can add their own config manually themselves.
     * @return array[]
     */
    public static function DEFAULT_PROFILES (): array
    {
        return [
            [
                'container_profile_name'        => 'Default',
                'container_profile_description' => 'Default Profile',
                'others'                        => json_encode([
                    "devices" => [
                        "eth0" => [
                            "type"    => "nic",
                            "name"    => "eth0",
                            "network" => "incusbr0",
                        ],
                        "root" => [
                            "type" => "disk",
                            "path" => "/",
                            "pool" => "default",
                        ],
                    ],
                    'name'    => 'TonicsCloudProfileDefault',
                ]),
            ],
            [
                'container_profile_name'        => 'Port 80 - HTTP',
                'container_profile_description' => 'Port 80 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudPort80" => [
                            "type"    => "proxy",
                            "listen"  => "tcp:0.0.0.0:80",    // listen to port on the host
                            "connect" => "tcp:127.0.0.1:80",  // proxy it to the container or instance
                        ],
                    ],
                    'name'    => 'TonicsCloudPort80',
                ]),
            ],
            [
                'container_profile_name'        => 'Port 443 - HTTPS',
                'container_profile_description' => 'Port 443 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudPort443" => [
                            "type"    => "proxy",
                            "listen"  => "tcp:0.0.0.0:443",    // listen to port on the host
                            "connect" => "tcp:127.0.0.1:443",  // proxy it to the container or instance
                        ],
                    ],
                    'name'    => 'TonicsCloudPort443',
                ]),
            ],
            [
                'container_profile_name'        => 'Port 25 - SMTP',
                'container_profile_description' => 'Port 25 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudPort25" => [
                            "type"    => "proxy",
                            "listen"  => "tcp:0.0.0.0:25",    // listen to port on the host
                            "connect" => "tcp:127.0.0.1:25",  // proxy it to the container or instance
                        ],
                    ],
                    'name'    => 'TonicsCloudPort25',
                ]),
            ],
            [
                'container_profile_name'        => 'Port 2525 - SMTP (Alternate SMTP port)',
                'container_profile_description' => 'Port 2525 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudPort2525" => [
                            "type"    => "proxy",
                            "listen"  => "tcp:0.0.0.0:2525",    // listen to port on the host
                            "connect" => "tcp:127.0.0.1:2525",  // proxy it to the container or instance
                        ],
                    ],
                    'name'    => 'TonicsCloudPort2525',
                ]),
            ],
            [
                'container_profile_name'        => 'Port 465 - SMTPS (SMTP over SSL/TLS)',
                'container_profile_description' => 'Port 465 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudPort465" => [
                            "type"    => "proxy",
                            "listen"  => "tcp:0.0.0.0:465",    // listen to port on the host
                            "connect" => "tcp:127.0.0.1:465",  // proxy it to the container or instance
                        ],
                    ],
                    'name'    => 'TonicsCloudPort465',
                ]),
            ],
            [
                'container_profile_name'        => 'Port 587 - SMTP (Submission)',
                'container_profile_description' => 'Port 587 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudPort587" => [
                            "type"    => "proxy",
                            "listen"  => "tcp:0.0.0.0:587",    // listen to port on the host
                            "connect" => "tcp:127.0.0.1:587",  // proxy it to the container or instance
                        ],
                    ],
                    'name'    => 'TonicsCloudPort587',
                ]),
            ],
            [
                'container_profile_name'        => 'Port 110 - POP3 (Post Office Protocol version 3)',
                'container_profile_description' => 'Port 110 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudPort110" => [
                            "type"    => "proxy",
                            "listen"  => "tcp:0.0.0.0:110",    // listen to port on the host
                            "connect" => "tcp:127.0.0.1:110",  // proxy it to the container or instance
                        ],
                    ],
                    'name'    => 'TonicsCloudPort110',
                ]),
            ],
            [
                'container_profile_name'        => 'Port 995 - POP3S (POP3 over SSL/TLS)',
                'container_profile_description' => 'Port 995 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudPort995" => [
                            "type"    => "proxy",
                            "listen"  => "tcp:0.0.0.0:995",    // listen to port on the host
                            "connect" => "tcp:127.0.0.1:995",  // proxy it to the container or instance
                        ],
                    ],
                    'name'    => 'TonicsCloudPort995',
                ]),
            ],
            [
                'container_profile_name'        => 'Port 143 - IMAP (Internet Message Access Protocol)',
                'container_profile_description' => 'Port 143 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudPort143" => [
                            "type"    => "proxy",
                            "listen"  => "tcp:0.0.0.0:143",    // listen to port on the host
                            "connect" => "tcp:127.0.0.1:143",  // proxy it to the container or instance
                        ],
                    ],
                    'name'    => 'TonicsCloudPort143',
                ]),
            ],
            [
                'container_profile_name'        => 'Port 993 - IMAPS (IMAP over SSL/TLS)',
                'container_profile_description' => 'Port 993 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudPort993" => [
                            "type"    => "proxy",
                            "listen"  => "tcp:0.0.0.0:993",    // listen to port on the host
                            "connect" => "tcp:127.0.0.1:993",  // proxy it to the container or instance
                        ],
                    ],
                    'name'    => 'TonicsCloudPort993',
                ]),
            ],
            [
                'container_profile_name'        => 'Port 3306 - MariaDB/MySQL',
                'container_profile_description' => 'Port 3306 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudPort3306" => [
                            "type"    => "proxy",
                            "listen"  => "tcp:0.0.0.0:3306",    // listen to port on the host
                            "connect" => "tcp:127.0.0.1:3306",  // proxy it to the container or instance
                        ],
                    ],
                    'name'    => 'TonicsCloudPort3306',
                ]),
            ],
            [
                'container_profile_name'        => 'Port 6379 - Redis',
                'container_profile_description' => 'Port 6379 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudPort6379" => [
                            "type"    => "proxy",
                            "listen"  => "tcp:0.0.0.0:6379",    // listen to port on the host
                            "connect" => "tcp:127.0.0.1:6379",  // proxy it to the container or instance
                        ],
                    ],
                    'name'    => 'TonicsCloudPort6379',
                ]),
            ],
            [
                'container_profile_name'        => 'Port 5432 - PostgreSQL',
                'container_profile_description' => 'Port 5432 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudPort5432" => [
                            "type"    => "proxy",
                            "listen"  => "tcp:0.0.0.0:5432",    // listen to port on the host
                            "connect" => "tcp:127.0.0.1:5432",  // proxy it to the container or instance
                        ],
                    ],
                    'name'    => 'TonicsCloudPort5432',
                ]),
            ],
            [
                'container_profile_name'        => 'Port 27017 - MongoDB',
                'container_profile_description' => 'Port 27017 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudPort27017" => [
                            "type"    => "proxy",
                            "listen"  => "tcp:0.0.0.0:27017",    // listen to port on the host
                            "connect" => "tcp:127.0.0.1:27017",  // proxy it to the container or instance
                        ],
                    ],
                    'name'    => 'TonicsCloudPort27017',
                ]),
            ],
            #---------------------------------
            # FOR PROXY PROTOCOL ENABLED
            #---------------------------------

            [
                'container_profile_name'        => 'Proxy Protocol 80 - HTTP',
                'container_profile_description' => 'Proxy Protocol 80 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudProxyPort80" => [
                            "type"           => "proxy",
                            "listen"         => "tcp:0.0.0.0:80",   // listen to port on the host
                            "connect"        => "tcp:127.0.0.1:80", // proxy it to the container or instance
                            "proxy_protocol" => "true",
                        ],
                    ],
                    'name'    => 'TonicsCloudProxyPort80',
                ]),
            ],
            [
                'container_profile_name'        => 'Proxy Protocol 443 - HTTPS',
                'container_profile_description' => 'Proxy Protocol 443 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudProxyPort443" => [
                            "type"           => "proxy",
                            "listen"         => "tcp:0.0.0.0:443",   // listen to port on the host
                            "connect"        => "tcp:127.0.0.1:443", // proxy it to the container or instance
                            "proxy_protocol" => "true",
                        ],
                    ],
                    'name'    => 'TonicsCloudProxyPort443',
                ]),
            ],
            [
                'container_profile_name'        => 'Proxy Protocol 25 - SMTP',
                'container_profile_description' => 'Proxy Protocol 25 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudProxyPort25" => [
                            "type"           => "proxy",
                            "listen"         => "tcp:0.0.0.0:25",   // listen to port on the host
                            "connect"        => "tcp:127.0.0.1:25", // proxy it to the container or instance
                            "proxy_protocol" => "true",
                        ],
                    ],
                    'name'    => 'TonicsCloudProxyPort25',
                ]),
            ],
            [
                'container_profile_name'        => 'Proxy Protocol 2525 - SMTP (Alternate SMTP port)',
                'container_profile_description' => 'Proxy Protocol 2525 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudProxyPort2525" => [
                            "type"           => "proxy",
                            "listen"         => "tcp:0.0.0.0:2525",   // listen to port on the host
                            "connect"        => "tcp:127.0.0.1:2525", // proxy it to the container or instance
                            "proxy_protocol" => "true",
                        ],
                    ],
                    'name'    => 'TonicsCloudProxyPort2525',
                ]),
            ],
            [
                'container_profile_name'        => 'Proxy Protocol 465 - SMTPS (SMTP over SSL/TLS)',
                'container_profile_description' => 'Proxy Protocol 465 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudProxyPort465" => [
                            "type"           => "proxy",
                            "listen"         => "tcp:0.0.0.0:465",   // listen to port on the host
                            "connect"        => "tcp:127.0.0.1:465", // proxy it to the container or instance
                            "proxy_protocol" => "true",
                        ],
                    ],
                    'name'    => 'TonicsCloudProxyPort465',
                ]),
            ],
            [
                'container_profile_name'        => 'Proxy Protocol 587 - SMTP (Submission)',
                'container_profile_description' => 'Proxy Protocol 587 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudProxyPort587" => [
                            "type"           => "proxy",
                            "listen"         => "tcp:0.0.0.0:587",   // listen to port on the host
                            "connect"        => "tcp:127.0.0.1:587", // proxy it to the container or instance
                            "proxy_protocol" => "true",
                        ],
                    ],
                    'name'    => 'TonicsCloudProxyPort587',
                ]),
            ],
            [
                'container_profile_name'        => 'Proxy Protocol 110 - POP3 (Post Office Protocol version 3)',
                'container_profile_description' => 'Proxy Protocol 110 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudProxyPort110" => [
                            "type"           => "proxy",
                            "listen"         => "tcp:0.0.0.0:110",   // listen to port on the host
                            "connect"        => "tcp:127.0.0.1:110", // proxy it to the container or instance
                            "proxy_protocol" => "true",
                        ],
                    ],
                    'name'    => 'TonicsCloudProxyPort110',
                ]),
            ],
            [
                'container_profile_name'        => 'Proxy Protocol 995 - POP3S (POP3 over SSL/TLS)',
                'container_profile_description' => 'Proxy Protocol 995 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudProxyPort995" => [
                            "type"           => "proxy",
                            "listen"         => "tcp:0.0.0.0:995",   // listen to port on the host
                            "connect"        => "tcp:127.0.0.1:995", // proxy it to the container or instance
                            "proxy_protocol" => "true",
                        ],
                    ],
                    'name'    => 'TonicsCloudProxyPort995',
                ]),
            ],
            [
                'container_profile_name'        => 'Proxy Protocol 143 - IMAP (Internet Message Access Protocol)',
                'container_profile_description' => 'Proxy Protocol 143 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudProxyPort143" => [
                            "type"           => "proxy",
                            "listen"         => "tcp:0.0.0.0:143",   // listen to port on the host
                            "connect"        => "tcp:127.0.0.1:143", // proxy it to the container or instance
                            "proxy_protocol" => "true",
                        ],
                    ],
                    'name'    => 'TonicsCloudProxyPort143',
                ]),
            ],
            [
                'container_profile_name'        => 'Proxy Protocol 993 - IMAPS (IMAP over SSL/TLS)',
                'container_profile_description' => 'Proxy Protocol 993 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudProxyPort993" => [
                            "type"           => "proxy",
                            "listen"         => "tcp:0.0.0.0:993",   // listen to port on the host
                            "connect"        => "tcp:127.0.0.1:993", // proxy it to the container or instance
                            "proxy_protocol" => "true",
                        ],
                    ],
                    'name'    => 'TonicsCloudProxyPort993',
                ]),
            ],
            [
                'container_profile_name'        => 'Proxy Protocol 3306 - MariaDB/MySQL',
                'container_profile_description' => 'Proxy Protocol 3306 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudProxyPort3306" => [
                            "type"           => "proxy",
                            "listen"         => "tcp:0.0.0.0:3306",   // listen to port on the host
                            "connect"        => "tcp:127.0.0.1:3306", // proxy it to the container or instance
                            "proxy_protocol" => "true",
                        ],
                    ],
                    'name'    => 'TonicsCloudProxyPort3306',
                ]),
            ],
            [
                'container_profile_name'        => 'Proxy Protocol 6379 - Redis',
                'container_profile_description' => 'Proxy Protocol 6379 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudProxyPort6379" => [
                            "type"           => "proxy",
                            "listen"         => "tcp:0.0.0.0:6379",   // listen to port on the host
                            "connect"        => "tcp:127.0.0.1:6379", // proxy it to the container or instance
                            "proxy_protocol" => "true",
                        ],
                    ],
                    'name'    => 'TonicsCloudProxyPort6379',
                ]),
            ],
            [
                'container_profile_name'        => 'Proxy Protocol 5432 - PostgreSQL',
                'container_profile_description' => 'Proxy Protocol 5432 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudProxyPort5432" => [
                            "type"           => "proxy",
                            "listen"         => "tcp:0.0.0.0:5432",   // listen to port on the host
                            "connect"        => "tcp:127.0.0.1:5432", // proxy it to the container or instance
                            "proxy_protocol" => "true",
                        ],
                    ],
                    'name'    => 'TonicsCloudProxyPort5432',
                ]),
            ],
            [
                'container_profile_name'        => 'Proxy Protocol 27017 - MongoDB',
                'container_profile_description' => 'Proxy Protocol 27017 From Host Instance To Container',
                'others'                        => json_encode([
                    "devices" => [
                        "tonicsCloudProxyPort27017" => [
                            "type"           => "proxy",
                            "listen"         => "tcp:0.0.0.0:27017",   // listen to port on the host
                            "connect"        => "tcp:127.0.0.1:27017", // proxy it to the container or instance
                            "proxy_protocol" => "true",
                        ],
                    ],
                    'name'    => 'TonicsCloudProxyPort27017',
                ]),
            ],
        ];
    }

    /**
     * @param array $data
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function createContainer (array $data = []): void
    {
        $input = input()->fromPost(empty($data) ? $_POST : $data);
        $onAddCloudAutomationEvent = new OnAddCloudAutomationEvent();
        event()->dispatch($onAddCloudAutomationEvent);

        /** @var CloudAutomationInterface $handler */
        $handler = $onAddCloudAutomationEvent->getCloudAutomationHandler($input->retrieve('automations', TonicsContainerDefaultAutomation::DEFAULT_NAME));
        $payLoad = [
            'containerService' => $this,
            'input'            => $input,
        ];

        $handler->automate($payLoad);
    }

    /**
     * @param array $data
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function updateContainer (array $data = []): void
    {
        $input = input()->fromPost(empty($data) ? $_POST : $data);
        $containerID = $input->retrieve('container_id');

        $validator = $this->getValidator();
        $validation = $validator->make($input->all(), $this->getContainerUpdateRule());
        if ($validation->fails()) {
            $this->setFails(true)->setErrors($validation->getErrors())->setRedirectsRoute(route('tonicsCloud.containers.edit', [$containerID]));
            return;
        }

        $return = false;
        db(onGetDB: function (TonicsQuery $db) use ($containerID, $input, &$return) {
            $containerProfiles = array_filter((array)$input->retrieve('container_profiles', []));
            $containerDeviceConfig = $input->retrieve('container_devices_config');
            $containerUniqueSlugID = $input->retrieve('slug_id');

            $variables = $this->getContainerVariables($input);

            $containerTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINERS);
            $db->Update($containerTable)
                ->Set('container_name', $input->retrieve('container_name'))
                ->Set('container_description', $input->retrieve('container_description'))
                ->Set('others', db()->JsonSet('others', '$.container_profiles', db()->JsonCompact(json_encode($containerProfiles))))
                ->Set('others', db()->JsonSet('others', '$.container_device_config', db()->JsonCompact(json_encode($containerDeviceConfig))))
                ->Set('others', db()->JsonSet('others', '$.container_variables', db()->JsonCompact(json_encode($variables))))
                ->Set('others', db()->JsonSet('others', '$.variables', db()->JsonCompact(json_encode($input->retrieve('variables')))))
                ->WhereEquals('container_id', $containerID)
                ->Exec();

            if (!empty($containerProfiles)) {
                $jobData = [
                    'container_id'             => $containerID,
                    'container_unique_slug_id' => $containerUniqueSlugID,
                    'container_profiles'       => ContainerService::getProfiles($containerProfiles),
                    'container_device_config'  => $containerDeviceConfig,
                    'container_variables'      => $variables,
                ];

                $jobs = [
                    [
                        'job'      => new CloudJobQueueUpdateContainer(),
                        'children' => [
                            [
                                'job' => new CloudJobQueueContainerIsRunning(),
                            ],
                        ],
                    ],
                ];

                TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
                $this->setFails(false)->setMessage('Container Updated Enqueued')->setRedirectsRoute(route('tonicsCloud.containers.edit', [$containerID]));
                $return = true;
            }

        });

        if (!$return) {
            $this->setFails(false)->setMessage('Container Updated')->setRedirectsRoute(route('tonicsCloud.containers.edit', [$containerID]));
        }
    }

    /**
     * @throws \Exception
     */
    protected function getContainerUpdateRule (): array
    {
        return [
            'container_name'        => ['required', 'string', 'CharLen' => ['min' => 3, 'max' => 1000]],
            'container_description' => ['string'],
            'slug_id'               => ['required', 'string'],
        ];
    }

    /**
     * @param TonicsRouterRequestInputMethodsInterface $input
     *
     * @return array
     */
    public function getContainerVariables (TonicsRouterRequestInputMethodsInterface $input): array
    {
        $variables = [];
        if ($input->hasValue('variables')) {
            $variables = $input->retrieve('variables');
            $variables = parse_ini_string($variables, true, INI_SCANNER_RAW);
            $variables = (is_array($variables)) ? $variables : [];
        }

        return $variables;
    }

    /**
     * @param array $profileIDS
     *
     * @return array|null
     * @throws \Exception
     */
    public static function getProfiles (array $profileIDS): ?array
    {
        if (empty($profileIDS)) {
            return null;
        }

        $profiles = null;

        db(onGetDB: function (TonicsQuery $db) use ($profileIDS, &$profiles) {
            $profiles = $db->Select("*")
                ->From(TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINER_PROFILES))
                ->WhereIn('container_profile_id', $profileIDS)->FetchResult();
        });
        return $profiles;
    }

    /**
     * @param $containerID
     * @param string $status
     *
     * @return void
     * @throws \Exception
     */
    public function updateContainerStatus ($containerID, string $status): void
    {
        $container = self::getContainer($containerID);
        if ($container) {
            if ($container->container_status === 'Running' && $status === 'Start') {
                return;
            }

            $jobData = [
                'container_id'             => $containerID,
                'container_unique_slug_id' => $container->slug_id,
            ];

            if ($status === 'Delete') {
                $jobs = [
                    [
                        'job'      => new CloudJobQueueStopContainer(),
                        'children' => [
                            [
                                'job'      => new CloudJobQueueContainerHasStopped(),
                                'children' => [
                                    ['job' => new CloudJobQueueDeleteContainer()],
                                ],
                            ],
                        ],
                    ],
                ];

                TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
            }

            if ($status === 'Force Delete') {
                # Force Deletion might be dangerous, but can be necessary for useless containers that are stubborn to get rid off,
                # it is also useful in cases where the container doesn't even exist on the incus server no more but still available in the TonicsCloud db rcords
                $jobs = [
                    [
                        'job'      => new CloudJobQueueDeleteContainer(),
                        'children' => [],
                    ],
                ];

                TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
            }

            if ($status === 'Start') {
                $jobs = [
                    [
                        'job'      => new CloudJobQueueStartContainer(),
                        'children' => [
                            [
                                'job' => new CloudJobQueueContainerIsRunning(),
                            ],
                        ],
                    ],
                ];

                TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
            }

            if ($status === 'ShutDown') {
                $jobs = [
                    [
                        'job'      => new CloudJobQueueStopContainer(),
                        'children' => [
                            [
                                'job' => new CloudJobQueueContainerHasStopped(),
                            ],
                        ],
                    ],
                ];

                TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
            }

            if ($status === 'Reboot') {
                $jobs = [
                    [
                        'job'      => new CloudJobQueueStopContainer(),
                        'children' => [
                            [
                                'job'      => new CloudJobQueueContainerHasStopped(),
                                'children' => [
                                    [
                                        'job'      => new CloudJobQueueStartContainer(),
                                        'children' => [
                                            ['job' => new CloudJobQueueContainerIsRunning()],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ];

                TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
            }

        }
        return;
    }

    /**
     * @param $containerID         -- The ID or SlugID, depending on what you have in $col
     * @param bool $withCustomerID -- If true, This would get the container only if it its own by the active session user, in cli, this would be false by default
     * @param string $col          -- column to use to find $containerID
     *
     * @return mixed
     * @throws \Exception
     */
    public static function getContainer ($containerID, bool $withCustomerID = true, string $col = 'container_id'): mixed
    {
        $container = null;
        db(onGetDB: function (TonicsQuery $db) use ($col, $containerID, &$container, $withCustomerID) {
            $containerTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINERS);
            $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);

            $container = $db->Select("container_id, $containerTable.slug_id, 
            $containerTable.container_status, container_name, container_description, 
            $serviceInstanceTable.service_instance_id, $serviceInstanceTable.others as serviceInstanceOthers, $containerTable.others as containerOthers")
                ->From($containerTable)
                ->Join("$serviceInstanceTable", "$serviceInstanceTable.service_instance_id", "$containerTable.service_instance_id")
                ->when($withCustomerID, function (TonicsQuery $db) use ($serviceInstanceTable) {
                    if (helper()->isCLI() === false) {
                        $db->WhereEquals("$serviceInstanceTable.fk_customer_id", \session()::getUserID());
                    }
                })
                ->WhereNull("$serviceInstanceTable.end_time")
                ->WhereNull("$containerTable.end_time")
                ->WhereEquals(table()->pick([TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINERS) => [$col]]), $containerID)->FetchFirst();
        });

        return $container;

    }

    /**
     * @throws \Exception
     */
    public function deleteContainer ($containerID, string $status): void
    {
        $container = self::getContainer($containerID);

        if ($container) {

            $jobData = [
                'container_id'             => $container->container_id,
                'container_unique_slug_id' => $container->slug_id,
            ];

            if ($status === 'Force Delete') {
                # Force Deletion might be dangerous, but can be necessary for useless containers that are stubborn to get rid off,
                # it is also useful in cases where the container doesn't even exist on the incus server no more but still available in the TonicsCloud db rcords
                $jobs = [
                    [
                        'job'      => new CloudJobQueueDeleteContainer(),
                        'children' => [],
                    ],
                ];
            } else {
                $jobs = [
                    [
                        'job'      => new CloudJobQueueStopContainer(),
                        'children' => [
                            [
                                'job'      => new CloudJobQueueContainerHasStopped(),
                                'children' => [
                                    ['job' => new CloudJobQueueDeleteContainer()],
                                ],
                            ],
                        ],
                    ],
                ];
            }

            TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
        }
    }

    /**
     * Here is an example usage:
     *
     * ```
     * [
     *      'ACME_EMAIL' => $email,
     *      ...
     * ]
     * ```
     *
     * @param array $mappers
     *
     * @return string
     */
    public function createContainerVariables (array $mappers): string
    {
        $string = '';
        foreach ($mappers as $key => $mapper) {
            $string .= "$key={$mapper}\n";
        }
        return $string;
    }

    /**
     * @param array $inputs
     * @param array $rules
     *
     * @return Validation
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function validateGeneral (array $inputs, array $rules): Validation
    {
        $validator = $this->getValidator();
        return $validator->make($inputs, $rules);
    }

    /**
     * @param TonicsRouterRequestInputMethodsInterface $input
     *
     * @return Validation
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function validateContainerCreate (TonicsRouterRequestInputMethodsInterface $input): Validation
    {
        $validator = $this->getValidator();
        $validator->changeErrorMessage(['cloud_instance:required' => 'Choose Instance To Place Container Into or Deploy a New One']);
        return $validator->make($input->all(), $this->getContainerCreateRule());
    }

    /**
     * @throws \Exception
     */
    public function getContainerCreateRule (): array
    {
        return [
            'container_name'        => ['required', 'string', 'CharLen' => ['min' => 3, 'max' => 1000]],
            'container_description' => ['string'],
            'cloud_instance'        => ['required', 'string'],
        ];
    }

    /**
     * @param Validation $validator
     *
     * @return void
     * @throws \Throwable
     */
    public function handleValidationFailureForContainerCreate (Validation $validator): void
    {
        $this->setFails(true)
            ->setErrors($validator->getErrors())
            ->setRedirectsRoute(route('tonicsCloud.containers.create'));
    }
}