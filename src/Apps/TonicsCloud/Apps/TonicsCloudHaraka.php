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
use App\Apps\TonicsCloud\Services\ContainerService;
use Random\RandomException;

class TonicsCloudHaraka extends CloudAppInterface implements CloudAppSignalInterface
{
    const HARAKA_PATH        = '/root/tonics_haraka';
    const HARAKA_CONFIG_PATH = self::HARAKA_PATH . '/config';

    const RELAY_CONFIG_PATH                          = self::HARAKA_CONFIG_PATH . '/relay.ini';
    const SMTP_CONFIG_PATH                           = self::HARAKA_CONFIG_PATH . '/smtp.ini';
    const SERVER_CONFIG_PATH                         = self::HARAKA_CONFIG_PATH . '/me';
    const DKIM_SIGN_CONFIG_PATH                      = self::HARAKA_CONFIG_PATH . '/dkim_sign.ini';
    const DKIM_CONFIG_PATH                           = self::HARAKA_CONFIG_PATH . '/dkim';
    const AUTH_CONFIG_PATH                           = self::HARAKA_CONFIG_PATH . '/auth_enc_file.ini';
    const ALIASES_CONFIG_PATH                        = self::HARAKA_CONFIG_PATH . '/aliases.json';
    const TLS_CONFIG_PATH                            = self::HARAKA_CONFIG_PATH . '/tls.ini';
    const RELAY_DEST_DOMAIN_CONFIG_PATH              = self::HARAKA_CONFIG_PATH . '/relay_dest_domains.ini';
    const RCPT_TO_ACCESS_WHITELIST_CONFIG_PATH       = self::HARAKA_CONFIG_PATH . '/rcpt_to.access.whitelist';
    const RCPT_TO_ACCESS_BLACKLIST_REGEX_CONFIG_PATH = self::HARAKA_CONFIG_PATH . '/rcpt_to.access.blacklist_regex';

    const SERVER_CONFIG_KEY              = 'haraka_server';
    const TLS_CONFIG_KEY                 = 'haraka_tls';
    const ALIAS_FROM_KEY                 = 'alias_from';
    const ALIAS_TO_KEY                   = 'alias_to';
    const REGENERATE_DNS_KEY             = 'haraka_regenerate';
    const DNS_INFO_KEY                   = 'haraka_dns_info';
    const DKIM_DOMAIN_KEY                = 'haraka_dkim_domain';
    const CREDENTIAL_CONFIG_USERNAME_KEY = 'haraka_smtp_credentials_credential_username';
    const CREDENTIAL_CONFIG_PASSWORD_KEY = 'haraka_smtp_credentials_credential_password';

