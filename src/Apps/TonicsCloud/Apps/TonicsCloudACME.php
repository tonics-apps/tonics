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
     * @inheritDoc
     * @throws \Exception
     */
    public function updateSettings(): bool
    {
       return $this->runCommand(
            function ($out){
                // install acme
                if (empty($out)){
                    $this->install();
                }
                $this->updateAcmeEmail();
                $this->issueCertificate();
            },
            function ($err) {
                $err = trim($err);
                if (str_ends_with($err, "'{$this->acmeBinDir()}': No such file or directory")){
                    $this->install();
                    $this->issueCertificate();
                }

                }, "bash", "-c", "ls {$this->acmeBinDir()}");

    }

    private function acmeRootPath(): string
    {
        return "/root/.acme.sh";
    }

    private function acmeBinDir(): string
    {
        return "/root/.acme.sh/acme.sh";
    }

    private function acmeBin(): string
    {
        $issuer = $this->getPostPrepareForFlight()->Issuer;
        return "/root/.acme.sh/acme.sh --server $issuer";
    }

    /**
     * @throws \Exception
     */
    private function issueCertificate(): void
    {
        $postFlight = $this->getPostPrepareForFlight();
        $mode = $postFlight->Mode;
        $commands = [];
        foreach ($postFlight->Sites as $site){
            $site = " -d " . escapeshellarg($site);
            $commands[] = "{$this->acmeBin()} --issue --$mode $site --log";
        }

        $command = implode(" && ", $commands);
        $this->runCommand(null,function (){}, "bash", "-c", $command);
        $this->installCert();
    }

    /**
     * @throws \Exception
     */
    private function installCert(): void
    {
        $postFlight = $this->getPostPrepareForFlight();
        $mode = $postFlight->Mode;
        foreach ($postFlight->Sites as $site){
            $reload = '';
            if ($mode !== 'standalone'){
                $reload = <<<RELOAD
--reloadcmd     "service $mode force-reload"
RELOAD;
            }

            $certCert = escapeshellarg("/etc/ssl/{$site}_cert.cer");
            $key = escapeshellarg("/etc/ssl/$site.key");
            $fullchainCer = escapeshellarg("/etc/ssl/{$site}_fullchain.cer");

            $siteSafe = escapeshellarg($site);

            $command = <<<COMMAND
{$this->acmeBin()} --install-cert -d $siteSafe \
--cert-file      $certCert  \
--key-file       $key  \
--fullchain-file $fullchainCer \
$reload --log
COMMAND;

            $this->runCommand(null, null, "bash", "-c", $command);
        }
    }

    /**
     * @return string
     */
    private function getAcmeEmail(): string
    {
        $email = $this->getPostPrepareForFlight()->Email ?? '';
        return escapeshellarg(filter_var($email, FILTER_SANITIZE_EMAIL));
    }
    /**
     * @throws \Exception
     */
    private function updateAcmeEmail(): void
    {
        $email = $this->getAcmeEmail();
        $dir = $this->acmeRootPath();
        $this->runCommand(null, null, "bash", "-c", <<<CM
sed -i "s/^ACCOUNT_EMAIL='.*'/ACCOUNT_EMAIL=$email/" $dir/account.conf
CM
);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function install(): bool
    {
        $email = $this->getAcmeEmail();
        return $this->runCommand(null, null, "bash", "-c", "apt update && apt-get install -y cron curl socat && curl https://get.acme.sh | sh -s email=$email");
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function uninstall(): bool
    {
        return $this->runCommand(null, null, "bash", "-c", "{$this->acmeBin()} --uninstall");
    }

    public function prepareForFlight(array $data, string $flightType = self::PREPARATION_TYPE_SETTINGS): array
    {
        $settings = [];
        $sites = [];
        $modes = [
            'standalone' => 'standalone',
            'nginx' => 'nginx',
            'apache' => 'apache',
        ];

        $issuer = [
            'letsencrypt' => 'letsencrypt',
            'zerossl' => 'zerossl',
        ];

        # Not sure yet if the concept is going to be this way so this condition might be deleted
        /*if ($flightType === self::PREPARATION_TYPE_AUTO) {

            $acmeEmail = $data['acme_email'] ?? null;
            $acmeSite = $data['acme_sites[]'] ?? null;

            $data = [
                [
                    'main_field_slug' => 'acme_sites',
                    'field_input_name' => 'acme_sites[]',
                    'field_options' => <<<JSON
{"acme_sites[]":"$acmeSite"}
JSON,
                ],
                [
                    'main_field_slug' => 'acme_email',
                    'field_input_name' => 'acme_email',
                    'field_options' => <<<JSON
{"acme_email":"$acmeEmail"}
JSON,
                ],
                [
                    'main_field_slug' => 'acme_mode',
                    'field_input_name' => 'acme_mode',
                    'field_options' => '{"acme_mode":"nginx"}'
                ],
                [
                    'main_field_slug' => 'acme_issuer',
                    'field_input_name' => 'acme_issuer',
                    'field_options' => '{"acme_issuer":"letsencrypt"}'
                ]
            ];

            // Convert array of arrays to array of objects
            $data = array_map(fn($item) => (object)$item, $data);
        }*/

        foreach ($data as $field) {
            if (isset($field->main_field_slug) && isset($field->field_input_name)){
                $fieldOptions = json_decode($field->field_options);

                $value = strtolower($fieldOptions->{$field->field_input_name} ?? '');

                if ($field->field_input_name == 'acme_email'){
                    $settings['Email'] = $this->replaceContainerGlobalVariables($value);
                }

                if ($field->field_input_name == 'acme_mode' && isset($modes[$value])){
                    $settings['Mode']  = $value;
                }

                if ($field->field_input_name == 'acme_issuer' && isset($issuer[$value])){
                    $settings['Issuer']  = $value;
                }

                if ($field->field_input_name == 'acme_sites[]'){
                    $sites[] = $this->replaceContainerGlobalVariables($value);
                }
            }
        }

        $settings['Sites'] = $sites;
        return $settings;
    }

    public function reload(): true
    {
        return true;
    }

    public function stop(): true
    {
        return true;
    }

    public function start(): true
    {
        return true;
    }

    public function isStatus(string $statusString): bool
    {
        return true;
    }
}