<?php
/*
 * Copyright (c) 2024. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Interfaces;

use App\Apps\TonicsCloud\Controllers\InstanceController;
use App\Apps\TonicsCloud\Events\OnAddCloudServerEvent;
use App\Apps\TonicsCloud\Jobs\Instance\CloudJobQueueDestroyInstance;
use App\Apps\TonicsCloud\Jobs\Instance\CloudJobQueueInstanceHasDeleted;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Exception;
use Throwable;

class CloudServerInterfaceAbstract extends DefaultJobQueuePaths implements HandlerInterface, CloudServerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var $event OnAddCloudServerEvent */
        $event->addCloudServerHandler($this);
    }

    public function displayName(): string
    {
        // TODO: Implement displayName() method.
    }

    public function name(): string
    {
        // TODO: Implement name() method.
    }

    public function createInstance(array $data)
    {
        // TODO: Implement createInstance() method.
    }

    public function destroyInstance(array $data)
    {
        // TODO: Implement destroyInstance() method.
    }

    public function resizeInstance(array $data)
    {
        // TODO: Implement resizeInstance() method.
    }

    public function changeInstanceStatus(array $data)
    {
        // TODO: Implement changeInstanceStatus() method.
    }

    public function isStatus(array $data, string $statusString): bool
    {
        // TODO: Implement isStatus() method.
    }

    public function instanceStatus(array $data): mixed
    {
        // TODO: Implement instanceStatus() method.
    }

    public function instance(array $data): mixed
    {
        // TODO: Implement instance() method.
    }

    public function info(array $data): array
    {
        // TODO: Implement info() method.
    }

    public function instances(array $data): \Generator
    {
        // TODO: Implement instances() method.
    }

    public function regions(): array
    {
        // TODO: Implement regions() method.
    }

    public function prices(): array
    {
        // TODO: Implement prices() method.
    }

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

INCUS;

        $script = <<<'RAW'

# Disable history logging for password entry
set +o history

# Enable logging
LOG_FILE="/var/log/upcloud_deploy.log"
touch $LOG_FILE
exec > >(tee -a $LOG_FILE) 2>&1

# Install necessary programs
DEBIAN_FRONTEND=noninteractive apt-get install -y nano gnupg

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
DEBIAN_FRONTEND=noninteractive apt-get update -y
DEBIAN_FRONTEND=noninteractive apt-get install -y incus

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