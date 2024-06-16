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

namespace App\Apps\TonicsCloud\Interfaces;

use App\Apps\TonicsCloud\Controllers\ImageController;
use App\Apps\TonicsCloud\Controllers\InstanceController;
use App\Apps\TonicsCloud\Events\OnAddCloudAutomationEvent;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueAddAppsContainersDb;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueContainerHasStopped;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueContainerIsRunning;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueCreateContainer;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueImageImported;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueImportImage;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueStartContainer;
use App\Apps\TonicsCloud\Services\ContainerService;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Interfaces\TonicsRouterRequestInputMethodsInterface;

abstract class CloudAutomationInterfaceAbstract implements HandlerInterface, CloudAutomationInterface
{
    const IMAGE_NGINX     = 'Nginx';
    const IMAGE_TONICS    = 'Tonics';
    const IMAGE_WORDPRESS = 'WordPress';
    protected mixed $containerID         = null;
    protected mixed $containerSlugID     = null;
    protected array $images              = [];
    protected array $imageOthers         = [];
    protected array $defaultImageVersion = [];

    public function handleEvent (object $event): void
    {
        /** @var $event OnAddCloudAutomationEvent */
        $event->addCloudAutomationHandler($this);
    }

    /**
     * @throws \Throwable
     * @throws \ReflectionException
     */
    public function automate ($data = []): void
    {
        $data['jobs'] = $this->defaultContainerCreateQueuePaths($data['input']);
        $this->createContainer($data);
    }

