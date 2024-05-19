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

use App\Apps\TonicsCloud\Controllers\InstanceController;
use App\Apps\TonicsCloud\Events\OnAddCloudServerEvent;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Exception;
use Throwable;

abstract class CloudServerInterfaceAbstract extends DefaultJobQueuePaths implements HandlerInterface, CloudServerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var $event OnAddCloudServerEvent */
        $event->addCloudServerHandler($this);
    }

    abstract public function displayName(): string;

    abstract public function name(): string;

    abstract public function createInstance(array $data);

    abstract public function destroyInstance(array $data);

    abstract public function resizeInstance(array $data);

    abstract public function changeInstanceStatus(array $data);

    abstract public function isStatus(array $data, string $statusString): bool;

    abstract public function instanceStatus(array $data): mixed;

    abstract public function instance(array $data): mixed;

    abstract public function info(array $data): array;

    abstract public function instances(array $data): \Generator;

    abstract public function regions(): array;

    abstract public function prices(): array;

    /**
     * @param string $cert
     * @param string $sshKey
     * @param bool $production
     * @param int $incusPort
     * @return string
     */
    protected function initializationScript(string $cert, string $sshKey = '', bool $production = true, int $incusPort = 7597): string
    {
        $productionSSH = "DEBIAN_FRONTEND=noninteractive apt-get remove openssh-server -y";
        if ($production === false) {
            $productionSSH = <<<'PRODUCTION'

# Harden SSH
sed -i -e 's/PermitRootLogin yes/PermitRootLogin no/g' /etc/ssh/sshd_config
sed -i -e 's/PasswordAuthentication yes/PasswordAuthentication no/g' /etc/ssh/sshd_config
sed -i -e 's/#PubkeyAuthentication yes/PubkeyAuthentication yes/g' /etc/ssh/sshd_config
systemctl restart sshd

# Add new user
if [ -n "${USERNAME}" ] && [ "${USERNAME}" != "root" ]; then
    useradd -m -s /bin/bash "${USERNAME}"
    # Set password with chpasswd
    chpasswd <<< "${USERNAME}:${PASSWORD}"
    usermod -aG sudo "${USERNAME}"
    # Configure SSH
    SSHDIR="/home/${USERNAME}/.ssh"
    mkdir -p "${SSHDIR}"
    echo "${SSHKEY}" >> "${SSHDIR}/authorized_keys"
    chmod 700 "${SSHDIR}"
    chmod 600 "${SSHDIR}/authorized_keys"
    chown -R "${USERNAME}:${USERNAME}" "${SSHDIR}"
fi
PRODUCTION;
        }

        $endScript = <<<'END'

# Configure hostname
if [ -n "${HOST}" ]; then
    hostnamectl set-hostname "${HOST}"
fi

# Add NameSever
echo "nameserver 8.8.8.8" >> /etc/resolv.conf
service networking restart

# Re-enable history logging
set -o history
END;

        $incusInit = <<<INCUS
# Init incus
# Store the preseed configuration in a file
cat <<EOF > incus-preseed.yaml
config:
  core.https_address: '[::]:$incusPort'
networks:
- config:
    ipv4.address: auto
    ipv6.address: none
  description: ""
  name: incusbr0
  type: ""
  project: default
storage_pools:
- config: {}
  description: ""
  name: default
  driver: dir
profiles:
- config: {}
  description: ""
  devices:
    eth0:
      name: eth0
      network: incusbr0
      type: nic
    root:
      path: /
      pool: default
      type: disk
  name: default
projects: []
cluster: null
EOF

# Run incus init with the preseed file as input
incus admin init --preseed < incus-preseed.yaml

# Add Client Certificate
incus config trust add-certificate incus-client-cert.txt

# Remove the preseed file and the client cert file
rm incus-preseed.yaml incus-client-cert.txt

# Function to create the SystemD services
systemdServices() {
    cat <<'EOF' > /etc/systemd/system/auto-run-scripts.service
[Unit]
Description=Auto Run Scripts Service
After=network.target

[Service]
Type=simple
ExecStart=/bin/bash -c 'mkdir -p /root/scripts && cd /root/scripts && if [ "$(ls -A)" ]; then for script in *; do [ -f "\$script" ] && /bin/bash "\$script"; done && rm -f *; else exit 0; fi'
Restart=always
RestartSec=60s
SyslogIdentifier=auto-run-scripts

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload

# Auto script for tc-agent
systemctl start auto-run-scripts && systemctl enable auto-run-scripts

# Add the tc-agent container
incus launch images:alpine/3.19/amd64 tc-agent
}

systemdServices

INCUS;

        $script = <<<'RAW'

# Disable history logging for password entry
set +o history

# Enable logging
LOG_FILE="/var/log/upcloud_deploy.log"
touch $LOG_FILE
exec > >(tee -a $LOG_FILE) 2>&1

# Install necessary programs
DEBIAN_FRONTEND=noninteractive apt update -y
DEBIAN_FRONTEND=noninteractive apt install -y gnupg

# Import Public Key
curl -fsSL https://pkgs.zabbly.com/key.asc | gpg --show-keys --fingerprint

# Save The Key Locally
mkdir -p /etc/apt/keyrings/
curl -fsSL https://pkgs.zabbly.com/key.asc -o /etc/apt/keyrings/zabbly.asc

# Add The Package To Repository
sh -c 'cat <<EOF > /etc/apt/sources.list.d/zabbly-incus-stable.sources
Enabled: yes
Types: deb
URIs: https://pkgs.zabbly.com/incus/stable
Suites: $(. /etc/os-release && echo ${VERSION_CODENAME})
Components: main
Architectures: $(dpkg --print-architecture)
Signed-By: /etc/apt/keyrings/zabbly.asc
EOF'

# Update system and Install incus
DEBIAN_FRONTEND=noninteractive apt update -y
DEBIAN_FRONTEND=noninteractive apt install -y incus

# Store The Client Cert in a File
cat <<EOF > incus-client-cert.txt
${CERT}
EOF

RAW;

        return <<<VAR
#!/bin/bash

USERNAME="tonics-cloud"
PASSWORD="tonics-cloud"
CERT="$cert"
SSHKEY="$sshKey"

$script
$incusInit
$productionSSH
$endScript
VAR;

    }

    /**
     * @param string $name
     * @return bool
     * @throws Exception
     */
    protected function regionExist(string $name): bool
    {
        foreach ($this->regions() as $region) {
            if (isset($region['id']) && $region['id'] === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $data
     * @return mixed|string|null
     * @throws Exception
     * @throws Throwable
     */
    public static function ProviderInstanceID(array $data): mixed
    {
        $providerInstanceID = $data['provider_instance_id'] ?? '';
        if (empty($providerInstanceID) && isset($data['service_instance_id'])) {
            $settings = [
                'instance_id' => $data['service_instance_id'],
                'column' => 'service_instance_id'
            ];
            $providerInstanceID = InstanceController::GetServiceInstances($settings)?->provider_instance_id;
        }

        return $providerInstanceID;
    }


    /**
     * @param array $data
     * @return mixed|string|null
     * @throws Exception
     * @throws Throwable
     */
    public static function GetServiceInstances(array $data): mixed
    {
        $settings = [
            'instance_id' => $data['service_instance_id'],
            'column' => 'service_instance_id'
        ];
        return InstanceController::GetServiceInstances($settings);
    }

    /**
     * Port to use when connecting remotely to the incus server
     * @return int
     */
    public static function IncusPort(): int
    {
        return 7597;
    }

}