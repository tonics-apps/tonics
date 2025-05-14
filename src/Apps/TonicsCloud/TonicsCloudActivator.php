<?php
/*
 *     Copyright (c) 2024-2025. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\TonicsCloud;

use App\Apps\TonicsCloud\Commands\CloudJobQueueManager;
use App\Apps\TonicsCloud\EventHandlers\CloudAutomationsHandler\TonicsContainerDefaultAutomation;
use App\Apps\TonicsCloud\EventHandlers\CloudAutomationsHandler\TonicsContainerHarakaMailServerAutomation;
use App\Apps\TonicsCloud\EventHandlers\CloudAutomationsHandler\TonicsContainerMultipleStaticSitesAutomation;
use App\Apps\TonicsCloud\EventHandlers\CloudAutomationsHandler\TonicsContainerStandaloneStaticSiteAutomation;
use App\Apps\TonicsCloud\EventHandlers\CloudAutomationsHandler\TonicsContainerTonicsCMSAutomation;
use App\Apps\TonicsCloud\EventHandlers\CloudAutomationsHandler\TonicsContainerWordPressCMSAutomation;
use App\Apps\TonicsCloud\EventHandlers\CloudDNSHandler\LinodeCloudDNSHandler;
use App\Apps\TonicsCloud\EventHandlers\CloudMenus;
use App\Apps\TonicsCloud\EventHandlers\CloudServersHandler\LinodeCloudServerHandler;
use App\Apps\TonicsCloud\EventHandlers\CloudServersHandler\UpCloudServerHandler;
use App\Apps\TonicsCloud\EventHandlers\Fields\CloudAutomations;
use App\Apps\TonicsCloud\EventHandlers\Fields\CloudContainerImages;
use App\Apps\TonicsCloud\EventHandlers\Fields\CloudContainerProfiles;
use App\Apps\TonicsCloud\EventHandlers\Fields\CloudContainersOfInstance;
use App\Apps\TonicsCloud\EventHandlers\Fields\CloudCredit;
use App\Apps\TonicsCloud\EventHandlers\Fields\CloudInstanceInfo;
use App\Apps\TonicsCloud\EventHandlers\Fields\CloudInstances;
use App\Apps\TonicsCloud\EventHandlers\Fields\CloudPaymentMethods;
use App\Apps\TonicsCloud\EventHandlers\Fields\CloudRegions;
use App\Apps\TonicsCloud\EventHandlers\Fields\HandleFieldTopHTMLWrapper;
use App\Apps\TonicsCloud\EventHandlers\Fields\PricingTable;
use App\Apps\TonicsCloud\EventHandlers\Fields\Sanitization\RenderTonicsCloudDefaultContainerVariablesStringSanitization;
use App\Apps\TonicsCloud\EventHandlers\HandleDataTableTemplate;
use App\Apps\TonicsCloud\EventHandlers\JobQueueTransporter\DatabaseCloudJobQueueTransporter;
use App\Apps\TonicsCloud\EventHandlers\Messages\TonicsCloudAppMessage;
use App\Apps\TonicsCloud\EventHandlers\Messages\TonicsCloudContainerMessage;
use App\Apps\TonicsCloud\EventHandlers\Messages\TonicsCloudDomainMessage;
use App\Apps\TonicsCloud\EventHandlers\Messages\TonicsCloudInstanceMessage;
use App\Apps\TonicsCloud\EventHandlers\TonicsCloudPermissionRole;
use App\Apps\TonicsCloud\Events\OnAddCloudAutomationEvent;
use App\Apps\TonicsCloud\Events\OnAddCloudDNSEvent;
use App\Apps\TonicsCloud\Events\OnAddCloudJobClassEvent;
use App\Apps\TonicsCloud\Events\OnAddCloudJobQueueTransporter;
use App\Apps\TonicsCloud\Events\OnAddCloudServerEvent;
use App\Apps\TonicsCloud\Interfaces\CloudDNSInterface;
use App\Apps\TonicsCloud\Interfaces\CloudServerInterfaceAbstract;
use App\Apps\TonicsCloud\Library\JobQueue;
use App\Apps\TonicsCloud\Route\Routes;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\FieldItemsExtensionConfig;
use App\Modules\Core\Commands\OnStartUpCLI;
use App\Modules\Core\Configs\DatabaseConfig;
use App\Modules\Core\Events\OnAddConsoleCommand;
use App\Modules\Core\Events\OnAddMessageType;
use App\Modules\Core\Events\OnAddRole;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnAddFieldSanitization;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Events\OnFieldTopHTMLWrapperUserSettings;
use App\Modules\Payment\EventHandlers\TonicsCloudPaymentHandler\TonicsCloudFlutterWaveHandler;
use App\Modules\Payment\EventHandlers\TonicsCloudPaymentHandler\TonicsCloudPayPalHandler;
use App\Modules\Payment\EventHandlers\TonicsCloudPaymentHandler\TonicsCloudPayStackHandler;
use App\Modules\Payment\Events\TonicsCloud\OnAddTonicsCloudPaymentEvent;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Route;

class TonicsCloudActivator implements ExtensionConfig, FieldItemsExtensionConfig
{
    use Routes;

    const CAN_ACCESS_TONICS_CLOUD = 'TONICS_CAN_ACCESS_TONICS_CLOUD';
    const JOB_QUEUE_TRANSPORTER_DATABASE_TYPE = 'DATABASE';
    const TONICS_CLOUD_PROVIDER = 'cloud_providers';
    const TONICS_CLOUD_SERVICES = 'cloud_services';
    const TONICS_CLOUD_SERVICE_INSTANCES = 'cloud_service_instances';
    const TONICS_CLOUD_SERVICE_INSTANCE_USAGE_LOG = 'cloud_service_instance_log';
    const TONICS_CLOUD_CREDITS = 'cloud_credits';
    const TONICS_CLOUD_CONTAINERS = 'cloud_containers';
    const TONICS_CLOUD_CONTAINER_PROFILES = 'cloud_container_profiles';
    const TONICS_CLOUD_CONTAINER_IMAGES = 'cloud_container_images';
    const TONICS_CLOUD_APPS = 'cloud_apps';
    const TONICS_CLOUD_APPS_TO_CONTAINERS = 'cloud_apps_containers';
    const TONICS_CLOUD_JOBS_QUEUE = 'cloud_job_queue';
    const TONICS_CLOUD_DNS = 'cloud_dns';

    static array $TABLES = [
        self::TONICS_CLOUD_PROVIDER => ['provider_id', 'provider_name', 'provider_perm_name', 'created_at', 'updated_at'],

        self::TONICS_CLOUD_SERVICES => [
            'service_id', 'service_name', 'service_provider_id',
            'service_description', 'service_type',
            'monthly_rate', 'others',
            'created_at', 'updated_at',
        ],

        self::TONICS_CLOUD_SERVICE_INSTANCES => [
            'service_instance_id', 'slug_id', 'provider_instance_id', 'service_instance_name', 'service_instance_status', 'fk_service_id',
            'fk_customer_id', 'start_time', 'end_time', 'others', 'created_at', 'updated_at',
        ],

        self::TONICS_CLOUD_SERVICE_INSTANCE_USAGE_LOG => ['log_id', 'service_instance_id', 'log_description', 'usage_data', 'created_at', 'updated_at'],

        self::TONICS_CLOUD_CONTAINERS => [
            'container_id', 'slug_id', 'container_name', 'container_description', 'container_status',
            'service_instance_id', 'others', 'created_at', 'updated_at', 'end_time',
        ],

        self::TONICS_CLOUD_CONTAINER_PROFILES => ['container_profile_id', 'container_profile_name', 'container_profile_description', 'others', 'created_at', 'updated_at'],

        self::TONICS_CLOUD_CONTAINER_IMAGES => ['container_image_id', 'container_image_name', 'container_image_logo', 'container_image_description', 'others', 'created_at', 'updated_at'],

        self::TONICS_CLOUD_APPS => ['app_id', 'app_name', 'app_description', 'app_version', 'others', 'created_at', 'updated_at'],

        self::TONICS_CLOUD_APPS_TO_CONTAINERS => ['id', 'app_status', 'app_status_msg', 'fk_container_id', 'fk_app_id', 'others', 'created_at', 'updated_at'],

        self::TONICS_CLOUD_CREDITS => ['credit_id', 'credit_amount', 'credit_description', 'fk_customer_id', 'last_checked', 'created_at', 'updated_at'],

        self::TONICS_CLOUD_JOBS_QUEUE => [
            'job_queue_id', 'job_queue_name', 'job_queue_parent_job_id', 'job_queue_status',
            'job_queue_priority', 'job_queue_data', 'created_at', 'updated_at', 'job_retry_after', 'job_attempts',
        ],

        self::TONICS_CLOUD_DNS => ['dns_record_id', 'dns_id', 'slug_id', 'dns_domain', 'dns_status_msg', 'fk_customer_id', 'fk_provider_id', 'others', 'created_at', 'updated_at'],
    ];

    private FieldData $fieldData;

    /**
     * @param FieldData $fieldData
     */
    public function __construct(FieldData $fieldData)
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @return string[]
     */
    public static function DEFAULT_PERMISSIONS(): array
    {
        return [
            self::CAN_ACCESS_TONICS_CLOUD,
        ];
    }

    /**
     * @param string $name
     *
     * @return CloudServerInterfaceAbstract
     * @throws \Throwable
     */
    public static function getCloudServerHandler(string $name = ''): CloudServerInterfaceAbstract
    {
        /** @var OnAddCloudServerEvent $cloudServer */
        $cloudServer = event()->dispatch(new OnAddCloudServerEvent())->event();
        if ($cloudServer->exist($name)) {
            return $cloudServer->getCloudServerHandler($name);
        }

        throw new \Exception("$name is an unknown cloud server handler name");
    }

    /**
     * @param string $name
     *
     * @return CloudDNSInterface
     * @throws \Exception|\Throwable
     */
    public static function getCloudDNSHandler(string $name = ''): CloudDNSInterface
    {
        /** @var OnAddCloudDNSEvent $cloudServer */
        $cloudServer = event()->dispatch(new OnAddCloudDNSEvent())->event();
        if ($cloudServer->exist($name)) {
            return $cloudServer->getCloudDNSHandler($name);
        }

        throw new \Exception("$name is an unknown cloud dns handler name");
    }

    /**
     * @throws \Exception
     */
    public static function getJobQueue(string $type = self::JOB_QUEUE_TRANSPORTER_DATABASE_TYPE): JobQueue
    {
        return new JobQueue($type);
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return true;
    }

    /**
     * @param Route $routes
     *
     * @return Route
     * @throws \ReflectionException
     */
    public function route(Route $routes): Route
    {
        $route = $this->routeApi($routes);
        return $this->routeWeb($route);
    }

    /**
     * @inheritDoc
     */
    public function events(): array
    {
        return [
            OnStartUpCLI::class => [
                CloudJobQueueManager::class,
            ],
            OnAddConsoleCommand::class => [
                CloudJobQueueManager::class,
            ],
            OnAddCloudJobQueueTransporter::class => [
                DatabaseCloudJobQueueTransporter::class,
            ],
            OnAddCloudServerEvent::class => [
                LinodeCloudServerHandler::class,
                UpCloudServerHandler::class,
            ],
            OnAddCloudDNSEvent::class => [
                LinodeCloudDNSHandler::class,
            ],
            OnAdminMenu::class => [
                CloudMenus::class,
            ],
            OnFieldMetaBox::class => [
                PricingTable::class,
                CloudRegions::class,
                CloudInstances::class,
                CloudContainerProfiles::class,
                CloudContainerImages::class,
                CloudContainersOfInstance::class,
                CloudCredit::class,
                CloudPaymentMethods::class,
                CloudInstanceInfo::class,
                CloudAutomations::class,
            ],
            OnHookIntoTemplate::class => [
                HandleDataTableTemplate::class,
            ],
            OnAddTonicsCloudPaymentEvent::class => [
                TonicsCloudFlutterWaveHandler::class,
                TonicsCloudPayStackHandler::class,
                TonicsCloudPayPalHandler::class,
            ],
            OnAddFieldSanitization::class => [
                RenderTonicsCloudDefaultContainerVariablesStringSanitization::class,
            ],
            OnFieldTopHTMLWrapperUserSettings::class => [
                HandleFieldTopHTMLWrapper::class,
            ],
            OnAddCloudJobClassEvent::class => [

            ],
            OnAddCloudAutomationEvent::class => [
                TonicsContainerDefaultAutomation::class,
                TonicsContainerStandaloneStaticSiteAutomation::class,
                TonicsContainerMultipleStaticSitesAutomation::class,
                TonicsContainerTonicsCMSAutomation::class,
                TonicsContainerWordPressCMSAutomation::class,
                TonicsContainerHarakaMailServerAutomation::class,
            ],
            OnAddRole::class => [
                TonicsCloudPermissionRole::class,
            ],
            OnAddMessageType::class => [
                TonicsCloudDomainMessage::class,
                TonicsCloudInstanceMessage::class,
                TonicsCloudContainerMessage::class,
                TonicsCloudAppMessage::class,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function tables(): array
    {
        return [
            self::getTable(self::TONICS_CLOUD_PROVIDER) => self::$TABLES[self::TONICS_CLOUD_PROVIDER],

            self::getTable(self::TONICS_CLOUD_SERVICES) => self::$TABLES[self::TONICS_CLOUD_SERVICES],
            self::getTable(self::TONICS_CLOUD_SERVICE_INSTANCES) => self::$TABLES[self::TONICS_CLOUD_SERVICE_INSTANCES],
            self::getTable(self::TONICS_CLOUD_SERVICE_INSTANCE_USAGE_LOG) => self::$TABLES[self::TONICS_CLOUD_SERVICE_INSTANCE_USAGE_LOG],

            self::getTable(self::TONICS_CLOUD_CREDITS) => self::$TABLES[self::TONICS_CLOUD_CREDITS],

            self::getTable(self::TONICS_CLOUD_CONTAINERS) => self::$TABLES[self::TONICS_CLOUD_CONTAINERS],

            self::getTable(self::TONICS_CLOUD_APPS) => self::$TABLES[self::TONICS_CLOUD_APPS],
            self::getTable(self::TONICS_CLOUD_APPS_TO_CONTAINERS) => self::$TABLES[self::TONICS_CLOUD_APPS_TO_CONTAINERS],

            self::getTable(self::TONICS_CLOUD_CONTAINER_IMAGES) => self::$TABLES[self::TONICS_CLOUD_CONTAINER_IMAGES],

            self::getTable(self::TONICS_CLOUD_JOBS_QUEUE) => self::$TABLES[self::TONICS_CLOUD_JOBS_QUEUE],
            self::getTable(self::TONICS_CLOUD_DNS) => self::$TABLES[self::TONICS_CLOUD_DNS],
        ];
    }

    /**
     * @param string $tableName
     *
     * @return string
     */
    public static function getTable(string $tableName): string
    {
        if (!key_exists($tableName, self::$TABLES)) {
            throw new \InvalidArgumentException("`$tableName` is an invalid table name");
        }

        return DatabaseConfig::getPrefix() . $tableName;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function onInstall(): void
    {
        $this->fieldData->importFieldItems($this->fieldItems());
    }

    /**
     * @return array
     */
    public function fieldItems(): array
    {
        $json = <<<'JSON'
[
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"1auniqf61uyo000000000\",\"field_input_name\":\"tonics_cloud_main_container\",\"fieldName\":\"TonicsCloud\",\"inputName\":\"tonics_cloud_main_container\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"Ensure you enter the key for your preferred cloud provider.\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 2,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"nt465ypaqhc000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens\",\"fieldName\":\"Core\",\"inputName\":\"tonics_cloud_main_container_APITokens\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 3,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 2,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"53e3hhytn2w0000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_LinodeAkamai\",\"fieldName\":\"Linode (Akamai)\",\"inputName\":\"tonics_cloud_main_container_APITokens_LinodeAkamai\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_text",
		"field_id": 4,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 3,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2qaxz6goqls0000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_Key\",\"fieldName\":\"Key\",\"inputName\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_Key\",\"textType\":\"password\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Linode (Akamai) Token\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_select",
		"field_id": 5,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 3,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"5tbl84xae9c0000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_LinodeDeploymentOption\",\"fieldName\":\"Deploy With\",\"inputName\":\"tonics_cloud_main_container_APITokens_LinodeDeploymentOption\",\"selectData\":\"Image,StackScript\",\"defaultValue\":\"StackScript\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 6,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 3,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"3vi7mrc1x7s0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Deployment Options\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"For StackScript or Image:\\n<br>\\n- Development mode gives you ssh access, this way, you can deploy and also use ssh to test things.\\n<br>\\n- Production mode nukes ssh as it isn't needed for production, please, use the production mode in all cases, unless you know what you are doing.\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 7,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 6,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"42yq4mdwvzs0000000000\",\"field_input_name\":\"\",\"fieldName\":\"StackScript\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_select",
		"field_id": 8,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 7,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4wrgy3e0l1q0000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_LinodeStackScript_Mode\",\"fieldName\":\"Mode\",\"inputName\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_LinodeStackScript_Mode\",\"selectData\":\"Production,Development\",\"defaultValue\":\"Production\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_text",
		"field_id": 9,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 7,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2xw2lu8vyqg0000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_LinodeStackScript_SSH_PUBLIC_KEY_DEV_MODE\",\"fieldName\":\"SSH Public Key\",\"inputName\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_LinodeStackScript_SSH_PUBLIC_KEY_DEV_MODE\",\"textType\":\"password\",\"defaultValue\":\"\",\"info\":\"This is only valid for development mode, there is no ssh in production mode, the user & pass is tonics-cloud\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"An example is 'ssh-rsa AAABBB1x2y3z...\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 10,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 6,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"1jeluuzy5thc000000000\",\"field_input_name\":\"\",\"fieldName\":\"Image\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_select",
		"field_id": 11,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 10,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"6utixdfwdwk0000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_LinodeCustomImage_Mode\",\"fieldName\":\"Mode\",\"inputName\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_LinodeCustomImage_Mode\",\"selectData\":\"Production,Development\",\"defaultValue\":\"Production\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_text",
		"field_id": 12,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 10,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2ifvtp7g2rc0000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_LinodeImage\",\"fieldName\":\"Image\",\"inputName\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_LinodeImage\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Linode Image e.g private/2034922\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_text",
		"field_id": 13,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 10,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3srs91ba8k80000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_LinodeCustomImage_SSH_PUBLIC_KEY_DEV_MODE\",\"fieldName\":\"SSH Public Key\",\"inputName\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_LinodeCustomImage_SSH_PUBLIC_KEY_DEV_MODE\",\"textType\":\"password\",\"defaultValue\":\"\",\"info\":\"This is only valid for development mode, there is no ssh in production mode, the user & pass is tonics-cloud\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"An example is 'ssh-rsa AAABBB1x2y3z...\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 14,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 3,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"4wls4995j5s0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Others\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_select",
		"field_id": 15,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 14,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"jacjsw18cio000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_Backup\",\"fieldName\":\"Enable Backup\",\"inputName\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_Backup\",\"selectData\":\"1:True,0:False\",\"defaultValue\":\"1\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_text",
		"field_id": 16,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 14,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1suty7p9zof4000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_Region\",\"fieldName\":\"Region (in json)\",\"inputName\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_Region\",\"textType\":\"textarea\",\"defaultValue\":\"[{\\\"label\\\":\\\"Dallas, TX\\\",\\\"id\\\":\\\"us-central\\\"},{\\\"label\\\":\\\"Mumbai, IN\\\",\\\"id\\\":\\\"ap-west\\\"},{\\\"label\\\":\\\"Toronto, CA\\\",\\\"id\\\":\\\"ca-central\\\"},{\\\"label\\\":\\\"Sydney, AU\\\",\\\"id\\\":\\\"ap-southeast\\\"},{\\\"label\\\":\\\"Fremont, CA\\\",\\\"id\\\":\\\"us-west\\\"},{\\\"label\\\":\\\"Atlanta, GA\\\",\\\"id\\\":\\\"us-southeast\\\"},{\\\"label\\\":\\\"Newark, NJ\\\",\\\"id\\\":\\\"us-east\\\"},{\\\"label\\\":\\\"London, UK\\\",\\\"id\\\":\\\"eu-west\\\"},{\\\"label\\\":\\\"Singapore, SG\\\",\\\"id\\\":\\\"ap-south\\\"},{\\\"label\\\":\\\"Frankfurt, DE\\\",\\\"id\\\":\\\"eu-central\\\"},{\\\"label\\\":\\\"Tokyo, JP\\\",\\\"id\\\":\\\"ap-northeast\\\"}]\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Should be in json format\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:250px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_text",
		"field_id": 17,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 14,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"51cuk64oobc0000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_PriceList\",\"fieldName\":\"Price List (in json)\",\"inputName\":\"tonics_cloud_main_container_APITokens_LinodeAkamai_PriceList\",\"textType\":\"textarea\",\"defaultValue\":\"{\\\"g6-nanode-1\\\":{\\\"service_type\\\":\\\"Server\\\",\\\"description\\\":\\\"Shared 1GB RAM - 1CPU Core - 25GB SSD\\\",\\\"price\\\":{\\\"monthly\\\":12},\\\"memory\\\":1024,\\\"disk\\\":25600},\\\"g6-standard-1\\\":{\\\"service_type\\\":\\\"Server\\\",\\\"description\\\":\\\"Shared 2GB RAM - 1CPU Core - 50GB SSD\\\",\\\"price\\\":{\\\"monthly\\\":20},\\\"memory\\\":2048,\\\"disk\\\":51200},\\\"g6-standard-2\\\":{\\\"service_type\\\":\\\"Server\\\",\\\"description\\\":\\\"Shared 4GB RAM - 2CPU Core - 80GB SSD\\\",\\\"price\\\":{\\\"monthly\\\":40},\\\"memory\\\":4096,\\\"disk\\\":81920},\\\"g6-dedicated-2\\\":{\\\"service_type\\\":\\\"Server\\\",\\\"description\\\":\\\"Dedicated 4GB RAM - 2CPU Core - 80GB SSD\\\",\\\"price\\\":{\\\"monthly\\\":55},\\\"memory\\\":4096,\\\"disk\\\":81920},\\\"g6-dedicated-4\\\":{\\\"service_type\\\":\\\"Server\\\",\\\"description\\\":\\\"Dedicated 8GB RAM - 4CPU Core - 160GB SSD\\\",\\\"price\\\":{\\\"monthly\\\":100},\\\"memory\\\":8192,\\\"disk\\\":163840}}\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"This should be in json format\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:250px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 18,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 2,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"ufcml9iftfk000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_UpCloud\",\"fieldName\":\"UpCloud\",\"inputName\":\"tonics_cloud_main_container_APITokens_UpCloud\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_text",
		"field_id": 19,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 18,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5hfthlfi0ag0000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_UpCloud_UserName\",\"fieldName\":\"Username\",\"inputName\":\"tonics_cloud_main_container_APITokens_UpCloud_UserName\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter UpCloud Username\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_text",
		"field_id": 20,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 18,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6qc6eh929zw0000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_UpCloud_Password\",\"fieldName\":\"Password\",\"inputName\":\"tonics_cloud_main_container_APITokens_UpCloud_Password\",\"textType\":\"password\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter UpCloud Password\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 21,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 18,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"78s8opltxuo0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Deployment Options\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"- Development mode gives you ssh access, this way, you can deploy and use ssh to test things.\\n<br>\\n- Production mode nukes ssh as it isn't needed for production, please, use the production mode in all cases, unless you know what you are doing.\\n<br>\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_select",
		"field_id": 22,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 21,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"5td1tmb0cno0000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_UpCloud_Mode\",\"fieldName\":\"Mode\",\"inputName\":\"tonics_cloud_main_container_APITokens_UpCloud_Mode\",\"selectData\":\"Production,Development\",\"defaultValue\":\"Production\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_text",
		"field_id": 23,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 21,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1o0kjpid9u00000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_UpCloud_SSH_PUBLIC_KEY\",\"fieldName\":\"SSH Public Key\",\"inputName\":\"tonics_cloud_main_container_APITokens_UpCloud_SSH_PUBLIC_KEY\",\"textType\":\"password\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"An example is 'ssh-rsa AAABBB1x2y3z...\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 24,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 18,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"jry7fvy27c0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Others\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_text",
		"field_id": 25,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 24,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1g8zkqz2m8sg000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_UpCloud_Region\",\"fieldName\":\"Region (in json)\",\"inputName\":\"tonics_cloud_main_container_APITokens_UpCloud_Region\",\"textType\":\"textarea\",\"defaultValue\":\"[{\\\"label\\\":\\\"Sydney #1\\\",\\\"id\\\":\\\"au-syd1\\\"},{\\\"label\\\":\\\"Frankfurt #1\\\",\\\"id\\\":\\\"de-fra1\\\"},{\\\"label\\\":\\\"Madrid #1\\\",\\\"id\\\":\\\"es-mad1\\\"},{\\\"label\\\":\\\"Helsinki #1\\\",\\\"id\\\":\\\"fi-hel1\\\"},{\\\"label\\\":\\\"Helsinki #2\\\",\\\"id\\\":\\\"fi-hel2\\\"},{\\\"label\\\":\\\"Amsterdam #1\\\",\\\"id\\\":\\\"nl-ams1\\\"},{\\\"label\\\":\\\"Warsaw #1\\\",\\\"id\\\":\\\"pl-waw1\\\"},{\\\"label\\\":\\\"Stockholm #1\\\",\\\"id\\\":\\\"se-sto1\\\"},{\\\"label\\\":\\\"Singapore #1\\\",\\\"id\\\":\\\"sg-sin1\\\"},{\\\"label\\\":\\\"London #1\\\",\\\"id\\\":\\\"uk-lon1\\\"},{\\\"label\\\":\\\"Chicago #1\\\",\\\"id\\\":\\\"us-chi1\\\"},{\\\"label\\\":\\\"New York #1\\\",\\\"id\\\":\\\"us-nyc1\\\"},{\\\"label\\\":\\\"San Jose #1\\\",\\\"id\\\":\\\"us-sjo1\\\"}] \",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Should be in json format\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:250px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_text",
		"field_id": 26,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 24,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4nm4otevtgq0000000000\",\"field_input_name\":\"tonics_cloud_main_container_APITokens_UpCloud_PriceList\",\"fieldName\":\"Price List (in json)\",\"inputName\":\"tonics_cloud_main_container_APITokens_UpCloud_PriceList\",\"textType\":\"textarea\",\"defaultValue\":\"{\\\"UpCloud-1xCPU-1GB\\\":{\\\"service_type\\\":\\\"Server\\\",\\\"description\\\":\\\"Shared 1GB RAM - 1CPU Core - 25GB SSD\\\",\\\"price\\\":{\\\"monthly\\\":12},\\\"memory\\\":1024,\\\"disk\\\":25.0, \\\"core\\\":1},\\\"UpCloud-1xCPU-2GB\\\":{\\\"service_type\\\":\\\"Server\\\",\\\"description\\\":\\\"Shared 2GB RAM - 1CPU Core - 50GB SSD\\\",\\\"price\\\":{\\\"monthly\\\":20},\\\"memory\\\":2048,\\\"disk\\\":50.0,\\\"core\\\":1},\\\"UpCloud-2xCPU-4GB\\\":{\\\"service_type\\\":\\\"Server\\\",\\\"description\\\":\\\"Shared 4GB RAM - 2CPU Core - 80GB SSD\\\",\\\"price\\\":{\\\"monthly\\\":40},\\\"memory\\\":4096,\\\"disk\\\":80.0,\\\"core\\\": 2},\\\"UpCloud-HIMEM-2xCPU-8GB\\\":{\\\"service_type\\\":\\\"Server\\\",\\\"description\\\":\\\"High Memory 8GB RAM - 2CPU Core - 100GB SSD\\\",\\\"price\\\":{\\\"monthly\\\":60},\\\"memory\\\":8192,\\\"disk\\\":100.0,\\\"core\\\": 2},\\\"UpCloud-HIMEM-2xCPU-16GB\\\":{\\\"service_type\\\":\\\"Server\\\",\\\"description\\\":\\\"High Memory 16GB RAM - 2CPU Core - 100GB SSD\\\",\\\"price\\\":{\\\"monthly\\\":100},\\\"memory\\\":16384,\\\"disk\\\":100.0,\\\"core\\\": 2},\\\"UpCloud-HIMEM-4xCPU-32GB\\\":{\\\"service_type\\\":\\\"Server\\\",\\\"description\\\":\\\"High Memory 32GB RAM - 4CPU Core - 100GB SSD\\\",\\\"price\\\":{\\\"monthly\\\":150},\\\"memory\\\":32768,\\\"disk\\\":100.0,\\\"core\\\": 4}}\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Should be in json format\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:250px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 27,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"5b6s1tk1zwg0000000000\",\"field_input_name\":\"tonics_cloud_main_container_cloudServer\",\"fieldName\":\"Cloud Server\",\"inputName\":\"tonics_cloud_main_container_cloudServer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_select",
		"field_id": 28,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 27,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"20zyzwwcd1uo000000000\",\"field_input_name\":\"tonics_cloud_main_container_cloudServer_Integration\",\"fieldName\":\"Choose Integration\",\"inputName\":\"tonics_cloud_main_container_cloudServer_Integration\",\"selectData\":\"Akamai:Linode (Akamai),UpCloud\",\"defaultValue\":\"Akamai\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 29,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"6wyopcacjfk000000000\",\"field_input_name\":\"tonics_cloud_main_container_DNS\",\"fieldName\":\"DNS\",\"inputName\":\"tonics_cloud_main_container_DNS\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_select",
		"field_id": 30,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 29,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"5yosv0doh2o0000000000\",\"field_input_name\":\"tonics_cloud_main_container_DNS_Integration\",\"fieldName\":\"Choose Integration\",\"inputName\":\"tonics_cloud_main_container_DNS_Integration\",\"selectData\":\"Akamai:Linode (Akamai)\",\"defaultValue\":\"Akamai\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 31,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"1vwwi0l22c8w000000000\",\"field_input_name\":\"tonics_cloud_main_container_Others\",\"fieldName\":\"Others\",\"inputName\":\"tonics_cloud_main_container_Others\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_select",
		"field_id": 32,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 31,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"1nfz13gsit40000000000\",\"field_input_name\":\"tonics_cloud_main_container_Others_enableBilling\",\"fieldName\":\"Enable Billing\",\"inputName\":\"tonics_cloud_main_container_Others_enableBilling\",\"selectData\":\"1:True,0:False\",\"defaultValue\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsCloud Settings",
		"field_name": "input_text",
		"field_id": 33,
		"field_slug": "app-tonicscloud-settings",
		"field_parent_id": 31,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5aaj1n7q0fk0000000000\",\"field_input_name\":\"tonics_cloud_main_container_Others_notifyIfCreditIsLessThan\",\"fieldName\":\"Notify (If Credit is Less Than)\",\"inputName\":\"tonics_cloud_main_container_Others_notifyIfCreditIsLessThan\",\"textType\":\"number\",\"defaultValue\":\"3\",\"info\":\"A notification would be sent to the user periodically once credit balance is less than the specified value.\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Instance Page]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-instance-page",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"h3e34b5gnk0000000000\",\"field_input_name\":\"instance_experience\",\"fieldName\":\"Instance Experience\",\"inputName\":\"instance_experience\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Instance Page]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-instance-page",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1yi3ooopqr28000000000\",\"field_input_name\":\"service_instance_name\",\"fieldName\":\"Instance Name\",\"inputName\":\"service_instance_name\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Instance Name\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Instance Page]",
		"field_name": "tonicscloud_cloudregions",
		"field_id": 3,
		"field_slug": "app-tonicscloud-instance-page",
		"field_parent_id": null,
		"field_options": "{\"field_slug\":\"tonicscloud_cloudregions\",\"field_slug_unique_hash\":\"5wekl53m9o40000000000\",\"field_input_name\":\"cloud_region\",\"fieldName\":\"Regions\",\"inputName\":\"cloud_region\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Instance Page]",
		"field_name": "tonicscloud_cloudinstanceinfo",
		"field_id": 4,
		"field_slug": "app-tonicscloud-instance-page",
		"field_parent_id": null,
		"field_options": "{\"field_slug\":\"tonicscloud_cloudinstanceinfo\",\"field_slug_unique_hash\":\"55tirvo9q6w0000000000\",\"field_input_name\":\"cloud_instance_info\",\"fieldName\":\"Info\",\"inputName\":\"cloud_instance_info\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Instance Page]",
		"field_name": "tonicscloud_pricingtable",
		"field_id": 5,
		"field_slug": "app-tonicscloud-instance-page",
		"field_parent_id": null,
		"field_options": "{\"field_slug\":\"tonicscloud_pricingtable\",\"field_slug_unique_hash\":\"2jvxp5xffz40000000000\",\"field_input_name\":\"\",\"fieldName\":\"Plans\",\"info\":\"The monthly price won't exceed the below monthly prices, for months where we have less than 31days, you would pay less, otherwise, it would be equal to the monthly prices.\",\"hideInUserEditForm\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Container Page]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-container-page",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"3eovy4bmz8o0000000000\",\"field_input_name\":\"container_experience\",\"fieldName\":\"Container Experience\",\"inputName\":\"container_experience\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Container Page]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-container-page",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"52y48c6brzg0000000000\",\"field_input_name\":\"container_name\",\"fieldName\":\"Container Name\",\"inputName\":\"container_name\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Container Name\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Container Page]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-container-page",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6n075oufrdk0000000000\",\"field_input_name\":\"container_description\",\"fieldName\":\"Container Description\",\"inputName\":\"container_description\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Container Description (Optional)\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Container Page]",
		"field_name": "modular_rowcolumn",
		"field_id": 4,
		"field_slug": "app-tonicscloud-container-page",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"1hpqtdywbe80000000000\",\"field_input_name\":\"\",\"fieldName\":\"Deployment Options\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"With Automations, you do not need to choose an image nor do any configuration in the container settings, everything is automated for you and depending on the automation, multiple containers might get deployed.\\n<br>\\n<br>\\nNote: Automation is only available when creating a new container.\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Container Page]",
		"field_name": "modular_rowcolumn",
		"field_id": 5,
		"field_slug": "app-tonicscloud-container-page",
		"field_parent_id": 4,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"67iivs8pq4g0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Container Settings\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Container Page]",
		"field_name": "tonicscloud_cloudcontainerprofiles",
		"field_id": 6,
		"field_slug": "app-tonicscloud-container-page",
		"field_parent_id": 5,
		"field_options": "{\"field_slug\":\"tonicscloud_cloudcontainerprofiles\",\"tonicscloud_cloudcontainerprofiles_cell\":\"1\",\"field_slug_unique_hash\":\"vglrb9d0om8000000000\",\"field_input_name\":\"container_profiles[]\",\"fieldName\":\"Profiles\",\"inputName\":\"container_profiles[]\",\"info\":\"Profiles are of two type, one with a port and one with a ProxyProtocolPort, ProxyProtocolPort can be used for reverse proxies or load balancers to communicate additional information about the incoming connection to the backend server. If you would be hosting multiple instances of a container on the same port e.g say, multiple websites, please use the ProxyProtocolPort instead.\\n<br>\\n<br>\",\"hideInUserEditForm\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Container Page]",
		"field_name": "modular_rowcolumn",
		"field_id": 7,
		"field_slug": "app-tonicscloud-container-page",
		"field_parent_id": 5,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"473mucjbt9w0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Variables Config\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Container Page]",
		"field_name": "input_text",
		"field_id": 8,
		"field_slug": "app-tonicscloud-container-page",
		"field_parent_id": 7,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"301f68xgxlw0000000000\",\"field_input_name\":\"variables\",\"fieldName\":\"Variables\",\"inputName\":\"variables\",\"textType\":\"textarea\",\"defaultValue\":\"ACME_EMAIL=enter_acme_email_for_ssl\\nACME_DOMAIN=enter_acme_domain_for_ssl\\nDB_DATABASE=enter_databae_here\\nDB_USER=enter_username_here\\nDB_PASS=enter_password_here\\nDB_HOST=localhost\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:150px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Container Page]",
		"field_name": "input_text",
		"field_id": 9,
		"field_slug": "app-tonicscloud-container-page",
		"field_parent_id": 7,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1xhpphr770hs000000000\",\"field_input_name\":\"propagateChanges\",\"fieldName\":\"Propagate Changes\",\"inputName\":\"propagateChanges\",\"textType\":\"textarea\",\"defaultValue\":\"UnZip, Script, ENV\",\"info\":\"Which App in the container the variables change should apply to on update. \\n<br>\\nIt should be separated by comma, and the order is important.\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Container Page]",
		"field_name": "input_text",
		"field_id": 10,
		"field_slug": "app-tonicscloud-container-page",
		"field_parent_id": 5,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"tj066j7ddzk000000000\",\"field_input_name\":\"container_devices_config\",\"fieldName\":\"Devices Config\",\"inputName\":\"container_devices_config\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Container Devices Config in JSON format\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Container Page]",
		"field_name": "tonicscloud_cloudautomations",
		"field_id": 11,
		"field_slug": "app-tonicscloud-container-page",
		"field_parent_id": 4,
		"field_options": "{\"field_slug\":\"tonicscloud_cloudautomations\",\"tonicscloud_cloudautomations_cell\":\"1\",\"field_slug_unique_hash\":\"32i7uaarn1k0000000000\",\"field_input_name\":\"automations\",\"fieldName\":\"Automations\",\"inputName\":\"automations\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Container Page]",
		"field_name": "tonicscloud_cloudcontainerimages",
		"field_id": 12,
		"field_slug": "app-tonicscloud-container-page",
		"field_parent_id": null,
		"field_options": "{\"field_slug\":\"tonicscloud_cloudcontainerimages\",\"field_slug_unique_hash\":\"wodp5b20434000000000\",\"field_input_name\":\"container_image\",\"fieldName\":\"Golden Image\",\"inputName\":\"container_image\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Container Page]",
		"field_name": "tonicscloud_cloudinstances",
		"field_id": 13,
		"field_slug": "app-tonicscloud-container-page",
		"field_parent_id": null,
		"field_options": "{\"field_slug\":\"tonicscloud_cloudinstances\",\"field_slug_unique_hash\":\"5iznpo9se6c000000000\",\"field_input_name\":\"cloud_instance\",\"fieldName\":\"Instance:\",\"inputName\":\"cloud_instance\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Image Page]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-image-page",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"2icfwsr03qk0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Image Experience\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Image Page]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-image-page",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"31iqrimtzuw0000000000\",\"field_input_name\":\"container_image_name\",\"fieldName\":\"Image Name\",\"inputName\":\"container_image_name\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Image Name\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Image Page]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-image-page",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2ccgoim71ri8000000000\",\"field_input_name\":\"container_image_description\",\"fieldName\":\"Image Description\",\"inputName\":\"container_image_description\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Image Description\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Image Page]",
		"field_name": "input_text",
		"field_id": 4,
		"field_slug": "app-tonicscloud-image-page",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"20k85vjx71q800000000\",\"field_input_name\":\"image_apps\",\"fieldName\":\"Image Apps\",\"inputName\":\"image_apps\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Apps in Image (Separate By Comma)\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Image Page]",
		"field_name": "input_text",
		"field_id": 5,
		"field_slug": "app-tonicscloud-image-page",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6h0f1og06hg0000000000\",\"field_input_name\":\"container_image_logo\",\"fieldName\":\"Image Logo\",\"inputName\":\"container_image_logo\",\"textType\":\"url\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"https://image.com/logo.svg\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Image Page]",
		"field_name": "modular_rowcolumn",
		"field_id": 6,
		"field_slug": "app-tonicscloud-image-page",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"182iclcbz9s0000000000\",\"field_input_name\":\"image_link_mirrors_container\",\"fieldName\":\"Image Link Mirrors\",\"inputName\":\"image_link_mirrors_container\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Image Page]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 7,
		"field_slug": "app-tonicscloud-image-page",
		"field_parent_id": 6,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"6vats95fpt40000000000\",\"field_input_name\":\"image_link_mirrors_info_container\",\"fieldName\":\"Image Info\",\"inputName\":\"image_link_mirrors_info_container\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Repeat Image\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Image Page]",
		"field_name": "input_text",
		"field_id": 8,
		"field_slug": "app-tonicscloud-image-page",
		"field_parent_id": 7,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"64k6ebd9ry00000000000\",\"field_input_name\":\"image_link_mirrors_version\",\"fieldName\":\"Version\",\"inputName\":\"image_link_mirrors_version\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Version of the Image (e.g, v1.1.0, 1.25.0, etc)\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"v3.0.1\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Image Page]",
		"field_name": "input_text",
		"field_id": 9,
		"field_slug": "app-tonicscloud-image-page",
		"field_parent_id": 7,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"zigc86e51i8000000000\",\"field_input_name\":\"image_hash\",\"fieldName\":\"Image Hash\",\"inputName\":\"image_hash\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"The SHA256 of the image that is being downloaded.\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Image Page]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 10,
		"field_slug": "app-tonicscloud-image-page",
		"field_parent_id": 7,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"4yi9r9bbg4w0000000000\",\"field_input_name\":\"image_link_mirror_repeater_field\",\"fieldName\":\"URL\",\"inputName\":\"image_link_mirror_repeater_field\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Add Link Mirror\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Image Page]",
		"field_name": "input_text",
		"field_id": 11,
		"field_slug": "app-tonicscloud-image-page",
		"field_parent_id": 10,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6y1ehkx24i40000000000\",\"field_input_name\":\"image_link_mirror[]\",\"fieldName\":\"Enter URL\",\"inputName\":\"image_link_mirror[]\",\"textType\":\"url\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter URL\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud  [App Config] [ACME]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-app-config-acme",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"22wi60ti5ark000000000\",\"field_input_name\":\"\",\"fieldName\":\"ACME Config\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"Global Variable you can use:\\n<br>\\n<code>[[RAND_STRING]]<\\/code> - auto-generates cryptographically secure pseudo-random bytes.\\n<br>\\n<code>[[ACME_EMAIL]]<\\/code> - Pull from the container specific global variable if there is one.\\n<br>\\n<code>[[ACME_DOMAIN]]<\\/code> - Pull from the container specific global variable if there is one.\\n<br>\\n...and any more specified in the container variable.\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud  [App Config] [ACME]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-app-config-acme",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"777xc71ywjo0000000000\",\"field_input_name\":\"acme_email\",\"fieldName\":\"Email\",\"inputName\":\"acme_email\",\"textType\":\"text\",\"defaultValue\":\"[[ACME_EMAIL]]\",\"info\":\"Ensure the email is valid, renewal notices would be sent to the emall when applicable.\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"mail@tonics.app\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud  [App Config] [ACME]",
		"field_name": "input_select",
		"field_id": 3,
		"field_slug": "app-tonicscloud-app-config-acme",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2v741lsnhuo0000000000\",\"field_input_name\":\"acme_mode\",\"fieldName\":\"Modes\",\"inputName\":\"acme_mode\",\"selectData\":\"nginx:Nginx,apache2:Apache2,standalone:Standalone\",\"defaultValue\":\"Nginx\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsCloud  [App Config] [ACME]",
		"field_name": "input_select",
		"field_id": 4,
		"field_slug": "app-tonicscloud-app-config-acme",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"3heecqdehdk0000000000\",\"field_input_name\":\"acme_issuer\",\"fieldName\":\"Issuer\",\"inputName\":\"acme_issuer\",\"selectData\":\"letsencrypt:LetsEncrypt,zerossl:ZeroSSL\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsCloud  [App Config] [ACME]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 5,
		"field_slug": "app-tonicscloud-app-config-acme",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"3x0h7osnw3q0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Domain\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Add New Site\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud  [App Config] [ACME]",
		"field_name": "input_text",
		"field_id": 6,
		"field_slug": "app-tonicscloud-app-config-acme",
		"field_parent_id": 5,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1cyuq84gapr4000000000\",\"field_input_name\":\"acme_sites[]\",\"fieldName\":\"Site\",\"inputName\":\"acme_sites[]\",\"textType\":\"text\",\"defaultValue\":\"[[ACME_DOMAIN]]\",\"info\":\"This can be separated by comma, e.g.: siteone.com, sitetwo.com\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"siteone.com, ...\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud  [App Config] [Default]",
		"field_name": "input_text",
		"field_id": 1,
		"field_slug": "app-tonicscloud-app-config-default",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"7f9nl332bbo0000000000\",\"field_input_name\":\"config\",\"fieldName\":\"Manual Config\",\"inputName\":\"config\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Config\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:500px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Nginx]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-app-config-nginx",
		"field_parent_id": null,
		"field_options": "{\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"5l2r8ntok740000000000\",\"field_input_name\":\"app_config_nginx_recipe\",\"fieldName\":\"Nginx Recipe\",\"inputName\":\"app_config_nginx_recipe\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"Global Variable you can use:\\n<br>\\n<code>[[RAND_STRING]]<\\/code> - auto-generates cryptographically secure pseudo-random bytes\\n<br>\\n<code>[[ACME_DOMAIN]]<\\/code> - Pull from the container specific global variable if there is one\\n<br>\\n...and any more specified in the container variable.\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Nginx]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 2,
		"field_slug": "app-tonicscloud-app-config-nginx",
		"field_parent_id": 1,
		"field_options": "{\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"uwtvd1nvteo000000000\",\"field_input_name\":\"\",\"fieldName\":\"Recipe Repeater\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Repeat Recipe\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Nginx]",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 3,
		"field_slug": "app-tonicscloud-app-config-nginx",
		"field_parent_id": 2,
		"field_options": "{\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"5z0rte6xjow0000000000\",\"field_input_name\":\"app_config_nginx_recipe_selected\",\"fieldName\":\"Choose Reciper\",\"inputName\":\"app_config_nginx_recipe_selected\",\"fieldSlug\":[\"app-tonicscloud-nginx-recipe-wordpress-simple\",\"app-tonicscloud-app-config-default\",\"app-tonicscloud-nginx-recipe-reverse-proxy\",\"app-tonicscloud-nginx-recipe-reverse-proxy-simple\",\"app-tonicscloud-nginx-recipe-tonics-simple\",\"app-tonicscloud-nginx-recipe-static-site-https\",\"app-tonicscloud-nginx-recipe-static-site-http\"],\"defaultFieldSlug\":\"app-tonicscloud-app-config-default\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Reverse Proxy]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 1,
		"field_slug": "app-tonicscloud-nginx-recipe-reverse-proxy",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"7i7wz9iw9qk000000000\",\"field_input_name\":\"\",\"fieldName\":\"Server Block\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Repeat Server Block\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Reverse Proxy]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-nginx-recipe-reverse-proxy",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4f8c9v68lq40000000000\",\"field_input_name\":\"open_server_block\",\"fieldName\":\"Open Server Block\",\"inputName\":\"open_server_block\",\"textType\":\"textarea\",\"defaultValue\":\"server {\\n        listen 80 proxy_protocol;\\n        listen [::]:80 proxy_protocol;\\n\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:125px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Reverse Proxy]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-nginx-recipe-reverse-proxy",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5esoq67r2300000000000\",\"field_input_name\":\"server_name\",\"fieldName\":\"Server Name\",\"inputName\":\"server_name\",\"textType\":\"text\",\"defaultValue\":\"[[ACME_DOMAIN]]\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"website.com\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Reverse Proxy]",
		"field_name": "input_text",
		"field_id": 4,
		"field_slug": "app-tonicscloud-nginx-recipe-reverse-proxy",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"69jixllp6ns0000000000\",\"field_input_name\":\"open_location_block\",\"fieldName\":\"Open Location Block\",\"inputName\":\"open_location_block\",\"textType\":\"textarea\",\"defaultValue\":\"        location / {\\n                proxy_set_header Host $http_host;\\n                proxy_set_header X-Real-IP $remote_addr;\\n                proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;\\n                proxy_set_header X-Forwarded-Proto $scheme;\\n\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:155px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Reverse Proxy]",
		"field_name": "tonicscloud_cloudcontainersofinstance",
		"field_id": 5,
		"field_slug": "app-tonicscloud-nginx-recipe-reverse-proxy",
		"field_parent_id": 1,
		"field_options": "{\"field_slug\":\"tonicscloud_cloudcontainersofinstance\",\"tonicscloud_cloudcontainersofinstance_cell\":\"1\",\"field_slug_unique_hash\":\"37x5y9lbsay0000000000\",\"field_input_name\":\"proxy_pass_container\",\"fieldName\":\"Proxy To \\ud83d\\udc47\",\"inputName\":\"proxy_pass_container\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Reverse Proxy]",
		"field_name": "input_text",
		"field_id": 6,
		"field_slug": "app-tonicscloud-nginx-recipe-reverse-proxy",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"71ryvi0dvx80000000000\",\"field_input_name\":\"close_location_block\",\"fieldName\":\"Close Location Block\",\"inputName\":\"close_location_block\",\"textType\":\"textarea\",\"defaultValue\":\"}\\n\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Reverse Proxy]",
		"field_name": "input_text",
		"field_id": 7,
		"field_slug": "app-tonicscloud-nginx-recipe-reverse-proxy",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"vac7m1y0n3k000000000\",\"field_input_name\":\"close_server_block\",\"fieldName\":\"Close Server Block\",\"inputName\":\"close_server_block\",\"textType\":\"textarea\",\"defaultValue\":\"        real_ip_header proxy_protocol;\\n        set_real_ip_from 127.0.0.1;\\n}\\n\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:100px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud [DNS]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-dns",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"1v5cxmr6dwbk000000000\",\"field_input_name\":\"tonicsCloud_domain_records_container\",\"fieldName\":\"Domain Records\",\"inputName\":\"tonicsCloud_domain_records_container\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [DNS]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-dns",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2luvvymnceg0000000000\",\"field_input_name\":\"dns_domain\",\"fieldName\":\"Domain\",\"inputName\":\"dns_domain\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"website.com\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [DNS]",
		"field_name": "tonicscloud_cloudinstances",
		"field_id": 3,
		"field_slug": "app-tonicscloud-dns",
		"field_parent_id": 1,
		"field_options": "{\"field_slug\":\"tonicscloud_cloudinstances\",\"tonicscloud_cloudinstances_cell\":\"1\",\"field_slug_unique_hash\":\"5aacwi5a45s0000000000\",\"field_input_name\":\"dns_cloud_instance\",\"fieldName\":\"Default A/AAAA Value From \\ud83d\\udc47\",\"inputName\":\"dns_cloud_instance\"}"
	},
	{
		"field_field_name": "App TonicsCloud [DNS]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 4,
		"field_slug": "app-tonicscloud-dns",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"73u2zyqwib0000000000\",\"field_input_name\":\"dns_record\",\"fieldName\":\"Record\",\"inputName\":\"dns_record\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Add New Record\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [DNS]",
		"field_name": "input_text",
		"field_id": 5,
		"field_slug": "app-tonicscloud-dns",
		"field_parent_id": 4,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"30vabuhnsna0000000000\",\"field_input_name\":\"dns_sub_domain\",\"fieldName\":\"Subdomain\",\"inputName\":\"dns_sub_domain\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"(optional) Keep blank to create a record for the root domain.\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [DNS]",
		"field_name": "input_select",
		"field_id": 6,
		"field_slug": "app-tonicscloud-dns",
		"field_parent_id": 4,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"7gat39mlrac0000000000\",\"field_input_name\":\"dns_record_type\",\"fieldName\":\"Record type\",\"inputName\":\"dns_record_type\",\"selectData\":\"A,AAAA,CAA,CNAME,MX,SRV,TXT\",\"defaultValue\":\"A\"}"
	},
	{
		"field_field_name": "App TonicsCloud [DNS]",
		"field_name": "input_text",
		"field_id": 7,
		"field_slug": "app-tonicscloud-dns",
		"field_parent_id": 4,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"278vmp4im4ys000000000\",\"field_input_name\":\"dns_value\",\"fieldName\":\"Value\",\"inputName\":\"dns_value\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"Some record types required format as below:\\n<br>\\n<br>\\nMX Format:  [priority] [mail domain] e.g. 10 mail.example.com\\n<br>\\nSRV Format: [priority] [weight] [port] [server host name] e.g. 1 10 5269 xmpp-server.example.com\\n<br>\\nCAA Format:  [tag] [value] e.g. issue \\\"caa.example.com\\\"\\n<br>\\n<br>\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Record Value\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [DNS]",
		"field_name": "input_select",
		"field_id": 8,
		"field_slug": "app-tonicscloud-dns",
		"field_parent_id": 4,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"6201ialo9f40000000000\",\"field_input_name\":\"dns_ttl\",\"fieldName\":\"TTL\",\"inputName\":\"dns_ttl\",\"selectData\":\"60:1 minutes,120:2 minutes,300:5 minutes,3600:1 hour,7200:2 hours,14400:4 hours,28800:8 hours,57600:16 hours,86400:1 day,172800:2 days,345600:4 days,604800:1 week,1209600:2 weeks,2419200:4 weeks\",\"defaultValue\":\"300\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Billing Page]",
		"field_name": "tonicscloud_cloudcredit",
		"field_id": 1,
		"field_slug": "app-tonicscloud-billing-page",
		"field_parent_id": null,
		"field_options": "{\"field_slug\":\"tonicscloud_cloudcredit\",\"field_slug_unique_hash\":\"1gowctvs6qe8000000000\",\"field_input_name\":\"\",\"fieldName\":\"Cloud Credit\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Billing Page]",
		"field_name": "modular_rowcolumn",
		"field_id": 2,
		"field_slug": "app-tonicscloud-billing-page",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"1gn98sxd3q4g000000000\",\"field_input_name\":\"\",\"fieldName\":\"Make Payment\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Billing Page]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-billing-page",
		"field_parent_id": 2,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4gqs6syqtfy0000000000\",\"field_input_name\":\"payment_amount\",\"fieldName\":\"Amount\",\"inputName\":\"payment_amount\",\"textType\":\"number\",\"defaultValue\":\"5.00\",\"info\":\"Payment Amount Must Be At Least $1.00\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [Billing Page]",
		"field_name": "tonicscloud_cloudpaymentmethods",
		"field_id": 4,
		"field_slug": "app-tonicscloud-billing-page",
		"field_parent_id": 2,
		"field_options": "{\"field_slug\":\"tonicscloud_cloudpaymentmethods\",\"tonicscloud_cloudpaymentmethods_cell\":\"1\",\"field_slug_unique_hash\":\"1qbvj877cgow000000000\",\"field_input_name\":\"\",\"fieldName\":\"Payment Method(s)\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Reverse Proxy Simple]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 1,
		"field_slug": "app-tonicscloud-nginx-recipe-reverse-proxy-simple",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"5yb53xmsei80000000000\",\"field_input_name\":\"\",\"fieldName\":\"Server Block Simple\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"If SSL is true, please ensure you have generated an SSL certificate through the ACME App\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Repeat Server Block\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Reverse Proxy Simple]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-nginx-recipe-reverse-proxy-simple",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3who8ghjet00000000000\",\"field_input_name\":\"server_name\",\"fieldName\":\"Server Name\",\"inputName\":\"server_name\",\"textType\":\"url\",\"defaultValue\":\"[[ACME_DOMAIN]]\",\"info\":\"Don't start with http or https, e.g, it should not be https://example.com but example.com\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"tonics.app\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Reverse Proxy Simple]",
		"field_name": "tonicscloud_cloudcontainersofinstance",
		"field_id": 3,
		"field_slug": "app-tonicscloud-nginx-recipe-reverse-proxy-simple",
		"field_parent_id": 1,
		"field_options": "{\"field_slug\":\"tonicscloud_cloudcontainersofinstance\",\"tonicscloud_cloudcontainersofinstance_cell\":\"1\",\"field_slug_unique_hash\":\"28qg6nik93k0000000000\",\"field_input_name\":\"proxy_pass_container\",\"fieldName\":\"Proxy To \\ud83d\\udc47\",\"inputName\":\"proxy_pass_container\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Reverse Proxy Simple]",
		"field_name": "input_select",
		"field_id": 4,
		"field_slug": "app-tonicscloud-nginx-recipe-reverse-proxy-simple",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"5dqpovlv5000000000000\",\"field_input_name\":\"server_ssl\",\"fieldName\":\"SSL\",\"inputName\":\"server_ssl\",\"selectData\":\"0:False,1:True\",\"defaultValue\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Upload Unzip]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-app-config-upload-unzip",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"65499rzo7qg0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Unzip Config\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"It supports multiple formats: (.tar.gz, .tgz, .tar.bz, .tbz, .tar, 7z, .zip, .rar, .gz, .lz, etc, check the formats below for more). <br><br>\\n\\n<b>Options:<\\/b> <br>\\n- Extract To: Path to extract archive <br>\\n- Archive File: Archive to extract <br>\\n- Create SubDirectory: Whether to create a new directory for the archive even if the archive only contains one file in its root directory <br>\\n- Format: Archive Format <br>\\n- Overwrite: When extracting from files, allow overwriting of local files.<br><br>\\n\\n<i>Powered by atools<\\/i>\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Upload Unzip]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 2,
		"field_slug": "app-tonicscloud-app-config-upload-unzip",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"37zj4aqu5aw0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Unzip\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Repeat Unzip\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Upload Unzip]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-app-config-upload-unzip",
		"field_parent_id": 2,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2vaztkywnk00000000000\",\"field_input_name\":\"unzip_extractTo\",\"fieldName\":\"Extract To\",\"inputName\":\"unzip_extractTo\",\"textType\":\"text\",\"defaultValue\":\"/var/www/[[ACME_DOMAIN]]\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Upload Unzip]",
		"field_name": "input_text",
		"field_id": 4,
		"field_slug": "app-tonicscloud-app-config-upload-unzip",
		"field_parent_id": 2,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5cbaf9xaf600000000000\",\"field_input_name\":\"unzip_archiveFile\",\"fieldName\":\"Archive File\",\"inputName\":\"unzip_archiveFile\",\"textType\":\"url\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter URL\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Upload Unzip]",
		"field_name": "input_select",
		"field_id": 5,
		"field_slug": "app-tonicscloud-app-config-upload-unzip",
		"field_parent_id": 2,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"430yly4zg280000000000\",\"field_input_name\":\"unzip_format\",\"fieldName\":\"Format\",\"inputName\":\"unzip_format\",\"selectData\":\":Auto Detect,7z,a,ace,alz,arc,arj,bz,bz2,cab,cpio,deb,gz,jar,lha,lrz,lz,lzh,lzma,lzo,rar,rpm,rz,t7z,tar,tar.7z,tar.bz,tar.bz2,tar.gz,tar.lz,tar.lzo,tar.xz,tar.Z,tbz,tbz2,tgz,tlz,txz,tZ,tzo,war,xz,Z,zip\",\"defaultValue\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Upload Unzip]",
		"field_name": "input_select",
		"field_id": 6,
		"field_slug": "app-tonicscloud-app-config-upload-unzip",
		"field_parent_id": 2,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"6y5aljg5myo0000000000\",\"field_input_name\":\"unzip_overwrite\",\"fieldName\":\"Overwrite\",\"inputName\":\"unzip_overwrite\",\"selectData\":\"0:False,1:True\",\"defaultValue\":\"1\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Upload Unzip]",
		"field_name": "input_select",
		"field_id": 7,
		"field_slug": "app-tonicscloud-app-config-upload-unzip",
		"field_parent_id": 2,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"6wm8yessefo0000000000\",\"field_input_name\":\"unzip_createSubDirectory\",\"fieldName\":\"Create SubDirectory\",\"inputName\":\"unzip_createSubDirectory\",\"selectData\":\"0:False,1:True\",\"defaultValue\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [MySQL]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-app-config-mysql",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"60cd6ovnpho0000000000\",\"field_input_name\":\"MySQL_CONFIG_CONTAINER\",\"fieldName\":\"MySQL-MariaDB Config\",\"inputName\":\"MySQL_CONFIG_CONTAINER\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"Deleting database and user here won't remove it from the server, this is to avoid accidental deletion, if you are sure about the deletion, then remove it in this section and list it in the Removal section below.\\n<br>\\n<br>\\nNote: The username can't be root and if you are connecting remotely, ensure the 3306 proxy is set or a proxy is configured to allow the connection\\n<br>\\n<br>\\nGlobal Variable you can use:\\n<br>\\n<code>[[RAND_STRING]]<\\/code> - auto-generates cryptographically secure pseudo-random bytes.\\n<br>\\n<code>[[DB_DATABASE]]<\\/code> - Pull from the container specific global variable if there is one.\\n<br>\\n<code>[[DB_USER]]<\\/code> - Pull from the container specific global variable if there is one.\\n<br>\\n<code>[[DB_PASS]]<\\/code> - Pull from the container specific global variable if there is one.\\n<br>\\n<code>[[DB_HOST]]<\\/code> - Pull from the container specific global variable if there is one or default to localhost.\\n<br>\\n...and any more specified in the container variable.\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [MySQL]",
		"field_name": "modular_rowcolumn",
		"field_id": 2,
		"field_slug": "app-tonicscloud-app-config-mysql",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"77ltk20c3y80000000000\",\"field_input_name\":\"MySQL_CONFIG_CONTAINER_USER\",\"fieldName\":\"User(s)\",\"inputName\":\"MySQL_CONFIG_CONTAINER_USER\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [MySQL]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 3,
		"field_slug": "app-tonicscloud-app-config-mysql",
		"field_parent_id": 2,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"62v74cv0w9s0000000000\",\"field_input_name\":\"\",\"fieldName\":\"User\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Add User\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [MySQL]",
		"field_name": "input_text",
		"field_id": 4,
		"field_slug": "app-tonicscloud-app-config-mysql",
		"field_parent_id": 3,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3sgleofvnro0000000000\",\"field_input_name\":\"user_name\",\"fieldName\":\"Name\",\"inputName\":\"user_name\",\"textType\":\"text\",\"defaultValue\":\"[[DB_USER]]\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Username\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [MySQL]",
		"field_name": "input_text",
		"field_id": 5,
		"field_slug": "app-tonicscloud-app-config-mysql",
		"field_parent_id": 3,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6hrbtfyncwg0000000000\",\"field_input_name\":\"user_pass\",\"fieldName\":\"Password\",\"inputName\":\"user_pass\",\"textType\":\"password\",\"defaultValue\":\"[[DB_PASS]]\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter User Password\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [MySQL]",
		"field_name": "input_text",
		"field_id": 6,
		"field_slug": "app-tonicscloud-app-config-mysql",
		"field_parent_id": 3,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"58f8ve2px440000000000\",\"field_input_name\":\"user_remote_address\",\"fieldName\":\"Host\",\"inputName\":\"user_remote_address\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Leave empty for localhost\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [MySQL]",
		"field_name": "modular_rowcolumn",
		"field_id": 7,
		"field_slug": "app-tonicscloud-app-config-mysql",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"43aaquchw4g0000000000\",\"field_input_name\":\"MySQL_CONFIG_CONTAINER_DATABASE\",\"fieldName\":\"Database(s)\",\"inputName\":\"MySQL_CONFIG_CONTAINER_DATABASE\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [MySQL]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 8,
		"field_slug": "app-tonicscloud-app-config-mysql",
		"field_parent_id": 7,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"3nl97lkj1vw0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Database\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Add Database\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [MySQL]",
		"field_name": "input_text",
		"field_id": 9,
		"field_slug": "app-tonicscloud-app-config-mysql",
		"field_parent_id": 8,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"51jqzprktyw0000000000\",\"field_input_name\":\"db_name\",\"fieldName\":\"Name\",\"inputName\":\"db_name\",\"textType\":\"text\",\"defaultValue\":\"[[DB_DATABASE]]\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Database Name\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [MySQL]",
		"field_name": "input_text",
		"field_id": 10,
		"field_slug": "app-tonicscloud-app-config-mysql",
		"field_parent_id": 8,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6en6hjp8iy00000000000\",\"field_input_name\":\"db_user\",\"fieldName\":\"User (Optional)\",\"inputName\":\"db_user\",\"textType\":\"text\",\"defaultValue\":\"[[DB_USER]]\",\"info\":\"Enter user you want to grant access to this database, please ensure the user is created or about to be created.\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Username\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [MySQL]",
		"field_name": "input_text",
		"field_id": 11,
		"field_slug": "app-tonicscloud-app-config-mysql",
		"field_parent_id": 8,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5c23jpv5d6c0000000000\",\"field_input_name\":\"db_user_host\",\"fieldName\":\"User Host\",\"inputName\":\"db_user_host\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Optional if user is not set, if user is set and the User Host is null, we assume localhost\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"User Host\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [MySQL]",
		"field_name": "input_text",
		"field_id": 12,
		"field_slug": "app-tonicscloud-app-config-mysql",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6if1x49amdc0000000000\",\"field_input_name\":\"config\",\"fieldName\":\"Config\",\"inputName\":\"config\",\"textType\":\"textarea\",\"defaultValue\":\"[mysqld]\\nbind-address            = 127.0.0.1\\n\\n#\\n# * Fine Tuning\\n#\\n\\n#key_buffer_size        = 128M\\n#max_allowed_packet     = 1G\\n#thread_stack           = 192K\\n#thread_cache_size      = 8\\n#max_connections        = 100\\n#table_cache            = 64\\n\\n#\\n# * SSL/TLS\\n#\\n\\n# For documentation, please read\\n# https://mariadb.com/kb/en/securing-connections-for-client-and-server/\\n#ssl-ca = /etc/mysql/cacert.pem\\n#ssl-cert = /etc/mysql/server-cert.pem\\n#ssl-key = /etc/mysql/server-key.pem\\n#require-secure-transport = on\\n\\n#\\n# * Character sets\\n#\\n\\n# MySQL/MariaDB default is Latin1, but in Debian we rather default to the full\\n# utf8 4-byte character set. See also client.cnf\\ncharacter-set-server  = utf8mb4\\ncollation-server      = utf8mb4_general_ci\\n\\n\\n# Uncomment For Remote Connection\\n[mysqld]\\n# skip-networking=0\\n# skip-bind-address\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:500px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [MySQL]",
		"field_name": "modular_rowcolumn",
		"field_id": 13,
		"field_slug": "app-tonicscloud-app-config-mysql",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"2bv36abhhtlw000000000\",\"field_input_name\":\"\",\"fieldName\":\"Removal\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [MySQL]",
		"field_name": "input_text",
		"field_id": 14,
		"field_slug": "app-tonicscloud-app-config-mysql",
		"field_parent_id": 13,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3lerg7rramm0000000000\",\"field_input_name\":\"db_remove\",\"fieldName\":\"Database\",\"inputName\":\"db_remove\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"E.g: db_1, db_2, etc\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"List of database to remove separated by comma\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [MySQL]",
		"field_name": "input_text",
		"field_id": 15,
		"field_slug": "app-tonicscloud-app-config-mysql",
		"field_parent_id": 13,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1rpbsfocr3i8000000000\",\"field_input_name\":\"user_remove\",\"fieldName\":\"Users\",\"inputName\":\"user_remove\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"E.g: user_1, user_2, etc\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"List of user to remove separated by comma\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [PHP]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-app-config-php",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"1f16y6naih8g000000000\",\"field_input_name\":\"\",\"fieldName\":\"PHP Config\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"Global Variable you can use:\\n<br>\\n<code>[[PHP_VERSION]]<\\/code> - e.g 8.2\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [PHP]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-app-config-php",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4r6t89u9fle0000000000\",\"field_input_name\":\"fpm\",\"fieldName\":\"PHP-FPM.conf \",\"inputName\":\"fpm\",\"textType\":\"textarea\",\"defaultValue\":\";;;;;;;;;;;;;;;;;;;;;\\n; FPM Configuration ;\\n;;;;;;;;;;;;;;;;;;;;;\\n\\n; All relative paths in this configuration file are relative to PHP's install\\n; prefix (/usr). This prefix can be dynamically changed by using the\\n; '-p' argument from the command line.\\n\\n;;;;;;;;;;;;;;;;;;\\n; Global Options ;\\n;;;;;;;;;;;;;;;;;;\\n\\n[global]\\n; Pid file\\n; Note: the default prefix is /var\\n; Default Value: none\\n; Warning: if you change the value here, you need to modify systemd\\n; service PIDFile= setting to match the value here.\\npid = /run/php/php[[PHP_VERSION]]-fpm.pid\\n\\n; Error log file\\n; If it's set to \\\"syslog\\\", log is sent to syslogd instead of being written\\n; into a local file.\\n; Note: the default prefix is /var\\n; Default Value: log/php-fpm.log\\nerror_log = /var/log/php[[PHP_VERSION]]-fpm.log\\n\\n; syslog_facility is used to specify what type of program is logging the\\n; message. This lets syslogd specify that messages from different facilities\\n; will be handled differently.\\n; See syslog(3) for possible values (ex daemon equiv LOG_DAEMON)\\n; Default Value: daemon\\n;syslog.facility = daemon\\n\\n; syslog_ident is prepended to every message. If you have multiple FPM\\n; instances running on the same server, you can change the default value\\n; which must suit common needs.\\n; Default Value: php-fpm\\n;syslog.ident = php-fpm\\n\\n; Log level\\n; Possible Values: alert, error, warning, notice, debug\\n; Default Value: notice\\n;log_level = notice\\n\\n; Log limit on number of characters in the single line (log entry). If the\\n; line is over the limit, it is wrapped on multiple lines. The limit is for\\n; all logged characters including message prefix and suffix if present. However\\n; the new line character does not count into it as it is present only when\\n; logging to a file descriptor. It means the new line character is not present\\n; when logging to syslog.\\n; Default Value: 1024\\n;log_limit = 4096\\n\\n; Log buffering specifies if the log line is buffered which means that the\\n; line is written in a single write operation. If the value is false, then the\\n; data is written directly into the file descriptor. It is an experimental\\n; option that can potentially improve logging performance and memory usage\\n; for some heavy logging scenarios. This option is ignored if logging to syslog\\n; as it has to be always buffered.\\n; Default value: yes\\n;log_buffering = no\\n\\n; If this number of child processes exit with SIGSEGV or SIGBUS within the time\\n; interval set by emergency_restart_interval then FPM will restart. A value\\n; of '0' means 'Off'.\\n; Default Value: 0\\n;emergency_restart_threshold = 0\\n\\n; Interval of time used by emergency_restart_interval to determine when\\n; a graceful restart will be initiated.  This can be useful to work around\\n; accidental corruptions in an accelerator's shared memory.\\n; Available Units: s(econds), m(inutes), h(ours), or d(ays)\\n; Default Unit: seconds\\n; Default Value: 0\\n;emergency_restart_interval = 0\\n\\n; Time limit for child processes to wait for a reaction on signals from master.\\n; Available units: s(econds), m(inutes), h(ours), or d(ays)\\n; Default Unit: seconds\\n; Default Value: 0\\n;process_control_timeout = 0\\n\\n; The maximum number of processes FPM will fork. This has been designed to control\\n; the global number of processes when using dynamic PM within a lot of pools.\\n; Use it with caution.\\n; Note: A value of 0 indicates no limit\\n; Default Value: 0\\n; process.max = 128\\n\\n; Specify the nice(2) priority to apply to the master process (only if set)\\n; The value can vary from -19 (highest priority) to 20 (lowest priority)\\n; Note: - It will only work if the FPM master process is launched as root\\n;       - The pool process will inherit the master process priority\\n;         unless specified otherwise\\n; Default Value: no set\\n; process.priority = -19\\n\\n; Send FPM to background. Set to 'no' to keep FPM in foreground for debugging.\\n; Default Value: yes\\n;daemonize = yes\\n\\n; Set open file descriptor rlimit for the master process.\\n; Default Value: system defined value\\n;rlimit_files = 1024\\n\\n; Set max core size rlimit for the master process.\\n; Possible Values: 'unlimited' or an integer greater or equal to 0\\n; Default Value: system defined value\\n;rlimit_core = 0\\n\\n; Specify the event mechanism FPM will use. The following is available:\\n; - select     (any POSIX os)\\n; - poll       (any POSIX os)\\n; - epoll      (linux >= 2.5.44)\\n; - kqueue     (FreeBSD >= 4.1, OpenBSD >= 2.9, NetBSD >= 2.0)\\n; - /dev/poll  (Solaris >= 7)\\n; - port       (Solaris >= 10)\\n; Default Value: not set (auto detection)\\n;events.mechanism = epoll\\n\\n; When FPM is built with systemd integration, specify the interval,\\n; in seconds, between health report notification to systemd.\\n; Set to 0 to disable.\\n; Available Units: s(econds), m(inutes), h(ours)\\n; Default Unit: seconds\\n; Default value: 10\\n;systemd_interval = 10\\n\\n;;;;;;;;;;;;;;;;;;;;\\n; Pool Definitions ;\\n;;;;;;;;;;;;;;;;;;;;\\n\\n; Multiple pools of child processes may be started with different listening\\n; ports and different management options.  The name of the pool will be\\n; used in logs and stats. There is no limitation on the number of pools which\\n; FPM can handle. Your system will tell you anyway :)\\n\\n; Include one or more files. If glob(3) exists, it is used to include a bunch of\\n; files from a glob(3) pattern. This directive can be used everywhere in the\\n; file.\\n; Relative path can also be used. They will be prefixed by:\\n;  - the global prefix if it's been set (-p argument)\\n;  - /usr otherwise\\ninclude=/etc/php/[[PHP_VERSION]]/fpm/pool.d/*.conf\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:500px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [PHP]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-app-config-php",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"53abszcaax40000000000\",\"field_input_name\":\"ini\",\"fieldName\":\"PHP.ini\",\"inputName\":\"ini\",\"textType\":\"textarea\",\"defaultValue\":\"[PHP]\\n\\n;;;;;;;;;;;;;;;;;;;\\n; About php.ini   ;\\n;;;;;;;;;;;;;;;;;;;\\n; PHP's initialization file, generally called php.ini, is responsible for\\n; configuring many of the aspects of PHP's behavior.\\n\\n; PHP attempts to find and load this configuration from a number of locations.\\n; The following is a summary of its search order:\\n; 1. SAPI module specific location.\\n; 2. The PHPRC environment variable.\\n; 3. A number of predefined registry keys on Windows\\n; 4. Current working directory (except CLI)\\n; 5. The web server's directory (for SAPI modules), or directory of PHP\\n; (otherwise in Windows)\\n; 6. The directory from the --with-config-file-path compile time option, or the\\n; Windows directory (usually C:\\\\windows)\\n; See the PHP docs for more specific information.\\n; https://php.net/configuration.file\\n\\n; The syntax of the file is extremely simple.  Whitespace and lines\\n; beginning with a semicolon are silently ignored (as you probably guessed).\\n; Section headers (e.g. [Foo]) are also silently ignored, even though\\n; they might mean something in the future.\\n\\n; Directives following the section heading [PATH=/www/mysite] only\\n; apply to PHP files in the /www/mysite directory.  Directives\\n; following the section heading [HOST=www.example.com] only apply to\\n; PHP files served from www.example.com.  Directives set in these\\n; special sections cannot be overridden by user-defined INI files or\\n; at runtime. Currently, [PATH=] and [HOST=] sections only work under\\n; CGI/FastCGI.\\n; https://php.net/ini.sections\\n\\n; Directives are specified using the following syntax:\\n; directive = value\\n; Directive names are *case sensitive* - foo=bar is different from FOO=bar.\\n; Directives are variables used to configure PHP or PHP extensions.\\n; There is no name validation.  If PHP can't find an expected\\n; directive because it is not set or is mistyped, a default value will be used.\\n\\n; The value can be a string, a number, a PHP constant (e.g. E_ALL or M_PI), one\\n; of the INI constants (On, Off, True, False, Yes, No and None) or an expression\\n; (e.g. E_ALL & ~E_NOTICE), a quoted string (\\\"bar\\\"), or a reference to a\\n; previously set variable or directive (e.g. ${foo})\\n\\n; Expressions in the INI file are limited to bitwise operators and parentheses:\\n; |  bitwise OR\\n; ^  bitwise XOR\\n; &  bitwise AND\\n; ~  bitwise NOT\\n; !  boolean NOT\\n\\n; Boolean flags can be turned on using the values 1, On, True or Yes.\\n; They can be turned off using the values 0, Off, False or No.\\n\\n; An empty string can be denoted by simply not writing anything after the equal\\n; sign, or by using the None keyword:\\n\\n; foo =         ; sets foo to an empty string\\n; foo = None    ; sets foo to an empty string\\n; foo = \\\"None\\\"  ; sets foo to the string 'None'\\n\\n; If you use constants in your value, and these constants belong to a\\n; dynamically loaded extension (either a PHP extension or a Zend extension),\\n; you may only use these constants *after* the line that loads the extension.\\n\\n;;;;;;;;;;;;;;;;;;;\\n; About this file ;\\n;;;;;;;;;;;;;;;;;;;\\n; PHP comes packaged with two INI files. One that is recommended to be used\\n; in production environments and one that is recommended to be used in\\n; development environments.\\n\\n; php.ini-production contains settings which hold security, performance and\\n; best practices at its core. But please be aware, these settings may break\\n; compatibility with older or less security conscience applications. We\\n; recommending using the production ini in production and testing environments.\\n\\n; php.ini-development is very similar to its production variant, except it is\\n; much more verbose when it comes to errors. We recommend using the\\n; development version only in development environments, as errors shown to\\n; application users can inadvertently leak otherwise secure information.\\n\\n; This is the php.ini-production INI file.\\n\\n;;;;;;;;;;;;;;;;;;;\\n; Quick Reference ;\\n;;;;;;;;;;;;;;;;;;;\\n\\n; The following are all the settings which are different in either the production\\n; or development versions of the INIs with respect to PHP's default behavior.\\n; Please see the actual settings later in the document for more details as to why\\n; we recommend these changes in PHP's behavior.\\n\\n; display_errors\\n;   Default Value: On\\n;   Development Value: On\\n;   Production Value: Off\\n\\n; display_startup_errors\\n;   Default Value: On\\n;   Development Value: On\\n;   Production Value: Off\\n\\n; error_reporting\\n;   Default Value: E_ALL\\n;   Development Value: E_ALL\\n;   Production Value: E_ALL & ~E_DEPRECATED & ~E_STRICT\\n\\n; log_errors\\n;   Default Value: Off\\n;   Development Value: On\\n;   Production Value: On\\n\\n; max_input_time\\n;   Default Value: -1 (Unlimited)\\n;   Development Value: 60 (60 seconds)\\n;   Production Value: 60 (60 seconds)\\n\\n; output_buffering\\n;   Default Value: Off\\n;   Development Value: 4096\\n;   Production Value: 4096\\n\\n; register_argc_argv\\n;   Default Value: On\\n;   Development Value: Off\\n;   Production Value: Off\\n\\n; request_order\\n;   Default Value: None\\n;   Development Value: \\\"GP\\\"\\n;   Production Value: \\\"GP\\\"\\n\\n; session.gc_divisor\\n;   Default Value: 100\\n;   Development Value: 1000\\n;   Production Value: 1000\\n\\n; session.sid_bits_per_character\\n;   Default Value: 4\\n;   Development Value: 5\\n;   Production Value: 5\\n\\n; short_open_tag\\n;   Default Value: On\\n;   Development Value: Off\\n;   Production Value: Off\\n\\n; variables_order\\n;   Default Value: \\\"EGPCS\\\"\\n;   Development Value: \\\"GPCS\\\"\\n;   Production Value: \\\"GPCS\\\"\\n\\n; zend.exception_ignore_args\\n;   Default Value: Off\\n;   Development Value: Off\\n;   Production Value: On\\n\\n; zend.exception_string_param_max_len\\n;   Default Value: 15\\n;   Development Value: 15\\n;   Production Value: 0\\n\\n;;;;;;;;;;;;;;;;;;;;\\n; php.ini Options  ;\\n;;;;;;;;;;;;;;;;;;;;\\n; Name for user-defined php.ini (.htaccess) files. Default is \\\".user.ini\\\"\\n;user_ini.filename = \\\".user.ini\\\"\\n\\n; To disable this feature set this option to an empty value\\n;user_ini.filename =\\n\\n; TTL for user-defined php.ini files (time-to-live) in seconds. Default is 300 seconds (5 minutes)\\n;user_ini.cache_ttl = 300\\n\\n;;;;;;;;;;;;;;;;;;;;\\n; Language Options ;\\n;;;;;;;;;;;;;;;;;;;;\\n\\n; Enable the PHP scripting language engine under Apache.\\n; https://php.net/engine\\nengine = On\\n\\n; This directive determines whether or not PHP will recognize code between\\n; <? and ?> tags as PHP source which should be processed as such. It is\\n; generally recommended that <?php and ?> should be used and that this feature\\n; should be disabled, as enabling it may result in issues when generating XML\\n; documents, however this remains supported for backward compatibility reasons.\\n; Note that this directive does not control the <?= shorthand tag, which can be\\n; used regardless of this directive.\\n; Default Value: On\\n; Development Value: Off\\n; Production Value: Off\\n; https://php.net/short-open-tag\\nshort_open_tag = Off\\n\\n; The number of significant digits displayed in floating point numbers.\\n; https://php.net/precision\\nprecision = 14\\n\\n; Output buffering is a mechanism for controlling how much output data\\n; (excluding headers and cookies) PHP should keep internally before pushing that\\n; data to the client. If your application's output exceeds this setting, PHP\\n; will send that data in chunks of roughly the size you specify.\\n; Turning on this setting and managing its maximum buffer size can yield some\\n; interesting side-effects depending on your application and web server.\\n; You may be able to send headers and cookies after you've already sent output\\n; through print or echo. You also may see performance benefits if your server is\\n; emitting less packets due to buffered output versus PHP streaming the output\\n; as it gets it. On production servers, 4096 bytes is a good setting for performance\\n; reasons.\\n; Note: Output buffering can also be controlled via Output Buffering Control\\n;   functions.\\n; Possible Values:\\n;   On = Enabled and buffer is unlimited. (Use with caution)\\n;   Off = Disabled\\n;   Integer = Enables the buffer and sets its maximum size in bytes.\\n; Note: This directive is hardcoded to Off for the CLI SAPI\\n; Default Value: Off\\n; Development Value: 4096\\n; Production Value: 4096\\n; https://php.net/output-buffering\\noutput_buffering = 4096\\n\\n; You can redirect all of the output of your scripts to a function.  For\\n; example, if you set output_handler to \\\"mb_output_handler\\\", character\\n; encoding will be transparently converted to the specified encoding.\\n; Setting any output handler automatically turns on output buffering.\\n; Note: People who wrote portable scripts should not depend on this ini\\n;   directive. Instead, explicitly set the output handler using ob_start().\\n;   Using this ini directive may cause problems unless you know what script\\n;   is doing.\\n; Note: You cannot use both \\\"mb_output_handler\\\" with \\\"ob_iconv_handler\\\"\\n;   and you cannot use both \\\"ob_gzhandler\\\" and \\\"zlib.output_compression\\\".\\n; Note: output_handler must be empty if this is set 'On' !!!!\\n;   Instead you must use zlib.output_handler.\\n; https://php.net/output-handler\\n;output_handler =\\n\\n; URL rewriter function rewrites URL on the fly by using\\n; output buffer. You can set target tags by this configuration.\\n; \\\"form\\\" tag is special tag. It will add hidden input tag to pass values.\\n; Refer to session.trans_sid_tags for usage.\\n; Default Value: \\\"form=\\\"\\n; Development Value: \\\"form=\\\"\\n; Production Value: \\\"form=\\\"\\n;url_rewriter.tags\\n\\n; URL rewriter will not rewrite absolute URL nor form by default. To enable\\n; absolute URL rewrite, allowed hosts must be defined at RUNTIME.\\n; Refer to session.trans_sid_hosts for more details.\\n; Default Value: \\\"\\\"\\n; Development Value: \\\"\\\"\\n; Production Value: \\\"\\\"\\n;url_rewriter.hosts\\n\\n; Transparent output compression using the zlib library\\n; Valid values for this option are 'off', 'on', or a specific buffer size\\n; to be used for compression (default is 4KB)\\n; Note: Resulting chunk size may vary due to nature of compression. PHP\\n;   outputs chunks that are few hundreds bytes each as a result of\\n;   compression. If you prefer a larger chunk size for better\\n;   performance, enable output_buffering in addition.\\n; Note: You need to use zlib.output_handler instead of the standard\\n;   output_handler, or otherwise the output will be corrupted.\\n; https://php.net/zlib.output-compression\\nzlib.output_compression = Off\\n\\n; https://php.net/zlib.output-compression-level\\n;zlib.output_compression_level = -1\\n\\n; You cannot specify additional output handlers if zlib.output_compression\\n; is activated here. This setting does the same as output_handler but in\\n; a different order.\\n; https://php.net/zlib.output-handler\\n;zlib.output_handler =\\n\\n; Implicit flush tells PHP to tell the output layer to flush itself\\n; automatically after every output block.  This is equivalent to calling the\\n; PHP function flush() after each and every call to print() or echo() and each\\n; and every HTML block.  Turning this option on has serious performance\\n; implications and is generally recommended for debugging purposes only.\\n; https://php.net/implicit-flush\\n; Note: This directive is hardcoded to On for the CLI SAPI\\nimplicit_flush = Off\\n\\n; The unserialize callback function will be called (with the undefined class'\\n; name as parameter), if the unserializer finds an undefined class\\n; which should be instantiated. A warning appears if the specified function is\\n; not defined, or if the function doesn't include/implement the missing class.\\n; So only set this entry, if you really want to implement such a\\n; callback-function.\\nunserialize_callback_func =\\n\\n; The unserialize_max_depth specifies the default depth limit for unserialized\\n; structures. Setting the depth limit too high may result in stack overflows\\n; during unserialization. The unserialize_max_depth ini setting can be\\n; overridden by the max_depth option on individual unserialize() calls.\\n; A value of 0 disables the depth limit.\\n;unserialize_max_depth = 4096\\n\\n; When floats & doubles are serialized, store serialize_precision significant\\n; digits after the floating point. The default value ensures that when floats\\n; are decoded with unserialize, the data will remain the same.\\n; The value is also used for json_encode when encoding double values.\\n; If -1 is used, then dtoa mode 0 is used which automatically select the best\\n; precision.\\nserialize_precision = -1\\n\\n; open_basedir, if set, limits all file operations to the defined directory\\n; and below.  This directive makes most sense if used in a per-directory\\n; or per-virtualhost web server configuration file.\\n; Note: disables the realpath cache\\n; https://php.net/open-basedir\\n;open_basedir =\\n\\n; This directive allows you to disable certain functions.\\n; It receives a comma-delimited list of function names.\\n; https://php.net/disable-functions\\ndisable_functions =\\n\\n; This directive allows you to disable certain classes.\\n; It receives a comma-delimited list of class names.\\n; https://php.net/disable-classes\\ndisable_classes =\\n\\n; Colors for Syntax Highlighting mode.  Anything that's acceptable in\\n; <span style=\\\"color: ???????\\\"> would work.\\n; https://php.net/syntax-highlighting\\n;highlight.string  = #DD0000\\n;highlight.comment = #FF9900\\n;highlight.keyword = #007700\\n;highlight.default = #0000BB\\n;highlight.html    = #000000\\n\\n; If enabled, the request will be allowed to complete even if the user aborts\\n; the request. Consider enabling it if executing long requests, which may end up\\n; being interrupted by the user or a browser timing out. PHP's default behavior\\n; is to disable this feature.\\n; https://php.net/ignore-user-abort\\n;ignore_user_abort = On\\n\\n; Determines the size of the realpath cache to be used by PHP. This value should\\n; be increased on systems where PHP opens many files to reflect the quantity of\\n; the file operations performed.\\n; Note: if open_basedir is set, the cache is disabled\\n; https://php.net/realpath-cache-size\\n;realpath_cache_size = 4096k\\n\\n; Duration of time, in seconds for which to cache realpath information for a given\\n; file or directory. For systems with rarely changing files, consider increasing this\\n; value.\\n; https://php.net/realpath-cache-ttl\\n;realpath_cache_ttl = 120\\n\\n; Enables or disables the circular reference collector.\\n; https://php.net/zend.enable-gc\\nzend.enable_gc = On\\n\\n; If enabled, scripts may be written in encodings that are incompatible with\\n; the scanner.  CP936, Big5, CP949 and Shift_JIS are the examples of such\\n; encodings.  To use this feature, mbstring extension must be enabled.\\n;zend.multibyte = Off\\n\\n; Allows to set the default encoding for the scripts.  This value will be used\\n; unless \\\"declare(encoding=...)\\\" directive appears at the top of the script.\\n; Only affects if zend.multibyte is set.\\n;zend.script_encoding =\\n\\n; Allows to include or exclude arguments from stack traces generated for exceptions.\\n; In production, it is recommended to turn this setting on to prohibit the output\\n; of sensitive information in stack traces\\n; Default Value: Off\\n; Development Value: Off\\n; Production Value: On\\nzend.exception_ignore_args = On\\n\\n; Allows setting the maximum string length in an argument of a stringified stack trace\\n; to a value between 0 and 1000000.\\n; This has no effect when zend.exception_ignore_args is enabled.\\n; Default Value: 15\\n; Development Value: 15\\n; Production Value: 0\\n; In production, it is recommended to set this to 0 to reduce the output\\n; of sensitive information in stack traces.\\nzend.exception_string_param_max_len = 0\\n\\n;;;;;;;;;;;;;;;;;\\n; Miscellaneous ;\\n;;;;;;;;;;;;;;;;;\\n\\n; Decides whether PHP may expose the fact that it is installed on the server\\n; (e.g. by adding its signature to the Web server header).  It is no security\\n; threat in any way, but it makes it possible to determine whether you use PHP\\n; on your server or not.\\n; https://php.net/expose-php\\nexpose_php = Off\\n\\n;;;;;;;;;;;;;;;;;;;\\n; Resource Limits ;\\n;;;;;;;;;;;;;;;;;;;\\n\\n; Maximum execution time of each script, in seconds\\n; https://php.net/max-execution-time\\n; Note: This directive is hardcoded to 0 for the CLI SAPI\\nmax_execution_time = 60\\n\\n; Maximum amount of time each script may spend parsing request data. It's a good\\n; idea to limit this time on productions servers in order to eliminate unexpectedly\\n; long running scripts.\\n; Note: This directive is hardcoded to -1 for the CLI SAPI\\n; Default Value: -1 (Unlimited)\\n; Development Value: 60 (60 seconds)\\n; Production Value: 60 (60 seconds)\\n; https://php.net/max-input-time\\nmax_input_time = 60\\n\\n; Maximum input variable nesting level\\n; https://php.net/max-input-nesting-level\\n;max_input_nesting_level = 64\\n\\n; How many GET/POST/COOKIE input variables may be accepted\\n;max_input_vars = 1000\\n\\n; How many multipart body parts (combined input variable and file uploads) may\\n; be accepted.\\n; Default Value: -1 (Sum of max_input_vars and max_file_uploads)\\n;max_multipart_body_parts = 1500\\n\\n; Maximum amount of memory a script may consume\\n; https://php.net/memory-limit\\nmemory_limit = 500M\\n\\n;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;\\n; Error handling and logging ;\\n;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;\\n\\n; This directive informs PHP of which errors, warnings and notices you would like\\n; it to take action for. The recommended way of setting values for this\\n; directive is through the use of the error level constants and bitwise\\n; operators. The error level constants are below here for convenience as well as\\n; some common settings and their meanings.\\n; By default, PHP is set to take action on all errors, notices and warnings EXCEPT\\n; those related to E_NOTICE and E_STRICT, which together cover best practices and\\n; recommended coding standards in PHP. For performance reasons, this is the\\n; recommend error reporting setting. Your production server shouldn't be wasting\\n; resources complaining about best practices and coding standards. That's what\\n; development servers and development settings are for.\\n; Note: The php.ini-development file has this setting as E_ALL. This\\n; means it pretty much reports everything which is exactly what you want during\\n; development and early testing.\\n;\\n; Error Level Constants:\\n; E_ALL             - All errors and warnings\\n; E_ERROR           - fatal run-time errors\\n; E_RECOVERABLE_ERROR  - almost fatal run-time errors\\n; E_WARNING         - run-time warnings (non-fatal errors)\\n; E_PARSE           - compile-time parse errors\\n; E_NOTICE          - run-time notices (these are warnings which often result\\n;                     from a bug in your code, but it's possible that it was\\n;                     intentional (e.g., using an uninitialized variable and\\n;                     relying on the fact it is automatically initialized to an\\n;                     empty string)\\n; E_STRICT          - run-time notices, enable to have PHP suggest changes\\n;                     to your code which will ensure the best interoperability\\n;                     and forward compatibility of your code\\n; E_CORE_ERROR      - fatal errors that occur during PHP's initial startup\\n; E_CORE_WARNING    - warnings (non-fatal errors) that occur during PHP's\\n;                     initial startup\\n; E_COMPILE_ERROR   - fatal compile-time errors\\n; E_COMPILE_WARNING - compile-time warnings (non-fatal errors)\\n; E_USER_ERROR      - user-generated error message\\n; E_USER_WARNING    - user-generated warning message\\n; E_USER_NOTICE     - user-generated notice message\\n; E_DEPRECATED      - warn about code that will not work in future versions\\n;                     of PHP\\n; E_USER_DEPRECATED - user-generated deprecation warnings\\n;\\n; Common Values:\\n;   E_ALL (Show all errors, warnings and notices including coding standards.)\\n;   E_ALL & ~E_NOTICE  (Show all errors, except for notices)\\n;   E_ALL & ~E_NOTICE & ~E_STRICT  (Show all errors, except for notices and coding standards warnings.)\\n;   E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR  (Show only errors)\\n; Default Value: E_ALL\\n; Development Value: E_ALL\\n; Production Value: E_ALL & ~E_DEPRECATED & ~E_STRICT\\n; https://php.net/error-reporting\\nerror_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT\\n\\n; This directive controls whether or not and where PHP will output errors,\\n; notices and warnings too. Error output is very useful during development, but\\n; it could be very dangerous in production environments. Depending on the code\\n; which is triggering the error, sensitive information could potentially leak\\n; out of your application such as database usernames and passwords or worse.\\n; For production environments, we recommend logging errors rather than\\n; sending them to STDOUT.\\n; Possible Values:\\n;   Off = Do not display any errors\\n;   stderr = Display errors to STDERR (affects only CGI/CLI binaries!)\\n;   On or stdout = Display errors to STDOUT\\n; Default Value: On\\n; Development Value: On\\n; Production Value: Off\\n; https://php.net/display-errors\\ndisplay_errors = Off\\n\\n; The display of errors which occur during PHP's startup sequence are handled\\n; separately from display_errors. We strongly recommend you set this to 'off'\\n; for production servers to avoid leaking configuration details.\\n; Default Value: On\\n; Development Value: On\\n; Production Value: Off\\n; https://php.net/display-startup-errors\\ndisplay_startup_errors = Off\\n\\n; Besides displaying errors, PHP can also log errors to locations such as a\\n; server-specific log, STDERR, or a location specified by the error_log\\n; directive found below. While errors should not be displayed on productions\\n; servers they should still be monitored and logging is a great way to do that.\\n; Default Value: Off\\n; Development Value: On\\n; Production Value: On\\n; https://php.net/log-errors\\nlog_errors = On\\n\\n; Do not log repeated messages. Repeated errors must occur in same file on same\\n; line unless ignore_repeated_source is set true.\\n; https://php.net/ignore-repeated-errors\\nignore_repeated_errors = Off\\n\\n; Ignore source of message when ignoring repeated messages. When this setting\\n; is On you will not log errors with repeated messages from different files or\\n; source lines.\\n; https://php.net/ignore-repeated-source\\nignore_repeated_source = Off\\n\\n; If this parameter is set to Off, then memory leaks will not be shown (on\\n; stdout or in the log). This is only effective in a debug compile, and if\\n; error reporting includes E_WARNING in the allowed list\\n; https://php.net/report-memleaks\\nreport_memleaks = On\\n\\n; This setting is off by default.\\n;report_zend_debug = 0\\n\\n; Turn off normal error reporting and emit XML-RPC error XML\\n; https://php.net/xmlrpc-errors\\n;xmlrpc_errors = 0\\n\\n; An XML-RPC faultCode\\n;xmlrpc_error_number = 0\\n\\n; When PHP displays or logs an error, it has the capability of formatting the\\n; error message as HTML for easier reading. This directive controls whether\\n; the error message is formatted as HTML or not.\\n; Note: This directive is hardcoded to Off for the CLI SAPI\\n; https://php.net/html-errors\\n;html_errors = On\\n\\n; If html_errors is set to On *and* docref_root is not empty, then PHP\\n; produces clickable error messages that direct to a page describing the error\\n; or function causing the error in detail.\\n; You can download a copy of the PHP manual from https://php.net/docs\\n; and change docref_root to the base URL of your local copy including the\\n; leading '/'. You must also specify the file extension being used including\\n; the dot. PHP's default behavior is to leave these settings empty, in which\\n; case no links to documentation are generated.\\n; Note: Never use this feature for production boxes.\\n; https://php.net/docref-root\\n; Examples\\n;docref_root = \\\"/phpmanual/\\\"\\n\\n; https://php.net/docref-ext\\n;docref_ext = .html\\n\\n; String to output before an error message. PHP's default behavior is to leave\\n; this setting blank.\\n; https://php.net/error-prepend-string\\n; Example:\\n;error_prepend_string = \\\"<span style='color: #ff0000'>\\\"\\n\\n; String to output after an error message. PHP's default behavior is to leave\\n; this setting blank.\\n; https://php.net/error-append-string\\n; Example:\\n;error_append_string = \\\"</span>\\\"\\n\\n; Log errors to specified file. PHP's default behavior is to leave this value\\n; empty.\\n; https://php.net/error-log\\n; Example:\\n;error_log = php_errors.log\\n; Log errors to syslog (Event Log on Windows).\\n;error_log = syslog\\n\\n; The syslog ident is a string which is prepended to every message logged\\n; to syslog. Only used when error_log is set to syslog.\\n;syslog.ident = php\\n\\n; The syslog facility is used to specify what type of program is logging\\n; the message. Only used when error_log is set to syslog.\\n;syslog.facility = user\\n\\n; Set this to disable filtering control characters (the default).\\n; Some loggers only accept NVT-ASCII, others accept anything that's not\\n; control characters. If your logger accepts everything, then no filtering\\n; is needed at all.\\n; Allowed values are:\\n;   ascii (all printable ASCII characters and NL)\\n;   no-ctrl (all characters except control characters)\\n;   all (all characters)\\n;   raw (like \\\"all\\\", but messages are not split at newlines)\\n; https://php.net/syslog.filter\\n;syslog.filter = ascii\\n\\n;windows.show_crt_warning\\n; Default value: 0\\n; Development value: 0\\n; Production value: 0\\n\\n;;;;;;;;;;;;;;;;;\\n; Data Handling ;\\n;;;;;;;;;;;;;;;;;\\n\\n; The separator used in PHP generated URLs to separate arguments.\\n; PHP's default setting is \\\"&\\\".\\n; https://php.net/arg-separator.output\\n; Example:\\n;arg_separator.output = \\\"&amp;\\\"\\n\\n; List of separator(s) used by PHP to parse input URLs into variables.\\n; PHP's default setting is \\\"&\\\".\\n; NOTE: Every character in this directive is considered as separator!\\n; https://php.net/arg-separator.input\\n; Example:\\n;arg_separator.input = \\\";&\\\"\\n\\n; This directive determines which super global arrays are registered when PHP\\n; starts up. G,P,C,E & S are abbreviations for the following respective super\\n; globals: GET, POST, COOKIE, ENV and SERVER. There is a performance penalty\\n; paid for the registration of these arrays and because ENV is not as commonly\\n; used as the others, ENV is not recommended on productions servers. You\\n; can still get access to the environment variables through getenv() should you\\n; need to.\\n; Default Value: \\\"EGPCS\\\"\\n; Development Value: \\\"GPCS\\\"\\n; Production Value: \\\"GPCS\\\";\\n; https://php.net/variables-order\\nvariables_order = \\\"GPCS\\\"\\n\\n; This directive determines which super global data (G,P & C) should be\\n; registered into the super global array REQUEST. If so, it also determines\\n; the order in which that data is registered. The values for this directive\\n; are specified in the same manner as the variables_order directive,\\n; EXCEPT one. Leaving this value empty will cause PHP to use the value set\\n; in the variables_order directive. It does not mean it will leave the super\\n; globals array REQUEST empty.\\n; Default Value: None\\n; Development Value: \\\"GP\\\"\\n; Production Value: \\\"GP\\\"\\n; https://php.net/request-order\\nrequest_order = \\\"GP\\\"\\n\\n; This directive determines whether PHP registers $argv & $argc each time it\\n; runs. $argv contains an array of all the arguments passed to PHP when a script\\n; is invoked. $argc contains an integer representing the number of arguments\\n; that were passed when the script was invoked. These arrays are extremely\\n; useful when running scripts from the command line. When this directive is\\n; enabled, registering these variables consumes CPU cycles and memory each time\\n; a script is executed. For performance reasons, this feature should be disabled\\n; on production servers.\\n; Note: This directive is hardcoded to On for the CLI SAPI\\n; Default Value: On\\n; Development Value: Off\\n; Production Value: Off\\n; https://php.net/register-argc-argv\\nregister_argc_argv = Off\\n\\n; When enabled, the ENV, REQUEST and SERVER variables are created when they're\\n; first used (Just In Time) instead of when the script starts. If these\\n; variables are not used within a script, having this directive on will result\\n; in a performance gain. The PHP directive register_argc_argv must be disabled\\n; for this directive to have any effect.\\n; https://php.net/auto-globals-jit\\nauto_globals_jit = On\\n\\n; Whether PHP will read the POST data.\\n; This option is enabled by default.\\n; Most likely, you won't want to disable this option globally. It causes $_POST\\n; and $_FILES to always be empty; the only way you will be able to read the\\n; POST data will be through the php://input stream wrapper. This can be useful\\n; to proxy requests or to process the POST data in a memory efficient fashion.\\n; https://php.net/enable-post-data-reading\\n;enable_post_data_reading = Off\\n\\n; Maximum size of POST data that PHP will accept.\\n; Its value may be 0 to disable the limit. It is ignored if POST data reading\\n; is disabled through enable_post_data_reading.\\n; https://php.net/post-max-size\\npost_max_size = 8M\\n\\n; Automatically add files before PHP document.\\n; https://php.net/auto-prepend-file\\nauto_prepend_file =\\n\\n; Automatically add files after PHP document.\\n; https://php.net/auto-append-file\\nauto_append_file =\\n\\n; By default, PHP will output a media type using the Content-Type header. To\\n; disable this, simply set it to be empty.\\n;\\n; PHP's built-in default media type is set to text/html.\\n; https://php.net/default-mimetype\\ndefault_mimetype = \\\"text/html\\\"\\n\\n; PHP's default character set is set to UTF-8.\\n; https://php.net/default-charset\\ndefault_charset = \\\"UTF-8\\\"\\n\\n; PHP internal character encoding is set to empty.\\n; If empty, default_charset is used.\\n; https://php.net/internal-encoding\\n;internal_encoding =\\n\\n; PHP input character encoding is set to empty.\\n; If empty, default_charset is used.\\n; https://php.net/input-encoding\\n;input_encoding =\\n\\n; PHP output character encoding is set to empty.\\n; If empty, default_charset is used.\\n; See also output_buffer.\\n; https://php.net/output-encoding\\n;output_encoding =\\n\\n;;;;;;;;;;;;;;;;;;;;;;;;;\\n; Paths and Directories ;\\n;;;;;;;;;;;;;;;;;;;;;;;;;\\n\\n; UNIX: \\\"/path1:/path2\\\"\\n;include_path = \\\".:/usr/share/php\\\"\\n;\\n; Windows: \\\"\\\\path1;\\\\path2\\\"\\n;include_path = \\\".;c:\\\\php\\\\includes\\\"\\n;\\n; PHP's default setting for include_path is \\\".;/path/to/php/pear\\\"\\n; https://php.net/include-path\\n\\n; The root of the PHP pages, used only if nonempty.\\n; if PHP was not compiled with FORCE_REDIRECT, you SHOULD set doc_root\\n; if you are running php as a CGI under any web server (other than IIS)\\n; see documentation for security issues.  The alternate is to use the\\n; cgi.force_redirect configuration below\\n; https://php.net/doc-root\\ndoc_root =\\n\\n; The directory under which PHP opens the script using /~username used only\\n; if nonempty.\\n; https://php.net/user-dir\\nuser_dir =\\n\\n; Directory in which the loadable extensions (modules) reside.\\n; https://php.net/extension-dir\\n;extension_dir = \\\"./\\\"\\n; On windows:\\n;extension_dir = \\\"ext\\\"\\n\\n; Directory where the temporary files should be placed.\\n; Defaults to the system default (see sys_get_temp_dir)\\n;sys_temp_dir = \\\"/tmp\\\"\\n\\n; Whether or not to enable the dl() function.  The dl() function does NOT work\\n; properly in multithreaded servers, such as IIS or Zeus, and is automatically\\n; disabled on them.\\n; https://php.net/enable-dl\\nenable_dl = Off\\n\\n; cgi.force_redirect is necessary to provide security running PHP as a CGI under\\n; most web servers.  Left undefined, PHP turns this on by default.  You can\\n; turn it off here AT YOUR OWN RISK\\n; **You CAN safely turn this off for IIS, in fact, you MUST.**\\n; https://php.net/cgi.force-redirect\\n;cgi.force_redirect = 1\\n\\n; if cgi.nph is enabled it will force cgi to always sent Status: 200 with\\n; every request. PHP's default behavior is to disable this feature.\\n;cgi.nph = 1\\n\\n; if cgi.force_redirect is turned on, and you are not running under Apache or Netscape\\n; (iPlanet) web servers, you MAY need to set an environment variable name that PHP\\n; will look for to know it is OK to continue execution.  Setting this variable MAY\\n; cause security issues, KNOW WHAT YOU ARE DOING FIRST.\\n; https://php.net/cgi.redirect-status-env\\n;cgi.redirect_status_env =\\n\\n; cgi.fix_pathinfo provides *real* PATH_INFO/PATH_TRANSLATED support for CGI.  PHP's\\n; previous behaviour was to set PATH_TRANSLATED to SCRIPT_FILENAME, and to not grok\\n; what PATH_INFO is.  For more information on PATH_INFO, see the cgi specs.  Setting\\n; this to 1 will cause PHP CGI to fix its paths to conform to the spec.  A setting\\n; of zero causes PHP to behave as before.  Default is 1.  You should fix your scripts\\n; to use SCRIPT_FILENAME rather than PATH_TRANSLATED.\\n; https://php.net/cgi.fix-pathinfo\\n;cgi.fix_pathinfo=1\\n\\n; if cgi.discard_path is enabled, the PHP CGI binary can safely be placed outside\\n; of the web tree and people will not be able to circumvent .htaccess security.\\n;cgi.discard_path=1\\n\\n; FastCGI under IIS supports the ability to impersonate\\n; security tokens of the calling client.  This allows IIS to define the\\n; security context that the request runs under.  mod_fastcgi under Apache\\n; does not currently support this feature (03/17/2002)\\n; Set to 1 if running under IIS.  Default is zero.\\n; https://php.net/fastcgi.impersonate\\n;fastcgi.impersonate = 1\\n\\n; Disable logging through FastCGI connection. PHP's default behavior is to enable\\n; this feature.\\n;fastcgi.logging = 0\\n\\n; cgi.rfc2616_headers configuration option tells PHP what type of headers to\\n; use when sending HTTP response code. If set to 0, PHP sends Status: header that\\n; is supported by Apache. When this option is set to 1, PHP will send\\n; RFC2616 compliant header.\\n; Default is zero.\\n; https://php.net/cgi.rfc2616-headers\\n;cgi.rfc2616_headers = 0\\n\\n; cgi.check_shebang_line controls whether CGI PHP checks for line starting with #!\\n; (shebang) at the top of the running script. This line might be needed if the\\n; script support running both as stand-alone script and via PHP CGI<. PHP in CGI\\n; mode skips this line and ignores its content if this directive is turned on.\\n; https://php.net/cgi.check-shebang-line\\n;cgi.check_shebang_line=1\\n\\n;;;;;;;;;;;;;;;;\\n; File Uploads ;\\n;;;;;;;;;;;;;;;;\\n\\n; Whether to allow HTTP file uploads.\\n; https://php.net/file-uploads\\nfile_uploads = On\\n\\n; Temporary directory for HTTP uploaded files (will use system default if not\\n; specified).\\n; https://php.net/upload-tmp-dir\\n;upload_tmp_dir =\\n\\n; Maximum allowed size for uploaded files.\\n; https://php.net/upload-max-filesize\\nupload_max_filesize = 2M\\n\\n; Maximum number of files that can be uploaded via a single request\\nmax_file_uploads = 20\\n\\n;;;;;;;;;;;;;;;;;;\\n; Fopen wrappers ;\\n;;;;;;;;;;;;;;;;;;\\n\\n; Whether to allow the treatment of URLs (like http:// or ftp://) as files.\\n; https://php.net/allow-url-fopen\\nallow_url_fopen = On\\n\\n; Whether to allow include/require to open URLs (like https:// or ftp://) as files.\\n; https://php.net/allow-url-include\\nallow_url_include = Off\\n\\n; Define the anonymous ftp password (your email address). PHP's default setting\\n; for this is empty.\\n; https://php.net/from\\n;from=\\\"john@doe.com\\\"\\n\\n; Define the User-Agent string. PHP's default setting for this is empty.\\n; https://php.net/user-agent\\n;user_agent=\\\"PHP\\\"\\n\\n; Default timeout for socket based streams (seconds)\\n; https://php.net/default-socket-timeout\\ndefault_socket_timeout = 60\\n\\n; If your scripts have to deal with files from Macintosh systems,\\n; or you are running on a Mac and need to deal with files from\\n; unix or win32 systems, setting this flag will cause PHP to\\n; automatically detect the EOL character in those files so that\\n; fgets() and file() will work regardless of the source of the file.\\n; https://php.net/auto-detect-line-endings\\n;auto_detect_line_endings = Off\\n\\n;;;;;;;;;;;;;;;;;;;;;;\\n; Dynamic Extensions ;\\n;;;;;;;;;;;;;;;;;;;;;;\\n\\n; If you wish to have an extension loaded automatically, use the following\\n; syntax:\\n;\\n;   extension=modulename\\n;\\n; For example:\\n;\\n;   extension=mysqli\\n;\\n; When the extension library to load is not located in the default extension\\n; directory, You may specify an absolute path to the library file:\\n;\\n;   extension=/path/to/extension/mysqli.so\\n;\\n; Note : The syntax used in previous PHP versions ('extension=<ext>.so' and\\n; 'extension='php_<ext>.dll') is supported for legacy reasons and may be\\n; deprecated in a future PHP major version. So, when it is possible, please\\n; move to the new ('extension=<ext>) syntax.\\n;\\n; Notes for Windows environments :\\n;\\n; - Many DLL files are located in the ext/\\n;   extension folders as well as the separate PECL DLL download.\\n;   Be sure to appropriately set the extension_dir directive.\\n;\\n;extension=bz2\\n\\n; The ldap extension must be before curl if OpenSSL 1.0.2 and OpenLDAP is used\\n; otherwise it results in segfault when unloading after using SASL.\\n; See https://github.com/php/php-src/issues/8620 for more info.\\n;extension=ldap\\n\\n;extension=curl\\n;extension=ffi\\n;extension=ftp\\n;extension=fileinfo\\n;extension=gd\\n;extension=gettext\\n;extension=gmp\\n;extension=intl\\n;extension=imap\\n;extension=mbstring\\n;extension=exif      ; Must be after mbstring as it depends on it\\n;extension=mysqli\\n;extension=oci8_12c  ; Use with Oracle Database 12c Instant Client\\n;extension=oci8_19  ; Use with Oracle Database 19 Instant Client\\n;extension=odbc\\n;extension=openssl\\n;extension=pdo_firebird\\n;extension=pdo_mysql\\n;extension=pdo_oci\\n;extension=pdo_odbc\\n;extension=pdo_pgsql\\n;extension=pdo_sqlite\\n;extension=pgsql\\n;extension=shmop\\n\\n; The MIBS data available in the PHP distribution must be installed.\\n; See https://www.php.net/manual/en/snmp.installation.php\\n;extension=snmp\\n\\n;extension=soap\\n;extension=sockets\\n;extension=sodium\\n;extension=sqlite3\\n;extension=tidy\\n;extension=xsl\\n;extension=zip\\nextension=sysvmsg\\n;zend_extension=opcache\\n\\n;;;;;;;;;;;;;;;;;;;\\n; Module Settings ;\\n;;;;;;;;;;;;;;;;;;;\\n\\n[CLI Server]\\n; Whether the CLI web server uses ANSI color coding in its terminal output.\\ncli_server.color = On\\n\\n[Date]\\n; Defines the default timezone used by the date functions\\n; https://php.net/date.timezone\\n;date.timezone =\\n\\n; https://php.net/date.default-latitude\\n;date.default_latitude = 31.7667\\n\\n; https://php.net/date.default-longitude\\n;date.default_longitude = 35.2333\\n\\n; https://php.net/date.sunrise-zenith\\n;date.sunrise_zenith = 90.833333\\n\\n; https://php.net/date.sunset-zenith\\n;date.sunset_zenith = 90.833333\\n\\n[filter]\\n; https://php.net/filter.default\\n;filter.default = unsafe_raw\\n\\n; https://php.net/filter.default-flags\\n;filter.default_flags =\\n\\n[iconv]\\n; Use of this INI entry is deprecated, use global input_encoding instead.\\n; If empty, default_charset or input_encoding or iconv.input_encoding is used.\\n; The precedence is: default_charset < input_encoding < iconv.input_encoding\\n;iconv.input_encoding =\\n\\n; Use of this INI entry is deprecated, use global internal_encoding instead.\\n; If empty, default_charset or internal_encoding or iconv.internal_encoding is used.\\n; The precedence is: default_charset < internal_encoding < iconv.internal_encoding\\n;iconv.internal_encoding =\\n\\n; Use of this INI entry is deprecated, use global output_encoding instead.\\n; If empty, default_charset or output_encoding or iconv.output_encoding is used.\\n; The precedence is: default_charset < output_encoding < iconv.output_encoding\\n; To use an output encoding conversion, iconv's output handler must be set\\n; otherwise output encoding conversion cannot be performed.\\n;iconv.output_encoding =\\n\\n[imap]\\n; rsh/ssh logins are disabled by default. Use this INI entry if you want to\\n; enable them. Note that the IMAP library does not filter mailbox names before\\n; passing them to rsh/ssh command, thus passing untrusted data to this function\\n; with rsh/ssh enabled is insecure.\\n;imap.enable_insecure_rsh=0\\n\\n[intl]\\n;intl.default_locale =\\n; This directive allows you to produce PHP errors when some error\\n; happens within intl functions. The value is the level of the error produced.\\n; Default is 0, which does not produce any errors.\\n;intl.error_level = E_WARNING\\n;intl.use_exceptions = 0\\n\\n[sqlite3]\\n; Directory pointing to SQLite3 extensions\\n; https://php.net/sqlite3.extension-dir\\n;sqlite3.extension_dir =\\n\\n; SQLite defensive mode flag (only available from SQLite 3.26+)\\n; When the defensive flag is enabled, language features that allow ordinary\\n; SQL to deliberately corrupt the database file are disabled. This forbids\\n; writing directly to the schema, shadow tables (eg. FTS data tables), or\\n; the sqlite_dbpage virtual table.\\n; https://www.sqlite.org/c3ref/c_dbconfig_defensive.html\\n; (for older SQLite versions, this flag has no use)\\n;sqlite3.defensive = 1\\n\\n[Pcre]\\n; PCRE library backtracking limit.\\n; https://php.net/pcre.backtrack-limit\\n;pcre.backtrack_limit=100000\\n\\n; PCRE library recursion limit.\\n; Please note that if you set this value to a high number you may consume all\\n; the available process stack and eventually crash PHP (due to reaching the\\n; stack size limit imposed by the Operating System).\\n; https://php.net/pcre.recursion-limit\\n;pcre.recursion_limit=100000\\n\\n; Enables or disables JIT compilation of patterns. This requires the PCRE\\n; library to be compiled with JIT support.\\n;pcre.jit=1\\n\\n[Pdo]\\n; Whether to pool ODBC connections. Can be one of \\\"strict\\\", \\\"relaxed\\\" or \\\"off\\\"\\n; https://php.net/pdo-odbc.connection-pooling\\n;pdo_odbc.connection_pooling=strict\\n\\n[Pdo_mysql]\\n; Default socket name for local MySQL connects.  If empty, uses the built-in\\n; MySQL defaults.\\npdo_mysql.default_socket=\\n\\n[Phar]\\n; https://php.net/phar.readonly\\n;phar.readonly = On\\n\\n; https://php.net/phar.require-hash\\n;phar.require_hash = On\\n\\n;phar.cache_list =\\n\\n[mail function]\\n; For Win32 only.\\n; https://php.net/smtp\\nSMTP = localhost\\n; https://php.net/smtp-port\\nsmtp_port = 25\\n\\n; For Win32 only.\\n; https://php.net/sendmail-from\\n;sendmail_from = me@example.com\\n\\n; For Unix only.  You may supply arguments as well (default: \\\"sendmail -t -i\\\").\\n; https://php.net/sendmail-path\\n;sendmail_path =\\n\\n; Force the addition of the specified parameters to be passed as extra parameters\\n; to the sendmail binary. These parameters will always replace the value of\\n; the 5th parameter to mail().\\n;mail.force_extra_parameters =\\n\\n; Add X-PHP-Originating-Script: that will include uid of the script followed by the filename\\nmail.add_x_header = Off\\n\\n; Use mixed LF and CRLF line separators to keep compatibility with some\\n; RFC 2822 non conformant MTA.\\nmail.mixed_lf_and_crlf = Off\\n\\n; The path to a log file that will log all mail() calls. Log entries include\\n; the full path of the script, line number, To address and headers.\\n;mail.log =\\n; Log mail to syslog (Event Log on Windows).\\n;mail.log = syslog\\n\\n[ODBC]\\n; https://php.net/odbc.default-db\\n;odbc.default_db    =  Not yet implemented\\n\\n; https://php.net/odbc.default-user\\n;odbc.default_user  =  Not yet implemented\\n\\n; https://php.net/odbc.default-pw\\n;odbc.default_pw    =  Not yet implemented\\n\\n; Controls the ODBC cursor model.\\n; Default: SQL_CURSOR_STATIC (default).\\n;odbc.default_cursortype\\n\\n; Allow or prevent persistent links.\\n; https://php.net/odbc.allow-persistent\\nodbc.allow_persistent = On\\n\\n; Check that a connection is still valid before reuse.\\n; https://php.net/odbc.check-persistent\\nodbc.check_persistent = On\\n\\n; Maximum number of persistent links.  -1 means no limit.\\n; https://php.net/odbc.max-persistent\\nodbc.max_persistent = -1\\n\\n; Maximum number of links (persistent + non-persistent).  -1 means no limit.\\n; https://php.net/odbc.max-links\\nodbc.max_links = -1\\n\\n; Handling of LONG fields.  Returns number of bytes to variables.  0 means\\n; passthru.\\n; https://php.net/odbc.defaultlrl\\nodbc.defaultlrl = 4096\\n\\n; Handling of binary data.  0 means passthru, 1 return as is, 2 convert to char.\\n; See the documentation on odbc_binmode and odbc_longreadlen for an explanation\\n; of odbc.defaultlrl and odbc.defaultbinmode\\n; https://php.net/odbc.defaultbinmode\\nodbc.defaultbinmode = 1\\n\\n[MySQLi]\\n\\n; Maximum number of persistent links.  -1 means no limit.\\n; https://php.net/mysqli.max-persistent\\nmysqli.max_persistent = -1\\n\\n; Allow accessing, from PHP's perspective, local files with LOAD DATA statements\\n; https://php.net/mysqli.allow_local_infile\\n;mysqli.allow_local_infile = On\\n\\n; It allows the user to specify a folder where files that can be sent via LOAD DATA\\n; LOCAL can exist. It is ignored if mysqli.allow_local_infile is enabled.\\n;mysqli.local_infile_directory =\\n\\n; Allow or prevent persistent links.\\n; https://php.net/mysqli.allow-persistent\\nmysqli.allow_persistent = On\\n\\n; Maximum number of links.  -1 means no limit.\\n; https://php.net/mysqli.max-links\\nmysqli.max_links = -1\\n\\n; Default port number for mysqli_connect().  If unset, mysqli_connect() will use\\n; the $MYSQL_TCP_PORT or the mysql-tcp entry in /etc/services or the\\n; compile-time value defined MYSQL_PORT (in that order).  Win32 will only look\\n; at MYSQL_PORT.\\n; https://php.net/mysqli.default-port\\nmysqli.default_port = 3306\\n\\n; Default socket name for local MySQL connects.  If empty, uses the built-in\\n; MySQL defaults.\\n; https://php.net/mysqli.default-socket\\nmysqli.default_socket =\\n\\n; Default host for mysqli_connect() (doesn't apply in safe mode).\\n; https://php.net/mysqli.default-host\\nmysqli.default_host =\\n\\n; Default user for mysqli_connect() (doesn't apply in safe mode).\\n; https://php.net/mysqli.default-user\\nmysqli.default_user =\\n\\n; Default password for mysqli_connect() (doesn't apply in safe mode).\\n; Note that this is generally a *bad* idea to store passwords in this file.\\n; *Any* user with PHP access can run 'echo get_cfg_var(\\\"mysqli.default_pw\\\")\\n; and reveal this password!  And of course, any users with read access to this\\n; file will be able to reveal the password as well.\\n; https://php.net/mysqli.default-pw\\nmysqli.default_pw =\\n\\n; Allow or prevent reconnect\\nmysqli.reconnect = Off\\n\\n; If this option is enabled, closing a persistent connection will rollback\\n; any pending transactions of this connection, before it is put back\\n; into the persistent connection pool.\\n;mysqli.rollback_on_cached_plink = Off\\n\\n[mysqlnd]\\n; Enable / Disable collection of general statistics by mysqlnd which can be\\n; used to tune and monitor MySQL operations.\\nmysqlnd.collect_statistics = On\\n\\n; Enable / Disable collection of memory usage statistics by mysqlnd which can be\\n; used to tune and monitor MySQL operations.\\nmysqlnd.collect_memory_statistics = Off\\n\\n; Records communication from all extensions using mysqlnd to the specified log\\n; file.\\n; https://php.net/mysqlnd.debug\\n;mysqlnd.debug =\\n\\n; Defines which queries will be logged.\\n;mysqlnd.log_mask = 0\\n\\n; Default size of the mysqlnd memory pool, which is used by result sets.\\n;mysqlnd.mempool_default_size = 16000\\n\\n; Size of a pre-allocated buffer used when sending commands to MySQL in bytes.\\n;mysqlnd.net_cmd_buffer_size = 2048\\n\\n; Size of a pre-allocated buffer used for reading data sent by the server in\\n; bytes.\\n;mysqlnd.net_read_buffer_size = 32768\\n\\n; Timeout for network requests in seconds.\\n;mysqlnd.net_read_timeout = 31536000\\n\\n; SHA-256 Authentication Plugin related. File with the MySQL server public RSA\\n; key.\\n;mysqlnd.sha256_server_public_key =\\n\\n[OCI8]\\n\\n; Connection: Enables privileged connections using external\\n; credentials (OCI_SYSOPER, OCI_SYSDBA)\\n; https://php.net/oci8.privileged-connect\\n;oci8.privileged_connect = Off\\n\\n; Connection: The maximum number of persistent OCI8 connections per\\n; process. Using -1 means no limit.\\n; https://php.net/oci8.max-persistent\\n;oci8.max_persistent = -1\\n\\n; Connection: The maximum number of seconds a process is allowed to\\n; maintain an idle persistent connection. Using -1 means idle\\n; persistent connections will be maintained forever.\\n; https://php.net/oci8.persistent-timeout\\n;oci8.persistent_timeout = -1\\n\\n; Connection: The number of seconds that must pass before issuing a\\n; ping during oci_pconnect() to check the connection validity. When\\n; set to 0, each oci_pconnect() will cause a ping. Using -1 disables\\n; pings completely.\\n; https://php.net/oci8.ping-interval\\n;oci8.ping_interval = 60\\n\\n; Connection: Set this to a user chosen connection class to be used\\n; for all pooled server requests with Oracle Database Resident\\n; Connection Pooling (DRCP).  To use DRCP, this value should be set to\\n; the same string for all web servers running the same application,\\n; the database pool must be configured, and the connection string must\\n; specify to use a pooled server.\\n;oci8.connection_class =\\n\\n; High Availability: Using On lets PHP receive Fast Application\\n; Notification (FAN) events generated when a database node fails. The\\n; database must also be configured to post FAN events.\\n;oci8.events = Off\\n\\n; Tuning: This option enables statement caching, and specifies how\\n; many statements to cache. Using 0 disables statement caching.\\n; https://php.net/oci8.statement-cache-size\\n;oci8.statement_cache_size = 20\\n\\n; Tuning: Enables row prefetching and sets the default number of\\n; rows that will be fetched automatically after statement execution.\\n; https://php.net/oci8.default-prefetch\\n;oci8.default_prefetch = 100\\n\\n; Tuning: Sets the amount of LOB data that is internally returned from\\n; Oracle Database when an Oracle LOB locator is initially retrieved as\\n; part of a query. Setting this can improve performance by reducing\\n; round-trips.\\n; https://php.net/oci8.prefetch-lob-size\\n; oci8.prefetch_lob_size = 0\\n\\n; Compatibility. Using On means oci_close() will not close\\n; oci_connect() and oci_new_connect() connections.\\n; https://php.net/oci8.old-oci-close-semantics\\n;oci8.old_oci_close_semantics = Off\\n\\n[PostgreSQL]\\n; Allow or prevent persistent links.\\n; https://php.net/pgsql.allow-persistent\\npgsql.allow_persistent = On\\n\\n; Detect broken persistent links always with pg_pconnect().\\n; Auto reset feature requires a little overheads.\\n; https://php.net/pgsql.auto-reset-persistent\\npgsql.auto_reset_persistent = Off\\n\\n; Maximum number of persistent links.  -1 means no limit.\\n; https://php.net/pgsql.max-persistent\\npgsql.max_persistent = -1\\n\\n; Maximum number of links (persistent+non persistent).  -1 means no limit.\\n; https://php.net/pgsql.max-links\\npgsql.max_links = -1\\n\\n; Ignore PostgreSQL backends Notice message or not.\\n; Notice message logging require a little overheads.\\n; https://php.net/pgsql.ignore-notice\\npgsql.ignore_notice = 0\\n\\n; Log PostgreSQL backends Notice message or not.\\n; Unless pgsql.ignore_notice=0, module cannot log notice message.\\n; https://php.net/pgsql.log-notice\\npgsql.log_notice = 0\\n\\n[bcmath]\\n; Number of decimal digits for all bcmath functions.\\n; https://php.net/bcmath.scale\\nbcmath.scale = 0\\n\\n[browscap]\\n; https://php.net/browscap\\n;browscap = extra/browscap.ini\\n\\n[Session]\\n; Handler used to store/retrieve data.\\n; https://php.net/session.save-handler\\nsession.save_handler = files\\n\\n; Argument passed to save_handler.  In the case of files, this is the path\\n; where data files are stored. Note: Windows users have to change this\\n; variable in order to use PHP's session functions.\\n;\\n; The path can be defined as:\\n;\\n;     session.save_path = \\\"N;/path\\\"\\n;\\n; where N is an integer.  Instead of storing all the session files in\\n; /path, what this will do is use subdirectories N-levels deep, and\\n; store the session data in those directories.  This is useful if\\n; your OS has problems with many files in one directory, and is\\n; a more efficient layout for servers that handle many sessions.\\n;\\n; NOTE 1: PHP will not create this directory structure automatically.\\n;         You can use the script in the ext/session dir for that purpose.\\n; NOTE 2: See the section on garbage collection below if you choose to\\n;         use subdirectories for session storage\\n;\\n; The file storage module creates files using mode 600 by default.\\n; You can change that by using\\n;\\n;     session.save_path = \\\"N;MODE;/path\\\"\\n;\\n; where MODE is the octal representation of the mode. Note that this\\n; does not overwrite the process's umask.\\n; https://php.net/session.save-path\\n;session.save_path = \\\"/var/lib/php/sessions\\\"\\n\\n; Whether to use strict session mode.\\n; Strict session mode does not accept an uninitialized session ID, and\\n; regenerates the session ID if the browser sends an uninitialized session ID.\\n; Strict mode protects applications from session fixation via a session adoption\\n; vulnerability. It is disabled by default for maximum compatibility, but\\n; enabling it is encouraged.\\n; https://wiki.php.net/rfc/strict_sessions\\nsession.use_strict_mode = 0\\n\\n; Whether to use cookies.\\n; https://php.net/session.use-cookies\\nsession.use_cookies = 1\\n\\n; https://php.net/session.cookie-secure\\n;session.cookie_secure =\\n\\n; This option forces PHP to fetch and use a cookie for storing and maintaining\\n; the session id. We encourage this operation as it's very helpful in combating\\n; session hijacking when not specifying and managing your own session id. It is\\n; not the be-all and end-all of session hijacking defense, but it's a good start.\\n; https://php.net/session.use-only-cookies\\nsession.use_only_cookies = 1\\n\\n; Name of the session (used as cookie name).\\n; https://php.net/session.name\\nsession.name = PHPSESSID\\n\\n; Initialize session on request startup.\\n; https://php.net/session.auto-start\\nsession.auto_start = 0\\n\\n; Lifetime in seconds of cookie or, if 0, until browser is restarted.\\n; https://php.net/session.cookie-lifetime\\nsession.cookie_lifetime = 0\\n\\n; The path for which the cookie is valid.\\n; https://php.net/session.cookie-path\\nsession.cookie_path = /\\n\\n; The domain for which the cookie is valid.\\n; https://php.net/session.cookie-domain\\nsession.cookie_domain =\\n\\n; Whether or not to add the httpOnly flag to the cookie, which makes it\\n; inaccessible to browser scripting languages such as JavaScript.\\n; https://php.net/session.cookie-httponly\\nsession.cookie_httponly =\\n\\n; Add SameSite attribute to cookie to help mitigate Cross-Site Request Forgery (CSRF/XSRF)\\n; Current valid values are \\\"Strict\\\", \\\"Lax\\\" or \\\"None\\\". When using \\\"None\\\",\\n; make sure to include the quotes, as `none` is interpreted like `false` in ini files.\\n; https://tools.ietf.org/html/draft-west-first-party-cookies-07\\nsession.cookie_samesite =\\n\\n; Handler used to serialize data. php is the standard serializer of PHP.\\n; https://php.net/session.serialize-handler\\nsession.serialize_handler = php\\n\\n; Defines the probability that the 'garbage collection' process is started on every\\n; session initialization. The probability is calculated by using gc_probability/gc_divisor,\\n; e.g. 1/100 means there is a 1% chance that the GC process starts on each request.\\n; Default Value: 1\\n; Development Value: 1\\n; Production Value: 1\\n; https://php.net/session.gc-probability\\nsession.gc_probability = 0\\n\\n; Defines the probability that the 'garbage collection' process is started on every\\n; session initialization. The probability is calculated by using gc_probability/gc_divisor,\\n; e.g. 1/100 means there is a 1% chance that the GC process starts on each request.\\n; For high volume production servers, using a value of 1000 is a more efficient approach.\\n; Default Value: 100\\n; Development Value: 1000\\n; Production Value: 1000\\n; https://php.net/session.gc-divisor\\nsession.gc_divisor = 1000\\n\\n; After this number of seconds, stored data will be seen as 'garbage' and\\n; cleaned up by the garbage collection process.\\n; https://php.net/session.gc-maxlifetime\\nsession.gc_maxlifetime = 1440\\n\\n; NOTE: If you are using the subdirectory option for storing session files\\n;       (see session.save_path above), then garbage collection does *not*\\n;       happen automatically.  You will need to do your own garbage\\n;       collection through a shell script, cron entry, or some other method.\\n;       For example, the following script is the equivalent of setting\\n;       session.gc_maxlifetime to 1440 (1440 seconds = 24 minutes):\\n;          find /path/to/sessions -cmin +24 -type f | xargs rm\\n\\n; Check HTTP Referer to invalidate externally stored URLs containing ids.\\n; HTTP_REFERER has to contain this substring for the session to be\\n; considered as valid.\\n; https://php.net/session.referer-check\\nsession.referer_check =\\n\\n; Set to {nocache,private,public,} to determine HTTP caching aspects\\n; or leave this empty to avoid sending anti-caching headers.\\n; https://php.net/session.cache-limiter\\nsession.cache_limiter = nocache\\n\\n; Document expires after n minutes.\\n; https://php.net/session.cache-expire\\nsession.cache_expire = 180\\n\\n; trans sid support is disabled by default.\\n; Use of trans sid may risk your users' security.\\n; Use this option with caution.\\n; - User may send URL contains active session ID\\n;   to other person via. email/irc/etc.\\n; - URL that contains active session ID may be stored\\n;   in publicly accessible computer.\\n; - User may access your site with the same session ID\\n;   always using URL stored in browser's history or bookmarks.\\n; https://php.net/session.use-trans-sid\\nsession.use_trans_sid = 0\\n\\n; Set session ID character length. This value could be between 22 to 256.\\n; Shorter length than default is supported only for compatibility reason.\\n; Users should use 32 or more chars.\\n; https://php.net/session.sid-length\\n; Default Value: 32\\n; Development Value: 26\\n; Production Value: 26\\nsession.sid_length = 26\\n\\n; The URL rewriter will look for URLs in a defined set of HTML tags.\\n; <form> is special; if you include them here, the rewriter will\\n; add a hidden <input> field with the info which is otherwise appended\\n; to URLs. <form> tag's action attribute URL will not be modified\\n; unless it is specified.\\n; Note that all valid entries require a \\\"=\\\", even if no value follows.\\n; Default Value: \\\"a=href,area=href,frame=src,form=\\\"\\n; Development Value: \\\"a=href,area=href,frame=src,form=\\\"\\n; Production Value: \\\"a=href,area=href,frame=src,form=\\\"\\n; https://php.net/url-rewriter.tags\\nsession.trans_sid_tags = \\\"a=href,area=href,frame=src,form=\\\"\\n\\n; URL rewriter does not rewrite absolute URLs by default.\\n; To enable rewrites for absolute paths, target hosts must be specified\\n; at RUNTIME. i.e. use ini_set()\\n; <form> tags is special. PHP will check action attribute's URL regardless\\n; of session.trans_sid_tags setting.\\n; If no host is defined, HTTP_HOST will be used for allowed host.\\n; Example value: php.net,www.php.net,wiki.php.net\\n; Use \\\",\\\" for multiple hosts. No spaces are allowed.\\n; Default Value: \\\"\\\"\\n; Development Value: \\\"\\\"\\n; Production Value: \\\"\\\"\\n;session.trans_sid_hosts=\\\"\\\"\\n\\n; Define how many bits are stored in each character when converting\\n; the binary hash data to something readable.\\n; Possible values:\\n;   4  (4 bits: 0-9, a-f)\\n;   5  (5 bits: 0-9, a-v)\\n;   6  (6 bits: 0-9, a-z, A-Z, \\\"-\\\", \\\",\\\")\\n; Default Value: 4\\n; Development Value: 5\\n; Production Value: 5\\n; https://php.net/session.hash-bits-per-character\\nsession.sid_bits_per_character = 5\\n\\n; Enable upload progress tracking in $_SESSION\\n; Default Value: On\\n; Development Value: On\\n; Production Value: On\\n; https://php.net/session.upload-progress.enabled\\n;session.upload_progress.enabled = On\\n\\n; Cleanup the progress information as soon as all POST data has been read\\n; (i.e. upload completed).\\n; Default Value: On\\n; Development Value: On\\n; Production Value: On\\n; https://php.net/session.upload-progress.cleanup\\n;session.upload_progress.cleanup = On\\n\\n; A prefix used for the upload progress key in $_SESSION\\n; Default Value: \\\"upload_progress_\\\"\\n; Development Value: \\\"upload_progress_\\\"\\n; Production Value: \\\"upload_progress_\\\"\\n; https://php.net/session.upload-progress.prefix\\n;session.upload_progress.prefix = \\\"upload_progress_\\\"\\n\\n; The index name (concatenated with the prefix) in $_SESSION\\n; containing the upload progress information\\n; Default Value: \\\"PHP_SESSION_UPLOAD_PROGRESS\\\"\\n; Development Value: \\\"PHP_SESSION_UPLOAD_PROGRESS\\\"\\n; Production Value: \\\"PHP_SESSION_UPLOAD_PROGRESS\\\"\\n; https://php.net/session.upload-progress.name\\n;session.upload_progress.name = \\\"PHP_SESSION_UPLOAD_PROGRESS\\\"\\n\\n; How frequently the upload progress should be updated.\\n; Given either in percentages (per-file), or in bytes\\n; Default Value: \\\"1%\\\"\\n; Development Value: \\\"1%\\\"\\n; Production Value: \\\"1%\\\"\\n; https://php.net/session.upload-progress.freq\\n;session.upload_progress.freq =  \\\"1%\\\"\\n\\n; The minimum delay between updates, in seconds\\n; Default Value: 1\\n; Development Value: 1\\n; Production Value: 1\\n; https://php.net/session.upload-progress.min-freq\\n;session.upload_progress.min_freq = \\\"1\\\"\\n\\n; Only write session data when session data is changed. Enabled by default.\\n; https://php.net/session.lazy-write\\n;session.lazy_write = On\\n\\n[Assertion]\\n; Switch whether to compile assertions at all (to have no overhead at run-time)\\n; -1: Do not compile at all\\n;  0: Jump over assertion at run-time\\n;  1: Execute assertions\\n; Changing from or to a negative value is only possible in php.ini! (For turning assertions on and off at run-time, see assert.active, when zend.assertions = 1)\\n; Default Value: 1\\n; Development Value: 1\\n; Production Value: -1\\n; https://php.net/zend.assertions\\nzend.assertions = -1\\n\\n; Assert(expr); active by default.\\n; https://php.net/assert.active\\n;assert.active = On\\n\\n; Throw an AssertionError on failed assertions\\n; https://php.net/assert.exception\\n;assert.exception = On\\n\\n; Issue a PHP warning for each failed assertion. (Overridden by assert.exception if active)\\n; https://php.net/assert.warning\\n;assert.warning = On\\n\\n; Don't bail out by default.\\n; https://php.net/assert.bail\\n;assert.bail = Off\\n\\n; User-function to be called if an assertion fails.\\n; https://php.net/assert.callback\\n;assert.callback = 0\\n\\n[COM]\\n; path to a file containing GUIDs, IIDs or filenames of files with TypeLibs\\n; https://php.net/com.typelib-file\\n;com.typelib_file =\\n\\n; allow Distributed-COM calls\\n; https://php.net/com.allow-dcom\\n;com.allow_dcom = true\\n\\n; autoregister constants of a component's typelib on com_load()\\n; https://php.net/com.autoregister-typelib\\n;com.autoregister_typelib = true\\n\\n; register constants casesensitive\\n; https://php.net/com.autoregister-casesensitive\\n;com.autoregister_casesensitive = false\\n\\n; show warnings on duplicate constant registrations\\n; https://php.net/com.autoregister-verbose\\n;com.autoregister_verbose = true\\n\\n; The default character set code-page to use when passing strings to and from COM objects.\\n; Default: system ANSI code page\\n;com.code_page=\\n\\n; The version of the .NET framework to use. The value of the setting are the first three parts\\n; of the framework's version number, separated by dots, and prefixed with \\\"v\\\", e.g. \\\"v4.0.30319\\\".\\n;com.dotnet_version=\\n\\n[mbstring]\\n; language for internal character representation.\\n; This affects mb_send_mail() and mbstring.detect_order.\\n; https://php.net/mbstring.language\\n;mbstring.language = Japanese\\n\\n; Use of this INI entry is deprecated, use global internal_encoding instead.\\n; internal/script encoding.\\n; Some encoding cannot work as internal encoding. (e.g. SJIS, BIG5, ISO-2022-*)\\n; If empty, default_charset or internal_encoding or iconv.internal_encoding is used.\\n; The precedence is: default_charset < internal_encoding < iconv.internal_encoding\\n;mbstring.internal_encoding =\\n\\n; Use of this INI entry is deprecated, use global input_encoding instead.\\n; http input encoding.\\n; mbstring.encoding_translation = On is needed to use this setting.\\n; If empty, default_charset or input_encoding or mbstring.input is used.\\n; The precedence is: default_charset < input_encoding < mbstring.http_input\\n; https://php.net/mbstring.http-input\\n;mbstring.http_input =\\n\\n; Use of this INI entry is deprecated, use global output_encoding instead.\\n; http output encoding.\\n; mb_output_handler must be registered as output buffer to function.\\n; If empty, default_charset or output_encoding or mbstring.http_output is used.\\n; The precedence is: default_charset < output_encoding < mbstring.http_output\\n; To use an output encoding conversion, mbstring's output handler must be set\\n; otherwise output encoding conversion cannot be performed.\\n; https://php.net/mbstring.http-output\\n;mbstring.http_output =\\n\\n; enable automatic encoding translation according to\\n; mbstring.internal_encoding setting. Input chars are\\n; converted to internal encoding by setting this to On.\\n; Note: Do _not_ use automatic encoding translation for\\n;       portable libs/applications.\\n; https://php.net/mbstring.encoding-translation\\n;mbstring.encoding_translation = Off\\n\\n; automatic encoding detection order.\\n; \\\"auto\\\" detect order is changed according to mbstring.language\\n; https://php.net/mbstring.detect-order\\n;mbstring.detect_order = auto\\n\\n; substitute_character used when character cannot be converted\\n; one from another\\n; https://php.net/mbstring.substitute-character\\n;mbstring.substitute_character = none\\n\\n; Enable strict encoding detection.\\n;mbstring.strict_detection = Off\\n\\n; This directive specifies the regex pattern of content types for which mb_output_handler()\\n; is activated.\\n; Default: mbstring.http_output_conv_mimetypes=^(text/|application/xhtml\\\\+xml)\\n;mbstring.http_output_conv_mimetypes=\\n\\n; This directive specifies maximum stack depth for mbstring regular expressions. It is similar\\n; to the pcre.recursion_limit for PCRE.\\n;mbstring.regex_stack_limit=100000\\n\\n; This directive specifies maximum retry count for mbstring regular expressions. It is similar\\n; to the pcre.backtrack_limit for PCRE.\\n;mbstring.regex_retry_limit=1000000\\n\\n[gd]\\n; Tell the jpeg decode to ignore warnings and try to create\\n; a gd image. The warning will then be displayed as notices\\n; disabled by default\\n; https://php.net/gd.jpeg-ignore-warning\\n;gd.jpeg_ignore_warning = 1\\n\\n[exif]\\n; Exif UNICODE user comments are handled as UCS-2BE/UCS-2LE and JIS as JIS.\\n; With mbstring support this will automatically be converted into the encoding\\n; given by corresponding encode setting. When empty mbstring.internal_encoding\\n; is used. For the decode settings you can distinguish between motorola and\\n; intel byte order. A decode setting cannot be empty.\\n; https://php.net/exif.encode-unicode\\n;exif.encode_unicode = ISO-8859-15\\n\\n; https://php.net/exif.decode-unicode-motorola\\n;exif.decode_unicode_motorola = UCS-2BE\\n\\n; https://php.net/exif.decode-unicode-intel\\n;exif.decode_unicode_intel    = UCS-2LE\\n\\n; https://php.net/exif.encode-jis\\n;exif.encode_jis =\\n\\n; https://php.net/exif.decode-jis-motorola\\n;exif.decode_jis_motorola = JIS\\n\\n; https://php.net/exif.decode-jis-intel\\n;exif.decode_jis_intel    = JIS\\n\\n[Tidy]\\n; The path to a default tidy configuration file to use when using tidy\\n; https://php.net/tidy.default-config\\n;tidy.default_config = /usr/local/lib/php/default.tcfg\\n\\n; Should tidy clean and repair output automatically?\\n; WARNING: Do not use this option if you are generating non-html content\\n; such as dynamic images\\n; https://php.net/tidy.clean-output\\ntidy.clean_output = Off\\n\\n[soap]\\n; Enables or disables WSDL caching feature.\\n; https://php.net/soap.wsdl-cache-enabled\\nsoap.wsdl_cache_enabled=1\\n\\n; Sets the directory name where SOAP extension will put cache files.\\n; https://php.net/soap.wsdl-cache-dir\\nsoap.wsdl_cache_dir=\\\"/tmp\\\"\\n\\n; (time to live) Sets the number of second while cached file will be used\\n; instead of original one.\\n; https://php.net/soap.wsdl-cache-ttl\\nsoap.wsdl_cache_ttl=86400\\n\\n; Sets the size of the cache limit. (Max. number of WSDL files to cache)\\nsoap.wsdl_cache_limit = 5\\n\\n[sysvshm]\\n; A default size of the shared memory segment\\n;sysvshm.init_mem = 10000\\n\\n[ldap]\\n; Sets the maximum number of open links or -1 for unlimited.\\nldap.max_links = -1\\n\\n[dba]\\n;dba.default_handler=\\n\\n[opcache]\\n; Determines if Zend OPCache is enabled\\n;opcache.enable=1\\n\\n; Determines if Zend OPCache is enabled for the CLI version of PHP\\n;opcache.enable_cli=0\\n\\n; The OPcache shared memory storage size.\\n;opcache.memory_consumption=128\\n\\n; The amount of memory for interned strings in Mbytes.\\n;opcache.interned_strings_buffer=8\\n\\n; The maximum number of keys (scripts) in the OPcache hash table.\\n; Only numbers between 200 and 1000000 are allowed.\\n;opcache.max_accelerated_files=10000\\n\\n; The maximum percentage of \\\"wasted\\\" memory until a restart is scheduled.\\n;opcache.max_wasted_percentage=5\\n\\n; When this directive is enabled, the OPcache appends the current working\\n; directory to the script key, thus eliminating possible collisions between\\n; files with the same name (basename). Disabling the directive improves\\n; performance, but may break existing applications.\\n;opcache.use_cwd=1\\n\\n; When disabled, you must reset the OPcache manually or restart the\\n; webserver for changes to the filesystem to take effect.\\n;opcache.validate_timestamps=1\\n\\n; How often (in seconds) to check file timestamps for changes to the shared\\n; memory storage allocation. (\\\"1\\\" means validate once per second, but only\\n; once per request. \\\"0\\\" means always validate)\\n;opcache.revalidate_freq=2\\n\\n; Enables or disables file search in include_path optimization\\n;opcache.revalidate_path=0\\n\\n; If disabled, all PHPDoc comments are dropped from the code to reduce the\\n; size of the optimized code.\\n;opcache.save_comments=1\\n\\n; If enabled, compilation warnings (including notices and deprecations) will\\n; be recorded and replayed each time a file is included. Otherwise, compilation\\n; warnings will only be emitted when the file is first cached.\\n;opcache.record_warnings=0\\n\\n; Allow file existence override (file_exists, etc.) performance feature.\\n;opcache.enable_file_override=0\\n\\n; A bitmask, where each bit enables or disables the appropriate OPcache\\n; passes\\n;opcache.optimization_level=0x7FFFBFFF\\n\\n;opcache.dups_fix=0\\n\\n; The location of the OPcache blacklist file (wildcards allowed).\\n; Each OPcache blacklist file is a text file that holds the names of files\\n; that should not be accelerated. The file format is to add each filename\\n; to a new line. The filename may be a full path or just a file prefix\\n; (i.e., /var/www/x  blacklists all the files and directories in /var/www\\n; that start with 'x'). Line starting with a ; are ignored (comments).\\n;opcache.blacklist_filename=\\n\\n; Allows exclusion of large files from being cached. By default all files\\n; are cached.\\n;opcache.max_file_size=0\\n\\n; Check the cache checksum each N requests.\\n; The default value of \\\"0\\\" means that the checks are disabled.\\n;opcache.consistency_checks=0\\n\\n; How long to wait (in seconds) for a scheduled restart to begin if the cache\\n; is not being accessed.\\n;opcache.force_restart_timeout=180\\n\\n; OPcache error_log file name. Empty string assumes \\\"stderr\\\".\\n;opcache.error_log=\\n\\n; All OPcache errors go to the Web server log.\\n; By default, only fatal errors (level 0) or errors (level 1) are logged.\\n; You can also enable warnings (level 2), info messages (level 3) or\\n; debug messages (level 4).\\n;opcache.log_verbosity_level=1\\n\\n; Preferred Shared Memory back-end. Leave empty and let the system decide.\\n;opcache.preferred_memory_model=\\n\\n; Protect the shared memory from unexpected writing during script execution.\\n; Useful for internal debugging only.\\n;opcache.protect_memory=0\\n\\n; Allows calling OPcache API functions only from PHP scripts which path is\\n; started from specified string. The default \\\"\\\" means no restriction\\n;opcache.restrict_api=\\n\\n; Mapping base of shared memory segments (for Windows only). All the PHP\\n; processes have to map shared memory into the same address space. This\\n; directive allows to manually fix the \\\"Unable to reattach to base address\\\"\\n; errors.\\n;opcache.mmap_base=\\n\\n; Facilitates multiple OPcache instances per user (for Windows only). All PHP\\n; processes with the same cache ID and user share an OPcache instance.\\n;opcache.cache_id=\\n\\n; Enables and sets the second level cache directory.\\n; It should improve performance when SHM memory is full, at server restart or\\n; SHM reset. The default \\\"\\\" disables file based caching.\\n;opcache.file_cache=\\n\\n; Enables or disables opcode caching in shared memory.\\n;opcache.file_cache_only=0\\n\\n; Enables or disables checksum validation when script loaded from file cache.\\n;opcache.file_cache_consistency_checks=1\\n\\n; Implies opcache.file_cache_only=1 for a certain process that failed to\\n; reattach to the shared memory (for Windows only). Explicitly enabled file\\n; cache is required.\\n;opcache.file_cache_fallback=1\\n\\n; Enables or disables copying of PHP code (text segment) into HUGE PAGES.\\n; Under certain circumstances (if only a single global PHP process is\\n; started from which all others fork), this can increase performance\\n; by a tiny amount because TLB misses are reduced.  On the other hand, this\\n; delays PHP startup, increases memory usage and degrades performance\\n; under memory pressure - use with care.\\n; Requires appropriate OS configuration.\\n;opcache.huge_code_pages=0\\n\\n; Validate cached file permissions.\\n;opcache.validate_permission=0\\n\\n; Prevent name collisions in chroot'ed environment.\\n;opcache.validate_root=0\\n\\n; If specified, it produces opcode dumps for debugging different stages of\\n; optimizations.\\n;opcache.opt_debug_level=0\\n\\n; Specifies a PHP script that is going to be compiled and executed at server\\n; start-up.\\n; https://php.net/opcache.preload\\n;opcache.preload=\\n\\n; Preloading code as root is not allowed for security reasons. This directive\\n; facilitates to let the preloading to be run as another user.\\n; https://php.net/opcache.preload_user\\n;opcache.preload_user=\\n\\n; Prevents caching files that are less than this number of seconds old. It\\n; protects from caching of incompletely updated files. In case all file updates\\n; on your site are atomic, you may increase performance by setting it to \\\"0\\\".\\n;opcache.file_update_protection=2\\n\\n; Absolute path used to store shared lockfiles (for *nix only).\\n;opcache.lockfile_path=/tmp\\n\\n[curl]\\n; A default value for the CURLOPT_CAINFO option. This is required to be an\\n; absolute path.\\n;curl.cainfo =\\n\\n[openssl]\\n; The location of a Certificate Authority (CA) file on the local filesystem\\n; to use when verifying the identity of SSL/TLS peers. Most users should\\n; not specify a value for this directive as PHP will attempt to use the\\n; OS-managed cert stores in its absence. If specified, this value may still\\n; be overridden on a per-stream basis via the \\\"cafile\\\" SSL stream context\\n; option.\\n;openssl.cafile=\\n\\n; If openssl.cafile is not specified or if the CA file is not found, the\\n; directory pointed to by openssl.capath is searched for a suitable\\n; certificate. This value must be a correctly hashed certificate directory.\\n; Most users should not specify a value for this directive as PHP will\\n; attempt to use the OS-managed cert stores in its absence. If specified,\\n; this value may still be overridden on a per-stream basis via the \\\"capath\\\"\\n; SSL stream context option.\\n;openssl.capath=\\n\\n[ffi]\\n; FFI API restriction. Possible values:\\n; \\\"preload\\\" - enabled in CLI scripts and preloaded files (default)\\n; \\\"false\\\"   - always disabled\\n; \\\"true\\\"    - always enabled\\n;ffi.enable=preload\\n\\n; List of headers files to preload, wildcard patterns allowed.\\n;ffi.preload=\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:500px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Tonics Simple]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 1,
		"field_slug": "app-tonicscloud-nginx-recipe-tonics-simple",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"k19dmab8s9s000000000\",\"field_input_name\":\"\",\"fieldName\":\"Server Block Simple\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"If SSL is true, please ensure you have generated an SSL certificate through the ACME App\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Repeat Server Block\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Tonics Simple]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-nginx-recipe-tonics-simple",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"91qs8fhp0x8000000000\",\"field_input_name\":\"server_name\",\"fieldName\":\"Server Name\",\"inputName\":\"server_name\",\"textType\":\"url\",\"defaultValue\":\"[[ACME_DOMAIN]]\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"tonics.app\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Tonics Simple]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-nginx-recipe-tonics-simple",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4todrp1oglc0000000000\",\"field_input_name\":\"root\",\"fieldName\":\"Root\",\"inputName\":\"root\",\"textType\":\"text\",\"defaultValue\":\"/var/www/tonics\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Root Path\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Tonics Simple]",
		"field_name": "input_select",
		"field_id": 4,
		"field_slug": "app-tonicscloud-nginx-recipe-tonics-simple",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"1u7tfh7vifhc000000000\",\"field_input_name\":\"server_ssl\",\"fieldName\":\"SSL\",\"inputName\":\"server_ssl\",\"selectData\":\"0:False,1:True\",\"defaultValue\":\"0\"}"
	},
	{
		"field_field_name": "App Tonicscloud App Config ENV",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-app-config-env",
		"field_parent_id": null,
		"field_options": "{\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"6yccojv18l40000000000\",\"field_input_name\":\"app_config_env_recipe\",\"fieldName\":\"ENV Recipe\",\"inputName\":\"app_config_env_recipe\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"Note that the env path root directory must exist, for example, if the env path is <code>\\/var\\/www\\/.env<\\/code>, the path <code>\\/var\\/www<\\/code> should exist beforehand\\n<br>\\n<br>\\nGlobal Variable you can use:\\n<br>\\n<code>[[RAND_STRING]]<\\/code> - auto-generates cryptographically secure pseudo-random bytes.\\n<br>\\n<code>[[DB_DATABASE]]<\\/code> - Pull from the container specific global variable if there is one.\\n<br>\\n<code>[[DB_USER]]<\\/code> - Pull from the container specific global variable if there is one.\\n<br>\\n<code>[[DB_PASS]]<\\/code> - Pull from the container specific global variable if there is one.\\n<br>\\n<code>[[DB_HOST]]<\\/code> - Pull from the container specific global variable if there is one or default to localhost.\\n<br>\\n...and any more specified in the container variable.\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App Tonicscloud App Config ENV",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 2,
		"field_slug": "app-tonicscloud-app-config-env",
		"field_parent_id": 1,
		"field_options": "{\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"1pox9dubck8w000000000\",\"field_input_name\":\"\",\"fieldName\":\"ENV Repeater\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Repeat Recipe\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App Tonicscloud App Config ENV",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 3,
		"field_slug": "app-tonicscloud-app-config-env",
		"field_parent_id": 2,
		"field_options": "{\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"1uiwxw4k63wg000000000\",\"field_input_name\":\"app_config_env_recipe_selected\",\"fieldName\":\"Choose Recipe\",\"inputName\":\"app_config_env_recipe_selected\",\"fieldSlug\":[\"app-tonicscloud-env-recipe-tonics\",\"app-tonicscloud-env-recipe-laravel\",\"app-tonicscloud-env-recipe-wordpress\",\"app-tonicscloud-env-recipe-manual\",\"app-tonicscloud-env-recipe-tonics-existing-installation\"],\"defaultFieldSlug\":\"app-tonicscloud-env-recipe-tonics\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\"}"
	},
	{
		"field_field_name": "App TonicsCloud ENV Recipe »» [Tonics]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-env-recipe-tonics",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"371d41mxn880000000000\",\"field_input_name\":\"\",\"fieldName\":\"Tonics ENV\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud ENV Recipe »» [Tonics]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-env-recipe-tonics",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"70aqm23kf400000000000\",\"field_input_name\":\"env_path\",\"fieldName\":\"ENV Path\",\"inputName\":\"env_path\",\"textType\":\"text\",\"defaultValue\":\"/var/www/tonics/web/.env\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter ENV Path\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud ENV Recipe »» [Tonics]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-env-recipe-tonics",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5cp1omeixf80000000000\",\"field_input_name\":\"env_content\",\"fieldName\":\"ENV Content\",\"inputName\":\"env_content\",\"textType\":\"textarea\",\"defaultValue\":\"APP_NAME=Tonics\\nAPP_ENV=production\\nAPP_URL_PORT=443\\nAPP_URL=https://[[ACME_DOMAIN]]\\nAPP_TIME_ZONE=Africa/Lagos\\nAPP_LANGUAGE=0\\nAPP_LOG_404=1\\nAPP_PAGINATION_MAX_LIMIT=20\\nAPP_STARTUP_CLI_FORK_LIMIT=1\\n\\nJOB_TRANSPORTER=DATABASE\\nSCHEDULE_TRANSPORTER=DATABASE\\n\\nINSTALL_KEY=[[RAND_STRING_RENDER]]\\nAPP_KEY=xxx\\nAPP_POST_ENDPOINT=https://tonics.app/api/app_store\\nSITE_KEY=[[RAND_STRING]]\\n\\nMAINTENANCE_MODE=0\\nAUTO_UPDATE_MODULES=1\\nAUTO_UPDATE_APPS=1\\n\\nACTIVATE_EVENT_STREAM_MESSAGE=1\\n\\nDB_CONNECTION=mysql\\nDB_HOST=[[DB_HOST]]\\nDB_PORT=3306\\nDB_DATABASE=[[DB_DATABASE]]\\nDB_USERNAME=[[DB_USER]]\\nDB_PASSWORD=[[DB_PASS]]\\nDB_CHARSET=utf8mb4\\nDB_ENGINE=InnoDB\\nDB_PREFIX=tonics_\\n\\nMAIL_MAILER=smtp\\nMAIL_HOST=mail.domain.com\\nMAIL_PORT=587\\nMAIL_USERNAME=user\\nMAIL_PASSWORD=password\\nMAIL_ENCRYPTION=tls\\nMAIL_FROM_ADDRESS=user@mail.domain.com\\nMAIL_REPLY_TO=user@mail.domain.com\\n\\nDROPBOX_KEY=xxx\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:500px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud ENV Recipe »» [Laravel]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-env-recipe-laravel",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"1tone5nesw1s000000000\",\"field_input_name\":\"\",\"fieldName\":\"Laravel ENV\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud ENV Recipe »» [Laravel]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-env-recipe-laravel",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"78xchl9uq6w0000000000\",\"field_input_name\":\"env_path\",\"fieldName\":\"ENV Path\",\"inputName\":\"env_path\",\"textType\":\"text\",\"defaultValue\":\"/var/www/laravel/.env\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter ENV Path\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud ENV Recipe »» [Laravel]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-env-recipe-laravel",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4r1k5ce9v7s0000000000\",\"field_input_name\":\"env_content\",\"fieldName\":\"ENV Content\",\"inputName\":\"env_content\",\"textType\":\"textarea\",\"defaultValue\":\"APP_NAME=Laravel\\nAPP_ENV=local\\nAPP_KEY=\\nAPP_DEBUG=true\\nAPP_URL=http://localhost\\n\\nLOG_CHANNEL=stack\\nLOG_DEPRECATIONS_CHANNEL=null\\nLOG_LEVEL=debug\\n\\nDB_CONNECTION=mysql\\nDB_HOST=[[DB_HOST]]\\nDB_PORT=3306\\nDB_DATABASE=[[DB_DATABASE]]\\nDB_USERNAME=[[DB_USER]]\\nDB_PASSWORD=[[DB_PASS]]\\n\\nBROADCAST_DRIVER=log\\nCACHE_DRIVER=file\\nFILESYSTEM_DISK=local\\nQUEUE_CONNECTION=sync\\nSESSION_DRIVER=file\\nSESSION_LIFETIME=120\\n\\nMEMCACHED_HOST=127.0.0.1\\n\\nREDIS_HOST=127.0.0.1\\nREDIS_PASSWORD=null\\nREDIS_PORT=6379\\n\\nMAIL_MAILER=smtp\\nMAIL_HOST=mailhog\\nMAIL_PORT=1025\\nMAIL_USERNAME=null\\nMAIL_PASSWORD=null\\nMAIL_ENCRYPTION=null\\nMAIL_FROM_ADDRESS=\\\"hello@example.com\\\"\\nMAIL_FROM_NAME=\\\"${APP_NAME}\\\"\\n\\nAWS_ACCESS_KEY_ID=\\nAWS_SECRET_ACCESS_KEY=\\nAWS_DEFAULT_REGION=us-east-1\\nAWS_BUCKET=\\nAWS_USE_PATH_STYLE_ENDPOINT=false\\n\\nPUSHER_APP_ID=\\nPUSHER_APP_KEY=\\nPUSHER_APP_SECRET=\\nPUSHER_APP_CLUSTER=mt1\\n\\nMIX_PUSHER_APP_KEY=\\\"${PUSHER_APP_KEY}\\\"\\nMIX_PUSHER_APP_CLUSTER=\\\"${PUSHER_APP_CLUSTER}\\\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:500px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud ENV Recipe »» [WordPress]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-env-recipe-wordpress",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"fmn34pq1svs000000000\",\"field_input_name\":\"\",\"fieldName\":\"WordPress Config\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud ENV Recipe »» [WordPress]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-env-recipe-wordpress",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2gs1ctigats0000000000\",\"field_input_name\":\"env_path\",\"fieldName\":\"Path\",\"inputName\":\"env_path\",\"textType\":\"text\",\"defaultValue\":\"/var/www/wordpress/wp-config.php\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Path\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud ENV Recipe »» [WordPress]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-env-recipe-wordpress",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2dbjfvhmbbtw000000000\",\"field_input_name\":\"env_content\",\"fieldName\":\"Content\",\"inputName\":\"env_content\",\"textType\":\"textarea\",\"defaultValue\":\"<?php\\n/**\\n * The base configuration for WordPress\\n *\\n * The wp-config.php creation script uses this file during the installation.\\n * You don't have to use the web site, you can copy this file to \\\"wp-config.php\\\"\\n * and fill in the values.\\n *\\n * This file contains the following configurations:\\n *\\n * * Database settings\\n * * Secret keys\\n * * Database table prefix\\n * * ABSPATH\\n *\\n * @link https://wordpress.org/documentation/article/editing-wp-config-php/\\n *\\n * @package WordPress\\n */\\n\\n// ** Database settings - You can get this info from your web host ** //\\n/** The name of the database for WordPress */\\ndefine( 'DB_NAME', '[[DB_DATABASE]]' );\\n\\n/** Database username */\\ndefine( 'DB_USER', '[[DB_USER]]' );\\n\\n/** Database password */\\ndefine( 'DB_PASSWORD', '[[DB_PASS]]' );\\n\\n/** Database hostname */\\ndefine( 'DB_HOST', '[[DB_HOST]]' );\\n\\n/** Database charset to use in creating database tables. */\\ndefine( 'DB_CHARSET', 'utf8' );\\n\\n/** The database collate type. Don't change this if in doubt. */\\ndefine( 'DB_COLLATE', '' );\\n\\n/**#@+\\n * Authentication unique keys and salts.\\n *\\n * Change these to different unique phrases! You can generate these using\\n * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.\\n *\\n * You can change these at any point in time to invalidate all existing cookies.\\n * This will force all users to have to log in again.\\n *\\n * @since 2.6.0\\n */\\ndefine( 'AUTH_KEY',         '[[RAND_STRING]]' );\\ndefine( 'SECURE_AUTH_KEY',  '[[RAND_STRING]]' );\\ndefine( 'LOGGED_IN_KEY',    '[[RAND_STRING]]' );\\ndefine( 'NONCE_KEY',        '[[RAND_STRING]]' );\\ndefine( 'AUTH_SALT',        '[[RAND_STRING]]' );\\ndefine( 'SECURE_AUTH_SALT', '[[RAND_STRING]]' );\\ndefine( 'LOGGED_IN_SALT',   '[[RAND_STRING]]' );\\ndefine( 'NONCE_SALT',       '[[RAND_STRING]]' );\\n\\n/**#@-*/\\n\\n/**\\n * WordPress database table prefix.\\n *\\n * You can have multiple installations in one database if you give each\\n * a unique prefix. Only numbers, letters, and underscores please!\\n */\\n$table_prefix = 'wp_';\\n\\n/**\\n * For developers: WordPress debugging mode.\\n *\\n * Change this to true to enable the display of notices during development.\\n * It is strongly recommended that plugin and theme developers use WP_DEBUG\\n * in their development environments.\\n *\\n * For information on other constants that can be used for debugging,\\n * visit the documentation.\\n *\\n * @link https://wordpress.org/documentation/article/debugging-in-wordpress/\\n */\\ndefine( 'WP_DEBUG', false );\\n\\n/* Add any custom values between this line and the \\\"stop editing\\\" line. */\\n\\n\\n\\n/* That's all, stop editing! Happy publishing. */\\n\\n/** Absolute path to the WordPress directory. */\\nif ( ! defined( 'ABSPATH' ) ) {\\n\\tdefine( 'ABSPATH', __DIR__ . '/' );\\n}\\n\\n/** Sets up WordPress vars and included files. */\\nrequire_once ABSPATH . 'wp-settings.php';\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:500px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud ENV Recipe »» [Manual]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-env-recipe-manual",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"id7xhplib5s000000000\",\"field_input_name\":\"\",\"fieldName\":\"Manual ENV\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud ENV Recipe »» [Manual]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-env-recipe-manual",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"43iqrc0f3re0000000000\",\"field_input_name\":\"env_path\",\"fieldName\":\"ENV Path\",\"inputName\":\"env_path\",\"textType\":\"text\",\"defaultValue\":\"/var/www/.env\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter ENV Path\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud ENV Recipe »» [Manual]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-env-recipe-manual",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3tenqnk5guu0000000000\",\"field_input_name\":\"env_content\",\"fieldName\":\"ENV Content\",\"inputName\":\"env_content\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter your manual config\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:500px;\"}"
	},
	{
		"field_field_name": "App Tonicscloud App Config Script",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-app-config-script",
		"field_parent_id": null,
		"field_options": "{\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"em5qrt886bs000000000\",\"field_input_name\":\"app_config_script_recipe\",\"fieldName\":\"Script Recipe\",\"inputName\":\"app_config_script_recipe\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App Tonicscloud App Config Script",
		"field_name": "modular_fieldselectiondropper",
		"field_id": 2,
		"field_slug": "app-tonicscloud-app-config-script",
		"field_parent_id": 1,
		"field_options": "{\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"4k8ygj5o0p60000000000\",\"field_input_name\":\"app_config_script_recipe_selected\",\"fieldName\":\"Choose Recipe\",\"inputName\":\"app_config_script_recipe_selected\",\"fieldSlug\":[\"app-tonicscloud-script-recipe-manual\",\"app-tonicscloud-script-recipe-tonics\",\"app-tonicscloud-script-recipe-wordpress\"],\"defaultFieldSlug\":\"app-tonicscloud-script-recipe-tonics\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\"}"
	},
	{
		"field_field_name": "App TonicsCloud Script Recipe »» [Manual]",
		"field_name": "input_text",
		"field_id": 1,
		"field_slug": "app-tonicscloud-script-recipe-manual",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"64fmuksdbqo0000000000\",\"field_input_name\":\"content\",\"fieldName\":\"Manual Script\",\"inputName\":\"content\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:500px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud Script Recipe »» [Tonics]",
		"field_name": "input_text",
		"field_id": 1,
		"field_slug": "app-tonicscloud-script-recipe-tonics",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"1qq2mvb0l1r400000000\",\"field_input_name\":\"content\",\"fieldName\":\"Tonics Script\",\"inputName\":\"content\",\"textType\":\"textarea\",\"defaultValue\":\"chown -R \\\"www-data:www-data\\\" \\/var\\/www\\/*\\n\\n#\\n#   Change permission of all directory and files\\nfind \\/var\\/www\\/ -type d -exec chmod 755 {} \\\\;\\nfind \\/var\\/www\\/ -type f -exec chmod 644 {} \\\\;\\n\\n#\\n#   Change permission of env file\\nchmod 660 \\/var\\/www\\/*\\/web\\/.env\\n\\n#\\n#   Allow Tonics To Manage private uploads\\nfind \\/var\\/www\\/*\\/private -type d -exec chmod 775 {} \\\\;\\nfind \\/var\\/www\\/*\\/private -type f -exec chmod 664 {} \\\\;\\n\\n#\\n#   Allow Tonics To Manage public contents\\nfind \\/var\\/www\\/*\\/web\\/public -type d -exec chmod 775 {} \\\\;\\nfind \\/var\\/www\\/*\\/web\\/public -type f -exec chmod 664 {} \\\\;\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:500px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud Script Recipe »» [WordPress]",
		"field_name": "input_text",
		"field_id": 1,
		"field_slug": "app-tonicscloud-script-recipe-wordpress",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"70djp4s0l6g0000000000\",\"field_input_name\":\"content\",\"fieldName\":\"WordPress Script\",\"inputName\":\"content\",\"textType\":\"textarea\",\"defaultValue\":\"chown -R \\\"www-data:www-data\\\" \\/var\\/www\\/*\\n\\n#\\n#   Change permission of all directory and files\\nfind \\/var\\/www\\/ -type d -exec chmod 755 {} \\\\;\\nfind \\/var\\/www\\/ -type f -exec chmod 644 {} \\\\;\\n\\n#\\n#   Change permission of wp-config\\nchmod 660 \\/var\\/www\\/*\\/wp-config.php\\n\\n#\\n#   Allow WordPress To Manage public contents\\nfind \\/var\\/www\\/*\\/wp-content -type d -exec chmod 775 {} \\\\;\\nfind \\/var\\/www\\/*\\/wp-content -type f -exec chmod 664 {} \\\\;\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:500px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud Variable Recipe »» [Common]",
		"field_name": "input_text",
		"field_id": 1,
		"field_slug": "app-tonicscloud-variable-recipe-common",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"666lgfmyxgg0000000000\",\"field_input_name\":\"variables\",\"fieldName\":\"Common Variables\",\"inputName\":\"variables\",\"textType\":\"textarea\",\"defaultValue\":\"ACME_EMAIL=enter_acme_email_for_ssl\\nACME_DOMAIN=enter_acme_domain_for_ssl\\nDB_DATABASE=enter_databae_here\\nDB_USER=enter_username_here\\nDB_PASS=enter_password_here\\nDB_HOST=localhost\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:300px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Static Site (HTTPS)]",
		"field_name": "input_text",
		"field_id": 1,
		"field_slug": "app-tonicscloud-nginx-recipe-static-site-https",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"2pd2j1ct2p40000000000\",\"field_input_name\":\"config\",\"fieldName\":\"Static Config\",\"inputName\":\"config\",\"textType\":\"textarea\",\"defaultValue\":\"server {\\n    listen 80;\\n    listen [::]:80;\\n    server_name [[ACME_DOMAIN]];\\n\\n    # Redirect HTTP to HTTPS\\n    location \\/ {\\n        return 301 https:\\/\\/$host$request_uri;\\n    }\\n}\\n\\nserver {\\n    listen 443 ssl;\\n    server_name [[ACME_DOMAIN]];\\n\\n    http2 on;\\n    ssl_certificate \\/etc\\/ssl\\/[[ACME_DOMAIN]]_fullchain.cer;\\n    ssl_certificate_key \\/etc\\/ssl\\/[[ACME_DOMAIN]].key;\\n    ssl_protocols        TLSv1.3 TLSv1.2 TLSv1.1;\\n\\n    location \\/ {\\n        root \\/var\\/www\\/[[ACME_DOMAIN]];\\n        index index.html;\\n    }\\n\\n    # Additional HTTPS configurations if needed\\n}\\n\",\"info\":\"Please ensure you have an SSL before using this recipe, otherwise, use the HTTP version and use the ACME app to generate an SSL, then choose this recipe once you done.\\n<br>\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:500px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud Nginx Recipe »» [Static Site (HTTP)]",
		"field_name": "input_text",
		"field_id": 1,
		"field_slug": "app-tonicscloud-nginx-recipe-static-site-http",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"1gy1i0f12mm8000000000\",\"field_input_name\":\"\",\"fieldName\":\"Static Config\",\"inputName\":\"config\",\"textType\":\"textarea\",\"defaultValue\":\"server {\\n    listen 80;\\n    listen [::]:80;\\n    server_name [[ACME_DOMAIN]];\\n\\n    root \\/var\\/www\\/[[ACME_DOMAIN]];\\n    index index.html;\\n\\n    location \\/ {\\n        try_files $uri $uri\\/ =404;\\n    }\\n}\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:500px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud ENV Recipe »» [Tonics Existing Installation]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-env-recipe-tonics-existing-installation",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4l24vxoek1e0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Tonics ENV\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud ENV Recipe »» [Tonics Existing Installation]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-env-recipe-tonics-existing-installation",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"47p55jagaki0000000000\",\"field_input_name\":\"env_path\",\"fieldName\":\"ENV Path\",\"inputName\":\"env_path\",\"textType\":\"text\",\"defaultValue\":\"/var/www/tonics/web/.env\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter ENV Path\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud ENV Recipe »» [Tonics Existing Installation]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-env-recipe-tonics-existing-installation",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4s86dq48s9s0000000000\",\"field_input_name\":\"env_content\",\"fieldName\":\"ENV Content\",\"inputName\":\"env_content\",\"textType\":\"textarea\",\"defaultValue\":\"APP_NAME=Tonics\\nAPP_ENV=production\\nAPP_URL_PORT=443\\nAPP_URL=https://[[ACME_DOMAIN]]\\nAPP_TIME_ZONE=Africa/Lagos\\nAPP_LANGUAGE=0\\nAPP_LOG_404=1\\nAPP_PAGINATION_MAX_LIMIT=20\\nAPP_STARTUP_CLI_FORK_LIMIT=1\\n\\nJOB_TRANSPORTER=DATABASE\\nSCHEDULE_TRANSPORTER=DATABASE\\n\\nINSTALL_KEY=[[RAND_STRING]]\\nAPP_KEY=[[RAND_STRING]]\\nSITE_KEY=[[RAND_STRING]]\\n\\nMAINTENANCE_MODE=0\\nAUTO_UPDATE_MODULES=1\\nAUTO_UPDATE_APPS=1\\n\\nACTIVATE_EVENT_STREAM_MESSAGE=1\\n\\nDB_CONNECTION=mysql\\nDB_HOST=[[DB_HOST]]\\nDB_PORT=3306\\nDB_DATABASE=[[DB_DATABASE]]\\nDB_USERNAME=[[DB_USER]]\\nDB_PASSWORD=[[DB_PASS]]\\nDB_CHARSET=utf8mb4\\nDB_ENGINE=InnoDB\\nDB_PREFIX=tonics_\\n\\nMAIL_MAILER=smtp\\nMAIL_HOST=mail.domain.com\\nMAIL_PORT=587\\nMAIL_USERNAME=user\\nMAIL_PASSWORD=password\\nMAIL_ENCRYPTION=tls\\nMAIL_FROM_ADDRESS=user@mail.domain.com\\nMAIL_REPLY_TO=user@mail.domain.com\\n\\nDROPBOX_KEY=xxx\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:500px;\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Standalone Static Site]",
		"field_name": "input_text",
		"field_id": 1,
		"field_slug": "app-tonicscloud-automation-standalone-static-site",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"2nk59qiqyge000000000\",\"field_input_name\":\"tonicsCloud_standalone_static_site_domainName\",\"fieldName\":\"Domain Name\",\"inputName\":\"tonicsCloud_standalone_static_site_domainName\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Domain Name e.g example.com\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Standalone Static Site]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-automation-standalone-static-site",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"1vp0l5u7ld4w000000000\",\"field_input_name\":\"tonicsCloud_standalone_static_site_emailAddress\",\"fieldName\":\"Email Address\",\"inputName\":\"tonicsCloud_standalone_static_site_emailAddress\",\"textType\":\"email\",\"defaultValue\":\"[[TONICS_CUSTOMER_EMAIL]]\",\"info\":\"Email Address is required for SSL Cert\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Email Address (For SSL)\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Standalone Static Site]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-automation-standalone-static-site",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"5qj0ynehj8c0000000000\",\"field_input_name\":\"tonicsCloud_standalone_static_site_archiveFile\",\"fieldName\":\"Archive File\",\"inputName\":\"tonicsCloud_standalone_static_site_archiveFile\",\"textType\":\"url\",\"defaultValue\":\"\",\"info\":\"Link to the site project, ensure this is an archive file and it has no parent folder\\n<br>\\nIt supports multiple formats: (.zip, .tar.gz, .tgz, .tar.bz, .tbz, .tar, 7z, .rar, .gz, .lz, etc)\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter URL\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Multiple Static Site]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 1,
		"field_slug": "app-tonicscloud-automation-multiple-static-site",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"28ehadgb7udc000000000\",\"field_input_name\":\"\",\"fieldName\":\"Static Site\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Add New Static Site\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Multiple Static Site]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-automation-multiple-static-site",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2nk59qiqyge000000000\",\"field_input_name\":\"tonicsCloud_multi_static_site_domainName[]\",\"fieldName\":\"Domain Name\",\"inputName\":\"tonicsCloud_multi_static_site_domainName[]\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Domain Name e.g example.com\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Multiple Static Site]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-automation-multiple-static-site",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1vp0l5u7ld4w000000000\",\"field_input_name\":\"tonicsCloud_multi_static_site_emailAddress[]\",\"fieldName\":\"Email Address\",\"inputName\":\"tonicsCloud_multi_static_site_emailAddress[]\",\"textType\":\"email\",\"defaultValue\":\"[[TONICS_CUSTOMER_EMAIL]]\",\"info\":\"Email Address is required for SSL Cert\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Email Address (For SSL)\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Multiple Static Site]",
		"field_name": "input_text",
		"field_id": 4,
		"field_slug": "app-tonicscloud-automation-multiple-static-site",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5qj0ynehj8c0000000000\",\"field_input_name\":\"tonicsCloud_multi_static_site_archiveFile[]\",\"fieldName\":\"Archive File\",\"inputName\":\"tonicsCloud_multi_static_site_archiveFile[]\",\"textType\":\"url\",\"defaultValue\":\"\",\"info\":\"Link to the site project, ensure this is an archive file and it has no parent folder\\n<br>\\nIt supports multiple formats: (.zip, .tar.gz, .tgz, .tar.bz, .tbz, .tar, 7z, .rar, .gz, .lz, etc)\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter URL\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Tonics CMS]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 1,
		"field_slug": "app-tonicscloud-automation-tonics-cms",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\",\"TonicsCloudRenderDefaultContainerVariables\",\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"1szcf417ki9s000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Tonics Site\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"\",\"info\":\"<pre style= \\\"all: revert;\\\">\\nNote For Archive: Archive is only for existing Tonics installation (Tonics solution would be overwritten if an archive is provided).\\nLastly, it should have the following structure explicitly:\\n.\\n\\u251c\\u2500\\u2500 private\\n\\u2514\\u2500\\u2500 web\\n\\u2514\\u2500\\u2500 exported.sql (the exported db)\\n<\\/pre>\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"Add New Tonics\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Tonics CMS]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-automation-tonics-cms",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"62zh0vd9f7o0000000000\",\"field_input_name\":\"tonicsCloud_tonicsCMS_site_domainName[]\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Domain Name\",\"inputName\":\"tonicsCloud_tonicsCMS_site_domainName[]\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Your domain name without the http(s) prefix\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Domain Name e.g example.com\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Tonics CMS]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-automation-tonics-cms",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"73rzl8qlhk80000000000\",\"field_input_name\":\"tonicsCloud_tonicsCMS_site_dbUser[]\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"DB User\",\"inputName\":\"tonicsCloud_tonicsCMS_site_dbUser[]\",\"textType\":\"text\",\"defaultValue\":\"[[RAND_USERNAME_RENDER]]\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Your Database Username\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Tonics CMS]",
		"field_name": "input_select",
		"field_id": 4,
		"field_slug": "app-tonicscloud-automation-tonics-cms",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"3d5f3dccyo80000000000\",\"field_input_name\":\"tonicsCloud_tonicsCMS_site_solution[]\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Solution\",\"inputName\":\"tonicsCloud_tonicsCMS_site_solution[]\",\"selectData\":\"CloudTonics,WriTonics,AudioTonics\",\"defaultValue\":\"CloudTonics\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\",\"hookName\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Tonics CMS]",
		"field_name": "input_text",
		"field_id": 5,
		"field_slug": "app-tonicscloud-automation-tonics-cms",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"5egelus1qs40000000000\",\"field_input_name\":\"tonicsCloud_tonicsCMS_site_emailAddress[]\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Email Address\",\"inputName\":\"tonicsCloud_tonicsCMS_site_emailAddress[]\",\"textType\":\"text\",\"defaultValue\":\"[[TONICS_CUSTOMER_EMAIL]]\",\"info\":\"Email Address is required for SSL Cert\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Email Address (For SSL)\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Tonics CMS]",
		"field_name": "input_text",
		"field_id": 6,
		"field_slug": "app-tonicscloud-automation-tonics-cms",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"1wv2lf2ctglc000000000\",\"field_input_name\":\"tonicsCloud_tonicsCMS_site_dbPass[]\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"DB Pass\",\"inputName\":\"tonicsCloud_tonicsCMS_site_dbPass[]\",\"textType\":\"password\",\"defaultValue\":\"[[RAND_STRING_RENDER]]\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Your Database Password\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Tonics CMS]",
		"field_name": "input_text",
		"field_id": 7,
		"field_slug": "app-tonicscloud-automation-tonics-cms",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"6ympfq0bqg80000000000\",\"field_input_name\":\"tonicsCloud_tonicsCMS_site_archive[]\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Archive (Optional)\",\"inputName\":\"tonicsCloud_tonicsCMS_site_archive[]\",\"textType\":\"url\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Archive Zip Link\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [WordPress CMS]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 1,
		"field_slug": "app-tonicscloud-automation-wordpress-cms",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\",\"TonicsCloudRenderDefaultContainerVariables\",\"TonicsCloudRenderDefaultContainerVariables\",\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"1szcf417ki9s000000000\",\"field_input_name\":\"\",\"fieldName\":\"WordPress Site\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"\",\"info\":\"<pre style= \\\"all: revert;\\\">\\nNote For Archive: Archive is only for existing WordPress installation.\\nLastly, it should have the following structure explicitly:\\n.\\n\\u251c\\u2500\\u2500 wp-contents\\n\\u2514\\u2500\\u2500 wp-includes\\n\\u2514\\u2500\\u2500 ... (and all of the remaining wordpress files)\\n\\u2514\\u2500\\u2500 exported.sql (the exported db)\\n<\\/pre>\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Add New Tonics\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [WordPress CMS]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-automation-wordpress-cms",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"62zh0vd9f7o0000000000\",\"field_input_name\":\"tonicsCloud_wordpressCMS_site_domainName[]\",\"fieldName\":\"Domain Name\",\"inputName\":\"tonicsCloud_wordpressCMS_site_domainName[]\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Your domain name without the http(s) prefix\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Domain Name e.g example.com\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [WordPress CMS]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-automation-wordpress-cms",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"73rzl8qlhk80000000000\",\"field_input_name\":\"tonicsCloud_wordpressCMS_site_dbUser[]\",\"fieldName\":\"DB User\",\"inputName\":\"tonicsCloud_wordpressCMS_site_dbUser[]\",\"textType\":\"text\",\"defaultValue\":\"[[RAND_USERNAME_RENDER]]\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Your Database Username\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [WordPress CMS]",
		"field_name": "input_text",
		"field_id": 4,
		"field_slug": "app-tonicscloud-automation-wordpress-cms",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"zhilpghlvw0000000000\",\"field_input_name\":\"tonicsCloud_wordpressCMS_site_dbName[]\",\"fieldName\":\"DB Name\",\"inputName\":\"tonicsCloud_wordpressCMS_site_dbName[]\",\"textType\":\"text\",\"defaultValue\":\"wordpress\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Database Name\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [WordPress CMS]",
		"field_name": "input_text",
		"field_id": 5,
		"field_slug": "app-tonicscloud-automation-wordpress-cms",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"5egelus1qs40000000000\",\"field_input_name\":\"tonicsCloud_wordpressCMS_site_emailAddress[]\",\"fieldName\":\"Email Address\",\"inputName\":\"tonicsCloud_wordpressCMS_site_emailAddress[]\",\"textType\":\"text\",\"defaultValue\":\"[[TONICS_CUSTOMER_EMAIL]]\",\"info\":\"Email Address is required for SSL Cert\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Email Address (For SSL)\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [WordPress CMS]",
		"field_name": "input_text",
		"field_id": 6,
		"field_slug": "app-tonicscloud-automation-wordpress-cms",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"1wv2lf2ctglc000000000\",\"field_input_name\":\"tonicsCloud_wordpressCMS_site_dbPass[]\",\"fieldName\":\"DB Pass\",\"inputName\":\"tonicsCloud_wordpressCMS_site_dbPass[]\",\"textType\":\"password\",\"defaultValue\":\"[[RAND_STRING_RENDER]]\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Your Database Password\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [WordPress CMS]",
		"field_name": "input_text",
		"field_id": 7,
		"field_slug": "app-tonicscloud-automation-wordpress-cms",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"6ympfq0bqg80000000000\",\"field_input_name\":\"tonicsCloud_wordpressCMS_site_archive[]\",\"fieldName\":\"Archive (Optional)\",\"inputName\":\"tonicsCloud_wordpressCMS_site_archive[]\",\"textType\":\"url\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Archive Zip Link\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"1cu67cyatly8000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Haraka Config\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6do8a05l9dw0000000000\",\"field_input_name\":\"haraka_server\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Server\",\"inputName\":\"haraka_server\",\"textType\":\"textarea\",\"defaultValue\":\"[[ACME_DOMAIN]]\",\"info\":\"This is where you add the name of your smtp server, e.g mail.example.com or smtp.example.com\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:150px;\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5vzxl754hmo0000000000\",\"field_input_name\":\"haraka_tls\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"TLS\",\"inputName\":\"haraka_tls\",\"textType\":\"textarea\",\"defaultValue\":\"key=/etc/ssl/[[ACME_DOMAIN]].key\\ncert=/etc/ssl/[[ACME_DOMAIN]]_fullchain.cer\",\"info\":\"Certification generated by ACME.sh for secure connection\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"height:150px;\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "modular_rowcolumn",
		"field_id": 4,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"2pk9rk1vyem0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Aliases\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"<pre style=\\\"all: revert;\\\">\\nThere is no concept of viewing a received mail, but with aliases you can forward it to another mailboxes, e.g, gmail, outlook, zoho, etc. \\nThis way we can keep things light and secure.\\n</pre>\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 5,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"502icowu1ak0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Alias\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"Repeat Alias\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "input_text",
		"field_id": 6,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": 5,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3okv3dyot1c0000000000\",\"field_input_name\":\"alias_from\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"From\",\"inputName\":\"alias_from\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Where the email would be sent to\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Where the email would be sent to\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "input_text",
		"field_id": 7,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": 5,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"3bxnaffjxzi0000000000\",\"field_input_name\":\"alias_to\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"To\",\"inputName\":\"alias_to\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Where it should be forwarded to.\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Can be separated by comma for multiple addresses\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "modular_rowcolumn",
		"field_id": 8,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"3mfflb3bb080000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"SMTP Credentials\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"<pre style=\\\"all: revert;\\\">Your smtp info would be:\\nHost: your_mail_server_domain (the one in server config)\\nUser: your_choosen_credential_user\\nPass: your_choosen_credential_pass\\nPort: 587\\nEncryption: tls\\n</pre>\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 9,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": 8,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"3sdfgwrsoqa0000000000\",\"field_input_name\":\"haraka_smtp_credentials_credential\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Credential\",\"inputName\":\"haraka_smtp_credentials_credential\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"\",\"repeat_button_text\":\"Add New Credential\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "input_text",
		"field_id": 10,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": 9,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4iwowl59nyc0000000000\",\"field_input_name\":\"haraka_smtp_credentials_credential_username\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Username\",\"inputName\":\"haraka_smtp_credentials_credential_username\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter your new smtp username\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "input_text",
		"field_id": 11,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": 9,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"2oygc896l540000000000\",\"field_input_name\":\"haraka_smtp_credentials_credential_password\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Password\",\"inputName\":\"haraka_smtp_credentials_credential_password\",\"textType\":\"password\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter your new smtp password\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "modular_rowcolumn",
		"field_id": 12,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"6t0fynafac00000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"DNS\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 13,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": 12,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"39naf0vgi0e0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Domain\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"If this is the first time, Ensure regenerate DNS is set to True\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"\",\"repeat_button_text\":\"Add New Domain\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "input_select",
		"field_id": 14,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": 13,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4mvmcdobn240000000000\",\"field_input_name\":\"haraka_regenerate\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Regenerate DNS\",\"inputName\":\"haraka_regenerate\",\"selectData\":\"True,False\",\"defaultValue\":\"True\",\"info\":\"Regenration should be done only once except something went wrong and you want to regenerate.\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "input_text",
		"field_id": 15,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": 13,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"72jyjbup0ew0000000000\",\"field_input_name\":\"haraka_dkim_domain\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Domain Name\",\"inputName\":\"haraka_dkim_domain\",\"textType\":\"url\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Domain Name\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud [App Config] [Haraka]",
		"field_name": "input_text",
		"field_id": 16,
		"field_slug": "app-tonicscloud-app-config-haraka",
		"field_parent_id": 13,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"29br9v92yjfo000000000\",\"field_input_name\":\"haraka_dns_info\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"DNS Info (Read-Only)\",\"inputName\":\"haraka_dns_info\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"This is read-only, the dns info would show here.\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"1\",\"required\":\"1\",\"styles\":\"height:200px;\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Haraka MailServer]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicscloud-automation-haraka-mailserver",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4dg0lqa2qtk0000000000\",\"field_input_name\":\"\",\"fieldName\":\"MailServer\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"\",\"info\":\"This would configure a secure Haraka Mail Server for you automatically with dkim, dmarc, spf, authentication, no ssh, and no open relay.\\n\\nPlease ensure the domain points to the instance ip address.\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Haraka MailServer]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicscloud-automation-haraka-mailserver",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1y3wg5w5ufsw000000000\",\"field_input_name\":\"tonicsCloud_tonicsHaraka_domainName\",\"fieldName\":\"Domain Name\",\"inputName\":\"tonicsCloud_tonicsHaraka_domainName\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"Your domain name without the http(s) prefix\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Domain Name e.g example.com\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	},
	{
		"field_field_name": "App TonicsCloud Automation »» [Haraka MailServer]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicscloud-automation-haraka-mailserver",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[\"TonicsCloudRenderDefaultContainerVariables\"],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"2q8ai0hu38o0000000000\",\"field_input_name\":\"tonicsCloud_tonicsHaraka_emailAddress\",\"fieldName\":\"Email Address\",\"inputName\":\"tonicsCloud_tonicsHaraka_emailAddress\",\"textType\":\"email\",\"defaultValue\":\"[[TONICS_CUSTOMER_EMAIL]]\",\"info\":\"Email Address is required for SSL Cert\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Email Address (For SSL)\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\"}"
	}
]
JSON;
        return json_decode($json);
    }

    public function onUninstall(): void
    {
        return;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function onUpdate(): void
    {
        $this->fieldData->importFieldItems($this->fieldItems());
        self::UpdateCloudImages();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function UpdateCloudImages(): void
    {
        db(onGetDB: function (TonicsQuery $db) {
            $cloudImageTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINER_IMAGES);
            $db->Q()->insertOnDuplicate($cloudImageTable, self::CloudImages(), ['container_image_description', 'container_image_logo', 'others']);
        });
    }

    /**
     * @return mixed
     */
    public static function CloudImages(): mixed
    {
        $images = <<<IMAGES
eNrtnftv4rgWx/8VhHSlXYmH7TiOzfy0d3elXWle2sdd6S6ryK/Q3AJhAkznofnfr5MQCjQptIGZQs+o7ZDEie1znNgn3w+2HASDz/EAvZADOvg8HxA0aOtkupDx1KZhPJEjG07lxLZfzF2C9ks7mWUfq5KNk1GSHQuCQftqsZjNB/3+cjZOpOndxNfxxJpY9pJ01M+2ZtlWXyeTSTKd93mf0/7LeLr8EC7cr45TPS4u2Ju/H+UZBnczNHau03i2iJNplsRjRflav2ZHW9+9kmksf/p3p/X2l7edlpya1utRPP3wfZbWJU0WVzadZxsYBdxd/nM7v+y8Pfjcfq+S5PomSSfry7Qw6mGcX6zFe6RTXKyFe4R8n50xidM0cdcb/L2q/LA/7I/ixdVS9Vw1h/0/kmk8/3GcLM0vSzXsL9ymnnfHrsDDfmrHVs7tfNg3yc00M9mwn1UlDPNce7y7KkXXWIVJGLoS9AjqusK4JK4IPdzNy/PI03oLmfZGn9r/dAobhFdyftUetI3gyENKRIQjgYwwmkTE86im0tfC9zU1AnncGhT5gmtNuaFcoggTgriiUftLp9KUuEdLS3pfzZIu0x65axHPmanejg85qc6KXGOpI6x9ZXlkgwhL6UmuIoyjiAkc+VFgvMiThhkZuKZosS8osy6R4ZQr1f7izChns9wkf77+769v2522y939XRXMfcpL4P7/4cdXP7v/fn79H/f39/z+OH6BOu0wiu3Y/GTdDTl25Wr//XnYzneFsRm2B7hTbs5kaqeLYu90OR6vD2RPFbdv2J4kZjmWaZgmNzoZLyfTYXudKJ7OlovbpPmRiXsGFNmH8/FylB9w1ukW7UBn7aKbV7c7c382Lpbkj4p5fsLn4bDcnV9kmO0dVhVmmF1heDfb8ox7si5PvT0rXE7jd8vCEesrkFhHN/MUee+u0frfzskbhihPy36/uJ/OtulJpenxXbsXl1zYD4t6g1d1Bad3wkbJTm99D8fv0niy+LS8eYj1Ky1TnlV3MD8ze7LU+M47pe82ussLcyHRepTEkwCnMW/gwk0D1XlyJ83aofsGHTUOp8d0eFHCrKO4NP+ia+6//9+HAL/jh7t3wxxl2u1deaq8P125a+283GedVtaXdlquK+20ip60xov+KW/bbBR8Yf5kV26ImIwQuxo1eeTmlqm7UcuD+Zm348iqgGR4G5EMy5Bk2Ofuhw7ro5KatsBOM/JZVSqeXoer8XG4rvFljoowd+OisVafxPwhjeReQ1W7LKh0GTvAX6mdWblYeeBQv8XTKPmmzluXujRmOElS+2Z1qey0z+tHwus9tt2pzKEPgPdyMRd+NFvQWt8+sA4bripPKrrkX10J16mMnS1ui3GbZXG5UC0XzuTrx2Ce6Lf8UNG/r9OP0ti4ZJPZ2BXC1X+8Tt7KDnXLQ92itPNB68VtXsnNbSPfeH6V99XOgVDb8TicJfN4Pegonib5MTl1ln1vw9IeqyrkySI5ntsvp36WH+DKw27Tu02p4l7llfdq0GTAtFWI9zadf6sBcoWnt5vCCb1Ir5lVRqQfUUMvlgbcHnLVJcmv8rCXbTUNQxy/YeQWemYN4VM80pxZHz8oktqw17bft67d9G1ijecxOtT1x+i/b5+0efqz7r5363JgG6EfY5EKpUb05oS995+/vWzSbf9gTMuN3a9br/KqPpGeezfBfd33Il2ueu9v+Dw4xNeHdAwVTa3qVq5+b7xxhzfr3//+59l17B+xvbr+QGjcbHjmLFfboxfHdgLtM5O+6hokgdgQYsMLjA1vO5eLCQ1xjXpCIDi8sOCwFh+oaxn0BC0DosPjRodNoYQ63/sH+x7CQwgPITx8yuFhtbi2cYtDePh8wsOvxfOVjfGf9pf2iy/xAB/CygaDNZu3B5cV7BaXTeVNr7DMcm7T7AzXyDMjbduoNNEky8GoftY++quN7lWSxp+yvMZdNZb6+iEELR20/8gvnedU8hCtJIpiHctxKz+rgp+lAdnhZ/MovFuO3RpgnatqVbeEss5lPt2d4H9/ijpmk2pFhBaBlr5mDLFAYuYhbTwiA8yYJcogSdwvj7CJ/MA3TFkSUOxHEiHPy8nXrLV9AxtUtPEDk9USrDIgPFIURUb4rvbYIhP4UkWaCcF9ZZRPFRY60s5agnHkSTdIFFhTy0Rkoy2Ctbwvjp4JUKlApQKV+kgqtXxiAph6OWDq/r4csNQniqXefzsCcHo2wGntkH7vYC8zx/DeYT0gqICggswIMiMgqICggsrYSGXcfl8FjOm5qohN39sBYwoiIoiIwJiCiHgRIuLXUY8AHoWoDuBRgEchrHtyYd2mAg906BnToQ3hAKBDIbCDwA7oUAjsnmVg1xiJ24VAySEQqDfIp2ncN1/q4wHQ2dWsgD/dh+5Dp0tF27Bn9n2avaCnR8QO6MmdwY6AOLoKVPsyq9naQcWXOCt31qGLBDHqYc6RYYYSRoVVfhBYxIhV1FMkirQRzCIeIMYjEWCqAkKVSyzdKCJAOcjJe97XrKTX41X76qoorFFMG6wZipCPOBcikAgrXxD3yfquFUvLpCAUeX6EOHMmUZIqGgnjESq36MxiXtFyltF7pxFtnC8AmwBsArD5SGDT3agAa14orHm3LwZQ84mCmvnEoatJRGGm0OcMbuajuwLa3ByOA6QJkCbIeSDnAaQJkCaoeY3UvM13TYBonquU1/SNHCCaoOSBkgeIJih5F6HkHUv5AQgTojaAMAHChLDt6YVtHjCYFxC4NdX7gcGEyA0iN2AwIXJ7XpHb/TjbLlnpHUJWskH7F5nKa7kPruT80XDlVZ5BwVcWmb2S8fhfBL3MlN3Z9GDQ0tsGLYtr7WMtGcN8B7X0eug4HGJRs2rfFcduXZVnWrO7DkhUwtjA9f4oCrhQEcXWRJHxtPB8jQnR3FOIeBLZgCjGJBJGIYaJ8HjEUUC9LSBxtaD5yt21MGLjPAFGBBgRYMRHwojF3Qk84oXyiJVdFiCJTxRJLJYtL3wGPOKz5RHLUV6BJFaOYIFNBDYRVC5QuUDlAjQRNK4mGtf2uxmAE89V42r6GgngRJC4QOICOBEkrouQuI6slOxqXfQQrcsftPPl6PZJXWxjHpHlLCtj7ya+jifWxLKXpKN+tjXLtvquxhPXRvq6r/1+fvHwobOHkG1Rq1iEd//8IezOQnE9wo6haU2LZf6qHJUf2pjlxeXYwzW76yQtn1CipaR+oE1EPYaIDfwACetlc01rTTnHmkQR8wJicGAiEzBmEBERwxxFuFgPrkeCr17XoIdqdteufYelCTgJkNVC+9QXAZUkEEKhiGZfyVBuN9IeDXhEguzLGpQSqajFPg0iIbZXeyuabqeU8cqJRWpUvMZZg4oHKh6oeI9U8YqlUkHEu0wRr6qLBg3viWp4ubM6rULKW00vAkreZSh5VYPz4e3ofFgOz90H9+MPd0boINqBaAeiHYh2MKEIqHag2jVbHmDz5ROIducq2jV9OQeiHYh2INqBaAei3UWIdseVgmBeEQjegLiEeUUgenuC0VsAE4tcwqLdDVV/mFgEAjgI4GBiEQjgnmEAt5dv24Uu/UOgSzFo/5Wk5m1q5/MTzjHiSmtmWR7FNCN/lZvdlw/EMdk2jrku+14kE2O0i2Synt/zW9+tlsFr5Wued/I1aXiPfN+AW1zXttq368O3jlyVIQx3Fl4v1uYLw0IZD52/SQ+HoStjGBazgobh2gZhmNfo5BnUsqOIWMUxwjgSvqIaCySMINRqxbPhs0GR4FR5HraMaE9rzbAVXEoRMZea5OzorlNwj35zn1Qsl3hUlzS+fp1HDLWSUBo5u1udfXXHD7jRvvOK2y2QVUoyhKwbZXoujRa+JFhQSz3FFPHM9gQ1JdJarJy3Kq37tEO+3ruUXuMCAfcK3Ctwr4/kXtePDWBfL5R9rRsPAf/6RPnXFfKar6636lI7rS0oFhbbe86T22wMW4v5be6GTsDJAicLUitIrcDJAicLSmsjpfX+N4IAzp4tONvwzSSAs6C7gu4K4Czorhehuz4PfQ6AXogyAegFoBfCzKceZm4xDgD4nm+c2RRvAMAXAk0INAHwhUATAs1zgQ53sWN24LqGBbayjzkmLmU/2+gn3WQsu5NYX3/sPnQSV75NDRefWz+++n0Hk5nf5YZ9wrxtbji/xuoSJ4CH9aSmtayOH+WVwUYdXIrum+6bsezhAHP3w5j/VTOrhVcD5AZLilMcUF9Jw5QhlPMIaRMxjoRQEvvY45p4nBGFfMV9bAnzuaSMSS/Dif9K4zP31LoGp/fTvVnVeckTkvpIE4FtYJwHmJFMUoUixTQVRCOqrWGICs6Q9X0cSEWF7zEPW48LqzIv/bA0cXLmftqow+k9tSez2vVKPaKiCNkgW8xc80AGnvQRD4zhnjY6D1qo5xnmESQ5twHWntvkhrm0vrFHx8EbFwhwcMDBAQd/JA5ePECABb9QFrx+lAs0ONDgQIN/Kxp8mMezw8qAFlhuYLlBZQeVHVhuYLlBZG8ish/ylhaA7rMV2hu+GwagG3R20NkB6Aad/SJ09ueqmQLiDcEnBJ/AeEP4+dTCz/3gCXDe5xt+NoVegPOG+BPiT+C8If6E+PNcSdC6dg/CJ8Se8PXi8wg9q4VPDMrnOYeeh9D0dQ1CnKBBQPB53OCzKTRfh2ujg30PwScEnxB8PuHgk1SLnwTETwg+L+brbdtfPP7yfxIMcho=
IMAGES;
        return unserialize(gzuncompress(base64_decode($images)));
    }

    public function onDelete(): void
    {
    }

    /**
     * @throws \Throwable
     */
    public function info(): array
    {
        return [
            "name" => "TonicsCloud",
            "type" => "App", // You can change it to 'Theme', 'Tools', 'Modules' or Any Category Suited for Your App
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "slug_id" => 'a67af86b-27ca-11ef-9736-124c30cfdb6b',
            "version" => '1-O-app.1747085600',
            "description" => "This is TonicsCloud",
            "info_url" => '',
            "settings_page" => route('tonicsCloud.settings'), // can be null or a route name
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/app-tonics_cloud/releases/latest",
            "authors" => [
                "name" => "Your Name",
                "email" => "name@website.com",
                "role" => "Developer",
            ],
            "credits" => [],
        ];
    }
}