    /**
     * @param TonicsRouterRequestInputMethodsInterface $input
     * @param array $moreJobChildren
     *
     * @return array[]
     */
    protected function defaultContainerCreateQueuePaths (TonicsRouterRequestInputMethodsInterface $input, array $moreJobChildren = []): array
    {
        $containerQueuePath = [
            [
                'job'      => new CloudJobQueueCreateContainer(),
                'children' => [
                    [
                        'job'      => new CloudJobQueueContainerHasStopped(),
                        'children' => [
                            [
                                'job'      => new CloudJobQueueStartContainer(),
                                'children' => [
                                    [
                                        'job'      => new CloudJobQueueContainerIsRunning(),
                                        'children' => [
                                            [
                                                'job'      => new CloudJobQueueAddAppsContainersDb(),
                                                'children' => [
                                                    $moreJobChildren,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if ($input->hasValue('container_image')) {
            $jobs = [
                [
                    'job'      => new CloudJobQueueImportImage(),
                    'children' => [
                        [
                            'job'      => new CloudJobQueueImageImported(),
                            'children' => $containerQueuePath,
                        ],
                    ],
                ],
            ];
        } else {
            $jobs = $containerQueuePath;
        }

        return $jobs;
    }

    /**
     * For container to get created, input in $data, should contain atleast:
     *
     * ```
     * $data['input'] = [
     *     'container_name' => 'My Container',
     *     'container_description' => 'My Container Description', // optional
     *     'cloud_instance' => '...', // the provider_instance_id
     *      // optional, meaning it would create an empty image container
     *     'container_image' => '...', // the container_image_id
     *     // optional, it would use the latest version by default
     *     '{$containerImage}_image_version' => 'v1.26.0-bookworm', // where {containerImage} is the value of the container_image_id e.g `6_image_version`
     *     'variables' => '...', // optional
     *     'container_profiles' => [1, 2], // optional
     *     'container_device_config' => '...', // optional
     * ];
     *
     * // The data should contain the jobs
     * $data['jobs] = [...]
     * ```
     *
     * @param array $data
     * @param callable|null $onCreateContainer
     * -- This would return an array containing `containerID` and `containerSlugID` (uuid)
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function createContainer (array $data = [], callable $onCreateContainer = null): void
    {
        /** @var TonicsRouterRequestInputMethodsInterface $input */
        $input = $data['input'];
        /** @var ContainerService $containerService */
        $containerService = $data['containerService'];

        $validation = $containerService->validateContainerCreate($input);
        if ($validation->fails()) {
            $containerService->handleValidationFailureForContainerCreate($validation);
            return;
        }

        $cloudInstance = $input->retrieve('cloud_instance');
        $settings = [
            'instance_id' => $cloudInstance,
            'user_id'     => \session()::getUserID(),
        ];

        $serviceInstanceFromDB = InstanceController::GetServiceInstances($settings);
        if (empty($serviceInstanceFromDB)) {
            $containerService->setFails(true)
                ->setErrors(["You Either Don't Own This Instance or Something Serious Went Wrong"])
                ->setRedirectsRoute(route('tonicsCloud.containers.create'));
            return;
        }

        db(onGetDB: function (TonicsQuery $db) use ($onCreateContainer, $containerService, $serviceInstanceFromDB, $input, $data) {
            $db->beginTransaction();

            $containerTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINERS);
            $containerImage = null;
            if ($input->hasValue('container_image')) {
                $containerImage = $input->retrieve('container_image');
            }

            $variables = $containerService->getContainerVariables($input);

            $imageVersion = $input->retrieve("{$containerImage}_image_version");
            $containerProfiles = array_filter((array)$input->retrieve('container_profiles', []));
            $containerName = $input->retrieve('container_name');
            $containerDescription = $input->retrieve('container_description');
            $containerDeviceConfig = $input->retrieve('container_devices_config');
            $containerReturning = $db->InsertReturning($containerTable, [
                'container_name' => $containerName, 'container_description' => $containerDescription, 'service_instance_id' => $serviceInstanceFromDB->service_instance_id,
                'others'         => json_encode(
                    [
                        'container_image'         => $containerImage,
                        'image_version'           => $imageVersion,
                        'container_profiles'      => $containerProfiles,
                        'container_device_config' => $containerDeviceConfig,
                        'container_variables'     => $variables,
                        'variables'               => $input->retrieve('variables'),
                    ]),
            ], ['container_id', 'slug_id'], 'container_id');

            if ($onCreateContainer) {
                $onCreateContainer([
                    'containerID'     => $containerReturning->container_id,
                    'containerSlugID' => $containerReturning->slug_id,
                ]);
            }

            $this->containerID = $containerReturning->container_id;
            $this->containerSlugID = $containerReturning->slug_id;

            $jobData = [
                'instance_id'              => $input->retrieve('cloud_instance'),
                'container_id'             => $containerReturning->container_id,
                'container_unique_slug_id' => $containerReturning->slug_id,
                'container_image'          => ImageController::getImageData($containerImage),
                'container_profiles'       => ContainerService::getProfiles($containerProfiles),
                'container_device_config'  => $containerDeviceConfig,
                'container_variables'      => $variables,
                'image_version'            => $imageVersion,
            ];

            $jobs = $data['jobs'];

            TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);

            $db->commit();
        });

        $containerService->setFails(false)
            ->setMessage('Container Creation Enqueued, Refresh For Changes in Few Seconds')
            ->setRedirectsRoute(route('tonicsCloud.containers.index'));
    }

    /**
     * Example Usage:
     *
     * ```
     * createProxyContainerIfNecessary(
     *     $serviceInstanceObject,
     *     [xxx, xxx], // $containersToProxyTo
     *     [
     *         'proxyEmail' => 'example@email.com', // would be used for the ACME Setup
     *         'proxyJobCallback' => function($cloudInstance, $allPlacesToProxyToIncludingExistingOneInServiceInstanceObject) {
     *             // other setup and return job that should finalize the proxy setup
     *             return $job
     *         }
     * )
     * ```
     *
     * @param \stdClass $serviceInstance
     * @param array $containersToProxyTo
     * @param array $data
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function createProxyContainerIfNecessary (\stdClass $serviceInstance, array $containersToProxyTo, array $data): void
    {
        $cloudInstance = $serviceInstance->provider_instance_id;
        /** @var TonicsRouterRequestInputMethodsInterface $input */
        $input = $data['input'];
        /** @var ContainerService $containerService */
        $containerService = $data['containerService'];
        $email = $data['proxyEmail'];
        $proxyJobCallback = $data['proxyJobCallback'];

        $serviceInstanceOthers = json_decode($serviceInstance->others);
        # Proxy Configuration Queue
        if (!isset($serviceInstanceOthers->container_proxy_to)) {
            $serviceInstanceOthers->container_proxy_to = [];
        }
        $serviceInstanceOthers->container_proxy_to = [...$containersToProxyTo, ...$serviceInstanceOthers->container_proxy_to];

        $proxyJob = $proxyJobCallback($cloudInstance, $serviceInstanceOthers->container_proxy_to);

        if (!isset($serviceInstanceOthers->containerProxy)) {
            $createProxyContainer = true;
        } else {
            $createProxyContainer = empty(ContainerService::getContainer($serviceInstanceOthers->containerProxy));
        }

        # There is no proxy yet, create one
        if ($createProxyContainer) {

            $proxyInput = $input->all();
            $containerName = $proxyInput['container_name'] . '[Proxy]';
            $proxyInput['container_name'] = helper()->strLimit($containerName, 220);
            $proxyInput['container_image'] = $this->getImageID(self::IMAGE_NGINX);
            $proxyInput['variables'] = <<<VARIABLES
ACME_EMAIL={$email}
VARIABLES;
            $proxyInput['container_profiles'] = $this->getProfilesForProxyContainer();
            $data['input'] = input()->fromPost($proxyInput);
            $data['jobs'] = $this->defaultContainerCreateQueuePaths($data['input'], ['job' => $proxyJob]);
            $containerID = null;
            $this->createContainer($data, function ($createdData) use (&$containerID) {
                $containerID = $createdData['containerID'];
            });

            if ($containerService->fails() === false) {
                $serviceInstanceOthers->containerProxy = $containerID;
                InstanceController::updateInstanceServiceOthers($serviceInstanceOthers, $cloudInstance);
            }

        } else {
            TonicsCloudActivator::getJobQueue()->enqueue($proxyJob);
        }
    }

    /**
     * @param string $imageName
     *
     * @return mixed
     * @throws \Exception
     */
    public function getImageID (string $imageName): mixed
    {
        if (isset($this->images[$imageName])) {
            return $this->images[$imageName];
        }

        $image = ContainerService::getContainerImageByName($imageName);
        $imageID = $image?->container_image_id;
        if ($imageID) {
            $this->images[$imageName] = $imageID;
            $this->imageOthers[$imageName] = json_decode($image->others);
            return $imageID;
        }

        return null;
    }

    /**
     * This gets the latest version of an image
     *
     * @param string $imageName
     *
     * @return int|string|null
     * @throws \Exception
     */
    public function getImageVersion (string $imageName): int|string|null
    {
        $imageOthers = null;
        if (isset($this->images[$imageName])) {
            $imageOthers = $this->imageOthers[$imageName];
        } else {
            $image = ContainerService::getContainerImageByName($imageName);
            $this->images[$imageName] = $image?->container_image_id;
            $this->imageOthers[$imageName] = json_decode($image->others);
            $imageOthers = $this->imageOthers[$imageName];
        }
        
        if (is_object($imageOthers)) {
            # Get the properties of the object
            $properties = get_object_vars($imageOthers->images);
            # Get the last property key
            return array_key_last($properties);
        }

        return null;
    }

    /**
     * @throws \Exception
     */
    public function getProfilesForProxyContainer ()
    {
        $profiles = ContainerService::getProfilesByName(['Proxy Protocol 80 - HTTP', 'Proxy Protocol 443 - HTTPS']);
        return array_map(fn($profile) => $profile->container_profile_id, $profiles);
    }

    /**
     * Example Usage:
     *
     * ```
     * $keyMapping = [
     *     'user_name' => 'name',
     *     'user_email' => 'email',
     *     'user_password' => 'password'
     * ];
     *
     * $sites = $this->mapInputToArray($input, $keyMapping);
     *
     * // Output
     * [
     *     [
     *         'name' => 'JohnDoe',
     *         'email' => 'john@example.com',
     *         'password' => 'securepassword123',
     *     ],
     *     [
     *         'name' => 'JaneDoe',
     *         'email' => 'jane@example.com',
     *         'password' => 'anotherpassword456',
     *     ],
     * ]
     * ```
     *
     * @param TonicsRouterRequestInputMethodsInterface $input
     * @param array $keyMapping
     *
     * @return array
     */
    protected function mapInputToArray (TonicsRouterRequestInputMethodsInterface $input, array $keyMapping): array
    {
        $inputData = [];
        $maxLength = 0;

        # Retrieve input data and calculate the maximum length of the arrays
        foreach ($keyMapping as $inputKey => $outputKey) {
            $data = $input->retrieve($inputKey);
            if (!is_array($data)) {
                continue;
            }
            $inputData[$inputKey] = $data;
            $maxLength = max($maxLength, count($data));
        }

        $mapped = [];
        # Prepare mapped array based on key mapping
        for ($i = 0; $i < $maxLength; $i++) {
            $site = [];
            foreach ($keyMapping as $inputKey => $outputKey) {
                $site[$outputKey] = $inputData[$inputKey][$i] ?? '';
            }
            $mapped[] = $site;
        }

        return $mapped;
    }
}