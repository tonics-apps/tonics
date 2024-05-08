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

namespace App\Apps\TonicsCloud\Controllers;

use App\Apps\TonicsCloud\Jobs\Domain\CloudJobQueueCreateDomain;
use App\Apps\TonicsCloud\Jobs\Domain\CloudJobQueueDeleteDomain;
use App\Apps\TonicsCloud\Jobs\Domain\CloudJobQueueUpdateDomain;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\EventHandlers\Fields\Modular\RowColumnRepeater;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class DomainController
{
    use Validator;
    private FieldData $fieldData;
    private AbstractDataLayer $abstractDataLayer;

    /**
     * @param FieldData $fieldData
     * @param AbstractDataLayer $abstractDataLayer
     */
    public function __construct(FieldData $fieldData, AbstractDataLayer $abstractDataLayer)
    {
        $this->fieldData = $fieldData;
        $this->abstractDataLayer = $abstractDataLayer;
    }

    /**
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function index()
    {
        $dataTableHeaders = [
            ['type' => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_DNS . '::' . 'slug_id',
                'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'slug_id'
            ],
            [
                'type' => '',
                'slug' => TonicsCloudActivator::TONICS_CLOUD_DNS . '::' . 'dns_domain',
                'title' => 'Domain',
                'minmax' => '55px, .7fr', 'td' => 'dns_domain'
            ],
            [
                'type' => '',
                'slug' => TonicsCloudActivator::TONICS_CLOUD_DNS . '::' . 'dns_status_msg',
                'title' => 'Info',
                'minmax' => '60px, .8fr', 'td' => 'dns_status_msg'
            ],
            ['type' => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_DNS . '::' . 'created_at', 'title' => 'Created At', 'minmax' => '70px, .6fr', 'td' => 'created_at'],
        ];

        $data = null;
        db( onGetDB: function (TonicsQuery $db) use (&$data){
            $dnsRecordsTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_DNS);
            $data = $db->Select('dns_id, slug_id, dns_domain, CONCAT("http://", dns_domain) _preview_link, dns_status_msg, created_at,
                CONCAT("/customer/tonics_cloud/domains/", slug_id, "/edit" ) as _edit_link')
                ->From("$dnsRecordsTable")
                ->WhereEquals('fk_customer_id', \session()::getUserID())
                ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('dns_domain', url()->getParam('query'));
                })
                ->OrderByDesc(table()->pickTable($dnsRecordsTable, ['created_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));
        });

        view('Apps::TonicsCloud/Views/Domain/index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $data ?? [],
                'dataTableType' => 'EDITABLE_PREVIEW',
            ],
            'SiteURL' => AppConfig::getAppUrl(),
        ]);

    }

    /**
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function dataTable()
    {
        $entityBag = null;
        if ($this->getAbstractDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deletion Enqueued", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        }
    }

    /**
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function create(): void
    {
        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Apps::TonicsCloud/Views/Domain/create', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $this->getFieldData()
                ->generateFieldWithFieldSlug(['app-tonicscloud-dns'], $oldFormInput)->getHTMLFrag()
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     * @throws \Throwable
     */
    public function store()
    {
        $validator = $this->getValidator();
        $validation = $validator->make(input()->fromPost()->all(), $this->getDomainCreateRule());
        if ($validator->fails()){
            session()->flash($validation->getErrors(), input()->fromPost()->all());
            redirect(route('tonicsCloud.domains.create'));
        }

        db(onGetDB: function (TonicsQuery $db){
            $serviceOthers = null;
            $service = null;
            if (input()->fromPost()->hasValue('dns_cloud_instance')){
                $settings = [
                    'instance_id' => input()->fromPost()->retrieve('dns_cloud_instance'),
                    'user_id' => \session()::getUserID()
                ];
                $service = InstanceController::GetServiceInstances($settings);
                $serviceOthers = json_decode($service->others);
            }

            $fields = json_decode(input()->fromPost()->retrieve('_fieldDetails'));
            $domain = $this->getSoaDomainRecord();
            $records = self::parseDomainRecordsInfo($fields, $domain, $serviceOthers);

            $db->beginTransaction();

            $table = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_DNS);
            $domainReturning = $db->InsertReturning($table, [
                'dns_domain' => $domain['domain'],
                'fk_provider_id' => $service->fk_provider_id, 'fk_customer_id' => \session()::getUserID(),
                'others' => json_encode(['records' => $records, 'fieldData' => $fields])
            ], ['dns_id'], 'dns_id');

            $jobData = [
                'domain' => $domain,
                'records' => $records,
                'dns_id' => $domainReturning->dns_id
            ];

            $jobs = [
                [
                    'job' => new CloudJobQueueCreateDomain(),
                    'children' => []
                ]
            ];

            TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);

            $db->commit();
        });

        session()->flash(['Domain Creation Enqueued'], [], Session::SessionCategories_FlashMessageSuccess);
        redirect(route('tonicsCloud.domains.index'));
    }

    /**
     * @param string $slugID
     * @return void
     * @throws \Exception
     */
    public function edit(string $slugID)
    {
        $domain = self::getDomain($slugID, 'slug_id');
        $domainOthers = json_decode($domain->others);

        addToGlobalVariable('Data', (array)$domain);

        $fieldCategories = $this->getFieldData()->compareSortAndUpdateFieldItems($domainOthers?->fieldData);
        $htmlFrag = $this->getFieldData()->getUsersFormFrag($fieldCategories);

        view('Apps::TonicsCloud/Views/Domain/edit', [
            'DomainData' => $domain,
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $htmlFrag
        ]);

    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function update(string $slugID)
    {
        $validator = $this->getValidator();
        $validation = $validator->make(input()->fromPost()->all(), $this->getDomainCreateRule());
        if ($validator->fails()){
            session()->flash($validation->getErrors(), input()->fromPost()->all());
            redirect(route('tonicsCloud.domains.edit', [$slugID]));
        }

        $domain = self::getDomain($slugID, 'slug_id');
        $domainOthers = json_decode($domain->others);
        $serviceOthers = null;
        if (input()->fromPost()->hasValue('dns_cloud_instance')){
            $settings = [
                'instance_id' => input()->fromPost()->retrieve('dns_cloud_instance'),
                'user_id' => \session()::getUserID()
            ];
            $service = InstanceController::GetServiceInstances($settings);
            $serviceOthers = json_decode($service->others);
        }

        $domainFromPost = $this->getSoaDomainRecord();

        $fields = json_decode(input()->fromPost()->retrieve('_fieldDetails'));
        $newRecords = self::parseDomainRecordsInfo($fields, $domainFromPost, $serviceOthers);

        # No Changes
        $oldRecords = json_decode(json_encode($domainOthers->records), true);
        if ($domain->dns_domain === $domainFromPost['domain'] && $newRecords === $oldRecords){
            session()->flash(["No Changes, That's Fine"], [], Session::SessionCategories_FlashMessageInfo);
            redirect(route('tonicsCloud.domains.edit', [$slugID]));
        }

        $recordsUntouched = [];
        # Step One, Add Records If It Isn't in The Old Record
        foreach ($newRecords as $key => $newRecord){
            if (isset($oldRecords[$key])){
                $recordsUntouched[$key] = $oldRecords[$key]; # Using the oldRecords data is important, this way, I can access the record_id
                unset($newRecords[$key]);
                unset($oldRecords[$key]);
            }
        }

        # Step Two, Remove Old Records That Isn't In The New Records
        foreach ($oldRecords as $key => $oldRecord){
            if (isset($newRecords[$key])){
                unset($oldRecords[$key]);
                $recordsUntouched[$key] = $oldRecord;
            }
        }


        # Step Three, Update The List of Records We Have
        $recordsUntouched = $recordsUntouched + $newRecords;

        $jobData = [
            'domain' => $domainFromPost,
            'records' => $newRecords,
            'delete_records' => $oldRecords,
            'dns_id' => $domain->dns_id,
            'domain_id' => $domainOthers->domain_id
        ];

        db(onGetDB: function (TonicsQuery $db) use ($recordsUntouched, $fields, $jobData, $domainFromPost, $slugID) {
            $db->beginTransaction();

            $table = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_DNS);
            $db->Update($table)
                ->Set('dns_domain', $domainFromPost['domain'])
                ->Set('others', json_encode(['records' => $recordsUntouched, 'deleted_records' => $jobData['delete_records'], 'fieldData' => $fields, 'domain_id' => $jobData['domain_id']]))
                ->WhereEquals('slug_id', $slugID)
                ->Exec();

            $jobs = [
                [
                    'job' => new CloudJobQueueUpdateDomain(),
                    'children' => []
                ]
            ];

            TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);

            $db->commit();
        });

        session()->flash(["Domain Update Enqueued Changes"], [], Session::SessionCategories_FlashMessageSuccess);
        redirect(route('tonicsCloud.domains.index'));
    }

    /**
     * @throws \Exception
     */
    public function deleteMultiple($entityBag): true
    {
        $deleteItems = $this->getAbstractDataLayer()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveDeleteElements, $entityBag);
        foreach ($deleteItems as $delete) {
            $delete = (array)$delete;
            $serviceInstancePrefix = TonicsCloudActivator::TONICS_CLOUD_DNS . '::';
            $slugID = $delete[$serviceInstancePrefix . 'slug_id'] ?? '';
            $domain = self::getDomain($slugID, 'slug_id');

            if ($domain){
                $domainOthers = json_decode($domain->others);
                $jobData = [
                    'dns_id' => $domain->dns_id,
                    'domain_id' => $domainOthers->domain_id,
                ];

                $jobs = [
                  [
                      'job' => new CloudJobQueueDeleteDomain(),
                      'children' => []
                  ]
                ];

                TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
            }

        }

        return true;
    }

    /**
     * Parses a string and returns an array with specified keys.
     *
     *```
     * $inputString = '1 10 5269 xmpp-server.example.com';
     * $keys = array('key1', 'key2', 'key3', 'key4');
     * $resultArray = parseDNSValueToArray($inputString, $keys);
     * print_r($resultArray);
     * ```
     * @param string $string The input string to parse.
     * @param array $keys The array of keys corresponding to the expected values in the input string.
     * @return array The parsed array with values assigned to the specified keys.
     *
     */
    public static function parseDNSValueToArray(string $string, array $keys): array
    {
        $string = preg_replace('/\s+/', ' ', $string);
        $values = explode(' ', $string);
        $result = [];

        foreach ($keys as $index => $key) {
            if (isset($values[$index])) {
                $value = $values[$index];
                if (is_numeric($value)){
                    $value = (int)$value;
                }
                $result[$key] = $value;
            } else {
                $result[$key] = null;
            }
    }
    
    return $result;
}


    /**
     * @throws \Exception
     */
    public function getDomainCreateRule(): array
    {
        return [
            'dns_domain' => ['required', 'string'],
            'dns_cloud_instance' => ['string']
        ];
    }

    /**
     * @param array $fields
     * @param array $domain
     * @param \stdClass|null $serviceInstanceOthers
     * @return array
     */
    public static function parseDomainRecordsInfo(array &$fields, array $domain, \stdClass $serviceInstanceOthers = null): array
    {
        $records = [];
        $new = null;
        foreach ($fields as $field){
            if (isset($field->field_name) && $field->field_name === RowColumnRepeater::FieldSlug){
                $new = [];
            }

            $subDomain = '';
            if (isset($field->field_input_name)){
                $fieldOptions = json_decode($field->field_options);

                $fieldValue = $fieldOptions->{$field->field_input_name} ?? null;

                if ($field->field_input_name === 'dns_sub_domain'){
                    $subDomain = $fieldValue;
                    $new['name'] = $subDomain . $domain['domain'];
                }

                if ($field->field_input_name === 'dns_record_type'){
                    $new['type'] = $fieldValue;
                }

                if ($field->field_input_name === 'dns_value'){

                    if ($new['type'] === 'A' && empty($fieldValue)){
                        $ipv4 = $serviceInstanceOthers?->ip?->ipv4[0];
                        $fieldOptions->{$field->field_input_name} = $ipv4;
                        $field->field_options = json_encode($fieldOptions);
                        $new['target'] = $ipv4;
                    }

                    else if ($new['type'] === 'AAAA' && empty($fieldValue)){
                        $ipv4 = $serviceInstanceOthers?->ip?->ipv6[0];
                        $ipv6 = self::extractIPv6($ipv4);
                        $fieldOptions->{$field->field_input_name} = $ipv6;
                        $field->field_options = json_encode($fieldOptions);
                        $new['target'] = $ipv6;
                    }

                    else if ($new['type'] === 'MX'){
                        $parsed = self::parseDNSValueToArray($fieldValue, ['priority', 'target']);
                        $new = [...$new, ...$parsed];
                        $new['name'] = $subDomain;
                    } else if ($new['type'] === 'SRV'){
                        $parsed = self::parseDNSValueToArray($fieldValue, ['priority', 'weight', 'port', 'target']);
                        $parsed['protocol'] = null;
                        $new = [...$new, ...$parsed];
                        $new['name'] = $subDomain;
                    } else if ($new['type'] === 'CAA'){
                        $parsed = self::parseDNSValueToArray($fieldValue, ['tag',  'target']);
                        $new = [...$new, ...$parsed];
                        $new['name'] = $subDomain;
                    } else {
                        $new['target'] = $fieldValue;
                    }
                }

                if ($field->field_input_name === 'dns_ttl'){
                    $new['ttl_sec'] = (int)$fieldValue;
                    $records[crc32(json_encode($new))] = $new;
                }
            }

        }

        return $records;
    }

    /**
     * @param string $inputString
     * @return string|null
     */
    public static function extractIPv6(string $inputString): ?string
    {
        $pattern = '/([a-fA-F0-9:]+)\/\d+/';
        preg_match($pattern, $inputString, $matches);
        return $matches[1] ?? null;
    }

    /**
     * @param $domainID
     * @param string $col
     * @return \stdclass|null
     * @throws \Exception
     */
    public static function getDomain($domainID, string $col = 'dns_id'): ?\stdclass
    {
        $domain = null;
        db(onGetDB: function (TonicsQuery $db) use ($col, $domainID, &$domain) {
            $table = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_DNS);
            $providerTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_PROVIDER);
            $domain = $db->Select('*')->From($table)
                ->Join($providerTable, "$providerTable.provider_id", "$table.fk_provider_id")
                ->WhereEquals("fk_customer_id", \session()::getUserID())
                ->WhereEquals(table()->pick([TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_DNS) => [$col]]), $domainID)
                ->FetchFirst();
        });

        if ($domain){
            return  $domain;
        }
        return null;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function getSoaDomainRecord(): array
    {
        return [
            'domain' => input()->fromPost()->retrieve('dns_domain'),
            'type' => 'master',
            'ttl_sec' => 3600,
            'soa_email' => 'olayemi@tonics.app',
            "description" => "Managed By TonicsCloud"
        ];
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData $fieldData
     */
    public function setFieldData(FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @return AbstractDataLayer
     */
    public function getAbstractDataLayer(): AbstractDataLayer
    {
        return $this->abstractDataLayer;
    }

}