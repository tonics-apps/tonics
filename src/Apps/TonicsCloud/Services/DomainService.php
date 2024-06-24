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

use App\Apps\TonicsCloud\TonicsCloudActivator;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class DomainService extends TonicsCloudAbstractService
{
    /**
     * @return array[]
     */
    public static function DataTableHeaders (): array
    {
        return [
            [
                'type'  => '', 'hide' => true, 'slug' => TonicsCloudActivator::TONICS_CLOUD_DNS . '::' . 'dns_id',
                'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'dns_id',
            ],
            [
                'type'  => '', 'hide' => true, 'slug' => TonicsCloudActivator::TONICS_CLOUD_DNS . '::' . 'slug_id',
                'title' => 'Slug ID', 'minmax' => '50px, .5fr', 'td' => 'slug_id',
            ],
            [
                'type'   => '',
                'slug'   => TonicsCloudActivator::TONICS_CLOUD_DNS . '::' . 'dns_domain',
                'title'  => 'Domain',
                'minmax' => '55px, .7fr', 'td' => 'dns_domain',
            ],
            [
                'type'   => '',
                'slug'   => TonicsCloudActivator::TONICS_CLOUD_DNS . '::' . 'dns_status_msg',
                'title'  => 'Info',
                'minmax' => '60px, .8fr', 'td' => 'dns_status_msg',
            ],
            ['type' => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_DNS . '::' . 'created_at', 'title' => 'Created At', 'minmax' => '70px, .6fr', 'td' => 'created_at'],
        ];
    }

    /**
     * @param bool $otherColumn
     *
     * @return string
     */
    public static function DefaultDomainSelect (bool $otherColumn = true): string
    {
        $previewColumn = self::PreviewLinkColumn();
        $editColumn = self::EditLinkColumn();
        $dnsRecordsTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_DNS);
        $select = "dns_id, fk_customer_id, slug_id, dns_domain, $previewColumn, dns_status_msg, $dnsRecordsTable.created_at, $editColumn";
        if ($otherColumn) {
            $select = $select . ", $dnsRecordsTable.others";
        }
        return $select;
    }

    /**
     * @return string
     */
    public static function PreviewLinkColumn (): string
    {
        return 'CONCAT("http://", dns_domain) as _preview_link';
    }

    /**
     * @return string
     */
    public static function EditLinkColumn (): string
    {
        return 'CONCAT("/customer/tonics_cloud/domains/", slug_id, "/edit" ) as _edit_link';
    }

    /**
     * @param $domainID
     * @param string $col
     *
     * @return \stdclass|null
     * @throws \Exception
     */
    public static function getDomain ($domainID, string $col = 'dns_id'): ?\stdclass
    {
        $domain = null;
        db(onGetDB: function (TonicsQuery $db) use ($col, $domainID, &$domain) {
            $table = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_DNS);
            $providerTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_PROVIDER);
            $domain = $db->Select(self::DefaultDomainSelect())->From($table)
                ->Join($providerTable, "$providerTable.provider_id", "$table.fk_provider_id")
                ->WhereEquals("fk_customer_id", \session()::getUserID())
                ->WhereEquals(table()->pick([TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_DNS) => [$col]]), $domainID)
                ->FetchFirst();
        });

        if ($domain) {
            return $domain;
        }
        return null;
    }
}