    /**
     *  Data should contain:
     *
     *  ```
     *  [
     *      'haraka_server' => '[[ACME_DOMAIN]]', // haraka me config
     *      'haraka_tls' => "key=/etc/ssl/[[ACME_DOMAIN]].key\ncert=/etc/ssl/[[ACME_DOMAIN]]_fullchain.cer", // haraka tls config
     *      'alias_from' => '...',
     *      'alias_to' => '...',
     *      'haraka_smtp_credentials_credential_username' => '...',
     *      'haraka_smtp_credentials_credential_password' => '...',
     *      'haraka_dkim_domain' => '[[ACME_DOMAIN]]'
     *  ]
     *  ```
     *
     * @param array $data
     *
     * @return mixed
     */
    public static function createFieldDetails (array $data = []): mixed
    {
        $fieldDetails = <<<'JSON'
[{"field_id":1,"field_parent_id":null,"field_name":"modular_rowcolumn","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"modular_rowcolumn\",\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"1cu67cyatly8000000000\",\"field_input_name\":\"\",\"Haraka Config_f461bbe60f2d2be4e7f0\":\"on\"}"},{"field_id":2,"field_parent_id":1,"field_name":"input_text","field_input_name":"haraka_server","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"input_text\",\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"6do8a05l9dw0000000000\",\"field_input_name\":\"haraka_server\",\"haraka_server\":\"[[ACME_DOMAIN]]\"}"},{"field_id":3,"field_parent_id":1,"field_name":"input_text","field_input_name":"haraka_tls","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"input_text\",\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"5vzxl754hmo0000000000\",\"field_input_name\":\"haraka_tls\",\"haraka_tls\":\"key=/etc/ssl/[[ACME_DOMAIN]].key\\ncert=/etc/ssl/[[ACME_DOMAIN]]_fullchain.cer\"}"},{"field_id":4,"field_parent_id":1,"field_name":"modular_rowcolumn","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"modular_rowcolumn\",\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"2pk9rk1vyem0000000000\",\"field_input_name\":\"\"}"},{"field_id":5,"field_parent_id":4,"field_name":"modular_rowcolumnrepeater","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"modular_rowcolumnrepeater\",\"_moreOptions\":{\"inputName\":\"\",\"field_slug_unique_hash\":\"502icowu1ak0000000000\",\"field_slug\":\"modular_rowcolumnrepeater\",\"field_name\":\"Alias\",\"depth\":\"0\",\"repeat_button_text\":\"Repeat Section\",\"grid_template_col\":\" grid-template-columns: ;\",\"row\":\"1\",\"column\":\"2\",\"_cell_position\":null,\"_can_have_repeater_button\":true},\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"502icowu1ak0000000000\",\"field_input_name\":\"\"}"},{"field_id":6,"field_parent_id":5,"field_name":"input_text","field_input_name":"alias_from","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"3okv3dyot1c0000000000\",\"field_input_name\":\"alias_from\",\"alias_from\":\"\"}"},{"field_id":7,"field_parent_id":5,"field_name":"input_text","field_input_name":"alias_to","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"2\",\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"3bxnaffjxzi0000000000\",\"field_input_name\":\"alias_to\",\"alias_to\":\"\"}"},{"field_id":8,"field_parent_id":1,"field_name":"modular_rowcolumn","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"modular_rowcolumn\",\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"3mfflb3bb080000000000\",\"field_input_name\":\"\"}"},{"field_id":9,"field_parent_id":8,"field_name":"modular_rowcolumnrepeater","field_input_name":"haraka_smtp_credentials_credential","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"modular_rowcolumnrepeater\",\"_moreOptions\":{\"inputName\":\"haraka_smtp_credentials_credential\",\"field_slug_unique_hash\":\"3sdfgwrsoqa0000000000\",\"field_slug\":\"modular_rowcolumnrepeater\",\"field_name\":\"Credential\",\"depth\":\"0\",\"repeat_button_text\":\"Add New Credential\",\"grid_template_col\":\" grid-template-columns: ;\",\"row\":\"1\",\"column\":\"2\",\"_cell_position\":null,\"_can_have_repeater_button\":true},\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"3sdfgwrsoqa0000000000\",\"field_input_name\":\"haraka_smtp_credentials_credential\"}"},{"field_id":10,"field_parent_id":9,"field_name":"input_text","field_input_name":"haraka_smtp_credentials_credential_username","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"4iwowl59nyc0000000000\",\"field_input_name\":\"haraka_smtp_credentials_credential_username\",\"haraka_smtp_credentials_credential_username\":\"\"}"},{"field_id":11,"field_parent_id":9,"field_name":"input_text","field_input_name":"haraka_smtp_credentials_credential_password","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"2\",\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"2oygc896l540000000000\",\"field_input_name\":\"haraka_smtp_credentials_credential_password\",\"haraka_smtp_credentials_credential_password\":\"\"}"},{"field_id":12,"field_parent_id":1,"field_name":"modular_rowcolumn","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"modular_rowcolumn\",\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"6t0fynafac00000000000\",\"field_input_name\":\"\"}"},{"field_id":13,"field_parent_id":12,"field_name":"modular_rowcolumnrepeater","field_input_name":"","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"modular_rowcolumnrepeater\",\"_moreOptions\":{\"inputName\":\"\",\"field_slug_unique_hash\":\"39naf0vgi0e0000000000\",\"field_slug\":\"modular_rowcolumnrepeater\",\"field_name\":\"Domain\",\"depth\":\"0\",\"repeat_button_text\":\"Add New Domain\",\"grid_template_col\":\" grid-template-columns: ;\",\"row\":\"1\",\"column\":\"1\",\"_cell_position\":null,\"_can_have_repeater_button\":true},\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"39naf0vgi0e0000000000\",\"field_input_name\":\"\"}"},{"field_id":14,"field_parent_id":13,"field_name":"input_select","field_input_name":"haraka_regenerate","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"input_select\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"4mvmcdobn240000000000\",\"field_input_name\":\"haraka_regenerate\",\"haraka_regenerate\":\"True\"}"},{"field_id":15,"field_parent_id":13,"field_name":"input_text","field_input_name":"haraka_dkim_domain","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"72jyjbup0ew0000000000\",\"field_input_name\":\"haraka_dkim_domain\",\"haraka_dkim_domain\":\"\"}"},{"field_id":16,"field_parent_id":13,"field_name":"input_text","field_input_name":"haraka_dns_info","main_field_slug":"app-tonicscloud-app-config-haraka","field_options":"{\"field_slug\":\"input_text\",\"_cell_position\":\"1\",\"main_field_slug\":\"app-tonicscloud-app-config-haraka\",\"field_slug_unique_hash\":\"29br9v92yjfo000000000\",\"field_input_name\":\"haraka_dns_info\",\"haraka_dns_info\":\"\"}"}]
JSON;
        $fields = json_decode($fieldDetails);
        return json_encode(self::updateFieldOptions($fields, $data));
    }

