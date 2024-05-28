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

namespace App\Apps\TonicsCloud\Apps;

use App\Apps\TonicsCloud\Interfaces\CloudAppInterface;
use App\Apps\TonicsCloud\Interfaces\CloudAppSignalInterface;

class TonicsCloudACME extends CloudAppInterface implements CloudAppSignalInterface
{

    /**
     * Data should contain:
     *
     * ```
     * [
     *     'acme_email' => 'example@email.com',
     *     'acme_mode' => 'nginx', // can be: nginx, apache2, standalone
     *     'acme_issuer' => 'Letsencrypt', // can be: Letsencrypt, ZeroSSL
     *     'acme_sites' => ['siteone.com', 'sitetwo.com']
     * ]
     * ```
     *
     * @param array $data
     *
     * @return mixed
     */
    public static function createFieldDetails (array $data = []): mixed
    {
        $fieldDetails = <<<'JSON'
[{"field_id":1,"field_parent_id":null,"field_name":"modular_rowcolumn","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-acme","field_options":"{\"field_slug\":\"modular_rowcolumn\",\"main_field_slug\":\"app-tonicscloud-app-config-acme\",\"field_slug_unique_hash\":\"22wi60ti5ark000000000\",\"field_input_name\":\"\"}"},{"field_id":2,"field_parent_id":1,"field_name":"input_text","field_input_name":"acme_email","main_field_slug":"app-tonicscloud-app-config-acme","field_options":"{\"field_slug\":\"input_text\",\"main_field_slug\":\"app-tonicscloud-app-config-acme\",\"field_slug_unique_hash\":\"777xc71ywjo0000000000\",\"field_input_name\":\"acme_email\",\"acme_email\":\"[[ACME_EMAIL]]\"}"},{"field_id":3,"field_parent_id":1,"field_name":"input_select","field_input_name":"acme_mode","main_field_slug":"app-tonicscloud-app-config-acme","field_options":"{\"field_slug\":\"input_select\",\"main_field_slug\":\"app-tonicscloud-app-config-acme\",\"field_slug_unique_hash\":\"2v741lsnhuo0000000000\",\"field_input_name\":\"acme_mode\",\"acme_mode\":\"nginx\"}"},{"field_id":4,"field_parent_id":1,"field_name":"input_select","field_input_name":"acme_issuer","main_field_slug":"app-tonicscloud-app-config-acme","field_options":"{\"field_slug\":\"input_select\",\"main_field_slug\":\"app-tonicscloud-app-config-acme\",\"field_slug_unique_hash\":\"3heecqdehdk0000000000\",\"field_input_name\":\"acme_issuer\",\"acme_issuer\":\"Letsencrypt\"}"},{"field_id":5,"field_parent_id":1,"field_name":"modular_rowcolumnrepeater","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-acme","field_options":"{\"field_slug\":\"modular_rowcolumnrepeater\",\"_moreOptions\":{\"inputName\":\"\",\"field_slug_unique_hash\":\"3x0h7osnw3q0000000000\",\"field_slug\":\"modular_rowcolumnrepeater\",\"field_name\":\"Domain\",\"depth\":\"0\",\"repeat_button_text\":\"Add New Site\",\"grid_template_col\":\" grid-template-columns: ;\",\"row\":\"1\",\"column\":\"1\",\"_cell_position\":null,\"_can_have_repeater_button\":true},\"main_field_slug\":\"app-tonicscloud-app-config-acme\",\"field_slug_unique_hash\":\"3x0h7osnw3q0000000000\",\"field_input_name\":\"\"}"},{"field_id":6,"field_parent_id":5,"field_name":"input_text","field_input_name":"acme_sites[]","main_field_slug":"app-tonicscloud-app-config-acme","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-acme\",\"field_slug_unique_hash\":\"1cyuq84gapr4000000000\",\"field_input_name\":\"acme_sites[]\",\"acme_sites[]\":\"[[ACME_DOMAIN]]\"}"}] 
JSON;

        $fields = self::updateFieldOptions(json_decode($fieldDetails), $data);
        if (isset($data['acme_sites']) && is_array($data['acme_sites'])) {
            $data['acme_sites[]'] = implode(', ', $data['acme_sites']);
            $fields = self::updateFieldOptions(json_decode($fieldDetails), $data);
        }

        return json_encode($fields);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     * @throws \Throwable
     */
    public function updateSettings (): bool
    {
        return $this->runCommand(
            function ($out) {
                // install acme
                if (empty($out)) {
                    $this->install();
                }
                $this->updateAcmeEmail();
                $this->issueCertificate();
            },
            function ($err) {
                $err = trim($err);
                if (str_ends_with($err, "'{$this->acmeBinDir()}': No such file or directory")) {
                    $this->install();
                    $this->issueCertificate();
                }

            }, "bash", "-c", "ls {$this->acmeBinDir()}");

    }

    /**
     * @inheritDoc
     * @throws \Exception
     * @throws \Throwable
     */
    public function install (): bool
    {
        $email = $this->getAcmeEmail();
        return $this->runCommand(null, null, "bash", "-c", "apt update && apt-get install -y cron curl socat && curl https://get.acme.sh | sh -s email=$email");
    }

    /**
     * @inheritDoc
     * @throws \Exception
     * @throws \Throwable
     */
    public function uninstall (): bool
    {
        return $this->runCommand(null, null, "bash", "-c", "{$this->acmeBin()} --uninstall");
    }

    public function prepareForFlight (array $data, string $flightType = self::PREPARATION_TYPE_SETTINGS): array
    {
        $settings = [];
        $sites = [];
        $modes = [
            'standalone' => 'standalone',
            'nginx'      => 'nginx',
            'apache'     => 'apache',
        ];

        $issuer = [
            'letsencrypt' => 'letsencrypt',
            'zerossl'     => 'zerossl',
        ];

        foreach ($data as $field) {
            if (isset($field->main_field_slug) && isset($field->field_input_name)) {
                $fieldOptions = json_decode($field->field_options);

                $value = strtolower($fieldOptions->{$field->field_input_name} ?? '');

                if ($field->field_input_name == 'acme_email') {
                    $settings['Email'] = $this->replaceContainerGlobalVariables($value);
                }

                if ($field->field_input_name == 'acme_mode' && isset($modes[$value])) {
                    $settings['Mode'] = $value;
                }

                if ($field->field_input_name == 'acme_issuer' && isset($issuer[$value])) {
                    $settings['Issuer'] = $value;
                }

                if ($field->field_input_name == 'acme_sites[]') {
                    $sitesExploded = explode(',', $value);
                    $sites = [...$sites, ...array_map(fn($site) => $this->replaceContainerGlobalVariables(trim($site)), $sitesExploded)];
                }
            }
        }

        $settings['Sites'] = $sites;
        return $settings;
    }

    /**
     * @return string
     */
    private function getAcmeEmail (): string
    {
        $email = $this->getPostPrepareForFlight()->Email ?? '';
        return escapeshellarg(filter_var($email, FILTER_SANITIZE_EMAIL));
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    private function updateAcmeEmail (): void
    {
        $email = $this->getAcmeEmail();
        $dir = $this->acmeRootPath();
        $this->runCommand(null, null, "bash", "-c", <<<CM
sed -i "s/^ACCOUNT_EMAIL='.*'/ACCOUNT_EMAIL=$email/" $dir/account.conf
CM,
        );
    }

    private function acmeRootPath (): string
    {
        return "/root/.acme.sh";
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    private function issueCertificate (): void
    {
        $postFlight = $this->getPostPrepareForFlight();
        $mode = $postFlight->Mode;
        $commands = [];
        foreach ($postFlight->Sites as $site) {
            $site = " -d " . escapeshellarg($site);
            $commands[] = "{$this->acmeBin()} --issue --$mode $site --log";
        }

        $command = implode(" && ", $commands);
        $this->runCommand(null, function ($err) {
            if (!empty($err)) {
                throw new \Exception($err);
            }
        }, "bash", "-c", $command);
        $this->installCert();
    }

    private function acmeBin (): string
    {
        $issuer = $this->getPostPrepareForFlight()->Issuer;
        return "/root/.acme.sh/acme.sh --server $issuer";
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    private function installCert (): void
    {
        $postFlight = $this->getPostPrepareForFlight();
        $mode = $postFlight->Mode;
        foreach ($postFlight->Sites as $site) {
            $reload = '';
            if ($mode !== 'standalone') {
                $reload = " --reloadcmd 'service $mode force-reload' ";
            }

            $certCert = escapeshellarg("/etc/ssl/{$site}_cert.cer");
            $key = escapeshellarg("/etc/ssl/$site.key");
            $fullChainCer = escapeshellarg("/etc/ssl/{$site}_fullchain.cer");

            $siteSafe = escapeshellarg($site);
            $command = "{$this->acmeBin()} --install-cert -d $siteSafe --cert-file $certCert --key-file $key --fullchain-file $fullChainCer $reload --log ";
            $this->runCommand(null, null, "bash", "-c", $command);
        }
    }

    private function acmeBinDir (): string
    {
        return "/root/.acme.sh/acme.sh";
    }

    public function reload (): true
    {
        return true;
    }

    public function stop (): true
    {
        return true;
    }

    public function start (): true
    {
        return true;
    }

    public function isStatus (string $statusString): bool
    {
        return true;
    }
}