    /**
     * @inheritDoc
     * @throws \Throwable
     */
    public function updateSettings (): mixed
    {
        $config = $this->getPostPrepareForFlight();
        $command = "";
        foreach ($config as $path => $value) {

            $command .= <<<COMMAND
mkdir -p "$(dirname "$path")" && touch "$path"
    cat <<'EOF' > "$path"
$value
EOF

COMMAND;

            if (str_ends_with($path, '/private')) {
                $command .= "chmod 0400 $path\n";
            }

        }

        return $this->runCommand(null, null, "bash", "-c", <<<SCRIPT
$command
SCRIPT,
        );
    }

    /**
     * @inheritDoc
     */
    public function install (): mixed
    {
        // TODO: Implement install() method.
    }

    /**
     * @inheritDoc
     */
    public function uninstall (): mixed
    {
        // TODO: Implement uninstall() method.
    }

    /**
     * @throws RandomException
     */
    public function prepareForFlight (array $data, string $flightType = self::PREPARATION_TYPE_SETTINGS): mixed
    {
        return $this->parseFields($data);
    }

    /**
     * @throws \Throwable
     */
    public function reload (): bool
    {
        return $this->signalSystemDService('tonics_haraka', self::SystemDSignalRestart);
    }

    /**
     * @throws \Throwable
     */
    public function stop (): bool
    {
        return $this->signalSystemDService('tonics_haraka', self::SystemDSignalStop);
    }

    /**
     * @throws \Throwable
     */
    public function start (): bool
    {
        return $this->signalSystemDService('tonics_haraka', self::SystemDSignalStart);
    }

    /**
     * @param string $statusString
     *
     * @return bool
     * @throws \Throwable
     */
    public function isStatus (string $statusString): bool
    {
        $status = '';
        if (CloudAppSignalInterface::STATUS_RUNNING === $statusString) {
            $this->runCommand(function ($out) use (&$status) { $status = $out; }, null, "bash", "-c", "systemctl show tonics_haraka -p ActiveState");
            return str_starts_with($status, 'ActiveState=active');
        }

        if (CloudAppSignalInterface::STATUS_STOPPED === $statusString) {
            $this->runCommand(function ($out) use (&$status) { $status = $out; }, null, "bash", "-c", "systemctl show tonics_haraka -p ActiveState");
            return str_starts_with($status, 'ActiveState=inactive');
        }

        return false;
    }

    /**
     * @throws RandomException
     * @throws \Exception
     */
    public function parseFields ($fields): array
    {
        $emailWhiteList = [];
        $domainNames = [];
        $settings = [];
        $config = [];
        $replaceDKIM = false;
        $dkim = null;
        $container = ContainerService::getContainer($this->getContainerID());
        $ip = null;
        if ($container) {
            $container->serviceInstanceOthers = json_decode($container->serviceInstanceOthers);
            $ip = $container->serviceInstanceOthers?->ip?->ipv4[0] ?? null;
        }

        $smtpReceiveAndSendOnly = <<<SMTP
listen=[::0]:587,[::0]:25

; Run using cluster to fork multiple backend processes
nodes=cpus

; Daemonize
;daemonize=true
daemon_log_file=/var/log/haraka.log
daemon_pid_file=/var/run/haraka.pid

; Spooling
; Save memory by spooling large messages to disk
spool_dir=/var/spool/haraka

[headers]
; replace max_header_lines
max_lines=1000

; replace max_received_count
max_received=100

SMTP;
        $smtpSendOnly = <<<SMTP
listen=[::0]:587

; Run using cluster to fork multiple backend processes
nodes=cpus

; Daemonize
;daemonize=true
daemon_log_file=/var/log/haraka.log
daemon_pid_file=/var/run/haraka.pid

; Spooling
; Save memory by spooling large messages to disk
spool_dir=/var/spool/haraka

[headers]
; replace max_header_lines
max_lines=1000

; replace max_received_count
max_received=100

SMTP;

        # Prep Config
        $config[self::SMTP_CONFIG_PATH] = $smtpSendOnly;
        $config[self::SERVER_CONFIG_PATH] = '';
        $config[self::RCPT_TO_ACCESS_BLACKLIST_REGEX_CONFIG_PATH] = '';
        $config[self::RCPT_TO_ACCESS_WHITELIST_CONFIG_PATH] = '';
        $config[self::RELAY_CONFIG_PATH] = <<<RELAY
[relay]
acl=true
dest_domains=true
RELAY;
        $config[self::RELAY_DEST_DOMAIN_CONFIG_PATH] = "[domains]\n";
        $config[self::DKIM_SIGN_CONFIG_PATH] = "headers_to_sign=Subject,From,To";
        $config[self::AUTH_CONFIG_PATH] = "";

        foreach ($fields as $field) {
            if (isset($field->main_field_slug) && isset($field->field_input_name)) {
                $fieldOptions = $this->getFieldOption($field);
                $value = $fieldOptions->{$field->field_input_name} ?? '';
                $valueReplaced = $this->replaceContainerGlobalVariables($value);

                if ($field->field_input_name == self::SERVER_CONFIG_KEY) {
                    $config[self::SERVER_CONFIG_PATH] = $valueReplaced;
                    continue;
                }

                if ($field->field_input_name === self::TLS_CONFIG_KEY) {
                    $config[self::TLS_CONFIG_PATH] = $valueReplaced;
                    continue;
                }

                if ($field->field_input_name === self::ALIAS_FROM_KEY && !isset($settings[self::ALIAS_FROM_KEY][$valueReplaced])) {
                    $aliasFrom = $valueReplaced;
                    $settings[self::ALIAS_FROM_KEY][$aliasFrom] = [
                        'action' => 'alias',
                        'to'     => [],
                    ];
                    $dom = $this->extractDomainFromEmail($aliasFrom);
                    if (!empty($dom) && !isset($domainNames[$dom])) {
                        $domainNames[$dom] = $dom;
                        # User want to receive and send, configure that
                        $config[self::SMTP_CONFIG_PATH] = $smtpReceiveAndSendOnly;
                        # Block Sending from all users of the domain by default
                        $config[self::RCPT_TO_ACCESS_BLACKLIST_REGEX_CONFIG_PATH] .= ".*@$dom\n";
                        # Only relay the configured domain
                        $config[self::RELAY_DEST_DOMAIN_CONFIG_PATH] .= "$dom = " . '{ "action": "continue" }' . "\n";
                    }
                    continue;
                }

                if ($field->field_input_name === self::ALIAS_TO_KEY
                    && !empty($aliasFrom)
                    && isset($settings[self::ALIAS_FROM_KEY][$aliasFrom])) {
                    $valueReplacedExploded = explode(',', $valueReplaced);

                    if (!isset($emailWhiteList[$aliasFrom])) {
                        $emailWhiteList[$aliasFrom] = $aliasFrom;
                        # WhiteList Selected Email
                        $config[self::RCPT_TO_ACCESS_WHITELIST_CONFIG_PATH] .= "$aliasFrom\n";
                    }

                    $settings[self::ALIAS_FROM_KEY][$aliasFrom]['to'] = [...$settings[self::ALIAS_FROM_KEY][$aliasFrom]['to'], ...$valueReplacedExploded];
                    continue;
                }

                if ($field->field_input_name === self::CREDENTIAL_CONFIG_USERNAME_KEY && !empty($value)) {
                    $username = $valueReplaced;
                    $settings[self::CREDENTIAL_CONFIG_USERNAME_KEY][$username] = null;
                    continue;
                }

                if ($field->field_input_name === self::CREDENTIAL_CONFIG_PASSWORD_KEY && !empty($username)) {
                    $settings[self::CREDENTIAL_CONFIG_USERNAME_KEY][$username] = $this->mkPasswd($value);
                    $config[self::AUTH_CONFIG_PATH] = "mail={SHA512-CRYPT}{$settings[self::CREDENTIAL_CONFIG_USERNAME_KEY][$username]}\n";
                    continue;
                }

                if ($field->field_input_name == self::REGENERATE_DNS_KEY && $valueReplaced === 'True') {
                    $replaceDKIM = true;
                    $fieldOptions = $this->getFieldOption($field);
                    $field->field_options = $fieldOptions;
                    $fieldOptions->{$field->field_input_name} = 'False'; # Return to false to stop regenerating everytime
                    continue;
                }

                if ($field->field_input_name == self::DKIM_DOMAIN_KEY && $replaceDKIM) {
                    $dom = $valueReplaced;
                    $dkim = $this->generateDkimKeys($dom);
                    # Prep DKIM Location
                    $config[self::DKIM_CONFIG_PATH . "/$dom/private"] = $dkim['private'];
                    $config[self::DKIM_CONFIG_PATH . "/$dom/public"] = $dkim['public'];
                    $config[self::DKIM_CONFIG_PATH . "/$dom/selector"] = $dkim['selector'];
                    $config[self::DKIM_CONFIG_PATH . "/$dom/dns"] = $dkim['dns_key'] . '   ' . $dkim['dns_value'];
                    continue;
                }

                if ($field->field_input_name == self::DNS_INFO_KEY && $replaceDKIM && !empty($dkim)) {
                    $ip = $ip ?? "[[INSTANCE-IP]]";
                    $server = rtrim(strtok($config[self::SERVER_CONFIG_PATH], "\n"));
                    $fieldOptions = $this->getFieldOption($field);
                    $field->field_options = $fieldOptions;
                    $instructions = "For A record, Add:\n$server  A  $ip\n\n";
                    $instructions .= "For MX record, Add:\n{$dkim['domain']}.   MX    10  $server.\n\n";
                    $instructions .= "For DKIM, Add this TXT record to the {$dkim['domain']} DNS zone.\n\n";
                    $instructions .= "{$dkim['dns_key']}   {$dkim['dns_value']}\n\n";
                    $instructions .= "For SPF, Add a new TXT Record:\nv=spf1 mx a -all\n\n";
                    $instructions .= "For DMARC, Add a new TXT Record:\n";
                    $instructions .= "_dmarc  IN TXT v=DMARC1; p=reject; adkim=s; aspf=r; rua=mailto:dmarc-feedback@{$dkim['domain']}; ruf=mailto:dmarc-feedback@{$dkim['domain']}; pct=100\n\n";
                    $fieldOptions->{$field->field_input_name} = $instructions;
                    $replaceDKIM = false;
                    $dkim = null;
                }

            }
        }

        $config[self::ALIASES_CONFIG_PATH] = json_encode($settings[self::ALIAS_FROM_KEY], JSON_PRETTY_PRINT);
        return $config;
    }

    /**
     * @param $password
     *
     * @return string
     * @throws \Random\RandomException
     */
    public function mkPasswd ($password): string
    {
        // Generate a random salt
        $salt = base64_encode(random_bytes(32));
        return crypt($password, '$6$' . $salt);
    }

    /**
     * @param $email
     *
     * @return string
     */
    public function extractDomainFromEmail ($email): string
    {
        return mb_strtolower(explode('@', trim($email))[1] ?? '');
    }

    /**
     * @param $domain
     *
     * @return array
     */
    public function generateDkimKeys ($domain): array
    {
        // Generate the DKIM selector in the specified format
        $selector = strtolower(date('M') . date('Y'));

        // Initialize arrays to hold the keys
        $keys = [];

        // Create a 2048-bit RSA key with an SHA256 digest
        $pk = openssl_pkey_new([
            'digest_alg'       => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        // Extract the private key
        openssl_pkey_export($pk, $privateKey);
        $keys['private'] = $privateKey;

        // Extract the public key
        $pubKey = openssl_pkey_get_details($pk);
        $keys['public'] = $pubKey['key'];

        // Prepare the public key for DNS
        $dnsKey = "$selector._domainkey.$domain IN TXT";
        $dnsValue = 'v=DKIM1;p=';
        $dnsValue2 = 'v=DKIM1;p=';

        // Strip and split the key into smaller parts and format for DNS
        $publicKey = preg_replace('/^-+.*?-+$/m', '', $keys['public']);
        $publicKey = str_replace(["\r", "\n"], '', $publicKey);
        $keyParts = str_split($publicKey, 253); // Becomes 255 when quotes are included

        foreach ($keyParts as $keyPart) {
            $dnsValue .= trim($keyPart);
            $dnsValue2 .= '"' . trim($keyPart) . '" ';
        }

        return [
            'domain'            => $domain,
            'selector'          => $selector,
            'private'           => $keys['private'],
            'public'            => $keys['public'],
            'dns_key'           => $dnsKey,
            'dns_value'         => trim($dnsValue),
            'dns_value_escaped' => trim($dnsValue2),
        ];
    }
}